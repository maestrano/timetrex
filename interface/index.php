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
 * $Revision: 4782 $
 * $Id: index.php 4782 2011-06-01 21:56:18Z ipso $
 * $Date: 2011-06-01 14:56:18 -0700 (Wed, 01 Jun 2011) $
 */
require_once('../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');


/* We assign $title var before translation to save breadcrumb in db as english.
 * Yet string in gettext() is still found by xgettext extraction utility.
 * This construction lets us do both without duplicating the string literal.
 */
$smarty->assign('title', TTi18n::gettext($title = 'Home'));
BreadCrumb::setCrumb($title);

//Debug::setVerbosity( 11 );

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'pref_data'
												) ) );

//Grab total number of exceptions for each severity level
$exceptions = array();

$elf = TTnew( 'ExceptionListFactory' );
$elf->getFlaggedExceptionsByUserIdAndPayPeriodStatus( $current_user->getId(), 10 );
if ( $elf->getRecordCount() > 0 ) {
	foreach($elf as $e_obj) {
		$exceptions[$e_obj->getColumn('severity_id')] = $e_obj->getColumn('total');
	}
}
unset($elf, $e_obj);
$smarty->assign_by_ref('exceptions', $exceptions);


//Grab list of recent requests
$rlf = TTnew( 'RequestListFactory' );
$rlf->getByUserIDAndCompanyId( $current_user->getId(), $current_company->getId(), 5, 1 );
if ($rlf->getRecordCount() > 0 ) {
	$status_options = $rlf->getOptions('status');
	$type_options = $rlf->getOptions('type');

	foreach ($rlf as $r_obj) {
		$requests[] = array(
							'id' => $r_obj->getId(),
							'user_date_id' => $r_obj->getUserDateID(),
							'date_stamp' => TTDate::strtotime($r_obj->getColumn('date_stamp')),
							'status_id' => $r_obj->getStatus(),
							'status' => Misc::TruncateString( $status_options[$r_obj->getStatus()], 15 ),
							'type_id' => $r_obj->getType(),
							'type' => $type_options[$r_obj->getType()],
							'created_date' => $r_obj->getCreatedDate(),
							'deleted' => $r_obj->getDeleted()
						);
	}
}
$smarty->assign_by_ref('requests', $requests);

//Grab list of unread messages
$mclf = TTnew( 'MessageControlListFactory' );
$mclf->getByCompanyIdAndUserIdAndFolder( $current_user->getCompany(), $current_user->getId(), 10, 5, 1 );
if ( $mclf->getRecordCount() > 0 ) {
	$object_name_options = $mclf->getOptions('object_name');
	foreach ($mclf as $message) {
		//Get user info
		$user_id = $message->getColumn('from_user_id');
		$user_full_name = Misc::getFullName( $message->getColumn('from_first_name'), $message->getColumn('from_middle_name'), $message->getColumn('from_last_name') );

		$messages[] = array(
							'id' => $message->getId(),
							'parent_id' => $message->getParent(),
							'object_type_id' => $message->getObjectType(),
							'object_type' => Option::getByKey($message->getObjectType(), $object_name_options ),
							'object_id' => $message->getObject(),
							'status_id' => $message->getStatus(),
							'subject' => $message->getSubject(),
							'body' => $message->getBody(),

							'user_id' => $user_id,
							'user_full_name' =>  $user_full_name,
							'created_date' => $message->getCreatedDate(),
							'created_by' => $message->getCreatedBy(),
							'updated_date' => $message->getUpdatedDate(),
							'updated_by' => $message->getUpdatedBy(),
							'deleted_date' => $message->getDeletedDate(),
							'deleted_by' => $message->getDeletedBy()
						);
	}
	$smarty->assign_by_ref('messages', $messages);
}

//Grab requests pending authorization if they are a supervisor.
if ( $permission->Check('authorization','enabled')
		AND $permission->Check('authorization','view')
		AND $permission->Check('request','authorize') ) {

	$hllf = TTnew( 'HierarchyLevelListFactory' );
	$request_levels = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), array(1010,1020,1030,1040,1100) );

	$selected_levels['request'] = 1;
	if ( isset($selected_levels['request']) AND isset($request_levels[$selected_levels['request']]) ) {
		$request_selected_level = $request_levels[$selected_levels['request']];
		Debug::Text(' Switching Levels to Level: '. key($request_selected_level), __FILE__, __LINE__, __METHOD__,10);
	} elseif ( isset($request_levels[1]) ) {
		$request_selected_level = $request_levels[1];
	} else {
		Debug::Text( 'No Request Levels... Not in hierarchy?', __FILE__, __LINE__, __METHOD__,10);
		$request_selected_level = 0;
	}

	if ( is_array($request_selected_level) ) {
		$rlf = TTnew( 'RequestListFactory' );
		$rlf->getByHierarchyLevelMapAndStatusAndNotAuthorized($request_selected_level, 30);

		$status_options = $rlf->getOptions('status');
		$type_options = $rlf->getOptions('type');

		foreach( $rlf as $r_obj) {
			//Grab authorizations for this object.
			$pending_requests[] = array(
									'id' => $r_obj->getId(),
									'user_date_id' => $r_obj->getId(),
									'user_id' => $r_obj->getUserDateObject()->getUser(),
									'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
									'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
									'type_id' => $r_obj->getType(),
									'type' => $type_options[$r_obj->getType()],
									'status_id' => $r_obj->getStatus(),
									'status' => $status_options[$r_obj->getStatus()]
								);
		}
	} else {
		Debug::Text( 'No hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
	}
	$smarty->assign_by_ref('pending_requests', $pending_requests);
	unset($pending_requests, $request_hierarchy_id, $request_user_id, $request_node_data, $request_current_level_user_ids, $request_parent_level_user_ids, $request_child_level_user_ids );
}

$smarty->display('index.tpl');
?>