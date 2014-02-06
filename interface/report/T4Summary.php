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
 * $Revision: 4247 $
 * $Id: T4Summary.php 4247 2011-02-15 21:40:19Z ipso $
 * $Date: 2011-02-15 13:40:19 -0800 (Tue, 15 Feb 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_t4_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'T4 Summary Report')); // See index.php

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
									'-1060-sin' => TTi18n::gettext('SIN')
									);

$non_static_columns = array(		'-1100-income' => TTi18n::gettext('Income (14)'),
									'-1110-income_tax' => TTi18n::gettext('Income Tax (22)'),
									'-1120-employee_cpp' => TTi18n::gettext('Employee CPP (16)'),
									'-1125-ei_earnings' => TTi18n::gettext('EI Insurable Earnings (24)'),
									'-1126-cpp_earnings' => TTi18n::gettext('CPP Pensionable Earnings (26)'),
									'-1130-employee_ei' => TTi18n::gettext('Employee EI (18)'),
									'-1140-union_dues' => TTi18n::gettext('Union Dues (44)'),
									'-1150-employer_cpp' => TTi18n::gettext('Employer CPP'),
									'-1160-employer_ei' => TTi18n::gettext('Employer EI'),
									'-1170-rpp' => TTi18n::gettext('RPP Contributions (20)'),
									'-1180-charity' => TTi18n::gettext('Charity Donations (46)'),
									'-1190-pension_adjustment' => TTi18n::gettext('Pension Adjustment (52)'),
									'-1200-other_box_0' => TTi18n::gettext('Other Box 1'),
									'-1210-other_box_1' => TTi18n::gettext('Other Box 2'),
									'-1220-other_box_2' => TTi18n::gettext('Other Box 3'),
									'-1220-other_box_3' => TTi18n::gettext('Other Box 4'),
									'-1220-other_box_4' => TTi18n::gettext('Other Box 5'),
									'-1220-other_box_5' => TTi18n::gettext('Other Box 6'),
									);

$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
$pseallf->getByCompanyId( $current_company->getId() );
if ( $pseallf->getRecordCount() > 0 ) {
	$pseal_obj = $pseallf->getCurrent();
}

$column_ps_entry_name_map = array(
								'income' => @$setup_data['income_psea_ids'], //Gross Pay
								'income_tax' => @$setup_data['tax_psea_ids'],
								'employee_cpp' => @$setup_data['employee_cpp_psea_id'],
								'employee_ei' => @$setup_data['employee_ei_psea_id'],
								'ei_earnings' => @$setup_data['ei_earnings_psea_ids'],
								'cpp_earnings' => @$setup_data['cpp_earnings_psea_ids'],
								'union_dues' => @$setup_data['union_dues_psea_id'],
								'employer_cpp' => @$setup_data['employer_cpp_psea_id'],
								'employer_ei' => @$setup_data['employer_ei_psea_id'],
								'rpp' => @$setup_data['rpp_psea_ids'],
								'charity' => @$setup_data['charity_psea_ids'],
								'pension_adjustment' => @$setup_data['pension_adjustment_psea_ids'],
								'other_box_0' => @$setup_data['other_box'][0]['psea_ids'],
								'other_box_1' => @$setup_data['other_box'][1]['psea_ids'],
								'other_box_2' => @$setup_data['other_box'][2]['psea_ids'],
								'other_box_3' => @$setup_data['other_box'][3]['psea_ids'],
								'other_box_4' => @$setup_data['other_box'][4]['psea_ids'],
								'other_box_5' => @$setup_data['other_box'][5]['psea_ids'],
								);

$columns = Misc::prependArray( $static_columns, $non_static_columns);

$pplf = TTnew( 'PayPeriodListFactory' );
$year_options = $pplf->getYearsArrayByCompanyId( $current_company->getId() );

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array() );

$action = Misc::findSubmitButton();
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
switch ($action) {
	case 'display_t4s':
	case 'export_xml':
	case 'display_report':
	case 'export':
		//Debug::setVerbosity(11);

		Debug::Text('Submit!: '. $action, __FILE__, __LINE__, __METHOD__,10);
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

			if ( isset($filter_data['year']) AND isset($filter_data['user_ids']) ) {
				//Get all pay period IDs in year.
				if ( isset($filter_data['year']) ) {
					$year_epoch = mktime(0,0,0,1,1,$filter_data['year']);
					Debug::Text(' Year: '. TTDate::getDate('DATE+TIME', $year_epoch) , __FILE__, __LINE__, __METHOD__,10);
				}

				$pself = TTnew( 'PayStubEntryListFactory' );
				$pself->getReportByCompanyIdAndUserIdAndTransactionStartDateAndTransactionEndDate($current_company->getId(), $filter_data['user_ids'], TTDate::getBeginYearEpoch($year_epoch), TTDate::getEndYearEpoch($year_epoch) );

				$report_columns = $static_columns;

				foreach( $pself as $pse_obj ) {
					$user_id = $pse_obj->getColumn('user_id');
					$pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');

					$raw_rows[$user_id][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}
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
					foreach($raw_rows as $user_id => $raw_row) {
						$user_obj = $ulf->getById( $user_id )->getCurrent();

						$tmp_rows[$x]['user_id'] = $user_id;
						$tmp_rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
						//$tmp_rows[$x]['province'] = Option::getByKey($user_obj->getProvince(), $user_obj->getOptions('province') );
						//$tmp_rows[$x]['province'] = $user_obj->getProvince();

						$tmp_rows[$x]['province'] = $user_obj->getProvince();
						$tmp_rows[$x]['country'] = $user_obj->getCountry();

						$tmp_rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
						$tmp_rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options );
						$tmp_rows[$x]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
						$tmp_rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );

						$tmp_rows[$x]['sin'] = $user_obj->getSIN();

						foreach($column_ps_entry_name_map as $column_key => $ps_entry_map) {
							$tmp_rows[$x][$column_key] = Misc::MoneyFormat( Misc::sumMultipleColumns( $raw_rows[$user_id], $ps_entry_map), FALSE );
						}

						$x++;
					}
				}
				//var_dump($tmp_rows);

				//Skip grouping if they are displaying T4's
				if ( $action != 'display_t4s' AND isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
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

					//$rows = Sort::Multisort($rows, $filter_data['primary_sort'], NULL, 'ASC');
					$rows = Sort::Multisort($rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

					$total_row = Misc::ArrayAssocSum($rows, NULL, 2);

					$last_row = count($rows);
					$rows[$last_row] = $total_row;
					foreach ($static_columns as $static_column_key => $static_column_val) {
						Debug::Text('Clearing Column: '. $static_column_key, __FILE__, __LINE__, __METHOD__,10);
						$rows[$last_row][Misc::trimSortPrefix($static_column_key)] = NULL;
					}
					unset($static_column_key, $static_column_val);
				}

			}
		}

		foreach( $filter_data['column_ids'] as $column_key ) {
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
		}

		if ( isset($rows) AND ( $action == 'display_t4s' OR $action == 'export_xml' ) ) {
			Debug::Text('Generating PDF/XML: '. $action, __FILE__, __LINE__, __METHOD__,10);

			$last_row = count($rows)-1;
			$total_row = $last_row+1;

			//Get company information
			$clf = TTnew( 'CompanyListFactory' );
			$company_obj = $clf->getById( $current_company->getId() )->getCurrent();

			//Debug::setVerbosity(11);
			require_once( Environment::getBasePath() .'/classes/GovernmentForms/GovernmentForms.class.php');
			$gf = new GovernmentForms();
			if ( $action == 'export_xml' ) {
				$t619 = $gf->getFormObject( 'T619', 'CA' );
				$t619->transmitter_number = $setup_data['transmitter_number'];
				$t619->transmitter_name = $company_obj->getName();
				$t619->transmitter_address1 = $company_obj->getAddress1();
				$t619->transmitter_address2 = $company_obj->getAddress2();
				$t619->transmitter_city = $company_obj->getCity();
				$t619->transmitter_province = $company_obj->getProvince();
				$t619->transmitter_postal_code = $company_obj->getPostalCode();
				$t619->contact_name = $current_user->getFullName();
				$t619->contact_phone = $company_obj->getWorkPhone();
				$t619->contact_email = $current_user->getWorkEmail();
				$gf->addForm( $t619 );
			}

			$t4 = $gf->getFormObject( 'T4', 'CA' );

			if ( isset($filter_data['include_t4_back']) AND $filter_data['include_t4_back'] == 1 ) {
				$t4->setShowInstructionPage(TRUE);
			}

			$t4->setType( $filter_data['type'] );
			$t4->year = $filter_data['year'];
			$t4->payroll_account_number = $setup_data['payroll_account_number'];

			$t4->company_name = $setup_data['company_name'];

			$i=0;
			foreach($rows as $row) {
				if ( $i == $last_row ) {
					continue;
				}

				$ulf = TTnew( 'UserListFactory' );
				$user_obj = $ulf->getById( $row['user_id'] )->getCurrent();

				$ee_data = array(
							'first_name' => $user_obj->getFirstName(),
							'middle_name' => $user_obj->getMiddleName(),
							'last_name' => $user_obj->getLastName(),
							'address1' => $user_obj->getAddress1(),
							'address2' => $user_obj->getAddress2(),
							'city' => $user_obj->getCity(),
							'province' => $user_obj->getProvince(),
							'employment_province' => $user_obj->getProvince(),
							'postal_code' => $user_obj->getPostalCode(),
							'sin' => $row['sin'],
							'employee_number' => $user_obj->getEmployeeNumber(),
							'l14' => $row['income'],
							'l22' => $row['income_tax'],
							'l16' => $row['employee_cpp'],
							'l24' => $row['ei_earnings'],
							'l26' => $row['cpp_earnings'],
							'l18' => $row['employee_ei'],
							'l44' => $row['union_dues'],
							'l20' => $row['rpp'] ,
							'l46' => $row['charity'],
							'l52' => $row['pension_adjustment'],
							'cpp_exempt' => FALSE,
							'ei_exempt' => FALSE,
							'other_box_0_code' => NULL,
							'other_box_0' => NULL,
							'other_box_1_code' => NULL,
							'other_box_1' => NULL,
							'other_box_2_code' => NULL,
							'other_box_2' => NULL,
							'other_box_3_code' => NULL,
							'other_box_3' => NULL,
							'other_box_4_code' => NULL,
							'other_box_4' => NULL,
							'other_box_5_code' => NULL,
							'other_box_5' => NULL,
							);

				//Get User Tax / Deductions by Pay Stub Account.
				$udlf = TTnew( 'UserDeductionListFactory' );
				if ( isset($setup_data['employee_cpp_psea_id']) ) {
					$udlf->getByUserIdAndPayStubEntryAccountID( $user_obj->getId(), $setup_data['employee_cpp_psea_id'] );
					if ( $setup_data['employee_cpp_psea_id'] != 0
							AND $udlf->getRecordCount() == 0 ) {
						//Debug::Text('CPP Exempt!', __FILE__, __LINE__, __METHOD__,10);
						$ee_data['cpp_exempt'] = TRUE;
					}
				}

				if ( isset($setup_data['employee_ei_psea_id'] ) ) {
					$udlf->getByUserIdAndPayStubEntryAccountID( $user_obj->getId(), $setup_data['employee_ei_psea_id'] );
					if ( $setup_data['employee_ei_psea_id'] != 0
							AND $udlf->getRecordCount() == 0 ) {
						//Debug::Text('EI Exempt!', __FILE__, __LINE__, __METHOD__,10);
						$ee_data['ei_exempt'] = TRUE;
					}
				}

				if ( $row['other_box_0'] > 0 AND isset($setup_data['other_box'][0]['box']) AND $setup_data['other_box'][0]['box'] !='') {
					$ee_data['other_box_0_code'] = $setup_data['other_box'][0]['box'];
					$ee_data['other_box_0'] = $row['other_box_0'];
				}

				if ( $row['other_box_1'] > 0 AND isset($setup_data['other_box'][1]['box']) AND $setup_data['other_box'][1]['box'] !='') {
					$ee_data['other_box_1_code'] = $setup_data['other_box'][1]['box'];
					$ee_data['other_box_1'] = $row['other_box_1'];
				}

				if ( $row['other_box_2'] > 0 AND isset($setup_data['other_box'][2]['box']) AND $setup_data['other_box'][2]['box'] !='') {
					$ee_data['other_box_2_code'] = $setup_data['other_box'][2]['box'];
					$ee_data['other_box_2'] = $row['other_box_2'];
				}

				if ( $row['other_box_3'] > 0 AND isset($setup_data['other_box'][3]['box']) AND $setup_data['other_box'][3]['box'] !='') {
					$ee_data['other_box_3_code'] = $setup_data['other_box'][3]['box'];
					$ee_data['other_box_3'] = $row['other_box_3'];
				}

				if ( $row['other_box_4'] > 0 AND isset($setup_data['other_box'][4]['box']) AND $setup_data['other_box'][4]['box'] !='') {
					$ee_data['other_box_4_code'] = $setup_data['other_box'][4]['box'];
					$ee_data['other_box_4'] = $row['other_box_4'];
				}
				if ( $row['other_box_5'] > 0 AND isset($setup_data['other_box'][5]['box']) AND $setup_data['other_box'][5]['box'] !='') {
					$ee_data['other_box_5_code'] = $setup_data['other_box'][5]['box'];
					$ee_data['other_box_5'] = $row['other_box_5'];
				}
				$t4->addRecord( $ee_data );
				unset($ee_data);

				$i++;
			}
			$gf->addForm( $t4 );

			//Handle T4Summary
			$t4s = $gf->getFormObject( 'T4Sum', 'CA' );
			$t4s->year = $t4->year;
			$t4s->payroll_account_number = $t4->payroll_account_number;
			$t4s->company_name = $t4->company_name;

			$t4s->company_address1 = $setup_data['company_address1'];
			$t4s->company_address2 = $setup_data['company_address2'];
			$t4s->company_city = $setup_data['company_city'];
			$t4s->company_province = $setup_data['company_province'];
			$t4s->company_postal_code = $setup_data['company_postal_code'];

			$t4s->l76 = $current_user->getFullName(); //Contact name.
			$t4s->l78 = $company_obj->getWorkPhone();

			$t4s->l88 = count($rows)-1;
			$t4s->l14 = $rows[$last_row]['income'];
			$t4s->l22 = $rows[$last_row]['income_tax'];
			$t4s->l16 = $rows[$last_row]['employee_cpp'];
			$t4s->l18 = $rows[$last_row]['employee_ei'];
			$t4s->l27 = $rows[$last_row]['employer_cpp'];
			$t4s->l19 = $rows[$last_row]['employer_ei'];
			$t4s->l20 = $rows[$last_row]['rpp'];
			$t4s->l52 = $rows[$last_row]['pension_adjustment'];

			$total_deductions = Misc::MoneyFormat( $rows[$last_row]['employee_cpp'] + $rows[$last_row]['employer_cpp'] + $rows[$last_row]['employee_ei'] + $rows[$last_row]['employer_ei'] + $rows[$last_row]['income_tax'], FALSE );
			$t4s->l82 = $total_deductions;
			$gf->addForm( $t4s );

			if ( $action == 'display_t4s' ) {
				$file_name = 't4_'.$filter_data['year'].'.pdf';
				$mime_type = 'application/pdf';
				$output = $gf->output( 'PDF' );
			} elseif ( $action == 'export_xml' ) {
				$file_name = 't4_'.$filter_data['year'].'.xml';
				$mime_type = 'application/octetstream';
				$output = $gf->output( 'XML' );
			}
			unset($t4, $t4s);

			if ( Debug::getVerbosity() == 11 ) {
				Debug::Display();
			} elseif ( strlen($output) > 0 ) {
				Misc::FileDownloadHeader( $file_name, $mime_type, strlen($output));
				echo $output;
				exit;
			} else {
				echo TTi18n::getText('Invalid data, unable to generate report.');
				Debug::writeToLog();
				exit;
			}
		} else {
			Debug::Text('NOT Generating PDF: ', __FILE__, __LINE__, __METHOD__,10);
		}

		if ( $action == 'export' ) {
			if ( isset($rows) AND isset($filter_columns) ) {
				Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);
				$data = Misc::Array2CSV( $rows, $filter_columns, FALSE );

				Misc::FileDownloadHeader('report.csv', 'application/csv', strlen($data) );
				echo $data;
			} else {
				echo TTi18n::gettext('No Data To Export!') ."<br>\n";
			}
		} else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			//$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/T4SummaryReport.tpl');
		}

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
				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );

				//$filter_data['year'] = $year_options[$year_keys[1]];

				$filter_data['column_ids'] = array_keys($columns);

				//$filter_data['sort_column'] = 'last_name';
				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1020-province';


			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL );

		//Deduction PSEA accounts
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$filter_data['pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50), TRUE );

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$filter_data['deduction_pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(20,30), TRUE );

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

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );

		$filter_data['year_options'] = $year_options;
		$filter_data['type_options'] = array('government' => TTi18n::gettext('Government (Multiple Employees/Page)'), 'employee' => TTi18n::gettext('Employee (One Employee/Page)') );

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

		$smarty->display('report/T4Summary.tpl');

		break;
}
?>