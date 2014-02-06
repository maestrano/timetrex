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
 * $Id: Form940ez.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require(Environment::getBasePath() .'/classes/fpdi/fpdi.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_form940ez') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Form 940-EZ Report')); // See index.php

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

$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
$pseallf->getByCompanyId( $current_company->getId() );
if ( $pseallf->getRecordCount() > 0 ) {
	$pseal_obj = $pseallf->getCurrent();
}

$cf = TTnew( 'CompanyFactory' );
$state_options = $cf->getOptions('province', 'US' );

$column_ps_entry_name_map = array(
								'p1_1' => @$setup_data['p1_1_psea_ids'],
								'p1_2' => @$setup_data['p1_2_psea_ids'],
								);

$pplf = TTnew( 'PayPeriodListFactory' );
$year_options = $pplf->getYearsArrayByCompanyId( $current_company->getId() );

$quarter_dates = array(
						1 => array( 'start' => mktime(0,0,0,1,1, $filter_data['year'] ),'end' => mktime(0,0,-1,4,1, $filter_data['year'] ) ),
						2 => array( 'start' => mktime(0,0,0,4,1, $filter_data['year'] ),'end' => mktime(0,0,-1,7,1, $filter_data['year'] ) ),
						3 => array( 'start' => mktime(0,0,0,7,1, $filter_data['year'] ),'end' => mktime(0,0,-1,10,1, $filter_data['year'] ) ),
						4 => array( 'start' => mktime(0,0,0,10,1, $filter_data['year'] ),'end' => mktime(0,0,-1,13,1, $filter_data['year'] ) ),
						);

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array() );


$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
switch ($action) {
	case 'print_form':
	case 'display_form':
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
				$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
				$pseallf->getByCompanyId( $current_company->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pseal_obj = $pseallf->getCurrent();
				}

				//
				//Get all data for the form.
				//

				foreach( $quarter_dates as $quarter_id => $quarter_dates_arr ) {
					//Get Pay Periods in date range.
					Debug::Text('Start Date: '. TTDate::getDate('DATE+TIME', $quarter_dates_arr['start']) .' End Date: '. TTDate::getDate('DATE+TIME', $quarter_dates_arr['end']), __FILE__, __LINE__, __METHOD__,10);

					$pplf = TTnew( 'PayPeriodListFactory' );
					$pplf->getByCompanyIdAndTransactionStartDateAndTransactionEndDate( $current_company->getId(), $quarter_dates_arr['start'], $quarter_dates_arr['end'] );
					if ( $pplf->getRecordCount() > 0 ) {
						foreach($pplf as $pp_obj) {
							$pay_period_ids[] = $pp_obj->getID();
						}
					}

					if ( isset($pay_period_ids) ) {
						$payments_over_cutoff = 7000;

						//PS Account Amounts...
						//Get employees who have recieved pay stubs.
						$pself = TTnew( 'PayStubEntryListFactory' );
						$pself->getReportByCompanyIdAndUserIdAndPayPeriodId( $current_company->getId(), $filter_data['user_ids'], $pay_period_ids );
						if ( $pself->getRecordCount() > 0 ) {
							foreach( $pself as $pse_obj ) {
								$user_id = $pse_obj->getColumn('user_id');
								//$pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');
								$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

								if ( isset($ps_entries[$pay_stub_entry_name_id]) ) {
									$ps_entries[$pay_stub_entry_name_id] = bcadd($ps_entries[$pay_stub_entry_name_id],$pse_obj->getColumn('amount'),2 );
								} else {
									$ps_entries[$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
								}

								if ( isset($user_quarter_ps_entries[$user_id][$pay_stub_entry_name_id]) ) {
									$user_quarter_ps_entries[$quarter_id][$user_id][$pay_stub_entry_name_id] = bcadd($user_quarter_ps_entries[$quarter_id][$user_id][$pay_stub_entry_name_id],$pse_obj->getColumn('amount'),2 );
								} else {
									$user_quarter_ps_entries[$quarter_id][$user_id][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
								}

								if ( isset($user_total_ps_entries[$user_id][$pay_stub_entry_name_id]) ) {
									$user_total_ps_entries[$user_id][$pay_stub_entry_name_id] = bcadd($user_total_ps_entries[$user_id][$pay_stub_entry_name_id],$pse_obj->getColumn('amount'),2 );
								} else {
									$user_total_ps_entries[$user_id][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
								}

							}

							$lines_arr[$quarter_id]['p1_1'] = Misc::sumMultipleColumns( $ps_entries, $column_ps_entry_name_map['p1_1']);
							$lines_arr[$quarter_id]['p1_2'] = Misc::sumMultipleColumns( $ps_entries, $column_ps_entry_name_map['p1_2']);
							$lines_arr[$quarter_id]['p3_under_cutoff'] = 0;

							//Get Line3 for each user.
							foreach( $user_quarter_ps_entries[$quarter_id] as $user_id => $user_quarter_ps_entry_arr ) {
									$user_quarter_lines_arr[$user_id]['p1_1'] = Misc::sumMultipleColumns( $user_quarter_ps_entry_arr, $column_ps_entry_name_map['p1_1']);
									$user_quarter_lines_arr[$user_id]['p1_2'] = Misc::sumMultipleColumns( $user_quarter_ps_entry_arr, $column_ps_entry_name_map['p1_2']);
									$tmp_quarter_user_payment = ($user_quarter_lines_arr[$user_id]['p1_1'] - $user_quarter_lines_arr[$user_id]['p1_2']);

									$user_total_lines_arr[$user_id]['p1_1'] = Misc::sumMultipleColumns( $user_total_ps_entries[$user_id], $column_ps_entry_name_map['p1_1']);
									$user_total_lines_arr[$user_id]['p1_2'] = Misc::sumMultipleColumns( $user_total_ps_entries[$user_id], $column_ps_entry_name_map['p1_2']);
									$tmp_total_user_payment = ($user_total_lines_arr[$user_id]['p1_1'] - $user_total_lines_arr[$user_id]['p1_2']);

									Debug::Text('User ID: '. $user_id .' Quarter Payment: '. $tmp_quarter_user_payment .' Total Payment: '. $tmp_total_user_payment, __FILE__, __LINE__, __METHOD__,10);
									if ( $tmp_total_user_payment <= $payments_over_cutoff ) {
										Debug::Text('Under - Under Cutoff: '. $tmp_quarter_user_payment, __FILE__, __LINE__, __METHOD__,10);
										$lines_arr[$quarter_id]['p3_under_cutoff'] += $tmp_quarter_user_payment;
									} else {
										//Handle under cutoff
										$tmp_user_remaining_under_cutoff = $payments_over_cutoff - ($tmp_total_user_payment - $tmp_quarter_user_payment);
										if ($tmp_user_remaining_under_cutoff > 0 ) {
											Debug::Text('Under - Over Cutoff, Remaining: '. $tmp_user_remaining_under_cutoff, __FILE__, __LINE__, __METHOD__,10);
											$lines_arr[$quarter_id]['p3_under_cutoff'] += $payments_over_cutoff - ($tmp_total_user_payment - $tmp_quarter_user_payment);
										} else {
											Debug::Text('Under - WAY Over Cutoff, None remaining...', __FILE__, __LINE__, __METHOD__,10);
										}
									}
							}

							$lines_arr[$quarter_id]['p1_3'] = ($lines_arr[$quarter_id]['p1_1'] - $lines_arr[$quarter_id]['p1_2']) - $lines_arr[$quarter_id]['p3_under_cutoff'];
							$lines_arr[$quarter_id]['p1_4'] = $lines_arr[$quarter_id]['p1_2'] + $lines_arr[$quarter_id]['p1_3'];
							$lines_arr[$quarter_id]['p1_5'] = $lines_arr[$quarter_id]['p1_1'] - $lines_arr[$quarter_id]['p1_4'];
							$lines_arr[$quarter_id]['p1_6'] = bcmul( $lines_arr[$quarter_id]['p1_5'], 0.008);
						}
						unset($user_id);
						//var_dump($user_ps_entries);

					}
					unset($pay_period_ids, $ps_entries);
				}

				//Calc Part 1, Line 3 here.
				if ( isset($lines_arr) ) {
					$lines_arr['total'] = Misc::ArrayAssocSum($lines_arr, NULL, 6);
					Debug::Arr($lines_arr, 'Lines Array: ', __FILE__, __LINE__, __METHOD__,10);

					//Line 8
					$p1_line8 = $lines_arr['total']['p1_6'] - 0;
				}

				$border = 0;
				$pdf= new fpdi();

				//Import original Gov't supplied PDF.
				if ( $show_background == TRUE ) {
					$pagecount = $pdf->setSourceFile(Environment::getBasePath().'interface'. DIRECTORY_SEPARATOR .'forms'. DIRECTORY_SEPARATOR .'us'. DIRECTORY_SEPARATOR .'tax'. DIRECTORY_SEPARATOR .'f940ez.pdf');
					$tplidx = $pdf->ImportPage(1);
				}

				$pdf->setMargins(0,0,0,0);
				$pdf->SetAutoPageBreak(FALSE);
				$pdf->SetFont('freeserif','',10);

				$pdf->AddPage();
				if ( isset($tplidx) ) {
					$pdf->useTemplate($tplidx,0,0);
				}

				$pdf->setXY(40,39);
				$pdf->Cell(75,6,$current_company->getName(), $border, 0, 'L');

				$pdf->setXY(157,39);
				$pdf->Cell(10,6,$filter_data['year'], $border, 0, 'R');

				$pdf->setXY(122,47);
				$pdf->Cell(45,6,$current_company->getBusinessNumber(), $border, 0, 'R');

				$pdf->setXY(40,56);
				$pdf->Cell(75,6,$current_company->getAddress1() .' '. $current_company->getAddress2(), $border, 0, 'L');

				$pdf->setXY(122,56);
				$pdf->Cell(45,6,$current_company->getCity().', '. $current_company->getProvince() .' '. $current_company->getPostalCode(), $border, 0, 'R');


				if ( isset($lines_arr) ) {
					//Line A
					$pdf->setXY(173,69);
					$pdf->Cell(25,6, Misc::getBeforeDecimal( Misc::MoneyFormat( '0.00', FALSE) ), $border, 0, 'R');

					$pdf->setXY(198,69);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat( '0.00', FALSE) ), $border, 0, 'L');

					//State
					$pdf->setXY(174,73);
					$pdf->Cell(30,6, Option::getByKey( $setup_data['state_id'], $state_options), $border, 0, 'R');

					//State Reporting Number
					$pdf->setXY(174,77);
					$pdf->Cell(30,6, $setup_data['state_report_number'], $border, 0, 'R');

					//Part1
					//Line1
					$pdf->setXY(173,94);
					$pdf->Cell(25,6, Misc::getBeforeDecimal( Misc::MoneyFormat( $lines_arr['total']['p1_1'], FALSE) ), $border, 0, 'R');

					$pdf->setXY(198,94);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat( $lines_arr['total']['p1_1'], FALSE) ), $border, 0, 'L');

					//Line2
					$pdf->setXY(135,106);
					$pdf->Cell(25,6, Misc::getBeforeDecimal( Misc::MoneyFormat( $lines_arr['total']['p1_2'], FALSE) ), $border, 0, 'R');

					$pdf->setXY(160,106);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat( $lines_arr['total']['p1_2'], FALSE) ), $border, 0, 'L');

					//Line3
					$pdf->setXY(135,115);
					$pdf->Cell(25,6, Misc::getBeforeDecimal( Misc::MoneyFormat( $lines_arr['total']['p1_3'], FALSE) ), $border, 0, 'R');

					$pdf->setXY(160,115);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat( $lines_arr['total']['p1_3'], FALSE) ), $border, 0, 'L');

					//Line4
					$pdf->setXY(173,119);
					$pdf->Cell(25,6, Misc::getBeforeDecimal( Misc::MoneyFormat( $lines_arr['total']['p1_4'], FALSE) ), $border, 0, 'R');

					$pdf->setXY(198,119);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat($lines_arr['total']['p1_4'], FALSE) ), $border, 0, 'L');

					//Line5
					$pdf->setXY(173,124);
					$pdf->Cell(25,6, Misc::getBeforeDecimal( Misc::MoneyFormat($lines_arr['total']['p1_5'], FALSE) ), $border, 0, 'R');

					$pdf->setXY(198,124);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat($lines_arr['total']['p1_5'], FALSE) ), $border, 0, 'L');

					//Line 6
					$pdf->setXY(173,128);
					$pdf->Cell(25,6, Misc::getBeforeDecimal( Misc::MoneyFormat($lines_arr['total']['p1_6'], FALSE) ), $border, 0, 'R');

					$pdf->setXY(198,128);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat($lines_arr['total']['p1_6'], FALSE) ), $border, 0, 'L');

					//Line 7
					$pdf->setXY(173,132);
					$pdf->Cell(25,6, Misc::getBeforeDecimal( Misc::MoneyFormat('0.00', FALSE) ), $border, 0, 'R');

					$pdf->setXY(198,132);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat('0.00', FALSE) ), $border, 0, 'L');

					//Line 8
					$pdf->setXY(173,136);
					$pdf->Cell(25,6, Misc::getBeforeDecimal( Misc::MoneyFormat( $p1_line8, FALSE) ), $border, 0, 'R');

					$pdf->setXY(198,136);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat( $p1_line8, FALSE) ), $border, 0, 'L');

					if ( $lines_arr['total']['p1_6'] > 500 ) {
						//Col-1
						$p2_col1 = bcmul( @$lines_arr[1]['p3_under_cutoff'], 0.008, 2);
						$pdf->setXY(40,158);
						$pdf->Cell(25,6, Misc::MoneyFormat( $p2_col1, FALSE), $border, 0, 'R');

						//Col-2
						$p2_col2 = bcmul( @$lines_arr[2]['p3_under_cutoff'], 0.008, 2);
						$pdf->setXY(71,158);
						$pdf->Cell(25,6, Misc::MoneyFormat( $p2_col2, FALSE), $border, 0, 'R');

						//Col-3
						$p2_col3 = bcmul( @$lines_arr[3]['p3_under_cutoff'], 0.008, 2);
						$pdf->setXY(102,158);
						$pdf->Cell(25,6, Misc::MoneyFormat( $p2_col3, FALSE), $border, 0, 'R');

						//Col-4
						$p2_col4 = bcsub( $lines_arr['total']['p1_6'], bcadd( bcadd($p2_col1, $p2_col2), $p2_col3) );
						$pdf->setXY(138,158);
						$pdf->Cell(25,6, Misc::MoneyFormat( $p2_col4, FALSE), $border, 0, 'R');

						//Col-Total
						$pdf->setXY(175,158);
						$pdf->Cell(25,6, Misc::MoneyFormat( $lines_arr['total']['p1_6'], FALSE), $border, 0, 'R');
					}

					//Voucher
					$ein = @explode('-', $current_company->getBusinessNumber() );

					if ( isset($ein[0]) AND isset($ein[1]) ) {
						$pdf->setXY(15,236);
						$pdf->Cell(13,6,$ein[0], $border, 0, 'R');

						$pdf->setXY(29,236);
						$pdf->Cell(40,6,$ein[1], $border, 0, 'L');
					}

					$pdf->setXY(82,244);
					$pdf->Cell(75,6,$current_company->getName(), $border, 0, 'L');

					$pdf->setXY(82,253);
					$pdf->Cell(45,6,$current_company->getAddress1().' '. $current_company->getAddress2(), $border, 0, 'L');

					$pdf->setXY(82,261);
					$pdf->Cell(45,6,$current_company->getCity().', '. $current_company->getProvince() .' '. $current_company->getPostalCode(), $border, 0, 'L');


					$pdf->setXY(157,234);
					$pdf->Cell(35,6,Misc::getBeforeDecimal( Misc::MoneyFormat( $lines_arr['total']['p1_6'], FALSE) ), $border, 0, 'R');

					$pdf->setXY(193,234);
					$pdf->Cell(6,6, Misc::getAfterDecimal( Misc::MoneyFormat( $lines_arr['total']['p1_6'], FALSE) ), $border, 0, 'L');
				}

				$output = $pdf->Output('','S');

				if ( Debug::getVerbosity() == 11 ) {
					Debug::Display();
				} else {
					Misc::FileDownloadHeader('f940ez.pdf', 'application/pdf', strlen($output));
					echo $output;
				}
				exit;
			}
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
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );
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

		//Deduction PSEA accounts
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$filter_data['deduction_pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(20,30,40), TRUE );
		$filter_data['earning_pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,40), TRUE );
		$filter_data['income_pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,30,40), TRUE );

		//Get employee list
		//$filter_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );

		//Quarters
		$filter_data['year_options'] = $year_options;

		$setup_data['state_options'] = $state_options;

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('setup_data', $setup_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/Form940ez.tpl');

		break;
}
?>