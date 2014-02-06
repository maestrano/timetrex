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
 * $Revision: 7628 $
 * $Id: ViewRequest.php 7628 2012-09-12 19:21:29Z ipso $
 * $Date: 2012-09-12 12:21:29 -0700 (Wed, 12 Sep 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('request','enabled')
		OR !( $permission->Check('request','edit')
				OR $permission->Check('request','edit_own')
				 ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'View Request')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'hierarchy_type_id',
												'request_id',
												'request_queue_ids',
												'selected_level'
												) ) );

if ( isset($request_queue_ids) ) {
	$request_queue_ids = unserialize( base64_decode( urldecode($request_queue_ids) ) );
	Debug::Arr($request_queue_ids, ' Input Request Queue IDs '. $action, __FILE__, __LINE__, __METHOD__,10);
}
if ( isset($data) ) {
	$data['date_stamp'] = TTDate::parseDateTime($data['date_stamp']);
}

$rf = TTnew( 'RequestFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'pass':
		if ( count($request_queue_ids) > 1 ) {
			//Remove the authorized/declined request from the stack.
			array_shift($request_queue_ids);
			Redirect::Page( URLBuilder::getURL( array('id' => $request_queue_ids[0], 'selected_level' => $selected_level, 'request_queue_ids' => base64_encode( serialize($request_queue_ids) ) ), 'ViewRequest.php') );
		} else {
			Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
		}
		break;
	case 'decline':
	case 'authorize':
		//Debug::setVerbosity(11);
		Debug::text(' Authorizing Request: Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		if ( !empty($request_id) ) {
			Debug::text(' Authorizing Request ID: '. $request_id, __FILE__, __LINE__, __METHOD__,10);

			$af = TTnew( 'AuthorizationFactory' );
			$af->StartTransaction();
			$af->setCurrentUser( $current_user->getID() );
			$af->setObjectType( $hierarchy_type_id );
			$af->setObject( $request_id );

			if ( $action == 'authorize' ) {
				Debug::text(' Approving Authorization: ', __FILE__, __LINE__, __METHOD__,10);
				$af->setAuthorized(TRUE);
			} else {
				Debug::text(' Declining Authorization: ', __FILE__, __LINE__, __METHOD__,10);
				$af->setAuthorized(FALSE);
			}

			if ( $af->isValid() ) {
				$af->Save();
				$af->CommitTransaction();

				if ( count($request_queue_ids) > 1 ) {
					//Remove the authorized/declined request from the stack.
					array_shift($request_queue_ids);
					Redirect::Page( URLBuilder::getURL( array('id' => $request_queue_ids[0], 'selected_level' => $selected_level, 'request_queue_ids' => base64_encode( serialize($request_queue_ids) ) ), 'ViewRequest.php') );
					break;
				}
			}

			$af->FailTransaction();
		}
		Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
		break;
	default:
		if ( (int)$id > 0 ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$rlf = TTnew( 'RequestListFactory' );
			$rlf->getByIDAndCompanyID( $id, $current_company->getId() );
			if ( $rlf->getRecordCount() == 1 ) {
				foreach ($rlf as $r_obj) {
					//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);
					$hierarchy_type_id = $r_obj->getHierarchyTypeID();
					$type_id = $r_obj->getType();

					$data = array(
										'id' => $r_obj->getId(),
										'user_date_id' => $r_obj->getId(),
										'user_id' => $r_obj->getUserDateObject()->getUser(),
										'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
										'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
										'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
										'type_id' => $r_obj->getType(),
										'hierarchy_type_id' => $r_obj->getHierarchyTypeID(),
										'status_id' => $r_obj->getStatus(),
										'authorized' => $r_obj->getAuthorized(),
										'created_date' => $r_obj->getCreatedDate(),
										'created_by' => $r_obj->getCreatedBy(),
										'updated_date' => $r_obj->getUpdatedDate(),
										'updated_by' => $r_obj->getUpdatedBy(),
										'deleted_date' => $r_obj->getDeletedDate(),
										'deleted_by' => $r_obj->getDeletedBy()
									);
				}

				//Get Next Request to authorize:
				if ( $permission->Check('request','authorize')
						AND $selected_level != NULL
						AND count($request_queue_ids) <= 1 ) {

					Debug::Text('Get Request Queue: ', __FILE__, __LINE__, __METHOD__,10);

					$ulf = TTnew( 'UserListFactory' );
					$hlf = TTnew( 'HierarchyListFactory' );
					$hllf = TTnew( 'HierarchyLevelListFactory' );

					$request_levels = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), $hierarchy_type_id );
					//Debug::Arr( $request_levels, 'Request Levels', __FILE__, __LINE__, __METHOD__,10);

					if ( isset($selected_level) AND isset($request_levels[$selected_level]) ) {
						$request_selected_level = $request_levels[$selected_level];
						Debug::Text(' Switching Levels to Level: '. key($request_selected_level), __FILE__, __LINE__, __METHOD__,10);
					} elseif ( isset($request_levels[1]) ) {
						$request_selected_level = $request_levels[1];
					} else {
						Debug::Text( 'No Request Levels... Not in hierarchy?', __FILE__, __LINE__, __METHOD__,10);
						$request_selected_level = 0;
					}

					if ( is_array($request_selected_level) ) {
						Debug::Text( 'Hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
						$rlf = TTnew( 'RequestListFactory' );
						//$rlf->getByHierarchyLevelMapAndStatusAndNotAuthorized( $request_selected_level, 30 );
						$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($request_selected_level, $type_id, 30 );

						//Get all IDs that need authorizing.
						//Only do 25 at a time, then grab more.
						$i=0;
						$start=FALSE;
						foreach( $rlf as $r_obj) {
							if ( $id == $r_obj->getId() ) {
								$start = TRUE;
							}

							if ( $start == TRUE ) {
								$request_queue_ids[] = $r_obj->getId();
							}

							if ( $i > 25 ) {
								break;
							}
							$i++;
						}

						if ( isset($request_queue_ids) ) {
							$request_queue_ids = array_unique($request_queue_ids);
						}
					} else {
						Debug::Text( 'No hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
					}
				}
			}
		}

		//Select box options;
		$data['status_options'] = $rf->getOptions('status');
		$data['type_options'] = $rf->getOptions('type');

		if ( isset($request_queue_ids) ) {
			Debug::Arr($request_queue_ids, ' Output Request Queue IDs '. $action, __FILE__, __LINE__, __METHOD__,10);
			$smarty->assign_by_ref('request_queue_ids', urlencode( base64_encode( serialize($request_queue_ids) ) ) );
		}

		$smarty->assign_by_ref('selected_level', $selected_level);
		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('rf', $rf);

$smarty->display('request/ViewRequest.tpl');
?>