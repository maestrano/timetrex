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
 * $Revision: 1549 $
 * $Id: UserExceptionList.php 1549 2007-12-14 21:41:35Z ipso $
 * $Date: 2007-12-14 13:41:35 -0800 (Fri, 14 Dec 2007) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('schedule','enabled')
		OR !( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_child')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity( 11 );

$smarty->assign('title', TTi18n::gettext($title = 'Scheduled Shifts List')); // See index.php

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
									'-1000-first_name' => TTi18n::gettext('First Name'),
									'-1002-last_name' => TTi18n::gettext('Last Name'),
									'-1010-title' => TTi18n::gettext('Title'),
									'-1039-group' => TTi18n::gettext('Group'),
									'-1040-default_branch' => TTi18n::gettext('Default Branch'),
									'-1050-default_department' => TTi18n::gettext('Default Department'),
									'-1160-branch_id' => TTi18n::gettext('Branch'),
									'-1170-department_id' => TTi18n::gettext('Department'),
									'-1202-status_id' => TTi18n::gettext('Status'),
									'-1210-start_time' => TTi18n::gettext('Start Time'),
									'-1220-end_time' => TTi18n::gettext('End Time'),
									'-1230-total_time' => TTi18n::gettext('Total Time'),
									);

$professional_edition_columns = array(
/*
									'-1180-job' => TTi18n::gettext('Job'),
									'-1182-job_status' => TTi18n::gettext('Job Status'),
									'-1183-job_branch' => TTi18n::gettext('Job Branch'),
									'-1184-job_department' => TTi18n::gettext('Job Department'),
									'-1185-job_group' => TTi18n::gettext('Job Group'),
									'-1190-job_item' => TTi18n::gettext('Task'),
*/
									);

if ( $current_company->getProductEdition() >= 20 ) {
	$columns = Misc::prependArray( $columns, $professional_edition_columns);
	ksort($columns);
}

if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
	//Default columns.
	$filter_data['columns'] = array(
								'-1000-first_name',
								'-1002-last_name',
								'-1202-status_id',
								'-1210-start_time',
								'-1220-end_time',
								'-1230-total_time',
								);

	if ( $sort_column == '' ) {
		$sort_column = $filter_data['sort_column'] = 'start_time';
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
	case 'delete':
	case 'undelete':
		//Debug::setVerbosity( 11 );
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		if ( DEMO_MODE == FALSE
			AND ( $permission->Check('schedule','delete') OR $permission->Check('schedule','delete_own') OR $permission->Check('schedule','delete_child')  ) ) {
			$slf = TTnew( 'ScheduleListFactory' );
			$slf->StartTransaction();

			$slf->getByCompanyIdAndId($current_company->getID(), $ids );
			if ( $slf->getRecordCount() > 0 ) {
				foreach($slf as $s_obj) {
					$s_obj->setDeleted(TRUE);
					if ( $s_obj->isValid() ) {
						$s_obj->setEnableReCalculateDay(TRUE); //Need to remove absence time when deleting a schedule.
						$s_obj->Save();
					}
				}
			}
			//$plf->FailTransaction();
			$slf->CommitTransaction();
		}

		Redirect::Page( URLBuilder::getURL( array('saved_search_id' => $saved_search_id ), 'ScheduleList.php') );

		break;
	case 'search_form_delete':
	case 'search_form_update':
	case 'search_form_save':
	case 'search_form_clear':
	case 'search_form_search':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'ScheduleList.php') );
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
		$slf = TTnew( 'ScheduleListFactory' );

		$hlf = TTnew( 'HierarchyListFactory' );
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
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

		if ( isset($pay_period_ids[0]) AND ( !isset($filter_data['pay_period_ids']) OR $filter_data['pay_period_ids'] == '' ) ) {
			$filter_data['pay_period_ids'] = '-1';
		}

		//If they aren't searching, limit to the last pay period by default for performance optimization when there are hundreds of thousands of schedules.
		if ( $action == '' AND isset($pay_period_ids[0]) AND isset($pay_period_ids[1]) AND !isset($filter_data['pay_period_ids']) ) {
			$filter_data['pay_period_ids'] = array($pay_period_ids[0],$pay_period_ids[1]);
		}

		//Order In punches before Out punches.
		$sort_array = Misc::prependArray( $sort_array, array( 'udf.pay_period_id' => 'asc','uf.last_name' => 'asc', 'a.start_time' => 'asc', 'a.status_id' => 'asc' ) );
		$slf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($slf);

		$schedule_status_options = $slf->getOptions('status');

		$splf = TTnew( 'SchedulePolicyListFactory' );
		$schedule_policy_options = $splf->getByCompanyIdArray( $current_company->getId(), FALSE );

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

		foreach ($slf as $s_obj) {
			//Debug::Text('Status ID: '. $r_obj->getStatus() .' Status: '. $status_options[$r_obj->getStatus()], __FILE__, __LINE__, __METHOD__,10);
			$user_obj = $ulf->getById( $s_obj->getColumn('user_id') )->getCurrent();

			$rows[] = array(
						'id' => $s_obj->getColumn('schedule_id'),

						'user_id' => $s_obj->getColumn('user_id'),
						'first_name' => $user_obj->getFirstName(),
						'last_name' => $user_obj->getLastName(),
						'title' => Option::getByKey($user_obj->getTitle(), $title_options ),
						'group' => Option::getByKey($user_obj->getGroup(), $group_options ),
						'default_branch' => Option::getByKey($user_obj->getDefaultBranch(), $branch_options ),
						'default_department' => Option::getByKey($user_obj->getDefaultDepartment(), $department_options ),

						//'branch_id' => $s_obj->getColumn('branch_id'),
						'branch_id' => Option::getByKey( $s_obj->getBranch(), $branch_options ),
						//'department_id' => $s_obj->getColumn('department_id'),
						'department_id' => Option::getByKey( $s_obj->getDepartment(), $department_options ),
						//'status_id' => $s_obj->getStatus(),
						'status_id' => Option::getByKey($s_obj->getStatus(), $schedule_status_options),
						'start_time' => TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ),
						'end_time' => TTDate::getDate('DATE+TIME', $s_obj->getEndTime() ),

						'total_time' => TTDate::getTimeUnit( $s_obj->getTotalTime() ),

						//'job_id' => $s_obj->getColumn('job_id'),
						//'job_name' => $s_obj->getColumn('job_name'),

						'is_owner' => $permission->isOwner( $s_obj->getCreatedBy(), $current_user->getId() ),
						'is_child' => $permission->isChild( $s_obj->getColumn('user_id'), $permission_children_ids ),
					);

		}
		$smarty->assign_by_ref('rows', $rows);

		$all_array_option = array('-1' => TTi18n::gettext('-- Any --'));

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

		//Select box options;
		$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
		$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
		$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
		$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
		$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['pay_period_options'] = Misc::prependArray( $all_array_option, $pay_period_options );
		$filter_data['schedule_status_options'] = Misc::prependArray( $all_array_option, $schedule_status_options );
		$filter_data['schedule_policy_options'] = Misc::prependArray( $all_array_option, $schedule_policy_options );

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
$smarty->display('schedule/ScheduleList.tpl');
?>