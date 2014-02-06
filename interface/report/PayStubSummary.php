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
 * $Id: PayStubSummary.php 9210 2013-02-28 00:16:41Z ipso $
 * $Date: 2013-02-27 16:16:41 -0800 (Wed, 27 Feb 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_pay_stub_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Pay Stub Summary Report'));  // See index.php

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
							'-0900-first_name' => TTi18n::gettext('First Name'),
							'-0901-middle_name' => TTi18n::gettext('Middle Name'),
							'-0902-middle_initial' => TTi18n::gettext('Middle Initial'),
							'-0903-last_name' => TTi18n::gettext('Last Name'),
							'-1000-full_name' => TTi18n::gettext('Full Name'),
							'-1002-employee_number' => TTi18n::gettext('Employee #'),
							'-1010-title' => TTi18n::gettext('Title'),
							'-1020-province' => TTi18n::gettext('Province/State'),
							'-1030-country' => TTi18n::gettext('Country'),
							'-1039-group' => TTi18n::gettext('Group'),
							'-1040-default_branch' => TTi18n::gettext('Default Branch'),
							'-1050-default_department' => TTi18n::gettext('Default Department'),
							'-1060-sin' => TTi18n::gettext('SIN/SSN'),
							'-1065-birth_date' => TTi18n::gettext('Birth Date'),
							'-1070-hire_date' => TTi18n::gettext('Hire Date'),
							'-1080-since_hire_date' => TTi18n::gettext('Since Hired'),
							'-1085-termination_date' => TTi18n::gettext('Termination Date'),
							'-1086-institution' => TTi18n::gettext('Bank Institution'),
							'-1087-transit' => TTi18n::gettext('Bank Transit/Routing'),
							'-1089-account' => TTi18n::gettext('Bank Account'),
							'-1090-pay_period' => TTi18n::gettext('Pay Period'),
							'-1100-pay_stub_start_date' => TTi18n::gettext('Start Date'),
							'-1110-pay_stub_end_date' => TTi18n::gettext('End Date'),
							'-1120-pay_stub_transaction_date' => TTi18n::gettext('Transaction Date'),
							'-1130-currency' => TTi18n::gettext('Currency'),
							'-1131-current_currency' => TTi18n::gettext('Current Currency'),
							);

$psealf = TTnew( 'PayStubEntryAccountListFactory' );
$psen_columns = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,60,65), FALSE );

$columns = Misc::prependArray( $static_columns, $psen_columns);

$default_transaction_start_date = TTDate::getBeginMonthEpoch( time() );
$default_transaction_end_date = TTDate::getEndMonthEpoch( time() );

//Get all pay periods
$pplf = TTnew( 'PayPeriodListFactory' );
$pplf->getPayPeriodsWithPayStubsByCompanyId( $current_company->getId() );
$pay_period_options = array();
if ( $pplf->getRecordCount() > 0 ) {
	$pp=0;
	foreach ($pplf as $pay_period_obj) {
		$pay_period_ids[] = $pay_period_obj->getId();
		$pay_period_end_dates[$pay_period_obj->getId()] = $pay_period_obj->getEndDate();

		if ( $pp == 0 ) {
			$default_transaction_start_date = $pay_period_obj->getEndDate();
			$default_transaction_end_date = $pay_period_obj->getTransactionDate()+86400;
		}
		$pp++;
	}
	$pplf = TTnew( 'PayPeriodListFactory' );
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));
}

if ( isset($filter_data['transaction_start_date']) ) {
	$filter_data['transaction_start_date'] = TTDate::getBeginDayEpoch( TTDate::parseDateTime($filter_data['transaction_start_date']) );
}

if ( isset($filter_data['transaction_end_date']) ) {
	$filter_data['transaction_end_date'] = TTDate::getEndDayEpoch( TTDate::parseDateTime($filter_data['transaction_end_date']) );
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'currency_ids', 'pay_period_ids', 'column_ids' ), array() );

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
if ( $permission->Check('pay_stub','view') == FALSE ) {
	$hlf = TTnew( 'HierarchyListFactory' );
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('pay_stub','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('pay_stub','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'view_pay_stubs':
	case 'export':
	case 'display_report':
		//Debug::setVerbosity(11);
		Debug::Text('Submit! Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'aFilter Data', __FILE__, __LINE__, __METHOD__,10);
		if ( Misc::isSystemLoadValid() == FALSE ) {
			echo TTi18n::getText('Please try again later...');
			exit;
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' ) {
				unset($filter_data['transaction_start_date']);
				unset($filter_data['transaction_end_date']);
			} else {
				unset($filter_data['pay_period_ids']);
			}

			foreach( $ulf as $u_obj ) {
				$filter_data['user_id'][] = $u_obj->getId();
			}

			//Trim sort prefix from selected pay periods.
			if ( isset($filter_data['pay_period_ids']) ) {
				$tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
				$filter_data['pay_period_ids'] = array();
				foreach( $tmp_filter_pay_period_ids as $key => $filter_pay_period_id) {
					$filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
				}
				unset($key, $tmp_filter_pay_period_ids, $filter_pay_period_id);
			}

			if ( ( ( isset($filter_data['transaction_start_date']) AND isset($filter_data['transaction_end_date']) ) OR isset($filter_data['pay_period_ids']) )
					AND isset($filter_data['user_id']) ) {
				if ( $action == 'view_pay_stubs' ) {
					Debug::Text('View Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);

					$pslf = TTnew( 'PayStubListFactory' );
					//$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $filter_data['user_ids'], $current_company->getId(), $filter_data['pay_period_ids']);
					$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
					if ( $pslf->getRecordCount() > 0 ) {
						if ( !isset($filter_data['hide_employer_rows']) ) {
							//Must be false, because if it isn't checked it won't be set.
							$filter_data['hide_employer_rows'] = FALSE;
						}

						$output = $pslf->getPayStub( $pslf, (bool)$filter_data['hide_employer_rows'] );

						if ( Debug::getVerbosity() < 11 ) {
							Misc::FileDownloadHeader('pay_stub.pdf', 'application/pdf', strlen($output));
							echo $output;
							exit;
						}
					}
				} elseif ( $action == 'export' AND $filter_data['export_type'] != 'csv' ) {
					Debug::Text('Export NON-CSV', __FILE__, __LINE__, __METHOD__,10);

					$pslf = TTnew( 'PayStubListFactory' );
					//$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $filter_data['user_ids'], $current_company->getId(), $filter_data['pay_period_ids']);
					$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
					if ( $pslf->getRecordCount() > 0 AND strlen($filter_data['export_type']) >= 3) {
						$output = $pslf->exportPayStub( $pslf, $filter_data['export_type'] );

						if ( Debug::getVerbosity() < 11 ) {
							if ( stristr( $filter_data['export_type'], 'cheque') ) {
								Misc::FileDownloadHeader('checks_'. str_replace(array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.pdf', 'application/pdf', strlen($output));
							} else {

								//Include file creation number in the exported file name, so the user knows what it is without opening the file,
								//and can generate multiple files if they need to match a specific number.
								$ugdlf = TTnew( 'UserGenericDataListFactory' );
								$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), 'PayStubFactory', TRUE );
								if ( $ugdlf->getRecordCount() > 0 ) {
									$ugd_obj = $ugdlf->getCurrent();
									$setup_data = $ugd_obj->getData();
								}

								if ( isset($setup_data) ) {
									$file_creation_number = $setup_data['file_creation_number']++;
								} else {
									$file_creation_number = 0;
								}
								Misc::FileDownloadHeader('eft_'. $file_creation_number .'_'. str_replace(array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.txt', 'application/text', strlen($output));
							}

							if ( $output != FALSE ) {
								echo $output;
							} else {
								echo TTi18n::gettext('No data to export.') ."<br>\n";
							}
							exit;
						}
					} else {
						echo TTi18n::gettext('No data to export or export format is invalid.') ."<br>\n";
						exit;
					}
				} else {
					//Get column headers
					$report_columns = array();

					//Strip off Employee Deduction, Earnings, etc from names so they don't clutter reports.
					$psealf->getByCompanyId( $current_company->getId() );
					foreach($psealf as $psea_obj) {
						//$report_columns[$psen_obj->getId()] = $psen_obj->getDescription();
						$report_columns[$psea_obj->getId()] = $psea_obj->getName();
					}
					//var_dump($report_columns);

					$report_columns = Misc::prependArray( $static_columns, $report_columns);

					$pself = TTnew( 'PayStubEntryListFactory' );
					$pself->getReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
					if ( $pself->getRecordCount() > 0 ) {
						//Prepare data for regular report.
						foreach( $pself as $pse_obj ) {
							$user_id = $pse_obj->getColumn('user_id');
							$pay_stub_id = $pse_obj->getColumn('pay_stub_id');
							$currency_id = $pse_obj->getColumn('currency_id');
							$currency_rate = $pse_obj->getColumn('currency_rate');
							//$pay_period_id = $pse_obj->getColumn('pay_period_id');
							//$pay_stub_transaction_date = $pse_obj->getColumn('pay_stub_transaction_date');
							$pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');

							//$raw_rows[$user_id][$pay_p][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');

							if ( !isset($raw_rows[$user_id][$pay_stub_id]) ) {
								$raw_rows[$user_id][$pay_stub_id]['pay_period_id'] = $pse_obj->getColumn('pay_period_id');
								$raw_rows[$user_id][$pay_stub_id]['pay_stub_start_date'] = TTDate::strtotime( $pse_obj->getColumn('pay_stub_start_date') );
								$raw_rows[$user_id][$pay_stub_id]['pay_stub_end_date'] = TTDate::strtotime( $pse_obj->getColumn('pay_stub_end_date') );
								$raw_rows[$user_id][$pay_stub_id]['pay_stub_transaction_date'] = TTDate::strtotime( $pse_obj->getColumn('pay_stub_transaction_date') );
								$raw_rows[$user_id][$pay_stub_id]['currency_id'] = $pse_obj->getColumn('currency_id');
								$raw_rows[$user_id][$pay_stub_id]['currency_rate'] = $pse_obj->getColumn('currency_rate');
							}
							$raw_rows[$user_id][$pay_stub_id]['pay_stub_entry_name'][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
						}
						unset($user_id, $pay_stub_id, $currency_id, $currency_rate, $pay_stub_entry_name_id);
					}
					//var_dump($raw_rows);

					if ( Misc::isSystemLoadValid() == FALSE ) {
						echo TTi18n::getText('Please try again later...');
						exit;
					}
					
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

						$balf = TTnew( 'BankAccountListFactory' );

						$x=0;
						foreach($raw_rows as $user_id => $data_b) {
							$user_obj = $ulf->getById( $user_id )->getCurrent();
							$balf->getUserAccountByCompanyIdAndUserId( $user_obj->getCompany(), $user_obj->getID() );
							if ( $balf->getRecordCount() == 1 ) {
								$ba_obj = $balf->getCurrent();
							}

							foreach($data_b as $pay_stub_id => $raw_row) {
								$tmp_rows[$x]['user_id'] = $user_id;
								$tmp_rows[$x]['first_name'] = $user_obj->getFirstName();
								$tmp_rows[$x]['middle_name'] = $user_obj->getMiddleName();
								$tmp_rows[$x]['middle_initial'] = $user_obj->getMiddleInitial();
								$tmp_rows[$x]['last_name'] = $user_obj->getLastName();
								$tmp_rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
								$tmp_rows[$x]['employee_number'] = $user_obj->getEmployeeNumber();
								//$tmp_rows[$x]['province'] = Option::getByKey($user_obj->getProvince(), $user_obj->getCompanyObject()->getOptions('province', $user_obj->getCountry() ) );
								//$tmp_rows[$x]['country'] = Option::getByKey($user_obj->getCountry(), $user_obj->getCompanyObject()->getOptions('country') );
								$tmp_rows[$x]['province'] = $user_obj->getProvince();
								$tmp_rows[$x]['country'] = $user_obj->getCountry();

								$tmp_rows[$x]['pay_period'] = Option::getByKey($raw_row['pay_period_id'], $pay_period_options, NULL );
								$tmp_rows[$x]['pay_period_order'] = Option::getByKey($raw_row['pay_period_id'], $pay_period_end_dates, NULL );

								$tmp_rows[$x]['pay_stub_start_date_order'] = $raw_row['pay_stub_start_date'];
								$tmp_rows[$x]['pay_stub_end_date_order'] = $raw_row['pay_stub_end_date'];
								$tmp_rows[$x]['pay_stub_transaction_order'] = $raw_row['pay_stub_transaction_date'];

								$tmp_rows[$x]['pay_stub_start_date'] = TTDate::getDate('DATE', $raw_row['pay_stub_start_date'] );
								$tmp_rows[$x]['pay_stub_end_date'] = TTDate::getDate('DATE', $raw_row['pay_stub_end_date'] );
								$tmp_rows[$x]['pay_stub_transaction_date'] = TTDate::getDate('DATE', $raw_row['pay_stub_transaction_date'] );

								$tmp_rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
								$tmp_rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options );
								$tmp_rows[$x]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
								$tmp_rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );

								$sin_number = NULL;
								if ( $permission->Check('user','view_sin') == TRUE ) {
									$sin_number = $user_obj->getSIN();
								} else {
									$sin_number = $user_obj->getSecureSIN();
								}

								$tmp_rows[$x]['sin'] = $sin_number;
								$tmp_rows[$x]['birth_date_order'] = $user_obj->getBirthDate();
								$tmp_rows[$x]['birth_date'] = TTDate::getDate('DATE', $user_obj->getBirthDate() );

								$tmp_rows[$x]['hire_date_order'] = $user_obj->getHireDate();
								$tmp_rows[$x]['hire_date'] = TTDate::getDate('DATE', $user_obj->getHireDate() );
								$tmp_rows[$x]['since_hire_date'] = TTDate::getHumanTimeSince( $user_obj->getHireDate() );

								$tmp_rows[$x]['termination_date_order'] = $user_obj->getTerminationDate();
								$tmp_rows[$x]['termination_date'] = TTDate::getDate('DATE', $user_obj->getTerminationDate() );

								if ( isset($ba_obj ) ) {
									$tmp_rows[$x]['institution'] = $ba_obj->getInstitution();
									$tmp_rows[$x]['transit'] = $ba_obj->getTransit();
									$tmp_rows[$x]['account'] = $ba_obj->getAccount();
								} else {
									$tmp_rows[$x]['institution'] = NULL;
									$tmp_rows[$x]['transit'] = NULL;
									$tmp_rows[$x]['account'] = NULL;
								}

								$tmp_rows[$x]['currency'] = $tmp_rows[$x]['current_currency'] = Option::getByKey( $raw_row['currency_id'], $currency_options );
								if ( $currency_convert_to_base == TRUE ) {
									$tmp_rows[$x]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
								}

								foreach($raw_row['pay_stub_entry_name'] as $id => $amount ) {
									//$tmp_rows[$x][$id] = $amount;
									$tmp_rows[$x][$id] = $base_currency_obj->getBaseCurrencyAmount( $amount, $raw_row['currency_rate'], $currency_convert_to_base );
								}
								unset($id, $amount);

								$x++;
							}
							unset($ba_obj);
						}
					}
					//var_dump($rows);

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

						$tmp_rows = Misc::ArrayGroupBy( $tmp_rows, array(Misc::trimSortPrefix($filter_data['primary_group_by']),Misc::trimSortPrefix($filter_data['secondary_group_by'])), Misc::trimSortPrefix($ignore_elements, TRUE) );
					}

					if ( isset($tmp_rows) ) {
						foreach($tmp_rows as $row) {
							$rows[] = $row;
						}

						$special_sort_columns = array('pay_period', 'pay_stub_start_date', 'pay_stub_end_date', 'pay_stub_transaction_date');
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
					}

					foreach( $filter_data['column_ids'] as $column_key ) {
						$filter_columns[Misc::trimSortPrefix($column_key)] = $report_columns[$column_key];
					}
				}
			}
		}

		if ( $action == 'export' AND $filter_data['export_type'] == 'csv' ) {
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

			$smarty->display('report/PayStubSummaryReport.tpl');
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
				//$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys((array)$pay_period_options)) );
				$filter_data['transaction_start_date'] = $default_transaction_start_date;
				$filter_data['transaction_end_date'] = $default_transaction_end_date;
				$filter_data['group_ids'] = array( -1 );
				$filter_data['currency_ids'] = array( -1 );
				$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys($pay_period_options)) );
				//$filter_data['primary_group_by'] = '-1000-full_name';

				$default_columns = array( 5 => '-1000-full_name', 6 => '-1090-pay_period' );

				$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
				$pseallf->getByCompanyId( $current_company->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pseal_obj = $pseallf->getCurrent();

					$default_linked_columns = array(
												$pseal_obj->getTotalGross(),
												$pseal_obj->getTotalNetPay(),
												$pseal_obj->getTotalEmployeeDeduction(),
												$pseal_obj->getTotalEmployerDeduction() );
				} else {
					$default_linked_columns = array();
				}

				$filter_data['column_ids'] = Misc::prependArray( $default_columns, $default_linked_columns );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1120-pay_stub_transaction_date';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'currency_ids', 'column_ids' ), NULL );

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

		//Get pay periods
		//$pplf = TTnew( 'PayPeriodListFactory' );
		//$pplf->getPayPeriodsWithPayStubsByCompanyId( $current_company->getId() );
		$pay_period_options = Misc::prependArray( $all_array_option, $pplf->getArrayByListFactory( $pplf, FALSE, TRUE ) );
		$filter_data['src_pay_period_options'] = Misc::arrayDiffByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );
		$filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );

		//Get currencies
		$crlf = TTnew( 'CurrencyListFactory' );
		$crlf->getByCompanyId( $current_company->getId() );
		$currency_options = Misc::prependArray( $all_array_option, $crlf->getArrayByListFactory( $crlf, FALSE, TRUE ) );
		$filter_data['src_currency_options'] = Misc::arrayDiffByKey( (array)$filter_data['currency_ids'], $currency_options );
		$filter_data['selected_currency_options'] = Misc::arrayIntersectByKey( (array)$filter_data['currency_ids'], $currency_options );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );


		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$filter_data['group_by_options'] = Misc::prependArray( array('0' => TTi18n::gettext('No Grouping')), $static_columns );

		$psf = TTnew( 'PayStubFactory' );
		$filter_data['export_type_options'] = Misc::prependArray( array( 'csv' => TTi18n::gettext('CSV (Excel)') ), Misc::trimSortPrefix( $psf->getOptions('export_type') ) );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/PayStubSummary.tpl');

		break;
}
?>