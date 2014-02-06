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
 * $Revision: 4822 $
 * $Id: UserList.php 4822 2011-06-12 02:25:33Z ipso $
 * $Date: 2011-06-11 19:25:33 -0700 (Sat, 11 Jun 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','view') OR $permission->Check('user','view_child')  ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Employee List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'form',
												'page',
												'filter_data',
												'sort_column',
												'sort_order',
												'saved_search_id',
												'company_id',
												'ids'
												) ) );

$columns = array(
											'-1010-employee_number' => TTi18n::gettext('Employee #'),
											'-1020-status' => TTi18n::gettext('Status'),
											'-1030-user_name' => TTi18n::gettext('User Name'),
											'-1040-phone_id' => TTi18n::gettext('Phone ID'),
											'-1050-ibutton_id' => TTi18n::gettext('iButton'),

											'-1060-first_name' => TTi18n::gettext('First Name'),
											'-1070-middle_name' => TTi18n::gettext('Middle Name'),
											'-1080-last_name' => TTi18n::gettext('Last Name'),

											'-1090-title' => TTi18n::gettext('Title'),

											'-1099-user_group' => TTi18n::gettext('Group'),
											'-1100-default_branch' => TTi18n::gettext('Branch'),
											'-1110-default_department' => TTi18n::gettext('Department'),

											'-1120-sex' => TTi18n::gettext('Sex'),

											'-1130-address1' => TTi18n::gettext('Address 1'),
											'-1140-address2' => TTi18n::gettext('Address 2'),

											'-1150-city' => TTi18n::gettext('City'),
											'-1160-province' => TTi18n::gettext('Province/State'),
											'-1170-country' => TTi18n::gettext('Country'),
											'-1180-postal_code' => TTi18n::gettext('Postal Code'),
											'-1190-work_phone' => TTi18n::gettext('Work Phone'),
											'-1200-home_phone' => TTi18n::gettext('Home Phone'),
											'-1210-mobile_phone' => TTi18n::gettext('Mobile Phone'),
											'-1220-fax_phone' => TTi18n::gettext('Fax Phone'),
											'-1230-home_email' => TTi18n::gettext('Home Email'),
											'-1240-work_email' => TTi18n::gettext('Work Email'),
											'-1250-birth_date' => TTi18n::gettext('Birth Date'),
											'-1260-hire_date' => TTi18n::gettext('Hire Date'),
											'-1270-termination_date' => TTi18n::gettext('Termination Date'),
											'-1280-sin' => TTi18n::gettext('SIN/SSN'),
											);

if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
	//Default columns.
	$filter_data['columns'] = array(
								'-1060-first_name',
								'-1080-last_name',
								'-1200-home_phone'
								);
	if ( $sort_column == '' ) {
		$sort_column = $filter_data['sort_column'] = 'last_name';
		$sort_order = $filter_data['sort_order'] = 'asc';
	}
}
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

//Handle different actions for different forms.
$action = Misc::findSubmitButton();
if ( isset($form) AND $form != '' ) {
	$action = strtolower($form.'_'.$action);
} else {
	$action = strtolower($action);
}
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL( array('saved_search_id' => $saved_search_id, 'company_id' => $company_id ), 'EditUser.php') );

		break;
	case 'delete':
	case 'undelete':
		//Debug::setVerbosity( 11 );
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		if ( DEMO_MODE == FALSE
			AND ( $permission->Check('user','delete') OR $permission->Check('user','delete_own') OR $permission->Check('user','delete_child')  ) ) {

			if ( is_array($ids) ) {
				$ulf = TTnew( 'UserListFactory' );
				$ulf->StartTransaction();

				foreach ($ids as $id) {
					if ( $id != $current_user->getId() ) {
						$ulf->getByIdAndCompanyId($id, $current_company->getID() );
						foreach ($ulf as $user) {
							$is_owner = $permission->isOwner( $user->getCreatedBy(), $user->getID() );
							$is_child = $permission->isChild( $user->getId(), $permission_children_ids );

							if ( $permission->Check('user','delete')
									OR ( $permission->Check('user','delete_child') AND $is_child === TRUE )
									OR ( $permission->Check('user','delete_own') AND $is_owner === TRUE ) ) {
								$user->setDeleted($delete);
								$user->Save();
							}
						}
					}
				}

				$ulf->CommitTransaction();
			}
		}

		Redirect::Page( URLBuilder::getURL( array('saved_search_id' => $saved_search_id ), 'UserList.php') );

		break;
	case 'search_form_delete':
	case 'search_form_update':
	case 'search_form_save':
	case 'search_form_clear':
	case 'search_form_search':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'UserList.php') );
	default:
		BreadCrumb::setCrumb($title);

		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, $sort_column ) );
		Debug::Text('Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Saved Search ID: '. $saved_search_id, __FILE__, __LINE__, __METHOD__,10);

		if ( isset($company_id) AND $company_id != '' ) {
			$filter_data['company_id'] = $company_id;
		}

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],	array(
															'sort_column' => Misc::trimSortPrefix($sort_column),
															'sort_order' => $sort_order,
															'saved_search_id' => $saved_search_id,
															'page' => $page
														) );

		$ulf = TTnew( 'UserListFactory' );
		$clf = TTnew( 'CompanyListFactory' );

		if ( $permission->Check('company','view') ) {
			$ulf->getSearchByArrayCriteria( $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
		} else {
			if ( $permission->Check('user','view') == FALSE ) {
				if ( $permission->Check('user','view_child') ) {
					$filter_data['permission_children_ids'] = $permission_children_ids;
				}
				if ( $permission->Check('user','view_own') ) {
					$filter_data['permission_children_ids'][] = $current_user->getId();
				}
			}

			Debug::Text('Users in company only!', __FILE__, __LINE__, __METHOD__,10);
			$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
		}

		$pager = new Pager($ulf);

		if ( $permission->Check('company','view') ) {
			$clf = TTnew( 'CompanyListFactory' );
			$clf->getAll();
			$company_options = $clf->getArrayByListFactory( $clf, FALSE, TRUE );
		}

		//Get title list,
		$utlf = TTnew( 'UserTitleListFactory' );
		$utlf->getByCompanyId( $current_company->getId() );
		$title_options = $utlf->getArrayByListFactory( $utlf, FALSE, TRUE );

		$blf = TTnew( 'BranchListFactory' );
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );

		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );

		$uglf = TTnew( 'UserGroupListFactory' );
		$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) );

		foreach ($ulf as $u_obj) {
			$company_name = $clf->getById( $u_obj->getCompany() )->getCurrent()->getName();

			$users[] = array(
									'id' => $u_obj->getId(),
									'company_id' => $u_obj->getCompany(),
									'employee_number' => $u_obj->getEmployeeNumber(),
									'status_id' => $u_obj->getStatus(),
									'status' => Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions('status') ),
									'user_name' => $u_obj->getUserName(),
									'phone_id' => $u_obj->getPhoneID(),
									'ibutton_id' => $u_obj->getIButtonID(),

									'full_name' => $u_obj->getFullName(TRUE),
									'first_name' => $u_obj->getFirstName(),
									'middle_name' => $u_obj->getMiddleName(),
									'last_name' => $u_obj->getLastName(),

									'title' => Option::getByKey($u_obj->getTitle(), $title_options ),
									'user_group' => Option::getByKey($u_obj->getGroup(), $group_options ),

									'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $branch_options ),
									'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $department_options ),

									'sex_id' => $u_obj->getSex(),
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
									'birth_date' => TTDate::getDate('DATE', $u_obj->getBirthDate() ),
									'sin' => $u_obj->getSecureSIN(),
									'hire_date' => TTDate::getDate('DATE', $u_obj->getHireDate() ),
									'termination_date' => TTDate::getDate('DATE', $u_obj->getTerminationDate() ),

									'map_url' => $u_obj->getMapURL(),
									
									'is_owner' => $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() ),
									'is_child' => $permission->isChild( $u_obj->getId(), $permission_children_ids ),
									'deleted' => $u_obj->getDeleted(),
							);
		}
		//var_dump($users);

		$all_array_option = array('-1' => TTi18n::gettext('-- Any --'));


		//Select box options;
		if ( $permission->Check('company','view') ) {
			$filter_data['company_options'] = Misc::prependArray( $all_array_option, $company_options );
		}

		$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
		$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
		$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
		$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
		$filter_data['sex_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('sex') );
		$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );

		$cf = TTnew( 'CompanyFactory' );
		$filter_data['country_options'] = Misc::prependArray( $all_array_option, $cf->getOptions('country') );
		if ( isset($filter_data['country']) ) {
			$filter_data['province_options'] = Misc::prependArray( $all_array_option, $cf->getOptions('province', $filter_data['country'] ) );
		} else {
			$filter_data['province_options'] = $all_array_option;
		}

		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$filter_data['pay_period_schedule_options'] = Misc::prependArray( $all_array_option, $ppslf->getByCompanyIDArray( $current_company->getId() ) );

		$pglf = TTnew( 'PolicyGroupListFactory' );
		$filter_data['policy_group_options'] = Misc::prependArray( $all_array_option, $pglf->getByCompanyIDArray( $current_company->getId() ) );

		$filter_data['saved_search_options'] = $ugdlf->getArrayByListFactory( $ugdlf->getByUserIdAndScript( $current_user->getId(), $_SERVER['SCRIPT_NAME']), FALSE );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['columns'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['columns'], $columns );

		$filter_data['sort_options'] = Misc::trimSortPrefix($columns);
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray(TRUE);

		foreach( $filter_data['columns'] as $column_key ) {
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
		}
		unset($column_key);
																																			$obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65";$obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65";$obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65";@$obj = new $obj_class;$notice_data['retval'] = $obj->{$obj_function}();$notice_data['message'] = $obj->{$obj_error_msg_function}($notice_data['retval']);

		$smarty->assign_by_ref('users', $users);
		$smarty->assign_by_ref('notice_data', $notice_data);
		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign('total_columns', count($filter_columns)+3 );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );
		$smarty->assign_by_ref('saved_search_id', $saved_search_id );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('users/UserList.tpl');
?>