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
 * $Id: APIUser.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\Users
 */
class APIUserGenericData extends APIFactory {
	protected $main_class = 'UserGenericDataFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get user data for one or more users.
	 * @param array $data filter data
	 * @return array
	 */
	function getUserGenericData( $data = NULL ) {

		$data = $this->initializeFilterAndPager( $data );

		//Only allow getting generic data for currently logged in user unless user_id = 0, then get company wide data.
		//$data['filter_data']['user_id'] = $this->getCurrentUserObject()->getId();
		if ( !isset($data['filter_data']['user_id']) OR ( isset($data['filter_data']['user_id']) AND (int)$data['filter_data']['user_id'] !== 0 ) ) {
			Debug::Text('Forcing User ID to current user: '. $this->getCurrentUserObject()->getId(), __FILE__, __LINE__, __METHOD__, 10);
			$data['filter_data']['user_id'] = $this->getCurrentUserObject()->getId();
		} else {
			Debug::Text('Company wide data...', __FILE__, __LINE__, __METHOD__, 10);
			$data['filter_data']['user_id'] = 0; //Company wide data.
		}

		Debug::Arr($data, 'Getting User Generic Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$ugdlf = TTnew( 'UserGenericDataListFactory' );
		$ugdlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $ugdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $ugdlf->getRecordCount() > 0 ) {
			$this->setPagerObject( $ugdlf );

			foreach( $ugdlf as $ugd_obj ) {
				$retarr[] = $ugd_obj->getObjectAsArray( $data['filter_columns'] );
			}

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE );
	}

	/**
	 * Set user data for one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function setUserGenericData( $data ) {
		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' Users', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			foreach( $data as $key => $row ) {
				$row['company_id'] = $this->getCurrentUserObject()->getCompany();
				if ( !isset($row['user_id']) OR ( isset($row['user_id']) AND (int)$row['user_id'] !== 0 ) ) {
					Debug::Text('Forcing User ID to current user: '. $this->getCurrentUserObject()->getId(), __FILE__, __LINE__, __METHOD__, 10);
					$row['user_id'] = $this->getCurrentUserObject()->getId();
				} else {
					Debug::Text('Company wide data...', __FILE__, __LINE__, __METHOD__, 10);
					$row['user_id'] = 0; //Company wide data.
				}

				$primary_validator = new Validator();
				$lf = TTnew( 'UserGenericDataListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) ) {
					//Modifying existing object.
					//Get object, so we can only modify just changed data for specific records if needed.
					//$lf->getByUserIdAndId( $row['user_id'], $row['id'] );
					$lf->getByCompanyIdAndUserIdAndId( $row['company_id'], $row['user_id'], $row['id'] );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						$row = array_merge( $lf->getCurrent()->getObjectAsArray(), $row );
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, employee does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					//$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('user','add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'User Generic Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to save User Data...', __FILE__, __LINE__, __METHOD__, 10);
					$lf->setObjectFromArray( $row );

					//Force Company ID to current company.
					$lf->setCompany( $this->getCurrentCompanyObject()->getId() );

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving User Data...', __FILE__, __LINE__, __METHOD__, 10);
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('User Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					if ( $primary_validator->isValid() == FALSE ) {
						$validator[$key] = $primary_validator->getErrorsArray();
					} else {
						$validator[$key] = $lf->Validator->getErrorsArray();
					}
				}

				$lf->CommitTransaction();
			}

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
	 * Delete one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function deleteUserGenericData( $data ) {
		Debug::Arr($data, 'DataA: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Text('Received data for: '. count($data) .' Users', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserGenericDataListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get user object, so we can only modify just changed data for specific records if needed.
					$lf->getByUserIdAndId( $this->getCurrentUserObject()->getId(), $id );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists
						Debug::Text('User Generic Data Exists, deleting record: ', $id, __FILE__, __LINE__, __METHOD__, 10);
						$lf = $lf->getCurrent();
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, generic data does not exist') );
					}
				} else {
					$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, generic data does not exist') );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to delete user generic data...', __FILE__, __LINE__, __METHOD__, 10);
					$lf->setDeleted(TRUE);

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('User Deleted...', __FILE__, __LINE__, __METHOD__, 10);
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('User Generic Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					if ( $primary_validator->isValid() == FALSE ) {
						$validator[$key] = $primary_validator->getErrorsArray();
					} else {
						$validator[$key] = $lf->Validator->getErrorsArray();
					}
				}

				$lf->CommitTransaction();
			}

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
