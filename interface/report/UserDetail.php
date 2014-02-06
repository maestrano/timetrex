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
 * $Revision: 9210 $
 * $Id: UserDetail.php 9210 2013-02-28 00:16:41Z ipso $
 * $Date: 2013-02-27 16:16:41 -0800 (Wed, 27 Feb 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_user_detail') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Employee Detail Report')); // See index.php

if ( isset($config_vars['other']['report_maximum_execution_limit']) AND $config_vars['other']['report_maximum_execution_limit'] != '' ) { ini_set( 'max_execution_time', $config_vars['other']['report_maximum_execution_limit'] ); }
if ( isset($config_vars['other']['report_maximum_memory_limit']) AND $config_vars['other']['report_maximum_memory_limit'] != '' ) { ini_set( 'memory_limit', $config_vars['other']['report_maximum_memory_limit'] ); }

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'
												) ) );

//Debug::Arr($action, 'Action', __FILE__, __LINE__, __METHOD__,10);
//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);


URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );


$columns = array(
											'employee' => TTi18n::gettext('Employee Information'),
											'wage' => TTi18n::gettext('Wage History'),
											//'schedule' => 'Schedule History',
											'attendance' => TTi18n::gettext('Attendance History'),
											'exception' => TTi18n::gettext('Exception History'),
											//'accrual' => 'Accrual Balances',
											);

$static_columns = array(
											'-1000-full_name' => TTi18n::gettext('Full Name'),
											'-1010-title' => TTi18n::gettext('Title'),
											'-1020-province' => TTi18n::gettext('Province/State'),
											'-1030-country' => TTi18n::gettext('Country'),
											'-1040-default_branch' => TTi18n::gettext('Default Branch'),
											'-1050-default_department' => TTi18n::gettext('Default Department'),
											//'-1060-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
											);

//$columns = Misc::prependArray( $columns, $deduction_columns);

if ( isset($filter_data['start_date']) ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
}

if ( isset($filter_data['end_date']) ) {
	$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
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
if ( !isset($filter_data['column_ids']) ) {
	$filter_data['column_ids'] = array();
}

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
$wage_permission_children_ids = array();
if ( $permission->Check('user','view') == FALSE ) {
	$hlf = TTnew( 'HierarchyListFactory' );
	$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('user','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('user','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

//Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
if ( $permission->Check('wage','view') == FALSE ) {
	if ( $permission->Check('wage','view_child') == FALSE ) {
		$wage_permission_children_ids = array();
	}
	if ( $permission->Check('wage','view_own') ) {
		$wage_permission_children_ids[] = $current_user->getId();
	}

	$wage_filter_data['permission_children_ids'] = $wage_permission_children_ids;
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();

switch ($action) {
	case 'export':
	case 'display_report':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);
		if ( Misc::isSystemLoadValid() == FALSE ) {
			echo TTi18n::getText('Please try again later...');
			exit;
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$filter_data['user_ids'][] = $u_obj->getId();
			}

			//Get title list,
			$utlf = TTnew( 'UserTitleListFactory' );
			$user_titles = $utlf->getByCompanyIdArray( $current_company->getId() );

			//Get default branch list
			$blf = TTnew( 'BranchListFactory' );
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = TTnew( 'DepartmentListFactory' );
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			/*

				Get Wage History

			*/
			if ( isset($columns['wage']) ) {
				if ( $permission->Check('wage','view') == TRUE ) {
					$wage_filter_data['permission_children_ids'] = $filter_data['user_ids'];
				}
				$uwlf = TTnew( 'UserWageListFactory' );
				$uwlf->getByUserIdAndCompanyIdAndStartDateAndEndDate( $wage_filter_data['permission_children_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
				if ( $uwlf->getRecordCount() > 0 ) {
					foreach( $uwlf as $uw_obj ) {
						$user_wage_rows[$uw_obj->getUser()][] = array(
																'type_id' => $uw_obj->getType(),
																'type' => Option::getByKey($uw_obj->getType(), $uw_obj->getOptions('type') ),
																'wage' => $uw_obj->getWage(),
																'currency_symbol' => $uw_obj->getUserObject()->getCurrencyObject()->getSymbol(),
																'effective_date' => $uw_obj->getEffectiveDate(),
																'effective_date_since' => TTDate::getHumanTimeSince( $uw_obj->getEffectiveDate() )
																);
					}
				}
			}

			/*

				Get Attendance History

			*/
			if ( isset($columns['attendance']) ) {
				//Get policy names.
				$oplf = TTnew( 'OverTimePolicyListFactory' );
				$over_time_policy_arr = $oplf->getByCompanyIdArray($current_company->getId(), FALSE);

				$aplf = TTnew( 'AbsencePolicyListFactory' );
				$absence_policy_arr = $aplf->getByCompanyIdArray($current_company->getId(), FALSE);

				$pplf = TTnew( 'PremiumPolicyListFactory' );
				$premium_policy_arr = $pplf->getByCompanyIdArray($current_company->getId(), FALSE);

				//Get stats on number of days worked per month/week
				$udlf = TTnew( 'UserDateListFactory' );
				$udlf->getDaysWorkedByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'month', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
				if ( $udlf->getRecordCount() > 0 ) {
					foreach( $udlf as $ud_obj ) {
						//$user_days_worked[$ud_obj->getUser()]['month']
						$user_attendance_rows[$ud_obj->getUser()]['days_worked']['month'] = array(
																					'avg' => round( $ud_obj->getColumn('avg'),2),
																					'min' => $ud_obj->getColumn('min'),
																					'max' => $ud_obj->getColumn('max'),
																					);
					}
				}

				$udlf->getDaysWorkedByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'week', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
				if ( $udlf->getRecordCount() > 0 ) {
					foreach( $udlf as $ud_obj ) {
						$user_attendance_rows[$ud_obj->getUser()]['days_worked']['week'] = array(
																					'avg' => round( $ud_obj->getColumn('avg'),2),
																					'min' => $ud_obj->getColumn('min'),
																					'max' => $ud_obj->getColumn('max'),
																					);
					}
				}
				//var_dump($user_days_worked);

				$udtlf = TTnew( 'UserDateTotalListFactory' );
				$udtlf->getReportHoursByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'day', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
				if ( $udtlf->getRecordCount() > 0 ) {
					foreach( $udtlf as $udt_obj ) {
						if ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 20 ) {
							$type = 'regular';
							$policy_id = 0;
							$policy_name = 'regular';
						} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 30
										AND $udt_obj->getOverTimePolicyId() != 0 ) {
							$type = 'over_time';
							$policy_id = $udt_obj->getOverTimePolicyId();
							$policy_name = $over_time_policy_arr[$udt_obj->getOverTimePolicyId()];
						} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 40
										AND $udt_obj->getPremiumPolicyId() != 0) {
							$type = 'premium';
							$policy_id = $udt_obj->getPremiumPolicyId();
							$policy_name = $premium_policy_arr[$udt_obj->getPremiumPolicyId()];
						} elseif ( $udt_obj->getStatus() == 30 AND $udt_obj->getType() == 10
										AND $udt_obj->getAbsencePolicyId() != 0 ) {
							$type = 'absence';
							$policy_id = $udt_obj->getAbsencePolicyId();
							$policy_name = $absence_policy_arr[$udt_obj->getAbsencePolicyId()];
						} else {
							$type = NULL;
							$policy_id = NULL;
						}

						if ( $type !== NULL AND $policy_id !== NULL AND $policy_name !== NULL ) {
							$user_attendance_rows[$udt_obj->getColumn('user_id')]['hours_worked'][$type][$policy_id] = array(
																				'name' => $policy_name,
																				'day' => array(
																							'avg' => round( $udt_obj->getColumn('avg'),1),
																							'min' => $udt_obj->getColumn('min'),
																							'max' => $udt_obj->getColumn('max'),
																							'date_units' => $udt_obj->getColumn('date_units'),
																						),
																				'week' => array(),
																				'month' => array(),
																				);
						}
						unset($type, $policy_id, $policy_name);
					}
				}

				$udtlf->getReportHoursByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'week', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
				if ( $udtlf->getRecordCount() > 0 ) {
					foreach( $udtlf as $udt_obj ) {
						if ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 20 ) {
							$type = 'regular';
							$policy_id = 0;
							$policy_name = 'regular';
						} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 30
										AND $udt_obj->getOverTimePolicyId() != 0 ) {
							$type = 'over_time';
							$policy_id = $udt_obj->getOverTimePolicyId();
							$policy_name = $over_time_policy_arr[$udt_obj->getOverTimePolicyId()];
						} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 40
										AND $udt_obj->getPremiumPolicyId() != 0) {
							$type = 'premium';
							$policy_id = $udt_obj->getPremiumPolicyId();
							$policy_name = $premium_policy_arr[$udt_obj->getPremiumPolicyId()];
						} elseif ( $udt_obj->getStatus() == 30 AND $udt_obj->getType() == 10
										AND $udt_obj->getAbsencePolicyId() != 0 ) {
							$type = 'absence';
							$policy_id = $udt_obj->getAbsencePolicyId();
							$policy_name = $absence_policy_arr[$udt_obj->getAbsencePolicyId()];
						} else {
							$type = NULL;
							$policy_id = NULL;
						}

						if ( $type !== NULL AND $policy_id !== NULL AND $policy_name !== NULL ) {
							$user_attendance_rows[$udt_obj->getColumn('user_id')]['hours_worked'][$type][$policy_id]['week'] = array(
																							'avg' => round( $udt_obj->getColumn('avg'),1),
																							'min' => $udt_obj->getColumn('min'),
																							'max' => $udt_obj->getColumn('max'),
																							'date_units' => $udt_obj->getColumn('date_units'),
																				);
						}
						unset($type, $policy_id, $policy_name);
					}
				}

				$udtlf->getReportHoursByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'month', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
				if ( $udtlf->getRecordCount() > 0 ) {
					foreach( $udtlf as $udt_obj ) {
						if ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 20 ) {
							$type = 'regular';
							$policy_id = 0;
							$policy_name = 'regular';
						} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 30
										AND $udt_obj->getOverTimePolicyId() != 0 ) {
							$type = 'over_time';
							$policy_id = $udt_obj->getOverTimePolicyId();
							$policy_name = $over_time_policy_arr[$udt_obj->getOverTimePolicyId()];
						} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 40
										AND $udt_obj->getPremiumPolicyId() != 0) {
							$type = 'premium';
							$policy_id = $udt_obj->getPremiumPolicyId();
							$policy_name = $premium_policy_arr[$udt_obj->getPremiumPolicyId()];
						} elseif ( $udt_obj->getStatus() == 30 AND $udt_obj->getType() == 10
										AND $udt_obj->getAbsencePolicyId() != 0 ) {
							$type = 'absence';
							$policy_id = $udt_obj->getAbsencePolicyId();
							$policy_name = $absence_policy_arr[$udt_obj->getAbsencePolicyId()];
						} else {
							$type = NULL;
							$policy_id = NULL;
						}

						if ( $type !== NULL AND $policy_id !== NULL AND $policy_name !== NULL ) {
							$user_attendance_rows[$udt_obj->getColumn('user_id')]['hours_worked'][$type][$policy_id]['month'] = array(
																							'avg' => round( $udt_obj->getColumn('avg'),1),
																							'min' => $udt_obj->getColumn('min'),
																							'max' => $udt_obj->getColumn('max'),
																							'date_units' => $udt_obj->getColumn('date_units'),
																				);
						}
						unset($type, $policy_id, $policy_name);
					}
				}


				//var_dump($user_attendance_rows);
				//Repeat broken out by branch/department as well

			}

			if ( Misc::isSystemLoadValid() == FALSE ) {
				echo TTi18n::getText('Please try again later...');
				exit;
			}
			
			/*

				Exception History

			*/
			if ( isset($columns['exception']) ) {
				//Get exception types.
				$eplf = TTnew( 'ExceptionPolicyListFactory' );
				$eplf->getByCompanyId( $current_company->getId() );
				if ( $eplf->getRecordCount() > 0 ) {
					foreach( $eplf as $ep_obj) {
						$exception_policy_arr[$ep_obj->getId()] = array(
																'type_id' => $ep_obj->getType(),
																'name' => Option::getByKey($ep_obj->getType(), $ep_obj->getOptions('type') ),
																'severity_id' => $ep_obj->getSeverity(),
																);
					}
				}
				//var_dump($exception_policy_arr);

				$elf = TTnew( 'ExceptionListFactory' );
				$elf->getReportByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'week', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
				if ( $elf->getRecordCount() > 0 ) {
					foreach( $elf as $e_obj ) {
						$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['week'] = array(
																					'exception_policy_id' => $e_obj->getColumn('exception_policy_id'),
																					'name' => $exception_policy_arr[$e_obj->getColumn('exception_policy_id')]['name'],
																					'code' => $exception_policy_arr[$e_obj->getColumn('exception_policy_id')]['type_id'],
																					'avg' => round( $e_obj->getColumn('avg'),2),
																					'min' => $e_obj->getColumn('min'),
																					'max' => $e_obj->getColumn('max'),
																					'total' => $e_obj->getColumn('total'),
																					);
					}
				}

				$elf->getReportByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'month', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
				if ( $elf->getRecordCount() > 0 ) {
					foreach( $elf as $e_obj ) {
						$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['month'] = array(
																					'exception_policy_id' => $e_obj->getColumn('exception_policy_id'),
																					'name' => $exception_policy_arr[$e_obj->getColumn('exception_policy_id')]['name'],
																					'code' => $exception_policy_arr[$e_obj->getColumn('exception_policy_id')]['type_id'],
																					'avg' => round( $e_obj->getColumn('avg'),2),
																					'min' => $e_obj->getColumn('min'),
																					'max' => $e_obj->getColumn('max'),
																					'total' => $e_obj->getColumn('total'),
																					);
					}
				}

				$elf->getDOWReportByUserIdAndCompanyIdAndStartDateAndEndDate( $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
				if ( $elf->getRecordCount() > 0 ) {
					foreach( $elf as $e_obj ) {
						$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow'][$e_obj->getColumn('dow')] = $e_obj->getColumn('total');

						if ( isset($user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'])
								AND $e_obj->getColumn('total') > $user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max']['total'] ) {
							$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'] = array( 'total' => $e_obj->getColumn('total'), 'dow' => $e_obj->getColumn('dow') );
						} elseif ( isset($user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'])
								AND $e_obj->getColumn('total') == $user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max']['total'] ) {
							$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'] = array( 'total' => $e_obj->getColumn('total'), 'dow' => 99 );
						} elseif ( !isset($user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'])
								AND $e_obj->getColumn('total') > 0 ) {
							$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'] = array( 'total' => $e_obj->getColumn('total'), 'dow' => $e_obj->getColumn('dow') );
						}

					}
				}

			}
			//var_dump($user_exception_rows);

			/*

				Get Employee contact information.

			*/
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getReportByCompanyIdAndUserIDList( $current_company->getId(), $filter_data['user_ids'] );

			foreach ($ulf as $u_obj ) {
				if ( isset($user_wage_rows[$u_obj->getID()]) ) {
					$tmp_user_wage_rows = $user_wage_rows[$u_obj->getID()];
				} else {
					$tmp_user_wage_rows = NULL;
				}

				if ( isset($user_attendance_rows[$u_obj->getID()]) ) {
					$tmp_user_attendance_rows = $user_attendance_rows[$u_obj->getID()];
				} else {
					$tmp_user_attendance_rows = NULL;
				}

				if ( isset($user_exception_rows[$u_obj->getID()]) ) {
					$tmp_user_exception_rows = $user_exception_rows[$u_obj->getID()];
				} else {
					$tmp_user_exception_rows = NULL;
				}

				$row_arr = array(
									'id' => $u_obj->getId(),
									'employee_number' => $u_obj->getEmployeeNumber(),
									'user_name' => $u_obj->getUserName(),
									'phone_id' => $u_obj->getPhoneID(),
									'ibutton_id' => $u_obj->getIButtonID(),

									'full_name' => $u_obj->getFullName(TRUE),
									'first_name' => $u_obj->getFirstName(),
									'middle_name' => $u_obj->getMiddleName(),
									'last_name' => $u_obj->getLastName(),

									'title' => Option::getByKey($u_obj->getTitle(), $user_titles ),

									'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $branch_options ),
									'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $department_options ),

									'sex' => Option::getByKey($u_obj->getSex(), $u_obj->getOptions('sex') ),

									'address1' => $u_obj->getAddress1(),
									'address2' => $u_obj->getAddress2(),
									'city' => $u_obj->getCity(),
									'province' => $u_obj->getProvince(),
									'country' => $u_obj->getCountry(),
									'postal_code' => $u_obj->getPostalCode(),
									'work_phone' => $u_obj->getWorkPhone(),
									'home_phone' => $u_obj->getHomePhone(),
									'mobile_phone' => $u_obj->getMobilePhone(),
									'fax_phone' => $u_obj->getFaxPhone(),
									'home_email' => $u_obj->getHomeEmail(),
									'work_email' => $u_obj->getWorkEmail(),
									'birth_date' => $u_obj->getBirthDate(),
									'birth_date_since' => $u_obj->getAge(),
									'sin' => $u_obj->getSIN(),
									'hire_date' => $u_obj->getHireDate(),
									'hire_date_since' => TTDate::getHumanTimeSince( $u_obj->getHireDate() ),
									'termination_date' => $u_obj->getTerminationDate(),

									'user_wage_rows' => $tmp_user_wage_rows,
									'user_attendance_rows' => $tmp_user_attendance_rows,
									'user_exception_rows' => $tmp_user_exception_rows,
								);

				$rows[] = $row_arr;

				unset($tmp_user_wage_rows);
			}

			$rows = Sort::Multisort($rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);
		}

		foreach( $filter_data['column_ids'] as $column_key ) {
			$filter_columns[$column_key] = $columns[$column_key];
		}

		if ( $action == 'export' ) {
			if ( isset($rows) AND isset($filter_columns) ) {
				Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);
				$data = Misc::Array2CSV( $rows, $filter_columns );

				Misc::FileDownloadHeader('report.csv', 'application/csv', strlen($data) );
				echo $data;
			} else {
				echo TTi18n::gettext("No Data To Export!") ."<br>\n";
			}
		} else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/UserDetailReport.tpl');
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
				//Default selections
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );

				$filter_data['column_ids'] = array_keys($columns);

				$filter_data['start_date'] = TTDate::getBeginMonthEpoch();
				$filter_data['end_date'] = TTDate::getEndMonthEpoch();

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1000-full_name';
			}
		}

		$ulf = TTnew( 'UserListFactory' );
		$all_array_option = array('-1' => TTi18n::gettext('-- All --'));

		//Get include employee list.
		if ( !isset($filter_data['include_user_ids']) ) {
				$filter_data['include_user_ids'] = NULL;
		}
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );

		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		if ( !isset($filter_data['exclude_user_ids']) ) {
				$filter_data['exclude_user_ids'] = NULL;
		}
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		if ( !isset($filter_data['user_status_ids']) ) {
				$filter_data['user_status_ids'] = NULL;
		}
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		if ( !isset($filter_data['group_ids']) ) {
				$filter_data['group_ids'] = NULL;
		}
		$uglf = TTnew( 'UserGroupListFactory' );
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		if ( !isset($filter_data['branch_ids']) ) {
				$filter_data['branch_ids'] = NULL;
		}
		$blf = TTnew( 'BranchListFactory' );
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		//Get departments
		if ( !isset($filter_data['department_ids']) ) {
				$filter_data['department_ids'] = NULL;
		}
		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		//Get employee titles
		if ( !isset($filter_data['user_title_ids']) ) {
				$filter_data['user_title_ids'] = NULL;
		}
		$utlf = TTnew( 'UserTitleListFactory' );
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		//Get column list
		if ( !isset($filter_data['column_ids']) ) {
				$filter_data['column_ids'] = NULL;
		}
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );

		//Get primary/secondary order list
		$filter_data['sort_options'] = $static_columns;
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/UserDetail.tpl');

		break;
}
?>
