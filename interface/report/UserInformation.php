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
 * $Id: UserInformation.php 9210 2013-02-28 00:16:41Z ipso $
 * $Date: 2013-02-27 16:16:41 -0800 (Wed, 27 Feb 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_user_information') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Employee Information Report')); // See index.php

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


$columns = array(						'-1010-employee_number' => TTi18n::gettext('Employee #'),
										'-1020-status' => TTi18n::gettext('Status'),
										'-1030-user_name' => TTi18n::gettext('User Name'),
										'-1040-phone_id' => TTi18n::gettext('Quick Punch ID'),
										//'-1050-ibutton_id' => TTi18n::gettext('iButton'),

										'-1060-first_name' => TTi18n::gettext('First Name'),
										'-1070-middle_name' => TTi18n::gettext('Middle Name'),
										'-1080-last_name' => TTi18n::gettext('Last Name'),
										'-1085-full_name' => TTi18n::gettext('Full Name'),

										'-1090-title' => TTi18n::gettext('Title'),

										'-1099-group' => TTi18n::gettext('Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),

										'-1112-permission_control' => TTi18n::gettext('Permission Group'),
										'-1115-policy_group' => TTi18n::gettext('Policy Group'),
										'-1118-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),

										'-1120-sex' => TTi18n::gettext('Sex'),

										'-1130-address1' => TTi18n::gettext('Address 1'),
										'-1140-address2' => TTi18n::gettext('Address 2'),

										'-1150-city' => TTi18n::gettext('City'),
										'-1160-province' => TTi18n::gettext('Province/State'),
										'-1170-country' => TTi18n::gettext('Country'),
										'-1180-postal_code' => TTi18n::gettext('Postal Code'),
										'-1190-work_phone' => TTi18n::gettext('Work Phone'),
                                        '-1191-work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
										'-1200-home_phone' => TTi18n::gettext('Home Phone'),
										'-1210-mobile_phone' => TTi18n::gettext('Mobile Phone'),
										'-1220-fax_phone' => TTi18n::gettext('Fax Phone'),
										'-1230-home_email' => TTi18n::gettext('Home Email'),
										'-1240-work_email' => TTi18n::gettext('Work Email'),
										'-1250-birth_date' => TTi18n::gettext('Birth Date'),
										'-1260-hire_date' => TTi18n::gettext('Hire Date'),
										'-1270-termination_date' => TTi18n::gettext('Termination Date'),
										'-1280-sin' => TTi18n::gettext('SIN/SSN'),
/*
										'-1284-ibutton_id' => TTi18n::gettext('iButton'),
										'-1285-finger_print_1' => TTi18n::gettext('Finger Print 1'),
										'-1286-finger_print_2' => TTi18n::gettext('Finger Print 2'),
										'-1287-finger_print_3' => TTi18n::gettext('Finger Print 3'),
										'-1288-finger_print_4' => TTi18n::gettext('Finger Print 4'),
*/
										'-1289-note' => TTi18n::gettext('Note'),

										'-1290-institution' => TTi18n::gettext('Bank Institution'),
										'-1300-transit' => TTi18n::gettext('Bank Transit/Routing'),
										'-1310-account' => TTi18n::gettext('Bank Account'),

										'-1319-currency' => TTi18n::gettext('Currency'),
										'-1320-wage_type' => TTi18n::gettext('Wage Type'),
										'-1330-wage' => TTi18n::gettext('Wage'),
										'-1340-effective_date' => TTi18n::gettext('Wage Effective Date'),

										'-1500-language' => TTi18n::gettext('Language'),
										'-1510-date_format' => TTi18n::gettext('Date Format'),
										'-1520-time_format' => TTi18n::gettext('Time Format'),
										'-1530-time_unit' => TTi18n::gettext('Time Units'),
										'-1540-time_zone' => TTi18n::gettext('Time Zone'),
										'-1550-items_per_page' => TTi18n::gettext('Rows Per page'),
										);

//Get custom user fields
$oflf = TTnew( 'OtherFieldListFactory' );
$other_field_names = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getId(), 10 );
if ( is_array($other_field_names) ) {
	$columns = Misc::prependArray( $columns, $other_field_names);
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

//Company Deductions
$cdlf = TTnew( 'CompanyDeductionListFactory' );
$deduction_columns = $cdlf->getByCompanyIdAndStatusIdArray( $current_company->getId(), 10, FALSE);

$columns = Misc::prependArray( $columns, $deduction_columns);

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

        $bf = new BankAccountFactory();

		//Get all employees that match the criteria:
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$filter_data['user_ids'][] = $u_obj->getId();
			}

			$ulf->getReportByCompanyIdAndUserIDList( $current_company->getId(), $filter_data['user_ids'] );

			//Get title list,
			$utlf = TTnew( 'UserTitleListFactory' );
			$user_titles = $utlf->getByCompanyIdArray( $current_company->getId() );

			$uglf = TTnew( 'UserGroupListFactory' );
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

			//Get default branch list
			$blf = TTnew( 'BranchListFactory' );
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = TTnew( 'DepartmentListFactory' );
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			$pclf = TTnew( 'PermissionControlListFactory' );
			$pclf->getByCompanyId( $current_company->getId() );
			$permission_control_options = $pclf->getArrayByListFactory( $pclf, TRUE );

			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
			$pay_period_schedule_options = $ppslf->getByCompanyIDArray( $current_company->getId() );

			$pglf = TTnew( 'PolicyGroupListFactory' );
			$policy_group_options = $pglf->getByCompanyIDArray( $current_company->getId() );

			$pclf = TTnew( 'PermissionControlListFactory' );
			$pclf->getByCompanyIdAndUserId( $current_company->getId(), $filter_data['user_ids'] );
			$permission_control_user_map = $pclf->getUserToPermissionControlMapArrayByListFactory( $pclf );

			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
			$ppslf->getByCompanyIdAndUserId( $current_company->getId(), $filter_data['user_ids'] );
			$pay_period_schedule_user_map = $ppslf->getUserToPayPeriodScheduleMapArrayByListFactory( $ppslf );

			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getByCompanyIdAndUserId( $current_company->getId(), $filter_data['user_ids'] );
			$policy_group_user_map = $pglf->getUserToPolicyGroupMapArrayByListFactory( $pglf );

			$crlf = TTnew( 'CurrencyListFactory' );
			$crlf->getByCompanyId( $current_company->getId() );
			$currency_options = $crlf->getArrayByListFactory( $crlf, FALSE, TRUE );

			$upf = TTnew( 'UserPreferenceFactory' );
			$language_options = TTi18n::getLanguageArray();
			$date_format_options = $upf->getOptions('date_format');
			$time_format_options = $upf->getOptions('time_format');
			$time_unit_format_options = $upf->getOptions('time_unit_format');
			$timesheet_view_options = $upf->getOptions('timesheet_view');
			$start_week_day_options = $upf->getOptions('start_week_day');
			$time_zone_options = $upf->getOptions('time_zone');

            //Make sure we account for wage permissions.
            if ( $permission->Check('wage','view') == TRUE ) {
                $wage_filter_data['permission_children_ids'] = $filter_data['user_ids'];
            }
			$uwlf = TTnew( 'UserWageListFactory' );
			$uwlf->getLastWageByUserIdAndDate( $wage_filter_data['permission_children_ids'], TTDate::getTime() );
			if ( $uwlf->getRecordCount() > 0 ) {
				foreach($uwlf as $uw_obj) {
					$user_wage[$uw_obj->getUser()] = array(
															'type_id' => $uw_obj->getType(),
															'type' => Option::getByKey($uw_obj->getType(), $uw_obj->getOptions('type') ),
															'wage' => $uw_obj->getWage(),
															'effective_date' => $uw_obj->getEffectiveDate(),
															);
				}
			}

			$udlf = TTnew( 'UserDeductionListFactory' );
			$udlf->getByCompanyIdAndUserId( $current_company->getId(), $filter_data['user_ids']);
			if ( $udlf->getRecordCount() > 0 ) {
				foreach( $udlf as $ud_obj ) {
					//Get UserValue options
					$user_value_1_options = $ud_obj->getCompanyDeductionObject()->getUserValue1Options();

					if ( $ud_obj->getUserValue1() !== FALSE ) {
						$tmp_user_value = $ud_obj->getUserValue1();
					} elseif ( $ud_obj->getCompanyDeductionObject()->getUserValue1() ) {
						$tmp_user_value = $ud_obj->getCompanyDeductionObject()->getUserValue1();
					} else {
						$tmp_user_value = NULL;
					}

					if ( is_array($user_value_1_options) ) {
						$user_values[] = Option::getByKey( $tmp_user_value, $user_value_1_options );
					} else {
						$user_values[] = $tmp_user_value;
					}
					unset($tmp_user_value);

					if ( $ud_obj->getUserValue2() !== FALSE ) {
						$user_values[] = $ud_obj->getUserValue2();
					} elseif ( $ud_obj->getCompanyDeductionObject()->getUserValue2() ) {
						$user_values[] = $ud_obj->getCompanyDeductionObject()->getUserValue2();
					}

					if ( $ud_obj->getUserValue3() !== FALSE ) {
						$user_values[] = $ud_obj->getUserValue3();
					} elseif ( $ud_obj->getCompanyDeductionObject()->getUserValue3() ) {
						$user_values[] = $ud_obj->getCompanyDeductionObject()->getUserValue3();
					}

					if ( isset($user_values) ) {
						$user_value_str = implode(' / ', $user_values);
					} else {
						$user_value_str = 'N/A';
					}

					$user_deduction[$ud_obj->getUser()][$ud_obj->getCompanyDeduction()] = $user_value_str;

					unset($user_value_str, $user_values);
				}
			}
			//var_dump($user_deduction);

			foreach ($ulf as $u_obj ) {
				//Wage data
				if ( isset($user_wage[$u_obj->getId()]) ) {
					//Debug::Text('Wage Data found for User ID: '. $u_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					$user_wage_data = $user_wage[$u_obj->getId()];
				} else {
					Debug::Text('No Wage Data found for User ID: '. $u_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					//Dummy array
					$user_wage_data = array(
											'type_id' => NULL,
											'type' => NULL,
											'wage' => NULL,
											'effective_date_order' => NULL,
											'effective_date' => NULL
											);
				}

				//Get user preference data.
				$uplf = TTnew( 'UserPreferenceListFactory' );
				$uplf->getByUserId( $u_obj->getId() );
				if ( $uplf->getRecordCount() > 0 ) {
					$up_obj = $uplf->getCurrent();

					$user_pref_data = array(
											'language' => Option::getByKey( $up_obj->getLanguage(), $language_options ),
											'date_format' => Option::getByKey( $up_obj->getDateFormat(), $date_format_options),
											'time_format' => Option::getByKey( $up_obj->getTimeFormat(), $time_format_options),
											'time_unit' => Option::getByKey( $up_obj->getTimeUnitFormat(), $time_unit_format_options),
											'time_zone' => Option::getByKey($up_obj->getTimeZone(), $time_zone_options),
											'start_week_day' => Option::getByKey($up_obj->getStartWeekDay(), $start_week_day_options),
											'items_per_page' => $up_obj->getItemsPerPage(),
											);
				}

				if ( isset($permission_control_user_map[$u_obj->getId()]) ) {;
					$permission_control_id = $permission_control_user_map[$u_obj->getId()];
				} else {
					$permission_control_id = 0;
				}

				if ( isset($policy_group_user_map[$u_obj->getId()]) ) {;
					$policy_group_id = $policy_group_user_map[$u_obj->getId()];
				} else {
					$policy_group_id = 0;
				}

				if ( isset($pay_period_schedule_user_map[$u_obj->getId()]) ) {;
					$pay_period_schedule_id = $pay_period_schedule_user_map[$u_obj->getId()];
				} else {
					$pay_period_schedule_id = 0;
				}
/*
				$display_ibutton_id = 'No';
				if ( $u_obj->getIButtonID() != '' ) {
					$display_ibutton_id = 'Yes';
				}

				$display_finger_print_1 = 'No';
				if ( $u_obj->getFingerPrint1() != '' ) {
					$display_finger_print_1 = 'Yes';
				}

				$display_finger_print_2 = 'No';
				if ( $u_obj->getFingerPrint2() != '' ) {
					$display_finger_print_2 = 'Yes';
				}

				$display_finger_print_3 = 'No';
				if ( $u_obj->getFingerPrint3() != '' ) {
					$display_finger_print_3 = 'Yes';
				}

				$display_finger_print_4 = 'No';
				if ( $u_obj->getFingerPrint4() != '' ) {
					$display_finger_print_4 = 'Yes';
				}
*/
				$sin_number = NULL;
				if ( $permission->Check('user','view_sin') == TRUE ) {
					$sin_number = $u_obj->getSIN();
				} else {
					$sin_number = $u_obj->getSecureSIN();
				}

				$row_arr = array(
									'id' => $u_obj->getId(),
									'employee_number' => $u_obj->getEmployeeNumber(),
									'status' => Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions('status') ),
									'user_name' => $u_obj->getUserName(),
									'phone_id' => $u_obj->getPhoneID(),
									'ibutton_id' => $u_obj->getIButtonID(),

									'first_name' => $u_obj->getFirstName(),
									'middle_name' => $u_obj->getMiddleName(),
									'last_name' => $u_obj->getLastName(),
									'full_name' => $u_obj->getFullName(TRUE),

									'title' => Option::getByKey($u_obj->getTitle(), $user_titles ),

									'group' => Option::getByKey($u_obj->getGroup(), $group_options ),
									'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $branch_options ),
									'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $department_options ),

									'permission_control' => Option::getByKey( $permission_control_id, $permission_control_options ),
									'policy_group' => Option::getByKey( $policy_group_id, $policy_group_options ),
									'pay_period_schedule' => Option::getByKey( $pay_period_schedule_id, $pay_period_schedule_options ),

									'sex' => Option::getByKey($u_obj->getSex(), $u_obj->getOptions('sex') ),

									'address1' => $u_obj->getAddress1(),
									'address2' => $u_obj->getAddress2(),
									'city' => $u_obj->getCity(),
									'province' => $u_obj->getProvince(),
									'country' => $u_obj->getCountry(),
									'postal_code' => $u_obj->getPostalCode(),
									'work_phone' => $u_obj->getWorkPhone(),
                                    'work_phone_ext' => $u_obj->getWorkPhoneExt(),
									'home_phone' => $u_obj->getHomePhone(),
									'mobile_phone' => $u_obj->getMobilePhone(),
									'fax_phone' => $u_obj->getFaxPhone(),
									'home_email' => $u_obj->getHomeEmail(),
									'work_email' => $u_obj->getWorkEmail(),
									'birth_date' => $u_obj->getBirthDate(),
									'sin' => $sin_number,
									'hire_date' => $u_obj->getHireDate(),
									'termination_date' => $u_obj->getTerminationDate(),
/*
									'ibutton_id' => $display_ibutton_id,
									'finger_print_1' => $display_finger_print_1,
									'finger_print_2' => $display_finger_print_2,
									'finger_print_3' => $display_finger_print_3,
									'finger_print_4' => $display_finger_print_4,
*/
									'note' => $u_obj->getNote(),

									'other_id1' => $u_obj->getColumn('other_id1'),
									'other_id2' => $u_obj->getColumn('other_id2'),
									'other_id3' => $u_obj->getColumn('other_id3'),
									'other_id4' => $u_obj->getColumn('other_id4'),
									'other_id5' => $u_obj->getColumn('other_id5'),

									//Bank Info
									'institution' => $u_obj->getColumn('institution'),
									'transit' => $u_obj->getColumn('transit'),
									'account' => $bf->getSecureAccount( $u_obj->getColumn('account') ),

									//Wage info
									'currency' => Option::getByKey($u_obj->getCurrency(), $currency_options ),
									'wage_type' => $user_wage_data['type'],
									'wage' => $user_wage_data['wage'],
									//'effective_date_order' => $user_wage_data['effective_date_order'],
									'effective_date' => $user_wage_data['effective_date'],
								);

				if ( isset($user_deduction[$u_obj->getId()]) ) {
					$row_arr = Misc::prependArray( $row_arr, $user_deduction[$u_obj->getId()] );
				}

				if ( isset($user_pref_data) ) {
					$row_arr = Misc::prependArray( $row_arr, $user_pref_data );
				}

				$tmp_rows[] = $row_arr;

				unset($user_pref_data);
			}

			$tmp_rows = Sort::Multisort($tmp_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);
		} else {
			//No Users!
		}

		if ( isset($tmp_rows) ) {
			foreach($tmp_rows as $tmp_row ) {
				foreach($tmp_row as $column => $column_data) {
					if ( $column_data != '' AND strstr($column, '_date') ) {
						$column_data = TTDate::getDate('DATE', $column_data);
					}

					$row_columns[$column] = $column_data;
					unset($column, $column_data);
				}

				$rows[] = $row_columns;
				unset($row_columns);
			}
		}
		unset($tmp_rows);

		foreach( $filter_data['column_ids'] as $column_key ) {
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
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
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/UserInformationReport.tpl');
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
				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );

				$filter_data['column_ids'] = array(
												'-1060-first_name',
												'-1080-last_name',
												'-1090-title',
												'-1200-home_phone',
												'-1130-address1',
												'-1150-city',
												'-1160-province',
												'-1180-postal_code',
												);

				$filter_data['primary_sort'] = '-1080-last_name';
				$filter_data['secondary_sort'] = '-1160-province';
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
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_options']['effective_date_order'] = 'Wage Effective Date';
		unset($filter_data['sort_options']['effective_date']);

		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		//var_dump($filter_data);

		$smarty->display('report/UserInformation.tpl');

		break;
}
?>
