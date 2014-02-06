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
 * $Revision: 8603 $
 * $Id: AddPayPeriod.php 8603 2012-12-14 01:36:15Z ipso $
 * $Date: 2012-12-13 17:36:15 -0800 (Thu, 13 Dec 2012) $
 */
/*
 * Adds pay periods X hrs in advance, so schedules/shifts have something to attach to.
 * This file should/can be run as often as it needs to (once an hour)
 *
 */
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

$current_epoch = TTDate::getTime();

//If offset is only 24hrs then adding user_date rows can happen before the pay period
//was added. Add pay periods 48hrs in advance now?
$offset = 86400*2; //48hrs

$ppslf = new PayPeriodScheduleListFactory();

$clf = new CompanyListFactory();
$clf->getAll();
if ( $clf->getRecordCount() > 0 ) {
	foreach ( $clf as $c_obj ) {
		if ( $c_obj->getStatus() != 30 ) {
			//Get all pay period schedules.
			$ppslf->getByCompanyId( $c_obj->getId() );
			foreach ($ppslf as $pay_period_schedule) {
				$end_date = NULL;

				$pay_period_schedule->createNextPayPeriod($end_date, $offset);
				if ( PRODUCTION == TRUE AND DEMO_MODE == FALSE ) {
					$pay_period_schedule->forceClosePreviousPayPeriods( $current_epoch );
				}

				unset($ppf);
				unset($pay_period_schedule);
			}
		}
	}
}
Debug::writeToLog();
Debug::Display();
?>