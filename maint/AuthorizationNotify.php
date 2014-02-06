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
 * $Revision: 1396 $
 * $Id: AuthorizationNotify.php 1396 2007-11-07 16:49:35Z ipso $
 * $Date: 2007-11-07 08:49:35 -0800 (Wed, 07 Nov 2007) $
 */
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

$permission = new Permission();
$hlf = new HierarchyListFactory();
$hotlf = new HierarchyObjectTypeListFactory();

//Get all users
$ulf = new UserListFactory();
$ulf->getByStatus(10, NULL, array('company_id' => 'asc') );
foreach($ulf as $user ) {
	//Check authorize permissions for eact object type.
	if ( $permission->Check('default_schedule','authorize', $user->getId(), $user->getCompany() ) ) {
		//Get Hierarchy Control ID
		$default_schedule_hierarchy_id = $hotlf->getByCompanyIdAndObjectTypeId( $user->getCompany(), 10 )->getCurrent()->getHierarchyControl();
		Debug::Text('Default Schedule Hierarchy ID: '. $default_schedule_hierarchy_id, __FILE__, __LINE__, __METHOD__,10);

		//Get all levels below us.
		$default_schedule_levels = $hlf->getLevelsByHierarchyControlIdAndUserId( $default_schedule_hierarchy_id,  $user->getId()  );
		Debug::Arr( $default_schedule_levels, 'Default Schedule Levels', __FILE__, __LINE__, __METHOD__,10);
		$default_schedule_user_id = $user->getId();

		$default_schedule_node_data = $hlf->getByHierarchyControlIdAndUserId( $default_schedule_hierarchy_id, $default_schedule_user_id );

		//Get current level IDs
		$default_schedule_current_level_user_ids = $hlf->getCurrentLevelIdArrayByHierarchyControlIdAndUserId($default_schedule_hierarchy_id, $default_schedule_user_id );
		Debug::Arr( $default_schedule_current_level_user_ids, 'Default Schedule Current Level Ids', __FILE__, __LINE__, __METHOD__,10);

		//Get Parents
		$default_schedule_parent_level_user_ids = $hlf->getParentLevelIdArrayByHierarchyControlIdAndUserId($default_schedule_hierarchy_id, $default_schedule_user_id );
		Debug::Arr( $default_schedule_parent_level_user_ids, 'Default Schedule Parent Level Ids', __FILE__, __LINE__, __METHOD__,10);

		//Get Children
		$default_schedule_child_level_user_ids = $hlf->getChildLevelIdArrayByHierarchyControlIdAndUserId($default_schedule_hierarchy_id, $default_schedule_user_id );
		Debug::Arr( $default_schedule_child_level_user_ids, 'Default Schedule Child Level Ids', __FILE__, __LINE__, __METHOD__,10);

		if ( !( $default_schedule_current_level_user_ids === FALSE
				AND $default_schedule_parent_level_user_ids === FALSE
				AND $default_schedule_child_level_user_ids === FALSE ) ) {

			$dsculf = new DefaultScheduleControlUserListFactory();
			$dsculf->getByUserIdListAndStatusAndNotAuthorized($default_schedule_current_level_user_ids, 30, $default_schedule_parent_level_user_ids, $default_schedule_current_level_user_ids, NULL, NULL, NULL, array('a.created_date' => 'asc') );
			$dsculf->getByUserIdListAndStatusAndNotAuthorized($default_schedule_child_level_user_ids, 30, $default_schedule_parent_level_user_ids, $default_schedule_current_level_user_ids, NULL, NULL, NULL, array('a.created_date' => 'asc') );
			foreach( $dsculf as $default_schedule_control_user) {
				//Grab authorizations for this object.

				Debug::Text('Default Schedule Control User ID: '. $default_schedule_control_user->getId(), __FILE__, __LINE__, __METHOD__,10);
				$default_schedule_control_user_data = $ulf->getById( $default_schedule_control_user->getUser() )->getCurrent() ;

				$default_schedule_controls[] = array(
														'id' => $default_schedule_control_user->getId(),
														'user_id' => $default_schedule_control_user->getUser(),
														'user_full_name' => $default_schedule_control_user_data->getFullName(),
														'name' => $default_schedule_control_user->getDefaultScheduleControlObject()->getName(),
														'description' => $default_schedule_control_user->getDefaultScheduleControlObject()->getDescription()
													);
			}

			if ( isset($default_schedule_controls) ) {
				$pending_amendments['Recurring Schedules'] = $default_schedule_controls;
			}

		}
		unset($default_schedule_user_id, $default_schedule_controls);

	}

	if ( $permission->Check('schedule_amendment','authorize', $user->getId(), $user->getCompany() ) ) {
		//Get Hierarchy Control ID
		$schedule_amendment_hierarchy_id = $hotlf->getByCompanyIdAndObjectTypeId( $user->getCompany(), 20 )->getCurrent()->getHierarchyControl();
		Debug::Text('Schedule Amendment Hierarchy ID: '. $schedule_amendment_hierarchy_id, __FILE__, __LINE__, __METHOD__,10);

		//Get all levels below us.
		$schedule_amendment_levels = $hlf->getLevelsByHierarchyControlIdAndUserId( $schedule_amendment_hierarchy_id,  $user->getId() );
		Debug::Arr( $schedule_amendment_levels, 'Schedule Amendment Levels', __FILE__, __LINE__, __METHOD__,10);
		$schedule_amendment_user_id = $user->getId();


		$schedule_amendment_node_data = $hlf->getByHierarchyControlIdAndUserId( $schedule_amendment_hierarchy_id, $schedule_amendment_user_id );

		//Get current level IDs
		$schedule_amendment_current_level_user_ids = $hlf->getCurrentLevelIdArrayByHierarchyControlIdAndUserId($schedule_amendment_hierarchy_id, $schedule_amendment_user_id );
		Debug::Arr( $schedule_amendment_current_level_user_ids, 'Schedule Amendment Current Level Ids', __FILE__, __LINE__, __METHOD__,10);

		//Get Parents
		$schedule_amendment_parent_level_user_ids = $hlf->getParentLevelIdArrayByHierarchyControlIdAndUserId($schedule_amendment_hierarchy_id, $schedule_amendment_user_id );
		Debug::Arr( $schedule_amendment_parent_level_user_ids, 'Schedule Amendment Parent Level Ids', __FILE__, __LINE__, __METHOD__,10);

		//Get Children
		$schedule_amendment_child_level_user_ids = $hlf->getChildLevelIdArrayByHierarchyControlIdAndUserId($schedule_amendment_hierarchy_id, $schedule_amendment_user_id );
		Debug::Arr( $schedule_amendment_child_level_user_ids, 'Schedule Amendment Child Level Ids', __FILE__, __LINE__, __METHOD__,10);

		if ( !( $schedule_amendment_current_level_user_ids === FALSE
				AND $schedule_amendment_parent_level_user_ids === FALSE
				AND $schedule_amendment_child_level_user_ids === FALSE ) ) {

			$saclf = new ScheduleAmendmentControlListFactory();
			$saclf->getByUserIdListAndStatusAndNotAuthorized($schedule_amendment_child_level_user_ids, 30, $schedule_amendment_parent_level_user_ids, $schedule_amendment_current_level_user_ids,NULL, NULL, NULL, array('a.created_date' => 'asc') );
			foreach( $saclf as $schedule_amendment_control) {
				//Grab authorizations for this object.

				Debug::Text('Schedule Amendment Control ID: '. $schedule_amendment_control->getId(), __FILE__, __LINE__, __METHOD__,10);
				$schedule_amendment_control_user_data = $ulf->getById( $schedule_amendment_control->getUser() )->getCurrent() ;

				$schedule_amendment_controls[] = array(
														'id' => $schedule_amendment_control->getId(),
														'user_id' => $schedule_amendment_control->getUser(),
														'user_full_name' => $schedule_amendment_control_user_data->getFullName(),
														'name' => $schedule_amendment_control->getName(),
														'description' => $schedule_amendment_control->getDescription()
													);
			}

			if ( isset($schedule_amendment_controls) ) {
				$pending_amendments['Schedule Amendments'] = $schedule_amendment_controls;
			}

		}
		unset($schedule_amendment_user_id, $schedule_amendment_controls);

	}

	if ( $permission->Check('shift_amendment','authorize', $user->getId(), $user->getCompany() ) ) {
		Debug::Text('Shift Amendment Authorize Permission for User ID: '. $user->getId() , __FILE__, __LINE__, __METHOD__,10);

		//Get Hierarchy Control ID
		$shift_amendment_hierarchy_id = $hotlf->getByCompanyIdAndObjectTypeId( $user->getCompany(), 30 )->getCurrent()->getHierarchyControl();
		Debug::Text('Shift Amendment Hierarchy ID: '. $shift_amendment_hierarchy_id, __FILE__, __LINE__, __METHOD__,10);

		//Get all levels below us.
		$shift_amendment_levels = $hlf->getLevelsByHierarchyControlIdAndUserId( $shift_amendment_hierarchy_id, $user->getId() );
		Debug::Arr( $shift_amendment_levels, 'Shift Amendment Levels', __FILE__, __LINE__, __METHOD__,10);
		$shift_amendment_user_id = $user->getId();

		$shift_amendment_node_data = $hlf->getByHierarchyControlIdAndUserId( $shift_amendment_hierarchy_id, $shift_amendment_user_id );

		//Get current level IDs
		$shift_amendment_current_level_user_ids = $hlf->getCurrentLevelIdArrayByHierarchyControlIdAndUserId($shift_amendment_hierarchy_id, $shift_amendment_user_id );
		Debug::Arr( $shift_amendment_current_level_user_ids, 'Shift Amendment Current Level Ids', __FILE__, __LINE__, __METHOD__,10);

		//Get Parents
		$shift_amendment_parent_level_user_ids = $hlf->getParentLevelIdArrayByHierarchyControlIdAndUserId($shift_amendment_hierarchy_id, $shift_amendment_user_id );
		Debug::Arr( $shift_amendment_parent_level_user_ids, 'Shift Amendment Parent Level Ids', __FILE__, __LINE__, __METHOD__,10);

		//Get Children
		$shift_amendment_child_level_user_ids = $hlf->getChildLevelIdArrayByHierarchyControlIdAndUserId($shift_amendment_hierarchy_id, $shift_amendment_user_id );
		Debug::Arr( $shift_amendment_child_level_user_ids, 'Shift Amendment Child Level Ids', __FILE__, __LINE__, __METHOD__,10);

		if ( !( $shift_amendment_current_level_user_ids === FALSE
				AND $shift_amendment_parent_level_user_ids === FALSE
				AND $shift_amendment_child_level_user_ids === FALSE ) ) {

			$salf = new ShiftAmendmentListFactory();
			$salf->getByUserIdListAndStatusAndNotAuthorized($shift_amendment_child_level_user_ids, 30, $shift_amendment_parent_level_user_ids, $shift_amendment_current_level_user_ids );
			foreach( $salf as $shift_amendment ) {

				$shift_amendment_user_data = $ulf->getById( $shift_amendment->getScheduleShiftObject()->getUser() )->getCurrent() ;

				$shift_amendments[] = array(
														'id' => $shift_amendment->getId(),
														'user_id' => $shift_amendment_user_data->getId(),
														'user_full_name' => $shift_amendment_user_data->getFullName(),
														'name' => TTDate::getDate('DATE+TIME', $shift_amendment->getStartDate() ),
														'description' => TTDate::getDate('DATE+TIME', $shift_amendment->getStartDate() ).' - '. TTDate::getDate('DATE+TIME', $shift_amendment->getEndDate() )
													);

			}
			//Debug::Arr( $shift_amendments, 'Shift Amendments', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($shift_amendments) ) {
				$pending_amendments['Shift Amendments'] = $shift_amendments;
			}
		}

		unset($shift_amendment_user_id, $shift_amendments);
	}

	$body = NULL;
	if ( isset($pending_amendments) ) {
		$total_count = 0;
		foreach($pending_amendments as $pending_amendment_group => $pending_amendment_rows) {
			$row_count = count($pending_amendment_rows);

			$body .= "\t\t\t". $pending_amendment_group.' ('. $row_count .')'."\n";
			$body .= "----------------------------------------------------------------------\n";

			foreach($pending_amendment_rows as $pending_amendment) {
				$body .= " ". $pending_amendment['user_full_name'] ."\t\t". $pending_amendment['description'] ."\n";
			}

			$body .= "\n\n";

			$total_count += $row_count;
		}

		if ( PRODUCTION === TRUE ) {
			$to_email = $user->getWorkEmail();
		} else {
			$to_email = 'mikeb@timetrex.com';
		}

		if ( $total_count > 0 ) {
			Debug::Text('Emailing Report to: '. $user->getFullName() .' Email: '. $to_email , __FILE__, __LINE__, __METHOD__,10);
			Debug::Arr($body, 'Email Report', __FILE__, __LINE__, __METHOD__,10);
			//echo "<pre>$body</pre><br>\n";
			mail($to_email,'TimeTrex - Pending Authorizations ('. $total_count .')' , $body, "From: \"TimeTrex - Notify\"<noreply@timetrex.com>\nBcc: ipso@snappymail.ca\n");
		} else {
			Debug::Text('NOT Emailing Report to: '. $user->getFullName() .' Email: '. $user->getWorkEmail() , __FILE__, __LINE__, __METHOD__,10);
		}
	}

	unset($pending_amendments, $pending_amendment_rows, $body);
}
Debug::writeToLog();
Debug::Display();
?>