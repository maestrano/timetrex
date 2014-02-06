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
 * $Revision: 1246 $
 * $Id: InstallSchema_1001B.class.php 1246 2007-09-14 23:47:42Z ipso $
 * $Date: 2007-09-14 16:47:42 -0700 (Fri, 14 Sep 2007) $
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1037A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion() , __FILE__, __LINE__, __METHOD__,9);

		return TRUE;
	}


	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);

		//Migrate messages from old system to new system.
		$mlf = TTnew( 'MessageListFactory' );
		$mlf->StartTransaction();
		$mlf->getAll( NULL, NULL, NULL,  array('created_date' => 'asc' ) );
		if ( $mlf->getRecordCount() > 0 ) {
			$id_map = array(); //Maps the old_id to the message_sender_id.

			$ulf = TTnew( 'UserListFactory' );
			$i=0;
			$e=0;
			foreach( $mlf as $message) {
				if ( !in_array( $message->getObjectType(), array(5,50,90) ) ) {
					continue;
				}

				//Created_by is the sender
				//Object_id is the received if its an email, otherwise the receiver is all those in the thread.
				if ( $message->getCreatedBy() != FALSE ) {
					$ulf->getById( $message->getCreatedBy() );
					if ( $ulf->getRecordCount() > 0 ) {
						$created_by_user_obj = $ulf->getCurrent();
					}
				}

				if ( isset($created_by_user_obj) AND is_object($created_by_user_obj) AND $created_by_user_obj->getCompanyObject()->getStatus() != 30 ) {
					Debug::text('Message: Object Type: '. $message->getObjectType() .' Object ID: '. $message->getObject() .'  From User ID: '. $message->getCreatedBy() .' Subject: '. $message->getSubject() , __FILE__, __LINE__, __METHOD__, 10);

					$mcf = TTnew( 'MessageControlFactory' );

					$mcf->migration_status = $message->getStatus();
					$mcf->setObjectType( $message->getObjectType() );

					if ( $message->getParent() > 0 AND isset($id_map[$message->getParent()]) AND $id_map[$message->getParent()] > 0 ) {
						Debug::text('Using Parent ID: '. $id_map[$message->getParent()] , __FILE__, __LINE__, __METHOD__, 10);
						$mcf->setParent( $id_map[$message->getParent()] ); //We need to use our own parent_ids.
					}
					$mcf->setFromUserId( $message->getCreatedBy() );

					if ( $message->getObjectType() == 5 ) { //Email
						$mcf->setObject( $message->getCreatedBy() ); //User ID for the sender only.
						if ( $message->getCreatedBy() != $message->getObject() ) { //Make sure we don't save emails sent from ourselves to ourselves.
							$mcf->setToUserId( $message->getObject() ); //Get sender of the original message, as we only reply directly to them.
						}
					} else {
						$mcf->setObject( $message->getObject() ); //ID of the related objet.

						//We may never know who the message is actually sent to when its not an email, as the hierarchies and such could have changed.
						//Try our best though using current hierarchies.
						$hlf = TTnew( 'HierarchyListFactory' );
						$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $created_by_user_obj->getCompany(), $message->getCreatedBy(), $message->getObjectType(), TRUE, FALSE ); //Request - Immediate parents only.
						//Debug::Arr($request_parent_level_user_ids, 'Sending message to current direct Superiors: ', __FILE__, __LINE__, __METHOD__,10);
						if ( $request_parent_level_user_ids !== FALSE ) {
							$to_user_ids = (array)$request_parent_level_user_ids;
						}

						$mslf = TTnew( 'MessageSenderListFactory' );
						$mslf->getByCompanyIdAndObjectTypeAndObjectAndNotUser( $created_by_user_obj->getCompany(), $message->getObjectType(), $message->getObject(), $message->getCreatedBy() );
						if ( $mslf->getRecordCount() > 0 ) {
							foreach( $mslf as $ms_obj ) {
								$to_user_ids[] = $ms_obj->getUser();
							}
						}

						if ( isset($to_user_ids) ) {
							$mcf->setToUserId( $to_user_ids ); //Get sender of the original message, as we only reply directly to them.
						}
					}

					$mcf->setSubject( $message->getSubject() );
					$mcf->setBody( $message->getBody() );
					$mcf->setRequireAck( FALSE );

					//Match created/updated information with original message.
					$mcf->setCreatedBy( $message->getCreatedBy() );
					$mcf->setCreatedDate( $message->getCreatedDate() );
					$mcf->setUpdatedBy( $message->getUpdatedBy() );
					$mcf->setUpdatedDate( $message->getUpdatedDate() );

					$mcf->setEnableEmailMessage(FALSE); //Don't email out any messages, as they have already been sent.
					if ( $mcf->isValid() ) {
						//Some object_id's may be invalid as the request has been deleted or something.
						$mcf->Save(FALSE);
						if ( $mcf->getMessageSenderId() > 0 ) {
							$id_map[$message->getId()] = $mcf->getMessageSenderId();
						}
					} else {
						Debug::text('Failed creating message...', __FILE__, __LINE__, __METHOD__, 10);
						$e++;
					}
				}

				unset($created_by_user_obj, $to_user_ids, $ms_obj, $mslf, $mcf);
				$i++;
			}
			Debug::text('Converted: '. $i .' Messages, Failed: '. $e, __FILE__, __LINE__, __METHOD__, 10);
		}

		$mlf->CommitTransaction();
		unset($id_map);

		//Go through each permission group, and enable view_schedule_summary report for anyone who can see view_timesheet_summary.
		$clf = TTnew( 'CompanyListFactory' );
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach( $clf as $c_obj ) {
				Debug::text('Company: '. $c_obj->getName(), __FILE__, __LINE__, __METHOD__,9);
				if ( $c_obj->getStatus() != 30 ) {
					$pclf = TTnew( 'PermissionControlListFactory' );
					$pclf->getByCompanyId( $c_obj->getId(), NULL, NULL, NULL, array( 'name' => 'asc' ) ); //Sort order defaults to "level" column in newer versions which doesn't exist when this runs.
					if ( $pclf->getRecordCount() > 0 ) {
						foreach( $pclf as $pc_obj ) {
							Debug::text('Permission Group: '. $pc_obj->getName(), __FILE__, __LINE__, __METHOD__,9);
							$plf = TTnew( 'PermissionListFactory' );
							$plf->getByCompanyIdAndPermissionControlIdAndSectionAndName( $c_obj->getId(), $pc_obj->getId(), 'report', 'view_timesheet_summary');
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text('Found permission group with job analysis report enabled: '. $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__,9);
								$pc_obj->setPermission( array('report' => array('view_schedule_summary' => TRUE) ) );
							} else {
								Debug::text('Permission group does NOT have job analysis report enabled...', __FILE__, __LINE__, __METHOD__,9);
							}
						}
					}

				}
			}
		}

		return TRUE;
	}
}
?>
