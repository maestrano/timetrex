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
 * $Revision: 11121 $
 * $Id: UserExceptionList.php 11121 2013-10-12 04:36:49Z ipso $
 * $Date: 2013-10-11 21:36:49 -0700 (Fri, 11 Oct 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('punch','enabled')
		OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_own') OR $permission->Check('punch','view_child')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity( 11 );

$smarty->assign('title', TTi18n::gettext($title = 'Exception List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'form',
												'filter_data',
												'page',
												'sort_column',
												'sort_order',
												'saved_search_id',
												'ids',
												) ) );

$columns = array(
											'-1010-first_name' => TTi18n::gettext('First Name'),
											'-1020-middle_name' => TTi18n::gettext('Middle Name'),
											'-1030-last_name' => TTi18n::gettext('Last Name'),
											'-1040-date_stamp' => TTi18n::gettext('Date'),
											'-1050-severity' => TTi18n::gettext('Severity'),
											'-1060-exception_policy_type' => TTi18n::gettext('Exception'),
											'-1070-exception_policy_type_id' => TTi18n::gettext('Code'),
											);

if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
	//Default columns.
	if ( $permission->Check('punch','view') == TRUE OR $permission->Check('punch','view_child')) {
		$filter_data['columns'] = array(
									'-1010-first_name',
									'-1030-last_name',
									'-1040-date_stamp',
									'-1050-severity',
									'-1060-exception_policy_type',
									'-1070-exception_policy_type_id',
									);
	} else {
		$filter_data['columns'] = array(
									'-1040-date_stamp',
									'-1050-severity',
									'-1060-exception_policy_type',
									'-1070-exception_policy_type_id',
									);
	}
	if ( $sort_column == '' ) {
		$sort_column = $filter_data['sort_column'] = 'severity';
		$sort_order = $filter_data['sort_order'] = 'desc';
	}
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

Debug::Text('Form: '. $form, __FILE__, __LINE__, __METHOD__,10);
//Handle different actions for different forms.

$action = Misc::findSubmitButton();
if ( isset($form) AND $form != '' ) {
	$action = strtolower($form.'_'.$action);
} else {
	$action = strtolower($action);
}
switch ($action) {
	case 'search_form_delete':
	case 'search_form_update':
	case 'search_form_save':
	case 'search_form_clear':
	case 'search_form_search':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'UserExceptionList.php') );
	default:
		BreadCrumb::setCrumb($title);

		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, $sort_column ) );
		Debug::Text('Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Saved Search ID: '. $saved_search_id, __FILE__, __LINE__, __METHOD__,10);

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
		$elf = TTnew( 'ExceptionListFactory' );
		$hlf = TTnew( 'HierarchyListFactory' );
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		//Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		$filter_data['permission_children_ids'] = array();
		if ( $permission->Check('punch','view') == FALSE ) {
			if ( $permission->Check('punch','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('punch','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getByCompanyId( $current_company->getId() );
		$pay_period_options = $pplf->getArrayByListFactory( $pplf, FALSE, FALSE );
		$pay_period_ids = array_keys((array)$pay_period_options);

		if ( isset($pay_period_ids[0]) AND ( !isset($filter_data['pay_period_id']) OR $filter_data['pay_period_id'] == '' ) ) {
			$filter_data['pay_period_id'] = '-1';
		}

		$filter_data['pay_period_status_id'] = array(10,12,30); //All but closed

		$filter_data['type_id'] = array(30,40,50,55,60,70);
		if (  isset($filter_data['pre_mature']) ) {
			$filter_data['type_id'][] = 5;
		}

		//This query can be really slow, make sure we put a time limit on it.
		if ( DEPLOYMENT_ON_DEMAND == TRUE ) { $elf->setQueryStatementTimeout( 5000 ); }

		$elf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		if ( DEPLOYMENT_ON_DEMAND == TRUE ) { $elf->setQueryStatementTimeout(); }

		$pager = new Pager($elf);

		$epf = TTnew( 'ExceptionPolicyFactory' );
		$exception_policy_type_options = $epf->getOptions('type');
		$exception_policy_severity_options = $epf->getOptions('severity');

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

		$ulf = TTnew( 'UserListFactory' );
		$user_options = $ulf->getByCompanyIdArray( $current_company->getID(), FALSE );

		foreach ($elf as $e_obj) {
			//Debug::Text('Status ID: '. $r_obj->getStatus() .' Status: '. $status_options[$r_obj->getStatus()], __FILE__, __LINE__, __METHOD__,10);
			$user_obj = $ulf->getById( $e_obj->getColumn('user_id') )->getCurrent();

			$rows[] = array(
								'id' => $e_obj->getId(),
								'user_date_id' => $e_obj->getUserDateID(),
								'user_id' => $e_obj->getColumn('user_id'),
								'first_name' => $user_obj->getFirstName(),
								'middle_name' => $user_obj->getMiddleName(),
								'last_name' => $user_obj->getLastName(),
								'user_full_name' => Option::getByKey($e_obj->getColumn('user_id'), $user_options),
								'date_stamp' => TTDate::getDate('DATE', TTDate::strtotime($e_obj->getColumn('user_date_stamp')) ),
								'date_stamp_epoch' => TTDate::strtotime($e_obj->getColumn('user_date_stamp')),
								'type_id' => $e_obj->getType(),
								'severity_id' => $e_obj->getColumn('severity_id'),
								'severity' => Option::getByKey($e_obj->getColumn('severity_id'), $exception_policy_severity_options),
								'exception_color' => $e_obj->getColor(),
								'exception_background_color' => $e_obj->getBackgroundColor(),
								'exception_policy_type_id' => $e_obj->getColumn('exception_policy_type_id'),
								'exception_policy_type' => Option::getByKey($e_obj->getColumn('exception_policy_type_id'), $exception_policy_type_options ),
								'created_date' => $e_obj->getCreatedDate(),
								'deleted' => $e_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('rows', $rows);

		$all_array_option = array('-1' => TTi18n::gettext('-- Any --'));

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array( 'permission_children_ids' => $filter_data['permission_children_ids'] ) );
		$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

		//Select box options;
		$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
		$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
		$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
		$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
		$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['pay_period_options'] = Misc::prependArray( $all_array_option, $pay_period_options );
		$filter_data['severity_options'] = Misc::prependArray( $all_array_option, $exception_policy_severity_options );
		$filter_data['type_options'] = Misc::prependArray( $all_array_option, $exception_policy_type_options );

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

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );
		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign('total_columns', count($filter_columns)+3 );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('punch/UserExceptionList.tpl');
?>