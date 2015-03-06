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
 * @group DateTime
 */
class DateTimeTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeZone('Etc/GMT+8', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function testTimeUnit1() {
		Debug::text('Testing Time Unit Format: hh:mm', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('dMY');
		TTDate::setTimeFormat('g:i A');

		/*
			10 	=> 'hh:mm (2:15)',
			12 	=> 'hh:mm:ss (2:15:59)',
			20 	=> 'Hours (2.25)',
			22 	=> 'Hours (2.241)',
			30 	=> 'Minutes (135)'
		*/
		TTDate::setTimeUnitFormat(10);

		$this->assertEquals( TTDate::parseTimeUnit('00:01'), 60 );
		$this->assertEquals( TTDate::parseTimeUnit('-00:01'), -60 );

		$this->assertEquals( TTDate::parseTimeUnit('01:00'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('01'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('-1'), -3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1:'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1:00:00'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1:00:01'), 3601 );

		$this->assertEquals( TTDate::parseTimeUnit('00:60'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit(':60'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit(':1'), 60 );

		$this->assertEquals( TTDate::parseTimeUnit('1:00:01.5'), 3601 );
		$this->assertEquals( TTDate::parseTimeUnit('1:1.5'), 3660 );

		//Hybrid mode
		$this->assertEquals( TTDate::parseTimeUnit('1.000'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1.00'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('-1'), -3600 );
		$this->assertEquals( TTDate::parseTimeUnit('01'), 3600 );

		$this->assertEquals( TTDate::parseTimeUnit('0.25'), 900 );
		$this->assertEquals( TTDate::parseTimeUnit('0.50'), 1800 );

		$this->assertEquals( TTDate::parseTimeUnit('0.34'), 1200 ); //Automatically rounds to nearest 1min
	}

	function testTimeUnit2() {
		Debug::text('Testing Time Unit Format: Decimal', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('dMY');
		TTDate::setTimeFormat('g:i A');

		/*
			10 	=> 'hh:mm (2:15)',
			12 	=> 'hh:mm:ss (2:15:59)',
			20 	=> 'Hours (2.25)',
			22 	=> 'Hours (2.241)',
			30 	=> 'Minutes (135)'
		*/
		TTDate::setTimeUnitFormat(20);

		$this->assertEquals( TTDate::parseTimeUnit('1.000'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1.00'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('-1'), -3600 );
		$this->assertEquals( TTDate::parseTimeUnit('01'), 3600 );

		$this->assertEquals( TTDate::parseTimeUnit('0.25'), 900 );
		$this->assertEquals( TTDate::parseTimeUnit('0.50'), 1800 );

		$this->assertEquals( TTDate::parseTimeUnit('0.34'), 1200 ); //Automatically rounds to nearest 1min

		//Hybrid mode
		$this->assertEquals( TTDate::parseTimeUnit('00:01'), 60 );
		$this->assertEquals( TTDate::parseTimeUnit('-00:01'), -60 );

		$this->assertEquals( TTDate::parseTimeUnit('01:00'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('01'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('-1'), -3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1:'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1:00:00'), 3600 );

		$this->assertEquals( TTDate::parseTimeUnit('00:60'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit(':60'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit(':1'), 60 );

		$this->assertEquals( TTDate::parseTimeUnit('1:00:01.5'), 3600 );
		$this->assertEquals( TTDate::parseTimeUnit('1:1.5'), 3600 );
	}

	function testTimeUnit3() {
		Debug::text('Testing Time Unit Format: Decimal', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('dMY');
		TTDate::setTimeFormat('g:i A');

		/*
			10 	=> 'hh:mm (2:15)',
			12 	=> 'hh:mm:ss (2:15:59)',
			20 	=> 'Hours (2.25)',
			22 	=> 'Hours (2.241)',
			30 	=> 'Minutes (135)'
		*/
		TTDate::setTimeUnitFormat(20);

		$this->assertEquals( TTDate::parseTimeUnit('0.02'), (1 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.03'), (2 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.05'), (3 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.07'), (4 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.08'), (5 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.10'), (6 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.12'), (7 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.13'), (8 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.15'), (9 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.17'), (10 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.18'), (11 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.20'), (12 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.22'), (13 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.23'), (14 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.25'), (15 * 60) );

		$this->assertEquals( TTDate::parseTimeUnit('0.27'), (16 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.28'), (17 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.30'), (18 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.32'), (19 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.33'), (20 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.35'), (21 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.37'), (22 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.38'), (23 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.40'), (24 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.42'), (25 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.43'), (26 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.45'), (27 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.47'), (28 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.48'), (29 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.50'), (30 * 60) );


		$this->assertEquals( TTDate::parseTimeUnit('0.52'), (31 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.53'), (32 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.55'), (33 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.57'), (34 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.58'), (35 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.60'), (36 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.62'), (37 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.63'), (38 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.65'), (39 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.67'), (40 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.68'), (41 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.70'), (42 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.72'), (43 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.73'), (44 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.75'), (45 * 60) );

		$this->assertEquals( TTDate::parseTimeUnit('0.77'), (46 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.78'), (47 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.80'), (48 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.82'), (49 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.84'), (50 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.85'), (51 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.87'), (52 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.89'), (53 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.90'), (54 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.92'), (55 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.94'), (56 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.95'), (57 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.97'), (58 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('0.99'), (59 * 60) );
		$this->assertEquals( TTDate::parseTimeUnit('1.00'), (60 * 60) );
	}

	function testDate_DMY_1() {
		Debug::text('Testing Date Format: d-M-y', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('d-M-y');
		TTDate::setTimeFormat('g:i A');

		$this->assertEquals( TTDate::parseDateTime('25-Feb-05'), 1109318400 );

		$this->assertEquals( TTDate::parseDateTime('25-Feb-05 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-05 8:09 AM'), 1109347740 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-05 8:09:10 AM'), 1109347750 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-05 8:09:10 AM EST'), 1109336950 );

		$this->assertEquals( TTDate::parseDateTime('25-Feb-05 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-05 18:09:10'), 1109383750 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-05 18:09:10 EST'), 1109372950 );


		//Fails on PHP 5.1.2 due to strtotime()
		//TTDate::setDateFormat('d/M/y');
		//TTDate::setTimeFormat('g:i A');

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05'), 1109318400 );

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 8:09PM'), 1109390940 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 8:09 AM'), 1109347740 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 8:09:10 AM'), 1109347750 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 8:09:10 AM EST'), 1109336950 );

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 18:09'), 1109383740 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 18:09:10'), 1109383750 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 18:09:10 EST'), 1109372950 );


		TTDate::setDateFormat('d-M-Y');
		TTDate::setTimeFormat('g:i A');

		$this->assertEquals( TTDate::parseDateTime('25-Feb-2005'), 1109318400 );

		$this->assertEquals( TTDate::parseDateTime('25-Feb-2005 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-2005 8:09 AM'), 1109347740 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-2005 8:09:10 AM'), 1109347750 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-2005 8:09:10 AM EST'), 1109336950 );

		$this->assertEquals( TTDate::parseDateTime('25-Feb-2005 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-2005 18:09:10'), 1109383750 );
		$this->assertEquals( TTDate::parseDateTime('25-Feb-2005 18:09:10 EST'), 1109372950 );

		//Fails on PHP 5.1.2 due to strtotime()

		//TTDate::setDateFormat('d/M/Y');
		//TTDate::setTimeFormat('g:i A');

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005'), 1109318400 );

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 8:09PM'), 1109390940 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 8:09 AM'), 1109347740 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 8:09:10 AM'), 1109347750 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 8:09:10 AM EST'), 1109336950 );

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 18:09'), 1109383740 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 18:09:10'), 1109383750 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 18:09:10 EST'), 1109372950 );
	}

	function testDate_DMY_2() {
		Debug::text('Testing Date Format: dMY', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('dMY');
		TTDate::setTimeFormat('g:i A');

		$this->assertEquals( TTDate::parseDateTime('25Feb2005'), 1109318400 );

		$this->assertEquals( TTDate::parseDateTime('25Feb2005 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('25Feb2005 8:09 AM'), 1109347740 );
		$this->assertEquals( TTDate::parseDateTime('25Feb2005 8:09:10 AM'), 1109347750 );
		$this->assertEquals( TTDate::parseDateTime('25Feb2005 8:09:10 AM EST'), 1109336950 );

		$this->assertEquals( TTDate::parseDateTime('25Feb2005 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('25Feb2005 18:09:10'), 1109383750 );
		$this->assertEquals( TTDate::parseDateTime('25Feb2005 18:09:10 EST'), 1109372950 );
	}

	function testDate_DMY_3() {
		Debug::text('Testing Date Format: d-m-y', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('d-m-y');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::parseDateTime('25-02-2005'), 1109318400 );
		$this->assertEquals( TTDate::parseDateTime('25-02-2005 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('25-02-2005 8:09 AM'), 1109347740 );

		TTDate::setTimeFormat('g:i A T');
		$this->assertEquals( TTDate::parseDateTime('25-02-2005 8:09 AM EST'), 1109336940 );

		TTDate::setTimeFormat('G:i');
		$this->assertEquals( TTDate::parseDateTime('25-02-2005 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('25-02-2005 18:09 EST'), 1109372940 );

		//
		// Different separator
		//

		TTDate::setDateFormat('d/m/y');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::parseDateTime('25/02/2005'), 1109318400 );
		$this->assertEquals( TTDate::parseDateTime('25/02/2005 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('25/02/2005 8:09 AM'), 1109347740 );

		TTDate::setTimeFormat('g:i A T');
		$this->assertEquals( TTDate::parseDateTime('25/02/2005 8:09 AM EST'), 1109336940 );

		TTDate::setTimeFormat('G:i');
		$this->assertEquals( TTDate::parseDateTime('25/02/2005 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('25/02/2005 18:09 EST'), 1109372940 );
	}

	function testDate_MDY_1() {
		Debug::text('Testing Date Format: m-d-y', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('m-d-y');
		//Debug::text('zzz1: ', date_default_timezone_get(), __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeZone('PST8PDT'); //Force to non-DST timezone. 'PST' isnt actually valid.
		//Debug::text('zzz2: ', date_default_timezone_get(), __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::parseDateTime('02-25-2005'), 1109318400 );
		$this->assertEquals( TTDate::parseDateTime('02-25-05'), 1109318400 );

		$this->assertEquals( TTDate::parseDateTime('10-27-06'), 1161932400 );

		$this->assertEquals( TTDate::parseDateTime('02-25-2005 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('02-25-2005 8:09 AM'), 1109347740 );

		TTDate::setTimeFormat('g:i A T');
		$this->assertEquals( TTDate::parseDateTime('02-25-2005 8:09 AM EST'), 1109336940 );

		TTDate::setTimeFormat('G:i');
		$this->assertEquals( TTDate::parseDateTime('02-25-2005 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('02-25-2005 18:09 EST'), 1109372940 );

		//
		// Different separator
		//
		TTDate::setDateFormat('m/d/y');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::parseDateTime('02/25/2005'), 1109318400 );
		$this->assertEquals( TTDate::parseDateTime('02/25/2005 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('02/25/2005 8:09 AM'), 1109347740 );

		TTDate::setTimeFormat('g:i A T');
		$this->assertEquals( TTDate::parseDateTime('02/25/2005 8:09 AM EST'), 1109336940 );

		TTDate::setTimeFormat('G:i');
		$this->assertEquals( TTDate::parseDateTime('02/25/2005 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('02/25/2005 18:09 EST'), 1109372940 );
	}

	function testDate_MDY_2() {
		Debug::text('Testing Date Format: M-d-y', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('M-d-y');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::parseDateTime('Feb-25-2005'), 1109318400 );
		$this->assertEquals( TTDate::parseDateTime('Feb-25-05'), 1109318400 );
		$this->assertEquals( TTDate::parseDateTime('Feb-25-2005 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('Feb-25-2005 8:09 AM'), 1109347740 );

		TTDate::setTimeFormat('g:i A T');
		$this->assertEquals( TTDate::parseDateTime('Feb-25-2005 8:09 AM EST'), 1109336940 );

		TTDate::setTimeFormat('G:i');
		$this->assertEquals( TTDate::parseDateTime('Feb-25-2005 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('Feb-25-2005 18:09 EST'), 1109372940 );
	}

	function testDate_MDY_3() {
		Debug::text('Testing Date Format: m-d-y (two digit year)', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('m-d-y');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::parseDateTime('02-25-05'), 1109318400 );
		$this->assertEquals( TTDate::parseDateTime('02-25-05 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('02-25-05 8:09 AM'), 1109347740 );

		TTDate::setTimeFormat('g:i A T');
		$this->assertEquals( TTDate::parseDateTime('02-25-05 8:09 AM EST'), 1109336940 );

		TTDate::setTimeFormat('G:i');
		$this->assertEquals( TTDate::parseDateTime('02-25-05 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('02-25-05 18:09 EST'), 1109372940 );

		//Try test before 1970, like 1920 - *1920 fails after 2010 has passed, try a different value.

		$this->assertEquals( TTDate::parseDateTime('02-25-55'), -468604800 );
		$this->assertEquals( TTDate::parseDateTime('02-25-55 8:09PM'), -468532260 );
		$this->assertEquals( TTDate::parseDateTime('02-25-55 8:09 AM'), -468575460 );

	}


	function testDate_YMD_1() {
		Debug::text('Testing Date Format: Y-m-d', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('Y-m-d');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::parseDateTime('2005-02-25'), 1109318400 );
		$this->assertEquals( TTDate::parseDateTime('05-02-25'), 1109318400 );
		$this->assertEquals( TTDate::parseDateTime('2005-02-25 8:09PM'), 1109390940 );
		$this->assertEquals( TTDate::parseDateTime('2005-02-25 8:09 AM'), 1109347740 );

		TTDate::setTimeFormat('g:i A T');
		$this->assertEquals( TTDate::parseDateTime('2005-02-25 8:09 AM EST'), 1109336940 );

		TTDate::setTimeFormat('G:i');
		$this->assertEquals( TTDate::parseDateTime('2005-02-25 18:09'), 1109383740 );
		$this->assertEquals( TTDate::parseDateTime('2005-02-25 18:09 EST'), 1109372940 );
	}

	function test_getDayOfNextWeek() {
		Debug::text('Testing Date Format: Y-m-d', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('Y-m-d');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::getDateOfNextDayOfWeek( strtotime('29-Dec-06'), strtotime('27-Dec-06') ), strtotime('03-Jan-07') );
		$this->assertEquals( TTDate::getDateOfNextDayOfWeek( strtotime('25-Dec-06'), strtotime('28-Dec-06') ), strtotime('28-Dec-06') );
		$this->assertEquals( TTDate::getDateOfNextDayOfWeek( strtotime('31-Dec-06'), strtotime('25-Dec-06') ), strtotime('01-Jan-07') );

	}

	function test_getDateOfNextDayOfMonth() {
		Debug::text('Testing Date Format: Y-m-d', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('Y-m-d');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('01-Dec-06'), strtotime('02-Dec-06') ), strtotime('02-Dec-06') );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('14-Dec-06'), strtotime('23-Nov-06') ), strtotime('23-Dec-06') );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('14-Dec-06'), strtotime('13-Dec-06') ), strtotime('13-Jan-07') );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('14-Dec-06'), strtotime('14-Dec-06') ), strtotime('14-Dec-06') );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('12-Dec-06'), strtotime('01-Dec-04') ), strtotime('01-Jan-07') );

		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('12-Dec-06'), NULL, 1 ), strtotime('01-Jan-07') );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('12-Dec-06'), NULL, 12 ), strtotime('12-Dec-06') );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('12-Dec-06'), NULL, 31 ), strtotime('31-Dec-06') );

		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('01-Feb-07'), NULL, 31 ), strtotime('28-Feb-07') );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('01-Feb-08'), NULL, 29 ), strtotime('29-Feb-08') );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('01-Feb-08'), NULL, 31 ), strtotime('29-Feb-08') );

		//Anchor Epoch: 09-Apr-04 11:59 PM PDT Day Of Month Epoch:  Day Of Month: 24<br>
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime('09-Apr-04'), NULL, 24 ), strtotime('24-Apr-04') );
	}

	function test_parseEpoch() {
		Debug::text('Testing Date Parsing of EPOCH!', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setDateFormat('m-d-y');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::parseDateTime(1162670400), (int)1162670400 );


		TTDate::setDateFormat('Y-m-d');

		TTDate::setTimeFormat('g:i A');
		$this->assertEquals( TTDate::parseDateTime(1162670400), (int)1162670400 );
		
		$this->assertEquals( TTDate::parseDateTime(600), 600 ); //Test small epochs that may conflict with 24hr time that just has the time and not a date.
		$this->assertEquals( TTDate::parseDateTime(1800), 1800 );  //Test small epochs that may conflict with 24hr time that just has the time and not a date.

		$this->assertEquals( TTDate::parseDateTime(-600), -600 ); //Test small epochs that may conflict with 24hr time that just has the time and not a date.
		$this->assertEquals( TTDate::parseDateTime(-1800), -1800 ); //Test small epochs that may conflict with 24hr time that just has the time and not a date.
	}

	function test_roundTime() {
		//10 = Down
		//20 = Average
		//30 = Up

		//Test rounding down by 15minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:06 AM'), (60 * 15), 10), strtotime('15-Apr-07 8:00 AM') );
		//Test rounding down by 5minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:06 AM'), (60 * 5), 10), strtotime('15-Apr-07 8:05 AM') );
		//Test rounding down by 5minutes when no rounding should occur.
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:05 AM'), (60 * 5), 10), strtotime('15-Apr-07 8:05 AM') );

		//Test rounding down by 15minutes with 3minute grace.
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 4:58 PM'), (60 * 15), 10, (60 * 3) ), strtotime('15-Apr-07 5:00 PM') );
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 4:56 PM'), (60 * 15), 10, (60 * 3) ), strtotime('15-Apr-07 4:45 PM') );
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 5:11 PM'), (60 * 15), 10, (60 * 3) ), strtotime('15-Apr-07 5:00 PM') );
		//Test rounding down by 5minutes with 2minute grace
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 5:11 PM'), (60 * 5), 10, (60 * 2) ), strtotime('15-Apr-07 5:10 PM') );
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 5:07 PM'), (60 * 5), 10, (60 * 2) ), strtotime('15-Apr-07 5:05 PM') );


		//Test rounding avg by 15minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:06 AM'), (60 * 15), 20), strtotime('15-Apr-07 8:00 AM') );
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:08 AM'), (60 * 15), 20), strtotime('15-Apr-07 8:15 AM') );
		//Test rounding avg by 5minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:06 AM'), (60 * 5), 20), strtotime('15-Apr-07 8:05 AM') );
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:08 AM'), (60 * 5), 20), strtotime('15-Apr-07 8:10 AM') );
		//Test rounding avg by 5minutes when no rounding should occur.
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:05 AM'), (60 * 5), 20), strtotime('15-Apr-07 8:05 AM') );


		//Test rounding up by 15minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:06 AM'), (60 * 15), 30), strtotime('15-Apr-07 8:15 AM') );
		//Test rounding up by 5minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:06 AM'), (60 * 5), 30), strtotime('15-Apr-07 8:10 AM') );
		//Test rounding up by 5minutes when no rounding should occur.
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:05 AM'), (60 * 5), 30), strtotime('15-Apr-07 8:05 AM') );

		//Test rounding up by 15minutes with 3minute grace.
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:01 AM'), (60 * 15), 30, (60 * 3) ), strtotime('15-Apr-07 8:00 AM') );
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:04 AM'), (60 * 15), 30, (60 * 3) ), strtotime('15-Apr-07 8:15 AM') );
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:03 AM'), (60 * 15), 30, (60 * 3) ), strtotime('15-Apr-07 8:00 AM') );
		//Test rounding up by 5minutes with 2minute grace
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:03 AM'), (60 * 5), 30, (60 * 2) ), strtotime('15-Apr-07 8:05 AM') );
		$this->assertEquals( (int)TTDate::roundTime( strtotime('15-Apr-07 8:01 AM'), (60 * 5), 30, (60 * 2) ), strtotime('15-Apr-07 8:00 AM') );

	}

	function test_graceTime() {
		$this->assertEquals( (int)TTDate::graceTime( strtotime('15-Apr-07 7:58 AM'), (60 * 5), strtotime('15-Apr-07 8:00 AM') ), strtotime('15-Apr-07 8:00 AM') );
		$this->assertEquals( (int)TTDate::graceTime( strtotime('15-Apr-07 7:58:23 AM'), (60 * 5), strtotime('15-Apr-07 8:00 AM') ), strtotime('15-Apr-07 8:00 AM') );
	}

	function test_calculateTimeOnEachDayBetweenRange() {
		$test1_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime('01-Jan-09 8:00AM'), strtotime('01-Jan-09 11:30PM') );
		$this->assertEquals( count($test1_result), 1 );
		$this->assertEquals( $test1_result[1230796800], 55800 );

		$test2_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime('01-Jan-09 4:00PM'), strtotime('02-Jan-09 8:00AM') );
		$this->assertEquals( count($test2_result), 2 );
		$this->assertEquals( $test2_result[1230796800], 28800 );
		$this->assertEquals( $test2_result[1230883200], 28800 );

		$test3_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime('01-Jan-09 4:00PM'), strtotime('03-Jan-09 8:00AM') );
		$this->assertEquals( count($test3_result), 3 );
		$this->assertEquals( $test3_result[1230796800], 28800 );
		$this->assertEquals( $test3_result[1230883200], 86400 );
		$this->assertEquals( $test3_result[1230969600], 28800 );

		$test4_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime('01-Jan-09 4:00PM'), strtotime('9-Jan-09 8:00AM') );
		$this->assertEquals( count($test4_result), 9 );
		$this->assertEquals( $test4_result[1230796800], 28800 );
		$this->assertEquals( $test4_result[1230883200], 86400 );
		$this->assertEquals( $test4_result[1230969600], 86400 );
		$this->assertEquals( $test4_result[1231056000], 86400 );
		$this->assertEquals( $test4_result[1231142400], 86400 );
		$this->assertEquals( $test4_result[1231228800], 86400 );
		$this->assertEquals( $test4_result[1231315200], 86400 );
		$this->assertEquals( $test4_result[1231401600], 86400 );
		$this->assertEquals( $test4_result[1231488000], 28800 );

		$test5_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime('01-Jan-09 12:00AM'), strtotime('01-Jan-09 12:59:59PM') );
		$this->assertEquals( count($test5_result), 1 );
		$this->assertEquals( $test5_result[1230796800], 46799 );

		$test5_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime('01-Jan-09 12:00AM'), strtotime('02-Jan-09 12:00AM') );
		$this->assertEquals( count($test5_result), 1 );
		$this->assertEquals( $test5_result[1230796800], 86400 );

		$test5_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime('01-Jan-09 12:01AM'), strtotime('02-Jan-09 12:01AM') );
		$this->assertEquals( count($test5_result), 2 );
		$this->assertEquals( $test5_result[1230796800], 86340);
		$this->assertEquals( $test5_result[1230883200], 60);

		$test5_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime('01-Jan-09 1:53PM'), strtotime('03-Jan-09 6:12AM') );
		$this->assertEquals( count($test5_result), 3 );
		$this->assertEquals( $test5_result[1230796800], 36420);
		$this->assertEquals( $test5_result[1230883200], 86400);
		$this->assertEquals( $test5_result[1230969600], 22320);
	}

	function test_getWeek() {
		//Match up with PHP's function
		$date1 = strtotime('01-Nov-09 12:00PM');
		$this->assertEquals( date('W', $date1 ), 44 );
		$this->assertEquals( TTDate::getWeek( $date1, 1), 44 );

		$date1 = strtotime('02-Nov-09 12:00PM');
		$this->assertEquals( date('W', $date1 ), 45 );
		$this->assertEquals( TTDate::getWeek( $date1, 1), 45 );

		$date1 = strtotime('03-Nov-09 12:00PM');
		$this->assertEquals( date('W', $date1 ), 45 );
		$this->assertEquals( TTDate::getWeek( $date1, 1), 45 );

		$date1 = strtotime('04-Nov-09 12:00PM');
		$this->assertEquals( date('W', $date1 ), 45 );
		$this->assertEquals( TTDate::getWeek( $date1, 1), 45 );

		$date1 = strtotime('05-Nov-09 12:00PM');
		$this->assertEquals( date('W', $date1 ), 45 );
		$this->assertEquals( TTDate::getWeek( $date1, 1), 45 );

		$date1 = strtotime('06-Nov-09 12:00PM');
		$this->assertEquals( date('W', $date1 ), 45 );
		$this->assertEquals( TTDate::getWeek( $date1, 1), 45 );

		$date1 = strtotime('07-Nov-09 12:00PM');
		$this->assertEquals( date('W', $date1 ), 45 );
		$this->assertEquals( TTDate::getWeek( $date1, 1), 45 );

		$date1 = strtotime('08-Nov-09 12:00PM');
		$this->assertEquals( date('W', $date1 ), 45 );
		$this->assertEquals( TTDate::getWeek( $date1, 1), 45 );

		$date1 = strtotime('09-Nov-09 12:00PM');
		$this->assertEquals( date('W', $date1 ), 46 );
		$this->assertEquals( TTDate::getWeek( $date1, 1), 46 );

		//Test with Sunday as start day of week.
		$date1 = strtotime('01-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 45 );

		$date1 = strtotime('02-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 45 );

		$date1 = strtotime('03-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 45 );

		$date1 = strtotime('04-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 45 );

		$date1 = strtotime('05-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 45 );

		$date1 = strtotime('06-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 45 );

		$date1 = strtotime('07-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 45 );

		$date1 = strtotime('08-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 46 );

		$date1 = strtotime('09-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 46 );


		//Test with Tuesday as start day of week.
		$date1 = strtotime('01-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 44 );

		$date1 = strtotime('02-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 44 );

		$date1 = strtotime('03-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 45 );

		$date1 = strtotime('04-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 45 );

		$date1 = strtotime('05-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 45 );

		$date1 = strtotime('06-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 45 );

		$date1 = strtotime('07-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 45 );

		$date1 = strtotime('08-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 45 );

		$date1 = strtotime('09-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 45 );

		$date1 = strtotime('10-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 46 );

		$date1 = strtotime('11-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 2), 46 );


		//Test with Wed as start day of week.
		$date1 = strtotime('03-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 3), 44 );

		$date1 = strtotime('04-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 3), 45 );

		$date1 = strtotime('05-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 3), 45 );

		//Test with Thu as start day of week.
		$date1 = strtotime('04-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 4), 44 );

		$date1 = strtotime('05-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 4), 45 );

		$date1 = strtotime('06-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 4), 45 );

		//Test with Fri as start day of week.
		$date1 = strtotime('05-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 5), 44 );

		$date1 = strtotime('06-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 5), 45 );

		$date1 = strtotime('07-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 5), 45 );

		//Test with Sat as start day of week.
		$date1 = strtotime('06-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 6), 44 );

		$date1 = strtotime('07-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 6), 45 );

		$date1 = strtotime('08-Nov-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 6), 45 );

		//Test with different years
		$date1 = strtotime('31-Dec-09 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 1), 53 );

		$date1 = strtotime('01-Jan-10 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 1), 53 );

		$date1 = strtotime('04-Jan-10 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 1), 1 );

		$date1 = strtotime('03-Jan-10 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 0), 1 );

		$date1 = strtotime('09-Jan-10 12:00PM');
		$this->assertEquals( TTDate::getWeek( $date1, 6), 1 );


		//Start on Monday as thats what PHP uses.
		for( $i = strtotime('07-Jan-13'); $i < strtotime('06-Jan-13'); $i += (86400 * 7) ) {
			$this->assertEquals( TTDate::getWeek( $i, 1 ), date('W', $i ) );
		}

		//Start on Sunday.
		$this->assertEquals( TTDate::getWeek( strtotime('29-Dec-12'), 0 ), 52 );
		$this->assertEquals( TTDate::getWeek( strtotime('30-Dec-12'), 0 ), 1 );
		$this->assertEquals( TTDate::getWeek( strtotime('31-Dec-12'), 0 ), 1 );
		$this->assertEquals( TTDate::getWeek( strtotime('01-Jan-12'), 0 ), 1 );
		$this->assertEquals( TTDate::getWeek( strtotime('02-Jan-12'), 0 ), 1 );
		$this->assertEquals( TTDate::getWeek( strtotime('03-Jan-12'), 0 ), 1 );
		$this->assertEquals( TTDate::getWeek( strtotime('04-Jan-12'), 0 ), 1 );
		$this->assertEquals( TTDate::getWeek( strtotime('05-Jan-12'), 0 ), 1 );
		$this->assertEquals( TTDate::getWeek( strtotime('06-Jan-13'), 0 ), 2 );

		$this->assertEquals( TTDate::getWeek( strtotime('09-Apr-13'), 0 ), 15 );
		$this->assertEquals( TTDate::getWeek( strtotime('28-Jun-13'), 0 ), 26 );

		//Start on every other day of the week
		$this->assertEquals( TTDate::getWeek( strtotime('28-Jun-13'), 6 ), 25 );
		$this->assertEquals( TTDate::getWeek( strtotime('27-Jun-13'), 5 ), 25 );
		$this->assertEquals( TTDate::getWeek( strtotime('26-Jun-13'), 4 ), 25 );
		$this->assertEquals( TTDate::getWeek( strtotime('25-Jun-13'), 3 ), 25 );
		$this->assertEquals( TTDate::getWeek( strtotime('24-Jun-13'), 2 ), 25 );
		$this->assertEquals( TTDate::getWeek( strtotime('23-Jun-13'), 1 ), 25 );
		$this->assertEquals( TTDate::getWeek( strtotime('22-Jun-13'), 0 ), 25 );
	}

	function test_getNearestWeekDay() {
		//case 0: //No adjustment
		//	break 2;
		//case 1: //Previous day
		//	$epoch -= 86400;
		//	break;
		//case 2: //Next day
		//	$epoch += 86400;
		//	break;
		//case 3: //Closest day


		$date1 = strtotime('16-Jan-2010 12:00PM');
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 0 ), strtotime('16-Jan-2010 12:00PM') );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 1 ), strtotime('15-Jan-2010 12:00PM') );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 2 ), strtotime('18-Jan-2010 12:00PM') );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3 ), strtotime('15-Jan-2010 12:00PM') );

		$date2 = strtotime('17-Jan-2010 12:00PM');
		$this->assertEquals( TTDate::getNearestWeekDay( $date2, 3 ), strtotime('18-Jan-2010 12:00PM') );

		$holidays = array(
						TTDate::getBeginDayEpoch( strtotime('15-Jan-2010') )
						);
		$date1 = strtotime('16-Jan-2010 12:00PM');
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3, $holidays ), strtotime('14-Jan-2010 12:00PM') );

		$holidays = array(
						TTDate::getBeginDayEpoch( strtotime('15-Jan-2010') ),
						TTDate::getBeginDayEpoch( strtotime('14-Jan-2010') )
						);
		$date1 = strtotime('16-Jan-2010 12:00PM');
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 1, $holidays ), strtotime('13-Jan-2010 12:00PM') );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3, $holidays ), strtotime('18-Jan-2010 12:00PM') );

		$holidays = array(
						TTDate::getBeginDayEpoch( strtotime('15-Jan-2010') ),
						TTDate::getBeginDayEpoch( strtotime('14-Jan-2010') ),
						TTDate::getBeginDayEpoch( strtotime('18-Jan-2010') )
						);
		$date1 = strtotime('16-Jan-2010 12:00PM');
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 1, $holidays ), strtotime('13-Jan-2010 12:00PM') );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3, $holidays ), strtotime('13-Jan-2010 12:00PM') );

		$holidays = array(
						TTDate::getBeginDayEpoch( strtotime('15-Jan-2010') ),
						TTDate::getBeginDayEpoch( strtotime('14-Jan-2010') ),
						TTDate::getBeginDayEpoch( strtotime('13-Jan-2010') ),
						TTDate::getBeginDayEpoch( strtotime('18-Jan-2010') )
						);
		$date1 = strtotime('16-Jan-2010 12:00PM');
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 1, $holidays ), strtotime('12-Jan-2010 12:00PM') );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3, $holidays ), strtotime('19-Jan-2010 12:00PM') );
	}

	function test_timePeriodDates() {
		Debug::text('Testing Time Period Dates!', __FILE__, __LINE__, __METHOD__, 10);
		TTDate::setTimeZone('PST8PDT');

		$dates = TTDate::getTimePeriodDates('custom_date', strtotime('15-Jul-10 12:00 PM'), NULL, array('start_date' => strtotime('10-Jul-10 12:43 PM'), 'end_date' => strtotime('12-Jul-10 12:43 PM') ) );
		$this->assertEquals( $dates['start_date'], (int)1278745200 );
		$this->assertEquals( $dates['end_date'], (int)1279004399 );

		$dates = TTDate::getTimePeriodDates('custom_time', strtotime('15-Jul-10 12:00 PM'), NULL, array('start_date' => strtotime('10-Jul-10 12:43 PM'), 'end_date' => strtotime('12-Jul-10 12:53 PM') ) );
		$this->assertEquals( $dates['start_date'], (int)1278790980 );
		$this->assertEquals( $dates['end_date'], (int)1278964380 );

		$dates = TTDate::getTimePeriodDates('today', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1279177200 );
		$this->assertEquals( $dates['end_date'], (int)1279263599 );

		$dates = TTDate::getTimePeriodDates('yesterday', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1279090800 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates('last_24_hours', strtotime('15-Jul-10 12:43 PM') );
		$this->assertEquals( $dates['start_date'], (int)1279136580 );
		$this->assertEquals( $dates['end_date'], (int)1279222980 );

		$dates = TTDate::getTimePeriodDates('this_week', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1278831600 );
		$this->assertEquals( $dates['end_date'], (int)1279436399 );

		$dates = TTDate::getTimePeriodDates('last_week', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1278226800 );
		$this->assertEquals( $dates['end_date'], (int)1278831599 );

		$dates = TTDate::getTimePeriodDates('last_7_days', strtotime('15-Jul-10 12:43 PM') );
		$this->assertEquals( $dates['start_date'], (int)1278572400 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates('this_month', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1277967600 );
		$this->assertEquals( $dates['end_date'], (int)1280645999 );

		$dates = TTDate::getTimePeriodDates('last_month', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1275375600 );
		$this->assertEquals( $dates['end_date'], (int)1277967599 );

		$dates = TTDate::getTimePeriodDates('last_month', strtotime('15-Mar-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1265011200 );
		$this->assertEquals( $dates['end_date'], (int)1267430399 );

		$dates = TTDate::getTimePeriodDates('last_30_days', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1276585200 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates('this_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1277967600 );
		$this->assertEquals( $dates['end_date'], (int)1285916399 );

		$dates = TTDate::getTimePeriodDates('last_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1270105200 );
		$this->assertEquals( $dates['end_date'], (int)1277967599 );

		$dates = TTDate::getTimePeriodDates('last_90_days', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1271401200 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates('this_year_1st_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1262332800 );
		$this->assertEquals( $dates['end_date'], (int)1270105199 );

		$dates = TTDate::getTimePeriodDates('this_year_2nd_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1270105200 );
		$this->assertEquals( $dates['end_date'], (int)1277967599 );

		$dates = TTDate::getTimePeriodDates('this_year_3rd_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1277967600 );
		$this->assertEquals( $dates['end_date'], (int)1285916399 );

		$dates = TTDate::getTimePeriodDates('this_year_4th_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1285916400 );
		$this->assertEquals( $dates['end_date'], (int)1293868799 );

		$dates = TTDate::getTimePeriodDates('last_year_1st_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1230796800 );
		$this->assertEquals( $dates['end_date'], (int)1238569199 );

		$dates = TTDate::getTimePeriodDates('last_year_2nd_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1238569200 );
		$this->assertEquals( $dates['end_date'], (int)1246431599 );

		$dates = TTDate::getTimePeriodDates('last_year_3rd_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1246431600 );
		$this->assertEquals( $dates['end_date'], (int)1254380399 );

		$dates = TTDate::getTimePeriodDates('last_year_4th_quarter', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1254380400 );
		$this->assertEquals( $dates['end_date'], (int)1262332799 );

		$dates = TTDate::getTimePeriodDates('last_3_months', strtotime('15-May-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1266134400 );
		$this->assertEquals( $dates['end_date'], (int)1273906799 );

		$dates = TTDate::getTimePeriodDates('last_6_months', strtotime('15-May-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1258185600 );
		$this->assertEquals( $dates['end_date'], (int)1273906799 );

		$dates = TTDate::getTimePeriodDates('last_9_months', strtotime('15-May-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1250233200 );
		$this->assertEquals( $dates['end_date'], (int)1273906799 );

		$dates = TTDate::getTimePeriodDates('last_12_months', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1247554800 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates('this_year', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1262332800 );
		$this->assertEquals( $dates['end_date'], (int)1293868799 );

		$dates = TTDate::getTimePeriodDates('last_year', strtotime('15-Jul-10 12:00 PM') );
		$this->assertEquals( $dates['start_date'], (int)1230796800 );
		$this->assertEquals( $dates['end_date'], (int)1262332799 );
	}

	function test_DST() {
		TTDate::setTimeZone('PST8PDT'); //Force to timezone with DST.
		
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('03-Nov-12 10:00PM'), strtotime('04-Nov-12 1:59AM') ), FALSE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('03-Nov-12 10:00PM'), strtotime('04-Nov-12 2:00AM') ), TRUE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('03-Nov-12 10:00PM'), strtotime('04-Nov-12 2:01AM') ), TRUE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('04-Nov-12 12:30AM'), strtotime('04-Nov-12 1:59AM') ), FALSE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('04-Nov-12 12:30AM'), strtotime('04-Nov-12 2:00AM') ), TRUE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('04-Nov-12 1:00AM'), strtotime('04-Nov-12 6:30AM') ), TRUE );


		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('09-Mar-13 10:00PM'), strtotime('10-Mar-13 1:59AM') ), FALSE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('09-Mar-13 10:00PM'), strtotime('10-Mar-13 2:00AM') ), TRUE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('09-Mar-13 10:00PM'), strtotime('10-Mar-13 2:01AM') ), TRUE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('10-Mar-13 12:30AM'), strtotime('10-Mar-13 1:59AM') ), FALSE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('10-Mar-13 12:30AM'), strtotime('10-Mar-13 2:00AM') ), TRUE );
		$this->assertEquals( TTDate::doesRangeSpanDST( strtotime('10-Mar-13 1:30AM'), strtotime('10-Mar-13 6:30AM') ), TRUE );



		$this->assertEquals( TTDate::getDSTOffset( strtotime('03-Nov-12 10:00PM'), strtotime('04-Nov-12 1:59AM') ), 0 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('03-Nov-12 10:00PM'), strtotime('04-Nov-12 2:00AM') ), -3600 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('03-Nov-12 10:00PM'), strtotime('04-Nov-12 2:01AM') ), -3600 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('04-Nov-12 12:30AM'), strtotime('04-Nov-12 1:59AM') ), 0 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('04-Nov-12 12:30AM'), strtotime('04-Nov-12 2:00AM') ), -3600 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('04-Nov-12 1:00AM'), strtotime('04-Nov-12 6:30AM') ), -3600 );


		$this->assertEquals( TTDate::getDSTOffset( strtotime('09-Mar-13 10:00PM'), strtotime('10-Mar-13 1:59AM') ), 0 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('09-Mar-13 10:00PM'), strtotime('10-Mar-13 2:00AM') ), 3600 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('09-Mar-13 10:00PM'), strtotime('10-Mar-13 2:01AM') ), 3600 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('10-Mar-13 12:30AM'), strtotime('10-Mar-13 1:59AM') ), 0 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('10-Mar-13 12:30AM'), strtotime('10-Mar-13 2:00AM') ), 3600 );
		$this->assertEquals( TTDate::getDSTOffset( strtotime('10-Mar-13 1:30AM'), strtotime('10-Mar-13 6:30AM') ), 3600 );
	}

	function test_inApplyFrequencyWindow() {
		//Annually
		$frequency_criteria = array(
									'month' => 1,
									'day_of_month' => 2,
									'day_of_week' => 0,
									//'quarter' => 0,
									'quarter_month' => 0,
									'date' => 0
									);

		//No range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, strtotime('01-Jan-2010'), strtotime('01-Jan-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, strtotime('02-Jan-2010'), strtotime('02-Jan-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, strtotime('03-Jan-2010'), strtotime('03-Jan-2010'), $frequency_criteria ), FALSE );
		//Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, strtotime('01-Jan-2010'), strtotime('03-Jan-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, strtotime('02-Jan-2010 12:00PM'), strtotime('02-Jan-2010 12:00PM'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, strtotime('02-Jan-2010 12:00AM'), strtotime('02-Jan-2010 11:59PM'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('01-Jan-2010') - (86400 * 7) ), strtotime('01-Jan-2010'), $frequency_criteria ), FALSE ); //Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('02-Jan-2010') - (86400 * 7) ), strtotime('02-Jan-2010'), $frequency_criteria ), TRUE ); //Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('03-Jan-2010') - (86400 * 7) ), strtotime('03-Jan-2010'), $frequency_criteria ), TRUE ); //Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('04-Jan-2010') - (86400 * 7) ), strtotime('04-Jan-2010'), $frequency_criteria ), TRUE ); //Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('05-Jan-2010') - (86400 * 7) ), strtotime('05-Jan-2010'), $frequency_criteria ), TRUE ); //Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('06-Jan-2010') - (86400 * 7) ), strtotime('06-Jan-2010'), $frequency_criteria ), TRUE ); //Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('07-Jan-2010') - (86400 * 7) ), strtotime('07-Jan-2010'), $frequency_criteria ), TRUE ); //Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('08-Jan-2010') - (86400 * 7) ), strtotime('08-Jan-2010'), $frequency_criteria ), TRUE ); //Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('09-Jan-2010') - (86400 * 7) ), strtotime('09-Jan-2010'), $frequency_criteria ), TRUE ); //Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 20, ( strtotime('10-Jan-2010') - (86400 * 7) ), strtotime('10-Jan-2010'), $frequency_criteria ), FALSE ); //Range


		//Quarterly
		$frequency_criteria = array(
									'month' => 0,
									'day_of_month' => 15,
									'day_of_week' => 0,
									//'quarter' => 3,
									'quarter_month' => 2,
									'date' => 0,
									);

		//No range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Feb-2010'), strtotime('14-Feb-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('15-Feb-2010'), strtotime('15-Feb-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('16-Feb-2010'), strtotime('16-Feb-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-May-2010'), strtotime('14-May-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('15-May-2010'), strtotime('15-May-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('16-May-2010'), strtotime('16-May-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Aug-2010'), strtotime('14-Aug-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('15-Aug-2010'), strtotime('15-Aug-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('16-Aug-2010'), strtotime('16-Aug-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Nov-2010'), strtotime('14-Nov-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('15-Nov-2010'), strtotime('15-Nov-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('16-Nov-2010'), strtotime('16-Nov-2010'), $frequency_criteria ), FALSE );

		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Jan-2010'), strtotime('14-Jan-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Mar-2010'), strtotime('14-Mar-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Apr-2010'), strtotime('14-Apr-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Jun-2010'), strtotime('14-Jun-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Jul-2010'), strtotime('14-Jul-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Sep-2010'), strtotime('14-Sep-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Oct-2010'), strtotime('14-Oct-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('14-Dec-2010'), strtotime('14-Dec-2010'), $frequency_criteria ), FALSE );

		//Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('01-Aug-2010'), strtotime('15-Aug-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('15-Aug-2010'), strtotime('20-Aug-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('16-Aug-2010'), strtotime('20-Aug-2010'), $frequency_criteria ), FALSE );

		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('01-Jul-2010'), strtotime('15-Aug-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('01-Jun-2010'), strtotime('15-Aug-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('01-May-2010'), strtotime('15-Aug-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('01-Apr-2010'), strtotime('15-Aug-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('01-Apr-2009'), strtotime('15-Aug-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('16-Aug-2009'), strtotime('14-Dec-2010'), $frequency_criteria ), TRUE );

		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('19-Aug-2009'), strtotime('14-Nov-2009'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('19-Aug-2009'), strtotime('15-Nov-2009'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 25, strtotime('19-Aug-2009'), strtotime('15-Nov-2010'), $frequency_criteria ), TRUE );

		//Monthly
		$frequency_criteria = array(
									'month' => 2,
									'day_of_month' => 31,
									'day_of_week' => 0,
									//'quarter' => 0,
									'quarter_month' => 0,
									'date' => 0,
									);

		//No range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('27-Feb-2010'), strtotime('27-Feb-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('28-Feb-2010'), strtotime('28-Feb-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('01-Mar-2010'), strtotime('01-Mar-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('27-Feb-2011'), strtotime('27-Feb-2011'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('28-Feb-2011'), strtotime('28-Feb-2011'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('01-Mar-2011'), strtotime('01-Mar-2011'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('28-Feb-2012'), strtotime('28-Feb-2012'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('29-Feb-2012'), strtotime('29-Feb-2012'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('01-Mar-2012'), strtotime('01-Mar-2012'), $frequency_criteria ), FALSE );

		//Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('28-Feb-2010'), strtotime('05-Mar-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 30, strtotime('22-Feb-2010'), strtotime('28-Feb-2010'), $frequency_criteria ), TRUE );


		//Weekly
		$frequency_criteria = array(
									'month' => 0,
									'day_of_month' => 0,
									'day_of_week' => 2, //Tuesday
									//'quarter' => 0,
									'quarter_month' => 0,
									'date' => 0,
									);

		//No range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('12-Apr-2010'), strtotime('12-Apr-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('13-Apr-2010'), strtotime('13-Apr-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('14-Apr-2010'), strtotime('14-Apr-2010'), $frequency_criteria ), FALSE );

		//Range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('07-Apr-2010'), strtotime('12-Apr-2010'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('14-Apr-2010'), strtotime('19-Apr-2010'), $frequency_criteria ), FALSE );

		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('11-Apr-2010'), strtotime('17-Apr-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('12-Apr-2010'), strtotime('18-Apr-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('13-Apr-2010'), strtotime('19-Apr-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('14-Apr-2010'), strtotime('20-Apr-2010'), $frequency_criteria ), TRUE );

		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('12-Apr-2010'), strtotime('17-Apr-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('13-Apr-2010'), strtotime('17-Apr-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('14-Apr-2010'), strtotime('17-Apr-2010'), $frequency_criteria ), FALSE );

		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('11-Apr-2010'), strtotime('18-Apr-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('11-Apr-2010'), strtotime('19-Apr-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('11-Apr-2010'), strtotime('24-Apr-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 40, strtotime('11-Apr-2010'), strtotime('25-Apr-2010'), $frequency_criteria ), TRUE );

		//Specific date
		$frequency_criteria = array(
									'month' => 0,
									'day_of_month' => 0,
									'day_of_week' => 0,
									//'quarter' => 0,
									'quarter_month' => 0,
									'date' => strtotime('01-Jan-2010'),
									);

		//No range
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 100, strtotime('01-Jan-2010'), strtotime('01-Jan-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 100, strtotime('31-Dec-2009'), strtotime('01-Jan-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 100, strtotime('01-Jan-2010'), strtotime('02-Jan-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 100, strtotime('30-Dec-2009'), strtotime('31-Dec-2009'), $frequency_criteria ), FALSE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 100, strtotime('02-Jan-2010'), strtotime('03-Jan-2010'), $frequency_criteria ), FALSE );

		$this->assertEquals( TTDate::inApplyFrequencyWindow( 100, strtotime('01-Jan-2009'), strtotime('01-Jan-2010'), $frequency_criteria ), TRUE );
		$this->assertEquals( TTDate::inApplyFrequencyWindow( 100, strtotime('01-Jan-2009'), strtotime('01-Jan-2011'), $frequency_criteria ), TRUE );
	}

	//Compare pure PHP implementation of EasterDays to PHP calendar extension.
	function test_EasterDays() {
		if ( function_exists('easter_days') ) {
			for($i = 2000; $i < 2050; $i++ ) {
				$this->assertEquals( easter_days( $i ), TTDate::getEasterDays( $i ) );
			}
		}
	}

	function testTimeZones() {
		$upf = new UserPreferenceFactory();
		$zones = $upf->getOptions('time_zone');
		
		foreach( $zones as $zone => $name ) {
			$retval = TTDate::setTimeZone( Misc::trimSortPrefix( $zone ) );
			$this->assertEquals( $retval, TRUE );
		}
	}

}
?>