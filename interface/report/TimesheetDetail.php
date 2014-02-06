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
 * $Revision: 9210 $
 * $Id: TimesheetDetail.php 9210 2013-02-28 00:16:41Z ipso $
 * $Date: 2013-02-27 16:16:41 -0800 (Wed, 27 Feb 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once(Environment::getBasePath() .'classes/misc/arr_multisort.class.php');

$smarty->assign('title', TTi18n::gettext($title = 'TimeSheet Detail Report')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'
												) ) );

if ( isset($filter_data['print_timesheet']) AND $filter_data['print_timesheet'] >= 1 ) {
	if ( !$permission->Check('punch','enabled')
			OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_own') OR $permission->Check('punch','view_child'))
			) {
		$permission->Redirect( FALSE ); //Redirect
	}
} else {
	if ( !$permission->Check('report','enabled')
			OR !$permission->Check('report','view_timesheet_summary') ) {
		$permission->Redirect( FALSE ); //Redirect
	}
}

if ( isset($config_vars['other']['report_maximum_execution_limit']) AND $config_vars['other']['report_maximum_execution_limit'] != '' ) { ini_set( 'max_execution_time', $config_vars['other']['report_maximum_execution_limit'] ); }
if ( isset($config_vars['other']['report_maximum_memory_limit']) AND $config_vars['other']['report_maximum_memory_limit'] != '' ) { ini_set( 'memory_limit', $config_vars['other']['report_maximum_memory_limit'] ); }

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );

$static_columns = array(
/*
//Report shows full name by default.
											'full_name' => 'Full Name',
											'title' => 'Title',
											'province' => 'Province',
											'country' => 'Country',
											'default_branch' => 'Default Branch',
											'default_department' => 'Default Department',
*/
											'-1000-date_stamp' => TTi18n::gettext('Date'),
											'-1050-min_punch_time_stamp' => 'First In Punch',
											'-1060-max_punch_time_stamp' => 'Last Out Punch',
											);

$columns = array(

											'-1070-schedule_working' => TTi18n::gettext('Scheduled Time'),
											'-1080-schedule_absence' => TTi18n::gettext('Scheduled Absence'),
											'-1090-worked_time' => TTi18n::gettext('Worked Time'),
											'-1100-actual_time' => TTi18n::gettext('Actual Time'),
											'-1110-actual_time_diff' => TTi18n::gettext('Actual Time Difference'),
											'-1120-actual_time_diff_wage' => TTi18n::gettext('Actual Time Difference Wage'),
											'-1130-paid_time' => TTi18n::gettext('Paid Time'),
											'-1140-regular_time' => TTi18n::gettext('Regular Time'),
											'-1150-over_time' => TTi18n::gettext('Total Over Time'),
											'-1160-absence_time' => TTi18n::gettext('Total Absence Time'),
											);

$columns = Misc::prependArray( $static_columns, $columns);

//Get all Overtime policies.
$otplf = TTnew( 'OverTimePolicyListFactory' );
$otplf->getByCompanyId($current_company->getId());
if ( $otplf->getRecordCount() > 0 ) {
	foreach ($otplf as $otp_obj ) {
		$otp_columns['over_time_policy-'.$otp_obj->getId()] = $otp_obj->getName();
	}

	$columns = array_merge( $columns, $otp_columns);
}

//Get all Premium policies.
$pplf = TTnew( 'PremiumPolicyListFactory' );
$pplf->getByCompanyId($current_company->getId());
if ( $pplf->getRecordCount() > 0 ) {
	foreach ($pplf as $pp_obj ) {
		$pp_columns['premium_policy-'.$pp_obj->getId()] = $pp_obj->getName();
	}

	$columns = array_merge( $columns, $pp_columns);
}


//Get all Absence Policies.
$aplf = TTnew( 'AbsencePolicyListFactory' );
$aplf->getByCompanyId($current_company->getId());
if ( $aplf->getRecordCount() > 0 ) {
	foreach ($aplf as $ap_obj ) {
		$ap_columns['absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
	}

	$columns = array_merge( $columns, $ap_columns);
}


//Get all pay periods
$pplf = TTnew( 'PayPeriodListFactory' );
$pplf->getByCompanyId( $current_company->getId() );
if ( $pplf->getRecordCount() > 0 ) {
	$pp=0;
	foreach ($pplf as $pay_period_obj) {
		$pay_period_ids[] = $pay_period_obj->getId();
		$pay_period_end_dates[$pay_period_obj->getId()] = $pay_period_obj->getEndDate();

		if ( $pp == 0 ) {
			$default_start_date = $pay_period_obj->getStartDate();
			$default_end_date = $pay_period_obj->getEndDate();
		}
		$pp++;
	}

	$pplf = TTnew( 'PayPeriodListFactory' );
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'), FALSE );
}

if ( isset($filter_data['start_date']) ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
}

if ( isset($filter_data['end_date']) ) {
	$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array() );

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
$wage_permission_children_ids = array();
if ( $permission->Check('punch','view') == FALSE ) {
	$hlf = TTnew( 'HierarchyListFactory' );
	$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('punch','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('punch','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

//Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
if ( $permission->Check('wage','view') == FALSE ) {
	if ( $permission->Check('wage','view_child') == FALSE ) {
		$wage_permission_children_ids = array();
	}
	if ( $permission->Check('wage','view_own') ) {
		$wage_permission_children_ids[] = $current_user->getId();
	}

	$wage_filter_data['permission_children_ids'] = $wage_permission_children_ids;
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'export':
	case 'display_report':
	case 'display_timesheet':
	case 'display_detailed_timesheet':
		//Debug::setVerbosity(11);		
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		if ( Misc::isSystemLoadValid() == FALSE ) {
			echo TTi18n::getText('Please try again later...');
			exit;
		}
		
		//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);

		//Determine if this is a regular employee trying to print their own timesheet.
		//from the MyTimeSheet page.
		if ( isset($filter_data['print_timesheet']) AND $filter_data['print_timesheet'] >= 1 ) {
			//If they don't have permissions to see more then just their own punches, force
			//to currently logged in user.
			if ( !isset($filter_data['user_id']) OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_child') ) ) {
				$filter_data['user_id'] = $current_user->getId();
			}

			//Force as many settings as possible so they can't manually override them.
			$action = 'display_timesheet';
			if ( $filter_data['print_timesheet'] == 2 ) {
				$action = 'display_detailed_timesheet';
			}
			$filter_data = array(
									'permission_children_ids' => array( (int)$filter_data['user_id'] ),
									'pay_period_ids' => array( (int)$filter_data['pay_period_ids'] ),
									'date_type' => 'pay_period_ids',
									'primary_sort' => '-1000-date_stamp',
									'secondary_sort' => NULL,
									'primary_sort_dir' => 1,
									'secondary_sort_dir' => NULL,
									'column_ids' => $static_columns
								);
		}

/*
	protected $status_options = array(
										10 => 'System',
										20 => 'Worked',
										30 => 'Absence'
									);

	protected $type_options = array(
										10 => 'Total',
										20 => 'Regular',
										30 => 'Overtime'
									);
*/

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		Debug::Text('User Record Count: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' ) {
				unset($filter_data['start_date']);
				unset($filter_data['end_date']);
			} else {
				unset($filter_data['pay_period_ids']);
			}

			foreach( $ulf as $u_obj ) {
				$filter_data['user_id'][] = $u_obj->getId();
			}

			if ( isset($filter_data['pay_period_ids']) ) {
				//Trim sort prefix from selected pay periods.
				$tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
				$filter_data['pay_period_ids'] = array();
				foreach( $tmp_filter_pay_period_ids as $key => $filter_pay_period_id) {
					$filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
				}
				unset($key, $tmp_filter_pay_period_ids, $filter_pay_period_id);
			}

			//Get greatest end date of the selected ones.
			if ( isset($filter_data['pay_period_ids']) AND count($filter_data['pay_period_ids']) > 0 ) {
				if ( in_array('-1', $filter_data['pay_period_ids']) ) {
					$end_date = time();
				} else {
					$i=0;
					foreach ( $filter_data['pay_period_ids'] as $tmp_pay_period_id ) {
						$tmp_pay_period_id = Misc::trimSortPrefix($tmp_pay_period_id);
						if ( $i == 0 AND isset($pay_period_end_dates[$tmp_pay_period_id]) ) {
							$end_date = $pay_period_end_dates[$tmp_pay_period_id];
						} elseif ( isset($pay_period_end_dates[$tmp_pay_period_id]) AND $pay_period_end_dates[$tmp_pay_period_id] > $end_date ) {
							$end_date = $pay_period_end_dates[$tmp_pay_period_id];
						} else {
							$end_date = time();
						}

						$i++;
					}
					unset($tmp_pay_period_id, $i);
				}
			} else {
				$end_date = ( isset($filter_data['end_date']) ) ? $filter_data['end_date'] : time();
			}

            //Make sure we account for wage permissions.
            if ( $permission->Check('wage','view') == TRUE ) {
                $wage_filter_data['permission_children_ids'] = $filter_data['user_id'];
            }
			$uwlf = TTnew( 'UserWageListFactory' );
			$uwlf->getLastWageByUserIdAndDate( $wage_filter_data['permission_children_ids'], $end_date );
			if ( $uwlf->getRecordCount() > 0 ) {
				foreach($uwlf as $uw_obj) {
					$user_wage[$uw_obj->getUser()] = $uw_obj->getBaseCurrencyHourlyRate( $uw_obj->getHourlyRate() );
				}
			}
			unset($end_date);
			//var_dump($user_wage);

			$udtlf = TTnew( 'UserDateTotalListFactory' );
			if ( isset($filter_data['user_id']) ) {
				$udtlf->getDayReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			}

			$slf = TTnew( 'ScheduleListFactory' );
			if ( isset($filter_data['user_id']) ) {
				$slf->getDayReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			}
			if ( $slf->getRecordCount() > 0 ) {
				foreach($slf as $s_obj) {
					$user_id = $s_obj->getColumn('user_id');
					$status_id = $s_obj->getColumn('status_id');
					$status = strtolower( Option::getByKey($status_id, $s_obj->getOptions('status') ) );
					$pay_period_id = $s_obj->getColumn('pay_period_id');
					$date_stamp = TTDate::strtotime( $s_obj->getColumn('date_stamp') );

					$schedule_rows[$pay_period_id][$user_id][$date_stamp][$status] = $s_obj->getColumn('total_time');

					unset($user_id, $status_id, $status, $pay_period_id, $date_stamp);
				}
			}

			Debug::Text('Record Count: SLF: '. $slf->getRecordCount() .' UDTLF: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			foreach ($udtlf as $udt_obj ) {
				$user_id = $udt_obj->getColumn('id');
				$pay_period_id = $udt_obj->getColumn('pay_period_id');
				$date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );

				$status_id = $udt_obj->getColumn('status_id');
				$type_id = $udt_obj->getColumn('type_id');

				$category = 0;
				$policy_id = 0;

				if ( $status_id == 10 AND $type_id == 10 ) {
					$column = 'paid_time';
					$category = $column;
				} elseif ($status_id == 10 AND $type_id == 20) {
					$column = 'regular_time';
					$category = $column;
				} elseif ($status_id == 10 AND $type_id == 30) {
					$column = 'over_time_policy-'. $udt_obj->getColumn('over_time_policy_id');
					$category = 'over_time_policy';
					$policy_id = $udt_obj->getColumn('over_time_policy_id');
				} elseif ($status_id == 10 AND $type_id == 40) {
					$column = 'premium_policy-'. $udt_obj->getColumn('premium_policy_id');
					$category = 'premium_policy';
					$policy_id = $udt_obj->getColumn('premium_policy_id');
				} elseif ($status_id == 30 AND $type_id == 10) {
					$column = 'absence_policy-'. $udt_obj->getColumn('absence_policy_id');
					$category = 'absence_policy';
					$policy_id = $udt_obj->getColumn('absence_policy_id');
				} elseif ( ($status_id == 20 AND $type_id == 10 ) OR ($status_id == 10 AND $type_id == 100 ) ) {
					$column = 'worked_time';
					$category = $column;
				} else {
					$column = NULL;
				}

				//Debug::Text('Column: '. $column .' Status ID: '. $status_id .' Type ID: '. $type_id .' Total Time: '. $udt_obj->getColumn('total_time'), __FILE__, __LINE__, __METHOD__,10);
				if ( $column == 'worked_time' ) {
					//Handle actual time diff/wage here.
					if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column]) ) {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] += (int)$udt_obj->getColumn('total_time');
					} else {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = (int)$udt_obj->getColumn('total_time');
					}
					if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time']) ) {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] += $udt_obj->getColumn('actual_total_time');
					} else {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] = $udt_obj->getColumn('actual_total_time');
					}

					$actual_time_diff = bcsub($udt_obj->getColumn('actual_total_time'), $udt_obj->getColumn('total_time') );
					if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff']) ) {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] += $actual_time_diff;
					} else {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] = $actual_time_diff;
					}

					if ( isset($user_wage[$user_id]) ) {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff_wage'] = Misc::MoneyFormat( bcmul( TTDate::getHours($actual_time_diff), $user_wage[$user_id]), FALSE );
					} else {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff_wage'] = Misc::MoneyFormat( 0, FALSE );
					}
					unset($actual_time_diff);
				} elseif ( $column != NULL ) {
					if ( $udt_obj->getColumn('total_time') > 0 ) {

						//Total up all absence time.
						if ($status_id == 30 AND $type_id == 10) {
							if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time']) ) {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] += $udt_obj->getColumn('total_time');
							} else {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] = $udt_obj->getColumn('total_time');
							}
						}

						if ($status_id == 10 AND $type_id == 30) {
							if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time']) ) {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] += $udt_obj->getColumn('total_time');
							} else {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] = $udt_obj->getColumn('total_time');
							}
						}

						if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column]) ) {
							$tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] += $udt_obj->getColumn('total_time');
						} else {
							$tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = $udt_obj->getColumn('total_time');
						}

						//This messes with the ArraySum'ing, so only include it when we're generating a PDF timesheet.
						if ( $action == 'display_timesheet' OR $action == 'display_detailed_timesheet' ) {
							if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id]) ) {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] += $udt_obj->getColumn('total_time');
							} else {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] = $udt_obj->getColumn('total_time');
							}
						}
					}
				}

				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['working']) ) {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_working'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['working'];
				} else {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_working'] = NULL;
				}

				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence']) ) {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_absence'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence'];
				} else {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_absence'] = NULL;
				}

				$tmp_rows[$pay_period_id][$user_id][$date_stamp]['min_punch_time_stamp'] = TTDate::strtotime( $udt_obj->getColumn('min_punch_time_stamp') );
				$tmp_rows[$pay_period_id][$user_id][$date_stamp]['max_punch_time_stamp'] = TTDate::strtotime( $udt_obj->getColumn('max_punch_time_stamp') );

			}

			//Get all punches
			if ( $action == 'display_detailed_timesheet'  ) {
				$plf = TTnew( 'PunchListFactory' );
				$plf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data);
				if ( $plf->getRecordCount() > 0 ) {
					foreach( $plf as $p_obj ) {
						$punch_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][TTDate::strtotime( $p_obj->getColumn('date_stamp') )][$p_obj->getPunchControlID()][$p_obj->getStatus()] = array( 'status_id' => $p_obj->getStatus(), 'type_id' => $p_obj->getType(), 'type_code' => $p_obj->getTypeCode(), 'time_stamp' => $p_obj->getTimeStamp() );
					}
				}
				unset($plf,$p_obj);
			}

			$ulf = TTnew( 'UserListFactory' );

			$utlf = TTnew( 'UserTitleListFactory' );
			$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

			$blf = TTnew( 'BranchListFactory' );
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = TTnew( 'DepartmentListFactory' );
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			$uglf = TTnew( 'UserGroupListFactory' );
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

			//Get verified timesheets
			//Ignore if more then one pay period is selected
			$verified_time_sheets = NULL;
			if ( isset($filter_data['pay_period_ids']) AND count($filter_data['pay_period_ids']) > 0 ) {
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$pptsvlf->getByPayPeriodIdAndCompanyId( $filter_data['pay_period_ids'][0], $current_company->getId() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					foreach( $pptsvlf as $pptsv_obj ) {
						$verified_time_sheets[$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = array(
																										 'status_id' => $pptsv_obj->getStatus(),
																										 'created_date' => $pptsv_obj->getCreatedDate(),
																										);
					}
				}
			}

			if ( isset($tmp_rows) ) {
				$i=0;
				foreach($tmp_rows as $pay_period_id => $data_a ) {
					foreach($data_a as $user_id => $data_b ) {
						$user_obj = $ulf->getById( $user_id )->getCurrent();

						if ( isset($pay_period_options[$pay_period_id]) ) {
							$rows[$i]['pay_period'] = $pay_period_options[$pay_period_id];
						} else {
							$rows[$i]['pay_period'] = 'N/A';
						}
						$rows[$i]['pay_period_id'] = $pay_period_id;
						$rows[$i]['user_id'] = $user_id;
						$rows[$i]['first_name'] = $user_obj->getFirstName();
						$rows[$i]['last_name'] = $user_obj->getLastName();
						$rows[$i]['full_name'] = $user_obj->getFullName(TRUE);
						$rows[$i]['employee_number'] = $user_obj->getEmployeeNumber();
						$rows[$i]['province'] = $user_obj->getProvince();
						$rows[$i]['country'] = $user_obj->getCountry();

						$rows[$i]['group'] = Option::getByKey($user_obj->getGroup(), $group_options, NULL );
						$rows[$i]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
						$rows[$i]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
						$rows[$i]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );

						$rows[$i]['verified_time_sheet_date'] = FALSE;
						if ( $verified_time_sheets !== NULL AND isset($verified_time_sheets[$user_id][$pay_period_id]) ) {
							if ( $verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 50 ) {
								$rows[$i]['verified_time_sheet'] = TTi18n::gettext('Yes');
								$rows[$i]['verified_time_sheet_date'] = $verified_time_sheets[$user_id][$pay_period_id]['created_date'];
							} elseif ( $verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 30 OR $verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 45 ) {
								$rows[$i]['verified_time_sheet'] = TTi18n::gettext('Pending');
							} else {
								$rows[$i]['verified_time_sheet'] = TTi18n::gettext('Declined');
							}
						} else {
							$rows[$i]['verified_time_sheet'] = TTi18n::gettext('No');
						}

						$x=0;
						foreach($data_b as $date_stamp => $data_c ) {
							$sub_rows[$x]['date_stamp'] = $date_stamp;

							foreach($data_c as $column => $total_time) {
								$sub_rows[$x][$column] = $total_time;
							}
							$x++;
						}

						if ( isset($sub_rows) ) {
							foreach($sub_rows as $sub_row) {
								$tmp_sub_rows[] = $sub_row;
							}

							$sub_rows = Sort::Multisort($tmp_sub_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

							if ( $action != 'display_timesheet' AND $action != 'display_detailed_timesheet') {
								$total_sub_row = Misc::ArrayAssocSum($sub_rows, NULL, 2);

								$last_sub_row = count($sub_rows);
								$sub_rows[$last_sub_row] = $total_sub_row;
								//$static_columns['epoch'] = 'epoch';
								foreach ($static_columns as $static_column_key => $static_column_val) {
									$sub_rows[$last_sub_row][Misc::trimSortPrefix($static_column_key)] = NULL;
								}
								unset($static_column_key, $static_column_val);
							}

							//Convert units
							$tmp_sub_rows = $sub_rows;
							unset($sub_rows);


							$trimmed_static_columns = array_keys( Misc::trimSortPrefix($static_columns) );
							foreach($tmp_sub_rows as $sub_row ) {
								foreach($sub_row as $column => $column_data) {
									if ( $action != 'display_timesheet' AND $action != 'display_detailed_timesheet') {
										if ( $column == 'date_stamp' ) {
											$column_data = TTDate::getDate('DATE', $column_data);
										} elseif ( $column == 'min_punch_time_stamp' OR $column == 'max_punch_time_stamp' ) {
											$column_data = TTDate::getDate('TIME', $column_data);
										} elseif ( !strstr($column, 'wage') AND !in_array( $column, $trimmed_static_columns ) ) {
											$column_data = TTDate::getTimeUnit( $column_data );
										}
									}
									$sub_row_columns[$column] = $column_data;

									unset($column, $column_data);
								}

								$sub_rows[] = $sub_row_columns;
								unset($sub_row_columns);

								//$prev_row = $sub_row;
							}

							//var_dump($rows);
							foreach( $filter_data['column_ids'] as $column_key ) {
								if ( isset($columns[$column_key]) ) {
									$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
								}
							}
						}

						$rows[$i]['data'] = $sub_rows;
						unset($sub_rows, $tmp_sub_rows);

						$i++;
					}
				}
			}
			//print_r($rows);
			unset($tmp_rows);
		}

		if ( Misc::isSystemLoadValid() == FALSE ) {
			echo TTi18n::getText('Please try again later...');
			exit;
		}
		
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		if ( $action == 'display_timesheet' ) {
			if ( isset($rows) ) {
				$pdf_created_date = time();

				//Page width: 205mm
				$pdf = new TTPDF('P','mm','Letter');
				$pdf->setMargins(10,5);
				$pdf->SetAutoPageBreak(FALSE);
				$pdf->SetFont( TTi18n::getPDFDefaultFont(),'',10);

				$border = 0;

				//Create PDF TimeSheet for each employee.
				foreach( $rows as $user_data ) {
					$pdf->AddPage();

					$adjust_x = 10;
					$adjust_y = 10;

					//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

					$pdf->SetFont('','B',32);
					$pdf->Cell(200,15, TTi18n::gettext('Employee TimeSheet') , $border, 0, 'C');
					$pdf->Ln();
					$pdf->SetFont('','B',12);
					$pdf->Cell(200,5, $current_company->getName() , $border, 0, 'C');
					$pdf->Ln(10);

					$pdf->Rect( $pdf->getX(), $pdf->getY()-2, 200, 19 );

					$pdf->SetFont('','',12);
					$pdf->Cell(30,5, TTi18n::gettext('Employee:') , $border, 0, 'R');
					$pdf->SetFont('','B',12);
					$pdf->Cell(70,5, $user_data['first_name'] .' '. $user_data['last_name'] .' (#'. $user_data['employee_number'] .')', $border, 0, 'L');

					$pdf->SetFont('','',12);
					$pdf->Cell(40,5, TTi18n::gettext('Pay Period:') , $border, 0, 'R');
					$pdf->SetFont('','B',12);
					$pdf->Cell(60,5, $user_data['pay_period'], $border, 0, 'L');
					$pdf->Ln();

					$pdf->SetFont('','',12);
					$pdf->Cell(30,5, TTi18n::gettext('Title:') , $border, 0, 'R');
					$pdf->Cell(70,5, $user_data['title'], $border, 0, 'L');
					$pdf->Cell(40,5, TTi18n::gettext('Branch:') , $border, 0, 'R');
					$pdf->Cell(60,5, $user_data['default_branch'], $border, 0, 'L');
					$pdf->Ln();

					$pdf->Cell(30,5, TTi18n::gettext('Group:') , $border, 0, 'R');
					$pdf->Cell(70,5, $user_data['group'], $border, 0, 'L');
					$pdf->Cell(40,5, TTi18n::gettext('Department:') , $border, 0, 'R');
					$pdf->Cell(60,5, $user_data['default_department'], $border, 0, 'L');
					$pdf->Ln(5);

					$pdf->SetFont('','',10);
					//Start displaying dates/times here. Start with header.
					$column_widths = array(
										'line' => 5,
										'date_stamp' => 20,
										'dow' => 10,
										'min_punch_time_stamp' => 25,
										'max_punch_time_stamp' => 25,
										'worked_time' => 25,
										'regular_time' => 25,
										'over_time' => 20,
										'paid_time' => 20,
										'absence_time' => 25,
										);


					if ( isset($user_data['data']) AND is_array($user_data['data']) ) {
						if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' )  {
							//Fill in any missing days, only if they select by pay period.
							$pplf = TTnew( 'PayPeriodListFactory' );
							$pplf->getById( $user_data['pay_period_id'] );
							if ( $pplf->getRecordCount() == 1 ) {
								$pp_obj = $pplf->getCurrent();

								for( $d=TTDate::getBeginDayEpoch($pp_obj->getStartDate()); $d <= $pp_obj->getEndDate(); $d+=86400) {
									if ( Misc::inArrayByKeyAndValue($user_data['data'], 'date_stamp', TTDate::getBeginDayEpoch($d) ) == FALSE ) {
										$user_data['data'][] = array(
																'date_stamp' => TTDate::getBeginDayEpoch($d),
																'min_punch_time' => NULL,
																'max_punch_time' => NULL,
																'worked_time' => NULL,
																'regular_time' => NULL,
																'over_time' => NULL,
																'paid_time' => NULL,
																'absence_time' => NULL
															);

									}
								}
							}
						}
						$user_data['data'] = Sort::Multisort( $user_data['data'], 'date_stamp', NULL, 'ASC' );

						$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
						$totals = array();
						$totals = Misc::preSetArrayValues( $totals, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

						$i=1;
						$x=1;
						$y=1;
						$max_i = count($user_data['data']);
						foreach( $user_data['data'] as $data) {
							//Show Header
							if ( $i == 1 OR $x == 1 ) {
								if ( $x == 1 ) {
									$pdf->Ln();
								}

								$line_h = 6;
								$cell_h_min = $cell_h_max = $line_h * 2;

								$pdf->SetFont('','B',9);
								$pdf->setFillColor(220,220,220);
								$pdf->MultiCell( $column_widths['line'], $line_h, '#' , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['date_stamp'], $line_h, TTi18n::gettext('Date') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['dow'], $line_h, TTi18n::gettext('DoW') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['min_punch_time_stamp'], $line_h, TTi18n::gettext('First In') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['max_punch_time_stamp'], $line_h, TTi18n::gettext('Last Out') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['worked_time'], $line_h, TTi18n::gettext('Worked Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['regular_time'], $line_h, TTi18n::gettext('Regular Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['over_time'], $line_h, TTi18n::gettext('Over Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['paid_time'], $line_h, TTi18n::gettext('Paid Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['absence_time'], $line_h, TTi18n::gettext('Absence Time') , 1, 'C', 1, 0);
								$pdf->Ln();
							}

							$data = Misc::preSetArrayValues( $data, array('date_stamp', 'min_punch_time_stamp', 'max_punch_time_stamp', 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), '--' );

							if ( $x % 2 == 0 ) {
								$pdf->setFillColor(220,220,220);
							} else {
								$pdf->setFillColor(255,255,255);
							}

							if ( $data['date_stamp'] !== '' ) {
								$pdf->SetFont('','',9);
								$pdf->Cell( $column_widths['line'], 6, $x , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['date_stamp'], 6, TTDate::getDate('DATE', $data['date_stamp'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['dow'], 6, date('D', $data['date_stamp']) , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['min_punch_time_stamp'], 6, TTDate::getDate('TIME', $data['min_punch_time_stamp'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['max_punch_time_stamp'], 6, TTDate::getDate('TIME', $data['max_punch_time_stamp'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $data['worked_time'] ) , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $data['regular_time'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $data['over_time'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $data['paid_time'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $data['absence_time'] ), 1, 0, 'C', 1);
								$pdf->Ln();
							}

							$totals['worked_time'] += $data['worked_time'];
							$totals['paid_time'] += $data['paid_time'];
							$totals['absence_time'] += $data['absence_time'];
							$totals['regular_time'] += $data['regular_time'];
							$totals['over_time'] += $data['over_time'];

							$week_totals['worked_time'] += $data['worked_time'];
							$week_totals['paid_time'] += $data['paid_time'];
							$week_totals['absence_time'] += $data['absence_time'];
							$week_totals['regular_time'] += $data['regular_time'];
							$week_totals['over_time'] += $data['over_time'];

							if ( $x % 7 == 0 OR $i == $max_i ) {
								//Show Week Total.
								$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['min_punch_time_stamp']+$column_widths['max_punch_time_stamp'];
								$pdf->SetFont('','B',9);
								$pdf->Cell( $total_cell_width, 6, TTi18n::gettext('Week Total:').' ', 0, 0, 'R', 0);
								$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $week_totals['worked_time'] ) , 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $week_totals['regular_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $week_totals['over_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $week_totals['paid_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $week_totals['absence_time'] ), 0, 0, 'C', 0);
								$pdf->Ln(2);

								unset($week_totals);
								$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

								$x=0;
								$y++;

								//Force page break every 3 weeks.
								if ( $y == 4 AND $i !== $max_i ) {
									$pdf->AddPage();
								}
							}


							$i++;
							$x++;
						}
						unset($data);
					}

					if ( isset($totals) AND is_array($totals) ) {
						//Display overall totals.
						$pdf->Ln(3);
						$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['min_punch_time_stamp'];
						$pdf->SetFont('','B',9);
						$pdf->Cell( $total_cell_width, 6, '' , 0, 0, 'R', 0);
						$pdf->Cell( $column_widths['max_punch_time_stamp'], 6, TTi18n::gettext('Overall Total:').' ', 'T', 0, 'R', 0);
						$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $totals['worked_time'] ) , 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $totals['regular_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $totals['over_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $totals['paid_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $totals['absence_time'] ), 'T', 0, 'C', 0);
						$pdf->Ln();
						unset($totals);
					}

					$pdf->SetFont('','',9);
					$pdf->setFillColor(255,255,255);
					$pdf->Ln();

					//Signature lines
					$pdf->MultiCell(200,5, TTi18n::gettext('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '.TTi18n::gettext('worked during the designated period.'), $border, 'L');
					$pdf->Ln(5);

					$border = 0;
					$pdf->Cell(40,5, TTi18n::gettext('Employee Signature:'), $border, 0, 'L');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');
					$pdf->Cell(40,5, TTi18n::gettext('Supervisor Signature:'), $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(40,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, $user_data['first_name'] .' '. $user_data['last_name'] , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, TTi18n::gettext('(print name)'), $border, 0, 'C');

					if ( $user_data['verified_time_sheet_date'] != FALSE ) {
						$pdf->Ln();
						$pdf->SetFont('','B',10);
						$pdf->Cell(200,5, TTi18n::gettext('TimeSheet electronically signed by').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '. TTi18n::gettext('on') .' '. TTDate::getDate('DATE+TIME', $user_data['verified_time_sheet_date'] ), $border, 0, 'C');
						$pdf->SetFont('','',10);
					}


					//Add generated date/time at the bottom.
					$pdf->SetFont('','I',8);
					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(245, $adjust_y) );
					$pdf->Cell(200,5, TTi18n::gettext('Generated:') .' '. TTDate::getDate('DATE+TIME', $pdf_created_date ), $border, 0, 'C');
				}

				$output = $pdf->Output('','S');
			}

			Debug::Text('Output PDF...', __FILE__, __LINE__, __METHOD__,10);
			if ( isset($output) AND $output !== FALSE AND Debug::getVerbosity() < 11 ) {
				Misc::FileDownloadHeader('timesheet.pdf', 'application/pdf', strlen($output));
				echo $output;
				Debug::writeToLog();
				exit;
			} else {
				//Debug::Display();
				echo TTi18n::gettext('ERROR: Employee TimeSheet(s) not available!') . "<br>\n";
				Debug::writeToLog();
				exit;
			}

		} elseif ( $action == 'display_detailed_timesheet' ) {
			$output = FALSE;
			if ( isset($rows) ) {
				$pdf_created_date = time();

				//Page width: 205mm
				$pdf = new TTPDF('P','mm','Letter');
				$pdf->setMargins(10,5);
				$pdf->SetAutoPageBreak(TRUE, 10);
				$pdf->SetFont( TTi18n::getPDFDefaultFont(),'',10);

				$border = 0;

				//Create PDF TimeSheet for each employee.
				foreach( $rows as $user_data ) {
					$pdf->AddPage();

					$adjust_x = 10;
					$adjust_y = 10;

					//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

					$pdf->SetFont('','B',22);
					$pdf->Cell(200,8, TTi18n::gettext('Detailed Employee TimeSheet') , $border, 0, 'C');
					$pdf->Ln();
					$pdf->SetFont('','B',12);
					$pdf->Cell(200,5, $current_company->getName() , $border, 0, 'C');
					$pdf->Ln(8);

					$pdf->Rect( $pdf->getX(), $pdf->getY()-1, 200, 14 );

					$pdf->SetFont('','',10);
					$pdf->Cell(30,4, TTi18n::gettext('Employee:') , $border, 0, 'R');
					$pdf->SetFont('','B',10);
					$pdf->Cell(70,4, $user_data['first_name'] .' '. $user_data['last_name'] .' (#'. $user_data['employee_number'] .')', $border, 0, 'L');

					$pdf->SetFont('','',10);
					$pdf->Cell(40,4, TTi18n::gettext('Pay Period:') , $border, 0, 'R');
					$pdf->SetFont('','B',10);
					$pdf->Cell(60,4, $user_data['pay_period'], $border, 0, 'L');
					$pdf->Ln();

					$pdf->SetFont('','',10);
					$pdf->Cell(30,4, TTi18n::gettext('Title:') , $border, 0, 'R');
					$pdf->Cell(70,4, $user_data['title'], $border, 0, 'L');
					$pdf->Cell(40,4, TTi18n::gettext('Branch:') , $border, 0, 'R');
					$pdf->Cell(60,4, $user_data['default_branch'], $border, 0, 'L');
					$pdf->Ln();

					$pdf->Cell(30,4, TTi18n::gettext('Group:') , $border, 0, 'R');
					$pdf->Cell(70,4, $user_data['group'], $border, 0, 'L');
					$pdf->Cell(40,4, TTi18n::gettext('Department:') , $border, 0, 'R');
					$pdf->Cell(60,4, $user_data['default_department'], $border, 0, 'L');
					$pdf->Ln(3);

					$pdf->SetFont('','',10);
					//Start displaying dates/times here. Start with header.
					$column_widths = array(
										'line' => 5,
										'date_stamp' => 20,
										'dow' => 10,
										'in_punch_time_stamp' => 20,
										'out_punch_time_stamp' => 20,
										'worked_time' => 15,
										'paid_time' => 15,
										'regular_time' => 15,
										'over_time' => 37,
										'absence_time' => 43,
										);


					if ( isset($user_data['data']) AND is_array($user_data['data']) ) {
						if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' )  {
							//Fill in any missing days, only if they select by pay period.
							$pplf = TTnew( 'PayPeriodListFactory' );
							$pplf->getById( $user_data['pay_period_id'] );
							if ( $pplf->getRecordCount() == 1 ) {
								$pp_obj = $pplf->getCurrent();

								for( $d=TTDate::getBeginDayEpoch($pp_obj->getStartDate()); $d <= $pp_obj->getEndDate(); $d+=86400) {
									if ( Misc::inArrayByKeyAndValue($user_data['data'], 'date_stamp', TTDate::getBeginDayEpoch($d) ) == FALSE ) {
										$user_data['data'][] = array(
																'date_stamp' => TTDate::getBeginDayEpoch($d),
																'in_punch_time' => NULL,
																'out_punch_time' => NULL,
																'worked_time' => NULL,
																'regular_time' => NULL,
																'over_time' => NULL,
																'paid_time' => NULL,
																'absence_time' => NULL
															);

									}
								}
							}
						}
						$user_data['data'] = Sort::Multisort( $user_data['data'], 'date_stamp', NULL, 'ASC' );

						$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
						$totals = array();
						$totals = Misc::preSetArrayValues( $totals, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

						$i=1;
						$x=1;
						$y=1;
						$max_i = count($user_data['data']);
						foreach( $user_data['data'] as $data) {
							//Show Header
							if ( $i == 1 OR $x == 1 ) {
								if ( $x == 1 ) {
									$pdf->Ln();
								}

								$line_h = 5;
								$cell_h_min = $cell_h_max = $line_h * 2;

								$pdf->SetFont('','B',9);
								$pdf->setFillColor(220,220,220);
								$pdf->MultiCell( $column_widths['line'], $line_h, '#' , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['date_stamp'], $line_h, TTi18n::gettext('Date') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['dow'], $line_h, TTi18n::gettext('DoW') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['in_punch_time_stamp'], $line_h, TTi18n::gettext('In') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['out_punch_time_stamp'], $line_h, TTi18n::gettext('Out') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['worked_time'], $line_h, TTi18n::gettext('Worked') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['paid_time'], $line_h, TTi18n::gettext('Paid') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['regular_time'], $line_h, TTi18n::gettext('Regular') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['over_time'], $line_h, TTi18n::gettext('Over Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['absence_time'], $line_h, TTi18n::gettext('Absence Time') , 1, 'C', 1, 0);
								$pdf->Ln();
							}

							$data = Misc::preSetArrayValues( $data, array('date_stamp', 'in_punch_time_stamp', 'out_punch_time_stamp', 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), '--' );

							if ( $x % 2 == 0 ) {
								$pdf->setFillColor(220,220,220);
							} else {
								$pdf->setFillColor(255,255,255);
							}

							if ( $data['date_stamp'] !== '' ) {
								$default_line_h = 4;
								$line_h = $default_line_h;

								$total_rows_arr = array();

								//Find out how many punches fall on this day, so we can change row height to fit.
								$total_punch_rows = 1;
								if ( isset($punch_rows[$user_data['pay_period_id']][$user_data['user_id']][$data['date_stamp']]) ) {
									$day_punch_data = $punch_rows[$user_data['pay_period_id']][$user_data['user_id']][$data['date_stamp']];
									$total_punch_rows = count($day_punch_data);
								}
								$total_rows_arr[] = $total_punch_rows;

								$total_over_time_rows = 1;
								if ( $data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy']) ) {
									$total_over_time_rows = count($data['categorized_time']['over_time_policy']);
								}
								$total_rows_arr[] = $total_over_time_rows;

								$total_absence_rows = 1;
								if ( $data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy']) ) {
									$total_absence_rows = count($data['categorized_time']['absence_policy']);
								}
								$total_rows_arr[] = $total_absence_rows;

								rsort($total_rows_arr);
								$max_rows = $total_rows_arr[0];
								$line_h = $default_line_h*$max_rows;

								$pdf->SetFont('','',9);
								$pdf->Cell( $column_widths['line'], $line_h, $x , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['date_stamp'], $line_h, TTDate::getDate('DATE', $data['date_stamp'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['dow'], $line_h, date('D', $data['date_stamp']) , 1, 0, 'C', 1);

								$pre_punch_x = $pdf->getX();
								$pre_punch_y = $pdf->getY();

								//Print Punches
								if ( isset($day_punch_data) ) {
									$pdf->SetFont('','',8);

									$n=0;
									foreach( $day_punch_data as $punch_control_id => $punch_data ) {
										if ( !isset($punch_data[10]['time_stamp']) ) {
											$punch_data[10]['time_stamp'] = NULL;
											$punch_data[10]['type_code'] = NULL;
										}
										if ( !isset($punch_data[20]['time_stamp']) ) {
											$punch_data[20]['time_stamp'] = NULL;
											$punch_data[20]['type_code'] = NULL;
										}

										if ( $n > 0 ) {
											$pdf->setXY( $pre_punch_x, $punch_y+$default_line_h);
										}

										$pdf->Cell( $column_widths['in_punch_time_stamp'], $line_h/$total_punch_rows, TTDate::getDate('TIME', $punch_data[10]['time_stamp'] ) .' '. $punch_data[10]['type_code'], 1, 0, 'C', 1);
										$pdf->Cell( $column_widths['out_punch_time_stamp'], $line_h/$total_punch_rows, TTDate::getDate('TIME', $punch_data[20]['time_stamp'] ) .' '. $punch_data[20]['type_code'], 1, 0, 'C', 1);

										$punch_x = $pdf->getX();
										$punch_y = $pdf->getY();

										$n++;
									}

									$pdf->setXY( $punch_x, $pre_punch_y);

									$pdf->SetFont('','',9);
								} else {
									$pdf->Cell( $column_widths['in_punch_time_stamp'], $line_h, '', 1, 0, 'C', 1);
									$pdf->Cell( $column_widths['out_punch_time_stamp'], $line_h, '', 1, 0, 'C', 1);
								}

								$pdf->Cell( $column_widths['worked_time'], $line_h, TTDate::getTimeUnit( $data['worked_time'] ) , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['paid_time'], $line_h,  TTDate::getTimeUnit( $data['paid_time'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['regular_time'], $line_h, TTDate::getTimeUnit( $data['regular_time'] ), 1, 0, 'C', 1);

								if ( $data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy']) ) {
									$pre_over_time_x = $pdf->getX();
									$pdf->SetFont('','',8);

									//Count how many absence policy rows there are.
									$over_time_policy_total_rows = count($data['categorized_time']['over_time_policy']);
									foreach( $data['categorized_time']['over_time_policy'] as $policy_id => $value ) {
										$pdf->Cell( $column_widths['over_time'], $line_h/$total_over_time_rows, $otp_columns['over_time_policy-'.$policy_id].': '.TTDate::getTimeUnit( $value ), 1, 0, 'C', 1);
										$pdf->setXY( $pre_over_time_x, $pdf->getY()+($line_h/$total_over_time_rows) );

										$over_time_x = $pdf->getX();
									}
									$pdf->setXY( $over_time_x+$column_widths['over_time'], $pre_punch_y);

									$pdf->SetFont('','',9);
								} else {
									$pdf->Cell( $column_widths['over_time'], $line_h, TTDate::getTimeUnit( $data['over_time'] ), 1, 0, 'C', 1);
								}

								if ( $data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy']) ) {
									$pre_absence_time_x = $pdf->getX();
									$pdf->SetFont('','',8);

									//Count how many absence policy rows there are.
									$absence_policy_total_rows = count($data['categorized_time']['absence_policy']);
									foreach( $data['categorized_time']['absence_policy'] as $policy_id => $value ) {
										$pdf->Cell( $column_widths['absence_time'], $line_h/$total_absence_rows, $ap_columns['absence_policy-'.$policy_id].': '.TTDate::getTimeUnit( $value ), 1, 0, 'C', 1);
										$pdf->setXY( $pre_absence_time_x, $pdf->getY()+($line_h/$total_absence_rows));
									}

									$pdf->setY( $pdf->getY()-($line_h/$total_absence_rows));

									$pdf->SetFont('','',9);
								} else {
									$pdf->Cell( $column_widths['absence_time'], $line_h, TTDate::getTimeUnit( $data['absence_time'] ), 1, 0, 'C', 1);
								}

								$pdf->Ln();

								unset($day_punch_data);
							}

							$totals['worked_time'] += $data['worked_time'];
							$totals['paid_time'] += $data['paid_time'];
							$totals['absence_time'] += $data['absence_time'];
							$totals['regular_time'] += $data['regular_time'];
							$totals['over_time'] += $data['over_time'];

							$week_totals['worked_time'] += $data['worked_time'];
							$week_totals['paid_time'] += $data['paid_time'];
							$week_totals['absence_time'] += $data['absence_time'];
							$week_totals['regular_time'] += $data['regular_time'];
							$week_totals['over_time'] += $data['over_time'];

							if ( $x % 7 == 0 OR $i == $max_i ) {
								//Show Week Total.
								$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['in_punch_time_stamp']+$column_widths['out_punch_time_stamp'];
								$pdf->SetFont('','B',9);
								$pdf->Cell( $total_cell_width, 6, TTi18n::gettext('Week Total:').' ', 0, 0, 'R', 0);
								$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $week_totals['worked_time'] ) , 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $week_totals['paid_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $week_totals['regular_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $week_totals['over_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $week_totals['absence_time'] ), 0, 0, 'C', 0);
								$pdf->Ln(1);

								unset($week_totals);
								$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

								$x=0;
								$y++;

								//Force page break every 3 weeks.
								if ( $y == 4 AND $i !== $max_i ) {
									$pdf->AddPage();
								}
							}

							$i++;
							$x++;
						}
						unset($data);
					}

					if ( isset($totals) AND is_array($totals) ) {
						//Display overall totals.
						$pdf->Ln(4);
						$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['in_punch_time_stamp'];
						$pdf->SetFont('','B',9);
						$pdf->Cell( $total_cell_width, 6, '' , 0, 0, 'R', 0);
						$pdf->Cell( $column_widths['out_punch_time_stamp'], 6, TTi18n::gettext('Overall Total:').' ', 'T', 0, 'R', 0);
						$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $totals['worked_time'] ) , 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $totals['paid_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $totals['regular_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $totals['over_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $totals['absence_time'] ), 'T', 0, 'C', 0);
						$pdf->Ln();
						unset($totals);
					}

					$pdf->SetFont('','',10);
					$pdf->setFillColor(255,255,255);
					$pdf->Ln();

					//Signature lines
					$pdf->MultiCell(200,5, TTi18n::gettext('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '.TTi18n::gettext('worked during the designated period.'), $border, 'L');
					$pdf->Ln(5);

					$border = 0;
					$pdf->Cell(40,5, TTi18n::gettext('Employee Signature:'), $border, 0, 'L');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');
					$pdf->Cell(40,5, TTi18n::gettext('Supervisor Signature:'), $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(40,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, $user_data['first_name'] .' '. $user_data['last_name'] , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, TTi18n::gettext('(print name)'), $border, 0, 'C');

					if ( $user_data['verified_time_sheet_date'] != FALSE ) {
						$pdf->Ln();
						$pdf->SetFont('','B',10);
						$pdf->Cell(200,5, TTi18n::gettext('TimeSheet electronically signed by').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '. TTi18n::gettext('on') .' '. TTDate::getDate('DATE+TIME', $user_data['verified_time_sheet_date'] ), $border, 0, 'C');
						$pdf->SetFont('','',10);
					}


					//Add generated date/time at the bottom.
					$pdf->SetFont('','I',8);
					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(245, $adjust_y) );
					$pdf->Cell(200,5, TTi18n::gettext('Generated:') .' '. TTDate::getDate('DATE+TIME', $pdf_created_date ), $border, 0, 'C');
				}

				$output = $pdf->Output('','S');
			}

			Debug::Text('Output PDF...', __FILE__, __LINE__, __METHOD__,10);
			if ( $output !== FALSE AND Debug::getVerbosity() < 11 ) {
				Misc::FileDownloadHeader('detailed_timesheet.pdf', 'application/pdf', strlen($output));
				echo $output;
				Debug::writeToLog();
				exit;
			} else {
				//Debug::Display();
				echo TTi18n::gettext('ERROR: Employee TimeSheet(s) not available!') . "<br>\n";
				Debug::writeToLog();
				exit;
			}
		} elseif ( $action == 'export' ) {
			if ( isset($rows) AND isset($filter_columns) ) {
				//Add the basic identifing columns.
				$export_filter_columns = array(
												'first_name' => TTi18n::gettext('First Name'),
												'last_name' => TTi18n::gettext('Last Name'),
												'full_name' => TTi18n::gettext('Full Name'),
												'employee_number' => TTi18n::gettext('Employee #'),
												'province' => TTi18n::gettext('Province/State'),
												'country' => TTi18n::gettext('Country'),
												'group' => TTi18n::gettext('Group'),
												'title' => TTi18n::gettext('Title'),
												'default_branch' => TTi18n::gettext('Default Branch'),
												'default_department' => TTi18n::gettext('Default Department'),
 												'pay_period' => TTi18n::gettext('Pay Period'),
											);

				$filter_columns = Misc::prependArray( $export_filter_columns, $filter_columns );

				//Flatten array for exporting.
				foreach( $rows as $row ) {
					if ( is_array($row['data']) ) {
						foreach( $row['data'] as $sub_row ) {
							unset($row['data']);
							$tmp_rows[] = array_merge($row, $sub_row);
						}
					}
				}
				unset($rows);

				Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);
				$data = Misc::Array2CSV( $tmp_rows, $filter_columns );

				Misc::FileDownloadHeader('report.csv', 'application/csv', strlen($data) );
				echo $data;
			} else {
				echo TTi18n::gettext("No Data To Export!") ."<br>\n";
			}
		} else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/TimesheetDetailReport.tpl');
		}

		break;
	case 'delete':
	case 'save':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
		unset($generic_data['name']);
	default:
		BreadCrumb::setCrumb($title);

		if ( $action == 'load' ) {
			Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);

			extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );
		} elseif ( $action == '' ) {
			//Check for default saved report first.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

				$ugd_obj = $ugdlf->getCurrent();
				$filter_data = $ugd_obj->getData();
				$generic_data['id'] = $ugd_obj->getId();
			} else {
				Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__,10);
				//Default selections
				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );

				$filter_data['user_status_ids'] = array( 10 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['pay_period_ids'] = array( '-0000-'.@array_shift(array_keys((array)$pay_period_options)) );
				$filter_data['start_date'] = $default_start_date;
				$filter_data['end_date'] = $default_end_date;
				$filter_data['group_ids'] = array( -1 );

				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}

				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-date_stamp',
											'-1090-worked_time',
											'-1130-paid_time',
											'-1140-regular_time'
												) );

				$filter_data['primary_sort'] = '-1000-date_stamp';
				$filter_data['secondary_sort'] = '-1090-worked_time';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'punch_branch_ids', 'punch_department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL);

		$ulf = TTnew( 'UserListFactory' );
		$all_array_option = array('-1' => TTi18n::gettext('-- All --'));

		//Get include employee list.
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );
		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		$uglf = TTnew( 'UserGroupListFactory' );
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		$blf = TTnew( 'BranchListFactory' );
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		$filter_data['src_punch_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_branch_ids'], $branch_options );
		$filter_data['selected_punch_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_branch_ids'], $branch_options );

		//Get departments
		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		$filter_data['src_punch_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_department_ids'], $department_options );
		$filter_data['selected_punch_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_department_ids'], $department_options );

		//Get employee titles
		$utlf = TTnew( 'UserTitleListFactory' );
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		//Get pay periods
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getByCompanyId( $current_company->getId() );
		$pay_period_options = Misc::prependArray( $all_array_option, $pplf->getArrayByListFactory( $pplf, FALSE, TRUE ) );
		$filter_data['src_pay_period_options'] = Misc::arrayDiffByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );
		$filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );


		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_options']['effective_date_order'] = 'Wage Effective Date';
		unset($filter_data['sort_options']['effective_date']);
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();
/*
		//Get employee list
		$filter_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );

		//Get column list
		$filter_data['column_options'] = $columns;

		$filter_data['pay_period_options'] = $pay_period_options;

		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$filter_data['group_by_options'] = array(
												'0' => 'No Grouping',
												'title' => 'Title',
												'province' => 'Province',
												'country' => 'Country',
												'default_branch' => 'Default Branch',
												'default_department' => 'Default Department'
											);
*/
		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/TimesheetDetail.tpl');

		break;
}
?>