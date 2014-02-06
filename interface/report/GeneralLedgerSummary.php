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
 * $Revision: 5711 $
 * $Id: GeneralLedgerSummary.php 5711 2011-12-06 23:08:45Z ipso $
 * $Date: 2011-12-06 15:08:45 -0800 (Tue, 06 Dec 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_general_ledger_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'General Ledger Summary Report')); // See index.php

function replaceGLAccountVariables( $subject, $replace_arr = NULL) {
	$search_arr = array(
						'#default_branch#',
						'#default_branch_other_id1#',
						'#default_branch_other_id2#',
						'#default_branch_other_id3#',
						'#default_branch_other_id4#',
						'#default_branch_other_id5#',
						'#default_department#',
						'#default_department_other_id1#',
						'#default_department_other_id2#',
						'#default_department_other_id3#',
						'#default_department_other_id4#',
						'#default_department_other_id5#',
						'#employee_number#',
						'#employee_other_id1#',
						'#employee_other_id2#',
						'#employee_other_id3#',
						'#employee_other_id4#',
						'#employee_other_id5#',
						);

	if ( $subject != '' AND is_array($replace_arr) ) {
		$subject = str_replace( $search_arr, $replace_arr, $subject );
	}

	return $subject;
}

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
											'-1010-title' => TTi18n::gettext('Title'),
											'-1020-province' => TTi18n::gettext('Province'),
											'-1030-country' => TTi18n::gettext('Country'),
											'-1040-default_branch' => TTi18n::gettext('Default Branch'),
											'-1050-default_department' => TTi18n::gettext('Default Department'),
											);

//$static_columns = array();

$psealf = TTnew( 'PayStubEntryAccountListFactory' );
/*

$psen_columns = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,60,65), FALSE );


$columns = Misc::prependArray( $static_columns, $psen_columns);
*/
$psen_columns = array();

$columns = $static_columns;

//Get all pay periods
$pplf = TTnew( 'PayPeriodListFactory' );
$pplf->getByCompanyId( $current_company->getId() );
if ( $pplf->getRecordCount() > 0 ) {
	foreach ($pplf as $pay_period_obj) {
		$pay_period_ids[] = $pay_period_obj->getId();
	}
	$pplf = TTnew( 'PayPeriodListFactory' );
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'currency_ids', 'column_ids' ), array() );

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();

switch ($action) {
	case 'export':
	case 'display_report':
		//Debug::setVerbosity(11);

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'aFilter Data', __FILE__, __LINE__, __METHOD__,10);
/*
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$filter_data['user_ids'][] = $u_obj->getId();
			}
*/
			//Trim sort prefix from selected pay periods.
			$tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
			$filter_data['pay_period_ids'] = array();
			foreach( $tmp_filter_pay_period_ids as $key => $filter_pay_period_id) {
				$filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
			}
			unset($key, $tmp_filter_pay_period_ids, $filter_pay_period_id);

			//if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['user_ids']) ) {
			if ( isset($filter_data['pay_period_ids']) ) {
				//Get column headers
				$psealf->getByCompanyId( $current_company->getId() );
				foreach($psealf as $psea_obj) {
					//$report_columns[$psen_obj->getId()] = $psen_obj->getDescription();
					$report_columns[$psea_obj->getId()] = $psea_obj->getName();
				}
				//var_dump($report_columns);
				$report_columns = Misc::prependArray( $static_columns, $report_columns);

				$psealf = TTnew( 'PayStubEntryAccountListFactory' );
				$psealf->getByCompanyId( $current_company->getId() );
				if ( $psealf->getRecordCount() > 0 ) {
					foreach($psealf as $psea_obj) {
						$psea_arr[$psea_obj->getId()] = array(
																	'name' => $psea_obj->getName(),
																	'debit_account' => $psea_obj->getDebitAccount(),
																	'credit_account' => $psea_obj->getCreditAccount(),
																	);
					}
				}
				//var_dump($psea_arr);

				//Get all pay stubs.
				$pslf = TTnew( 'PayStubListFactory' );
				$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
				if ( $pslf->getRecordCount() > 0 ) {
					$ulf = TTnew( 'UserListFactory' );

					$blf = TTnew( 'BranchListFactory' );
					$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

					//Get Branch ID to Branch Code mapping
					$branch_code_map = array( 0 => 0 );
					$blf->getByCompanyId( $current_company->getId() );
					if ( $blf->getRecordCount() > 0 ) {
						foreach( $blf as $b_obj ) {
							//$branch_code_map[$b_obj->getId()] = $b_obj->getManualID();
							$branch_code_map[$b_obj->getId()] = $b_obj;
						}
					}

					$dlf = TTnew( 'DepartmentListFactory' );
					$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

					//Get Department ID to Branch Code mapping
					$department_code_map = array( 0 => 0 );
					$dlf->getByCompanyId( $current_company->getId() );
					if ( $dlf->getRecordCount() > 0 ) {
						foreach( $dlf as $d_obj ) {
							//$department_code_map[$d_obj->getId()] = $d_obj->getManualID();
							$department_code_map[$d_obj->getId()] = $d_obj;
						}
					}

					$utlf = TTnew( 'UserTitleListFactory' );
					$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

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

					foreach( $pslf as $ps_obj ) {
						$user_obj = $ulf->getById( $ps_obj->getUser() )->getCurrent();

						$replace_arr = array(
												( is_object($branch_code_map[(int)$user_obj->getDefaultBranch()]) ) ? $branch_code_map[(int)$user_obj->getDefaultBranch()]->getManualID() : NULL,
												( is_object($branch_code_map[(int)$user_obj->getDefaultBranch()]) ) ? $branch_code_map[(int)$user_obj->getDefaultBranch()]->getOtherID1() : NULL,
												( is_object($branch_code_map[(int)$user_obj->getDefaultBranch()]) ) ? $branch_code_map[(int)$user_obj->getDefaultBranch()]->getOtherID2() : NULL,
												( is_object($branch_code_map[(int)$user_obj->getDefaultBranch()]) ) ? $branch_code_map[(int)$user_obj->getDefaultBranch()]->getOtherID3() : NULL,
												( is_object($branch_code_map[(int)$user_obj->getDefaultBranch()]) ) ? $branch_code_map[(int)$user_obj->getDefaultBranch()]->getOtherID4() : NULL,
												( is_object($branch_code_map[(int)$user_obj->getDefaultBranch()]) ) ? $branch_code_map[(int)$user_obj->getDefaultBranch()]->getOtherID5() : NULL,
												( is_object($department_code_map[(int)$user_obj->getDefaultDepartment()]) ) ? $department_code_map[(int)$user_obj->getDefaultDepartment()]->getManualID() : NULL,
												( is_object($department_code_map[(int)$user_obj->getDefaultDepartment()]) ) ? $department_code_map[(int)$user_obj->getDefaultDepartment()]->getOtherID1() : NULL,
												( is_object($department_code_map[(int)$user_obj->getDefaultDepartment()]) ) ? $department_code_map[(int)$user_obj->getDefaultDepartment()]->getOtherID2() : NULL,
												( is_object($department_code_map[(int)$user_obj->getDefaultDepartment()]) ) ? $department_code_map[(int)$user_obj->getDefaultDepartment()]->getOtherID3() : NULL,
												( is_object($department_code_map[(int)$user_obj->getDefaultDepartment()]) ) ? $department_code_map[(int)$user_obj->getDefaultDepartment()]->getOtherID4() : NULL,
												( is_object($department_code_map[(int)$user_obj->getDefaultDepartment()]) ) ? $department_code_map[(int)$user_obj->getDefaultDepartment()]->getOtherID5() : NULL,
												$user_obj->getEmployeeNumber(),
												$user_obj->getOtherID1(),
												$user_obj->getOtherID2(),
												$user_obj->getOtherID3(),
												$user_obj->getOtherID4(),
												$user_obj->getOtherID5()
											);

						$je_records = NULL;

						//Get all PS Entries for this pay stub.
						$pself = TTnew( 'PayStubEntryListFactory' );
						$pself->getByPayStubIdAndYTDAdjustment( $ps_obj->getId(), FALSE ); //Skip any YTD adjustments, as they aren't needed in the GL
						if ( $pself->getRecordCount() > 0 ) {
							Debug::Text('Found Pay Stub Entries for PS ID:'. $ps_obj->getId() , __FILE__, __LINE__, __METHOD__,10);
							foreach ($pself as $pse_obj) {
								Debug::Text('Pay Stub Entry ID:'. $pse_obj->getId() .' PSE Account ID: '. $pse_obj->getPayStubEntryNameId() .' Currency Rate: '. $ps_obj->getCurrencyRate() .' Amount: '. $pse_obj->getAmount(), __FILE__, __LINE__, __METHOD__,10);

								if ( isset($psea_arr[$pse_obj->getPayStubEntryNameId()]) ) {
									if ( isset($psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'])
											AND $psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'] != '' ) {
										$debit_accounts = explode(',', $psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'] );

										foreach( $debit_accounts as $debit_account ) {
											$debit_account = replaceGLAccountVariables( $debit_account, $replace_arr);
											Debug::Text('Debit Entry: Account: '. $debit_account .' Amount: '. $pse_obj->getAmount() , __FILE__, __LINE__, __METHOD__,10);

											//Allow negative amounts, but skip any $0 entries
											if ( $pse_obj->getAmount() != 0 ) {
												$raw_je_records[] = array(
																	'type' => 'debit',
																	'account' => $debit_account,
																	'amount' => Misc::MoneyFormat( $base_currency_obj->getBaseCurrencyAmount( $pse_obj->getAmount(), $ps_obj->getCurrencyRate(), $currency_convert_to_base ), FALSE ),
																	);
											}
										}
										unset($debit_accounts, $debit_account);
									}

									if ( isset($psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'])
											AND $psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'] != '' ) {

										$credit_accounts = explode(',', $psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'] );
										Debug::Text('Combined Credit Accounts: '. count($credit_accounts) , __FILE__, __LINE__, __METHOD__,10);
										foreach( $credit_accounts as $credit_account) {
											$credit_account = replaceGLAccountVariables( $credit_account, $replace_arr);

											//Allow negative amounts, but skip any $0 entries
											if ( $pse_obj->getAmount() != 0 ) {
												$raw_je_records[] = array(
																	'type' => 'credit',
																	'account' => $credit_account,
																	'amount' => Misc::MoneyFormat( $base_currency_obj->getBaseCurrencyAmount( $pse_obj->getAmount(), $ps_obj->getCurrencyRate(), $currency_convert_to_base ), FALSE ),
																	);
											}
										}
										unset($credit_accounts, $credit_account);

									}

								} else {
									Debug::Text('No Pay Stub Entry Account Matches!', __FILE__, __LINE__, __METHOD__,10);
								}
							}

							if ( isset($raw_je_records) ) {
								//Group JE records by type then account.
								//var_dump($raw_je_records);
								$grouped_je_records = Misc::ArrayGroupBy( $raw_je_records, array('type', 'account'), array() );
								unset($raw_je_records);

								//Total each JE, so we can tell if they balance or not.
								$total_je_records['type'] = 'total';
								$total_je_records['account'] = NULL;
								$total_je_records['amount'] = NULL;
								foreach ( $grouped_je_records as $grouped_je_record ) {
									if ( isset($total_je_records[$grouped_je_record['type']]) ) {
										$total_je_records[$grouped_je_record['type']] = bcadd( $total_je_records[$grouped_je_record['type']], $grouped_je_record['amount'] );
									} else {
										$total_je_records[$grouped_je_record['type']] = $grouped_je_record['amount'];
									}

									if ( isset($total_je_records['debit'])
											AND isset($total_je_records['credit']) ) {
										$total_je_records['diff'] = bcsub($total_je_records['debit'],$total_je_records['credit']);
									}
								}

								$grouped_je_records['total'] = $total_je_records;

								foreach($grouped_je_records as $je_record) {
									$tmp_arr = array(
														'user_id' => $user_obj->getId(),
														'full_name' => $user_obj->getFullName(TRUE),
														'transaction_date' => $ps_obj->getTransactionDate(),
														'pay_stub_id' => $ps_obj->getId(),
														'pay_period_id' => $ps_obj->getPayPeriod(),
														'province' => $user_obj->getProvince(),
														'country' => $user_obj->getCountry(),
														'title' => $title_options[$user_obj->getTitle()],
														'default_branch_id' => $user_obj->getDefaultBranch(),
														'default_branch' => $branch_options[$user_obj->getDefaultBranch()],
														'default_department_id' => $user_obj->getDefaultDepartment(),
														'default_department' => $department_options[$user_obj->getDefaultDepartment()],
														'type' => $je_record['type'],
														'account' => $je_record['account'],
														'amount' => $je_record['amount'],
														);

									if ( $je_record['type'] == 'total' AND isset($je_record['debit']) AND isset($je_record['credit']) ) {
										$tmp_arr['total_debits'] = Misc::MoneyFormat( $je_record['debit'], FALSE);
										$tmp_arr['total_credits'] = Misc::MoneyFormat( $je_record['credit'], FALSE);
										$tmp_arr['total_diff'] = Misc::MoneyFormat( $je_record['diff'], FALSE);
									}

									$tmp_rows[] = $tmp_arr;
								}

								//var_dump($tmp_rows);
								unset($grouped_je_records, $total_je_records);
							}
						}
					}

					if ( isset($tmp_rows) AND isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
						Debug::Text('Grouping Data By: '. $filter_data['primary_group_by'], __FILE__, __LINE__, __METHOD__,10);

						$ignore_elements = array_keys($static_columns);

						$tmp_rows = Misc::ArrayGroupBy( $tmp_rows, array('transaction_date', 'type', 'account', Misc::trimSortPrefix($filter_data['primary_group_by']) ), $ignore_elements );
						//var_dump($tmp_rows);

						$final_group_key = Misc::trimSortPrefix($filter_data['primary_group_by']);
					} else {
						$final_group_key = 'user_id';
					}

					Debug::Text('Final Group Key: '. $final_group_key, __FILE__, __LINE__, __METHOD__,10);

					if ( isset($tmp_rows) ) {
						foreach($tmp_rows as $row) {
							$rows[] = $row;
						}

						//$rows = Sort::Multisort($rows, $filter_data['primary_sort'], NULL, 'ASC');
						$rows = Sort::Multisort($rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

						//Split to journal entries and JE records after everything has been grouped
						//and sorted.
						foreach( $rows as $row) {
							if ( !isset($final_rows[$row['transaction_date']][$row[$final_group_key]]) ) {
								if ( $final_group_key == 'user_id' ) {
									$source = $row['pay_stub_id'];
									$comment = $row['full_name'];
								} else {
									if ( $row[$final_group_key] == '--' ) {
										$source = TTi18n::gettext('TimeTrex');
									} else {
										$source = $row[$final_group_key];
									}
									if ( $row[$final_group_key] == '--' ) {
										$comment = TTi18n::gettext('Payroll');
									} else {
										$comment = $row[$final_group_key];
									}
								}

								if ( $currency_convert_to_base == TRUE ) {
									$comment .= ' ['. $base_currency_obj->getISOCode() .']';
								}

								$final_rows[$row['transaction_date']][$row[$final_group_key]] = array(
																							'source' => $source,
																							'comment' => $comment,
																							'transaction_date' => $row['transaction_date'],
																							//'records' => NULL
																							);
							}

							if ( $row['type'] == 'total' ) {
								$final_rows[$row['transaction_date']][$row[$final_group_key]]['records'][$row['type']][] = array(
																										'type' => $row['type'],
																										'account' => $row['account'],
																										'amount' => $row['amount'],
																										'total_debits' => $row['total_debits'],
																										'total_credits' => $row['total_credits'],
																										'total_diff' => $row['total_diff']
																										);
							} else {
								$final_rows[$row['transaction_date']][$row[$final_group_key]]['records'][$row['type']][] = array(
																										'type' => $row['type'],
																										'account' => $row['account'],
																										'amount' => $row['amount'],
																										);
							}
						}
						unset($rows);
						//var_dump($final_rows); //

						if ( $action == 'export' ) {
							$gle = new GeneralLedgerExport();
							$gle->setFileFormat( $filter_data['export_type'] );
						}

						//Flatten final rows
						foreach( $final_rows as $final_row_a ) {
							foreach( $final_row_a as $final_row_b ) {

								if ( $action == 'export' ) {
									$je = new GeneralLedgerExport_JournalEntry();
									$je->setDate( $final_row_b['transaction_date'] );

									if ( $final_row_b['source'] == '--' ) {
										$final_row_b['source'] = 'TimeTrex';
									}
									if ( $final_row_b['comment'] == '--' ) {
										$final_row_b['comment'] = 'Payroll';
									}
									$je->setSource( $final_row_b['source'] );
									$je->setComment( $final_row_b['comment'] );

									if ( isset($final_row_b['records'] ) ) {
										foreach( $final_row_b['records'] as $type => $je_records ) {
											foreach( $je_records as $je_record ) {
												$record = new GeneralLedgerExport_Record();
												$record->setAccount( $je_record['account'] );
												$record->setType( $type );
												$record->setAmount( $je_record['amount'] );
												$je->setRecord($record);
											}

										}
									}
									unset($type, $je_records, $je_record, $record);
									$gle->setJournalEntry($je);
								}

								$rows[] = $final_row_b;
							}
						}
						//var_dump($rows);
					}

				}
				//var_dump($tmp_rows);
			}
		//}

		if ( $action == 'export' ) {
			if ( isset($rows) AND isset($gle) AND isset($filter_data['export_type']) ) {
				if ( $gle->compile() == TRUE ) {

					$data = $gle->getCompiledData();
					Debug::Text('Exporting as: '. $filter_data['export_type'], __FILE__, __LINE__, __METHOD__,10);

					if ( $filter_data['export_type'] == 'simply' ) {
						$file_name = 'general_ledger_'. str_replace( array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.txt';
					} else {
						$file_name = 'general_ledger_'. str_replace( array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.csv';
					}

					Misc::FileDownloadHeader($file_name, 'application/text', strlen($data));
					echo $data;
				} else {
					echo "One or more journal entries did not balance!<br>\n";
					//Debug::Display();
				}
			} else {
				echo TTi18n::gettext('No Data To Export!') ."<br>\n";
			}
		} else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/GeneralLedgerSummaryReport.tpl');
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
				$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys($pay_period_options)) );
				$filter_data['group_ids'] = array( -1 );
				$filter_data['currency_ids'] = array( -1 );

				$default_columns = array( 5 => '-1000-full_name' );

				$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
				$pseallf->getByCompanyId( $current_company->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pseal_obj = $pseallf->getCurrent();

					$default_linked_columns = array(
												$pseal_obj->getTotalGross(),
												$pseal_obj->getTotalNetPay(),
												$pseal_obj->getTotalEmployeeDeduction(),
												$pseal_obj->getTotalEmployerDeduction() );

					$filter_data['secondary_sort'] = $pseal_obj->getTotalGross();
				} else {
					$default_linked_columns = array();
				}

				$filter_data['column_ids'] = Misc::prependArray( $default_columns, $default_linked_columns );

				$filter_data['primary_sort'] = '-1000-full_name';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'currency_ids', 'column_ids' ), NULL );

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
		//$filter_data['sort_options']['effective_date_order'] = 'Wage Effective Date';
		unset($filter_data['sort_options']['effective_date']);
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$filter_data['group_by_options'] = Misc::prependArray( array('0' => TTi18n::gettext('No Grouping')), $static_columns );

		$filter_data['export_type_options'] = array(
													'csv' => TTi18n::gettext('CSV (Excel)'),
													'simply' => TTi18n::gettext('Simply Accounting GL'),
													);

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/GeneralLedgerSummary.tpl');

		break;
}
?>
