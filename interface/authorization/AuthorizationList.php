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
 * $Revision: 6798 $
 * $Id: AuthorizationList.php 6798 2012-05-17 23:41:49Z ipso $
 * $Date: 2012-05-17 16:41:49 -0700 (Thu, 17 May 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('authorization','enabled')
		OR !( $permission->Check('authorization','view') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity(11);

$smarty->assign('title', TTi18n::gettext($title = 'Authorization List')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'ids',
												'selected_levels'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

	default:
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$ulf = TTnew( 'UserListFactory' );
		$hlf = TTnew( 'HierarchyListFactory' );
		$hllf = TTnew( 'HierarchyLevelListFactory' );
		$hotlf = TTnew( 'HierarchyObjectTypeListFactory' );

		if ( $permission->Check('request','authorize') ) {

			//
			//Missed Punch: request_punch
			//
			$hierarchy_levels['request_punch'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1010 ); //Missed Punch
			//Debug::Arr( $hierarchy_levels['request_punch'], 'Request Punch Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_punch'] = 0;
			if ( isset($selected_levels['request_punch']) AND isset($hierarchy_levels['request_punch'][$selected_levels['request_punch']]) ) {
				$selected_level_arr['request_punch'] = $hierarchy_levels['request_punch'][$selected_levels['request_punch']];
			} elseif ( isset($hierarchy_levels['request_punch'][1]) ) {
				$selected_level_arr['request_punch'] = $hierarchy_levels['request_punch'][1];
			}
			//Debug::Arr( $selected_level_arr['request_punch'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_punch']) ) {
				$rlf = TTnew( 'RequestListFactory' );
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_punch'], 10, 30, NULL, NULL, NULL, $sort_array ); //Missed Punch
				foreach( $rlf as $r_obj) {
					//Grab authorizations for this object.
					$requests['request_punch'][] = array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}


			//
			//Missed Punch: request_punch_adjust
			//
			$hierarchy_levels['request_punch_adjust'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1020 ); //Punch Adjust
			//Debug::Arr( $hierarchy_levels['request_punch_adjust'], 'Request Punch Adjust Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_punch_adjust'] = 0;
			if ( isset($selected_levels['request_punch_adjust']) AND isset($hierarchy_levels['request_punch_adjust'][$selected_levels['request_punch_adjust']]) ) {
				$selected_level_arr['request_punch_adjust'] = $hierarchy_levels['request_punch_adjust'][$selected_levels['request_punch_adjust']];
			} elseif ( isset($hierarchy_levels['request_punch_adjust'][1]) ) {
				$selected_level_arr['request_punch_adjust'] = $hierarchy_levels['request_punch_adjust'][1];
			}
			//Debug::Arr( $selected_level_arr['request_punch_adjust'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_punch_adjust']) ) {
				$rlf = TTnew( 'RequestListFactory' );
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_punch_adjust'], 20, 30, NULL, NULL, NULL, $sort_array ); //Punch Adjust
				foreach( $rlf as $r_obj) {
					//Grab authorizations for this object.
					$requests['request_punch_adjust'][] = array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}


			//
			//Missed Punch: request_absence
			//
			$hierarchy_levels['request_absence'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1030 ); //Absence
			//Debug::Arr( $hierarchy_levels['request_absence'], 'Request Punch Adjust Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_absence'] = 0;
			if ( isset($selected_levels['request_absence']) AND isset($hierarchy_levels['request_absence'][$selected_levels['request_absence']]) ) {
				$selected_level_arr['request_absence'] = $hierarchy_levels['request_absence'][$selected_levels['request_absence']];
			} elseif ( isset($hierarchy_levels['request_absence'][1]) ) {
				$selected_level_arr['request_absence'] = $hierarchy_levels['request_absence'][1];
			}
			//Debug::Arr( $selected_level_arr['request_absence'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_absence']) ) {
				$rlf = TTnew( 'RequestListFactory' );
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_absence'], 30, 30, NULL, NULL, NULL, $sort_array ); //Absence
				foreach( $rlf as $r_obj) {
					//Grab authorizations for this object.
					$requests['request_absence'][] = array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}


			//
			//Missed Punch: request_schedule
			//
			$hierarchy_levels['request_schedule'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1040 ); //Schedule
			//Debug::Arr( $hierarchy_levels['request_schedule'], 'Request Punch Adjust Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_schedule'] = 0;
			if ( isset($selected_levels['request_schedule']) AND isset($hierarchy_levels['request_schedule'][$selected_levels['request_schedule']]) ) {
				$selected_level_arr['request_schedule'] = $hierarchy_levels['request_schedule'][$selected_levels['request_schedule']];
			} elseif ( isset($hierarchy_levels['request_schedule'][1]) ) {
				$selected_level_arr['request_schedule'] = $hierarchy_levels['request_schedule'][1];
			}
			//Debug::Arr( $selected_level_arr['request_schedule'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_schedule']) ) {
				$rlf = TTnew( 'RequestListFactory' );
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_schedule'], 40, 30, NULL, NULL, NULL, $sort_array ); //Schedule
				foreach( $rlf as $r_obj) {
					//Grab authorizations for this object.
					$requests['request_schedule'][] = array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}

			//
			//Missed Punch: request_other
			//
			$hierarchy_levels['request_other'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1100 ); //Other
			//Debug::Arr( $hierarchy_levels['request_other'], 'Request Punch Adjust Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_other'] = 0;
			if ( isset($selected_levels['request_other']) AND isset($hierarchy_levels['request_other'][$selected_levels['request_other']]) ) {
				$selected_level_arr['request_other'] = $hierarchy_levels['request_other'][$selected_levels['request_other']];
			} elseif ( isset($hierarchy_levels['request_other'][1]) ) {
				$selected_level_arr['request_other'] = $hierarchy_levels['request_other'][1];
			}
			//Debug::Arr( $selected_level_arr['request_other'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_other']) ) {
				$rlf = TTnew( 'RequestListFactory' );
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_other'], 100, 30, NULL, NULL, NULL, $sort_array ); //Other
				foreach( $rlf as $r_obj) {
					//Grab authorizations for this object.
					$requests['request_other'][] = array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}

			if ( isset($requests) ) {
				$smarty->assign_by_ref('requests', $requests);
			}
		}

		if ( $permission->Check('punch','authorize') ) {
			//Debug::Text('TimeSheet: Selected Level: '. $selected_levels['timesheet'], __FILE__, __LINE__, __METHOD__,10);

			//$timesheet_levels = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 90 );
			$hierarchy_levels['timesheet'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 90 );
			//Debug::Arr( $timesheet_levels , 'TimeSheet Levels', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($selected_levels['timesheet']) AND isset($hierarchy_levels['timesheet'][$selected_levels['timesheet']]) ) {
				$selected_level_arr['timesheet'] = $hierarchy_levels['timesheet'][$selected_levels['timesheet']];
				Debug::Text(' Switching Levels to Level: '. key($selected_level_arr['timesheet']), __FILE__, __LINE__, __METHOD__,10);
			} elseif ( isset($hierarchy_levels['timesheet'][1]) ) {
				$selected_level_arr['timesheet'] = $hierarchy_levels['timesheet'][1];
			} else {
				Debug::Text( 'No TimeSheet Levels... Not in hierarchy?', __FILE__, __LINE__, __METHOD__,10);
				$selected_level_arr['timesheet'] = 0;
			}
			//Debug::Arr( $timesheet_selected_level, 'TimeSheet Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['timesheet']) ) {
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$pptsvlf->getByHierarchyLevelMapAndStatusAndNotAuthorized($selected_level_arr['timesheet'], 30, NULL, NULL, NULL, $sort_array );
				foreach( $pptsvlf as $pptsv_obj) {
					//Grab authorizations for this object.
					if ( is_object( $pptsv_obj->getUserObject() ) ) {
						$timesheets[] = array(
												'id' => $pptsv_obj->getId(),
												'pay_period_id' => $pptsv_obj->getPayPeriod(),
												'user_id' => $pptsv_obj->getUser(),
												'user_full_name' => $pptsv_obj->getUserObject()->getFullName(),
												'pay_period_start_date' => $pptsv_obj->getPayPeriodObject()->getStartDate(),
												'pay_period_end_date' => $pptsv_obj->getPayPeriodObject()->getEndDate(),
												'status_id' => $pptsv_obj->getStatus(),
												'status' => Option::getByKey($pptsv_obj->getStatus(), $pptsvlf->getOptions('status') ),
											);
					}
				}
				$smarty->assign_by_ref('timesheets', $timesheets);
			} else {
				Debug::Text( 'No hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}
		}

		$smarty->assign_by_ref('selected_levels', $selected_levels );
		$smarty->assign_by_ref('selected_level_arr', $selected_level_arr);
		$smarty->assign_by_ref('hierarchy_levels', $hierarchy_levels);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		break;
}
$smarty->display('authorization/AuthorizationList.tpl');
?>