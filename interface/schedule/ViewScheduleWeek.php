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
 * $Id: ViewScheduleWeek.php 7487 2012-08-15 22:35:09Z ipso $
 * $Date: 2012-08-15 15:35:09 -0700 (Wed, 15 Aug 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once(Environment::getBasePath() .'classes/misc/arr_multisort.class.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('schedule','enabled')
		OR !( $permission->Check('schedule','view') OR $permission->Check('schedule','view_own') OR $permission->Check('schedule','view_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'My Schedule')); // See index.php
//BreadCrumb::setCrumb($title);
BreadCrumb::setCrumb($title, str_replace('ViewScheduleWeek.php', 'ViewSchedule.php', $_SERVER['REQUEST_URI']) );

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
switch ($do) {
	case 'view_schedule':
	default:
		$user_ids = array();

		if ( $filter_data['start_date'] != '' AND $filter_data['show_days'] != '' ) {
			$start_date = $filter_data['start_date'] = TTDate::getBeginDayEpoch( TTDate::parseDateTime($filter_data['start_date']) );
			$end_date = $filter_data['end_date'] = $start_date + ($filter_data['show_days']*86400-3601);
		} else {
			$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getTime(), $current_user_prefs->getStartWeekDay() );
			$end_date = $filter_data['end_date'] = $start_date + ($filter_data['show_days']*(86400-3601));
		}

		Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);

		$sf = TTnew( 'ScheduleFactory' );
		$raw_schedule_shifts = $sf->getScheduleArray( $filter_data );
		if ( is_array($raw_schedule_shifts) ) {
			foreach( $raw_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
				foreach ( $day_schedule_shifts as $day_schedule_shift ) {
					$user_ids[] = $day_schedule_shift['user_id'];

					$day_schedule_shift['is_owner'] = $permission->isOwner( $day_schedule_shift['user_created_by'], $day_schedule_shift['user_id'] );
					$day_schedule_shift['is_child'] = $permission->isChild( $day_schedule_shift['user_id'], $permission_children_ids );

					$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][] = $day_schedule_shift;
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

		//Total up employees/time per day.
		if ( isset($schedule_shift_totals) ) {
			foreach( $schedule_shift_totals as $day_epoch => $total_arr) {
				if ( !isset($total_arr['users']) ) {
					$total_arr['users'] = array();
				}
				$schedule_shift_totals[$day_epoch]['total_users'] = count(array_unique($total_arr['users']));
			}
		}
		//print_r($schedule_shift_totals);

		if ( isset($tmp_schedule_shifts) ) {
			//Sort Branches/Departments first
			foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
				ksort($day_tmp_schedule_shift);
				$tmp_schedule_shifts[$day_epoch] = $day_tmp_schedule_shift;

				foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
					ksort($tmp_schedule_shifts[$day_epoch][$branch]);
				}
			}

			//Sort each department by start time.
			foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
				foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
					foreach ( $department_schedule_shifts as $department => $department_schedule_shift ) {
						//var_dump($department_schedule_shift);
						$schedule_shifts[$day_epoch][$branch][$department] = Sort::multiSort( $department_schedule_shift, 'start_time', 'last_name' );
					}
				}
			}
		}
		//print_r($schedule_shift_totals);

		$calendar_array = TTDate::getCalendarArray($start_date, $end_date, $current_user_prefs->getStartWeekDay(), FALSE);
		$smarty->assign_by_ref('calendar_array', $calendar_array);

		$hlf = TTnew( 'HolidayListFactory' );
		$holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_ids, $start_date, $end_date );

		$smarty->assign_by_ref('holidays', $holiday_array);
		$smarty->assign_by_ref('schedule_shifts', $schedule_shifts);
		$smarty->assign_by_ref('schedule_shift_totals', $schedule_shift_totals);

		$smarty->assign_by_ref('do', $do );

		break;
}
Debug::writeToLog();
$smarty->display('schedule/ViewScheduleWeek.tpl');
?>