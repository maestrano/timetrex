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
 * $Revision: 11018 $
 * $Id: MessageFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\Message
 */
class MessageFactory extends Factory {
	protected $table = 'message';
	protected $pk_sequence_name = 'message_id_seq'; //PK Sequence name
	protected $obj_handler = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										5 => 'email',
										//10 => 'default_schedule',
										//20 => 'schedule_amendment',
										//30 => 'shift_amendment',
										40 => 'authorization',
										50 => 'request',
										60 => 'job',
										70 => 'job_item',
										80 => 'client',
										90 => 'timesheet',
										100 => 'user' //For notes assigned to users?
									);
				break;
			case 'object_name':
				$retval = array(
										5 => TTi18n::gettext('Email'), //Email from user to another
										10 => TTi18n::gettext('Recurring Schedule'),
										20 => TTi18n::gettext('Schedule Amendment'),
										30 => TTi18n::gettext('Shift Amendment'),
										40 => TTi18n::gettext('Authorization'),
										50 => TTi18n::gettext('Request'),
										60 => TTi18n::gettext('Job'),
										70 => TTi18n::gettext('Task'),
										80 => TTi18n::gettext('Client'),
										90 => TTi18n::gettext('TimeSheet'),
										100 => TTi18n::gettext('Employee') //For notes assigned to users?
									);
				break;

			case 'folder':
				$retval = array(
										10 => TTi18n::gettext('Inbox'),
										20 => TTi18n::gettext('Sent')
									);
				break;
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('UNREAD'),
										20 => TTi18n::gettext('READ')
									);
				break;
			case 'priority':
				$retval = array(
										10 => TTi18n::gettext('LOW'),
										50 => TTi18n::gettext('NORMAL'),
										100 => TTi18n::gettext('HIGH'),
										110 => TTi18n::gettext('URGENT')
									);
				break;

		}

		return $retval;
	}


	function getParent() {
		if ( isset($this->data['parent_id']) ) {
			return $this->data['parent_id'];
		}

		return FALSE;
	}
	function setParent($id) {
		$id = trim($id);

		if ( empty($id) ) {
			$id = 0;
		}

		$mlf = TTnew( 'MessageListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'parent',
															$mlf->getByID($id),
															TTi18n::gettext('Parent is invalid')
															) ) {
			$this->data['parent_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getObjectHandler() {
		if ( is_object($this->obj_handler) ) {
			return $this->obj_handler;
		} else {
			switch ( $this->getObjectType() ) {
				case 5:
				case 100:
					$this->obj_handler = TTnew( 'UserListFactory' );
					break;
				case 40:
					$this->obj_handler = TTnew( 'AuthorizationListFactory' );
					break;
				case 50:
					$this->obj_handler = TTnew( 'RequestListFactory' );
					break;
				case 90:
					$this->obj_handler = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
					break;
			}

			return $this->obj_handler;
		}
	}

	function getObjectType() {
		if ( isset($this->data['object_type_id']) ) {
			return $this->data['object_type_id'];
		}

		return FALSE;
	}
	function setObjectType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'object_type',
											$type,
											TTi18n::gettext('Object Type is invalid'),
											$this->getOptions('type')) ) {

			$this->data['object_type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getObject() {
		if ( isset($this->data['object_id']) ) {
			return $this->data['object_id'];
		}

		return FALSE;
	}
	function setObject($id) {
		$id = trim($id);

		if ( $this->Validator->isResultSetWithRows(	'object',
													$this->getObjectHandler()->getByID($id),
													TTi18n::gettext('Object ID is invalid')
													) ) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPriority() {
		if ( isset($this->data['priority_id']) ) {
			return $this->data['priority_id'];
		}

		return FALSE;
	}
	function setPriority($priority = NULL) {
		$priority = trim($priority);

		if ( empty($priority) ) {
			$priority = 50;
		}

		$key = Option::getByValue($priority, $this->getOptions('priority') );
		if ($key !== FALSE) {
			$priority = $key;
		}

		if ( $this->Validator->inArrayKey(	'priority',
											$priority,
											TTi18n::gettext('Invalid Priority'),
											$this->getOptions('priority')) ) {

			$this->data['priority_id'] = $priority;

			return FALSE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return $this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->setStatusDate();

			$this->data['status_id'] = $status;

			return FALSE;
		}

		return FALSE;
	}

	function getStatusDate() {
		if ( isset($this->data['status_date']) ) {
			return $this->data['status_date'];
		}

		return FALSE;
	}
	function setStatusDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'status_date',
												$epoch,
												TTi18n::gettext('Incorrect Date')) ) {

			$this->data['status_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getSubject() {
		if ( isset($this->data['subject']) ) {
			return $this->data['subject'];
		}

		return FALSE;
	}
	function setSubject($text) {
		$text = trim($text);

		if 	(	strlen($text) == 0
				OR
				$this->Validator->isLength(		'subject',
												$text,
												TTi18n::gettext('Invalid Subject length'),
												2,
												100) ) {

			$this->data['subject'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getBody() {
		if ( isset($this->data['body']) ) {
			return $this->data['body'];
		}

		return FALSE;
	}
	function setBody($text) {
		$text = trim($text);

		if 	(	$this->Validator->isLength(		'body',
												$text,
												TTi18n::gettext('Invalid Body length'),
												5,
												1024) ) {

			$this->data['body'] = $text;

			return TRUE;
		}

		return FALSE;
	}


	function isAck() {
		if ($this->getRequireAck() == TRUE AND $this->getAckDate() == '' ) {
			return FALSE;
		}

		return TRUE;
	}

	function getRequireAck() {
		return $this->fromBool( $this->data['require_ack'] );
	}
	function setRequireAck($bool) {
		$this->data['require_ack'] = $this->toBool($bool);

		return true;
	}

	function getAck() {
		return $this->fromBool( $this->data['ack'] );
	}
	function setAck($bool) {
		$this->data['ack'] = $this->toBool($bool);

		if ( $this->getAck() == TRUE ) {
			$this->setAckDate();
			$this->setAckBy();
		}

		return true;
	}

	function getAckDate() {
		if ( isset($this->data['ack_date']) ) {
			return $this->data['ack_date'];
		}

		return FALSE;
	}
	function setAckDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'ack_date',
												$epoch,
												TTi18n::gettext('Invalid Acknowledge Date') ) ) {

			$this->data['ack_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getAckBy() {
		if ( isset($this->data['ack_by']) ) {
			return $this->data['ack_by'];
		}

		return FALSE;
	}
	function setAckBy($id = NULL) {
		$id = trim($id);

		if ( empty($id) ) {
			global $current_user;

			if ( is_object($current_user) ) {
				$id = $current_user->getID();
			} else {
				return FALSE;
			}
		}

		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'ack_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {

			$this->data['ack_by'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getEmailMessageAddresses() {
		$olf = $this->getObjectHandler();
		if ( is_object( $olf ) ) {

			$olf->getById( $this->getObject() );
			if ( $olf->getRecordCount() > 0 ) {
				$obj = $olf->getCurrent();

				switch ( $this->getObjectType() ) {
					case 5:
					case 100:
						Debug::Text('Email Object Type... Parent ID: '. $this->getParent(), __FILE__, __LINE__, __METHOD__,10);
						if ( $this->getParent() == 0 ) {
							$user_ids[] = $obj->getId();
						} else {
							$mlf = TTnew( 'MessageListFactory' );
							$mlf->getById( $this->getParent() );
							if ( $mlf->getRecordCount() > 0 ) {
								$m_obj = $mlf->getCurrent();

								$user_ids[] = $m_obj->getCreatedBy();
							}
							Debug::Text('cEmail Object Type... Parent ID: '. $this->getParent(), __FILE__, __LINE__, __METHOD__,10);
						}
						break;
					case 40:
						$user_ids[] = $obj->getId();
						break;
					case 50: //Request
						//Get all users who have contributed to the thread.
						$mlf = TTnew( 'MessageListFactory' );
						$mlf->getMessagesInThreadById( $this->getId() );
						Debug::Text(' Messages In Thread: '. $mlf->getRecordCount() , __FILE__, __LINE__, __METHOD__,10);
						if ( $mlf->getRecordCount() > 0 ) {
							foreach( $mlf as $m_obj ) {
								$user_ids[] = $m_obj->getCreatedBy();
							}
						}
						unset($mlf, $m_obj);
						//Debug::Arr($user_ids, 'User IDs in Thread: ', __FILE__, __LINE__, __METHOD__,10);

						//Only alert direct supervisor to request at this point. Because we need to take into account
						//if the request was authorized or not to determine if we should email the next higher level in the hierarchy.
						if ( $this->getParent() == 0 ) {
							//Get direct parent in hierarchy.
							$u_obj = $obj->getUserObject();

							$hlf = TTnew( 'HierarchyListFactory' );
							$user_ids[] = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $u_obj->getCompany(), $u_obj->getId(), $this->getObjectType(), TRUE, FALSE );
							unset($hlf);
						}

						global $current_user;
						if ( isset($current_user) AND is_object( $current_user ) AND isset($user_ids) AND is_array($user_ids) ) {
							$user_ids = array_unique( $user_ids );
							$current_user_key = array_search($current_user->getId(), $user_ids );
							Debug::Text(' Current User Key: '. $current_user_key, __FILE__, __LINE__, __METHOD__,10);
							if ( $current_user_key !== FALSE ) {
								Debug::Text(' Removing Current User From Recipient List...'. $current_user->getId() , __FILE__, __LINE__, __METHOD__,10);
								unset($user_ids[$current_user_key]);
							}
						} else {
							Debug::Text(' Current User Object not available...', __FILE__, __LINE__, __METHOD__,10);
						}
						unset($current_user, $current_user_key);

						break;
					case 90:
						$user_ids[] = $obj->getUser();
						break;
				}
			}

			if ( isset($user_ids) AND is_array($user_ids) ) {
				//Get user preferences and determine if they accept email notifications.
				Debug::Arr($user_ids, 'Recipient User Ids: ', __FILE__, __LINE__, __METHOD__,10);

				$uplf = TTnew( 'UserPreferenceListFactory' );
				$uplf->getByUserId( $user_ids );
				if ( $uplf->getRecordCount() > 0 ) {
					foreach( $uplf as $up_obj ) {
						if ( $up_obj->getEnableEmailNotificationMessage() == TRUE AND $up_obj->getUserObject()->getStatus() == 10 ) {
							if ( $up_obj->getUserObject()->getWorkEmail() != '' ) {
								$retarr[] = $up_obj->getUserObject()->getWorkEmail();
							}

							if ( $up_obj->getEnableEmailNotificationHome() AND $up_obj->getUserObject()->getHomeEmail() != '' ) {
								$retarr[] = $up_obj->getUserObject()->getHomeEmail();
							}
						}
					}

					if ( isset($retarr) ) {
						Debug::Arr($retarr, 'Recipient Email Addresses: ', __FILE__, __LINE__, __METHOD__,10);
						return $retarr;

					}
				}
			}
		}

		return FALSE;
	}

	function emailMessage() {
		Debug::Text('emailMessage: ', __FILE__, __LINE__, __METHOD__,10);

		$email_to_arr = $this->getEmailMessageAddresses();
		if ( $email_to_arr == FALSE ) {
			return FALSE;
		}

		$from = $reply_to = 'DoNotReply@'. Misc::getHostName( FALSE );

		global $current_user, $config_vars;
		if ( is_object($current_user) AND $current_user->getWorkEmail() != '' ) {
			$reply_to = $current_user->getWorkEmail();
		}
		Debug::Text('From: '. $from .' Reply-To: '. $reply_to, __FILE__, __LINE__, __METHOD__,10);

		$to = array_shift( $email_to_arr );
		Debug::Text('To: '. $to, __FILE__, __LINE__, __METHOD__,10);
		if ( is_array($email_to_arr) AND count($email_to_arr) > 0 ) {
			$bcc = implode(',', $email_to_arr);
		} else {
			$bcc = NULL;
		}

		$email_subject = TTi18n::gettext('New message waiting in').' '. APPLICATION_NAME;
		$email_body  = TTi18n::gettext('*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*')."\n\n";
		$email_body  .= TTi18n::gettext('You have a new message waiting for you in').' '. APPLICATION_NAME."\n";
		if ( $this->getSubject() != '' ) {
			$email_body .= TTi18n::gettext('Subject:').' '. $this->getSubject()."\n";
		}

		$protocol = 'http';
		if ( isset($config_vars['other']['force_ssl']) AND $config_vars['other']['force_ssl'] == 1 ) {
			$protocol .= 's';
		}

		$email_body .= TTi18n::gettext('Link').': <a href="'. $protocol .'://'. Misc::getHostName().Environment::getDefaultInterfaceBaseURL().'">'. APPLICATION_NAME .' '. TTi18n::getText('Login') .'</a>';

		//Define subject/body variables here.
		$search_arr = array(
							'#employee_first_name#',
							'#employee_last_name#',
							);

		$replace_arr = array(
							NULL,
							NULL,
							);

		$subject = str_replace( $search_arr, $replace_arr, $email_subject );
		Debug::Text('Subject: '. $subject, __FILE__, __LINE__, __METHOD__,10);

		$headers = array(
							'From'    => $from,
							'Subject' => $subject,
							'Bcc'	  => $bcc,
							'Reply-To' => $reply_to,
							'Return-Path' => $reply_to,
							'Errors-To' => $reply_to,
						 );

		$body = '<pre>'.str_replace( $search_arr, $replace_arr, $email_body ).'</pre>';
		Debug::Text('Body: '. $body, __FILE__, __LINE__, __METHOD__,10);

		$mail = new TTMail();
		$mail->setTo( $to );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody($body);

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == TRUE ) {
			TTLog::addEntry( $this->getId(), 500,  TTi18n::getText('Email Message to').': '. $to .' Bcc: '. $headers['Bcc'], NULL, $this->getTable() );
			return TRUE;
		}

		return TRUE; //Always return true
	}

	function postSave() {
		//Only email message notifications when they are not deleted and UNREAD still. Other it may email when a message is marked as read as well.
		//Don't email messages when they are being deleted.
		if ( $this->getDeleted() == FALSE AND $this->getStatus() == 10 ) {
			$this->emailMessage();
		}

		if ( $this->getStatus() == 20 ) {
			global $current_user;

			$this->removeCache( $current_user->getId() );
		}

		return TRUE;
	}

}
?>
