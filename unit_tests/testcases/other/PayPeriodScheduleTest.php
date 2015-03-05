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
 * @group PayPeriodSchedule
 */
class PayPeriodScheduleTest extends PHPUnit_Framework_TestCase {
	public $company_id = NULL;

	public function setUp() {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeZone('PST8PDT');
		TTDate::setTimeUnitFormat( 10 ); //HH:MM

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);
		$this->assertGreaterThan( 0, $this->company_id );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function deleteAllSchedules() {
		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getAll();
		foreach($ppslf as $pay_period_schedule_obj) {
			$pay_period_schedule_obj->Delete();
		}

		return TRUE;
	}

	function createPayPeriodSchedule($type, $start_dow, $transaction_dow, $primary_dom, $secondary_dom, $primary_transaction_dom, $secondary_transaction_dom, $transaction_bd, $day_start_time = '00:00' ) {
		$ppsf = new PayPeriodScheduleFactory();
		$ppsf->setCompany( $this->company_id );
		Debug::text('zzzCompany ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);

		$ppsf->setName( 'test_'.rand(1000, 99999) );
		$ppsf->setDescription( 'test' );
		/*
											20 	=> 'Bi-Weekly',
											30  => 'Semi-Monthly',
											40	=> 'Monthly + Advance'
		*/
		$ppsf->setType( $type );

		$day_start_time = TTDate::parseTimeUnit( $day_start_time );
		Debug::text('parsed Day Start Time: '. $day_start_time, __FILE__, __LINE__, __METHOD__, 10);
		$ppsf->setDayStartTime( $day_start_time );

		if ( $type == 10 OR $type == 20 ) {
		$ppsf->setStartDayOfWeek( $start_dow);
		$ppsf->setTransactionDate( $transaction_dow );
		} elseif  ( $type == 30 ) {
			$ppsf->setPrimaryDayOfMonth( $primary_dom );
			$ppsf->setSecondaryDayOfMonth( $secondary_dom );
			$ppsf->setPrimaryTransactionDayOfMonth($primary_transaction_dom );
			$ppsf->setSecondaryTransactionDayOfMonth( $secondary_transaction_dom );
		} elseif  ( $type == 50 ) {
			$ppsf->setPrimaryDayOfMonth( $primary_dom );
			$ppsf->setPrimaryTransactionDayOfMonth($primary_transaction_dom );
		}

		$ppsf->setTransactionDateBusinessDay( (bool)$transaction_bd );
		$ppsf->setTimeZone('PST8PDT');
		$ppsf->setEnableInitialPayPeriods(FALSE);

		if ( $ppsf->isValid() ) {
			$pp_schedule_id = $ppsf->Save();

			$ppslf = new PayPeriodScheduleListFactory();
			$ret_obj = $ppslf->getById( $pp_schedule_id )->getCurrent();


			return $ret_obj;
		}

		return FALSE;
	}

	//Weekly
	function testWeekly() {

		//	Anchor: 01-Nov-04
		//	Primary: 08-Nov-04
		//	Primary Trans: 12-Nov-04
		//	Secondary: 15-Nov-04
		//	Secondary Trans: 19-Nov-04

		$ret_obj = $this->createPayPeriodSchedule(			10,
															1, //Start DOW - Monday
															5, //Transaction DOW - Friday
															NULL, //Primary DOM
															NULL, //Secondary DOM
															NULL, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeFormat('g:i:s A T');
		$ret_obj->getNextPayPeriod( strtotime('23-Sep-04') );
		$next_end_date = $ret_obj->getNextEndDate();

		//var_dump($ret_obj->getNextStartDate());
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'27-Sep-04 12:00:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'03-Oct-04 11:59:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'08-Oct-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	40, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'04-Oct-04 12:00:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'10-Oct-04 11:59:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Oct-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	41, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'11-Oct-04 12:00:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'17-Oct-04 11:59:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'22-Oct-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	42, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'18-Oct-04 12:00:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'24-Oct-04 11:59:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'29-Oct-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	43, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'25-Oct-04 12:00:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'31-Oct-04 11:59:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'05-Nov-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	44, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'01-Nov-04 12:00:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'07-Nov-04 11:59:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'12-Nov-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	45, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'08-Nov-04 12:00:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'14-Nov-04 11:59:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'19-Nov-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	46, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'15-Nov-04 12:00:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'21-Nov-04 11:59:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'26-Nov-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	47, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'22-Nov-04 12:00:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'28-Nov-04 11:59:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'03-Dec-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	48, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'29-Nov-04 12:00:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'05-Dec-04 11:59:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-Dec-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	49, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'06-Dec-04 12:00:00 AM PST', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 				'12-Dec-04 11:59:59 PM PST', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'17-Dec-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	50, '2- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'13-Dec-04 12:00:00 AM PST', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 	'19-Dec-04 11:59:59 PM PST', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'24-Dec-04', '3- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	51, '3- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'20-Dec-04 12:00:00 AM PST', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 	'26-Dec-04 11:59:59 PM PST', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Dec-04', '4- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	52, '4- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'27-Dec-04 12:00:00 AM PST', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 	'02-Jan-05 11:59:59 PM PST', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'07-Jan-05', '5- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '5- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'03-Jan-05 12:00:00 AM PST', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 	'09-Jan-05 11:59:59 PM PST', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'14-Jan-05', '6- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '6- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'10-Jan-05 12:00:00 AM PST', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 	'16-Jan-05 11:59:59 PM PST', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'21-Jan-05', '7- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	3, '7- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'17-Jan-05 12:00:00 AM PST', '8- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 	'23-Jan-05 11:59:59 PM PST', '8- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'28-Jan-05', '8- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	4, '8- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 	'24-Jan-05 12:00:00 AM PST', '9- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 	'30-Jan-05 11:59:59 PM PST', '9- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'04-Feb-05', '9- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	5, '9- Pay Period Number');

		TTDate::setTimeFormat('g:i A T');
	}
/*
	//Disabled while PP start times are disabled.
	function testWeeklyB() {
		TTDate::setTimeFormat('g:i A T');

		//	Anchor: 01-Nov-04
		//	Primary: 08-Nov-04
		//	Primary Trans: 12-Nov-04
		//	Secondary: 15-Nov-04
		//	Secondary Trans: 19-Nov-04
		$ret_obj = $this->createPayPeriodSchedule(			10,
															1, //Start DOW - Monday
															5, //Transaction DOW - Friday
															NULL, //Primary DOM
															NULL, //Secondary DOM
															NULL, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															TRUE, //Transaction Business Day
															'18:00'
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$ret_obj->getNextPayPeriod( strtotime('29-Nov-04 00:00') );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'29-Nov-04 6:00 PM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 						'06-Dec-04 5:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'10-Dec-04', '1- Transaction Date');
		//$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	49, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'06-Dec-04 6:00 PM PST', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 						'13-Dec-04 5:59 PM PST', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'17-Dec-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	50, '2- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'13-Dec-04 6:00 PM PST', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'20-Dec-04 5:59 PM PST', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'24-Dec-04', '3- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	51, '3- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'20-Dec-04 6:00 PM PST', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'27-Dec-04 5:59 PM PST', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'31-Dec-04', '4- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	52, '4- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'27-Dec-04 6:00 PM PST', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'03-Jan-05 5:59 PM PST', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'07-Jan-05', '5- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '5- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'03-Jan-05 6:00 PM PST', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'10-Jan-05 5:59 PM PST', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'14-Jan-05', '6- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '6- Pay Period Number');
	}
*/

	//Test while PP start time is ignored. See above once its added again.
	function testWeeklyB() {
		TTDate::setTimeFormat('g:i A T');

		//	Anchor: 01-Nov-04
		//	Primary: 08-Nov-04
		//	Primary Trans: 12-Nov-04
		//	Secondary: 15-Nov-04
		//	Secondary Trans: 19-Nov-04
		$ret_obj = $this->createPayPeriodSchedule(			10,
															1, //Start DOW - Monday
															5, //Transaction DOW - Friday
															NULL, //Primary DOM
															NULL, //Secondary DOM
															NULL, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															TRUE, //Transaction Business Day
															'18:00'
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$ret_obj->getNextPayPeriod( strtotime('29-Nov-04 00:00') );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'29-Nov-04 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 						'05-Dec-04 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'10-Dec-04', '1- Transaction Date');
		//$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	49, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'06-Dec-04 12:00 AM PST', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 						'12-Dec-04 11:59 PM PST', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'17-Dec-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	50, '2- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'13-Dec-04 12:00 AM PST', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'19-Dec-04 11:59 PM PST', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'24-Dec-04', '3- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	51, '3- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'20-Dec-04 12:00 AM PST', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'26-Dec-04 11:59 PM PST', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'31-Dec-04', '4- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	52, '4- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'27-Dec-04 12:00 AM PST', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'02-Jan-05 11:59 PM PST', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'07-Jan-05', '5- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '5- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'03-Jan-05 12:00 AM PST', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'09-Jan-05 11:59 PM PST', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'14-Jan-05', '6- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '6- Pay Period Number');
	}

	//Bi-Weekly
	function testBiWeekly() {

		//	Anchor: 01-Nov-04
		//	Primary: 15-Nov-04
		//	Primary Trans: 22-Nov-04
		//	Secondary: 29-Nov-04
		//	Secondary Trans: 06-Dec-04
		$ret_obj = $this->createPayPeriodSchedule(			20,
															1, //Start DOW - Monday
															8, //Transaction DOW - Monday
															NULL, //Primary DOM
															NULL, //Secondary DOM
															NULL, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$ret_obj->getNextPayPeriod( strtotime('27-Nov-04') );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'29-Nov-04', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $next_end_date ), 						'12-Dec-04', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'20-Dec-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	26, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'13-Dec-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $next_end_date ), 						'26-Dec-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'03-Jan-05', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '2- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'27-Dec-04', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'09-Jan-05', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'17-Jan-05', '3- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '3- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'10-Jan-05', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'23-Jan-05', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Jan-05', '4- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	3, '4- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'24-Jan-05', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'06-Feb-05', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'14-Feb-05', '5- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	4, '5- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'07-Feb-05', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'20-Feb-05', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'28-Feb-05', '6- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	5, '6- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'21-Feb-05', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'06-Mar-05', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'14-Mar-05', '7- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	6, '7- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'07-Mar-05', '8- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'20-Mar-05', '8- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'28-Mar-05', '8- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	7, '8- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'21-Mar-05', '9- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'03-Apr-05', '9- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'11-Apr-05', '9- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	8, '9- Pay Period Number');
	}

	//Bi-Weekly
	function testBiWeeklyB() {

		//	Anchor: 01-Nov-04
		//	Primary: 15-Nov-04
		//	Primary Trans: 22-Nov-04
		//	Secondary: 29-Nov-04
		//	Secondary Trans: 06-Dec-04
		$ret_obj = $this->createPayPeriodSchedule(			20,
															1, //Start DOW - Monday
															0, //Transaction DOW - Same Day
															NULL, //Primary DOM
															NULL, //Secondary DOM
															NULL, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$ret_obj->getNextPayPeriod( strtotime('27-Nov-04') );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'29-Nov-04', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $next_end_date ), 						'12-Dec-04', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'12-Dec-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	25, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'13-Dec-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $next_end_date ), 						'26-Dec-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'26-Dec-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	26, '2- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'27-Dec-04', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'09-Jan-05', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'09-Jan-05', '3- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '3- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'10-Jan-05', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'23-Jan-05', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'23-Jan-05', '4- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '4- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'24-Jan-05', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'06-Feb-05', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'06-Feb-05', '5- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	3, '5- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'07-Feb-05', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'20-Feb-05', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'20-Feb-05', '6- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	4, '6- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'21-Feb-05', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'06-Mar-05', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'06-Mar-05', '7- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	5, '7- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'07-Mar-05', '8- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'20-Mar-05', '8- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'20-Mar-05', '8- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	6, '8- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'21-Mar-05', '9- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'03-Apr-05', '9- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'03-Apr-05', '9- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	7, '9- Pay Period Number');

	}

	//Test the DST changes in 2007, for the full year.
	function testBiWeeklyC() {

		//	Anchor: 01-Nov-04
		//	Primary: 15-Nov-04
		//	Primary Trans: 22-Nov-04
		//	Secondary: 29-Nov-04
		//	Secondary Trans: 06-Dec-04
		$ret_obj = $this->createPayPeriodSchedule(			20,
															1, //Start DOW - Monday
															0, //Transaction DOW - Same Day
															NULL, //Primary DOM
															NULL, //Secondary DOM
															NULL, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeFormat('g:i A T');

		$ret_obj->getNextPayPeriod( strtotime('03-Dec-06') );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'04-Dec-06 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'17-Dec-06 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'17-Dec-06', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	25, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'18-Dec-06 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'31-Dec-06 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'31-Dec-06', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	26, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'01-Jan-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'14-Jan-07 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'14-Jan-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'15-Jan-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'28-Jan-07 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'28-Jan-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'29-Jan-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'11-Feb-07 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'11-Feb-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	3, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'12-Feb-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'25-Feb-07 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'25-Feb-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	4, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'26-Feb-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'11-Mar-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'11-Mar-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	5, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'12-Mar-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'25-Mar-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'25-Mar-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	6, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'26-Mar-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'08-Apr-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'08-Apr-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	7, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'09-Apr-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'22-Apr-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'22-Apr-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	8, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'23-Apr-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'06-May-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'06-May-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	9, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'07-May-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'20-May-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'20-May-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	10, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'21-May-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'03-Jun-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'03-Jun-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	11, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'04-Jun-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'17-Jun-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'17-Jun-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	12, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'18-Jun-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'01-Jul-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'01-Jul-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	13, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'02-Jul-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'15-Jul-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'15-Jul-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	14, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'16-Jul-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'29-Jul-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'29-Jul-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	15, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'30-Jul-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'12-Aug-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'12-Aug-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	16, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'13-Aug-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'26-Aug-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'26-Aug-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	17, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'27-Aug-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'09-Sep-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'09-Sep-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	18, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'10-Sep-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'23-Sep-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'23-Sep-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	19, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'24-Sep-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'07-Oct-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'07-Oct-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	20, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'08-Oct-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'21-Oct-07 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'21-Oct-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	21, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Oct-07 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'04-Nov-07 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'04-Nov-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	22, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Nov-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'18-Nov-07 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'18-Nov-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	23, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'19-Nov-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'02-Dec-07 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'02-Dec-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	24, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'03-Dec-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'16-Dec-07 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'16-Dec-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	25, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'17-Dec-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'30-Dec-07 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'30-Dec-07', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	26, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'31-Dec-07 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'13-Jan-08 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'13-Jan-08', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '1- Pay Period Number');
	}

	//Test years that have 27 pay periods (ie: 2015) when pay period ends on a Sunday, pays on a Thursday
	function testBiWeeklyD() {
		$ret_obj = $this->createPayPeriodSchedule(			20,
															1, //Start DOW - Monday (Ends on Sunday)
															4, //Transaction DOW - Following Friday
															NULL, //Primary DOM
															NULL, //Secondary DOM
															NULL, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															FALSE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeFormat('g:i A T');

		$ret_obj->getNextPayPeriod( strtotime('15-Dec-14') );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'15-Dec-14 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'28-Dec-14 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'01-Jan-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	0, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'29-Dec-14 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'11-Jan-15 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'15-Jan-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'12-Jan-15 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'25-Jan-15 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'29-Jan-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'26-Jan-15 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'08-Feb-15 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'12-Feb-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	3, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'09-Feb-15 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'22-Feb-15 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'26-Feb-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	4, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'23-Feb-15 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'08-Mar-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'12-Mar-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	5, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'09-Mar-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'22-Mar-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'26-Mar-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	6, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'23-Mar-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'05-Apr-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'09-Apr-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	7, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'06-Apr-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'19-Apr-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'23-Apr-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	8, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'20-Apr-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'03-May-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'07-May-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	9, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'04-May-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'17-May-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'21-May-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	10, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'18-May-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'31-May-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'04-Jun-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	11, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'01-Jun-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'14-Jun-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'18-Jun-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	12, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'15-Jun-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'28-Jun-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'02-Jul-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	13, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'29-Jun-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'12-Jul-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'16-Jul-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	14, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'13-Jul-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'26-Jul-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'30-Jul-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	15, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'27-Jul-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'09-Aug-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'13-Aug-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	16, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'10-Aug-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'23-Aug-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'27-Aug-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	17, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'24-Aug-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'06-Sep-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'10-Sep-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	18, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'07-Sep-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'20-Sep-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'24-Sep-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	19, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'21-Sep-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'04-Oct-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'08-Oct-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	20, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Oct-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'18-Oct-15 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'22-Oct-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	21, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'19-Oct-15 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'01-Nov-15 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'05-Nov-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	22, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'02-Nov-15 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'15-Nov-15 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'19-Nov-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	23, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'16-Nov-15 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'29-Nov-15 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'03-Dec-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	24, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'30-Nov-15 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'13-Dec-15 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'17-Dec-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	25, '1- Pay Period Number');

		//27th Pay Period
		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'14-Dec-15 12:00 AM PST', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 					'27-Dec-15 11:59 PM PST', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),		'31-Dec-15', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	26, '1- Pay Period Number');
	}

	function testSemiMonthly() {
		//	Anchor: 01-Jan-04
		//	Primary: 15-Jan-04
		//	Primary Trans: 25-Jan-04 w/BD
		//	Secondary: 27-Jan-04 w/LDOM
		//	Secondary Trans: 10-Feb-04 w/BD

		$ret_obj = $this->createPayPeriodSchedule(			30,
															NULL, //Start DOW
															NULL, //Transaction DOW
															1, //Primary DOM
															15, //Secondary DOM
															25, //Primary Trans DOM
															10, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$ret_obj->getNextPayPeriod( strtotime('30-Jan-04') );
		$next_end_date = $ret_obj->getNextEndDate();

		Debug::text('zzStart: '. TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('zzEnd: '. TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Feb-04', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Feb-04', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'25-Feb-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	4, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Feb-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'29-Feb-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-Mar-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	5, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Mar-04', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Mar-04', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'25-Mar-04', '3- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	6, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Mar-04', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Mar-04', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'09-Apr-04', '4- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	7, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Apr-04', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Apr-04', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'23-Apr-04', '5- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	8, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Apr-04', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Apr-04', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-May-04', '6- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	9, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-May-04', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-May-04', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'25-May-04', '7- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	10, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-May-04', '8- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-May-04', '8- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-Jun-04', '8- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	11, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jun-04', '9- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Jun-04', '9- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'25-Jun-04', '9- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	12, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Jun-04', '10- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Jun-04', '10- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'09-Jul-04', '10- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	13, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jul-04', '11- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Jul-04', '11- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'23-Jul-04', '11- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	14, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Jul-04', '12- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Jul-04', '12- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-Aug-04', '12- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	15, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Aug-04', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Aug-04', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'25-Aug-04', '13- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	16, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Aug-04', '14- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Aug-04', '14- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-Sep-04', '14- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	17, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Sep-04', '15- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Sep-04', '15- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'24-Sep-04', '15- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	18, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Sep-04', '16- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Sep-04', '16- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'08-Oct-04', '16- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	19, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Oct-04', '17- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Oct-04', '17- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'25-Oct-04', '17- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	20, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Oct-04', '18- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Oct-04', '18- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-Nov-04', '18- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	21, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Nov-04', '19- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Nov-04', '19- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'25-Nov-04', '19- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	22, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Nov-04', '20- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Nov-04', '20- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-Dec-04', '20- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	23, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Dec-04', '21- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Dec-04', '21- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'24-Dec-04', '21- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	24, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Dec-04', '22- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Dec-04', '22- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-Jan-05', '22- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jan-05', '23- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Jan-05', '23- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'25-Jan-05', '23- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Jan-05', '24- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Jan-05', '24- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'10-Feb-05', '24- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	3, '1- Pay Period Number');
	}

	function testSemiMonthlyB() {
		//	Anchor: 24-Apr-04
		//	Primary: 08-May-04
		//	Primary Trans: 15-May-04 w/BD
		//	Secondary: 22-May-04
		//	Secondary Trans: 27-May-04 w/LDOM & BD
		$ret_obj = $this->createPayPeriodSchedule(			30,
															NULL, //Start DOW
															NULL, //Transaction DOW
															24, //Primary DOM
															8, //Secondary DOM
															15, //Primary Trans DOM
															31, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$ret_obj->getNextPayPeriod( strtotime('01-Dec-03') );
		$next_end_date = $ret_obj->getNextEndDate();

		Debug::text('zzStart: '. TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), __FILE__, __LINE__, __METHOD__, 10);

		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Dec-03', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'23-Dec-03', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Dec-03', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	24, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'24-Dec-03', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Jan-04', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Jan-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Jan-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'23-Jan-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Jan-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'24-Jan-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Feb-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'13-Feb-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	3, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Feb-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'23-Feb-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'27-Feb-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	4, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'24-Feb-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Mar-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Mar-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	5, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Mar-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'23-Mar-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Mar-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	6, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'24-Mar-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Apr-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Apr-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	7, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Apr-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'23-Apr-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Apr-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	8, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'24-Apr-04', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-May-04', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'14-May-04', '2- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	9, '1- Pay Period Number');


		$ret_obj->getNextPayPeriod( strtotime('20-Apr-04') );
		$next_end_date = $ret_obj->getNextEndDate();

		Debug::text('zzStart: '. TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'24-Apr-04', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-May-04', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'14-May-04', '1- Transaction Date');
		$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	9, '1- Pay Period Number');
	}

	function testSemiMonthlyC() {
		//	Anchor: 24-Apr-04
		//	Primary: 08-May-04
		//	Primary Trans: 15-May-04 w/BD
		//	Secondary: 22-May-04
		//	Secondary Trans: 27-May-04 w/LDOM & BD
		$ret_obj = $this->createPayPeriodSchedule(			30,
															NULL, //Start DOW
															NULL, //Transaction DOW
															8, //Primary DOM
															22, //Secondary DOM
															31, //Primary Trans DOM
															15, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);


		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$ret_obj->getNextPayPeriod( strtotime('20-Jun-05') );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Jun-05', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Jul-05', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Jul-05', '1- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Jul-05', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Jul-05', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'29-Jul-05', '2- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Jul-05', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Aug-05', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Aug-05', '3- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Aug-05', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Aug-05', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Aug-05', '4- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Aug-05', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Sep-05', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Sep-05', '5- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Sep-05', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Sep-05', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Sep-05', '6- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Sep-05', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Oct-05', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'14-Oct-05', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Oct-05', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Oct-05', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Oct-05', '6- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Oct-05', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Nov-05', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Nov-05', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Nov-05', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Nov-05', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Nov-05', '6- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Nov-05', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Dec-05', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Dec-05', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'08-Dec-05', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Dec-05', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Dec-05', '6- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Dec-05', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'07-Jan-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'13-Jan-06', '7- Transaction Date');
	}

	function testSemiMonthlyD() {
		//	Anchor: 08-May-04
		//	Primary: 22-May-04
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 05-Jun-04
		//	Secondary Trans: 15-Jun-04 w BD
		$ret_obj = $this->createPayPeriodSchedule(			30,
															NULL, //Start DOW
															NULL, //Transaction DOW
															5, //Primary DOM
															22, //Secondary DOM
															31, //Primary Trans DOM
															15, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);


		$ret_obj->getNextPayPeriod( strtotime('20-Jun-05') );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Jun-05', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'04-Jul-05', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Jul-05', '1- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'05-Jul-05', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Jul-05', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'29-Jul-05', '2- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Jul-05', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'04-Aug-05', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Aug-05', '3- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'05-Aug-05', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Aug-05', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Aug-05', '4- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Aug-05', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'04-Sep-05', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Sep-05', '5- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'05-Sep-05', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Sep-05', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Sep-05', '6- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Sep-05', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'04-Oct-05', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'14-Oct-05', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'05-Oct-05', '8- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Oct-05', '8- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Oct-05', '8- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Oct-05', '9- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'04-Nov-05', '9- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Nov-05', '9- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'05-Nov-05', '10- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Nov-05', '10- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Nov-05', '10- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Nov-05', '11- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'04-Dec-05', '11- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Dec-05', '11- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'05-Dec-05', '12- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'21-Dec-05', '12- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Dec-05', '12- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'22-Dec-05', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'04-Jan-06', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'13-Jan-06', '13- Transaction Date');
	}

	function testSemiMonthlyE() {
		//	Anchor: 08-May-04
		//	Primary: 22-May-04
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 05-Jun-04
		//	Secondary Trans: 15-Jun-04 w BD
		$ret_obj = $this->createPayPeriodSchedule(			30,
															NULL, //Start DOW
															NULL, //Transaction DOW
															5, //Primary DOM
															22, //Secondary DOM
															31, //Primary Trans DOM
															15, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);


		$ret_obj->getNextPayPeriod( strtotime('20-Jun-08') );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Jun-08 12:00 AM PDT', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Jul-08 11:59 PM PDT', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'15-Jul-08 12:00 PM PDT', '1- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Jul-08 12:00 AM PDT', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Jul-08 11:59 PM PDT', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'31-Jul-08 12:00 PM PDT', '2- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Jul-08 12:00 AM PDT', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Aug-08 11:59 PM PDT', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'15-Aug-08 12:00 PM PDT', '3- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Aug-08 12:00 AM PDT', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Aug-08 11:59 PM PDT', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'29-Aug-08 12:00 PM PDT', '4- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Aug-08 12:00 AM PDT', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Sep-08 11:59 PM PDT', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'15-Sep-08 12:00 PM PDT', '5- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Sep-08 12:00 AM PDT', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Sep-08 11:59 PM PDT', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'30-Sep-08 12:00 PM PDT', '6- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Sep-08 12:00 AM PDT', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Oct-08 11:59 PM PDT', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'15-Oct-08 12:00 PM PDT', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Oct-08 12:00 AM PDT', '8- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Oct-08 11:59 PM PDT', '8- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'31-Oct-08 12:00 PM PDT', '8- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Oct-08 12:00 AM PDT', '9- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Nov-08 11:59 PM PST', '9- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'14-Nov-08 12:00 PM PST', '9- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Nov-08 12:00 AM PST', '10- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Nov-08 11:59 PM PST', '10- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'28-Nov-08 12:00 PM PST', '10- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Nov-08 12:00 AM PST', '11- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Dec-08 11:59 PM PST', '11- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'15-Dec-08 12:00 PM PST', '11- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Dec-08 12:00 AM PST', '12- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Dec-08 11:59 PM PST', '12- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'31-Dec-08 12:00 PM PST', '12- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Dec-08 12:00 AM PST', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Jan-09 11:59 PM PST', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'15-Jan-09 12:00 PM PST', '13- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Jan-09 12:00 AM PST', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Jan-09 11:59 PM PST', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'30-Jan-09 12:00 PM PST', '13- Transaction Date');


		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Jan-09 12:00 AM PST', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Feb-09 11:59 PM PST', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'13-Feb-09 12:00 PM PST', '13- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Feb-09 12:00 AM PST', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Feb-09 11:59 PM PST', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'27-Feb-09 12:00 PM PST', '13- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Feb-09 12:00 AM PST', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Mar-09 11:59 PM PST', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'13-Mar-09 12:00 PM PDT', '13- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Mar-09 12:00 AM PST', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Mar-09 11:59 PM PDT', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'31-Mar-09 12:00 PM PDT', '13- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'22-Mar-09 12:00 AM PDT', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'04-Apr-09 11:59 PM PDT', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'15-Apr-09 12:00 PM PDT', '13- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 		'05-Apr-09 12:00 AM PDT', '13- Start Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 		'21-Apr-09 11:59 PM PDT', '13- End Date');
		$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextTransactionDate()),	'30-Apr-09 12:00 PM PDT', '13- Transaction Date');
	}

	function testMonthly() {
		//	Anchor: 01-May-04
		//	Primary: 27-May-04 w/LDOM
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 27-Jun-04
		//	Secondary Trans: 27-Jun-04 w/LDOM BD
		$ret_obj = $this->createPayPeriodSchedule(			50,
															NULL, //Start DOW
															NULL, //Transaction DOW
															1, //Primary DOM
															NULL, //Secondary DOM
															31, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);


		$ret_obj->getNextPayPeriod( strtotime('01-Jul-05') );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jul-05', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Jul-05', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Jul-05', '1- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Aug-05', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Aug-05', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Aug-05', '2- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Sep-05', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Sep-05', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Sep-05', '3- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Oct-05', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Oct-05', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Oct-05', '4- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Nov-05', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Nov-05', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Nov-05', '5- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Dec-05', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Dec-05', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Dec-05', '6- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jan-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Jan-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Jan-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Feb-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'28-Feb-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'28-Feb-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Mar-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Mar-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Mar-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Apr-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Apr-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Apr-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-May-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-May-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-May-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jun-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Jun-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Jun-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jul-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Jul-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Jul-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Aug-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Aug-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Aug-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Sep-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Sep-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Sep-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Oct-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Oct-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Oct-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Nov-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Nov-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Nov-06', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Dec-06', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Dec-06', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Dec-06', '7- Transaction Date');

	}

	//Test month pay period on the 15th of each month.
	function testMonthlyB() {
		//	Anchor: 01-May-04
		//	Primary: 27-May-04 w/LDOM
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 27-Jun-04
		//	Secondary Trans: 27-Jun-04 w/LDOM BD
		$ret_obj = $this->createPayPeriodSchedule(			50,
															NULL, //Start DOW
															NULL, //Transaction DOW
															15, //Primary DOM
															NULL, //Secondary DOM
															15, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															FALSE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);


		$ret_obj->getNextPayPeriod( strtotime('15-Jul-06') );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Jul-06', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Aug-06', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Aug-06', '1- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Aug-06', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Sep-06', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Sep-06', '2- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Sep-06', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Oct-06', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Oct-06', '3- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Oct-06', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Nov-06', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Nov-06', '4- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Nov-06', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Dec-06', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Dec-06', '5- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'15-Dec-06', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'14-Jan-07', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'15-Jan-07', '6- Transaction Date');
	}

	function testMonthlyC() {
		//	Anchor: 01-May-09
		//	Primary: 27-May-09 w/LDOM
		//	Primary Trans: 27-May-09 w/LDOM & BD
		//	Secondary: 27-Jun-09
		//	Secondary Trans: 27-Jun-09 w/LDOM BD
		$ret_obj = $this->createPayPeriodSchedule(			50,
															NULL, //Start DOW
															NULL, //Transaction DOW
															1, //Primary DOM
															NULL, //Secondary DOM
															31, //Primary Trans DOM
															NULL, //Secondary Trans DOM
															TRUE //Transaction Business Day
															);

		Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$ret_obj->getNextPayPeriod( strtotime('01-Jul-09') );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jul-09', '1- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Jul-09', '1- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Jul-09', '1- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Aug-09', '2- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Aug-09', '2- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Aug-09', '2- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Sep-09', '3- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Sep-09', '3- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Sep-09', '3- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Oct-09', '4- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Oct-09', '4- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Oct-09', '4- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Nov-09', '5- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Nov-09', '5- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Nov-09', '5- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Dec-09', '6- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Dec-09', '6- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Dec-09', '6- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jan-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Jan-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Jan-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Feb-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'28-Feb-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'28-Feb-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Mar-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Mar-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Mar-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Apr-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Apr-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Apr-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-May-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-May-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-May-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jun-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Jun-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Jun-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Jul-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Jul-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Jul-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Aug-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Aug-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Aug-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Sep-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Sep-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Sep-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Oct-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'31-Oct-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Oct-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Nov-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ), 			'30-Nov-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'30-Nov-10', '7- Transaction Date');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextStartDate() ), 		'01-Dec-10', '7- Start Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextEndDate() ),			'31-Dec-10', '7- End Date');
		$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),	'31-Dec-10', '7- Transaction Date');

	}

}
?>