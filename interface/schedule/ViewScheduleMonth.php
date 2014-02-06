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
 * $Revision: 7487 $
 * $Id: ViewScheduleMonth.php 7487 2012-08-15 22:35:09Z ipso $
 * $Date: 2012-08-15 15:35:09 -0700 (Wed, 15 Aug 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('schedule','enabled')
		OR !( $permission->Check('schedule','view') OR $permission->Check('schedule','view_own') OR $permission->Check('schedule','view_child')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'My Schedule')); // See index.php
BreadCrumb::setCrumb($title, str_replace('ViewScheduleMonth.php', 'ViewSchedule.php', $_SERVER['REQUEST_URI']) );

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'do',
												'page',
												'sort_column',
												'sort_order',
												'filter_data',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

if ( isset( $filter_data['start_date'] ) AND $filter_data['start_date'] != '' ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
} else {
	$filter_data['start_date'] = time();
}


if ( !isset($filter_data['show_days']) OR ( isset($filter_data['show_days']) AND $filter_data['show_days'] == '' )  ) {
	$filter_data['show_days'] = 1;
}
$filter_data['show_days'] = $filter_data['show_days'] * 7;

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
if ( $permission->Check('schedule','view') == FALSE ) {
	if ( $permission->Check('schedule','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('schedule','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$do = Misc::findSubmitButton('do');
switch (strtolower($do)) {
	case 'view_schedule':
	default:
		$user_ids = array();

		if ( $filter_data['start_date'] != '' AND $filter_data['show_days'] != '' ) {
			$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( $filter_data['start_date'], $current_user_prefs->getStartWeekDay() );
			$end_date = $filter_data['end_date'] = $start_date + ($filter_data['show_days']*86400-3601);
		} else {
			$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getTime(), $current_user_prefs->getStartWeekDay() );
			$end_date = $filter_data['end_date'] = $start_date + (7*(86400-3600));
		}

		Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);

		//var_dump( $filter_data);
		$sf = TTnew( 'ScheduleFactory' );
		$raw_schedule_shifts = $sf->getScheduleArray( $filter_data );
		//Debug::Arr($raw_schedule_shifts, 'Raw Schedule Shifts1: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($raw_schedule_shifts) ) {
			foreach( $raw_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
				foreach ( $day_schedule_shifts as $day_schedule_shift ) {
					$user_ids[] = $day_schedule_shift['user_id'];

					//$schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][] = $day_schedule_shift;
					$schedule_shifts[$day_epoch][] = $day_schedule_shift;
					if ( $day_schedule_shift['status_id'] == 10 ) { //Working
						if ( isset($schedule_shift_totals[$day_epoch]['total_time']) ) {
							$schedule_shift_totals[$day_epoch]['total_time'] += $day_schedule_shift['total_time'];
						} else {
							$schedule_shift_totals[$day_epoch]['total_time'] = $day_schedule_shift['total_time'];
						}
						$schedule_shift_totals[$day_epoch]['users'][] = $day_schedule_shift['user_id'];

					} elseif ( $day_schedule_shift['status_id'] == 20 ) { //Absent
						if ( isset($schedule_shift_totals[$day_epoch]['absent_total_time']) ) {
							$schedule_shift_totals[$day_epoch]['absent_total_time'] += $day_schedule_shift['total_time'];
						} else {
							$schedule_shift_totals[$day_epoch]['absent_total_time'] = $day_schedule_shift['total_time'];
						}
						$schedule_shift_totals[$day_epoch]['absent_users'][] = $day_schedule_shift['user_id'];
					}
				}
			}
		}
		$user_ids = array_unique($user_ids);
		$total_users = count($user_ids);

		//Debug::Arr($schedule_shift_totals, 'Totals: ', __FILE__, __LINE__, __METHOD__, 10);

		//Total up employees/time per day.
		if ( isset($schedule_shift_totals) ) {
			foreach( $schedule_shift_totals as $day_epoch => $total_arr) {
				if ( isset($total_arr['users']) ) {
					$schedule_shift_totals[$day_epoch]['total_users'] = count(array_unique($total_arr['users']));
				}
				if ( isset($total_arr['absent_users']) ) {
					$schedule_shift_totals[$day_epoch]['total_absent_users'] = count(array_unique($total_arr['absent_users']));
				}
			}
		}
		//var_dump($tmp_schedule_shifts);

		$calendar_array = TTDate::getCalendarArray($start_date, $end_date, $current_user_prefs->getStartWeekDay(), FALSE);
		//var_dump($calendar_array);
		$smarty->assign_by_ref('calendar_array', $calendar_array);

		//Get column headers, taking in to account start_day_of_week.
		$x=0;
		foreach( $calendar_array as $tmp_calendar_day ) {
			$calendar_column_headers[] = TTi18n::gettext(date('l', $tmp_calendar_day['epoch']));

			if ( $x == 6 ) {
				break;
			}
			$x++;
		}
		$smarty->assign_by_ref('calendar_column_headers', $calendar_column_headers);

		$hlf = TTnew( 'HolidayListFactory' );
		$holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_ids, $start_date, $end_date );
		//var_dump($holiday_array);
		$smarty->assign_by_ref('holidays', $holiday_array);

		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('serialize_filter_data', urlencode( base64_encode( serialize($filter_data) ) ) );
		$smarty->assign_by_ref('total_users', $total_users);

		$smarty->assign_by_ref('schedule_shifts', $schedule_shifts);
		$smarty->assign_by_ref('schedule_shift_totals', $schedule_shift_totals);

		$smarty->assign_by_ref('do', $do );

		break;
}
Debug::writeToLog();
$smarty->display('schedule/ViewScheduleMonth.tpl');
?>