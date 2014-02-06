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
 * $Revision: 9521 $
 * $Id: AccrualBalanceSummary.php 9521 2013-04-08 23:09:52Z ipso $
 * $Date: 2013-04-08 16:09:52 -0700 (Mon, 08 Apr 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_accrual_balance_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Accrual Balance Summary Report')); // See index.php


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
									);

$columns = array(
											'-1060-total_balance' => TTi18n::gettext('Total Balance'),
											);

$columns = Misc::prependArray( $static_columns, $columns);

//Get all accrual policies.
$aplf = TTnew( 'AccrualPolicyListFactory' );
$aplf->getByCompanyId($current_company->getId());
if ( $aplf->getRecordCount() > 0 ) {
	foreach ($aplf as $ap_obj ) {
		$ap_columns['accrual_policy-'.$ap_obj->getId()] = $ap_obj->getName();
	}
	$columns = array_merge( $columns, $ap_columns);
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array() );

$permission_children_ids = array();
if ( $permission->Check('accrual','view') == FALSE ) {
	$hlf = TTnew( 'HierarchyListFactory' );
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('accrual','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('accrual','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
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

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$filter_data['user_id'][] = $u_obj->getId();
			}
            $ablf = TTnew( 'AccrualBalanceListFactory' );
			$ablf->getByUserIdAndCompanyId( $filter_data['user_id'], $current_company->getId() );
			if ( $ablf->getRecordCount() ) {
				foreach ( $ablf as $ab_obj ) {
					$column = 'accrual_policy-'. $ab_obj->getAccrualPolicyID();
					Debug::Text('Column: '. $column .' User ID: '. $ab_obj->getUser() .' Balance: '. $ab_obj->getBalance() , __FILE__, __LINE__, __METHOD__,10);

					$tmp_rows[$ab_obj->getUser()][$column] = $ab_obj->getBalance();                    
                    
					if ( isset($tmp_rows[$ab_obj->getUser()]['total_balance']) ) {
						$tmp_rows[$ab_obj->getUser()]['total_balance'] += $ab_obj->getBalance();
					} else {
						$tmp_rows[$ab_obj->getUser()]['total_balance'] = $ab_obj->getBalance();
					}
				}
			}
            
			$ulf = TTnew( 'UserListFactory' );

			$utlf = TTnew( 'UserTitleListFactory' );
			$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

			$uglf = TTnew( 'UserGroupListFactory' );
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

			$blf = TTnew( 'BranchListFactory' );
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = TTnew( 'DepartmentListFactory' );
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			if ( isset($tmp_rows) ) {
				$x=0;
				foreach($tmp_rows as $user_id => $data_a ) {
					$user_obj = $ulf->getById( $user_id )->getCurrent();

					$rows[$x]['user_id'] = $user_id;
					$rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
					$rows[$x]['employee_number'] = $user_obj->getEmployeeNumber();
					$rows[$x]['province'] = $user_obj->getProvince();
					$rows[$x]['country'] = $user_obj->getCountry();
					$rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options );
					$rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options );
					$rows[$x]['default_branch'] = Option::getByKey($user_obj->getDefaultBranch(), $branch_options );
					$rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options );

					/*
					$rows[$x]['title'] = $title_options[$user_obj->getTitle()];
					$rows[$x]['group'] = $branch_options[$user_obj->getDefaultBranch()];
					$rows[$x]['default_branch'] = $branch_options[$user_obj->getDefaultBranch()];
					$rows[$x]['default_department'] = $department_options[$user_obj->getDefaultDepartment()];
					*/

					foreach($data_a as $column => $balance) {
						$rows[$x][$column] = $balance;
					}

					$x++;
				}
			}
			//var_dump($rows);
			unset($tmp_rows);

			if ( isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
				Debug::Text('Primary Grouping Data By: '. $filter_data['primary_group_by'], __FILE__, __LINE__, __METHOD__,10);

				$ignore_elements = array_keys($static_columns);

				$filter_data['column_ids'] = array_diff( $filter_data['column_ids'], $ignore_elements );

				//Add the group by element back in
				if ( isset($filter_data['secondary_group_by']) AND $filter_data['secondary_group_by'] != 0 ) {
					array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'], $filter_data['secondary_group_by'] );
				} else {
					array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'] );
				}

				$rows = Misc::ArrayGroupBy( $rows, array(Misc::trimSortPrefix($filter_data['primary_group_by']),Misc::trimSortPrefix($filter_data['secondary_group_by'])), Misc::trimSortPrefix($ignore_elements) );
			}
			if ( isset($rows) ) {
				foreach($rows as $row) {
					$tmp_rows[] = $row;
				}
				//$rows = Sort::Multisort($tmp_rows, $filter_data['primary_sort'], NULL, 'ASC');
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
						if ( !in_array( $column, $trimmed_static_columns ) ) {
							$column_data = TTDate::getTimeUnit( $column_data );
						}
                        
						$row_columns[$column] = $column_data;
						unset($column, $column_data);
					}
                    
					$rows[] = $row_columns;
					unset($row_columns);
				}
               
				//var_dump($rows);
			}
		}

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

			$smarty->display('report/AccrualBalanceSummaryReport.tpl');
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

				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}
				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-full_name',
											'-1060-total_balance',
												) );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1060-total_balance';

			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL );
        
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

		$smarty->display('report/AccrualBalanceSummary.tpl');

		break;
}
?>