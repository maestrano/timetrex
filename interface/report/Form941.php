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
 * $Revision: 4492 $
 * $Id: Form941.php 4492 2011-04-02 18:42:31Z ipso $
 * $Date: 2011-04-02 11:42:31 -0700 (Sat, 02 Apr 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_form941') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Form 941 Report')); // See index.php

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
								'2' 	=> @array( $setup_data['2_psea_ids'], $setup_data['2_exclude_psea_ids'] ),
								'3' 	=> @array( $setup_data['3_psea_ids'], $setup_data['3_exclude_psea_ids'] ),
								'5a' 	=> @array( $setup_data['5a_psea_ids'], $setup_data['5a_exclude_psea_ids'] ),
								'5b' 	=> @array( $setup_data['5b_psea_ids'], $setup_data['5b_exclude_psea_ids'] ),
								'5c' 	=> @array( $setup_data['5c_psea_ids'], $setup_data['5c_exclude_psea_ids'] ),
								'8'		=> @array( $setup_data['8_psea_ids'], $setup_data['8_exclude_psea_ids'] ),
								'9' 	=> @array( $setup_data['9_psea_ids'], $setup_data['9_exclude_psea_ids'] ),
								'12a' 	=> @array( $setup_data['12a_psea_ids'], $setup_data['12a_exclude_psea_ids'] ),
								);

$pplf = TTnew( 'PayPeriodListFactory' );
$year_options = $pplf->getYearsArrayByCompanyId( $current_company->getId() );

$quarter_options = array(
						1 => TTi18n::gettext('Quarter 1 (01-Jan to 31-Mar)'),
						2 => TTi18n::gettext('Quarter 2 (01-Apr to 30-Jun)'),
						3 => TTi18n::gettext('Quarter 3 (01-Jul to 30-Sep)'),
						4 => TTi18n::gettext('Quarter 4 (01-Oct to 31-Dec)'),
						);

$quarter_dates = array(
						1 => array(
									1 => array( 'start' => mktime(0,0,0,1,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,2,1, $filter_data['year'] ) ),
									2 => array( 'start' => mktime(0,0,0,2,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,3,1, $filter_data['year'] ) ),
									3 => array( 'start' => mktime(0,0,0,3,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,4,1, $filter_data['year'] ) ),
									),
						2 => array(
									1 => array( 'start' => mktime(0,0,0,4,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,5,1, $filter_data['year'] ) ),
									2 => array( 'start' => mktime(0,0,0,5,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,6,1, $filter_data['year'] ) ),
									3 => array( 'start' => mktime(0,0,0,6,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,7,1, $filter_data['year'] ) ),
									),
						3 => array(
									1 => array( 'start' => mktime(0,0,0,7,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,8,1, $filter_data['year'] ) ),
									2 => array( 'start' => mktime(0,0,0,8,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,9,1, $filter_data['year'] ) ),
									3 => array( 'start' => mktime(0,0,0,9,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,10,1, $filter_data['year'] ) ),
									),
						4 => array(
									1 => array( 'start' => mktime(0,0,0,10,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,11,1, $filter_data['year'] ) ),
									2 => array( 'start' => mktime(0,0,0,11,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,12,1, $filter_data['year'] ) ),
									3 => array( 'start' => mktime(0,0,0,12,1, $filter_data['year'] ), 'end' => mktime(0,0,-1,13,1, $filter_data['year'] ) ),
									)
						);

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

			if ( isset($filter_data['user_ids']) AND isset($filter_data['quarter_id']) ) {
				$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
				$pseallf->getByCompanyId( $current_company->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pseal_obj = $pseallf->getCurrent();
				}

				//
				//Get all data for the form.
				//
				require_once( Environment::getBasePath() .'/classes/fpdi/fpdi.php');
				require_once( Environment::getBasePath() .'/classes/tcpdf/tcpdf.php');
				require_once( Environment::getBasePath() .'/classes/GovernmentForms/GovernmentForms.class.php');

				$gf = new GovernmentForms();

				$f941 = $gf->getFormObject( '941', 'US' );
				//$f941->setDebug(FALSE);
				$f941->setShowBackground( $show_background );


				$total_users = 0;
				foreach( $quarter_dates[$filter_data['quarter_id']] as $month_id => $quarter_dates_arr ) {
					//Get Pay Periods in date range.
					Debug::Text('Start Date: '. TTDate::getDate('DATE+TIME', $quarter_dates_arr['start']) .' End Date: '. TTDate::getDate('DATE+TIME', $quarter_dates_arr['end']), __FILE__, __LINE__, __METHOD__,10);

					$pplf = TTnew( 'PayPeriodListFactory' );
					$pplf->getByCompanyIdAndTransactionStartDateAndTransactionEndDate( $current_company->getId(), $quarter_dates_arr['start'], $quarter_dates_arr['end'] );
					if ( $pplf->getRecordCount() > 0 ) {
						foreach($pplf as $pp_obj) {
							$pay_period_ids[] = $pp_obj->getID();
							$pay_period_transaction_dates[$pp_obj->getID()] = $pp_obj->getTransactionDate();
						}
					}

					if ( isset($pay_period_ids) ) {
						$pslf = TTnew( 'PayStubListFactory' );
						$pslf->getByCompanyIdAndPayPeriodId( $current_company->getId(), $pay_period_ids );
						if ( $pslf->getRecordCount() > 0 ) {
							foreach( $pslf as $ps_obj ) {
								if ( in_array( $ps_obj->getUser(), $filter_data['user_ids']) ) {
									$pay_stub_users[] = $ps_obj->getUser();
								}
							}
							$pay_stub_users = array_unique($pay_stub_users);

							if ( count($pay_stub_users) > $total_users ) {
								$total_users = count($pay_stub_users);
							}
							unset($pay_stub_users);
						}


						foreach( $pay_period_ids as $pay_period_id ) {
							//PS Account Amounts...
							//Get employees who have recieved pay stubs.
							$pself = TTnew( 'PayStubEntryListFactory' );
							$pself->getReportByCompanyIdAndUserIdAndPayPeriodId( $current_company->getId(), $filter_data['user_ids'], $pay_period_id );
							if ( $pself->getRecordCount() > 0 ) {
								foreach( $pself as $pse_obj ) {

									//$user_id = $pse_obj->getColumn('user_id');
									//$pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');
									$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

									if ( isset($ps_entries[$pay_stub_entry_name_id]) ) {
										$ps_entries[$pay_stub_entry_name_id] = bcadd($ps_entries[$pay_stub_entry_name_id],$pse_obj->getColumn('amount'),2 );
									} else {
										$ps_entries[$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
									}
								}
							}

							if ( isset($ps_entries) ) {
								$pp_lines_arr[$month_id][$pay_period_id]['2'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['2'][0], $column_ps_entry_name_map['2'][1] );
								$pp_lines_arr[$month_id][$pay_period_id]['3'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['3'][0], $column_ps_entry_name_map['3'][1] );

								$pp_lines_arr[$month_id][$pay_period_id]['5a'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['5a'][0], $column_ps_entry_name_map['5a'][1] );
								$pp_lines_arr[$month_id][$pay_period_id]['5a2'] = bcmul( $pp_lines_arr[$month_id][$pay_period_id]['5a'], $f941->social_security_rate );
								$pp_lines_arr[$month_id][$pay_period_id]['5b'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['5b'][0], $column_ps_entry_name_map['5b'][1] );
								$pp_lines_arr[$month_id][$pay_period_id]['5b2'] = bcmul( $pp_lines_arr[$month_id][$pay_period_id]['5b'], $f941->social_security_rate );
								$pp_lines_arr[$month_id][$pay_period_id]['5c'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['5c'][0], $column_ps_entry_name_map['5c'][1] );
								$pp_lines_arr[$month_id][$pay_period_id]['5c2'] = bcmul( $pp_lines_arr[$month_id][$pay_period_id]['5c'], $f941->medicare_rate );
								$pp_lines_arr[$month_id][$pay_period_id]['5d'] =  bcadd( bcadd( $pp_lines_arr[$month_id][$pay_period_id]['5a2'], $pp_lines_arr[$month_id][$pay_period_id]['5b2']), $pp_lines_arr[$month_id][$pay_period_id]['5c2']);

								$pp_lines_arr[$month_id][$pay_period_id]['6e'] =  bcadd( $pp_lines_arr[$month_id][$pay_period_id]['3'], $pp_lines_arr[$month_id][$pay_period_id]['5d']);

								$pp_lines_arr[$month_id][$pay_period_id]['7'] = bcsub( Misc::MoneyFormat($pp_lines_arr[$month_id][$pay_period_id]['5d'], FALSE) , bcadd( bcadd($pp_lines_arr[$month_id][$pay_period_id]['5a2'], $pp_lines_arr[$month_id][$pay_period_id]['5b2']), $pp_lines_arr[$month_id][$pay_period_id]['5c2'] ) );

								//Was 7b
								$pp_lines_arr[$month_id][$pay_period_id]['8'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['8'][0], $column_ps_entry_name_map['8'][1] );

								$pp_lines_arr[$month_id][$pay_period_id]['9'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['9'][0], $column_ps_entry_name_map['9'][1] );
								$pp_lines_arr[$month_id][$pay_period_id]['12a'] = Misc::calculateMultipleColumns( $ps_entries, $column_ps_entry_name_map['12a'][0], $column_ps_entry_name_map['12a'][1] );

								$pp_lines_arr[$month_id][$pay_period_id]['10'] = bcadd( bcadd( bcadd( $pp_lines_arr[$month_id][$pay_period_id]['6e'], $pp_lines_arr[$month_id][$pay_period_id]['7']), $pp_lines_arr[$month_id][$pay_period_id]['8']), $pp_lines_arr[$month_id][$pay_period_id]['9']);
							}
							unset($ps_entries);
						}

						//Total all pay periods by month_id
						if ( isset($pp_lines_arr) ) {
							foreach( $pp_lines_arr as $month_id => $pp_data ) {
								$lines_arr[$month_id] = Misc::ArrayAssocSum($pp_data, NULL, 8);
							}
						}
					}
					unset($pay_period_ids, $ps_entries);
				}
				if ( isset($lines_arr) ) {
					$lines_arr['total'] = Misc::ArrayAssocSum($lines_arr, NULL, 6);
					//Debug::Arr($lines_arr, 'aLines Array: ', __FILE__, __LINE__, __METHOD__,10);

					if ( isset($setup_data['quarter_deposit']) AND $setup_data['quarter_deposit'] != ''  ) {
						$lines_arr['total']['11'] = Misc::MoneyFormat($setup_data['quarter_deposit'], FALSE);
					} else {
						$lines_arr['total']['11'] = $setup_data['quarter_deposit'] = $lines_arr['total']['10'];
					}
				}


				$f941->year = $filter_data['year'];
				$f941->ein = $current_company->getBusinessNumber();
				$f941->name = $setup_data['name'];
				$f941->trade_name = $current_company->getName();
				$f941->address = $current_company->getAddress1() .' '. $current_company->getAddress2();
				$f941->city = $current_company->getCity();
				$f941->state = $current_company->getProvince();
				$f941->zip_code = $current_company->getPostalCode();

				$f941->quarter = $filter_data['quarter_id'];

				if ( isset($lines_arr) ) {
					$f941->l1 = $total_users;
					$f941->l2 = $lines_arr['total']['2'];
					$f941->l3 = $lines_arr['total']['3'];
					//$f941->l5 = 9999.99;

					$f941->l5a = $lines_arr['total']['5a'];
					$f941->l5b = $lines_arr['total']['5b'];
					$f941->l5c = $lines_arr['total']['5c'];

					$f941->l7 = $lines_arr['total']['7'];
					$f941->l8 = $lines_arr['total']['8'];
					$f941->l9 = $lines_arr['total']['9'];

					$f941->l11 = $lines_arr['total']['11'];
					$f941->l12a = $lines_arr['total']['12a'];

					$f941->l15b = TRUE;

					$f941->l16 = $current_company->getProvince();

					if ( isset($setup_data['deposit_schedule']) AND $setup_data['deposit_schedule'] == 10 ) {
						if ( isset($lines_arr['1']['10']) ) {
							$f941->l17_month1 = $lines_arr['1']['10'];
						}
						if ( isset($lines_arr['2']['10']) ) {
							$f941->l17_month2 = $lines_arr['2']['10'];
						}
						if ( isset($lines_arr['3']['10']) ) {
							$f941->l17_month3 = $lines_arr['3']['10'];
						}
					} elseif ( isset($setup_data['deposit_schedule']) AND $setup_data['deposit_schedule'] == 20 ) {
						$f941sb = $gf->getFormObject( '941sb', 'US' );
						$f941sb->setShowBackground( $show_background );

						$f941sb->year = $filter_data['year'];
						$f941sb->ein = $current_company->getBusinessNumber();
						$f941sb->name = $setup_data['name'];
						$f941sb->quarter = $filter_data['quarter_id'];

						for( $i=1; $i <= 3; $i++ ) {

							for( $d=1; $d <= 31; $d++ ) {
								if ( isset($pp_lines_arr[$i]) ) {
									foreach( $pp_lines_arr[$i] as $pay_period_id => $data ) {
										$dom = TTDate::getDayOfMonth($pay_period_transaction_dates[$pay_period_id]);
										if ( $d == $dom ) {
											$f941sb_data[$i][$d] = Misc::MoneyFormat($data['10'], FALSE);
										}
									}
								}
							}
						}

						if ( isset($f941sb_data[1]) ) {
							$f941sb->month1 = $f941sb_data[1];
						}
						if ( isset($f941sb_data[2]) ) {
							$f941sb->month2 = $f941sb_data[2];
						}
						if ( isset($f941sb_data[3]) ) {
							$f941sb->month3 = $f941sb_data[3];
						}
						unset($i, $d, $f941sb_data);
					}
				}

				$gf->addForm( $f941 );

				if ( isset($f941sb) AND is_object( $f941sb ) ) {
					$gf->addForm( $f941sb );
				}

				$output = $gf->output( 'PDF' );

				if ( Debug::getVerbosity() == 11 ) {
					Debug::Display();
				} else {
					Misc::FileDownloadHeader('f941.pdf', 'application/pdf', strlen($output));
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

		//Get employee list
		//$filter_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );

		//Quarters
		$filter_data['quarter_options'] = $quarter_options;
		$filter_data['year_options'] = $year_options;
		$filter_data['deposit_schedule_options'] = array( 10 => TTi18n::gettext('Monthly'), 20 => TTi18n::gettext('Semi-Weekly') );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('setup_data', $setup_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/Form941.tpl');

		break;
}
?>