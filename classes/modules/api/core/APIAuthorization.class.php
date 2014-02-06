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
 * $Revision: 2196 $
 * $Id: APIAuthorization.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\Core
 */
class APIAuthorization extends APIFactory {
	protected $main_class = 'AuthorizationFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get default authorization data for creating new authorizations.
	 * @return array
	 */
	function getAuthorizationDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text('Getting authorization default data...', __FILE__, __LINE__, __METHOD__,10);

		$data = array(
					);

		return $this->returnHandler( $data );
	}

	/**
	 * Get authorization data for one or more authorizations.
	 * @param array $data filter data
	 * @return array
	 */
	function getAuthorization( $data = NULL, $disable_paging = FALSE ) {
		Debug::Arr($data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Keep in mind administrators doing authorization often have access to ALL requests, or ALL users, so permission_children won't come into play.
		//Users should be able to see authorizations for their own requests.
		if ( isset($data['filter_data']['object_type_id']) AND in_array( $data['filter_data']['object_type_id'], array(1010,1020,1030,1040,1100) ) ) { //Requests
			Debug::Text('Request object_type_id: '. $data['filter_data']['object_type_id'], __FILE__, __LINE__, __METHOD__, 10);

			if ( !$this->getPermissionObject()->Check('request','enabled')
					OR !( $this->getPermissionObject()->Check('request','view') OR $this->getPermissionObject()->Check('request','view_own') OR $this->getPermissionObject()->Check('request','view_child')  ) ) {
				return  $this->getPermissionObject()->PermissionDenied();
			}

			$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'request', 'view' );
		} elseif ( isset($data['filter_data']['object_type_id']) AND in_array( $data['filter_data']['object_type_id'], array(90) ) ) { //Timesheets
			Debug::Text('TimeSheet object_type_id: '. $data['filter_data']['object_type_id'], __FILE__, __LINE__, __METHOD__, 10);

			if ( !$this->getPermissionObject()->Check('punch','enabled')
					OR !( $this->getPermissionObject()->Check('punch','view') OR $this->getPermissionObject()->Check('punch','view_own') OR $this->getPermissionObject()->Check('punch','view_child')  ) ) {
				return  $this->getPermissionObject()->PermissionDenied();
			}

			$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view' );
		} elseif ( isset($data['filter_data']['object_type_id']) AND in_array( $data['filter_data']['object_type_id'], array(200) ) ){ // Expense
		    Debug::Text('Expense object_type_id: '. $data['filter_data']['object_type_id'], __FILE__, __LINE__, __METHOD__, 10);

			if ( !$this->getPermissionObject()->Check('user_expense','enabled')
					OR !( $this->getPermissionObject()->Check('user_expense','view') OR $this->getPermissionObject()->Check('user_expense','view_own') OR $this->getPermissionObject()->Check('user_expense','view_child')  ) ) {
				return  $this->getPermissionObject()->PermissionDenied();
			}

			$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user_expense', 'view' );

		} else {
			//Invalid or not specified object_type_id
			Debug::Text('No valid object_type_id specified...', __FILE__, __LINE__, __METHOD__, 10);
			return $this->getPermissionObject()->PermissionDenied();
		}
		//Debug::Arr($data['filter_data']['permission_children_ids'],  'Permission Children: ', __FILE__, __LINE__, __METHOD__, 10);

		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		$blf = TTnew( 'AuthorizationListFactory' );
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids']  );

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
	function getCommonAuthorizationData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getAuthorization( $data, TRUE ) ) );
	}

	/**
	 * Validate authorization data for one or more authorizations.
	 * @param array $data authorization data
	 * @return array
	 */
	function validateAuthorization( $data ) {
		return $this->setAuthorization( $data, TRUE );
	}

	/**
	 * Set authorization data for one or more authorizations.
	 * @param array $data authorization data
	 * @return array
	 */
	function setAuthorization( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !( $this->getPermissionObject()->Check('request','authorize') OR $this->getPermissionObject()->Check('punch','authorize') OR $this->getPermissionObject()->Check('user_expense','authorize') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' Authorizations', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'AuthorizationListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					//Modifying existing object.
					//Get authorization object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							  $validate_only == TRUE
							  OR
								(
								$this->getPermissionObject()->Check('request','authorize')
								OR
								$this->getPermissionObject()->Check('punch','authorize')
                                OR
                                $this->getPermissionObject()->Check('user_expense','authorize')
								)
							) {

							Debug::Text('Row Exists, getting current data: ', $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Edit permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, record does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					//$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('authorization','add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);
					$lf->setObjectFromArray( $row );

					//Set the current user so we know who is doing the authorization.
					$lf->setCurrentUser( $this->getCurrentUserObject()->getId() );

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
	 * Delete one or more authorizations.
	 * @param array $data authorization data
	 * @return array
	 */
	function deleteAuthorization( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !( $this->getPermissionObject()->Check('request','authorize') OR $this->getPermissionObject()->Check('punch','authorize') OR $this->getPermissionObject()->Check('user_expense','authorize') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' Authorizations', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'AuthorizationListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get authorization object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							$this->getPermissionObject()->Check('request','authorize')
							OR
							$this->getPermissionObject()->Check('punch','authorize')
                            OR
                            $this->getPermissionObject()->Check('user_expense','authorize')
							) {
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
}
?>
