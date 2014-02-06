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
 * $Revision: 4335 $
 * $Id: EmbeddedMessageList.php 4335 2011-03-05 00:33:51Z ipso $
 * $Date: 2011-03-04 16:33:51 -0800 (Fri, 04 Mar 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('message','enabled')
		OR !( $permission->Check('message','view') OR $permission->Check('message','view_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Message List') ); // See index.php
//BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'object_type_id',
												'object_id',
												'object_user_id',
												'parent_id',
												'message_data',
												'template',
												'close'
												) ) );

$mcf = TTnew( 'MessageControlFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit_message':
		//Debug::setVerbosity(11);
		if ( !$permission->Check('message','enabled')
			OR !( $permission->Check('message','add') ) ) {

			$permission->Redirect( FALSE ); //Redirect

		}

		if ( isset($object_type_id) AND isset($object_id) ) {
			if ( !isset($parent_id) ) {
				$parent_id = 0;
			}

			$mcf->StartTransaction();

			$mcf = TTnew( 'MessageControlFactory' );

			$mcf->setObjectType( $object_type_id );
			$mcf->setObject( $object_id );
			$mcf->setParent( $parent_id );

			$mcf->setFromUserId( $current_user->getId() );

			//This needs to reply to all those involved in the object thread. As when the object (request) creator
			//responds, we don't know who exactly they are responding to? Or should it be the last message sender only?
			$mclf = TTnew( 'MessageControlListFactory' );
			$to_user_ids = $mclf->getByCompanyIdAndObjectTypeAndObjectAndNotUser( $current_user->getCompany(), $object_type_id, $object_id, $current_user->getId() );
			if ( isset($object_user_id) AND $object_user_id > 0) {
				$to_user_ids[] = $object_user_id;
			}
			$mcf->setToUserId( $to_user_ids );

			$mcf->setSubject( $message_data['subject'] );
			$mcf->setBody( $message_data['body'] );
			$mcf->setRequireAck( FALSE );

			if ( $mcf->isValid() ) {
				if ( $mcf->Save() == TRUE ) {
					//$mcf->FailTransaction();
					$mcf->CommitTransaction();
					Redirect::Page( URLBuilder::getURL( 	array(	'template' => $template,
																	'close' => 1,
																	'object_type_id' => $object_type_id,
																	'object_id' => $object_id), 'EmbeddedMessageList.php') );
					break;
				}
			}

			$mcf->FailTransaction();
		}
	default:
		if ( isset($object_type_id) AND isset($object_id) ) {
			$mclf = TTnew( 'MessageControlListFactory' );
			$mclf->getByCompanyIDAndUserIdAndObjectTypeAndObject( $current_user->getCompany(), $current_user->getId(), $object_type_id, $object_id );

			if ( $mclf->getRecordCount() > 0 ) {
				$mark_read_message_ids = array();
				$i=0;
				foreach( $mclf as $message ) {
					$ulf = TTnew( 'UserListFactory' );

					$from_user_id = $message->getColumn('from_user_id');
					$from_user_full_name = Misc::getFullName( $message->getColumn('from_first_name'), $message->getColumn('from_middle_name'), $message->getColumn('from_last_name') );

					$messages[] = array(
										'id' => $message->getId(),
										'parent_id' => $message->getParent(),
										'object_type_id' => $message->getObjectType(),
										'object_id' => $message->getObject(),
										'status_id' => $message->getStatus(),
										'subject' => $message->getSubject(),
										'body' => $message->getBody(),

										'from_user_id' => $from_user_id,
										'from_user_full_name' => $from_user_full_name,

										'created_date' => $message->getCreatedDate(),
										'created_by' => $message->getCreatedBy(),
										'updated_date' => $message->getUpdatedDate(),
										'updated_by' => $message->getUpdatedBy(),
										'deleted_date' => $message->getDeletedDate(),
										'deleted_by' => $message->getDeletedBy()
									);

					//Mark own messages as read.
					if ( $message->getStatus() == 10 AND $message->getCreatedBy() != $current_user->getId() ) {
						$mark_read_message_ids[] = $message->getId();
					}

					if ( $i == 0 ) {
						$parent_id = $message->getId();
						$default_subject = TTi18n::gettext('Re:').' '.$message->getSubject();
					}

					$i++;
				}

				MessageControlFactory::markRecipientMessageAsRead( $current_user->getCompany(), $current_user->getID(), $mark_read_message_ids );
			}

			//Get object data
			$object_name_options = $mclf->getOptions('object_name');
			$smarty->assign_by_ref('object_name', $object_name_options[$object_type_id]);
			$smarty->assign_by_ref('messages', $messages);
			$smarty->assign_by_ref('message_data', $message_data);
			$smarty->assign_by_ref('default_subject', $default_subject);
			$smarty->assign_by_ref('total_messages', $i);

			$smarty->assign_by_ref('parent_id', $parent_id);
			$smarty->assign_by_ref('object_type_id', $object_type_id);
			$smarty->assign_by_ref('object_id', $object_id);
			$smarty->assign_by_ref('object_user_id', $object_user_id);
		}

		$smarty->assign_by_ref('template', $template);
		$smarty->assign_by_ref('close', $close);

		break;
}

$smarty->assign_by_ref('mcf', $mcf);

if ( $template == 1 ) {
	$smarty->display('message/LayerMessageList.tpl');
} else {
	$smarty->display('message/EmbeddedMessageList.tpl');
}
?>