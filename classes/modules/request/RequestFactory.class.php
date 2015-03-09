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
 * @package Modules\Request
 */
class RequestFactory extends Factory {
	protected $table = 'request';
	protected $pk_sequence_name = 'request_id_seq'; //PK Sequence name

	var $user_date_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Missed Punch'),				//request_punch
										20 => TTi18n::gettext('Punch Adjustment'),			//request_punch_adjust
										30 => TTi18n::gettext('Absence (incl. Vacation)'),	//request_absence
										40 => TTi18n::gettext('Schedule Adjustment'),		//request_schedule
										100 => TTi18n::gettext('Other'),					//request_other
									);
				break;
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('INCOMPLETE'),
										20 => TTi18n::gettext('OPEN'),
										30 => TTi18n::gettext('PENDING'), //Used to be "Pending Authorizion"
										40 => TTi18n::gettext('AUTHORIZATION OPEN'),
										50 => TTi18n::gettext('AUTHORIZED'), //Used to be "Active"
										55 => TTi18n::gettext('DECLINED'), //Used to be "AUTHORIZATION DECLINED"
										60 => TTi18n::gettext('DISABLED')
									);
				break;
			case 'columns':
				$retval = array(

										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),
										'-1060-title' => TTi18n::gettext('Title'),
										'-1070-user_group' => TTi18n::gettext('Group'),
										'-1080-default_branch' => TTi18n::gettext('Branch'),
										'-1090-default_department' => TTi18n::gettext('Department'),

										'-1110-date_stamp' => TTi18n::gettext('Date'),
										'-1120-status' => TTi18n::gettext('Status'),
										'-1130-type' => TTi18n::gettext('Type'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('date_stamp', 'status', 'type'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'type',
								'date_stamp',
								'status'
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										//'user_date_id' => 'UserDateID',
										'user_id' => 'User',
										'date_stamp' => 'DateStamp',
										'pay_period_id' => 'PayPeriod',

										//'user_id' => FALSE,

										'first_name' => FALSE,
										'last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,

										'date_stamp' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'hierarchy_type_id' => 'HierarchyTypeId',
										'status_id' => 'Status',
										'status' => FALSE,
										'authorized' => 'Authorized',
										'authorization_level' => 'AuthorizationLevel',
										'message' => 'Message',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayPeriod() {
		if ( isset($this->data['pay_period_id']) ) {
			return (int)$this->data['pay_period_id'];
		}

		return FALSE;
	}
	function setPayPeriod($id = NULL) {
		$id = trim($id);

		if ( $id == NULL ) {
			$id = (int)PayPeriodListFactory::findPayPeriod( $this->getUser(), $this->getDateStamp() );
		}

		$pplf = TTnew( 'PayPeriodListFactory' );

		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later.
		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'pay_period',
														$pplf->getByID($id),
														TTi18n::gettext('Invalid Pay Period')
														) ) {
			$this->data['pay_period_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDateStamp( $raw = FALSE ) {
		if ( isset($this->data['date_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['date_stamp'];
			} else {
				return TTDate::strtotime( $this->data['date_stamp'] );
			}
		}

		return FALSE;
	}
	function setDateStamp($epoch) {
		$epoch = (int)$epoch;

		if	(	$this->Validator->isDate(		'date_stamp',
												$epoch,
												TTi18n::gettext('Incorrect date').' (a)')
			) {

			if	( $epoch > 0 ) {
				$this->data['date_stamp'] = $epoch;

				$this->setPayPeriod(); //Force pay period to be set as soon as the date is.
				return TRUE;
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date').' (b)');
			}
		}

		return FALSE;
	}

	//Convert hierarchy type_ids back to request type_ids.
	function getTypeIdFromHierarchyTypeId( $type_id ) {
		//Make sure we support an array of type_ids.
		if ( is_array($type_id) ) {
			foreach( $type_id as $request_type_id ) {
				$retval[] = ( $request_type_id >= 1000 AND $request_type_id < 2000 ) ? ( (int)$request_type_id - 1000 ) : (int)$request_type_id;
			}
		} else {
			$retval = ( $request_type_id >= 1000 AND $request_type_id < 2000 ) ? ( (int)$type_id - 1000 ) : (int)$type_id;
			Debug::text('Hierarchy Type ID: '. $type_id .' Request Type ID: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retval;
	}
	function getHierarchyTypeId( $type_id = NULL ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		//Make sure we support an array of type_ids.
		if ( is_array($type_id) ) {
			foreach( $type_id as $request_type_id ) {
				$retval[] = ( (int)$request_type_id + 1000 );
			}
		} else {
			$retval = ( (int)$type_id + 1000 );
			Debug::text('Request Type ID: '. $type_id .' Hierarchy Type ID: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retval;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return (int)$this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$value,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'status',
											$value,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getAuthorized() {
		if ( isset($this->data['authorized']) AND $this->data['authorized'] !== NULL) {
			return $this->fromBool( $this->data['authorized'] );
		}

		return NULL;
	}
	function setAuthorized($bool) {
		$this->data['authorized'] = $this->toBool($bool);

		return TRUE;
	}

	function getAuthorizationLevel() {
		if ( isset($this->data['authorization_level']) ) {
			return $this->data['authorization_level'];
		}

		return FALSE;
	}
	function setAuthorizationLevel($value) {
		$value = (int)trim( $value );

		if ( $value < 0 ) {
			$value = 0;
		}

		if ( $this->Validator->isNumeric(	'authorization_level',
											$value,
											TTi18n::gettext('Incorrect authorization level') ) ) {

			$this->data['authorization_level'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getMessage() {
		if ( isset($this->tmp_data['message']) ) {
			return $this->tmp_data['message'];
		}

		return FALSE;
	}
	function setMessage($text) {
		$text = trim($text);

		//Flex interface validates the message too soon, make it skip a 0 length message when only validating.
		if ( $this->validate_only == TRUE AND $text == '' ) {
			$minimum_length = 0;
		} else {
			$minimum_length = 5;
		}

		if	(	$this->Validator->isLength(		'message',
												$text,
												TTi18n::gettext('Invalid message length'),
												$minimum_length,
												1024) ) {

			$this->tmp_data['message'] = htmlspecialchars( $text );

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if (	$this->isNew() == TRUE
				AND $this->Validator->hasError('message') == FALSE
				AND $this->getMessage() == FALSE
				AND $this->validate_only == FALSE ) {
			$this->Validator->isTRUE(		'message',
											FALSE,
											TTi18n::gettext('Invalid message length') );
		}

		if ( $this->getDateStamp() == FALSE
			AND $this->Validator->hasError('date_stamp') == FALSE ) {
			$this->Validator->isTRUE(		'date_stamp',
											FALSE,
											TTi18n::gettext('Incorrect Date').' (c)' );
		}

		if ( !is_object( $this->getUserObject() ) ) {
			$this->Validator->isTRUE(		'user_id',
											FALSE,
											TTi18n::gettext('Invalid Employee') );
		}

		//Check to make sure this user has superiors to send a request too, otherwise we can't save the request.
		if ( is_object( $this->getUserObject() ) ) {
			$hlf = TTnew( 'HierarchyListFactory' );
			$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUser(), $this->getHierarchyTypeId(), TRUE, FALSE ); //Request - Immediate parents only.
			Debug::Arr($request_parent_level_user_ids, 'Check for Superiors: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( !is_array($request_parent_level_user_ids) OR count($request_parent_level_user_ids) == 0 ) {
				$this->Validator->isTRUE(		'message',
												FALSE,
												TTi18n::gettext('No supervisors are assigned to you at this time, please try again later') );
			}
		}

		if ( $this->getDeleted() == TRUE AND in_array( $this->getStatus(), array(50, 55) ) ) {
			$this->Validator->isTRUE(		'status_id',
											FALSE,
											TTi18n::gettext('Unable to delete requests after they have been authorized/declined') );
		}

		return TRUE;
	}

	function preSave() {
		//If this is a new request, find the current authorization level to assign to it.
		if ( $this->isNew() == TRUE ) {
			if ( $this->getStatus() == FALSE ) {
				$this->setStatus( 30 ); //Pending Auth.
			}

			if ( is_object( $this->getUserObject() ) ) {
				$hlf = TTnew( 'HierarchyListFactory' );
				$hierarchy_arr = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID(), $this->getHierarchyTypeId(), FALSE);
			}

			$hierarchy_highest_level = 99;
			if ( isset($hierarchy_arr) AND is_array( $hierarchy_arr ) ) {
				Debug::Arr($hierarchy_arr, ' Hierarchy Array: ', __FILE__, __LINE__, __METHOD__, 10);
				$hierarchy_arr = array_keys( $hierarchy_arr );
				$hierarchy_highest_level = end( $hierarchy_arr ) ;
				Debug::Text(' Setting hierarchy level to: '. $hierarchy_highest_level, __FILE__, __LINE__, __METHOD__, 10);
			}
			$this->setAuthorizationLevel( $hierarchy_highest_level );
		}

		if ( $this->getAuthorized() == TRUE ) {
			$this->setAuthorizationLevel( 0 );
		}

		return TRUE;
	}

	function postSave() {
		//Save message here after we have the request_id.
		if ( $this->getMessage() !== FALSE ) {
			$mcf = TTnew( 'MessageControlFactory' );
			$mcf->StartTransaction();

			$hlf = TTnew( 'HierarchyListFactory' );
			$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUser(), $this->getHierarchyTypeId(), TRUE, FALSE ); //Request - Immediate parents only.
			Debug::Arr($request_parent_level_user_ids, 'Sending message to current direct Superiors: ', __FILE__, __LINE__, __METHOD__, 10);

			$mcf = TTnew( 'MessageControlFactory' );
			$mcf->setFromUserId( $this->getUser() );
			$mcf->setToUserId( $request_parent_level_user_ids );
			$mcf->setObjectType( 50 ); //Messages don't break out request types like hierarchies do.
			$mcf->setObject( $this->getID() );
			$mcf->setParent( 0 );
			$mcf->setSubject( Option::getByKey( $this->getType(), $this->getOptions('type') ) .' '. TTi18n::gettext('request from') .': '. $this->getUserObject()->getFullName(TRUE) );
			$mcf->setBody( $this->getMessage() );

			if ( $mcf->isValid() ) {
				$mcf->Save();

				$mcf->CommitTransaction();
			} else {
				$mcf->FailTransaction();
			}
		}

		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('Delete authorization history for this request...'. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			$alf = TTnew( 'AuthorizationListFactory' );
			$alf->getByObjectTypeAndObjectId( $this->getHierarchyTypeId(), $this->getId() );
			foreach( $alf as $authorization_obj ) {
				Debug::Text('Deleting authorization ID: '. $authorization_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
				$authorization_obj->setDeleted(TRUE);
				$authorization_obj->Save();
			}
		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			/*
			if ( isset($data['user_id']) AND $data['user_id'] != ''
					AND isset($data['date_stamp']) AND $data['date_stamp'] != '' ) {
				Debug::text('Setting User Date ID based on User ID:'. $data['user_id'] .' Date Stamp: '. $data['date_stamp'], __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDate( $data['user_id'], TTDate::parseDateTime( $data['date_stamp'] ) );
			} elseif ( isset( $data['user_date_id'] ) AND $data['user_date_id'] > 0 ) {
				Debug::text(' Setting UserDateID: '. $data['user_date_id'], __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDateID( $data['user_date_id'] );
			} else {
				Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__, 10);
			}
			*/

			if ( isset($data['status_id']) AND $data['status_id'] == '' ) {
				unset($data['status_id']);
				$this->setStatus( 30 ); //Pending authorization
			}
			if ( isset($data['user_date_id']) AND $data['user_date_id'] == '' ) {
				unset($data['user_date_id']);
			}

			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {
					$function = 'set'.$function;
					switch( $key ) {
						case 'date_stamp':
							$this->setDateStamp( TTDate::parseDateTime( $data['date_stamp'] ) );
							break;
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
						case 'user_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'message': //Message is attached in the message factory, so we can't return it here.
							break;
						case 'status':
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Request - Type').': '. Option::getByKey( $this->getType(), $this->getOptions('type') ), NULL, $this->getTable(), $this );
	}
}
?>
