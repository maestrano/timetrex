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
 * $Id: Form1099Misc.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require(Environment::getBasePath() .'/classes/fpdi/fpdi.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_form1099misc') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Form 1099-Misc Report'));  // See index.php

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

$column_ps_entry_name_map = array(
								'4' => @array( $setup_data['4_psea_ids'], $setup_data['4_exclude_psea_ids'] ),
								'6' => @array( $setup_data['6_psea_ids'], $setup_data['6_exclude_psea_ids'] ),
								'7' => @array( $setup_data['7_psea_ids'], $setup_data['7_exclude_psea_ids'] ),
								);

if ( isset($filter_data['transaction_start_date']) ) {
	$filter_data['transaction_start_date'] = TTDate::parseDateTime($filter_data['transaction_start_date']);
}

if ( isset($filter_data['transaction_end_date']) ) {
	$filter_data['transaction_end_date'] = TTDate::parseDateTime($filter_data['transaction_end_date']);
}

if ( !isset($filter_data['include_user_ids']) ) {
	$filter_data['include_user_ids'] = array();
}
if ( !isset($filter_data['exclude_user_ids']) ) {
	$filter_data['exclude_user_ids'] = array();
}
if ( !isset($filter_data['user_status_ids']) ) {
	$filter_data['user_status_ids'] = array();
}
if ( !isset($filter_data['group_ids']) ) {
	$filter_data['group_ids'] = array();
}
if ( !isset($filter_data['branch_ids']) ) {
	$filter_data['branch_ids'] = array();
}
if ( !isset($filter_data['department_ids']) ) {
	$filter_data['department_ids'] = array();
}
if ( !isset($filter_data['user_title_ids']) ) {
	$filter_data['user_title_ids'] = array();
}

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
				$filter_data['user_id'][] = $u_obj->getId();
			}

			if ( isset($filter_data['user_id']) ) {
				$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
				$pseallf->getByCompanyId( $current_company->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pseal_obj = $pseallf->getCurrent();
				}

				//
				//Get all data for the form.
				//
				$ein = str_replace(array('-', ' '), '', $current_company->getBusinessNumber() );


				//PS Account Amounts...
				//Get employees who have recieved pay stubs.
				$pself = TTnew( 'PayStubEntryListFactory' );
				//$pself->getReportByCompanyIdAndUserIdAndPayPeriodId( $current_company->getId(), $filter_data['user_ids'], $pay_period_ids );
				$pself->getReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
				foreach( $pself as $pse_obj ) {
					$user_id = $pse_obj->getColumn('user_id');
					$pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');

					if ( isset($raw_rows[$user_id][$pay_stub_entry_name_id]) ) {
						$raw_rows[$user_id][$pay_stub_entry_name_id] = bcadd( $raw_rows[$user_id][$pay_stub_entry_name_id], $pse_obj->getColumn('amount') );
					} else {
						$raw_rows[$user_id][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
					}

				}

				//
				//Figure out state/locality wages/taxes.
				//
				$cdlf = TTnew( 'CompanyDeductionListFactory' );
				$cdlf->getByCompanyIdAndStatusIdAndTypeId( $current_company->getId(), array(10,20), 10 );
				if ( $cdlf->getRecordCount() > 0 ) {
					foreach( $cdlf as $cd_obj ) {
						$tax_deductions[] = array(
													'id' => $cd_obj->getId(),
													'province' => $cd_obj->getProvince(),
													'district' => $cd_obj->getDistrict(),
													'pay_stub_entry_account_id' => $cd_obj->getPayStubEntryAccount(),
													'include' => $cd_obj->getIncludePayStubEntryAccount(),
													'exclude' => $cd_obj->getExcludePayStubEntryAccount(),
													'user_ids' => $cd_obj->getUser()
													);
					}
				}

				$border = 0;

				$adjust_x = 0;
				$adjust_y = 0;

				$pdf = new fpdi();
				$pdf->SetFont('freeserif','',10);

				if ( $show_background == TRUE ) {
					$pagecount = $pdf->setSourceFile(Environment::getBasePath().'interface'. DIRECTORY_SEPARATOR .'forms'. DIRECTORY_SEPARATOR .'us'. DIRECTORY_SEPARATOR .'tax'. DIRECTORY_SEPARATOR .'f1099misc.pdf');

					//Import original Gov't supplied PDF.
					$tplidx[1] = $pdf->ImportPage(1);
					$tplidx[2] = $pdf->ImportPage(2);
					$tplidx[3] = $pdf->ImportPage(3);
					$tplidx[4] = $pdf->ImportPage(4);
					$tplidx[5] = $pdf->ImportPage(5);
					$tplidx[6] = $pdf->ImportPage(6);
				}

				if ( isset($raw_rows) ) {
					$ulf = TTnew( 'UserListFactory' );

					$x=0;
					foreach($raw_rows as $user_id => $raw_row) {
						$user_obj = $ulf->getById( $user_id )->getCurrent();

						//Handle state/district data here
						//FIXME: Loop through each raw_row pay stub account IDs, and match them to tax deductions
						//that way if a user is removed from a tax deduction half way through the year it will
						//still match up, assuming it isn't deleted.
						if ( isset($tax_deductions) AND isset($tax_deductions['user_ids']) ) {
							foreach( $tax_deductions as $tax_deduction_arr ) {
								if ( in_array( $user_id, $tax_deduction_arr['user_ids'] ) ) {
									Debug::Text('Found user in Tax Deduction ID: '. $tax_deduction_arr['id'] .' Pay Stub Entry Account ID: '. $tax_deduction_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__,10);

									if ( $tax_deduction_arr['province'] != '' AND $tax_deduction_arr['district'] == '' ) {
										//State Wages/Taxes
										//Handle two states here, just check if $tmp_rows[$x]['state_1'] isset,
										//if it is, move on to state 2.
										$lines_arr['state'][] = array(
																	'state' => $tax_deduction_arr['province'],
																	'wage' => Misc::MoneyFormat( Misc::sumMultipleColumns( $raw_row, $tax_deduction_arr['include'] ), FALSE ),
																	'tax' => Misc::MoneyFormat( Misc::sumMultipleColumns( $raw_row, array( $tax_deduction_arr['pay_stub_entry_account_id'] ) ), FALSE ),
																	);

									}
								} else {
									Debug::Text('DID NOT Find user in Tax Deduction ID: '. $tax_deduction_arr['id'], __FILE__, __LINE__, __METHOD__,10);
								}
							}
						}

						$lines_arr['4'] = Misc::calculateMultipleColumns( $raw_row, $column_ps_entry_name_map['4'][0], $column_ps_entry_name_map['4'][1] );
						$lines_arr['6'] = Misc::calculateMultipleColumns( $raw_row, $column_ps_entry_name_map['6'][0], $column_ps_entry_name_map['6'][1] );
						$lines_arr['7'] = Misc::calculateMultipleColumns( $raw_row, $column_ps_entry_name_map['7'][0], $column_ps_entry_name_map['7'][1] );
						//print_r($lines_arr);

						$pdf->setMargins(0,0,0,0);
						$pdf->SetAutoPageBreak(FALSE);
						$pdf->SetFont('freeserif','',10);

						$pages = array(1,2,4,5);

						foreach( $pages as $page ) {
							$pdf->AddPage();
							if ( isset($tplidx[$page]) ) {
								$pdf->useTemplate($tplidx[$page],0,0);
							}

							if ( $show_background == TRUE ) {
								$pdf->SetFont('freeserif','B', 24);
								$pdf->setFillColor( 255,255,255 );
								if ( $page == 1 ) {
									$pdf->setXY( Misc::AdjustXY(152, $adjust_x), Misc::AdjustXY(28, $adjust_y) );
								} elseif ( in_array( $page, array(2,4,5) ) ) {
									$pdf->setXY( Misc::AdjustXY(151, $adjust_x), Misc::AdjustXY(28, $adjust_y) );
								}
								$pdf->Cell(10,7, date('y', $filter_data['transaction_end_date']) , $border, 0, 'C', 1);
								$pdf->SetFont('freeserif','', 10);
							}

							//Company Info
							$pdf->setXY(25,30);
							$pdf->Cell(65,5,$current_company->getName(), $border, 0, 'L');

							$pdf->setXY(25,35);
							$pdf->Cell(65,5,$current_company->getAddress1().' '.$current_company->getAddress2(), $border, 0, 'L');

							$pdf->setXY(25,40);
							$pdf->Cell(65,5,$current_company->getCity().', '.$current_company->getProvince() .' '. $current_company->getPostalCode(), $border, 0, 'L');

							$pdf->setXY(25,45);
							$pdf->Cell(65,5, $current_company->getWorkPhone(), $border, 0, 'L');

							//Payers federal identifcation number
							$pdf->setXY(17,63);
							$pdf->Cell(40,5, $ein, $border, 0, 'L');

							//Recipient identifcation number
							$pdf->setXY(62,63);
							$pdf->Cell(40,5, $user_obj->getSIN(), $border, 0, 'L');

							//Employee Info
							$pdf->setXY(17,73);
							$pdf->Cell(40,5,$user_obj->getFirstName().' '. $user_obj->getLastName(), $border, 0, 'L');

							$pdf->setXY(17,90);
							$pdf->Cell(75,5,$user_obj->getAddress1().' '.$user_obj->getAddress2(), $border, 0, 'L');

							$pdf->setXY(17,103);
							$pdf->Cell(75,5,$user_obj->getCity().', '.$user_obj->getProvince() .' '. $user_obj->getPostalCode(), $border, 0, 'L');

							if ( isset($lines_arr) ) {
								if ( $lines_arr['4'] > 0 ) {
									$pdf->setXY(142,50);
									$pdf->Cell(30,5, Misc::MoneyFormat($lines_arr['4'], FALSE), $border, 0, 'L');

								}
								if ( $lines_arr['6'] > 0 ) {
									$pdf->setXY(142,65);
									$pdf->Cell(30,5, Misc::MoneyFormat($lines_arr['6'], FALSE), $border, 0, 'L');

								}
								if ( $lines_arr['7'] > 0 ) {
									$pdf->setXY(107,82);
									$pdf->Cell(30,5, Misc::MoneyFormat($lines_arr['7'], FALSE), $border, 0, 'L');
								}

								if ( isset($lines_arr['state']) AND is_array($lines_arr['state']) ) {
									$s=0;
									foreach( $lines_arr['state'] as $state_data ) {
										if ( $s == 0 ) {
											$state_y = 124;
										} elseif ( $s == 1 ) {
											$state_y = 129;
										} else {
											continue;
										}

										$pdf->setXY(107,$state_y);
										$pdf->Cell(30,5, Misc::MoneyFormat($state_data['tax'], FALSE), $border, 0, 'L');

										if ( isset($setup_data['state'][$state_data['state']]['state_id']) ) {
											$state_id = $setup_data['state'][$state_data['state']]['state_id'];
										} else {
											$state_id = NULL;
										}

										$pdf->setXY(139,$state_y);
										$pdf->Cell(30,5, $state_data['state'] .' '. $state_id, $border, 0, 'L');

										$pdf->setXY(177,$state_y);
										$pdf->Cell(30,5, Misc::MoneyFormat($state_data['wage'], FALSE), $border, 0, 'L');

										$s++;
									}
									unset($state_data, $state_id);
								}
							}

							if ( isset($filter_data['include_instruction']) AND $page == 2 ) {
								//Add instruction page.
								$pdf->AddPage();
								if ( isset($tplidx[3]) ) {
									$pdf->useTemplate($tplidx[3],0,0);
								}
							}
							if (  isset($filter_data['include_instruction']) AND $page == 5 ) {
								//Add instruction page.
								$pdf->AddPage();
								if ( isset($tplidx[6]) ) {
									$pdf->useTemplate($tplidx[6],0,0);
								}
							}
						}

						unset($lines_arr);
					}
				} elseif ( $show_background == TRUE ) {
					for( $i=1; $i <= 6; $i++ ) {
						$pdf->AddPage();
						$pdf->useTemplate($tplidx[$i],0,0);

						if ( $show_background == TRUE ) {
							$pdf->SetFont('freeserif','B', 24);
							$pdf->setFillColor( 255,255,255 );
							if ( $i == 1 ) {
								$pdf->setXY( Misc::AdjustXY(152, $adjust_x), Misc::AdjustXY(28, $adjust_y) );
							} elseif ( in_array( $i, array(2,4,5) ) ) {
								$pdf->setXY( Misc::AdjustXY(151, $adjust_x), Misc::AdjustXY(28, $adjust_y) );
							}
							$pdf->Cell(10,7, date('y', $filter_data['transaction_end_date']) , $border, 0, 'C', 1);
							$pdf->SetFont('freeserif','', 10);
						}

					}
				}

				//Finish off PDF
				$output = $pdf->Output('','S');

				if ( Debug::getVerbosity() == 11 ) {
					Debug::Display();
				} else {
					Misc::FileDownloadHeader('f1099misc.pdf', 'application/pdf', strlen($output));
					echo $output;
				}
				exit;
			}
		} else {
			echo TTi18n::gettext('No Employees Match Your Criteria!'). "br>\n";
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
				$filter_data['transaction_start_date'] = TTDate::getBeginYearEpoch();
				$filter_data['transaction_end_date'] = TTDate::getEndYearEpoch();

			}
		}

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

		//Get a unique list of states each employee belongs to
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByCompanyId( $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$setup_data['state_options'][$u_obj->getProvince()] = $u_obj->getProvince();
			}
		}

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('setup_data', $setup_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/Form1099Misc.tpl');

		break;
}
?>