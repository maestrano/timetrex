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
 * $Revision: 3157 $
 * $Id: PunchSummary.php 3157 2009-12-03 19:51:59Z ipso $
 * $Date: 2009-12-03 11:51:59 -0800 (Thu, 03 Dec 2009) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_schedule_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Schedule Summary Report')); // See index.php

//User Wage cache array.
function getUserWageObject( $user_wage_id, $user_id ) {
	global $user_wage_obj;

	if ( isset($user_wage_obj[$user_wage_id])
		AND is_object($user_wage_obj[$user_wage_id]) ) {
		return $user_wage_obj[$user_wage_id];
	} else {
		$uwlf = TTnew( 'UserWageListFactory' );

		//This handles future wage changes properly.
		$uwlf->getByIDAndUserId( $user_wage_id, $user_id );
		if ( $uwlf->getRecordCount() > 0 ) {
			$user_wage_obj[$user_wage_id] = $uwlf->getCurrent();

			return $user_wage_obj[$user_wage_id];
		}

		return FALSE;
	}
}

if ( isset($config_vars['other']['report_maximum_execution_limit']) AND $config_vars['other']['report_maximum_execution_limit'] != '' ) { ini_set( 'max_execution_time', $config_vars['other']['report_maximum_execution_limit'] ); }
if ( isset($config_vars['other']['report_maximum_memory_limit']) AND $config_vars['other']['report_maximum_memory_limit'] != '' ) { ini_set( 'memory_limit', $config_vars['other']['report_maximum_memory_limit'] ); }

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'

												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
												) );

$static_columns = array(			'-1000-full_name' => TTi18n::gettext('Full Name'),
									'-1002-employee_number' => TTi18n::gettext('Employee #'),
									'-1010-title' => TTi18n::gettext('Title'),
									'-1020-province' => TTi18n::gettext('Province/State'),
									'-1030-country' => TTi18n::gettext('Country'),
									'-1039-group' => TTi18n::gettext('Group'),
									'-1040-default_branch' => TTi18n::gettext('Default Branch'),
									'-1050-default_department' => TTi18n::gettext('Default Department'),
									'-1060-branch' => TTi18n::gettext('Branch'),
									'-1070-department' => TTi18n::gettext('Department'),
									'-1090-hourly_rate' => TTi18n::gettext('Hourly Rate'),
									'-1200-date_stamp' => TTi18n::gettext('Date'),
									'-1205-type' => TTi18n::gettext('Type'),
									'-1210-status' => TTi18n::gettext('Status'),
									'-1220-absence_policy' => TTi18n::gettext('Absence Policy'),
									'-1230-start_time' => TTi18n::gettext('Start Time'),
									'-1240-end_time' => TTi18n::gettext('End Time'),
									);

$professional_edition_static_columns = array(
									'-1180-job' => TTi18n::gettext('Job'),
									'-1181-job_manual_id' => TTi18n::gettext('Job Code'),
									'-1182-job_status' => TTi18n::gettext('Job Status'),
									'-1183-job_branch' => TTi18n::gettext('Job Branch'),
									'-1184-job_department' => TTi18n::gettext('Job Department'),
									'-1185-job_group' => TTi18n::gettext('Job Group'),
									'-1190-job_item' => TTi18n::gettext('Task'),
									);

if ( $current_company->getProductEdition() >= 20 ) {
	$static_columns = Misc::prependArray( $static_columns, $professional_edition_static_columns);
	ksort($static_columns);
}

//Get custom user fields
$oflf = TTnew( 'OtherFieldListFactory' );
$other_field_names = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getId(), 15 );
if ( is_array($other_field_names) ) {
	$static_columns = Misc::prependArray( $static_columns, $other_field_names);
}

$columns = array(
											'-1430-total_time' => TTi18n::gettext('Total Time'),
											'-1440-total_time_wage' => TTi18n::gettext('Total Time Wage'),
											);

$columns = Misc::prependArray( $static_columns, $columns);

$default_start_date = TTDate::getBeginMonthEpoch();
$default_end_date = TTDate::getEndMonthEpoch();

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
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));
}

if ( isset($filter_data['start_date']) ) {
	$filter_data['start_date'] = TTDate::getBeginDayEpoch( TTDate::parseDateTime($filter_data['start_date']) );
}

if ( isset($filter_data['end_date']) ) {
	$filter_data['end_date'] = TTDate::getEndDayEpoch( TTDate::parseDateTime($filter_data['end_date']) );
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'schedule_branch_ids', 'schedule_department_ids', 'user_title_ids', 'pay_period_ids', 'include_job_ids', 'exclude_job_ids', 'job_branch_ids', 'job_department_ids', 'job_group_ids', 'client_ids', 'job_item_ids', 'job_item_group_ids', 'column_ids' ), array() );

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
$wage_permission_children_ids = array();
if ( $permission->Check('user','view') == FALSE ) {
	$hlf = TTnew( 'HierarchyListFactory' );
	$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('user','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('user','view_own') ) {
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
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);
		if ( Misc::isSystemLoadValid() == FALSE ) {
			echo TTi18n::getText('Please try again later...');
			exit;
		}

		$filter_data['job_group_ids'] = Misc::trimSortPrefix( $filter_data['job_group_ids'], TRUE );

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' ) {
				unset($filter_data['start_date']);
				unset($filter_data['end_date']);
			} else {
				unset($filter_data['pay_period_ids']);
			}

			foreach( $ulf as $u_obj ) {
				$filter_data['include_user_ids'][] = $u_obj->getId();
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

			$slf = TTnew( 'ScheduleListFactory' );
			if ( $current_company->getProductEdition() >= 20 ) {
				if ( !isset($filter_data['job_item_ids']) ) {
					$filter_data['job_item_ids'] = array();
				}

				$jlf = TTnew( 'JobListFactory' );
				$jlf->getSearchByCompanyIdAndStatusIdAndBranchIdAndDepartmentIdAndGroupIdAndClientIdAndIncludeIdAndExcludeId(
					$current_company->getId(),
					NULL,
					NULL,
					NULL,
					Misc::trimSortPrefix( $filter_data['job_group_ids'], TRUE ),
					NULL,
					$filter_data['include_job_ids'],
					$filter_data['exclude_job_ids'] );

				$filter_data['job_ids'] = array();
				if ( $jlf->getRecordCount() > 0 ) {
					foreach( $jlf as $j_obj ) {
						$filter_data['job_ids'][] = $j_obj->getId();
					}
				}
			} else {
				$filter_data['job_ids'] = array( -1 );
				$filter_data['job_item_ids'] = array( -1 );
			}

			$aplf = TTnew( 'AbsencePolicyListFactory' );
			$aplf->getByCompanyId( $current_company->getId() );
			if ( $aplf->getRecordCount() > 0 ) {
				foreach( $aplf as $ap_obj ) {
					$absence_policy_paid_options[$ap_obj->getID()] = $ap_obj->isPaid();
				}
			}
			unset($aplf, $ap_obj);

			$sf = TTnew( 'ScheduleFactory' );
			$raw_schedule_shifts = $sf->getScheduleArray( $filter_data );
			if ( is_array($raw_schedule_shifts) ) {
				foreach ($raw_schedule_shifts as $iso_date => $data_a ) {
					foreach ($data_a as $shift_key => $data ) {
						//Hide wages if they don't have permission.
						if ( $permission->Check('wage','view') == FALSE AND !in_array( $data['user_id'], $wage_filter_data['permission_children_ids']) == TRUE ) {
							$data['hourly_rate'] = $data['total_time_wage'] = Misc::MoneyFormat(0);
						}
						$tmp_rows[$data['pay_period_id']][$data['user_id']][] = $data;
					}
				}
			}
			unset($raw_schedule_shifts);
			//var_dump($tmp_rows);

			$ulf = TTnew( 'UserListFactory' );

			$utlf = TTnew( 'UserTitleListFactory' );
			$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

			$uglf = TTnew( 'UserGroupListFactory' );
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

			$blf = TTnew( 'BranchListFactory' );
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = TTnew( 'DepartmentListFactory' );
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			if ( $current_company->getProductEdition() >= 20 ) {
				$jlf = TTnew( 'JobListFactory' );
				$job_options = $jlf->getByCompanyIdArray( $current_company->getId() );
				$job_status_options = $jlf->getOptions('status');

				$jglf = TTnew( 'JobGroupListFactory' );
				$jglf->getByCompanyId( $current_company->getId() );
				$job_group_options = $jglf->getArrayByListFactory( $jglf, FALSE );

				$jilf = TTnew( 'JobItemListFactory' );
				$job_item_options = $jilf->getByCompanyIdArray( $current_company->getId() );
			} else {
				$job_options = array();
				$job_status_options = array();
				$job_item_options = array();
				$job_group_options = array();
			}

			$aplf = TTnew( 'AbsencePolicyListFactory' );
			$absence_policy_options = $aplf->getByCompanyIdArray( $current_company->getId() );

			$sf = TTnew( 'ScheduleFactory' );
			$schedule_status_options = $sf->getOptions('status');
			$schedule_type_options = $sf->getOptions('type');

			if ( Misc::isSystemLoadValid() == FALSE ) {
				echo TTi18n::getText('Please try again later...');
				exit;
			}
			
			if ( isset($tmp_rows) ) {
				$x=0;
				foreach($tmp_rows as $pay_period_id => $data_a ) {
					$rows[$x]['pay_period_id'] = $pay_period_id;

					foreach($data_a as $user_id => $data_b ) {
						$user_obj = $ulf->getById( $user_id )->getCurrent();

						foreach($data_b as $key => $data_c ) {
							$rows[$x]['user_id'] = $user_id;
							$rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
							$rows[$x]['employee_number'] = $user_obj->getEmployeeNumber();
							$rows[$x]['province'] = $user_obj->getProvince();
							$rows[$x]['country'] = $user_obj->getCountry();

							$rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
							$rows[$x]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
							$rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );
							$rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options, NULL );

							$rows[$x]['date_stamp'] = (string)$data_c['date_stamp'];

							$rows[$x]['branch'] =  Option::getByKey($data_c['branch_id'], $branch_options, NULL );
							$rows[$x]['department'] = Option::getByKey($data_c['department_id'], $department_options, NULL );

							if ( isset($job_options[$data_c['job_id']]) ) {
								//$rows[$x]['job'] = Option::getByKey($data_c['job_id'], $job_options, NULL );
								$rows[$x]['job'] = $data_c['job'];
							} else {
								$rows[$x]['job'] = TTi18n::gettext('- No Job -');
							}
							$rows[$x]['job_manual_id'] = $data_c['job_manual_id'];
							$rows[$x]['job_status'] = Option::getByKey($data_c['job_status_id'], $job_status_options, NULL );
							$rows[$x]['job_branch'] = Option::getByKey($data_c['job_branch_id'], $branch_options, NULL );
							$rows[$x]['job_department'] = Option::getByKey($data_c['job_department_id'], $department_options, NULL );
							$rows[$x]['job_group'] = Option::getByKey($data_c['job_group_id'], $job_group_options, NULL );

							if ( isset($job_item_options[$data_c['job_item_id']]) ) {
								$rows[$x]['job_item'] = $job_item_options[$data_c['job_item_id']];
							} else {
								$rows[$x]['job_item'] = TTi18n::gettext('- No Task -');
							}

							$rows[$x]['type'] = Option::getByKey($data_c['type_id'], $schedule_type_options, NULL );
							$rows[$x]['status'] = Option::getByKey($data_c['status_id'], $schedule_status_options, NULL );
							$rows[$x]['start_time'] = $data_c['start_time'];
							$rows[$x]['end_time'] = $data_c['end_time'];

							$rows[$x]['absence_policy'] = Option::getByKey($data_c['absence_policy_id'], $absence_policy_options, NULL );
							$rows[$x]['hourly_rate'] = $data_c['hourly_rate'];
							$rows[$x]['total_time'] = $data_c['total_time'];
							$rows[$x]['total_time_wage'] = $data_c['total_time_wage'];

							$x++;
						}
					}

				}
			}
			//var_dump($rows);
			unset($tmp_rows);

			if ( isset($rows) AND isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
				Debug::Text('Primary Grouping Data By: '. $filter_data['primary_group_by'], __FILE__, __LINE__, __METHOD__,10);

				$ignore_elements = array_keys($static_columns);

				$filter_data['column_ids'] = array_diff( $filter_data['column_ids'], $ignore_elements );

				//Add the group by element back in
				if ( isset($filter_data['quaternary_group_by']) AND $filter_data['quaternary_group_by'] != 0 ) {
					array_unshift( $filter_data['column_ids'], $filter_data['quaternary_group_by'] );
				}
				if ( isset($filter_data['tertiary_group_by']) AND $filter_data['tertiary_group_by'] != 0 ) {
					array_unshift( $filter_data['column_ids'], $filter_data['tertiary_group_by'] );
				}
				if ( isset($filter_data['secondary_group_by']) AND $filter_data['secondary_group_by'] != 0 ) {
					array_unshift( $filter_data['column_ids'], $filter_data['secondary_group_by'] );
				}
				if ( isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != 0 ) {
					array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'] );
				}

				$rows = Misc::ArrayGroupBy( $rows, array(Misc::trimSortPrefix($filter_data['primary_group_by']), Misc::trimSortPrefix($filter_data['secondary_group_by']), Misc::trimSortPrefix($filter_data['tertiary_group_by']), Misc::trimSortPrefix($filter_data['quaternary_group_by'])), Misc::trimSortPrefix($ignore_elements) );
			}

			if ( isset($rows) ) {
				foreach($rows as $row) {
					$tmp_rows[] = $row;
				}
				//var_dump($tmp_rows);

				$rows = Sort::Multisort($tmp_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

				$total_row = Misc::ArrayAssocSum($rows, NULL, 2);

				$last_row = count($rows);
				$rows[$last_row] = $total_row;
				foreach ($static_columns as $static_column_key => $static_column_val) {
					$rows[$last_row][Misc::trimSortPrefix($static_column_key)] = NULL;
				}
				unset($static_column_key, $static_column_val);

				//Convert units
				$tmp_rows = $rows;
				unset($rows);

				$trimmed_static_columns = array_keys( Misc::trimSortPrefix($static_columns) );
				foreach($tmp_rows as $row ) {
					foreach($row as $column => $column_data) {
						//if ( $column != 'full_name' AND $column_data != '' ) {
						if ( $column == 'total_time' ) {
							$column_data = TTDate::getTimeUnit( $column_data );
						} elseif ( $column == 'start_time' OR $column == 'end_time' ) {
							$column_data = TTDate::getDate( 'DATE+TIME', $column_data );
							if ( $column_data == '' ) {
								$column_data = NULL;
							}
						}
						/*
						} elseif ( $column == 'date_stamp' ) {
							$column_data = TTDate::getDate( 'DATE', $column_data );
						}
						*/

						$row_columns[$column] = $column_data;
						unset($column, $column_data);
					}

					$rows[] = $row_columns;
					unset($row_columns);
				}
			}
		}
		//var_dump($rows);

		foreach( $filter_data['column_ids'] as $column_key ) {
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
		}

		if ( $action == 'export' ) {
			if ( isset($rows) AND isset($filter_columns) ) {
				Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);
				$data = Misc::Array2CSV( $rows, $filter_columns );

				Misc::FileDownloadHeader('report.csv', 'application/csv', strlen($data) );
				echo $data;
			} else {
				echo TTi18n::gettext('No Data To Export!') ."<br>\n";
			}
		} else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/ScheduleSummaryReport.tpl');
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
				$filter_data['start_date'] = $default_start_date;
				$filter_data['end_date'] = $default_end_date;
				$filter_data['pay_period_ids'] = array( '-0000-'.@array_shift(array_keys((array)$pay_period_options)) );

				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['schedule_status_ids'] = array( -1 );
				$filter_data['schedule_branch_ids'] = array( -1 );
				$filter_data['schedule_department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['job_group_ids'] = array( -1 );
				$filter_data['include_job_ids'] = array();
				$filter_data['exclude_job_ids'] = array();
				$filter_data['job_item_ids'] = array( -1 );

				$filter_data['group_ids'] = array( -1 );

				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}

				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-full_name',
											'-1160-branch',
											'-1170-department',
											'-1200-date_stamp',
											'-1210-status',
											'-1230-start_time',
											'-1240-end_time',
											'-1430-total_time',
												) );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1095-start_time';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'schedule_status_ids', 'schedule_branch_ids', 'schedule_department_ids', 'user_title_ids', 'pay_period_ids', 'include_job_ids', 'exclude_job_ids', 'job_branch_ids', 'job_department_ids', 'job_group_ids', 'client_ids', 'job_item_ids', 'job_item_group_ids', 'column_ids' ), NULL );

		$ulf = TTnew( 'UserListFactory' );
		$slf = TTnew( 'ScheduleListFactory' );

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

		//Schedule status
		$schedule_status_options = Misc::prependArray( $all_array_option, $slf->getOptions('status') );
		$filter_data['src_schedule_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['schedule_status_ids'], $schedule_status_options );
		$filter_data['selected_schedule_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['schedule_status_ids'], $schedule_status_options );

		//Get departments
		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		$filter_data['src_schedule_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['schedule_branch_ids'], $branch_options );
		$filter_data['selected_schedule_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['schedule_branch_ids'], $branch_options );

		$filter_data['src_schedule_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['schedule_department_ids'], $department_options );
		$filter_data['selected_schedule_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['schedule_department_ids'], $department_options );

		//Get employee titles
		$utlf = TTnew( 'UserTitleListFactory' );
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		if ( $current_company->getProductEdition() >= 20 ) {
			$jlf = TTnew( 'JobListFactory' );

			//Get include job list.
			$jlf->getByCompanyId( $current_company->getId() );
			$job_options = Misc::prependArray( array('0' => TTi18n::gettext('- No Job -') ), $jlf->getArrayByListFactory( $jlf, FALSE, TRUE ) );
			$filter_data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

			$filter_data['src_include_job_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_job_ids'], $job_options );
			$filter_data['selected_include_job_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_job_ids'], $job_options );

			//Get exclude job list
			$exclude_job_options = Misc::prependArray( $all_array_option, $jlf->getArrayByListFactory( $jlf, FALSE, TRUE ) );
			$filter_data['src_exclude_job_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_job_ids'], $job_options );
			$filter_data['selected_exclude_job_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_job_ids'], $job_options );

			//Get Job Groups
			$jglf = TTnew( 'JobGroupListFactory' );
			$nodes = FastTree::FormatArray( $jglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE);
			$job_group_options = Misc::prependArray( $all_array_option, $jglf->getArrayByNodes( $nodes, FALSE, TRUE ) );
			$filter_data['src_job_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['job_group_ids'], $job_group_options );
			$filter_data['selected_job_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['job_group_ids'], $job_group_options );

			//Get Job Items
			$jilf = TTnew( 'JobItemListFactory' );
			$jilf->getByCompanyId( $current_company->getId() );
			$job_item_options = Misc::prependArray( array('-1' => TTi18n::gettext('-- All --'), '0' => TTi18n::gettext('- No Task -') ), $jilf->getArrayByListFactory( $jilf, FALSE, TRUE ) );
			$filter_data['src_job_item_options'] = Misc::arrayDiffByKey( (array)$filter_data['job_item_ids'], $job_item_options );
			$filter_data['selected_job_item_options'] = Misc::arrayIntersectByKey( (array)$filter_data['job_item_ids'], $job_item_options );
		}

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

		$filter_data['group_by_options'] = Misc::prependArray( array('0' => TTi18n::gettext('No Grouping')), $static_columns );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/ScheduleSummary.tpl');

		break;
}
?>
