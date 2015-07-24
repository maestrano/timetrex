<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
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


/**
 * @package Modules\Message
 */
class MessageControlFactory extends Factory {
	protected $table = 'message_control';
	protected $pk_sequence_name = 'message_control_id_seq'; //PK Sequence name

	protected $obj_handler = NULL;
	protected $tmp_data = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('UNREAD'),
										20 => TTi18n::gettext('READ')
									);
				break;
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
			case 'type_to_api_map': //Maps the object_type_id to an API class that we can use to determine if the user has access to view the specific records or not.
				$retval = array(
										//5 => 'email', //Email is never linked to another class
										//10 => 'default_schedule',
										//20 => 'schedule_amendment',
										//30 => 'shift_amendment',
										40 => 'APIAuthorization',
										50 => 'APIRequest',
										60 => 'APIJob',
										70 => 'APIJobItem',
										80 => 'APIClient',
										90 => 'APITimeSheet',
										100 => 'APIUser' //For notes assigned to users?
									);
				break;
			case 'object_type':
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
			case 'priority':
				$retval = array(
										10 => TTi18n::gettext('LOW'),
										50 => TTi18n::gettext('NORMAL'),
										100 => TTi18n::gettext('HIGH'),
										110 => TTi18n::gettext('URGENT')
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-from_first_name' => TTi18n::gettext('From: First Name'),
										'-1020-from_middle_name' => TTi18n::gettext('From: Middle Name'),
										'-1030-from_last_name' => TTi18n::gettext('From: Last Name'),

										'-1110-to_first_name' => TTi18n::gettext('To: First Name'),
										'-1120-to_middle_name' => TTi18n::gettext('To: Middle Name'),
										'-1130-to_last_name' => TTi18n::gettext('To: Last Name'),

										'-1200-subject' => TTi18n::gettext('Subject'),
										'-1210-object_type' => TTi18n::gettext('Type'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										//'-2020-updated_by' => TTi18n::gettext('Updated By'),
										//'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'from_first_name',
								'from_last_name',
								'to_first_name',
								'to_last_name',
								'subject',
								'object_type',
								'created_date',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array();
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array();
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',

										'from_user_id' => 'FromUserID',
										'from_first_name' => FALSE,
										'from_middle_name' => FALSE,
										'from_last_name' => FALSE,

										'to_user_id' => 'ToUserID',
										'to_first_name' => FALSE,
										'to_middle_name' => FALSE,
										'to_last_name' => FALSE,

										'status_id' => FALSE,
										'object_type_id' => 'ObjectType',
										'object_type' => FALSE,
										'object_id' => 'Object',
										'parent_id' => 'Parent',
										'priority_id' => 'Priority',
										'subject' => 'Subject',
										'body' => 'Body',
										'require_ack' => 'RequireAck',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getFromUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getFromUserID(), 'from_user_obj' );
	}

	function getFromUserId() {
		if ( isset($this->tmp_data['from_user_id']) ) {
			return $this->tmp_data['from_user_id'];
		}

		return FALSE;
	}
	function setFromUserId( $id ) {
		if ( $id != '' ) {
			$this->tmp_data['from_user_id'] = $id;
			return TRUE;
		}
		return FALSE;
	}

	function getToUserId() {
		if ( isset($this->tmp_data['to_user_id']) ) {
			return $this->tmp_data['to_user_id'];
		}

		return FALSE;
	}
	function setToUserId( $ids ) {
		if ( !is_array($ids) ) {
			$ids = array($ids);
		}

		$ids = array_unique($ids);
		if ( count($ids) > 0 ) {
			foreach($ids as $id ) {
				if ( $id > 0 ) {
					$this->tmp_data['to_user_id'][] = $id;
				}
			}

			return TRUE;
		}
		return FALSE;
	}

	//Expose message_sender_id for migration purposes.
	function getMessageSenderId() {
		if ( isset($this->tmp_data['message_sender_id']) ) {
			return $this->tmp_data['message_sender_id'];
		}

		return FALSE;
	}
	function setMessageSenderId( $id ) {
		if ( $id != '' ) {
			$this->tmp_data['message_sender_id'] = $id;
			return TRUE;
		}
		return FALSE;

	}

	function isAck() {
		if ( $this->getRequireAck() == TRUE AND $this->getColumn('ack_date') == '' ) {
			return FALSE;
		}

		return TRUE;
	}

	//Parent ID is the parent message_sender_id.
	function getParent() {
		if ( isset($this->tmp_data['parent_id']) ) {
			return $this->tmp_data['parent_id'];
		}

		return FALSE;
	}
	function setParent($id) {
		$id = trim($id);

		if ( empty($id) ) {
			$id = 0;
		}

		if ( $id == 0
				OR $this->Validator->isNumeric(				'parent',
															$id,
															TTi18n::gettext('Parent is invalid')
															) ) {
			$this->tmp_data['parent_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//These functions are out of the ordinary, as the getStatus gets the status of a message based on a SQL join to the recipient table.
	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
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
			return (int)$this->data['object_type_id'];
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
			return (int)$this->data['object_id'];
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
			return (int)$this->data['priority_id'];
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

	function getSubject() {
		if ( isset($this->data['subject']) ) {
			return $this->data['subject'];
		}

		return FALSE;
	}
	function setSubject($text) {
		$text = trim($text);

		if	(	strlen($text) == 0
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

		//Flex interface validates the message too soon, make it skip a 0 length message when only validating.
		if ( $this->validate_only == TRUE AND $text == '' ) {
			$minimum_length = 0;
		} else {
			$minimum_length = 5;
		}

		if	(	$this->Validator->isLength(		'body',
												$text,
												TTi18n::gettext('Invalid Body length'),
												$minimum_length,
												(1024 * 10) ) ) {

			$this->data['body'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getRequireAck() {
		return $this->fromBool( $this->data['require_ack'] );
	}
	function setRequireAck($bool) {
		$this->data['require_ack'] = $this->toBool($bool);

		return TRUE;
	}

	function getEnableEmailMessage() {
		if ( isset($this->email_message) ) {
			return $this->email_message;
		}

		return TRUE;
	}
	function setEnableEmailMessage($bool) {
		$this->email_message = $bool;

		return TRUE;
	}

	function Validate() {
		//Only validate from/to user if there is a subject and body set, otherwise validation will fail on a new object with no data all the time.
		if ( $this->getSubject() != '' AND $this->getBody() != '' ) {
			if ( $this->Validator->hasError( 'from' ) == FALSE AND $this->getFromUserId() == '' ) {
				$this->Validator->isTrue(	'from',
											FALSE,
											TTi18n::gettext('Message sender is invalid') );

			}

			//Messages attached to objects do not require a recipient.
			if ( $this->Validator->hasError( 'to' ) == FALSE AND $this->getObjectType() == 5 AND ( (int)$this->getToUserId() == 0 OR ( is_array( $this->getToUserId() ) AND count( $this->getToUserId() ) == 0 ) ) ) {
				$this->Validator->isTrue(	'to',
											FALSE,
											TTi18n::gettext('Message recipient is invalid') );
			}
		}

		if ( $this->validate_only == FALSE ) {
			if ( $this->getObjectType() == '' ) {
					$this->Validator->isTrue(	'object_type_id',
												FALSE,
												TTi18n::gettext('Object type is invalid') );
			}

			if ( $this->getObject() == '' ) {
					$this->Validator->isTrue(	'object_id',
												FALSE,
												TTi18n::gettext('Object is invalid') );
			}
		}

		//If deleted is TRUE, we need to make sure all sender/recipient records are also deleted.
		return TRUE;
	}

	static function markRecipientMessageAsRead( $company_id, $user_id, $ids ) {
		if ( $company_id == '' OR $user_id == '' OR $ids == '' OR count($ids) == 0 ) {
			return FALSE;
		}

		Debug::Arr($ids, 'Message Recipeint Ids: ', __FILE__, __LINE__, __METHOD__, 10);

		$mrlf = TTnew( 'MessageRecipientListFactory' );
		$mrlf->getByCompanyIdAndUserIdAndMessageSenderIdAndStatus( $company_id, $user_id, $ids, 10 );
		if ( $mrlf->getRecordCount() > 0 ) {
			foreach( $mrlf as $mr_obj ) {
				$mr_obj->setStatus( 20 ); //Read
				$mr_obj->Save();
			}
		}

		return TRUE;
	}

	function getEmailMessageAddresses() {
		//Remove the From User from any recipicient list so we don't send emails back to ourselves.
		$user_ids = array_diff( $this->getToUserId(), array( $this->getFromUserId() ) );
		if ( isset($user_ids) AND is_array($user_ids) AND count($user_ids) > 0 ) {
			//Get user preferences and determine if they accept email notifications.
			Debug::Arr($user_ids, 'Recipient User Ids: ', __FILE__, __LINE__, __METHOD__, 10);

			$uplf = TTnew( 'UserPreferenceListFactory' );
			$uplf->getByUserId( $user_ids );
			if ( $uplf->getRecordCount() > 0 ) {
				foreach( $uplf as $up_obj ) {
					if ( $up_obj->getEnableEmailNotificationMessage() == TRUE AND is_object( $up_obj->getUserObject() ) AND $up_obj->getUserObject()->getStatus() == 10 ) {
						if ( $up_obj->getUserObject()->getWorkEmail() != '' AND $up_obj->getUserObject()->getWorkEmailIsValid() == TRUE ) {
							$retarr[] = $up_obj->getUserObject()->getWorkEmail();
						}

						if ( $up_obj->getEnableEmailNotificationHome() AND is_object( $up_obj->getUserObject() ) AND $up_obj->getUserObject()->getHomeEmail() != '' AND $up_obj->getUserObject()->getHomeEmailIsValid() == TRUE ) {
							$retarr[] = $up_obj->getUserObject()->getHomeEmail();
						}
					}
				}

				if ( isset($retarr) ) {
					Debug::Arr($retarr, 'Recipient Email Addresses: ', __FILE__, __LINE__, __METHOD__, 10);
					return array_unique($retarr);
				}
			}
		}

		return FALSE;
	}

	function emailMessage() {
		Debug::Text('emailMessage: ', __FILE__, __LINE__, __METHOD__, 10);

		$email_to_arr = $this->getEmailMessageAddresses();
		if ( $email_to_arr == FALSE ) {
			return FALSE;
		}

		//Get from User Object so we can include more information in the message.
		if ( is_object( $this->getFromUserObject() ) ) {
			$u_obj = $this->getFromUserObject();
		} else {
			Debug::Text('From object does not exist: '. $this->getFromUserID(), __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$from = $reply_to = '"'. APPLICATION_NAME .' - '. TTi18n::gettext('Message') .'"<DoNotReply@'. Misc::getEmailDomain() .'>';

		global $current_user, $config_vars;
		if ( is_object($current_user) AND $current_user->getWorkEmail() != '' ) {
			$reply_to = $current_user->getWorkEmail();
		}
		Debug::Text('To: '. implode(',', $email_to_arr), __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text('From: '. $from .' Reply-To: '. $reply_to, __FILE__, __LINE__, __METHOD__, 10);

		//Define subject/body variables here.
		$search_arr = array(
							'#from_employee_first_name#',
							'#from_employee_last_name#',
							'#from_employee_default_branch#',
							'#from_employee_default_department#',
							'#from_employee_group#',
							'#from_employee_title#',
							'#company_name#',
							'#link#',
							);

		$replace_arr = array(
							$u_obj->getFirstName(),
							$u_obj->getLastName(),
							( is_object( $u_obj->getDefaultBranchObject() ) ) ? $u_obj->getDefaultBranchObject()->getName() : NULL,
							( is_object( $u_obj->getDefaultDepartmentObject() ) ) ? $u_obj->getDefaultDepartmentObject()->getName() : NULL,
							( is_object( $u_obj->getGroupObject() ) ) ? $u_obj->getGroupObject()->getName() : NULL,
							( is_object( $u_obj->getTitleObject() ) ) ? $u_obj->getTitleObject()->getName() : NULL,
							( is_object( $u_obj->getCompanyObject() ) ) ? $u_obj->getCompanyObject()->getName() : NULL,
							NULL,
							);

		$email_subject = TTi18n::gettext('New message waiting in').' '. APPLICATION_NAME;

		$email_body	 = TTi18n::gettext('*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*')."\n\n";
		$email_body .= TTi18n::gettext('You have a new message waiting for you in').' '. APPLICATION_NAME."\n";
		$email_body .= ( $this->getSubject() != '' ) ? TTi18n::gettext('Subject').': '. $this->getSubject()."\n" : NULL;
		$email_body .= TTi18n::gettext('From').': #from_employee_first_name# #from_employee_last_name#'."\n";
		$email_body .= ( $replace_arr[2] != '' ) ? TTi18n::gettext('Default Branch').': #from_employee_default_branch#'."\n" : NULL;
		$email_body .= ( $replace_arr[3] != '' ) ? TTi18n::gettext('Default Department').': #from_employee_default_department#'."\n" : NULL;
		$email_body .= ( $replace_arr[4] != '' ) ? TTi18n::gettext('Group').': #from_employee_group#'."\n" : NULL;
		$email_body .= ( $replace_arr[5] != '' ) ? TTi18n::gettext('Title').': #from_employee_title#'."\n" : NULL;

		$email_body .= TTi18n::gettext('Link').': <a href="'. Misc::getURLProtocol() .'://'. Misc::getHostName().Environment::getDefaultInterfaceBaseURL().'">'.APPLICATION_NAME.' '. TTi18n::gettext('Login') .'</a>';

		$email_body .= ( $replace_arr[6] != '' ) ? "\n\n\n".TTi18n::gettext('Company').': #company_name#'."\n" : NULL; //Always put at the end

		$subject = str_replace( $search_arr, $replace_arr, $email_subject );
		Debug::Text('Subject: '. $subject, __FILE__, __LINE__, __METHOD__, 10);

		$headers = array(
							'From'		=> $from,
							'Subject'	=> $subject,
							'Reply-To'	=> $reply_to,
							'Return-Path' => $reply_to,
							'Errors-To' => $reply_to,
						);

		$body = '<html><body><pre>'.str_replace( $search_arr, $replace_arr, $email_body ).'</pre></body></html>';
		Debug::Text('Body: '. $body, __FILE__, __LINE__, __METHOD__, 10);

		$mail = new TTMail();
		$mail->setTo( $email_to_arr );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody($body);

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == TRUE ) {
			TTLog::addEntry( $this->getId(), 500, TTi18n::getText('Email Message to').': '. implode(', ', $email_to_arr), NULL, $this->getTable() );
			return TRUE;
		}

		return TRUE; //Always return true
	}

	function preSave() {
		//Check to make sure the 'From' user_id doesn't appear in the 'To' user list as well.
		$from_user_id_key = array_search( $this->getFromUserId(), (array)$this->getToUserId() );
		if ( $from_user_id_key !== FALSE ) {
			$to_user_ids = $this->getToUserId();
			unset($to_user_ids[$from_user_id_key]);
			$this->setToUserId( $to_user_ids );

			Debug::text('From user is assigned as a To user as well, removing...'. (int)$from_user_id_key, __FILE__, __LINE__, __METHOD__, 9);
		}

		Debug::Arr($this->getFromUserId(), 'From: ', __FILE__, __LINE__, __METHOD__, 9);
		Debug::Arr($this->getToUserId(), 'Sending To: ', __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}

	function postSave() {
		//Save Sender/Recipient records for this message.
		if ( $this->getDeleted() == FALSE ) {
			$to_user_ids = $this->getToUserId();
			if ( $to_user_ids != FALSE ) {
				foreach( $to_user_ids as $to_user_id ) {
					//We need one message_sender record for every recipient record, otherwise when a message is sent to
					//multiple recipients, and one of them replies, the parent_id will point to original sender record which
					//then maps to every single recipient, making it hard to show messages just between the specific users.
					//
					//On the other hand, having multiple sender records, one for each recipient makes it hard to show
					//just the necessary messages on the embedded message list, as it wants to show duplicates messages for
					//each recipient.
					$msf = TTnew( 'MessageSenderFactory' );
					$msf->setUser( $this->getFromUserId() );
					Debug::Text('Parent ID: '. $this->getParent(), __FILE__, __LINE__, __METHOD__, 10);

					//Only specify parent if the object type is message.
					if ( $this->getObjectType() == 5 ) {
						$msf->setParent( $this->getParent() );
					} else {
						$msf->setParent( 0 );
					}
					$msf->setMessageControl( $this->getId() );
					$msf->setCreatedBy( $this->getCreatedBy() );
					$msf->setCreatedDate( $this->getCreatedDate() );
					$msf->setUpdatedBy( $this->getUpdatedBy() );
					$msf->setUpdatedDate( $this->getUpdatedDate() );
					if ( $msf->isValid() ) {
						$message_sender_id = $msf->Save();
						$this->setMessageSenderId( $message_sender_id ); //Used mainly for migration purposes, so we can obtain this from outside the class.
						Debug::Text('Message Sender ID: '. $message_sender_id, __FILE__, __LINE__, __METHOD__, 10);

						if ( $message_sender_id != FALSE ) {
							$mrf = TTnew( 'MessageRecipientFactory' );
							$mrf->setUser( $to_user_id );
							$mrf->setMessageSender( $message_sender_id );
							if ( isset($this->migration_status) ) {
								$mrf->setStatus( $this->migration_status );
							}
							$mrf->setCreatedBy( $this->getCreatedBy() );
							$mrf->setCreatedDate( $this->getCreatedDate() );
							$mrf->setUpdatedBy( $this->getUpdatedBy() );
							$mrf->setUpdatedDate( $this->getUpdatedDate() );
							if ( $mrf->isValid() ) {
								$mrf->Save();
							}
						}
					}
				}

				//Send email to all recipients.
				if ( $this->getEnableEmailMessage() == TRUE ) {
					$this->emailMessage();
				}
			} //else {
				//If no recipients are specified (user replying to their own request before a superior does, or a user sending a request without a hierarchy)
				//Make sure we have at least one sender record.
				//Either that or make sure we always reply to ALL senders and recipients in the thread.
			//}
		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'to_user_id':
						case 'to_first_name':
						case 'to_middle_name':
						case 'to_last_name':
						case 'from_user_id':
						case 'from_first_name':
						case 'from_middle_name':
						case 'from_last_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status_id':
							$data[$variable] = $this->getStatus(); //Make sure this is returned as an INT.
							break;
						case 'object_type':
							$data[$variable] = Option::getByKey( $this->getObjectType(), $this->getOptions( $variable ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

}
?>
