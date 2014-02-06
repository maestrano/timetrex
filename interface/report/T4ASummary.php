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
 * $Revision: 2402 $
 * $Id: T4Summary.php 2402 2009-01-29 01:05:16Z ipso $
 * $Date: 2009-01-28 17:05:16 -0800 (Wed, 28 Jan 2009) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require(Environment::getBasePath() .'/classes/fpdi/fpdi.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_t4_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'T4A Summary Report')); // See index.php

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

$non_static_columns = array(		'-1100-pension' => TTi18n::gettext('Pension Or Superannuation (16)'),
									'-1110-lump_sum_payment' => TTi18n::gettext('Lump-sum Payments (18)'),
									'-1120-income_tax' => TTi18n::gettext('Income Tax (22)'),
									'-1125-eligible_retiring_allowance' => TTi18n::gettext('Eligible Retiring Allowance (26)'),
									'-1126-non_eligible_retiring_allowance' => TTi18n::gettext('Non-Eligible Retiring Allowance (26)'),
									'-1130-other_income' => TTi18n::gettext('Other Income (28)'),
									'-1140-rpp' => TTi18n::gettext('RPP Contributions (32)'),
									'-1150-pension_adjustment' => TTi18n::gettext('Pension Adjustment (34)'),
									'-1180-charity' => TTi18n::gettext('Charity Donations (46)'),
									);

$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
$pseallf->getByCompanyId( $current_company->getId() );
if ( $pseallf->getRecordCount() > 0 ) {
	$pseal_obj = $pseallf->getCurrent();
}

$column_ps_entry_name_map = array(
								'income_tax' => @$setup_data['income_tax_psea_ids'],
								'pension' => @$setup_data['pension_psea_ids'],
								'lump_sum_payment' => @$setup_data['lump_sum_payment_psea_ids'],
								'other_income' => @$setup_data['other_income_psea_ids'],
								'eligible_retiring_allowance' => @$setup_data['eligible_retiring_allowance_psea_ids'],
								'non_eligible_retiring_allowance' => @$setup_data['non_eligible_retiring_allowance_psea_ids'],
								'rpp' => @$setup_data['rpp_psea_ids'],
								'charity' => @$setup_data['charity_psea_ids'],
								'pension_adjustment' => @$setup_data['pension_adjustment_psea_ids'],
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
	case 'display_t4as':
	case 'display_report':
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
				if ( $action != 'display_t4as' AND isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
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

		if ( $action == 'display_t4as' ) {
			Debug::Text('Generating PDF: ', __FILE__, __LINE__, __METHOD__,10);

			$last_row = count($rows)-1;
			$total_row = $last_row+1;

			//Get company information
			$clf = TTnew( 'CompanyListFactory' );
			$company_obj = $clf->getById( $current_company->getId() )->getCurrent();

			//Debug::setVerbosity(11);
			$t4 = new T4;
			if ( isset($filter_data['include_t4_back']) AND $filter_data['include_t4_back'] == 1 ) {
				$t4->setShowInstructionPage(TRUE);
			}
			//$t4->setShowBackGround(FALSE);
			$t4->setShowBorder(FALSE);
			//$t4->setXOffset(10);
			//$t4->setYOffset(10);

			$t4->setType( $filter_data['type'] );
			$t4->setYear( $filter_data['year'] );
			$t4->setBusinessNumber( $company_obj->getBusinessNumber() );

			$t4->setCompanyName( $company_obj->getName() );
			$t4->setCompanyAddress1( $company_obj->getAddress1() );
			$t4->setCompanyAddress2( $company_obj->getAddress2() );
			$t4->setCompanyCity( $company_obj->getCity() );
			$t4->setCompanyProvince( $company_obj->getProvince() );
			$t4->setCompanyPostalCode( $company_obj->getPostalCode() );

			$i=0;
			$total_t4as=0;
			foreach($rows as $row) {
				if ( $i == $last_row ) {
					continue;
				}

				//Only show T4A's with non-zero amounts, excluding income tax deducted.
				if ( ($row['pension']+$row['lump_sum_payment']+$row['other_income']+$row['eligible_retiring_allowance']+$row['non_eligible_retiring_allowance']+$row['rpp']+$row['charity']+$row['pension_adjustment']) > 0 ) {
					$ulf = TTnew( 'UserListFactory' );
					$user_obj = $ulf->getById( $row['user_id'] )->getCurrent();

					$t4aee = new T4AEmployee();
					$t4aee->setSin( $row['sin'] );
					$t4aee->setFirstName( $user_obj->getFirstName() );
					$t4aee->setMiddleName( $user_obj->getMiddleName() );
					$t4aee->setLastName( $user_obj->getLastName()  );
					$t4aee->setAddress1( $user_obj->getAddress1() );
					$t4aee->setAddress2( $user_obj->getAddress2() );
					$t4aee->setCity( $user_obj->getCity() );
					$t4aee->setProvince( $user_obj->getProvince() );
					$t4aee->setPostalCode( $user_obj->getPostalCode() );
					//$t4ee->setEmployementCode( );

					$t4aee->setIncomeTax( $row['income_tax'] );
					$t4aee->setPension( $row['pension'] );
					$t4aee->setLumpSumPayment( $row['lump_sum_payment'] );
					$t4aee->setOtherIncome( $row['other_income'] );
					$t4aee->setEligibleRetiringAllowance( $row['eligible_retiring_allowance'] );
					$t4aee->setNonEligibleRetiringAllowance( $row['non_eligible_retiring_allowance'] );
					$t4aee->setEmployeeRPP( $row['rpp'] );
					$t4aee->setCharityDonations( $row['charity'] );
					$t4aee->setPensionAdjustment( $row['pension_adjustment'] );
					$t4->addT4AEmployee( $t4aee );

					$total_t4as++;
				}

				$i++;
			}
			$t4asum = new T4ASummary();
			$t4asum->setIncomeTax( $rows[$last_row]['income_tax'] );
			$t4asum->setPension( $rows[$last_row]['pension'] );
			$t4asum->setLumpSumPayment( $rows[$last_row]['lump_sum_payment'] );
			$t4asum->setOtherIncome( $rows[$last_row]['other_income'] );
			$t4asum->setEligibleRetiringAllowance( $rows[$last_row]['eligible_retiring_allowance'] );
			$t4asum->setNonEligibleRetiringAllowance( $rows[$last_row]['non_eligible_retiring_allowance'] );
			$t4asum->setEmployeeRPP( $rows[$last_row]['rpp'] );
			$t4asum->setCharityDonations( $rows[$last_row]['charity'] );
			$t4asum->setPensionAdjustment( $rows[$last_row]['pension_adjustment'] );
			$t4asum->setTotalT4As( $total_t4as );
			$t4->addT4ASummary( $t4asum );


			$t4->compileT4ASummary();
			$t4->compileT4A();

			$t4->displayPDF();
		} else {
			Debug::Text('NOT Generating PDF: ', __FILE__, __LINE__, __METHOD__,10);
		}

		$smarty->assign_by_ref('generated_time', TTDate::getTime() );
		//$smarty->assign_by_ref('pay_period_options', $pay_period_options );
		$smarty->assign_by_ref('filter_data', $filter_data );
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign_by_ref('rows', $rows);

		$smarty->display('report/T4ASummaryReport.tpl');

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

		$smarty->display('report/T4ASummary.tpl');

		break;
}
?>