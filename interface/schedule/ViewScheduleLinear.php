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
 * $Id: ViewScheduleLinear.php 7487 2012-08-15 22:35:09Z ipso $
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
BreadCrumb::setCrumb($title, str_replace('ViewScheduleLinear.php', 'ViewSchedule.php', $_SERVER['REQUEST_URI']) );


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
			$end_date = $filter_data['end_date'] = TTDate::getEndDayEpoch($start_date + ($filter_data['show_days']*(86400-3601)));
		} else {
			$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getTime(), $current_user_prefs->getStartWeekDay() );
			$end_date = $filter_data['end_date'] = TTDate::getEndDayEpoch($start_date + ($filter_data['show_days']*(86400-3601)));
		}

		Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);

		$i=0;
		$min_hour = 0;
		$max_hour = 0;

		$sf = TTnew( 'ScheduleFactory' );
		$raw_schedule_shifts = $sf->getScheduleArray(  $filter_data );
		if ( is_array($raw_schedule_shifts) ) {
			foreach( $raw_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
				foreach ( $day_schedule_shifts as $day_schedule_shift ) {
					//$day_schedule_shift['is_owner'] = $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() );
					//$day_schedule_shift['is_child'] = $permission->isChild( $u_obj->getId(), $permission_children_ids );
					$day_schedule_shift['is_owner'] = $permission->isOwner( $day_schedule_shift['user_created_by'], $day_schedule_shift['user_id'] );
					$day_schedule_shift['is_child'] = $permission->isChild( $day_schedule_shift['user_id'], $permission_children_ids );


					$day_schedule_shift['span_day'] = FALSE;
					$day_schedule_shift['span_day_split'] = TRUE;

					//var_dump($day_schedule_shift);
					$tmp_start_hour = TTDate::getHour( $day_schedule_shift['start_time'] );
					$tmp_end_hour = TTDate::getHour( $day_schedule_shift['end_time'] );
					if ( $tmp_end_hour < $tmp_start_hour ) {
						$tmp_end_hour = 24;
					}
					Debug::text(' Schedule: Start Date: '. TTDate::getDate('DATE+TIME', $day_schedule_shift['start_time']) .' End Date: '. TTDate::getDate('DATE+TIME',  $day_schedule_shift['end_time']) , __FILE__, __LINE__, __METHOD__,10);

					if ( $i == 0 OR $tmp_start_hour < $min_hour ) {
						$min_hour = $tmp_start_hour;
						//Always try to keep one hour before the actual min time,
						//otherwise the schedule looks cluttered.
						if ( $min_hour > 0 ) {
							$min_hour--;
						}
						//Debug::text(' aSetting Min Hour: '. $min_hour, __FILE__, __LINE__, __METHOD__,10);
					}

					if ( $i == 0 OR $tmp_end_hour > $max_hour ) {
						$max_hour = $tmp_end_hour;
						Debug::text(' aSetting Max Hour: '. $max_hour, __FILE__, __LINE__, __METHOD__,10);
						if ( $max_hour < 22 ) {
							$max_hour = $max_hour + 2;
						}
						Debug::text(' bSetting Max Hour: '. $max_hour, __FILE__, __LINE__, __METHOD__,10);
					}

					if ( TTDate::getDayOfMonth( $day_schedule_shift['start_time'] ) != TTDate::getDayOfMonth( ($day_schedule_shift['end_time']-1) ) ) { //-1 from end time to handle a 12:00AM end time without going to next day.
						Debug::text(' aSchedule Spans the Day boundary!', __FILE__, __LINE__, __METHOD__,10);
						$day_schedule_shift['span_day'] = TRUE;
						$min_hour = 0;
						$max_hour = 23;
					}

					if ( $day_schedule_shift['span_day'] == TRUE ) {

						//Cut shift into two days.
						$tmp_schedule_shift_day1 = $tmp_schedule_shift_day2 = $day_schedule_shift;
						$tmp_schedule_shift_day1['span_day_split'] = TRUE;
						$tmp_schedule_shift_day1['end_time'] = TTDate::getEndDayEpoch( $day_schedule_shift['start_time'] )+1;
						$tmp_schedule_shift_day2['start_time'] = TTDate::getBeginDayEpoch( $day_schedule_shift['end_time'] );
						$tmp_schedule_shift_day2['span_day_split'] = FALSE;

						$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][$day_schedule_shift['user_id']][] = $tmp_schedule_shift_day1;
						$tmp_schedule_shifts[TTDate::getISODateStamp($tmp_schedule_shift_day2['start_time'])][$day_schedule_shift['branch']][$day_schedule_shift['department']][$day_schedule_shift['user_id']][] = $tmp_schedule_shift_day2;

						Debug::text(' Shift SPans the Day Boundary: First End Date: '. TTDate::getDate('DATE+TIME', $tmp_schedule_shift_day1['end_time'] ) .' Second Start Date: '. TTDate::getDate('DATE+TIME', $tmp_schedule_shift_day2['start_time'] ) , __FILE__, __LINE__, __METHOD__,10);
					} else {
						$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][$day_schedule_shift['user_id']][] = $day_schedule_shift;
					}

					//$schedule_shifts[$day_epoch][] = $day_schedule_shift;
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

					$i++;
				}
			}
		}

		$total_span_hours = 1;
		if ( isset($schedule_shift_totals) ) {
			//Find out how many hours to span
			$total_span_hours = abs($max_hour - $min_hour)+1;
			Debug::text(' Total Hours Span: '. $total_span_hours, __FILE__, __LINE__, __METHOD__,10);

			if ( $min_hour > $max_hour) {
				$tmp_max_hour = $max_hour+24;
				$tmp_min_hour = $min_hour;
			} else {
				$tmp_max_hour = $max_hour;
				$tmp_min_hour = $min_hour;
			}


			//Generate smarty array for table header
			for($i=$tmp_min_hour; $i <= $tmp_max_hour; $i++) {
				$header_hours[] = array('hour' => TTDate::getTimeStamp( "","","", $i ) );
			}
			unset($tmp_min_hour, $tmp_max_hour);
			//var_dump($header_hours);

			//Total up employees/time per day.
			if ( isset($schedule_shift_totals) ) {
				foreach( $schedule_shift_totals as $day_epoch => $total_arr) {
					if ( !isset($total_arr['users']) ) {
						$total_arr['users'] = array();
					}
					$schedule_shift_totals[$day_epoch]['total_users'] = count(array_unique($total_arr['users']));
				}
			}
			//var_dump($tmp_schedule_shifts);
		}

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
			//Remember that we have to handle split shifts here, so its more difficult to sort by last name.
			foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
				foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
					foreach ( $department_schedule_shifts as $department => $department_schedule_shift ) {
						$tmp2_schedule_shifts[$day_epoch][$branch][$department] = Sort::multiSort( $department_schedule_shift, 'start_time' );
					}
				}
			}

			//Sort each department by start time.
			foreach ( $tmp2_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
				foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
					foreach ( $department_schedule_shifts as $department => $user_schedule_shifts ) {
						foreach ( $user_schedule_shifts as $user => $user_schedule_shift ) {
							$schedule_shifts[$day_epoch][$branch][$department][$user] = Sort::multiSort( $user_schedule_shift, 'start_time');
						}
					}
				}
			}
			unset($tmp_schedule_shifts, $tmp2_schedule_shifts);
			$tmp_schedule_shifts = $schedule_shifts;
		}
		//print_r($schedule_shifts);

		if ( isset($tmp_schedule_shifts) ) {
			//Format array so Smarty has an easier time.
			foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
				foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
					foreach ( $department_schedule_shifts as $department => $user_schedule_shifts ) {
						foreach ( $user_schedule_shifts as $user_id => $user_schedule_shifts ) {
							$x=0;
							foreach( $user_schedule_shifts as $user_schedule_shift ) {

								if ( $x == 0 ) {
									$tmp_min_start_date = TTDate::getTimeStamp( date('Y', $user_schedule_shift['start_time']),date('m', $user_schedule_shift['start_time']),date('d', $user_schedule_shift['start_time']), $min_hour );
								} else {
									$tmp_min_start_date = $prev_user_schedule_shift['end_time'];
								}

								$off_duty = ($user_schedule_shift['start_time'] - $tmp_min_start_date) / 900; //15 Min increments
								$on_duty = ($user_schedule_shift['end_time'] - $user_schedule_shift['start_time']) / 900;
								$user_schedule_shift['off_duty'] = $off_duty;
								$user_schedule_shift['on_duty'] = $on_duty;

								$schedule_shifts[$day_epoch][$branch][$department][$user_id][] = $user_schedule_shift;

								$prev_user_schedule_shift = $user_schedule_shift;
								$x++;
							}
						}
					}
				}
			}
		}

		$smarty->assign_by_ref('header_hours', $header_hours);
		$smarty->assign_by_ref('total_span_hours', $total_span_hours);
		$smarty->assign('total_span_columns', ($total_span_hours*4)+1);
		$smarty->assign('column_widths', round( floor(99 / $total_span_hours) / 4 ) );

		$calendar_array = TTDate::getCalendarArray($start_date, $end_date, $current_user_prefs->getStartWeekDay(), FALSE);
		//var_dump($calendar_array);
		$smarty->assign_by_ref('calendar_array', $calendar_array);

		$hlf = TTnew( 'HolidayListFactory' );
		$holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_ids, $start_date, $end_date );
		//var_dump($holiday_array);

		$smarty->assign_by_ref('holidays', $holiday_array);
		$smarty->assign_by_ref('schedule_shifts', $schedule_shifts);
		$smarty->assign_by_ref('schedule_shift_totals', $schedule_shift_totals);

		$smarty->assign_by_ref('do', $do );

		break;
}
Debug::writeToLog();
$smarty->display('schedule/ViewScheduleLinear.tpl');
?>