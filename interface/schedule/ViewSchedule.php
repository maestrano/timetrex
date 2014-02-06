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
 * $Id: ViewSchedule.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once(Environment::getBasePath() .'classes/misc/arr_multisort.class.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('schedule','enabled')
		OR !( $permission->Check('schedule','view') OR $permission->Check('schedule','view_own') OR $permission->Check('schedule','view_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'My Schedule')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'do',
												'generic_data',
												'filter_data',
												'page',
												'sort_column',
												'sort_order',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

if ( isset( $filter_data['start_date'] ) AND $filter_data['start_date'] != '' ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
} else {
	$filter_data['start_date'] = TTDate::getBeginWeekEpoch( time() );
}

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
if ( $permission->Check('schedule','view') == FALSE ) {
	$hlf = TTnew( 'HierarchyListFactory' );
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

	if ( $permission->Check('schedule','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('schedule','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

//Debug::setVerbosity(11);
$action = Misc::findSubmitButton('do');
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
switch ($action) {
	case 'print_schedule':
		//Debug::setVerbosity(11);
		Debug::Text('Print Schedule:', __FILE__, __LINE__, __METHOD__,10);
		if ( !isset($filter_data['show_days']) OR ( isset($filter_data['show_days']) AND $filter_data['show_days'] == '' ) ) {
			$filter_data['show_days'] = 4;
		}
		if ( !isset($filter_data['group_schedule']) ) {
			$filter_data['group_schedule'] = FALSE;
		}

		$filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getBeginDayEpoch( $filter_data['start_date'] ), $current_user_prefs->getStartWeekDay() );
		Debug::Text('Start Date: '. TTDate::getDate('DATE+TIME', $filter_data['start_date']), __FILE__, __LINE__, __METHOD__,10);
		$filter_data['end_date'] = $filter_data['start_date'] + (($filter_data['show_days']*7)*86400-3601);

		$sf = TTnew( 'ScheduleFactory' );
		$output = $sf->getSchedule( $filter_data, $current_user_prefs->getStartWeekDay(), $filter_data['group_schedule'] );

		//print_r($output);
		if ( $output == FALSE ) {
			echo TTi18n::getText('No Schedule to print!')."<br>\n";
		} else {
			if ( Debug::getVerbosity() < 11 ) {
				Misc::FileDownloadHeader('schedule.pdf', 'application/pdf', strlen($output));
				echo $output;
			} else {
				Debug::Display();
			}
		}
		exit;
		break;
	case 'filter':
		if ( $filter_start_date != '' AND $filter_show_days != '' ) {
			$start_date = $filter_start_date = TTDate::getBeginDayEpoch( $filter_start_date );
			$end_date = $start_date + ($filter_show_days*86400-3600);
		}
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
				$filter_data['default_branch_ids'] = array( -1 );
				$filter_data['default_department_ids'] = array( -1 );
				$filter_data['schedule_branch_ids'] = array( -1 );
				$filter_data['schedule_department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );
			}
		}

		$ulf = TTnew( 'UserListFactory' );
		$all_array_option = array('-1' => TTi18n::gettext('-- All --'));

		if ( !isset($filter_data['show_days']) OR ( isset($filter_data['show_days']) AND $filter_data['show_days'] == '' ) ) {
			$filter_data['show_days'] = 4;
		}

		if ( !isset( $filter_data['start_date']) OR $filter_data['start_date'] == '' OR $filter_data['show_days'] == '' ) {
			$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getTime(), $current_user_prefs->getStartWeekDay() );
			$end_date = $start_date + (7*(86400-3600));
		}

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

		//Get Employee Groups
		if ( !isset($filter_data['group_ids']) ) {
			$filter_data['group_ids'] = NULL;
		}
		$uglf = TTnew( 'UserGroupListFactory' );
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		if ( !isset($filter_data['schedule_branch_ids']) ) {
			$filter_data['schedule_branch_ids'] = NULL;
		}
		if ( !isset($filter_data['default_branch_ids']) ) {
			$filter_data['default_branch_ids'] = NULL;
		}
		$blf = TTnew( 'BranchListFactory' );
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_schedule_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['schedule_branch_ids'], $branch_options );
		$filter_data['selected_schedule_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['schedule_branch_ids'], $branch_options );
		$filter_data['src_default_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['default_branch_ids'], $branch_options );
		$filter_data['selected_default_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['default_branch_ids'], $branch_options );

		//Get departments
		if ( !isset($filter_data['schedule_department_ids']) ) {
			$filter_data['schedule_department_ids'] = NULL;
		}
		if ( !isset($filter_data['default_department_ids']) ) {
			$filter_data['default_department_ids'] = NULL;
		}
		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_schedule_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['schedule_department_ids'], $department_options );
		$filter_data['selected_schedule_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['schedule_department_ids'], $department_options );
		$filter_data['src_default_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['default_department_ids'], $department_options );
		$filter_data['selected_default_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['default_department_ids'], $department_options );

		//Get employee titles
		if ( !isset($filter_data['user_title_ids']) ) {
			$filter_data['user_title_ids'] = NULL;
		}
		$utlf = TTnew( 'UserTitleListFactory' );
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		$filter_data['show_days_options'] = array( 1 => TTi18n::gettext('1 Week'), 2 => TTi18n::gettext('2 Weeks'), 3 => TTi18n::gettext('3 Weeks'), 4 => TTi18n::gettext('4 Weeks'), 5 => TTi18n::gettext('5 Weeks'), 6 => TTi18n::gettext('6 Weeks'), 7 => TTi18n::gettext('7 Weeks'), 8 => TTi18n::gettext('8 Weeks'), 9 => TTi18n::gettext('9 Weeks'), 10 => TTi18n::gettext('10 Weeks'), 11 => TTi18n::gettext('11 Weeks'), 12 => TTi18n::gettext('12 Weeks'));
		$filter_data['view_type_options'] = array( 10 => TTi18n::gettext('Month'), 20 => TTi18n::gettext('Week'), 30 => TTi18n::gettext('Day') );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);
		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('ugdf', $ugdf);

		break;
}
$smarty->display('schedule/ViewSchedule.tpl');
?>