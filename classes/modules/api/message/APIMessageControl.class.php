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
 * @package API\Message
 */
class APIMessageControl extends APIFactory {
	protected $main_class = 'MessageControlFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get options for dropdown boxes.
	 * @param string $name Name of options to return, ie: 'columns', 'type', 'status'
	 * @param mixed $parent Parent name/ID of options to return if data is in hierarchical format. (ie: Province)
	 * @return array
	 */
	function getOptions( $name, $parent = NULL ) {
		if ( $name == 'user_columns' ) {
			$uf = TTnew('UserFactory');
			if	( $this->getPermissionObject()->Check('user', 'enabled') AND $this->getPermissionObject()->Check('user', 'view') ) {
				$retarr = $uf->getOptions('columns');
			} elseif  ( $this->getPermissionObject()->Check('user', 'enabled') AND $this->getPermissionObject()->Check('user', 'view_child') ) {
				$retarr = $uf->getOptions('user_child_secure_columns');
			} else {
				$retarr = $uf->getOptions('user_secure_columns');
			}
			return $retarr;
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default message_control data for creating new message_controles.
	 * @return array
	 */
	function getMessageControlDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text('Getting message_control default data...', __FILE__, __LINE__, __METHOD__, 10);

		$next_available_manual_id = MessageControlListFactory::getNextAvailableManualId( $company_obj->getId() );

		$data = array(
						'company_id' => $company_obj->getId(),
						'status_id' => 10,
						'manual_id' => $next_available_manual_id,
						'city' => $company_obj->getCity(),
						'country' => $company_obj->getCountry(),
						'province' => $company_obj->getProvince(),
						'work_phone' => $company_obj->getWorkPhone(),
						'fax_phone' => $company_obj->getFaxPhone(),
					);

		return $this->returnHandler( $data );
	}

	/**
	 * Get message_control data for one or more message_controles.
	 * @param array $data filter data
	 * @return array
	 */
	function getMessageControl( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('message', 'enabled')
				OR !( $this->getPermissionObject()->Check('message', 'view') OR $this->getPermissionObject()->Check('message', 'view_own') OR $this->getPermissionObject()->Check('message', 'view_child')	) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		//No need to check for permission_children, as the logged in user can only view their own messages anyways.
		$data['filter_data']['current_user_id'] = $this->getCurrentUserObject()->getId();

		$blf = TTnew( 'MessageControlListFactory' );
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'] );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}


	/**
	 * Get message data for one message or thread.
	 * @param array $data filter data
	 * @return array
	 */
	function getMessage( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('message', 'enabled')
				OR !( $this->getPermissionObject()->Check('message', 'view') OR $this->getPermissionObject()->Check('message', 'view_own') OR $this->getPermissionObject()->Check('message', 'view_child')	) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !isset($data['filter_data']['id']) ) {
			return $this->returnHandler( TRUE ); //No records returned.
		}

		$data['filter_data']['current_user_id'] = $this->getCurrentUserObject()->getId();

		$blf = TTnew( 'MessageControlListFactory' );
		$blf->getAPIMessageByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'] );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get message data attached to a single object.
	 * @param array $data filter data
	 * @return array
	 */
	function getEmbeddedMessage( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('message', 'enabled')
				OR !( $this->getPermissionObject()->Check('message', 'view') OR $this->getPermissionObject()->Check('message', 'view_own') OR $this->getPermissionObject()->Check('message', 'view_child')	) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( isset($data['filter_data']['object_type_id']) AND $data['filter_data']['object_type_id'] == 5 ) {
			Debug::Text('ERROR: Emails cant be embedded!', __FILE__, __LINE__, __METHOD__, 10);
			return $this->getPermissionObject()->PermissionDenied();
		}

		$type_to_api_map = $this->getOptions('type_to_api_map');
		if ( isset($data['filter_data']['object_type_id']) AND isset($type_to_api_map[$data['filter_data']['object_type_id']])
				AND isset($data['filter_data']['object_id']) ) {
			$tmp_apif = TTnew( $type_to_api_map[$data['filter_data']['object_type_id']] );
			$get_function = 'get'. str_replace('API', '', $type_to_api_map[$data['filter_data']['object_type_id']] );
			Debug::Text('API Class Name: '. $type_to_api_map[$data['filter_data']['object_type_id']] .' GET Function: '. $get_function, __FILE__, __LINE__, __METHOD__, 10);

			if ( method_exists( $tmp_apif, $get_function ) ) {
				$result = $this->stripReturnHandler( $tmp_apif->$get_function( array('filter_data' => array( 'id' => $data['filter_data']['object_id'] ), 'filter_items_per_page' => 1, 'filter_columns' => array('id' => TRUE) ) ) );
				if ( !( isset($result[0]) AND count($result[0]) > 0 ) ) {
					Debug::Text('ERROR: Permission denied, unable to find record for supplied object_id...', __FILE__, __LINE__, __METHOD__, 10);
					return $this->getPermissionObject()->PermissionDenied();
				}
			} else {
				Debug::Text('ERROR: Object Type ID is invalid...', __FILE__, __LINE__, __METHOD__, 10);
				return $this->returnHandler( FALSE ); //No records returned.
			}
		} else {
			Debug::Text('Object Type ID not defined...', __FILE__, __LINE__, __METHOD__, 10);
			//MyAccount -> Document doesn't send object_type_id
			//return $this->returnHandler( FALSE ); //No records returned.
		}


		$blf = TTnew( 'MessageControlListFactory' );
		$blf->getByCompanyIDAndUserIdAndObjectTypeAndObject( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), $data['filter_data']['object_type_id'], $data['filter_data']['object_id'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'] );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonMessageControlData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getMessageControl( $data, TRUE ) ) );
	}

	/**
	 * Validate message_control data for one or more message_controles.
	 * @param array $data message_control data
	 * @return array
	 */
	function validateMessageControl( $data ) {
		return $this->setMessageControl( $data, TRUE );
	}

	/**
	 * Set message_control data for one or more message_controls.
	 * @param array $data message_control data
	 * @return array
	 */
	function setMessageControl( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('message', 'enabled')
				OR !( $this->getPermissionObject()->Check('message', 'edit') OR $this->getPermissionObject()->Check('message', 'edit_own') OR $this->getPermissionObject()->Check('message', 'edit_child') OR $this->getPermissionObject()->Check('message', 'add') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' MessageControls', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'MessageControlListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Edit permission denied') );
				} else {
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('message', 'add'), TTi18n::gettext('Add permission denied') );
					
					//Security check, make sure any data passed as to_user_id is within the list of users available.
					if ( !isset($row['to_user_id']) OR is_array($row['to_user_id']) AND count($row['to_user_id']) == 0 ) {
						$row['to_user_id'] = FALSE;
					}

					if ( isset($row['object_type_id']) AND $row['object_type_id'] != 5 ) {
						Debug::Text('Adding message to request, determining our own to_user_ids...', __FILE__, __LINE__, __METHOD__, 10);
						//When replying to a request, find all users who have contributed messages to the request and make those the to_user_ids.
						$mslf = TTNew('MessageSenderListFactory');
						$mslf->getByCompanyIdAndObjectTypeAndObjectAndNotUser( $this->getCurrentCompanyObject()->getId(), (int)$row['object_type_id'], (int)$row['object_id'], $this->getCurrentUserObject()->getId() );
						if ( $mslf->getRecordCount() > 0 ) {
							$row['to_user_id'] = array();
							foreach( $mslf as $ms_obj ) {
								$row['to_user_id'][] = $ms_obj->getUser();
							}
							$row['to_user_id'] = array_unique( $row['to_user_id'] );
							Debug::Arr($row['to_user_id'], 'New Recipients: ', __FILE__, __LINE__, __METHOD__, 10);
						} else {
							$hlf = TTnew( 'HierarchyListFactory' );
							$rlf = TTnew('RequestListFactory');
							$rlf->getByIdAndCompanyId( (int)$row['object_id'], $this->getCurrentCompanyObject()->getId() );
							if ( $rlf->getRecordCount() == 1 ) {
								$object_type_id = $rlf->getHierarchyTypeId( (int)$rlf->getCurrent()->getType() );
							}
							$row['to_user_id'] = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), $object_type_id, TRUE, FALSE ); //Immediate parents only.
							Debug::Arr($row['to_user_id'], 'No one has replied yet, send to immediate superiors again...', __FILE__, __LINE__, __METHOD__, 10);
							unset($hlf, $rlf, $object_type_id);
						}
					} else {
						Debug::Text('Sending regular message, filter to_user_ids based on permissions...', __FILE__, __LINE__, __METHOD__, 10);
						$row['to_user_id'] = Misc::arrayColumn( $this->stripReturnHandler( $this->getUser( array( 'filter_data' => array( 'id' => (array)$row['to_user_id'] ), 'filter_columns' => array( 'id' => TRUE ) ), TRUE ) ), 'id' );
					}
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( $validate_only == TRUE ) {
					$lf->validate_only = TRUE;
				}

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->setObjectFromArray( $row );

					//Force current User ID as the FROM user.
					$lf->setFromUserId( $this->getCurrentUserObject()->getId() );

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $validate_only == TRUE ) {
							$save_result[$key] = TRUE;
						} else {
							$save_result[$key] = $lf->Save();
						}
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					if ( $primary_validator->isValid() == FALSE ) {
						$validator[$key] = $primary_validator->getErrorsArray();
					} else {
						$validator[$key] = $lf->Validator->getErrorsArray();
					}
				} elseif ( $validate_only == TRUE ) {
					$lf->FailTransaction();
				}


				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			if ( $validator_stats['valid_records'] > 0 AND $validator_stats['total_records'] == $validator_stats['valid_records'] ) {
				if ( $validator_stats['total_records'] == 1 ) {
					return $this->returnHandler( $save_result[$key] ); //Single valid record
				} else {
					return $this->returnHandler( TRUE, 'SUCCESS', TTi18n::getText('MULTIPLE RECORDS SAVED'), $save_result, $validator_stats ); //Multiple valid records
				}
			} else {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
			}
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Delete one or more message_controls.
	 * @param array $data message_control data
	 * @return array
	 */
	function deleteMessageControl( $data, $folder_id = FALSE) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( $folder_id == '' ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('message', 'enabled')
				OR !( $this->getPermissionObject()->Check('message', 'delete') OR $this->getPermissionObject()->Check('message', 'delete_own') OR $this->getPermissionObject()->Check('message', 'delete_child') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' MessageControls', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();

				if ( $folder_id == 10 ) { //Inbox
					$lf = TTnew( 'MessageRecipientListFactory' );
				} else { //Sent
					$lf = TTnew( 'MessageSenderListFactory' );
				}
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get message_control object, so we can only modify just changed data for specific records if needed.
					if ( $folder_id == 10 ) { //Inbox
						$lf->getByCompanyIdAndUserIdAndMessageSenderId( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), $id );
					} else { //Sent
						$lf->getByCompanyIdAndUserIdAndId( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), $id );
					}
					//$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );

					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('message', 'delete')
								OR ( $this->getPermissionObject()->Check('message', 'delete_own') ) ) { //Remove is_owner() checks, as the list factory filter it for us.
							Debug::Text('Record Exists, deleting record: ', $id, __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Delete permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
					}
				} else {
					$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10);
					$lf->setDeleted(TRUE);

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Record Deleted...', __FILE__, __LINE__, __METHOD__, 10);
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					if ( $primary_validator->isValid() == FALSE ) {
						$validator[$key] = $primary_validator->getErrorsArray();
					} else {
						$validator[$key] = $lf->Validator->getErrorsArray();
					}
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			if ( $validator_stats['valid_records'] > 0 AND $validator_stats['total_records'] == $validator_stats['valid_records'] ) {
				if ( $validator_stats['total_records'] == 1 ) {
					return $this->returnHandler( $save_result[$key] ); //Single valid record
				} else {
					return $this->returnHandler( TRUE, 'SUCCESS', TTi18n::getText('MULTIPLE RECORDS SAVED'), $save_result, $validator_stats ); //Multiple valid records
				}
			} else {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
			}
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Copy one or more message_controles.
	 * @param array $data message_control IDs
	 * @return array
	 */
	function copyMessageControl( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Text('Received data for: '. count($data) .' MessageControls', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$src_rows = $this->stripReturnHandler( $this->getMessageControl( array('filter_data' => array('id' => $data) ), TRUE ) );
		if ( is_array( $src_rows ) AND count($src_rows) > 0 ) {
			Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $src_rows as $key => $row ) {
				unset($src_rows[$key]['id'] ); //Clear fields that can't be copied
				//$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name
			}
			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setMessageControl( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Get limited (first/last name) user data for sending messages
	 * @param array $data filter data
	 * @param boolean $disable_paging disables paging and returns all records.
	 * @return array
	 */
	function getUser( $data, $disable_paging = FALSE ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( $this->getPermissionObject()->Check('message', 'send_to_any') ) {
			//Show all employees
			$data['filter_data']['permission_children_ids'] = NULL;
		} else {
			//Only allow sending to supervisors OR children.
			$hlf = TTnew( 'HierarchyListFactory' );

			//FIXME: For supervisors, we may need to include supervisors at the same level
			// Also how to handle cases where there are no To: recipients to select from.

			//Get Parents
			$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), array(1010, 1020, 1030, 1040, 1100), FALSE, FALSE );
			//Debug::Arr( $request_parent_level_user_ids, 'Request Parent Level Ids', __FILE__, __LINE__, __METHOD__, 10);

			//Get Children, in case the current user is a superior.
			$request_child_level_user_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), array(1010, 1020, 1030, 1040, 1100) );
			//Debug::Arr( $request_child_level_user_ids, 'Request Child Level Ids', __FILE__, __LINE__, __METHOD__, 10);

			$request_user_ids = array_merge( (array)$request_parent_level_user_ids, (array)$request_child_level_user_ids );
			//Debug::Arr( $request_user_ids, 'User Ids', __FILE__, __LINE__, __METHOD__, 10);

			$data['filter_data']['permission_children_ids'] = $request_user_ids;
			//Debug::Arr($data['filter_data']['permission_children_ids'], 'Permission Section: '. $permission_section .' Child IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		$data['filter_data']['status_id'] = 10; //Only include active employees.

		//Make sure the columns being asked for are available.
		$data['filter_columns'] = Misc::arrayIntersectByKey( array_merge( array('id'), array_keys( Misc::trimSortPrefix( $this->getOptions('user_columns') ) ) ), $data['filter_columns'] );

		if ( count($data['filter_columns']) == 0 ) { //Make sure we always default to some columns.
			Debug::Text('Overriding Filter Columns...', __FILE__, __LINE__, __METHOD__, 10);
			$data['filter_columns'] = array( 'id' => TRUE, 'first_name' => TRUE, 'last_name' => TRUE );
		}

		//Debug::Arr($this->getOptions('user_columns'), 'Final User Columns: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($data['filter_columns'], 'Final Filter Columns: ', __FILE__, __LINE__, __METHOD__, 10);

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $ulf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount() );

			$this->setPagerObject( $ulf );

			foreach( $ulf as $u_obj ) {
				$user_data = $u_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$retarr[] = $user_data;

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $ulf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			//Debug::Arr($retarr, 'User Data: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Check if there are unread messages for the current user.
	 * @return number of unread messages.
	 */
	function isNewMessage() {
		$mclf = new MessageControlListFactory();
		$unread_messages = $mclf->getNewMessagesByCompanyIdAndUserId( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
		Debug::text('UnRead Messages: '. $unread_messages, __FILE__, __LINE__, __METHOD__, 10);

		return $this->returnHandler( $unread_messages );
	}

	function markRecipientMessageAsRead( $mark_read_message_ids ) {
		return $this->returnHandler( MessageControlFactory::markRecipientMessageAsRead( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), $mark_read_message_ids ) );
	}

}
?>
