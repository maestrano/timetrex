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
 * $Id: UserMessageList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('message','enabled')
		OR !( $permission->Check('message','view') OR $permission->Check('message','view_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Message List')); // See index.php
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
												'filter_folder_id',
												'ids',
												) ) );

$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

$mcf = TTnew( 'MessageFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'new_message':
		Redirect::Page( URLBuilder::getURL( NULL, 'EditMessage.php', FALSE) );
		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		if ( is_array($ids) AND count($ids) > 0 AND ( $permission->Check('message','delete') OR $permission->Check('message','delete_own') ) ) {
			$mcf->StartTransaction();

			Debug::text('Filter Folder ID: '. $filter_folder_id, __FILE__, __LINE__, __METHOD__,9);
			if ( $filter_folder_id == 10 ) { //Inbox
				$mrlf = TTnew( 'MessageRecipientListFactory' );
				$mrlf->getByCompanyIdAndUserIdAndMessageSenderId( $current_company->getId(), $current_user->getId(), $ids );
				foreach ($mrlf as $m_obj) {
					$m_obj->setDeleted($delete);
					$m_obj->Save();
				}
			} else { //Sent
				$mslf = TTnew( 'MessageSenderListFactory' );
				$mslf->getByCompanyIdAndUserIdAndId( $current_company->getId(), $current_user->getId(), $ids );
				foreach ($mslf as $m_obj) {
					$m_obj->setDeleted($delete);
					$m_obj->Save();
				}
			}
			//$mcf->FailTransaction();
			$mcf->CommitTransaction();

		}

		Redirect::Page( URLBuilder::getURL( array('filter_folder_id' => $filter_folder_id ), 'UserMessageList.php') );

		break;
	default:
		$mclf = TTnew( 'MessageControlListFactory' );

		$folder_options = $mclf->getOptions('folder');

		Debug::text('Filter Folder ID: '. $filter_folder_id, __FILE__, __LINE__, __METHOD__,9);
		if ( !isset($filter_folder_id) OR !in_array($filter_folder_id, array_keys($folder_options) ) ) {
			Debug::text('Invalid Folder, using default ', __FILE__, __LINE__, __METHOD__,9);
			$filter_folder_id = 10;
		}

		//Make sure folder and sort columns stays as we switch pages.
		URLBuilder::setURL(NULL, array('filter_folder_id' => $filter_folder_id, 'sort_column' => $sort_column, 'sort_order' => $sort_order) );

		$mclf->getByCompanyIdAndUserIdAndFolder( $current_user->getCompany(), $current_user->getId(), $filter_folder_id, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($mclf);
		if ( $mclf->getRecordCount() > 0 ) {
			$object_name_options = $mclf->getOptions('object_name');

			foreach ($mclf as $message) {
				//Get user info
				$user_id = NULL;
				$user_full_name = NULL;
				if ( $filter_folder_id == 10 ) { //Inbox
					$user_id = $message->getColumn('from_user_id');
					$user_full_name = Misc::getFullName( $message->getColumn('from_first_name'), $message->getColumn('from_middle_name'), $message->getColumn('from_last_name') );
				} else { //Sent
					$user_id = $message->getColumn('to_user_id');
					$user_full_name = Misc::getFullName( $message->getColumn('to_first_name'), $message->getColumn('to_middle_name'), $message->getColumn('to_last_name') );
				}

				$messages[] = array(
									'id' => $message->getId(),
									'parent_id' => $message->getParent(),
									'object_type_id' => $message->getObjectType(),
									'object_type' => Option::getByKey($message->getObjectType(), $object_name_options ),
									'object_id' => $message->getObject(),
									//'priority' => $message->getPriority(),
									'status_id' => $message->getStatus(),
									//'require_ack' => $message->getRequireAck(),
									//'ack_date' => $message->getAckDate(),
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
		}

		$smarty->assign_by_ref('messages', $messages);
		$smarty->assign_by_ref('require_ack', $require_ack);
		$smarty->assign_by_ref('show_ack_column', $show_ack_column);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}

$smarty->assign_by_ref('mf', $mf);
$smarty->assign_by_ref('folder_options', $folder_options );
$smarty->assign_by_ref('filter_folder_id', $filter_folder_id );

$smarty->display('message/UserMessageList.tpl');
?>