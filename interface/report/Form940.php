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
 * $Id: Form940.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_form940') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Form 940 Report')); // See index.php

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
								'p2_3' => @array( $setup_data['total_payment_psea_ids'], $setup_data['exclude_total_payment_psea_ids'] ),
								'p2_4' => @array( $setup_data['exempt_payment_psea_ids'], $setup_data['exclude_exempt_payment_psea_ids'] ),
								);

$pplf = TTnew( 'PayPeriodListFactory' );
$year_options = $pplf->getYearsArrayByCompanyId( $current_company->getId() );

$quarter_dates = array(
						1 => array( 'start' => mktime(0,0,0,1,1, $filter_data['year'] ),'end' => mktime(0,0,-1,4,1, $filter_data['year'] ) ),
						2 => array( 'start' => mktime(0,0,0,4,1, $filter_data['year'] ),'end' => mktime(0,0,-1,7,1, $filter_data['year'] ) ),
						3 => array( 'start' => mktime(0,0,0,7,1, $filter_data['year'] ),'end' => mktime(0,0,-1,10,1, $filter_data['year'] ) ),
						4 => array( 'start' => mktime(0,0,0,10,1, $filter_data['year'] ),'end' => mktime(0,0,-1,13,1, $filter_data['year'] ) ),
						);

//Get a unique list of states each employee belongs to
$cf = TTnew( 'CompanyFactory' );
$state_options = Misc::prependArray( array( 0 => TTi18n::getText('- Multi-state Employer -') ), $cf->getOptions('province', 'US' ) );
/*
$ulf = TTnew( 'UserListFactory' );
$ulf->getByCompanyId( $current_company->getId() );
if ( $ulf->getRecordCount() > 0 ) {
	foreach( $ulf as $u_obj ) {
		$state_options[$u_obj->getProvince()] = $u_obj->getProvince();
	}
}
*/

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
				$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
				$pseallf->getByCompanyId( $current_company->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pseal_obj = $pseallf->getCurrent();
				}

				require_once( Environment::getBasePath() .'/classes/fpdi/fpdi.php');
				require_once( Environment::getBasePath() .'/classes/tcpdf/tcpdf.php');
				require_once( Environment::getBasePath() .'/classes/GovernmentForms/GovernmentForms.class.php');

				$gf = new GovernmentForms();

				$f940 = $gf->getFormObject( '940', 'US' );
				//$f940->setDebug(FALSE);
				$f940->setShowBackground( $show_background );

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
						$payments_over_cutoff = $f940->payment_cutoff_amount;

						//PS Account Amounts...
						//Get employees who have recieved pay stubs.
						$pself = TTnew( 'PayStubEntryListFactory' );
						$pself->getReportByCompanyIdAndUserIdAndPayPeriodId( $current_company->getId(), $filter_data['user_ids'], $pay_period_ids );
						Debug::Text('Record Count: '. $pself->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
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

							$lines_arr[$quarter_id]['p2_3'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['p2_3'][0], $column_ps_entry_name_map['p2_3'][1] );
							$lines_arr[$quarter_id]['p2_4'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['p2_4'][0], $column_ps_entry_name_map['p2_4'][1] );
							$lines_arr[$quarter_id]['p2_5'] = 0;

							//Get over cutoff amounts for each user.
							$i=0;
							foreach( $user_quarter_ps_entries[$quarter_id] as $user_id => $user_quarter_ps_entry_arr ) {
								$user_quarter_lines_arr[$user_id]['p2_3'] = Misc::calculateMultipleColumns( $user_quarter_ps_entry_arr, $column_ps_entry_name_map['p2_3'][0], $column_ps_entry_name_map['p2_3'][1] );
								$user_quarter_lines_arr[$user_id]['p2_4'] = Misc::calculateMultipleColumns( $user_quarter_ps_entry_arr, $column_ps_entry_name_map['p2_4'][0], $column_ps_entry_name_map['p2_4'][1] );
								$tmp_quarter_user_payment = ($user_quarter_lines_arr[$user_id]['p2_3'] - $user_quarter_lines_arr[$user_id]['p2_4']);

								if ( !isset($user_total_lines_arr[$user_id]['p2_3']) ) {
									$user_total_lines_arr[$user_id]['p2_3'] = 0;
								}
								if ( !isset($user_total_lines_arr[$user_id]['p2_4']) ) {
									$user_total_lines_arr[$user_id]['p2_4'] = 0;
								}
								if ( !isset($user_total_lines_arr[$user_id]['p2_5']) ) {
									$user_total_lines_arr[$user_id]['p2_5'] = 0;
								}
								if ( !isset($user_total_lines_arr[$user_id]['total_payment']) ) {
									$user_total_lines_arr[$user_id]['total_payment'] = 0;
								}

								$user_total_lines_arr[$user_id]['p2_3'] += $user_quarter_lines_arr[$user_id]['p2_3'];
								$user_total_lines_arr[$user_id]['p2_4'] += $user_quarter_lines_arr[$user_id]['p2_4'];
								$user_total_lines_arr[$user_id]['total_payment'] += $tmp_quarter_user_payment;

								//If the user exceeds the cutoff, simply minus the cutoff
								if ( $user_total_lines_arr[$user_id]['total_payment'] > $payments_over_cutoff ) {
									Debug::Text('User ID: '. $user_id .' Over Cutoff...', __FILE__, __LINE__, __METHOD__,10);

									if ( $user_total_lines_arr[$user_id]['p2_5'] == 0 ) {
										$user_total_lines_arr[$user_id]['p2_5'] = $user_total_lines_arr[$user_id]['total_payment'] - $payments_over_cutoff;
										$lines_arr[$quarter_id]['p2_5'] += $user_total_lines_arr[$user_id]['p2_5'];
										Debug::Text('User ID: '. $user_id .' Over Cutoff First Time, by: '. $user_total_lines_arr[$user_id]['p2_5'] , __FILE__, __LINE__, __METHOD__,10);
									} else {
										$user_total_lines_arr[$user_id]['p2_5'] += $tmp_quarter_user_payment;
										$lines_arr[$quarter_id]['p2_5'] += $tmp_quarter_user_payment;
										Debug::Text('User ID: '. $user_id .' Over Cutoff Other Time... Current: '. $tmp_quarter_user_payment .' Total: '. $user_total_lines_arr[$user_id]['p2_5'], __FILE__, __LINE__, __METHOD__,10);

									}
								}
								Debug::Text('User ID: '. $user_id .' Quarter Payment: '. $tmp_quarter_user_payment .' Total Payment: '. $user_total_lines_arr[$user_id]['total_payment'], __FILE__, __LINE__, __METHOD__,10);

								$i++;
							}

							$lines_arr[$quarter_id]['p2_6'] = $lines_arr[$quarter_id]['p2_4'] + $lines_arr[$quarter_id]['p2_5'];
							$lines_arr[$quarter_id]['p2_7'] = $lines_arr[$quarter_id]['p2_3'] - $lines_arr[$quarter_id]['p2_6'];
							$lines_arr[$quarter_id]['p2_8'] = $lines_arr[$quarter_id]['p2_7'] * $f940->futa_tax_before_adjustment_rate;
							$lines_arr[$quarter_id]['p3_9'] = $lines_arr[$quarter_id]['p2_7'] * $f940->futa_tax_rate;
							$lines_arr[$quarter_id]['p4_12'] = $lines_arr[$quarter_id]['p2_8'] + $lines_arr[$quarter_id]['p3_9'];
						}
						unset($user_id);
						//var_dump($user_ps_entries);

					}
					unset($pay_period_ids, $ps_entries);
				}

				$f940->year = $filter_data['year'];
				$f940->ein = $current_company->getBusinessNumber();
				$f940->name = $setup_data['name'];
				$f940->trade_name = $current_company->getName();
				$f940->address = $current_company->getAddress1() .' '. $current_company->getAddress2();
				$f940->city = $current_company->getCity();
				$f940->state = $current_company->getProvince();
				$f940->zip_code = $current_company->getPostalCode();

				if ( isset($setup_data['return_type']) AND is_array($setup_data['return_type']) ) {
					foreach( $setup_data['return_type'] as $return_type ) {
						switch ( $return_type ) {
							case 10: //Amended
								$return_type_arr[] = 'a';
								break;
							case 20: //Successor
								$return_type_arr[] = 'b';
								break;
							case 30: //No Payments
								$return_type_arr[] = 'c';
								break;
							case 40: //Final
								$return_type_arr[] = 'd';
								break;
						}
					}

					$f940->return_type = $return_type_arr;
				}

				if ( isset($setup_data['state_id']) ) {
					if ( $setup_data['state_id'] === 0 OR $setup_data['state_id'] == '00' OR $setup_data['state_id'] == '' ) {
						$f940->l1b = TRUE; //Let them set this manually.
					} else {
						if ( strlen($setup_data['state_id']) == 2 ) {
							$f940->l1a = $setup_data['state_id'];
						}
					}

				}

				//Exempt payment check boxes
				if ( isset($setup_data['exempt_payment']) AND is_array($setup_data['exempt_payment']) ) {
					foreach( $setup_data['exempt_payment'] as $return_type ) {
						switch ( $return_type ) {
							case 10: //Fringe
								$f940->l4a = TRUE;
								break;
							case 20: //Group life insurance
								$f940->l4b = TRUE;
								break;
							case 30: //Retirement/Pension
								$f940->l4c = TRUE;
								break;
							case 40: //Dependant care
								$f940->l4d = TRUE;
								break;
							case 50: //Other
								$f940->l4e = TRUE;
								break;
						}
					}
				}

				if ( isset($lines_arr) ) {
					//Calc Annual Total.
					if ( isset($lines_arr) ) {
						$lines_arr['total'] = Misc::ArrayAssocSum($lines_arr, NULL, 6);
						Debug::Arr($lines_arr, 'Lines Array: ', __FILE__, __LINE__, __METHOD__,10);
					}


					$f940->l3 = $lines_arr['total']['p2_3'];
					$f940->l4 = $lines_arr['total']['p2_4'];
					$f940->l5 = $lines_arr['total']['p2_5'];

					$f940->l10 = $setup_data['line_10'];

					$f940->l13 = $setup_data['tax_deposited'];

					$f940->l15b = TRUE;

					if ( isset($lines_arr[1]['p4_12']) ) {
						$f940->l16a = $lines_arr[1]['p4_12'];
					}
					if ( isset($lines_arr[2]['p4_12']) ) {
						$f940->l16b = $lines_arr[2]['p4_12'];
					}
					if ( isset($lines_arr[3]['p4_12']) ) {
						$f940->l16c = $lines_arr[3]['p4_12'];
					}
					if ( isset($lines_arr[4]['p4_12']) ) {
						$f940->l16d = $lines_arr[4]['p4_12'];
					}
				}

				$gf->addForm( $f940 );

				$output = $gf->output( 'PDF' );

				if ( Debug::getVerbosity() == 11 ) {
					Debug::Display();
				} else {
					Misc::FileDownloadHeader('f940.pdf', 'application/pdf', strlen($output));
					echo $output;
				}
				Debug::writeToLog();
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

		//PSEA accounts
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$filter_data['pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40), TRUE );

		$filter_data['return_type_options'] = array(
											0 => TTi18n::getText('--'),
											10 => TTi18n::getText('Amended'),
											20 => TTi18n::getText('Successor Employer'),
											30 => TTi18n::getText('No Payments to Employees'),
											40 => TTi18n::getText('Final: Business closed or stopped paying wages'),
										);

		$filter_data['exempt_payment_options'] = array(
											0 => TTi18n::getText('--'),
											10 => TTi18n::getText('4a. Fringe benefits'),
											20 => TTi18n::getText('4b. Group term life insurance'),
											30 => TTi18n::getText('4c. Retirement/Pension'),
											40 => TTi18n::getText('4d. Dependant care'),
											50 => TTi18n::getText('4e. Other'),
										);

		$filter_data['state_options'] = $state_options;

		//Get employee list
		//$filter_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );

		//Quarters
		$filter_data['year_options'] = $year_options;

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('setup_data', $setup_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/Form940.tpl');

		break;
}
?>