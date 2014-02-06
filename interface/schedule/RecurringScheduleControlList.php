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
 * $Revision: 7487 $
 * $Id: RecurringScheduleControlList.php 7487 2012-08-15 22:35:09Z ipso $
 * $Date: 2012-08-15 15:35:09 -0700 (Wed, 15 Aug 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('recurring_schedule','enabled')
		OR !( $permission->Check('recurring_schedule','view') OR $permission->Check('recurring_schedule','view_own') OR $permission->Check('recurring_schedule','view_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity(11);

$smarty->assign('title', TTi18n::gettext($title = 'Recurring Schedule List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'form',
												'page',
												'sort_column',
												'sort_order',
												'filter_data',
												'saved_search_id',
												'filter_template_id',
												'ids',
												) ) );

$columns = array(
											'-1010-first_name' => TTi18n::gettext('First Name'),
											'-1020-middle_name' => TTi18n::gettext('Middle Name'),
											'-1030-last_name' => TTi18n::gettext('Last Name'),
											'-1040-name' => TTi18n::gettext('Name'),
											'-1050-description' => TTi18n::gettext('Description'),
											'-1070-start_date' => TTi18n::gettext('Start Date'),
											'-1080-end_date' => TTi18n::gettext('End Date'),
											);

if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
	//Default columns.
	$filter_data['columns'] = array(
								'-1010-first_name',
								'-1030-last_name',
								'-1040-name',
								'-1050-description',
								'-1070-start_date',
								'-1080-end_date',
								);

	if ( $sort_column == '' ) {
		$sort_column = $filter_data['sort_column'] = 'last_name';
		$sort_order = $filter_data['sort_order'] = 'asc';
	}
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
if ( isset($form) AND $form != '' ) {
	$action = strtolower($form.'_'.$action);
} else {
	$action = strtolower($action);
}
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL( NULL, 'EditRecurringSchedule.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$rsclf = TTnew( 'RecurringScheduleControlListFactory' );

		foreach ($ids as $id => $user_ids) {
			$rsclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($rsclf as $rsc_obj) {
				//Get all users for this schedule.
				$current_users = $rsc_obj->getUser();

				$user_diff_arr = array_diff( (array)$current_users, (array)$user_ids );
				//Debug::Arr($user_diff_arr,'User Diff:', __FILE__, __LINE__, __METHOD__,10);

				if ( is_array($user_diff_arr) AND count($user_diff_arr) == 0 ) {
					Debug::Text('No more users assigned to schedule, deleting...', __FILE__, __LINE__, __METHOD__,10);

					//No more users assigned to this schedule, delete the whole thing.
					$rsc_obj->setDeleted($delete);
				} elseif ( is_array($user_diff_arr) AND count($user_diff_arr) > 0 ) {
					Debug::Text('Still more users assigned to schedule, removing users only...', __FILE__, __LINE__, __METHOD__,10);
					//Still users assigned to this schedule, remove users from it.
					$rsc_obj->setUser( $user_diff_arr );
				}

				if ( $rsc_obj->isValid() ) {
					$rsc_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringScheduleControlList.php') );

		break;
	case 'search_form_delete':
	case 'search_form_update':
	case 'search_form_save':
	case 'search_form_clear':
	case 'search_form_search':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'RecurringScheduleControlList.php') );
	default:
		BreadCrumb::setCrumb($title);

		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, $sort_column ) );
		Debug::Text('Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Saved Search ID: '. $saved_search_id, __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_template_id) AND $filter_template_id != '' ) {
			$filter_data['template_id'] = array($filter_template_id);
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

		$rsclf = TTnew( 'RecurringScheduleControlListFactory' );
		$ulf = TTnew( 'UserListFactory' );

		if ( $permission->Check('recurring_schedule','view') == FALSE ) {
			if ( $permission->Check('recurring_schedule','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('recurring_schedule','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$rsclf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($rsclf);

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

		$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' );
		$template_options = $rstclf->getByCompanyIdArray( $current_company->getId(), FALSE, TRUE );

		foreach ($rsclf as $rsc_obj) {
			$user_id = $rsc_obj->getColumn('user_id');

			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByID( $user_id );
			if ( $ulf->getRecordCount() == 1 ) {
				$u_obj = $ulf->getCurrent();
			} else {
				unset($u_obj);
				//Skip this row.
				Debug::Text('Skipping Row: User ID: '. $user_id , __FILE__, __LINE__, __METHOD__,10);
				//continue;
			}

			$rows[] = array(
								'id' => $rsc_obj->getId(),
								'user_id' => $user_id,
								'name' => $rsc_obj->getColumn('name'),
								'description' => $rsc_obj->getColumn('description'),
								'start_week' => $rsc_obj->getStartWeek(),
								'start_date' => $rsc_obj->getStartDate(),
								'end_date' => $rsc_obj->getEndDate(),
								'first_name' => ( isset($u_obj) ) ? $u_obj->getFirstName() : TTi18n::getText('OPEN'),
								'middle_name' => ( isset($u_obj) ) ? $u_obj->getMiddleName() : TTi18n::getText('OPEN'),
								'last_name' => ( isset($u_obj) ) ? $u_obj->getLastName() : TTi18n::getText('OPEN'),
								'user_full_name' => ( isset($u_obj) ) ? $u_obj->getFullName(TRUE) : TTi18n::getText('OPEN'),

								'is_owner' => $permission->isOwner( ( isset($u_obj) ) ? $u_obj->getCreatedBy() : $rsc_obj->getCreatedBy(), ( isset( $u_obj ) ) ? $u_obj->getId() : 0 ),
								'is_child' => $permission->isChild( ( isset($u_obj) ) ? $u_obj->getId() : 0, $permission_children_ids ),

								'deleted' => $rsc_obj->getDeleted()
							);

		}

		$all_array_option = array('-1' => TTi18n::gettext('-- Any --'));

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

		//Select box options;
		$filter_data['template_options'] = Misc::prependArray( $all_array_option, $template_options );
		$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
		$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
		$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
		$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
		$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );

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

		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign('total_columns', count($filter_columns)+3 );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );
		$smarty->assign_by_ref('saved_search_id', $saved_search_id );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('schedule/RecurringScheduleControlList.tpl');
?>