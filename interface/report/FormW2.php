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
 * $Revision: 4808 $
 * $Id: FormW2.php 4808 2011-06-09 16:17:13Z ipso $
 * $Date: 2011-06-09 09:17:13 -0700 (Thu, 09 Jun 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require(Environment::getBasePath() .'/classes/fpdi/fpdi.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_formW2') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Form W2 Report')); // See index.php

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

$static_columns = array(
											'-1000-full_name' => TTi18n::gettext('Full Name'),
											'-1010-province' => TTi18n::gettext('State'),
											'-1020-ssn' => TTi18n::gettext('SSN'),
											'-1180-state_1' => TTi18n::gettext('1- State (15)'),
											'-1230-district_1' => TTi18n::gettext('1- Locality (20)'),
											);

$non_static_columns = array(
											'-1100-wage' => TTi18n::gettext('Wages, Tips, Other (1)'),
											'-1110-federal_tax' => TTi18n::gettext('Federal Income Tax (2)'),
											'-1120-ss_wage' => TTi18n::gettext('Social Security Wages (3)'),
											'-1130-ss_tax' => TTi18n::gettext('Social Security Tax (4)'),
											'-1140-medicare_wage' => TTi18n::gettext('Medicare Wages (5)'),
											'-1150-medicare_tax' => TTi18n::gettext('Medicare Tax (6)'),
											'-1160-ss_tips' => TTi18n::gettext('Social Security Tips (7)'),
											'-1170-allocated_tips' => TTi18n::gettext('Allocated Tips (8)'),
											'-1180-advance_eic' => TTi18n::gettext('Advance EIC Payment (9)'),
											'-1190-dependent_care_benefit' => TTi18n::gettext('Dependent Care Benefits (10)'),
											'-1200-nonqualified_plan' => TTi18n::gettext('Nonqualified Plans (11)'),
											'-1210-box_12a' => TTi18n::gettext('Box 12a'),
											'-1220-box_12b' => TTi18n::gettext('Box 12b'),
											'-1230-box_12c' => TTi18n::gettext('Box 12c'),
											'-1240-box_12d' => TTi18n::gettext('Box 12d'),
											'-1250-box_14a' => TTi18n::gettext('Other Box 14(a)'),
											'-1260-box_14b' => TTi18n::gettext('Other Box 14(b)'),
											'-1260-box_14c' => TTi18n::gettext('Other Box 14(c)'),
											'-1500-state_wage_1' => TTi18n::gettext('1- State Wages, Tips, Other (16)'),
											'-1510-state_tax_1' => TTi18n::gettext('1- State Income Tax (17)'),
											'-1600-district_wage_1' => TTi18n::gettext('1- Locality Wages, Tips, Other (18)'),
											'-1610-district_tax_1' => TTi18n::gettext('1- Locality Income Tax (19)'),
											);

$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
$pseallf->getByCompanyId( $current_company->getId() );
if ( $pseallf->getRecordCount() > 0 ) {
	$pseal_obj = $pseallf->getCurrent();
}

$columns = Misc::prependArray( $static_columns, $non_static_columns);

$column_ps_entry_name_map = array(
								'wage' 					=> @array( $setup_data['1_psea_ids'], $setup_data['1_exclude_psea_ids'] ),
								'federal_tax' 			=> @array( $setup_data['2_psea_ids'], $setup_data['2_exclude_psea_ids'] ),
								'ss_wage' 				=> @array( $setup_data['3_psea_ids'], $setup_data['3_exclude_psea_ids'] ),
								'ss_tax' 				=> @array( $setup_data['4_psea_ids'], $setup_data['4_exclude_psea_ids'] ),
								'medicare_wage' 		=> @array( $setup_data['5_psea_ids'], $setup_data['5_exclude_psea_ids'] ),
								'medicare_tax' 			=> @array( $setup_data['6_psea_ids'], $setup_data['6_exclude_psea_ids'] ),
								'ss_tips' 				=> @array( $setup_data['7_psea_ids'], $setup_data['7_exclude_psea_ids'] ),
								'allocated_tips'		=> @array( $setup_data['8_psea_ids'], $setup_data['8_exclude_psea_ids'] ),
								'advance_eic'			=> @array( $setup_data['9_psea_ids'], $setup_data['9_exclude_psea_ids'] ),
								'dependent_care_benefit'=> @array( $setup_data['10_psea_ids'], $setup_data['10_exclude_psea_ids'] ),
								'nonqualified_plan' 	=> @array( $setup_data['11_psea_ids'], $setup_data['11_exclude_psea_ids'] ),
								'box_12a' 				=> @array( $setup_data['12a_psea_ids'], $setup_data['12a_exclude_psea_ids'] ),
								'box_12b' 				=> @array( $setup_data['12b_psea_ids'], $setup_data['12b_exclude_psea_ids'] ),
								'box_12c' 				=> @array( $setup_data['12c_psea_ids'], $setup_data['12c_exclude_psea_ids'] ),
								'box_12d' 				=> @array( $setup_data['12d_psea_ids'], $setup_data['12d_exclude_psea_ids'] ),
								'box_14a' 				=> @array( $setup_data['14a_psea_ids'], $setup_data['14a_exclude_psea_ids'] ),
								'box_14b' 				=> @array( $setup_data['14b_psea_ids'], $setup_data['14b_exclude_psea_ids'] ),
								'box_14c' 				=> @array( $setup_data['14c_psea_ids'], $setup_data['14c_exclude_psea_ids'] ),
								);

$pplf = TTnew( 'PayPeriodListFactory' );
$year_options = $pplf->getYearsArrayByCompanyId( $current_company->getId() );

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array() );

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
switch ($action) {
	case 'display_form':
	case 'print_form':
	case 'display_report':
		//Debug::setVerbosity(11);
		if ( $action == 'print_form' ) {
			$show_background = FALSE;
		} else {
			$show_background = TRUE;
		}

		Debug::Text('Submit!: '. $action, __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($filter_data, 'aFilter Data', __FILE__, __LINE__, __METHOD__,10);

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

			if ( isset($filter_data['user_ids']) AND isset($filter_data['year']) ) {
				if ( isset($filter_data['year']) ) {
					$year_epoch = mktime(0,0,0,1,1,$filter_data['year']);
					Debug::Text(' Year: '. TTDate::getDate('DATE+TIME', $year_epoch) , __FILE__, __LINE__, __METHOD__,10);
				}

				$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
				$pseallf->getByCompanyId( $current_company->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pseal_obj = $pseallf->getCurrent();
				}

				//
				//Get all data for the form.
				//

				//Get Pay Periods in date range.
				$pplf = TTnew( 'PayPeriodListFactory' );
				$pplf->getByCompanyIdAndTransactionStartDateAndTransactionEndDate( $current_company->getId(), TTDate::getBeginYearEpoch($year_epoch), TTDate::getEndYearEpoch($year_epoch) );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach($pplf as $pp_obj) {
						$pay_period_ids[] = $pp_obj->getID();
					}
				}

				$report_columns = $static_columns;

				$pself = TTnew( 'PayStubEntryListFactory' );
				$pself->getReportByCompanyIdAndUserIdAndPayPeriodId( $current_company->getId(), $filter_data['user_ids'], $pay_period_ids );

				foreach( $pself as $pse_obj ) {
					$user_id = $pse_obj->getColumn('user_id');
					$pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');

					$raw_rows[$user_id][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}
				//var_dump($raw_rows);

				//
				//Figure out state/locality wages/taxes.
				//
				$cdlf = TTnew( 'CompanyDeductionListFactory' );
				$cdlf->getByCompanyIdAndStatusIdAndTypeId( $current_company->getId(), array(10,20), 10 );
				if ( $cdlf->getRecordCount() > 0 ) {
					foreach( $cdlf as $cd_obj ) {
						$tax_deductions[] = array(
													'id' => $cd_obj->getId(),
													'name' => $cd_obj->getName(),
													'calculation_id' => $cd_obj->getCalculation(),
													'province' => $cd_obj->getProvince(),
													'district' => $cd_obj->getDistrictName(),
													'pay_stub_entry_account_id' => $cd_obj->getPayStubEntryAccount(),
													'include' => $cd_obj->getIncludePayStubEntryAccount(),
													'exclude' => $cd_obj->getExcludePayStubEntryAccount(),
													'user_ids' => $cd_obj->getUser(),
													'company_value1' => $cd_obj->getCompanyValue1(),
													'user_value1' => $cd_obj->getUserValue1(),
													'user_value5' => $cd_obj->getUserValue5(), //District
												);
					}
					//Debug::Arr($tax_deductions, 'Tax Deductions: ', __FILE__, __LINE__, __METHOD__,10);
				}

				if ( isset($raw_rows) ) {
					$ulf = TTnew( 'UserListFactory' );

					$x=0;
					foreach($raw_rows as $user_id => $raw_row) {
						$user_obj = $ulf->getById( $user_id )->getCurrent();

						$tmp_rows[$x]['user_id'] = $user_id;
						$tmp_rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
						//$tmp_rows[$x]['province'] = Option::getByKey($user_obj->getProvince(), $user_obj->getOptions('province') );
						$tmp_rows[$x]['province'] = $user_obj->getProvince();
						$tmp_rows[$x]['ssn'] = $user_obj->getSIN();

						foreach($column_ps_entry_name_map as $column_key => $ps_entry_map) {
							//$tmp_rows[$x][$column_key] = Misc::MoneyFormat( Misc::sumMultipleColumns( $raw_rows[$user_id], $ps_entry_map), FALSE );
							$tmp_rows[$x][$column_key] = Misc::MoneyFormat( Misc::calculateMultipleColumns( $raw_rows[$user_id], $ps_entry_map[0], $ps_entry_map[1] ), FALSE );
						}

						//Handle state/district data here
						//FIXME: Loop through each raw_row pay stub account IDs, and match them to tax deductions
						//that way if a user is removed from a tax deduction half way through the year it will
						//still match up, assuming it isn't deleted.
						//If an employee has worked in more than 2 states or localities, issue multiple W2's.
						//**Need to make sure we split up the earnings proper between states/localities when the employees switch between them.
						// Adam from DiscipleM??? requested this feature.
						if ( isset($tax_deductions) ) {
							foreach( $tax_deductions as $tax_deduction_arr ) {
								if ( isset($tax_deduction_arr['user_ids']) AND is_array($tax_deduction_arr['user_ids']) AND in_array( $user_id, $tax_deduction_arr['user_ids'] ) ) {
									//Debug::Arr($tax_deduction_arr, 'Tax / Deduction Data: ', __FILE__, __LINE__, __METHOD__,10);
									Debug::Text('Found User ID: '. $user_id .' in Tax Deduction ID: '. $tax_deduction_arr['id'] .' Pay Stub Entry Account ID: '. $tax_deduction_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__,10);

									//if ( $tax_deduction_arr['province'] != '' AND $tax_deduction_arr['district'] == '' AND $tax_deduction_arr['user_value5'] == '' ) {
									if ( $tax_deduction_arr['calculation_id'] == 200 AND $tax_deduction_arr['province'] != '' ) {
										//State Wages/Taxes
										//Handle two states here, just check if $tmp_rows[$x]['state_1'] isset,
										//if it is, move on to state 2.
										$tmp_rows[$x]['state_1'] = $tax_deduction_arr['province'];
										$tmp_rows[$x]['state_wage_1'] = Misc::MoneyFormat( Misc::sumMultipleColumns( $raw_rows[$user_id], $tax_deduction_arr['include'] ), FALSE );
										$tmp_rows[$x]['state_tax_1'] = Misc::MoneyFormat( Misc::sumMultipleColumns( $raw_rows[$user_id], array($tax_deduction_arr['pay_stub_entry_account_id']) ), FALSE );
									} elseif ( $tax_deduction_arr['calculation_id'] == 300 AND ( $tax_deduction_arr['district'] != '' OR $tax_deduction_arr['company_value1'] != '' ) )  {
										//District Wages/Taxes
										if ( $tax_deduction_arr['district'] == '' AND $tax_deduction_arr['company_value1'] != '' ) {
											$tmp_rows[$x]['district_1'] = $tax_deduction_arr['company_value1'];
										} else {
											$tmp_rows[$x]['district_1'] = $tax_deduction_arr['district'];
										}
										$tmp_rows[$x]['district_wage_1'] = Misc::MoneyFormat( Misc::sumMultipleColumns( $raw_rows[$user_id], $tax_deduction_arr['include'] ), FALSE );
										$tmp_rows[$x]['district_tax_1'] = Misc::MoneyFormat( Misc::sumMultipleColumns( $raw_rows[$user_id], array($tax_deduction_arr['pay_stub_entry_account_id']) ), FALSE );
									} else {
										//Debug::Text('Not State or Local income tax: '. $tax_deduction_arr['id'] .' Calculation: '. $tax_deduction_arr['calculation_id'] .' District: '. $tax_deduction_arr['district'] .' UserValue5: '.$tax_deduction_arr['user_value5'] .' CompanyValue1: '. $tax_deduction_arr['company_value1'], __FILE__, __LINE__, __METHOD__,10);
									}
								} else {
									Debug::Text('DID NOT Find user in Tax Deduction ID: '. $tax_deduction_arr['id'], __FILE__, __LINE__, __METHOD__,10);
								}
							}
						} else {
							Debug::Text('No Tax Deductions...', __FILE__, __LINE__, __METHOD__,10);
						}

						$x++;
					}
				}
				//print_r($tmp_rows);

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
						$rows[$last_row][ Misc::trimSortPrefix($static_column_key)] = NULL;
					}
					unset($static_column_key, $static_column_val);
				}
			}

			foreach( $filter_data['column_ids'] as $column_key ) {
				$filter_columns[ Misc::trimSortPrefix($column_key)] = $columns[$column_key];
			}

			if ( $action == 'display_form' OR $action == 'print_form' ) {
				Debug::Text('Generating PDF: ', __FILE__, __LINE__, __METHOD__,10);

				//Get company information
				$clf = TTnew( 'CompanyListFactory' );
				$company_obj = $clf->getById( $current_company->getId() )->getCurrent();

				$border = 0;
				$pdf= new fpdi();

				$pdf->setMargins(5,5,5,5);
				$pdf->SetAutoPageBreak(FALSE);
				$pdf->SetFont('freeserif','',10);

				if ( $show_background == TRUE ) {
					//Import original Gov't supplied PDF.
					$pagecount = $pdf->setSourceFile(Environment::getBasePath().'interface'. DIRECTORY_SEPARATOR .'forms'. DIRECTORY_SEPARATOR .'us'. DIRECTORY_SEPARATOR .'tax'. DIRECTORY_SEPARATOR .'w3.pdf');
					$tplidx_summary = $pdf->ImportPage(1);
				}

				$pdf->AddPage();
				if ( isset($tplidx_summary) ) {
					$pdf->useTemplate($tplidx_summary,0,0);
				}

				//Form year
				if ( $show_background == TRUE ) {
					$pdf->SetFont('freeserif','', 18);
					$pdf->setFillColor( 255,255,255 );
					$pdf->setXY( 135, 154 );
					$pdf->Cell(20,7, $filter_data['year'], $border, 1, 'C', 1);

					$pdf->SetFont('freeserif','', 10);
					$pdf->setXY( 59, 203.3 );
					$pdf->Cell(9,4, $filter_data['year'], $border, 1, 'C', 1);

					$pdf->SetFont('freeserif','', 10);
				}

				if ( isset($rows) ) {
					$i=0;
					$last_row = count($rows)-1;
					$total_row = $last_row+1;

					//
					// W3 form
					//

					//Control Number
					$pdf->setXY(46,18);
					$pdf->Cell(15,5, str_pad('0001',4,0,STR_PAD_LEFT) , $border, 0, 'L');

					//Kind of Payer
					$pdf->setXY(38.5,26);
					$pdf->Cell(5,5, 'X' , $border, 0, 'C');

					//Total W2's
					$pdf->setXY(16,43);
					$pdf->Cell(25,5, $last_row , $border, 0, 'L');

					//EIN
					$pdf->setXY(16,52);
					$pdf->Cell(40,5, $company_obj->getBusinessNumber() , $border, 0, 'L');

					//Company Name/Address
					$pdf->setXY(16,60);
					$pdf->Cell(75,5,$current_company->getName(), $border, 0, 'L');

					$pdf->setXY(16,66);
					$pdf->Cell(75,5,$current_company->getAddress1() .' '. $current_company->getAddress2(), $border, 0, 'L');

					$pdf->setXY(16,70);
					$pdf->Cell(45,5,$current_company->getCity().', '. $current_company->getProvince() .' '. $current_company->getPostalCode(), $border, 0, 'L');

					//Contact Person Info
					$pdf->setXY(16,120);
					$pdf->Cell(40,5, $current_user->getFullName() , $border, 0, 'L');

					//Phone
					$numeric_phone = $current_user->Validator->stripNonNumeric( $current_user->getWorkPhone() );
					$pdf->setXY(98,120);
					$pdf->Cell(10,5, substr( $numeric_phone, 0,3)  , $border, 0, 'C');

					$pdf->setXY(110,120);
					$pdf->Cell(30,5, substr( $numeric_phone, 3,3).'-'.substr( $numeric_phone, 6,4)  , $border, 0, 'L');

					$pdf->setXY(16,128);
					$pdf->Cell(70,5, $current_user->getWorkEmail()  , $border, 0, 'L');

					//Box 1
					if ($rows[$last_row]['wage'] > 0) {
						$pdf->setXY(106,27);
						$pdf->Cell(40,5, $rows[$last_row]['wage'] , $border, 0, 'R');
					}

					//Box 2
					if ($rows[$last_row]['federal_tax'] > 0) {
						$pdf->setXY(161,27);
						$pdf->Cell(40,5, $rows[$last_row]['federal_tax'] , $border, 0, 'R');
					}

					//Box 3
					if ($rows[$last_row]['ss_wage'] > 0) {
						$pdf->setXY(106,35);
						$pdf->Cell(40,5, $rows[$last_row]['ss_wage'] , $border, 0, 'R');
					}
					//Box 4
					if ($rows[$last_row]['ss_tax'] > 0) {
						$pdf->setXY(161,35);
						$pdf->Cell(40,5, $rows[$last_row]['ss_tax'] , $border, 0, 'R');
					}

					//Box 5
					if ($rows[$last_row]['medicare_wage'] > 0) {
						$pdf->setXY(106,43);
						$pdf->Cell(40,5, $rows[$last_row]['medicare_wage'] , $border, 0, 'R');
					}
					//Box 6
					if ($rows[$last_row]['medicare_tax'] > 0) {
						$pdf->setXY(161,43);
						$pdf->Cell(40,5, $rows[$last_row]['medicare_tax'] , $border, 0, 'R');
					}

					//Box 7
					if ($rows[$last_row]['ss_tips'] > 0) {
						$pdf->setXY(106,52);
						$pdf->Cell(40,5, $rows[$last_row]['ss_tips'] , $border, 0, 'R');
					}
					//Box 8
					if ($rows[$last_row]['allocated_tips'] > 0) {
						$pdf->setXY(161,52);
						$pdf->Cell(40,5, $rows[$last_row]['allocated_tips'] , $border, 0, 'R');
					}
					//Box 9
					if ($rows[$last_row]['advance_eic'] != 0) {
						$pdf->setXY(106,61);
						$pdf->Cell(40,5, abs($rows[$last_row]['advance_eic']) , $border, 0, 'R'); //Should always be positive.
					}
					//Box 10
					if ($rows[$last_row]['dependent_care_benefit'] != 0) {
						$pdf->setXY(161,61);
						$pdf->Cell(40,5, $rows[$last_row]['dependent_care_benefit'] , $border, 0, 'R');
					}
					//Box 11
					if ($rows[$last_row]['nonqualified_plan'] != 0) {
						$pdf->setXY(106,69);
						$pdf->Cell(40,5, $rows[$last_row]['nonqualified_plan'] , $border, 0, 'R');
					}


					//If more then one state is being report, Box 15 must be "X" and NO state ID.
					//Box 15
					foreach( $rows as $row ) {
						if ( $i == $last_row ) {
							continue;
						}

						if ( isset($row['state_1']) AND !is_numeric($row['state_1']) ) {
							$states[] = $row['state_1'];
						}
						if ( isset($row['state_2']) AND !is_numeric($row['state_2']) ) {
							$states[] = $row['state_2'];
						}
					}
					if ( isset($states) ) {
						$states = array_unique($states);
					}

					if ( !isset($states) OR count($states) > 1 ) {
						$pdf->setXY(16,102);
						$pdf->Cell(5,5, 'X' , $border, 0, 'C');
					} elseif ( isset($rows[0]['state_1']) ) {
						$pdf->setXY(16,102);
						$pdf->Cell(5,5, $rows[0]['state_1'] , $border, 0, 'C');

						if ( isset($setup_data['state'][$rows[0]['state_1']]['state_id']) ) {
							$pdf->setXY(28,102);
							$pdf->Cell(40,5, $setup_data['state'][$rows[0]['state_1']]['state_id'] , $border, 0, 'L');
						}
					}

					//1- State Wages
					if ( isset($rows[$last_row]['state_wage_1']) AND $rows[$last_row]['state_wage_1'] > 0 ) {
						$pdf->setXY(106,102);
						$pdf->Cell(40,5, $rows[$last_row]['state_wage_1'] , $border, 0, 'R');
					}
					//1- State Taxes
					if ( isset($rows[$last_row]['state_tax_1']) AND $rows[$last_row]['state_tax_1'] > 0 ) {
						$pdf->setXY(161,102);
						$pdf->Cell(40,5, $rows[$last_row]['state_tax_1'] , $border, 0, 'R');
					}

					//1- Local Wages
					if ( isset( $rows[$last_row]['district_wage_1'] ) AND $rows[$last_row]['district_wage_1'] > 0 ) {
						$pdf->setXY(106,111);
						$pdf->Cell(40,5, $rows[$last_row]['district_wage_1'] , $border, 0, 'R');
						//1- Local Tax
						if ( $rows[$last_row]['district_tax_1'] > 0 ) {
							$pdf->setXY(161,111);
							$pdf->Cell(40,5, $rows[$last_row]['district_tax_1'] , $border, 0, 'R');
						}
					}

					//Import original Gov't supplied PDF.
					if ( $show_background == TRUE ) {
						$pagecount = $pdf->setSourceFile(Environment::getBasePath().'interface'. DIRECTORY_SEPARATOR .'forms'. DIRECTORY_SEPARATOR .'us'. DIRECTORY_SEPARATOR .'tax'. DIRECTORY_SEPARATOR .'w2.pdf');
						$tplidx = $pdf->ImportPage(8);
						//$tplidx_back = $pdf->ImportPage(2);
					}

					//
					// W2's
					//
					foreach( $rows as $row ) {
						if ( $i == $last_row ) {
							continue;
						}
						$ulf = TTnew( 'UserListFactory' );
						$user_obj = $ulf->getById( $row['user_id'] )->getCurrent();

						$pdf->AddPage();
						if ( isset($tplidx) ) {
							$pdf->useTemplate($tplidx,0,0);
						}

						//Form year
						if ( $show_background == TRUE ) {
							$pdf->SetFont('freeserif','', 28);
							$pdf->setFillColor( 255,255,255 );
							$pdf->setXY( 101, 122 );
							$pdf->Cell(25,7, $filter_data['year'], $border, 1, 'C', 1);

							$pdf->SetFont('freeserif','', 10);
						}

						//Control Number - Changed in 2007
						//$pdf->setXY(16,18);
						//$pdf->Cell(15,5, str_pad($i+1,4,0,STR_PAD_LEFT) , $border, 0, 'L');

						$pdf->setXY(66,18);
						$pdf->Cell(45,5, $user_obj->getSIN(), $border, 0, 'L');

						//EIN
						$pdf->setXY(16,27);
						$pdf->Cell(40,5, $company_obj->getBusinessNumber() , $border, 0, 'L');

						//Company Name/Address
						$pdf->setXY(16,35);
						$pdf->Cell(75,5,$current_company->getName(), $border, 0, 'L');

						$pdf->setXY(16,40);
						$pdf->Cell(75,5,$current_company->getAddress1() .' '. $current_company->getAddress2(), $border, 0, 'L');

						$pdf->setXY(16,45);
						$pdf->Cell(45,5,$current_company->getCity().', '. $current_company->getProvince() .' '. $current_company->getPostalCode(), $border, 0, 'L');

						//Control Number
						$pdf->setXY(16,60);
						$pdf->Cell(15,5, str_pad($i+1,4,0,STR_PAD_LEFT) , $border, 0, 'L');

						$pdf->setXY(16,69);
						$pdf->Cell(45,5,$user_obj->getFirstName(). ' '. strtoupper( substr($user_obj->getMiddleName(),0,1) ), $border, 0, 'L');

						$pdf->setXY(61,69);
						$pdf->Cell(25,5,strtoupper( $user_obj->getLastName() ), $border, 0, 'L');

						//Address
						$pdf->setXY(16,80);
						$pdf->Cell(75,5,$user_obj->getAddress1().' '.$user_obj->getAddress2(), $border, 0, 'L');
						$pdf->setXY(16,85);
						$pdf->Cell(75,5,$user_obj->getCity().', '.$user_obj->getProvince(), $border, 0, 'L');
						$pdf->setXY(16,90);
						$pdf->Cell(75,5,$user_obj->getPostalCode(), $border, 0, 'L');

						//Box 1
						if ( isset($row['wage']) AND $row['wage'] > 0) {
							$pdf->setXY(116,27);
							$pdf->Cell(40,5, $row['wage'] , $border, 0, 'R');
						}
						//Box 2
						if ( isset($row['federal_tax']) AND $row['federal_tax'] > 0) {
							$pdf->setXY(161,27);
							$pdf->Cell(40,5, $row['federal_tax'] , $border, 0, 'R');
						}

						//Box 3
						if ( isset($row['ss_wage']) AND $row['ss_wage']  > 0) {
							$pdf->setXY(116,35);
							$pdf->Cell(40,5, $row['ss_wage'] , $border, 0, 'R');
						}
						//Box 4
						if ( isset($row['ss_tax']) AND $row['ss_tax'] > 0) {
							$pdf->setXY(161,35);
							$pdf->Cell(40,5, $row['ss_tax'] , $border, 0, 'R');
						}

						//Box 5
						if ( isset($row['medicare_wage']) AND $row['medicare_wage'] > 0) {
							$pdf->setXY(116,43);
							$pdf->Cell(40,5, $row['medicare_wage'] , $border, 0, 'R');
						}
						//Box 6
						if ( isset($row['medicare_tax']) AND $row['medicare_tax'] > 0) {
							$pdf->setXY(161,43);
							$pdf->Cell(40,5, $row['medicare_tax'] , $border, 0, 'R');
						}

						//Box 7
						if ( isset($row['ss_tips']) AND $row['ss_tips'] > 0) {
							$pdf->setXY(116,52);
							$pdf->Cell(40,5, $row['ss_tips'] , $border, 0, 'R');
						}
						//Box 8
						if ( isset($row['allocated_tips']) AND $row['allocated_tips'] > 0) {
							$pdf->setXY(161,52);
							$pdf->Cell(40,5, $row['allocated_tips'] , $border, 0, 'R');
						}
						//Box 9
						if ( isset($row['advance_eic']) AND $row['advance_eic'] != 0) {
							$pdf->setXY(116,61);
							$pdf->Cell(40,5, abs($row['advance_eic']) , $border, 0, 'R'); //Should always be positive.
						}

						//Box 10
						if ( isset($row['dependent_care_benefit']) AND $row['dependent_care_benefit'] > 0) {
							$pdf->setXY(161,61);
							$pdf->Cell(40,5, $row['dependent_care_benefit'] , $border, 0, 'R');
						}
						//Box 11
						if ( isset($row['nonqualified_plan']) AND $row['nonqualified_plan'] > 0) {
							$pdf->setXY(116,69);
							$pdf->Cell(40,5, $row['nonqualified_plan'] , $border, 0, 'R');
						}


						//Box 12a
						if ( isset($row['box_12a']) AND $row['box_12a'] > 0) {
							$pdf->setXY(161,69);
							$pdf->Cell(10,5, $setup_data['12a_code'] , $border, 0, 'R');
							$pdf->Cell(30,5, $row['box_12a'] , $border, 0, 'R');
						}
						//Box 12b
						if ( isset($row['box_12b']) AND $row['box_12b'] > 0) {
							$pdf->setXY(161,77);
							$pdf->Cell(10,5, $setup_data['12b_code'] , $border, 0, 'R');
							$pdf->Cell(30,5, $row['box_12b'] , $border, 0, 'R');
						}
						//Box 12c
						if ( isset($row['box_12c']) AND $row['box_12c'] > 0) {
							$pdf->setXY(161,86);
							$pdf->Cell(10,5, $setup_data['12c_code'] , $border, 0, 'R');
							$pdf->Cell(30,5, $row['box_12c'] , $border, 0, 'R');
						}
						//Box 12d
						if ( isset($row['box_12d']) AND $row['box_12d'] > 0) {
							$pdf->setXY(161,94);
							$pdf->Cell(10,5, $setup_data['12d_code'] , $border, 0, 'R');
							$pdf->Cell(30,5, $row['box_12d'] , $border, 0, 'R');
						}


						//Box 14a
						if ( isset($row['box_14a']) AND $row['box_14a'] > 0) {
							$pdf->setXY(117,86);
							$pdf->Cell(21,5, $setup_data['14a_name'].':' , $border, 0, 'L');
							$pdf->Cell(21,5, $row['box_14a'] , $border, 0, 'R');
						}
						//Box 14b
						if ( isset($row['box_14b']) AND $row['box_14b'] > 0) {
							$pdf->setXY(117,91);
							$pdf->Cell(21,5, $setup_data['14b_name'].':' , $border, 0, 'L');
							$pdf->Cell(21,5, $row['box_14b'] , $border, 0, 'R');
						}
						//Box 14c
						if ( isset($row['box_14c']) AND $row['box_14c'] > 0) {
							$pdf->setXY(117,96);
							$pdf->Cell(21,5, $setup_data['14c_name'].':' , $border, 0, 'L');
							$pdf->Cell(21,5, $row['box_14c'] , $border, 0, 'R');
						}


						if ( isset($row['state_1']) ) {
							//1- State
							$pdf->setXY(12,107);
							$pdf->Cell(10,5, $row['state_1'] , $border, 0, 'C');
						}

						//1- Employer State ID
						if ( isset($row['state_1']) AND isset($setup_data['state'][$row['state_1']]['state_id']) ) {
							$pdf->setXY(24,107);
							$pdf->Cell(40,5, $setup_data['state'][$row['state_1']]['state_id'] , $border, 0, 'L');
						}

						//1- State Wages
						if ( isset($row['state_wage_1']) AND $row['state_wage_1'] > 0 ) {
							$pdf->setXY(73,107);
							$pdf->Cell(25,5, $row['state_wage_1'] , $border, 0, 'R');
						}
						//1- State Taxes
						if ( isset($row['state_tax_1']) AND $row['state_tax_1'] > 0 ) {
							$pdf->setXY(101,107);
							$pdf->Cell(25,5, $row['state_tax_1'] , $border, 0, 'R');
						}

						//1- Local Wages
						if ( isset( $row['district_wage_1'] ) AND $row['district_wage_1'] > 0 ) {
							$pdf->setXY(131,107);
							$pdf->Cell(25,5, $row['district_wage_1'] , $border, 0, 'R');
							//1- Local Tax
							if ($row['district_tax_1'] > 0 ) {
								$pdf->setXY(159,107);
								$pdf->Cell(25,5, $row['district_tax_1'] , $border, 0, 'R');
							}

							//Locality
							$pdf->setXY(185,107);
							$pdf->Cell(18,5, $row['district_1'] , $border, 0, 'L');
						}

						if ( isset($row['state_2']) ) {
							//2- State
							$pdf->setXY(12,115);
							$pdf->Cell(10,5, $row['state_2'] , $border, 0, 'C');

							//2- Employer State ID
							$pdf->setXY(24,115);
							$pdf->Cell(40,5, '' , $border, 0, 'L');

							//2- State Wages
							if ( isset($row['state_wage_2']) AND $row['state_wage_2'] > 0 ) {
								$pdf->setXY(73,115);
								$pdf->Cell(25,5, $row['state_wage_2'] , $border, 0, 'R');
							}
							//2- State Taxes
							if ( isset($row['state_tax_2']) AND $row['state_tax_2'] > 0 ) {
								$pdf->setXY(101,115);
								$pdf->Cell(25,5, $row['state_tax_2'] , $border, 0, 'R');
							}

							//2- Local Wages
							if ( isset( $row['district_wage_2'] ) AND $row['district_wage_2'] > 0 ) {
								$pdf->setXY(131,115);
								$pdf->Cell(25,5, $row['district_wage_2'] , $border, 0, 'R');
								//2- Local Tax
								if (isset($row['district_tax_2']) AND $row['district_tax_2'] > 0 ) {
									$pdf->setXY(159,115);
									$pdf->Cell(25,5, $row['district_tax_2'] , $border, 0, 'R');
								}

								//Locality
								$pdf->setXY(185,115);
								$pdf->Cell(18,5, $row['district_2'] , $border, 0, 'L');
							}
						}

						//Block out W2 "copy"
						$pdf->setFillColor(255,255,255);
						$pdf->setXY(0,130);
						$pdf->Cell(95,10, '', $border, 0, 'L', 1);

						$i++;
					}
				}

				//Finish off PDF
				$output = $pdf->Output('','S');

				if ( Debug::getVerbosity() == 11 ) {
					Debug::Display();
				} else {
					Misc::FileDownloadHeader('w2.pdf', 'application/pdf', strlen($output));
					echo $output;
				}
				Debug::writeToLog();
				exit;
			}
		}

		$smarty->assign_by_ref('generated_time', TTDate::getTime() );
		//$smarty->assign_by_ref('pay_period_options', $pay_period_options );
		$smarty->assign_by_ref('filter_data', $filter_data );
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign_by_ref('rows', $rows);

		$smarty->display('report/FormW2Report.tpl');

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
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );

				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}


				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-full_name',
											'-1010-province',
											'-1100-wage',
											'-1110-federal_tax',
											'-1130-ss_tax',
											'-1150-medicare_tax',
											'-1200-state_tax_1',
											'-1230-district_tax_1',
												) );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1100-wage';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL );

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

		//PSEA accounts
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$filter_data['pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40), TRUE );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );


		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_options']['effective_date_order'] = 'Wage Effective Date';
		unset($filter_data['sort_options']['effective_date']);
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		//Get a unique list of states each employee belongs to
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByCompanyId( $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$setup_data['state_options'][$u_obj->getProvince()] = $u_obj->getProvince();
			}
		}

		//Quarters
		$filter_data['year_options'] = $year_options;

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('setup_data', $setup_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/FormW2.tpl');

		break;
}
?>