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
 * $Id: WagesPayableSummary.php 9210 2013-02-28 00:16:41Z ipso $
 * $Date: 2013-02-27 16:16:41 -0800 (Wed, 27 Feb 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_wages_payable_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Wages Payable Report')); // See index.php

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
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );

$static_columns = array(

								'-1000-full_name' => TTi18n::gettext('Full Name'),
								'-1002-employee_number' => TTi18n::gettext('Employee #'),
								'-1010-title' => TTi18n::gettext('Title'),
								'-1020-province' => TTi18n::gettext('Province/State'),
								'-1030-country' => TTi18n::gettext('Country'),
								'-1039-group' => TTi18n::gettext('Group'),
								'-1040-default_branch' => TTi18n::gettext('Default Branch'),
								'-1050-default_department' => TTi18n::gettext('Default Department'),
								'-1052-branch' => TTi18n::gettext('Branch'),
								'-1053-department' => TTi18n::gettext('Department'),
								'-1055-date_stamp' => TTi18n::gettext('Date'),

								//'-1060-sin' => 'SIN',
								//'-1070-hire_date' => 'Hire Date',
								//'-1080-since_hire_date' => 'Since Hired'
								'-1085-currency' => TTi18n::gettext('Currency'),
								'-1086-current_currency' => TTi18n::gettext('Current Currency'),
								'-1090-hourly_rate' => TTi18n::gettext('Hourly Rate')
								);
$append_columns = array(
								'-1100-gross_wage' => TTi18n::gettext('Gross Wage'),
								'-1110-paid_time' => TTi18n::gettext('Paid Time'),
								'-1120-regular_time' => TTi18n::gettext('Regular Time'),
								'-1130-regular_time_wage' => TTi18n::gettext('Regular Time Wage')
								);

$columns = array_merge($static_columns, $append_columns);

//Get all Overtime policies.
$otplf = TTnew( 'OverTimePolicyListFactory' );
$otplf->getByCompanyId($current_company->getId());
if ( $otplf->getRecordCount() > 0 ) {
	foreach ($otplf as $otp_obj ) {
		Debug::Text('Over Time Policy ID: '. $otp_obj->getId() .' Rate: '. $otp_obj->getRate() , __FILE__, __LINE__, __METHOD__,10);
		$policy_rates['over_time_policy-'.$otp_obj->getId()] = $otp_obj->getRate();

		$otp_columns['over_time_policy-'.$otp_obj->getId()] = $otp_obj->getName();
		$otp_columns['over_time_policy-'.$otp_obj->getId().'_wage'] = $otp_obj->getName().' Wage';
	}

	$columns = array_merge( $columns, $otp_columns);
}

//Get all Premium policies.
$pplf = TTnew( 'PremiumPolicyListFactory' );
$pplf->getByCompanyId($current_company->getId());
if ( $pplf->getRecordCount() > 0 ) {
	foreach ($pplf as $pp_obj ) {
		$policy_rates['premium_policy-'.$pp_obj->getId()] = $pp_obj;

		$pp_columns['premium_policy-'.$pp_obj->getId()] = $pp_obj->getName();
		$pp_columns['premium_policy-'.$pp_obj->getId().'_wage'] = $pp_obj->getName().' Wage';
	}

	$columns = array_merge( $columns, $pp_columns);
}

//Get all Absence Policies.
$aplf = TTnew( 'AbsencePolicyListFactory' );
$aplf->getByCompanyId($current_company->getId());
if ( $aplf->getRecordCount() > 0 ) {
	foreach ($aplf as $ap_obj ) {
		$ap_columns['absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
		if ( $ap_obj->getType() == 10 ) {
			$policy_rates['absence_policy-'.$ap_obj->getId()] = $ap_obj->getRate();
			$ap_columns['absence_policy-'.$ap_obj->getId().'_wage'] = $ap_obj->getName().' Wage';
		} else {
			$policy_rates['absence_policy-'.$ap_obj->getId()] = 0;
		}
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
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));
} else {
	$pay_period_options = array();
}

$wage_columns = array('regular_time');

if ( isset( $otp_columns ) ) {
	$wage_columns = array_merge( $wage_columns, array_keys($otp_columns));
}

if ( isset( $pp_columns ) ) {
	$wage_columns = array_merge( $wage_columns, array_keys($pp_columns));
}

if ( isset( $ap_columns ) ) {
	$wage_columns = array_merge( $wage_columns, array_keys($ap_columns));
}

if ( isset($filter_data['start_date']) ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
}

if ( isset($filter_data['end_date']) ) {
	$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'currency_ids', 'column_ids' ), array() );

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
		if ( Misc::isSystemLoadValid() == FALSE ) {
			echo TTi18n::getText('Please try again later...');
			exit;
		}

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);

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

			$utlf = TTnew( 'UserTitleListFactory' );
			$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

			$uglf = TTnew( 'UserGroupListFactory' );
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

			$blf = TTnew( 'BranchListFactory' );
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = TTnew( 'DepartmentListFactory' );
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			$crlf = TTnew( 'CurrencyListFactory' );
			$crlf->getByCompanyId( $current_company->getId() );
			$currency_options = $crlf->getArrayByListFactory( $crlf, FALSE, TRUE );

			//Get Base Currency
			$crlf->getByCompanyIdAndBase( $current_company->getId(), TRUE );
			if ( $crlf->getRecordCount() > 0 ) {
				$base_currency_obj = $crlf->getCurrent();
			}

			$currency_convert_to_base = FALSE;
			if ( in_array( '-1', $filter_data['currency_ids']) OR count($filter_data['currency_ids']) > 1 ) {
				Debug::Text('More then one currency selected, converting to base!', __FILE__, __LINE__, __METHOD__,10);
				$currency_convert_to_base = TRUE;
			}

			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getDayReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			foreach ($udtlf as $udt_obj ) {
				$user_id = $udt_obj->getColumn('id');

				$date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
				$status_id = $udt_obj->getColumn('status_id');
				$type_id = $udt_obj->getColumn('type_id');

				$branch_id = $udt_obj->getColumn('branch_id');
				$department_id = $udt_obj->getColumn('department_id');
				//$user_wage_id = $udt_obj->getColumn('user_wage_id');

				//Paid time doesn't belong to a branch/department, so if we try to group by branch/department there will
				//always be a blank line showing just the paid time. So if they don't want to display paid time, just exclude it completely.
				if ( $status_id == 10 AND $type_id == 10 AND in_array('-1110-paid_time', $filter_data['column_ids']) ) {
					$column = 'paid_time';
					$wage_column = 'user_wage_id';
				} elseif ($status_id == 10 AND $type_id == 20) {
					$column = 'regular_time';
					$wage_column = 'user_wage_id';
				} elseif ($status_id == 10 AND $type_id == 30) {
					$column = 'over_time_policy-'. $udt_obj->getColumn('over_time_policy_id');
					$wage_column = 'over_time_policy_wage_id';
				} elseif ($status_id == 10 AND $type_id == 40) {
					$column = 'premium_policy-'. $udt_obj->getColumn('premium_policy_id');
					$wage_column = 'premium_policy_wage_id';
				} elseif ($status_id == 30 AND $type_id == 10) {
					$column = 'absence_policy-'. $udt_obj->getColumn('absence_policy_id');
					$wage_column = 'absence_policy_wage_id';
				} elseif ( ($status_id == 20 AND $type_id == 10 ) OR ($status_id == 10 AND $type_id == 100 ) OR ($status_id == 10 AND $type_id == 110 ) ) {
					$column = 'worked_time';
					$wage_column = 'user_wage_id';
				} else {
					$column = NULL;
					$wage_column = NULL;
				}

				//Debug::Text('Column: '. $column .' Total Time: '. $udt_obj->getColumn('total_time'), __FILE__, __LINE__, __METHOD__,10);
				if ( ( isset($filter_data['include_no_data_users']) AND $filter_data['include_no_data_users'] == 1 )
						OR ( !isset($filter_data['include_no_data_users']) AND $date_stamp != '' AND $column != '' AND $udt_obj->getColumn('total_time') > 0 )  ) {

					$raw_rows[$user_id][$branch_id][$department_id][$date_stamp][$column][] = array(
														'total_time' => $udt_obj->getColumn('total_time'),
														'user_wage_id' => $udt_obj->getColumn( $wage_column )
														);

				}
			}
			//print_r($raw_rows);

			if ( Misc::isSystemLoadValid() == FALSE ) {
				echo TTi18n::getText('Please try again later...');
				exit;
			}
			
			$ulf = TTnew( 'UserListFactory' );

			if ( isset($raw_rows) ) {
				$x=0;

				foreach($raw_rows as $user_id => $data_a ) {
					foreach($data_a as $branch_id => $data_b) {
						foreach($data_b as $department_id => $data_c) {
							foreach($data_c as $date_stamp => $data_d) {
								$user_obj = $ulf->getById( $user_id )->getCurrent();

								$tmp_rows[$x]['user_id'] = $user_id;
								$tmp_rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
								$tmp_rows[$x]['employee_number'] = $user_obj->getEmployeeNumber();
								$tmp_rows[$x]['province'] = $user_obj->getProvince();
								$tmp_rows[$x]['country'] = $user_obj->getCountry();

								$tmp_rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
								$tmp_rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options );
								$tmp_rows[$x]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
								$tmp_rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );

								$tmp_rows[$x]['branch'] =  Option::getByKey($branch_id, $branch_options, NULL );
								$tmp_rows[$x]['department'] = Option::getByKey($department_id, $department_options, NULL );

								$tmp_rows[$x]['currency'] = $tmp_rows[$x]['current_currency'] = Option::getByKey( $user_obj->getCurrency(), $currency_options );
								if ( $currency_convert_to_base == TRUE ) {
									$tmp_rows[$x]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
								}

								$tmp_rows[$x]['date_stamp'] = $date_stamp;

								foreach($data_d as $column => $data_z) {
									foreach($data_z as $data_y) {
										$total_time = $data_y['total_time'];
										$user_wage_id = $data_y['user_wage_id'];

										$hourly_rate = 0;
										if ( $permission->Check('wage','view') == TRUE OR in_array( $user_id, $wage_filter_data['permission_children_ids']) == TRUE ) {
											$uw_obj = getUserWageObject( $user_wage_id, $user_id );
											if ( is_object($uw_obj) ) {
												if ( $currency_convert_to_base == TRUE ) {
													$hourly_rate = $uw_obj->getBaseCurrencyHourlyRate( $uw_obj->getHourlyRate() );
												} else {
													$hourly_rate = $uw_obj->getHourlyRate();
												}
											}
										}
										$tmp_rows[$x]['hourly_rate'] = Misc::MoneyFormat( $hourly_rate, FALSE );

										if ( isset($tmp_rows[$x][$column]) ) {
											$tmp_rows[$x][$column] += $total_time;
										} else {
											$tmp_rows[$x][$column] = $total_time;
										}

										//Get Rate
										if ( isset($policy_rates[$column]) ) {
											if ( strpos( $column, 'premium') !== FALSE AND is_object($policy_rates[$column]) ) {
												Debug::Text('aColumn: '. $column .' Premium Policy, has dynamic rate. Hourly Rate: '. $hourly_rate .' Policy Rate: '. $policy_rates[$column]->getRate(), __FILE__, __LINE__, __METHOD__,10);

												switch ( $policy_rates[$column]->getPayType() ) {
													case 10: //Pay Factor
														//Since they are already paid for this time with regular or OT, minus 1 from the rate
														$policy_hourly_rate = bcmul( $hourly_rate, bcsub( $policy_rates[$column]->getRate(), 1 ) );
														break;
													case 20: //Pay Plus Premium
														$policy_hourly_rate = $policy_rates[$column]->getRate();
														break;
													case 30: //Flat Hourly Rate (Relative)
														//Get the difference between the employees current wage and the premium wage.
														$policy_hourly_rate = bcsub( $policy_rates[$column]->getRate(), $hourly_rate );
														break;
													case 32: //Flat Hourly Rate (NOT relative)
														$policy_hourly_rate = $policy_rates[$column]->getRate();
														break;
													case 40: //Minimum/Prevailing
														$policy_hourly_rate = ( $policy_rates[$column]->getRate() > $hourly_rate ) ? ($policy_rates[$column]->getRate() - $hourly_rate) : 0;
														break;
												}

												//$policy_hourly_rate = $policy_rates[$column]->getHourlyRate( $hourly_rate );
											} else {
												//Debug::Text('Column: '. $column .' NOT Premium Policy: Policy Rate: '. $policy_rates[$column], __FILE__, __LINE__, __METHOD__,10);
												$policy_hourly_rate = bcmul( $policy_rates[$column], $hourly_rate );
											}
										} elseif ($column == 'regular_time') {
											$policy_hourly_rate = $hourly_rate;
										} else {
											$policy_hourly_rate = 0;
										}
										Debug::Text('bColumn: '. $column .' Total Time: '. $total_time .' Hourly Rate: '. $hourly_rate .' Policy Hourly Rate: '. $policy_hourly_rate .' Amount: '. TTDate::getHours( $total_time ) * $policy_hourly_rate, __FILE__, __LINE__, __METHOD__,10);

										$amount = bcmul( TTDate::getHours( $total_time ), $policy_hourly_rate);

										if ( in_array($column, $wage_columns) ) {
											//Debug::Text('...Column: '. $column .' Hourly Wage: '. $hourly_rate .' Rate: '. $rate .' Total Wage: '. ($hourly_rate * $rate), __FILE__, __LINE__, __METHOD__,10);

											if ( isset($tmp_rows[$x][$column.'_wage'] ) ) {
												$tmp_rows[$x][$column.'_wage'] = Misc::MoneyFormat( $tmp_rows[$x][$column.'_wage'] + $amount, FALSE);
											} else {
												$tmp_rows[$x][$column.'_wage'] = Misc::MoneyFormat( $amount, FALSE);
											}
										}

										if ( isset($tmp_rows[$x]['gross_wage'] ) ) {
											//Debug::Text('Adding to Gross, Prev Amount: '. $tmp_rows[$x]['gross_wage'] .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
											$tmp_rows[$x]['gross_wage'] = Misc::MoneyFormat( $tmp_rows[$x]['gross_wage'] + $amount, FALSE);
										} else {
											//Debug::Text('Setting Gross...', __FILE__, __LINE__, __METHOD__,10);
											$tmp_rows[$x]['gross_wage'] = Misc::MoneyFormat( $amount, FALSE);
										}

										unset($hourly_rate, $policy_hourly_rate, $total_time);
									}
								}

								$x++;
							}
						}
					}
				}
			}
			//print_r($tmp_rows);

			if ( isset($tmp_rows) AND isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
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

				$tmp_rows = Misc::ArrayGroupBy( $tmp_rows, array(Misc::trimSortPrefix($filter_data['primary_group_by']), Misc::trimSortPrefix($filter_data['secondary_group_by']), Misc::trimSortPrefix($filter_data['tertiary_group_by']), Misc::trimSortPrefix($filter_data['quaternary_group_by'])), Misc::trimSortPrefix($ignore_elements) );
			}
			//print_r($tmp_rows);

			if ( isset($tmp_rows) ) {
				foreach($tmp_rows as $row) {
					$rows[] = $row;
				}

				$special_sort_columns = array();
				if ( in_array( Misc::trimSortPrefix($filter_data['primary_sort']), $special_sort_columns ) ) {
						$filter_data['primary_sort'] = $filter_data['primary_sort'].'_order';
				}
				if ( in_array( Misc::trimSortPrefix($filter_data['secondary_sort']), $special_sort_columns ) ) {
						$filter_data['secondary_sort'] = $filter_data['secondary_sort'].'_order';
				}

				$rows = Sort::Multisort($rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

				$total_row = Misc::ArrayAssocSum($rows, NULL, 2);

				$last_row = count($rows);
				$rows[$last_row] = $total_row;
				foreach ($static_columns as $static_column_key => $static_column_val) {
					$rows[$last_row][Misc::trimSortPrefix($static_column_key)] = NULL;
				}
				unset($static_column_key, $static_column_val);

				$tmp_rows = $rows;
				unset($rows);
			}

			if ( isset($tmp_rows) ) {
				$trimmed_static_columns = array_keys( Misc::trimSortPrefix($static_columns) );
				foreach($tmp_rows as $tmp_row ) {
					foreach($tmp_row as $column => $column_data) {
						//if ( strstr($column, 'time') AND !strstr($column, 'wage') AND $column_data != '' ) {
						if ( $column == 'date_stamp') {
							$column_data = TTDate::getDate('DATE', $column_data);
						} elseif ( !in_array( $column, $trimmed_static_columns )
								AND !strstr($column, 'wage') AND $column_data != '' ) {
							//Debug::Text('Converting to Time Column: '. $column, __FILE__, __LINE__, __METHOD__,10);
							$column_data = TTDate::getTimeUnit( $column_data );
						}

						$row_columns[$column] = $column_data;
						unset($column, $column_data);
					}

					$rows[] = $row_columns;
					unset($row_columns);
				}
			}

			//var_dump($rows);
			foreach( $filter_data['column_ids'] as $column_key ) {
				$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
			}
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
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/WagesPayableSummaryReport.tpl');
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

				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );
				$filter_data['currency_ids'] = array( -1 );
				$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys($pay_period_options)) );

				$filter_data['column_ids'] = array(
											'-1000-full_name',
											'-1100-gross_wage',
											'-1110-paid_time',
											'-1120-regular_time'
												);

				$filter_data['start_date'] = TTDate::getBeginMonthEpoch();
				$filter_data['end_date'] = TTDate::getEndMonthEpoch();

				$filter_data['primary_group_by'] = '-1000-full_name';

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1100-gross_wage';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'currency_ids', 'column_ids' ), NULL);

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

		//Get employee titles
		$utlf = TTnew( 'UserTitleListFactory' );
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		//Get currencies
		$crlf = TTnew( 'CurrencyListFactory' );
		$crlf->getByCompanyId( $current_company->getId() );
		$currency_options = Misc::prependArray( $all_array_option, $crlf->getArrayByListFactory( $crlf, FALSE, TRUE ) );
		$filter_data['src_currency_options'] = Misc::arrayDiffByKey( (array)$filter_data['currency_ids'], $currency_options );
		$filter_data['selected_currency_options'] = Misc::arrayIntersectByKey( (array)$filter_data['currency_ids'], $currency_options );

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
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$filter_data['group_by_options'] = Misc::prependArray( array('0' => TTi18n::gettext('No Grouping')), $static_columns );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/WagesPayableSummary.tpl');

		break;
}
?>
