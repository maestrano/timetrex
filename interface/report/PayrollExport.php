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
 * $Revision: 1564 $
 * $Id: TimesheetSummary.php 1564 2007-12-26 20:00:13Z ipso $
 * $Date: 2007-12-26 12:00:13 -0800 (Wed, 26 Dec 2007) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_payroll_export') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Payroll Export'));  // See index.php


/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data',
												'setup_data',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
												) );

$static_columns = array();

$columns = array(					'-0010-regular_time' => TTi18n::gettext('Regular Time'),
									);

$columns = Misc::prependArray( $static_columns, $columns);

//Get all Overtime policies.
$otplf = TTnew( 'OverTimePolicyListFactory' );
$otplf->getByCompanyId($current_company->getId());
if ( $otplf->getRecordCount() > 0 ) {
	foreach ($otplf as $otp_obj ) {
		$otp_columns['-0020-over_time_policy-'.$otp_obj->getId()] = $otp_obj->getName();
	}

	$columns = array_merge( $columns, $otp_columns);
}

//Get all Premium policies.
$pplf = TTnew( 'PremiumPolicyListFactory' );
$pplf->getByCompanyId($current_company->getId());
if ( $pplf->getRecordCount() > 0 ) {
	foreach ($pplf as $pp_obj ) {
		$pp_columns['-0030-premium_policy-'.$pp_obj->getId()] = $pp_obj->getName();
	}

	$columns = array_merge( $columns, $pp_columns);
}


//Get all Absence Policies.
$aplf = TTnew( 'AbsencePolicyListFactory' );
$aplf->getByCompanyId($current_company->getId());
if ( $aplf->getRecordCount() > 0 ) {
	foreach ($aplf as $ap_obj ) {
		$ap_columns['-0040-absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
	}

	$columns = array_merge( $columns, $ap_columns);
}


$default_start_date = TTDate::getBeginMonthEpoch();
$default_end_date = TTDate::getEndMonthEpoch();

//Get all pay periods
$pplf = TTnew( 'PayPeriodListFactory' );
$pplf->getByCompanyId( $current_company->getId() );
if ( $pplf->getRecordCount() > 0 ) {
	$pp=0;
	foreach ($pplf as $pay_period_obj) {
		$pay_period_ids[] = $pay_period_obj->getId();
		$pay_period_end_dates[$pay_period_obj->getId()] = $pay_period_obj->getEndDate();

		if ( $pp == 0 ) {
			$default_start_date = $pay_period_obj->getStartDate();
			$default_end_date = $pay_period_obj->getEndDate();
		}
		$pp++;
	}

	$pplf = TTnew( 'PayPeriodListFactory' );
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));
} else {
	$pay_period_options = array();
}

if ( isset($filter_data['start_date']) ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
}

if ( isset($filter_data['end_date']) ) {
	$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array());

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
if ( $permission->Check('punch','view') == FALSE ) {
	$hlf = TTnew( 'HierarchyListFactory' );
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('punch','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('punch','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'export':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($setup_data, 'Setup Data', __FILE__, __LINE__, __METHOD__,10);

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

/*
	protected $status_options = array(
										10 => 'System',
										20 => 'Worked',
										30 => 'Absence'
									);

	protected $type_options = array(
										10 => 'Total',
										20 => 'Regular',
										30 => 'Overtime'
									);
*/

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' ) {
				unset($filter_data['start_date']);
				unset($filter_data['end_date']);
			} else {
				unset($filter_data['pay_period_ids']);
			}

			foreach( $ulf as $u_obj ) {
				$filter_data['user_id'][] = $u_obj->getId();
			}

			if ( isset($filter_data['pay_period_ids']) ) {
				//Trim sort prefix from selected pay periods.
				$tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
				$filter_data['pay_period_ids'] = array();
				foreach( $tmp_filter_pay_period_ids as $key => $filter_pay_period_id) {
					$filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
				}
				unset($key, $tmp_filter_pay_period_ids, $filter_pay_period_id);
			}

			//Get greatest end date of the selected ones.
			if ( isset($filter_data['pay_period_ids']) AND count($filter_data['pay_period_ids']) > 0 ) {
				if ( in_array('-1', $filter_data['pay_period_ids']) ) {
					$end_date = time();
				} else {
					$i=0;
					foreach ( $filter_data['pay_period_ids'] as $tmp_pay_period_id ) {
						$tmp_pay_period_id = Misc::trimSortPrefix($tmp_pay_period_id);
						if ( $i == 0 ) {
							$end_date = $pay_period_end_dates[$tmp_pay_period_id];
						} else {
							if ( $pay_period_end_dates[$tmp_pay_period_id] > $end_date ) {
								$end_date = $pay_period_end_dates[$tmp_pay_period_id];
							}
						}

						$i++;
					}
					unset($tmp_pay_period_id, $i);
				}
			} else {
				$end_date = ( isset($filter_data['end_date']) ) ? $filter_data['end_date'] : time() ;
			}
/*
			$uwlf = TTnew( 'UserWageListFactory' );
			$uwlf->getLastWageByUserIdAndDate( $filter_data['user_id'], $end_date );
			if ( $uwlf->getRecordCount() > 0 ) {
				foreach($uwlf as $uw_obj) {
					$user_wage[$uw_obj->getUser()] = $uw_obj->getBaseCurrencyHourlyRate( $uw_obj->getHourlyRate() );
				}
			}
			unset($end_date);
			//var_dump($user_wage);
*/

			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			foreach ($udtlf as $udt_obj ) {
				$user_id = $udt_obj->getColumn('id');
				$pay_period_id = $udt_obj->getColumn('pay_period_id');

				$status_id = $udt_obj->getColumn('status_id');
				$type_id = $udt_obj->getColumn('type_id');

				if ( $status_id == 10 AND $type_id == 10 ) {
					$column = 'paid_time';
				} elseif ($status_id == 10 AND $type_id == 20) {
					$column = 'regular_time';
				} elseif ($status_id == 10 AND $type_id == 30) {
					$column = 'over_time_policy-'. $udt_obj->getColumn('over_time_policy_id');
				} elseif ($status_id == 10 AND $type_id == 40) {
					$column = 'premium_policy-'. $udt_obj->getColumn('premium_policy_id');
				} elseif ($status_id == 30 AND $type_id == 10) {
					$column = 'absence_policy-'. $udt_obj->getColumn('absence_policy_id');
				} elseif ( ($status_id == 20 AND $type_id == 10 ) OR ($status_id == 10 AND $type_id == 100 ) ) {
					$column = 'worked_time';
				} else {
					$column = NULL;
				}

				//Debug::Text('Column: '. $column .' Status ID: '. $status_id .' Type ID: '. $type_id , __FILE__, __LINE__, __METHOD__,10);

				if ( $column != NULL ) {
					if ( isset($tmp_rows[$user_id][$pay_period_id][$column]) ) {
						$tmp_rows[$user_id][$pay_period_id][$column] += (int)$udt_obj->getColumn('total_time');
					} else {
						$tmp_rows[$user_id][$pay_period_id][$column] = (int)$udt_obj->getColumn('total_time');
					}
				}
			}
			//print_r($tmp_rows);

			$ulf = TTnew( 'UserListFactory' );

			$utlf = TTnew( 'UserTitleListFactory' );
			$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

			$uglf = TTnew( 'UserGroupListFactory' );
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

			$blf = TTnew( 'BranchListFactory' );
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = TTnew( 'DepartmentListFactory' );
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			if ( isset($tmp_rows) ) {
				$x=0;
				foreach($tmp_rows as $user_id => $data_a ) {
					$user_obj = $ulf->getById( $user_id )->getCurrent();

					foreach($data_a as $pay_period_id => $data_b ) {
						$rows[$x]['user_id'] = $user_obj->getId();
						$rows[$x]['first_name'] = $user_obj->getFirstName();
						$rows[$x]['middle_name'] = $user_obj->getMiddleName();
						$rows[$x]['last_name'] = $user_obj->getLastName();
						$rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
						$rows[$x]['employee_number'] = $user_obj->getEmployeeNumber();
						$rows[$x]['status'] = Option::getByKey( $user_obj->getStatus(), $user_obj->getOptions('status') );

						$rows[$x]['province'] = $user_obj->getProvince();
						$rows[$x]['country'] = $user_obj->getCountry();

						$rows[$x]['pay_period_id'] = $pay_period_id;
						$rows[$x]['pay_period_order'] = Option::getByKey($pay_period_id, $pay_period_end_dates, NULL );
						$rows[$x]['pay_period_end_date'] = Option::getByKey($pay_period_id, $pay_period_end_dates, NULL );
						$rows[$x]['pay_period'] = Option::getByKey($pay_period_id, $pay_period_options, NULL );

						$rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
						$rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options, NULL );
						$rows[$x]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
						$rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );

						foreach($data_b as $column => $total_time) {
							$rows[$x][$column] = (int)$total_time;
						}

						$x++;
					}
				}
			}
			//var_dump($rows);
			unset($tmp_rows);

			$file_name = 'payroll_export.csv';
			if ( isset($rows) ) {
				switch ( $filter_data['export_type'] ) {
					case 'adp': //ADP export format.
						$file_name = 'EPI'. $setup_data['adp']['company_code'] . $setup_data['adp']['batch_id'] .'.csv';

						$export_column_map = array();
						$static_export_column_map = array(
												'company_code' => 'Co Code',
												'batch_id' => 'Batch ID',
												'employee_number' =>  'File #',
												);

						$static_export_data_map = array(
										'company_code' => $setup_data['adp']['company_code'],
										'batch_id' => $setup_data['adp']['batch_id'],
										);

						//
						//Format allows for multiple duplicate columns.
						//ie: Hours 3 Code, Hours 3 Amount, Hours 3 Code, Hours 3 Amount, ...
						//However, we can only have a SINGLE O/T Hours column.
						//We also need to combine hours with the same code together.
						//
						ksort($setup_data['adp']['columns']);
						$setup_data['adp']['columns'] = Misc::trimSortPrefix( $setup_data['adp']['columns'] );

						foreach( $setup_data['adp']['columns'] as $column_id => $column_data ) {
							$column_name = NULL;
							if ( $column_data['hour_column'] == 'regular_time' ) {
								$column_name = 'Reg Hours';
								$export_data_map[$column_id] = trim($setup_data['adp']['columns'][$column_id]['hour_code']);
							} elseif ($column_data['hour_column'] == 'overtime' ) {
								$column_name = 'O/T Hours';
								$export_data_map[$column_id] = trim($setup_data['adp']['columns'][$column_id]['hour_code']);
							} elseif ( $column_data['hour_column'] >= 3 ) {
								$column_name = 'Hours '. $column_data['hour_column'] .' Amount';
								$export_column_map[$setup_data['adp']['columns'][$column_id]['hour_code'].'_code'] = 'Hours '. $column_data['hour_column'] .' Code';
								$export_data_map[$column_id] = trim($setup_data['adp']['columns'][$column_id]['hour_code']);
							}

							if ( $column_name != '' ) {
								$export_column_map[trim($setup_data['adp']['columns'][$column_id]['hour_code'])] = $column_name;
							}
						}
						$export_column_map = Misc::prependArray( $static_export_column_map, $export_column_map);

						//
						//Combine time from all columns with the same hours code.
						//
						$i=0;
						foreach($rows as $row) {
							foreach ( $static_export_column_map as $column_id => $column_name ) {
								if ( isset($static_export_data_map[$column_id]) ) {
									//Copy over static config values like company code/batch_id.
									$tmp_rows[$i][$column_id] = $static_export_data_map[$column_id];
								} elseif( isset($row[$column_id]) ) {
									if ( isset($static_export_column_map[$column_id]) ) {
										//Copy over employee_number. (File #)
										$tmp_rows[$i][$column_id] = $row[$column_id];
									}
								}
							}

							foreach ( $export_data_map as $column_id => $column_name ) {
								if ( !isset($tmp_rows[$i][$column_name]) ) {
									$tmp_rows[$i][$column_name] = 0;
								}

								if ( isset($row[$column_id]) ) {
									$tmp_rows[$i][$column_name] += $row[$column_id];
								}
								$tmp_rows[$i][$column_name.'_code']  = $column_name;
							}

							$i++;
						}

						//Convert time from seconds to hours.
						$convert_unit_columns = array_keys($static_export_column_map);

						foreach( $tmp_rows as $row => $data ) {
							foreach( $data as $column_id => $column_data ) {
								//var_dump($column_id,$column_data);
								if ( is_int($column_data) AND !in_array( $column_id, $convert_unit_columns ) ) {
									$tmp_rows[$row][$column_id] = TTDate::getTimeUnit( $column_data, 20 );
								}
							}
						}
						unset($row, $data, $column_id, $column_data);

						$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE );

						break;
					case 'paychex_preview': //Paychex Preview export format.
						//Add an advanced PayChex Previous format that supports rates perhaps?
						$file_name = $setup_data['paychex_preview']['client_number'] .'_TA.txt';

						ksort($setup_data['paychex_preview']['columns']);
						$setup_data['paychex_preview']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_preview']['columns'] );

						$data = NULL;
						foreach($rows as $row) {
							foreach( $setup_data['paychex_preview']['columns'] as $column_id => $column_data ) {
								if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
									$data .= str_pad($row['employee_number'], 6, ' ', STR_PAD_LEFT);
									$data .= str_pad('E'. str_pad( trim($column_data['hour_code']), 2, ' ', STR_PAD_RIGHT) , 47, ' ', STR_PAD_LEFT);
									$data .= str_pad( str_pad( TTDate::getTimeUnit( $row[$column_id], 20 ), 8, 0, STR_PAD_LEFT) , 17, ' ', STR_PAD_LEFT)."\n";
								}
							}
						}
						break;
                    case 'paychex_online': //Paychex Online Payroll CSV
                        $data = NULL;
                        ksort($setup_data['paychex_online']['columns']);
						$setup_data['paychex_online']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_online']['columns'] );

                        $earnings = array();
                        //Find all the hours codes
                        foreach( $setup_data['paychex_online']['columns'] as $column_id => $column_data ) {
							$hour_code = $column_data['hour_code'];
							$earnings[] = $hour_code;
                        }

						$export_column_map['employee_number'] = '';
                        foreach($earnings as $key => $value) {
                            $export_column_map[$value] = '';
                        }

                        $i=0;
						foreach($rows as $row) {
							if ( $i == 0 ) {
								//Include header.
								$tmp_row['employee_number'] = 'Employee Number';
                                foreach($earnings as $key => $value) {
                                    $tmp_row[$value] = $value . ' Hours';
                                }
								$tmp_rows[] = $tmp_row;
								unset($tmp_row);
							}

							//Combine all hours from the same code together.
							foreach( $setup_data['paychex_online']['columns'] as $column_id => $column_data ) {
								$hour_code = trim($column_data['hour_code']);
								if ( isset( $row[$column_id] ) AND $hour_code != '' ) {
									if ( !isset($tmp_hour_codes[$hour_code]) ) {
										$tmp_hour_codes[$hour_code] = 0;
									}
									$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$column_data['hour_code']], $row[$column_id] ); //Use seconds for math here.
								}
							}

							if ( isset($tmp_hour_codes) ) {
                                $tmp_row['employee_number'] = $row['employee_number'];
								foreach($tmp_hour_codes as $hour_code => $hours ) {
                                    $tmp_row[$hour_code] = TTDate::getTimeUnit($hours, 20);
                                }
                                $tmp_rows[] = $tmp_row;
								unset($tmp_hour_codes, $hour_code, $hours, $tmp_row);
							}

							$i++;
						}

						if ( isset( $tmp_rows) ) {
							$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
						}
						unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
						break;
					case 'ceridian_insync': //Ceridian InSync export format.
						$data = NULL;

						ksort($setup_data['ceridian_insync']['columns']);
						$setup_data['ceridian_insync']['columns'] = Misc::trimSortPrefix( $setup_data['ceridian_insync']['columns'] );

						$export_column_map = array(	'employee_number' => '', 'import_type_id' => '', 'employee_number_b' => '', 'check_type' => '',
													'hour_code' => '', 'value' => '', 'distribution' => '', 'rate' => '', 'premium' => '', 'day' => '', 'pay_period' => '');
						foreach($rows as $row) {
							foreach( $setup_data['ceridian_insync']['columns'] as $column_id => $column_data ) {
								if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
									$tmp_rows[] = array(
														'employee_number' => $row['employee_number'],
														'import_type_id' => 'COSTING',
														'employee_number_b' => str_pad( $row['employee_number'], 9, '0', STR_PAD_LEFT),
														'check_type' => 'REG',
														'hour_code' => trim($column_data['hour_code']),
														'value' => TTDate::getTimeUnit( $row[$column_id], 20 ),
														'distribution' => NULL,
														'rate' => NULL,
														'premium' => NULL,
														'day' => NULL,
														'pay_period' => NULL,
														);
								}
							}
						}

						if ( isset( $tmp_rows) ) {
							$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
						}
						unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);

						break;
					case 'millenium': //Millenium export format. Also used by Qqest.
						$data = NULL;

						ksort($setup_data['millenium']['columns']);
						$setup_data['millenium']['columns'] = Misc::trimSortPrefix( $setup_data['millenium']['columns'] );

						$export_column_map = array('employee_number' => '', 'transaction_code' => '', 'hour_code' => '', 'hours' => '');
						foreach($rows as $row) {
							foreach( $setup_data['millenium']['columns'] as $column_id => $column_data ) {
								if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
									$tmp_rows[] = array(
														'employee_number' => $row['employee_number'],
														'transaction_code' => 'E',
														'hour_code' => trim($column_data['hour_code']),
														'hours' => TTDate::getTimeUnit( $row[$column_id], 20 )
														);
								}
							}
						}

						if ( isset( $tmp_rows) ) {
							$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
						}
						unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
						break;
					case 'csv': //Generic CSV
						$data = NULL;

						ksort($setup_data['csv']['columns']);
						$setup_data['csv']['columns'] = Misc::trimSortPrefix( $setup_data['csv']['columns'] );

						$export_column_map = array('employee' => '', 'employee_number' => '', 'default_branch' => '', 'default_department' => '', 'pay_period' => '', 'hour_code' => '', 'hours' => '');

						$i=0;
						foreach($rows as $row) {
							if ( $i == 0 ) {
								//Include header.
								$tmp_rows[] = array(
													'employee' => 'Employee',
													'employee_number' => 'Employee Number',
													'default_branch' => 'Default Branch',
													'default_department' => 'Default Department',
													'pay_period' => 'Pay Period',
													'hour_code' => 'Hours Code',
													'hours' => 'Hours',
													);
							}

							//Combine all hours from the same code together.
							foreach( $setup_data['csv']['columns'] as $column_id => $column_data ) {
								$hour_code = trim($column_data['hour_code']);
								if ( isset( $row[$column_id] ) AND $hour_code != '' ) {
									if ( !isset($tmp_hour_codes[$hour_code]) ) {
										$tmp_hour_codes[$hour_code] = 0;
									}
									$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$column_data['hour_code']], $row[$column_id] ); //Use seconds for math here.
								}
							}

							if ( isset($tmp_hour_codes) ) {
								foreach($tmp_hour_codes as $hour_code => $hours ) {
									$tmp_rows[] = array(
														'employee' => $row['full_name'],
														'employee_number' => $row['employee_number'],
														'default_branch' => $row['default_branch'],
														'default_department' => $row['default_department'],
														'pay_period' => $row['pay_period'],
														'hour_code' => $hour_code,
														'hours' => TTDate::getTimeUnit($hours, 20 ),
														);
								}
								unset($tmp_hour_codes, $hour_code, $hours);
							}

							$i++;
						}

						if ( isset( $tmp_rows) ) {
							$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
						}
						unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);

						break;


					case 'quickbooks': //Quickbooks Pro export format.
						$file_name = 'payroll_export.iif';

						ksort($setup_data['quickbooks']['columns']);
						$setup_data['quickbooks']['columns'] = Misc::trimSortPrefix( $setup_data['quickbooks']['columns'] );

						//
						// Quickbooks header
						//
						/*
							Company Create Time can be found by first running an Timer Activity export in QuickBooks and viewing the output.

							PITEM field needs to be populated, as that is the PAYROLL ITEM in quickbooks. It can be the same as the ITEM field.
							PROJ could be mapped to the default department/branch?
						*/
						$data =  "!TIMERHDR\tVER\tREL\tCOMPANYNAME\tIMPORTEDBEFORE\tFROMTIMER\tCOMPANYCREATETIME\n";
						$data .= "TIMERHDR\t8\t0\t". trim($setup_data['quickbooks']['company_name']) ."\tN\tY\t". trim($setup_data['quickbooks']['company_created_date']) ."\n";
						$data .= "!TIMEACT\tDATE\tJOB\tEMP\tITEM\tPITEM\tDURATION\tPROJ\tNOTE\tXFERTOPAYROLL\tBILLINGSTATUS\n";

						foreach($rows as $row) {
							foreach( $setup_data['quickbooks']['columns'] as $column_id => $column_data ) {
								if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
									//Make sure employee name is in format: LastName, FirstName MiddleInitial
									$tmp_employee_name = $row['last_name'].', '. $row['first_name'];
									if ( isset($row['middle_name']) AND strlen($row['middle_name']) > 0 ) {
										$tmp_employee_name .= ' '.substr(trim($row['middle_name']),0,1);
									}

									$proj = NULL;
									if ( isset($row[$setup_data['quickbooks']['proj']]) ) {
										$proj = $row[$setup_data['quickbooks']['proj']];
									}

									$data .= "TIMEACT\t". date('n/j/y', $row['pay_period_end_date'])."\t\t". $tmp_employee_name ."\t". trim($column_data['hour_code']) ."\t". trim($column_data['hour_code']) ."\t".  TTDate::getTimeUnit( $row[$column_id], 10 ) ."\t". $proj ."\t\tY\t0\n";
									unset($tmp_employee_name);
								}
							}
						}

						break;
					case 'surepayroll': //SurePayroll Export format.
						ksort($setup_data['surepayroll']['columns']);
						$setup_data['surepayroll']['columns'] = Misc::trimSortPrefix( $setup_data['surepayroll']['columns'] );

						//
						//header
						//
						$data = 'TC'."\n";
						$data .= '00001'."\n";

						$export_column_map = array(	'pay_period_end_date' => 'Entry Date',
													'employee_number' => 'Employee Number',
													'last_name' => 'Last Name',
													'first_name' => 'First Name',
													'hour_code' => 'Payroll Code',
													'value' => 'Hours' );

						foreach($rows as $row) {
							foreach( $setup_data['surepayroll']['columns'] as $column_id => $column_data ) {

								if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
									Debug::Arr($column_data,'Output2', __FILE__, __LINE__, __METHOD__,10);
									$tmp_rows[] = array(
														'pay_period_end_date' => date('m/d/Y', $row['pay_period_end_date']),
														'employee_number' => $row['employee_number'],
														'last_name' => $row['last_name'],
														'first_name' => $row['first_name'],
														'hour_code' => trim($column_data['hour_code']),
														'value' => TTDate::getTimeUnit( $row[$column_id], 20 ),
														);
								}
							}
						}

						if ( isset( $tmp_rows) ) {
							$data .= Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
							$data = str_replace('"','', $data);
						}
						unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
						break;
					default:
						break;
				}
			}
		}

		if ( Debug::getVerbosity() == 11 ) {
			Debug::Arr($data,'Output', __FILE__, __LINE__, __METHOD__,10);

			Debug::Display();
		} else {
			if ( isset($data) AND strlen($data) > 0 ) {
				Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);

				Misc::FileDownloadHeader( $file_name, 'application/csv', strlen($data) );
				echo $data;
			} else {
				echo TTi18n::gettext("No Data To Export!") ."<br>\n";
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
				$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys($pay_period_options)) );
				$filter_data['start_date'] = $default_start_date;
				$filter_data['end_date'] = $default_end_date;
				$filter_data['group_ids'] = array( -1 );

				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL);

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
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getByCompanyId( $current_company->getId() );
		$pay_period_options = Misc::prependArray( $all_array_option, $pplf->getArrayByListFactory( $pplf, FALSE, TRUE ) );
		$filter_data['src_pay_period_options'] = Misc::arrayDiffByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );
		$filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );

		$filter_data['export_type_options'] = array(
													0 => TTi18n::gettext('-- Please Choose --'),
													'adp' => TTi18n::gettext('ADP'),
													'paychex_preview' => TTi18n::gettext('Paychex Preview'),
                                                    'paychex_online' => TTi18n::gettext('Paychex Online Payroll'),
													'ceridian_insync' => TTi18n::gettext('Ceridian Insync'),
													'millenium' => TTi18n::gettext('Millenium'),
													'quickbooks' => TTi18n::gettext('QuickBooks Pro'),
													'surepayroll' => TTi18n::gettext('SurePayroll'),
													'csv' => TTi18n::gettext('Generic Excel/CSV'),
													'other' => TTi18n::gettext('-- Other --'),
													);

		$setup_data['src_column_options'] = $columns;

		//
		//ADP  specific columns
		//
		$setup_data['adp_hour_column_options'][0] = TTi18n::gettext('-- DO NOT EXPORT --');
		$setup_data['adp_hour_column_options']['regular_time'] = TTi18n::gettext('Regular Time');
		$setup_data['adp_hour_column_options']['overtime'] = TTi18n::gettext('Overtime');
		for ( $i=3; $i <= 4; $i++ ) {
			$setup_data['adp_hour_column_options'][$i] = TTi18n::gettext('Hours') .' '. $i;
		}

		//Quickbooks additional column mapping
		$setup_data['quickbooks_proj_options'] = array(
													0 => TTi18n::gettext('-- NONE --'),
													'default_branch' => TTi18n::gettext('Default Branch'),
													'default_department' => TTi18n::gettext('Default Department'),
													'group' => TTi18n::gettext('Group'),
													'title' => TTi18n::gettext('Title'),
													);

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('setup_data', $setup_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/PayrollExport.tpl');

		break;
}
?>
