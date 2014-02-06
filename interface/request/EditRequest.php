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
 * $Id: EditRequest.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
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

$smarty->assign('title', TTi18n::gettext($title = 'Edit Request')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data) ) {
	$data['date_stamp'] = TTDate::parseDateTime($data['date_stamp']);
}

$rf = TTnew( 'RequestFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$rf->StartTransaction();

		$rf->setId( $data['id'] );
		$rf->setUserDate( $data['user_id'], $data['date_stamp'] );

		$rf->setType( $data['type_id'] );
		$rf->setStatus( 30 );
		if ( $rf->isNew() ) {
			Debug::Text('Object is NEW!', __FILE__, __LINE__, __METHOD__,10);
			$rf->setMessage( $data['message'] );
		} else {
			Debug::Text('Object is NOT new!', __FILE__, __LINE__, __METHOD__,10);
		}

		if ( $rf->isValid() ) {
			$request_id = $rf->Save();

			$rf->CommitTransaction();
			//$rf->FailTransaction();

			//Redirect::Page( URLBuilder::getURL( array('refresh' => FALSE ), '../CloseWindow.php') );

			Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );

		}
		$rf->FailTransaction();

	default:
		if ( (int)$id > 0 ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$rlf = TTnew( 'RequestListFactory' );
			$rlf->getByIDAndCompanyID( $id, $current_company->getId() );

			foreach ($rlf as $r_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $r_obj->getId(),
									'user_date_id' => $r_obj->getId(),
									'user_id' => $r_obj->getUserDateObject()->getUser(),
									'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
									'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
									'type_id' => $r_obj->getType(),
									'status_id' => $r_obj->getStatus(),
									'created_date' => $r_obj->getCreatedDate(),
									'created_by' => $r_obj->getCreatedBy(),
									'updated_date' => $r_obj->getUpdatedDate(),
									'updated_by' => $r_obj->getUpdatedBy(),
									'deleted_date' => $r_obj->getDeletedDate(),
									'deleted_by' => $r_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);
			//UserID has to be set at minimum
			$data = array(
						'user_id' => $current_user->getId(),
						'user_full_name' => $current_user->getFullName(),
						'date_stamp' => TTDate::getTime()
					);
		} else {
			$data['user_full_name'] = $current_user->getFullName();
		}

		//Select box options;
		$data['status_options'] = $rf->getOptions('status');
		$data['type_options'] = $rf->getOptions('type');

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('rf', $rf);

$smarty->display('request/EditRequest.tpl');
?>