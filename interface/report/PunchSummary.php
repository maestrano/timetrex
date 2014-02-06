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
 * $Revision: 9993 $
 * $Id: PunchSummary.php 9993 2013-05-24 20:16:41Z ipso $
 * $Date: 2013-05-24 13:16:41 -0700 (Fri, 24 May 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_punch_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Punch Summary Report')); // See index.php

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
									'-1090-date_stamp' => TTi18n::gettext('Date'),
									'-1100-in_time_stamp' => TTi18n::gettext('In Punch'),
									'-1101-in_type' => TTi18n::gettext('In Type'),
									'-1110-out_time_stamp' => TTi18n::gettext('Out Punch'),
									'-1111-out_type' => TTi18n::gettext('Out Type'),
									'-1120-in_actual_time_stamp' => TTi18n::gettext('In (Actual)'),
									'-1130-out_actual_time_stamp' => TTi18n::gettext('Out (Actual)'),
									'-1160-branch' => TTi18n::gettext('Branch'),
									'-1170-department' => TTi18n::gettext('Department'),
									'-1171-in_station_type' => TTi18n::gettext('In Station Type'),
									'-1172-in_station_station_id' => TTi18n::gettext('In Station ID'),
									'-1173-in_station_source' => TTi18n::gettext('In Station Source'),
									'-1174-in_station_description' => TTi18n::gettext('In Station Description'),
									'-1175-out_station_type' => TTi18n::gettext('Out Station Type'),
									'-1176-out_station_station_id' => TTi18n::gettext('Out Station ID'),
									'-1177-out_station_source' => TTi18n::gettext('Out Station Source'),
									'-1178-out_station_description' => TTi18n::gettext('Out Station Description'),
									'-1220-note' => TTi18n::gettext('Note'),
									'-1229-hourly_rate' => TTi18n::gettext('Hourly Rate'),
									);

$professional_edition_static_columns = array(
									'-1180-job' => TTi18n::gettext('Job'),
									'-1181-job_manual_id' => TTi18n::gettext('Job Code'),
									'-1181-job_description' => TTi18n::gettext('Job Description'),
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
											'-1440-actual_total_time' => TTi18n::gettext('Actual Time'),
											'-1450-actual_total_time_wage' => TTi18n::gettext('Actual Time Wage'),
											'-1460-actual_total_time_diff' => TTi18n::gettext('Actual Time Difference'),
											'-1470-actual_total_time_diff_wage' => TTi18n::gettext('Actual Time Difference Wage'),
											);

$professional_edition_columns = array(
											'-1400-quantity' => TTi18n::gettext('Quantity'),
											'-1410-bad_quantity' => TTi18n::gettext('Bad Quantity'),
									);

if ( $current_company->getProductEdition() >= 20 ) {
	$columns = Misc::prependArray( $columns, $professional_edition_columns);
	ksort($columns);
}

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

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'punch_branch_ids', 'punch_department_ids', 'user_title_ids', 'pay_period_ids', 'include_job_ids', 'exclude_job_ids', 'job_branch_ids', 'job_department_ids', 'job_group_ids', 'client_ids', 'job_item_ids', 'job_item_group_ids', 'column_ids' ), array() );

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

			$plf = TTnew( 'PunchListFactory' );
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

			//$plf->getReportByStartDateAndEndDateAndUserIdListAndBranchIdAndDepartmentIdAndJobIdListAndJobItemIdList( $filter_data['start_date'], $filter_data['end_date'], $filter_data['user_ids'], $filter_data['punch_branch_ids'], $filter_data['punch_department_ids'], $filter_data['job_ids'], $filter_data['job_item_ids'] );
			$plf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			foreach ($plf as $p_obj ) {
				//Debug::Text('User ID: '. $p_obj->getColumn('user_id') .' Status ID: '. $p_obj->getColumn('status_id') .' Time Stamp: '. TTDate::getDate('DATE+TIME', TTDate::strtotime( $p_obj->getColumn('punch_time_stamp') ) ), __FILE__, __LINE__, __METHOD__,10);

				if ( !isset($tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]) ) {

					$hourly_rate = 0;
					if ( $permission->Check('wage','view') == TRUE OR in_array( $p_obj->getColumn('user_id'), $wage_filter_data['permission_children_ids']) == TRUE ) {
						$uw_obj = getUserWageObject( $p_obj->getColumn('user_wage_id'), $p_obj->getColumn('user_id') );
						if ( is_object($uw_obj) ) {
							$hourly_rate = $uw_obj->getHourlyRate();
						}
					}

					$actual_time_diff = (int)$p_obj->getColumn('actual_total_time') - (int)$p_obj->getColumn('total_time');

					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')] = array(
						'user_id' => $p_obj->getColumn('user_id'),
						'group_id' => $p_obj->getColumn('group_id'),
						'branch_id' => $p_obj->getColumn('branch_id'),
						'department_id' => $p_obj->getColumn('department_id'),
						'job_id' => $p_obj->getColumn('job_id'),
						'job_name' => $p_obj->getColumn('job_name'),
						'job_status_id' => $p_obj->getColumn('job_status_id'),
						'job_manual_id' => $p_obj->getColumn('job_manual_id'),
						'job_description' => $p_obj->getColumn('job_description'),
						'job_branch_id' => $p_obj->getColumn('job_branch_id'),
						'job_department_id' => $p_obj->getColumn('job_department_id'),
						'job_group_id' => $p_obj->getColumn('job_group_id'),
						'job_item_id' => $p_obj->getColumn('job_item_id'),
						'quantity' => $p_obj->getColumn('quantity'),
						'bad_quantity' => $p_obj->getColumn('bad_quantity'),
						'note' => $p_obj->getColumn('note'),
						'total_time' => $p_obj->getColumn('total_time'),
						'total_time_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('total_time') ), $hourly_rate ), FALSE ),
						'actual_total_time' => $p_obj->getColumn('actual_total_time'),
						'actual_total_time_diff' => $actual_time_diff,
						'actual_total_time_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('actual_total_time') ), $hourly_rate ), FALSE ),
						'actual_total_time_diff_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $actual_time_diff ), $hourly_rate) ),
						'other_id1' => $p_obj->getColumn('other_id1'),
						'other_id2' => $p_obj->getColumn('other_id2'),
						'other_id3' => $p_obj->getColumn('other_id3'),
						'other_id4' => $p_obj->getColumn('other_id4'),
						'other_id5' => $p_obj->getColumn('other_id5'),
						'date_stamp' => TTDate::strtotime( $p_obj->getColumn('date_stamp') ),
						'in_time_stamp' => NULL,
						'in_actual_time_stamp' => NULL,
						'in_type' => NULL,
						'out_time_stamp' => NULL,
						'out_actual_time_stamp' => NULL,
						'out_type' => NULL,
						'user_wage_id' => $p_obj->getColumn('user_wage_id'),
						'hourly_rate' => Misc::MoneyFormat( $hourly_rate, FALSE ),
						'in_station_type_id' => NULL,
						'in_station_station_id' => NULL,
						'in_station_source' => NULL,
						'in_station_description' => NULL,
						'out_station_type_id' => NULL,
						'out_station_station_id' => NULL,
						'out_station_source' => NULL,
						'out_station_description' => NULL,
						);
				}

				if ( $p_obj->getColumn('status_id') == 10 ) {
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('time_stamp') );
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_type'] = $p_obj->getColumn('type_id');
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_actual_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('actual_time_stamp') );

					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_type_id'] = $p_obj->getColumn('station_type_id');
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_station_id'] = $p_obj->getColumn('station_station_id');
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_source']  = $p_obj->getColumn('station_source');
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_description'] = $p_obj->getColumn('station_description');
				} else {
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('time_stamp') );
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_type'] = $p_obj->getColumn('type_id');
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_actual_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('actual_time_stamp') );

					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_type_id'] = $p_obj->getColumn('station_type_id');
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_station_id'] = $p_obj->getColumn('station_station_id');
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_source']  = $p_obj->getColumn('station_source');
					$tmp_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_description'] = $p_obj->getColumn('station_description');
				}
				unset($user_wage_id, $hourly_rate, $uw_obj, $actual_time_diff);

			}
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

			$slf = TTnew( 'StationListFactory' );
			$station_type_options = $slf->getOptions('type');

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

			$punch_type_options = $plf->getOptions('type');

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

						foreach($data_b as $punch_control_id => $data_c ) {
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

							$rows[$x]['in_station_type'] = Option::getByKey($data_c['in_station_type_id'], $station_type_options, NULL );
							$rows[$x]['in_station_station_id'] = (string)$data_c['in_station_station_id'];
							$rows[$x]['in_station_source'] = (string)$data_c['in_station_source'];
							$rows[$x]['in_station_description'] = (string)$data_c['in_station_description'];

							$rows[$x]['out_station_type'] = Option::getByKey($data_c['out_station_type_id'], $station_type_options, NULL );
							$rows[$x]['out_station_station_id'] = (string)$data_c['out_station_station_id'];
							$rows[$x]['out_station_source'] = (string)$data_c['out_station_source'];
							$rows[$x]['out_station_description'] = (string)$data_c['out_station_description'];

							$rows[$x]['branch'] =  Option::getByKey($data_c['branch_id'], $branch_options, NULL );
							$rows[$x]['department'] = Option::getByKey($data_c['department_id'], $department_options, NULL );

							if ( isset($job_options[$data_c['job_id']]) ) {
								//$rows[$x]['job'] = Option::getByKey($data_c['job_id'], $job_options, NULL );
								$rows[$x]['job'] = $data_c['job_name'];
							} else {
								$rows[$x]['job'] = TTi18n::gettext('- No Job -');
							}
							$rows[$x]['job_manual_id'] = $data_c['job_manual_id'];
							$rows[$x]['job_description'] = $data_c['job_description'];
							$rows[$x]['job_status'] = Option::getByKey($data_c['job_status_id'], $job_status_options, NULL );
							$rows[$x]['job_branch'] = Option::getByKey($data_c['job_branch_id'], $branch_options, NULL );
							$rows[$x]['job_department'] = Option::getByKey($data_c['job_department_id'], $department_options, NULL );
							$rows[$x]['job_group'] = Option::getByKey($data_c['job_group_id'], $job_group_options, NULL );

							if ( isset($job_item_options[$data_c['job_item_id']]) ) {
								$rows[$x]['job_item'] = $job_item_options[$data_c['job_item_id']];
							} else {
								$rows[$x]['job_item'] = TTi18n::gettext('- No Task -');
							}

							$rows[$x]['quantity'] = $data_c['quantity'];
							$rows[$x]['bad_quantity'] = $data_c['bad_quantity'];
							$rows[$x]['note'] = $data_c['note'];
							$rows[$x]['hourly_rate'] = $data_c['hourly_rate'];
							$rows[$x]['total_time'] = $data_c['total_time'];
							$rows[$x]['total_time_wage'] = $data_c['total_time_wage'];
							$rows[$x]['actual_total_time'] = $data_c['actual_total_time'];
							$rows[$x]['actual_total_time_wage'] = $data_c['actual_total_time_wage'];
							$rows[$x]['actual_total_time_diff'] = $data_c['actual_total_time_diff'];
							$rows[$x]['actual_total_time_diff_wage'] = $data_c['actual_total_time_diff_wage'];

							$rows[$x]['in_time_stamp'] = $data_c['in_time_stamp'];
							$rows[$x]['in_actual_time_stamp'] = $data_c['in_actual_time_stamp'];
							if ( isset($punch_type_options[$data_c['in_type']]) ) {
								$rows[$x]['in_type'] = $punch_type_options[$data_c['in_type']];
							} else {
								$rows[$x]['in_type'] = NULL;
							}

							$rows[$x]['out_time_stamp'] = $data_c['out_time_stamp'];
							$rows[$x]['out_actual_time_stamp'] = $data_c['out_actual_time_stamp'];
							//if ( $data_c['out_type'] != '' ) {
							if ( isset($punch_type_options[$data_c['out_type']]) ) {
								$rows[$x]['out_type'] = $punch_type_options[$data_c['out_type']];
							} else {
								$rows[$x]['out_type'] = NULL;
							}

							$rows[$x]['other_id1'] = $data_c['other_id1'];
							$rows[$x]['other_id2'] = $data_c['other_id2'];
							$rows[$x]['other_id3'] = $data_c['other_id3'];
							$rows[$x]['other_id4'] = $data_c['other_id4'];
							$rows[$x]['other_id5'] = $data_c['other_id5'];

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
						if ( $column == 'total_time'
								OR $column == 'actual_total_time'
								OR $column == 'actual_total_time_diff') {
							$column_data = TTDate::getTimeUnit( $column_data );
						} elseif ( $column == 'in_time_stamp'
									OR $column == 'in_actual_time_stamp'
									OR $column == 'out_time_stamp'
									OR $column == 'out_actual_time_stamp' ) {
							$column_data = TTDate::getDate( 'DATE+TIME', $column_data );
							if ( $column_data == '' ) {
								$column_data = NULL;
							}
						} elseif ( $column == 'date_stamp' ) {
							$column_data = TTDate::getDate( 'DATE', $column_data );
						}

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

			$smarty->display('report/PunchSummaryReport.tpl');
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
				//$filter_data['date_range'] = 1;
				//$filter_data['start_date'] = TTDate::getBeginMonthEpoch( time() );
				//$filter_data['end_date'] = TTDate::getEndMonthEpoch( time() );

				$filter_data['start_date'] = $default_start_date;
				$filter_data['end_date'] = $default_end_date;
				$filter_data['pay_period_ids'] = array( '-0000-'.@array_shift(array_keys((array)$pay_period_options)) );

				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['punch_branch_ids'] = array( -1 );
				$filter_data['punch_department_ids'] = array( -1 );
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
											'-1100-in_time_stamp',
											'-1101-in_type',
											'-1110-out_time_stamp',
											'-1111-out_type',
											'-1160-branch',
											'-1170-department',
											'-1430-total_time',
												) );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1100-in_time_stamp';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'punch_branch_ids', 'punch_department_ids', 'user_title_ids', 'pay_period_ids', 'include_job_ids', 'exclude_job_ids', 'job_branch_ids', 'job_department_ids', 'job_group_ids', 'client_ids', 'job_item_ids', 'job_item_group_ids', 'column_ids' ), NULL );

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

		//Get departments
		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		$filter_data['src_punch_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_branch_ids'], $branch_options );
		$filter_data['selected_punch_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_branch_ids'], $branch_options );

		$filter_data['src_punch_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_department_ids'], $department_options );
		$filter_data['selected_punch_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_department_ids'], $department_options );

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

		$smarty->display('report/PunchSummary.tpl');

		break;
}
?>
