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
 * $Revision: 4258 $
 * $Id: GenericTaxSummary.php 4258 2011-02-16 20:46:59Z ipso $
 * $Date: 2011-02-16 12:46:59 -0800 (Wed, 16 Feb 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_generic_tax_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Generic Tax Summary Report')); // See index.php


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

//Make a "Quarter" drop down box that is just JS, and it just places the proper
//start/end dates in the start/end date column!

$static_columns = array(

											'-1000-full_name' => TTi18n::gettext('Full Name'),
											'-1005-sin' => TTi18n::gettext('SIN/SSN'),
											'-1010-title' => TTi18n::gettext('Title'),
											'-1020-province' => TTi18n::gettext('Province/State'),
											'-1030-country' => TTi18n::gettext('Country'),
											'-1039-group' => TTi18n::gettext('Group'),
											'-1040-default_branch' => TTi18n::gettext('Default Branch'),
											'-1050-default_department' => TTi18n::gettext('Default Department'),
											'-1060-transaction_date' => TTi18n::gettext('Transaction Date'),
											);

$columns = array(
											'-1070-subject_wages' => TTi18n::gettext('Subject Wages'),
											'-1080-taxable_wages' => TTi18n::gettext('Taxable Wages'),
											'-1090-tax_withheld' => TTi18n::gettext('Tax Withheld'),
											);

$columns = Misc::prependArray( $static_columns, $columns);

if ( isset($filter_data['start_date']) ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
}

if ( isset($filter_data['end_date']) ) {
	$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('company_deduction_ids', 'include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array() );

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();

switch ($action) {
	case 'export':
	case 'display_report':
		//Debug::setVerbosity(11);

		Debug::Text('Submit! Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'aFilter Data', __FILE__, __LINE__, __METHOD__,10);

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			//Get total gross pay stub account IDs
			$cdf = TTnew( 'CompanyDeductionFactory' );
			$cdf->setCompany( $current_company->getId() );
			$total_gross_psea_ids = $cdf->getExpandedPayStubEntryAccountIDs( $cdf->getPayStubEntryAccountLinkObject()->getTotalGross() );
			//var_dump($total_gross_psea_ids);

			//Get include/exclude IDs for company deduction.
			$cdlf = TTnew( 'CompanyDeductionListFactory' );
			$cdlf->getByCompanyIdAndId( $current_company->getId(), $filter_data['company_deduction_ids'] );
			if ( $cdlf->getRecordCount() > 0 ) {
				$taxable_wages_psea_ids = array();
				$tax_withheld_psea_ids = array();
				Debug::Text('Found Company Deductions...', __FILE__, __LINE__, __METHOD__,10);
				foreach( $cdlf as $cd_obj ) {
					$taxable_wages_psea_ids = array_merge( $taxable_wages_psea_ids, (array)$cd_obj->getCombinedIncludeExcludePayStubEntryAccount( $cd_obj->getIncludePayStubEntryAccount(),  $cd_obj->getExcludePayStubEntryAccount() ) );
					$tax_withheld_psea_ids[] = $cd_obj->getPayStubEntryAccount();
				}
				$taxable_wages_psea_ids = array_unique( $taxable_wages_psea_ids );
				$tax_withheld_psea_ids = array_unique( $tax_withheld_psea_ids );
			}
			//var_dump($taxable_wages_psea_ids);


			foreach( $ulf as $u_obj ) {
				$filter_data['user_ids'][] = $u_obj->getId();
			}

			//Get all pay periods by transaction start/end date
			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByCompanyIdAndTransactionStartDateAndTransactionEndDate( $current_company->getId(), $filter_data['start_date'], $filter_data['end_date']);
			if ( $pplf->getRecordCount() > 0 ) {
				foreach( $pplf as $pp_obj ) {
					$pay_period_ids[] = $pp_obj->getId();
				}
			}
			unset($pplf, $pp_obj);

			if ( isset($pay_period_ids) AND isset($filter_data['user_ids']) ) {
				//Get column headers
				/*
				$psealf = TTnew( 'PayStubEntryAccountListFactory' );
				$psealf->getByCompanyId( $current_company->getId() );
				foreach($psealf as $psea_obj) {
					//$report_columns[$psen_obj->getId()] = $psen_obj->getDescription();
					$report_columns[$psea_obj->getId()] = $psea_obj->getName();
				}
				//var_dump($report_columns);

				$report_columns = Misc::prependArray( $static_columns, $report_columns);
				*/

				$pself = TTnew( 'PayStubEntryListFactory' );
				$pself->getDateReportByCompanyIdAndUserIdAndPayPeriodId( $current_company->getId(), $filter_data['user_ids'], $pay_period_ids );

				//Prepare data for regular report.
				foreach( $pself as $pse_obj ) {
					$user_id = $pse_obj->getColumn('user_id');
					$transaction_date = TTDate::strtotime( $pse_obj->getColumn('transaction_date') );
					$pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');

					$raw_rows[$transaction_date][$user_id][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}
				unset($transaction_date, $user_id, $pay_stub_entry_name_id);
				//var_dump($raw_rows);

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
					foreach($raw_rows as $transaction_date => $raw_row_a) {

						foreach($raw_row_a as $user_id => $raw_row_b) {
							$user_obj = $ulf->getById( $user_id )->getCurrent();

							if ( $filter_data['transaction_date_format'] == 20 ) {
								$transaction_date_display = date('m', $transaction_date).' - '.date('F', $transaction_date);
							} elseif ( $filter_data['transaction_date_format'] == 30 ) { //Quarter
								$transaction_date_display = 'Quarter '.ceil( date('m', $transaction_date) / 3);
							} elseif ( $filter_data['transaction_date_format'] == 40 ) { //Year
								$transaction_date_display = date('Y', $transaction_date);
							} else {
								$transaction_date_display = TTDate::getDate('DATE', $transaction_date);
							}

							$tmp_rows[$x]['transaction_date'] = $transaction_date_display;
							$tmp_rows[$x]['user_id'] = $user_id;
							$tmp_rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
							//$tmp_rows[$x]['province'] = Option::getByKey($user_obj->getProvince(), $user_obj->getCompanyObject()->getOptions('province', $user_obj->getCountry() ) );
							//$tmp_rows[$x]['country'] = Option::getByKey($user_obj->getCountry(), $user_obj->getCompanyObject()->getOptions('country') );
							$tmp_rows[$x]['province'] = $user_obj->getProvince();
							$tmp_rows[$x]['country'] = $user_obj->getCountry();

							$tmp_rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
							$tmp_rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options );
							$tmp_rows[$x]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
							$tmp_rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );
							/*
							$tmp_rows[$x]['title'] = $title_options[$user_obj->getTitle()];
							$tmp_rows[$x]['default_branch'] = $branch_options[$user_obj->getDefaultBranch()];
							$tmp_rows[$x]['default_department'] = $department_options[$user_obj->getDefaultDepartment()];
							*/

							$tmp_rows[$x]['sin'] = $user_obj->getSIN();

							$total_gross_amount = 0;
							$taxable_wages_amount = 0;
							$tax_withheld_amount = 0;
							foreach($raw_row_b as $pay_stub_entry_name_id => $amount ) {
								if ( isset($total_gross_psea_ids) AND is_array($total_gross_psea_ids) AND in_array($pay_stub_entry_name_id, $total_gross_psea_ids ) ) {
									//Debug::Text('Total Gross Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
									$total_gross_amount += $amount;
								}

								if ( isset($taxable_wages_psea_ids) AND is_array($taxable_wages_psea_ids) AND in_array($pay_stub_entry_name_id, $taxable_wages_psea_ids ) ) {
									//Debug::Text('Total Taxable Wages Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
									$taxable_wages_amount += $amount;
								}

								if ( isset($tax_withheld_psea_ids) AND is_array($tax_withheld_psea_ids) AND in_array($pay_stub_entry_name_id, $tax_withheld_psea_ids ) ) {
									//Debug::Text('Total Tax Withheld Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
									$tax_withheld_amount += $amount;
								}
								//Debug::Text('Total: Gross: '. $total_gross_amount  .'  Taxable Wages: '. $taxable_wages_amount .' Tax Withheld: '. $tax_withheld_amount .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
								//$tmp_rows[$x][$pay_stub_entry_name_id] = $amount;
							}
							unset($$pay_stub_entry_name_id, $amount);

							$tmp_rows[$x]['subject_wages'] = Misc::MoneyFormat( $total_gross_amount, FALSE );
							$tmp_rows[$x]['taxable_wages'] = Misc::MoneyFormat( $taxable_wages_amount, FALSE );
							$tmp_rows[$x]['tax_withheld'] = Misc::MoneyFormat( $tax_withheld_amount, FALSE );

							$x++;
						}
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
					$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
				}

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
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/GenericTaxSummaryReport.tpl');
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
				$filter_data['start_date'] = TTDate::getBeginMonthEpoch();
				$filter_data['end_date'] = TTDate::getEndMonthEpoch();

				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				//$filter_data['company_deduction_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );

				$filter_data['transaction_date_format'] = 10;

				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}

				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-full_name',
											'-1060-transaction_date',
											'-1070-subject_wages',
											'-1080-taxable_wages',
											'-1090-tax_withheld',
												) );

				$filter_data['primary_group_by'] = '-1060-transaction_date';

				$filter_data['primary_sort'] = '-1060-transaction_date';
				$filter_data['secondary_sort'] = '-1000-full_name';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('company_deduction_ids', 'include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL );

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

		//Get Company Tax Deductions
		$cdlf = TTnew( 'CompanyDeductionListFactory' );
		$cdlf->getByCompanyIdAndTypeId( $current_company->getId(), 10 );
		//$company_deduction_options = Misc::prependArray( $all_array_option, $cdlf->getArrayByListFactory( $cdlf, FALSE, TRUE ) );
		$company_deduction_options = $cdlf->getArrayByListFactory( $cdlf, FALSE, TRUE );
		$filter_data['src_company_deduction_options'] = Misc::arrayDiffByKey( (array)$filter_data['company_deduction_ids'], $company_deduction_options );
		$filter_data['selected_company_deduction_options'] = Misc::arrayIntersectByKey( (array)$filter_data['company_deduction_ids'], $company_deduction_options );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );


		//Get transaction date format options
		$filter_data['transaction_date_format_options'] = array(
																10 => TTi18n::gettext('Complete Date'),
																20 => TTi18n::gettext('Month'),
																30 => TTi18n::gettext('Quarter'),
																40 => TTi18n::gettext('Year'),
																);
		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();


		$filter_data['group_by_options'] = Misc::prependArray( array('0' => TTi18n::gettext('No Grouping')), $static_columns );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/GenericTaxSummary.tpl');

		break;
}
?>