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
 * $Revision: 4104 $
 * $Id: RemittanceSummary.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_remittance_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Remittance Summary Report')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'setup_data',
												'generic_data',
												'filter_data'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );

$static_columns = array(			'-1000-full_name' => TTi18n::gettext('Full Name'),
											'-1010-title' => TTi18n::gettext('Title'),
											'-1020-province' => TTi18n::gettext('Province'),
											'-1030-country' => TTi18n::gettext('Country'),
											'-1039-group' => TTi18n::gettext('Group'),
											'-1040-default_branch' => TTi18n::gettext('Default Branch'),
											'-1050-default_department' => TTi18n::gettext('Default Department'),
											);

$columns = array(
											'-1060-total' => TTi18n::gettext('Total Deductions'),
											'-1070-ei_total' => TTi18n::gettext('EI'),
											'-1080-cpp_total' => TTi18n::gettext('CPP'),
											'-1090-tax_total' => TTi18n::gettext('Tax'),
											'-1100-gross_payroll' => TTi18n::gettext('Gross Pay')
											);

$columns = Misc::prependArray( $static_columns, $columns);

$psealf = TTnew( 'PayStubEntryAccountListFactory' );

//Combine all accounts to one array
$all_psea_ids = @array_merge( $setup_data['ei_psea_ids'], $setup_data['cpp_psea_ids'], $setup_data['tax_psea_ids']);

$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
$pseallf->getByCompanyId( $current_company->getId() );
if ( $pseallf->getRecordCount() > 0 ) {
	$pseal_obj = $pseallf->getCurrent();
}

//Get all pay periods
$pplf = TTnew( 'PayPeriodListFactory' );
$pplf->getByCompanyId( $current_company->getId() );
if ( $pplf->getRecordCount() > 0 ) {
	foreach ($pplf as $pay_period_obj) {
		$pay_period_ids[] = $pay_period_obj->getId();
		$pay_period_transaction_dates[$pay_period_obj->getId()] = $pay_period_obj->getTransactionDate();
	}

	$pplf = TTnew( 'PayPeriodListFactory' );
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array() );

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();

switch ($action) {
	case 'display_report':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'aFilter Data', __FILE__, __LINE__, __METHOD__,10);

		//Save report setup data
		$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), $_SERVER['SCRIPT_NAME'] );
		if ( $ugdlf->getRecordCount() > 0 ) {
			$ugdf->setID( $ugdlf->getCurrent()->getID() );
		}
		$ugdf->setCompany( $current_company->getId() );
		$ugdf->setScript( $_SERVER['SCRIPT_NAME'] );
		$ugdf->setName( $title );
		$ugdf->setData( $setup_data );
		$ugdf->setDefault( TRUE );
		if ( $ugdf->isValid() ) {
			$ugdf->Save();
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$filter_data['user_ids'][] = $u_obj->getId();
			}

			//Trim sort prefix from selected pay periods.
			$tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
			$filter_data['pay_period_ids'] = array();
			foreach( $tmp_filter_pay_period_ids as $key => $filter_pay_period_id) {
				$filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
			}
			unset($key, $tmp_filter_pay_period_ids, $filter_pay_period_id);

			if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['user_ids']) ) {
				//Get column headers
				//$psenlf->getAll();
				/*
				$psealf->getByCompanyId( $current_company->getId() );
				foreach($psealf as $psea_obj) {
					$report_columns[$psea_obj->getId()] = $psea_obj->getName();
				}
				$report_columns = Misc::prependArray( $static_columns, $report_columns);
				$report_columns['total'] = $columns['total'];
				*/

				//Get least transaction date of the selected ones.
				$i=0;
				foreach ( $filter_data['pay_period_ids'] as $tmp_pay_period_id ) {
					if ( $i == 0 ) {
						$transaction_date = $pay_period_transaction_dates[$tmp_pay_period_id];
					} else {
						if ( $pay_period_transaction_dates[$tmp_pay_period_id] < $transaction_date ) {
							$transaction_date = $pay_period_transaction_dates[$tmp_pay_period_id];
						}
					}

					$i++;
				}
				unset($tmp_pay_period_id, $i);

				$pself = TTnew( 'PayStubEntryListFactory' );
				$pself->getReportByCompanyIdAndUserIdAndPayPeriodId( $current_company->getId(), $filter_data['user_ids'], $filter_data['pay_period_ids'] );

				foreach( $pself as $pse_obj ) {
					$user_id = $pse_obj->getColumn('user_id');
					$pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');

					$raw_rows[$user_id][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}
				//var_dump($tmp_rows);

				if ( isset($raw_rows) ) {
					$ulf = TTnew( 'UserListFactory' );

					$utlf = TTnew( 'UserTitleListFactory' );
					$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

					$uglf = TTnew( 'UserGroupListFactory' );
					$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

					$blf = TTnew( 'BranchListFactory' );
					$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

					$dlf = TTnew( 'DepartmentListFactory' );
					$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

					$x=0;
					$total_employees = 0;
					foreach($raw_rows as $user_id => $raw_row) {
						$user_obj = $ulf->getById( $user_id )->getCurrent();

						$tmp_rows[$x]['user_id'] = $user_id;
						$tmp_rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
						$tmp_rows[$x]['province'] = $user_obj->getProvince();
						$tmp_rows[$x]['country'] = $user_obj->getCountry();

						//$tmp_rows[$x]['province'] = Option::getByKey($user_obj->getProvince(), $user_obj->getOptions('province') );
						//$tmp_rows[$x]['country'] = Option::getByKey($user_obj->getCountry(), $user_obj->getOptions('country') );

						$tmp_rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
						$tmp_rows[$x]['group'] = Option::getByKey($u_obj->getGroup(), $group_options );
						$tmp_rows[$x]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
						$tmp_rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );

						$total_amount = 0;
						$ei_total = 0;
						$cpp_total = 0;
						$tax_total = 0;
						$total_gross = 0;
						foreach($raw_row as $id => $amount ) {
							$tmp_rows[$x][$id] = $amount;

							if ( $id != 10 AND is_array($all_psea_ids) AND in_array($id, $all_psea_ids ) ) {
								//Debug::Text('Total Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
								$total_amount += $amount;
							}

							if ( isset($setup_data['ei_psea_ids']) AND in_array($id, $setup_data['ei_psea_ids'] ) ) {
								//Debug::Text('IE Total Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
								$ei_total += $amount;
							}

							if ( isset($setup_data['cpp_psea_ids']) AND in_array($id, $setup_data['cpp_psea_ids'] ) ) {
								//Debug::Text('CPP Total Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
								$cpp_total += $amount;
							}

							if ( isset($setup_data['tax_psea_ids']) AND in_array($id, $setup_data['tax_psea_ids'] ) ) {
								//Debug::Text('Tax Total Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
								$tax_total += $amount;
							}

							if ( $id == $pseal_obj->getTotalGross() ) {
								//Debug::Text('Gross Total Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
								$total_gross += $amount;
							}
						}

						$tmp_rows[$x]['total'] = Misc::MoneyFormat( $total_amount, FALSE );
						$tmp_rows[$x]['gross_payroll'] = Misc::MoneyFormat( $total_gross, FALSE );
						$tmp_rows[$x]['ei_total'] = Misc::MoneyFormat( $ei_total, FALSE );
						$tmp_rows[$x]['cpp_total'] = Misc::MoneyFormat( $cpp_total, FALSE );
						$tmp_rows[$x]['tax_total'] = Misc::MoneyFormat( $tax_total, FALSE );

						$total_employees++;

						$x++;
						unset($amount, $total_amount, $total_gross, $ei_total, $cpp_total, $tax_total);
					}
				}
				//var_dump($tmp_rows);

				if ( isset($tmp_rows) AND isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
					Debug::Text('Primary Grouping Data By: '. $filter_data['primary_group_by'], __FILE__, __LINE__, __METHOD__,10);

					$ignore_elements = array_keys($static_columns);

					$filter_data['column_ids'] = array_diff( $filter_data['column_ids'], $ignore_elements );

					//Add the group by element back in
					if ( isset($filter_data['secondary_group_by']) AND $filter_data['secondary_group_by'] != 0 ) {
						array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'], $filter_data['secondary_group_by'] );
					} else {
						array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'] );
					}

					$tmp_rows = Misc::ArrayGroupBy( $tmp_rows, array(Misc::trimSortPrefix($filter_data['primary_group_by']),Misc::trimSortPrefix($filter_data['secondary_group_by'])), Misc::trimSortPrefix($ignore_elements) );
				}

				if ( isset($tmp_rows) ) {
					foreach($tmp_rows as $row) {
						$rows[] = $row;
					}

					if ( $filter_data['primary_sort'] == 'hire_date' ) {
						$filter_data['primary_sort'] = 'hire_date_order';
					}
					//$rows = Sort::Multisort($rows, $filter_data['primary_sort'], NULL, 'ASC');
					$rows = Sort::Multisort($rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

					$total_row = Misc::ArrayAssocSum($rows, NULL, 2);

					//Get values to go directly on the PD7AE (05) form.
					if ( isset($total_row) ) {
						$form_data = array(
												/*
												'cpp' => Misc::MoneyFormat( $total_row[15] + $total_row[19] ),
												'ei' => Misc::MoneyFormat( $total_row[16] + $total_row[20] ),
												'tax' => Misc::MoneyFormat( $total_row[12] + $total_row[13] + $total_row[14] ),
												*/
												'cpp' => Misc::MoneyFormat( $total_row['cpp_total'] ),
												'ei' => Misc::MoneyFormat( $total_row['ei_total'] ),
												'tax' => Misc::MoneyFormat( $total_row['tax_total'] ),

												'amount_due' => Misc::MoneyFormat( $total_row['total'] ),
												'gross_payroll' => Misc::MoneyFormat( $total_row[$pseal_obj->getTotalGross()] ),
												'employees' => $total_employees,
												'due_date' => Wage::getRemittanceDueDate($transaction_date, $total_row['total'] ),
												'end_remitting_period' => date('Y-m', $transaction_date)
											);
					}
					//var_dump($form_data);

					$last_row = count($rows);
					$rows[$last_row] = $total_row;
					foreach ($static_columns as $static_column_key => $static_column_val) {
						$rows[$last_row][Misc::trimSortPrefix($static_column_key)] = NULL;
					}
					unset($static_column_key, $static_column_val);
				}

			}
		}

		foreach( $filter_data['column_ids'] as $column_key ) {
			//$filter_columns[$column_key] = $report_columns[$column_key];
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
		}

		$smarty->assign_by_ref('generated_time', TTDate::getTime() );
		$smarty->assign_by_ref('pay_period_options', $pay_period_options );
		$smarty->assign_by_ref('filter_data', $filter_data );
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign_by_ref('rows', $rows);
		$smarty->assign_by_ref('form_data', $form_data );

		$smarty->display('report/RemittanceSummaryReport.tpl');

		break;
	case 'delete':
	case 'save':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
		unset($generic_data['name']);

	default:
		BreadCrumb::setCrumb($title);

		$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), $_SERVER['SCRIPT_NAME'] );
		if ( $ugdlf->getRecordCount() > 0 ) {
			Debug::Text('Found Company Report Setup!', __FILE__, __LINE__, __METHOD__,10);
			$ugd_obj = $ugdlf->getCurrent();
			$setup_data = $ugd_obj->getData();
		}
		unset($ugd_obj);

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
				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE ) );
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys($pay_period_options)) );
				$filter_data['group_ids'] = array( -1 );

				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}

				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-full_name',
											'-1060-total',
											'-1070-ei_total',
											'-1080-cpp_total',
											'-1090-tax_total',
											'-1100-gross_payroll',
												) );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1060-total';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL );

		//Deduction PSEA accounts
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$filter_data['deduction_pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(20,30), FALSE );

		$ulf = TTnew( 'UserListFactory' );
		$all_array_option = array('-1' => TTi18n::gettext('-- All --'));

		//Get include employee list.
		$ulf->getByCompanyId( $current_company->getId() );
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
		$smarty->assign_by_ref('setup_data', $setup_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/RemittanceSummary.tpl');

		break;
}
?>