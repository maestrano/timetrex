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
 * $Id: APIBankAccount.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\Users
 */
class APIBankAccount extends APIFactory {
	protected $main_class = 'BankAccountFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get default branch data for creating new branches.
	 * @return array
	 */
	function getBankAccountDefaultData( $user_id = NULL ) {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text('Getting wage default data...', __FILE__, __LINE__, __METHOD__,10);

		//If user_id is passed, check for other wage entries, if none, default to the employees hire date.

		$data = array(
						'company_id' => $company_obj->getId(),
					);

		return $this->returnHandler( $data );
	}

	/**
	 * Get branch data for one or more branches.
	 * @param array $data filter data
	 * @return array
	 */
	function getBankAccount( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('user','enabled')
				OR !( $this->getPermissionObject()->Check('user','edit_bank') OR $this->getPermissionObject()->Check('user','edit_own_bank') OR $this->getPermissionObject()->Check('user','edit_child_bank')  ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		if ( $this->getPermissionObject()->Check('user','edit_bank') == TRUE ) {
			//Don't set permission_children_ids.
			$data['filter_data']['permission_children_ids'] = NULL;
		} else {
			if ( $this->getPermissionObject()->Check('user','edit_child_bank') == TRUE ) {
				//Manually handle the permission checks here because edit_child_bank doesn't fit with getPermissionChildren() appending "_own" or "_child" on the end.
				$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionHierarchyChildren( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
			}

			if ( $this->getPermissionObject()->Check('user','edit_own_bank') == TRUE  ) {
				$data['filter_data']['permission_children_ids'][] = $this->getCurrentUserObject()->getId();
			}
			Debug::Arr($data['filter_data']['permission_children_ids'],  'Permission Children: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		$blf = TTnew( 'BankAccountListFactory' );
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			Debug::Arr($retarr, 'Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonBankAccountData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getBankAccount( $data, TRUE ) ) );
	}

	/**
	 * Validate branch data for one or more branches.
	 * @param array $data branch data
	 * @return array
	 */
	function validateBankAccount( $data ) {
		return $this->setBankAccount( $data, TRUE );
	}

	/**
	 * Set branch data for one or more branches.
	 * @param array $data branch data
	 * @return array
	 */
	function setBankAccount( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('user','enabled')
				OR !( $this->getPermissionObject()->Check('user','edit_bank') OR $this->getPermissionObject()->Check('user','edit_own_bank') OR $this->getPermissionObject()->Check('user','edit_child_bank') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
			$permission_children_ids = FALSE;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' BankAccounts', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'BankAccountListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					//Modifying existing object.
					//Get wage object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							  $validate_only == TRUE
							  OR
								(

								$this->getPermissionObject()->Check('user','edit_bank')
									OR ( $this->getPermissionObject()->Check('user','edit_own_bank') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === TRUE )
									OR ( $this->getPermissionObject()->Check('user','edit_child_bank') AND $this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ) === TRUE )
								//$this->getPermissionObject()->Check('user','edit_bank')
								//	OR ( $this->getPermissionObject()->Check('user','edit_own_bank') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
								) ) {

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
					//$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('user','add'), TTi18n::gettext('Add permission denied') );
				}
				//Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					if ( ( $row['user_id'] != '' AND $row['user_id'] == $this->getCurrentUserObject()->getId() ) AND $row['company_id'] == '' AND $this->getPermissionObject()->Check('user','edit_own_bank') ) {
						Debug::Text('Current User/Company', __FILE__, __LINE__, __METHOD__,10);
						//Current user
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
						$row['user_id'] = $this->getCurrentUserObject()->getId();
					} elseif ( $row['user_id'] != '' AND $row['company_id'] == '' AND $this->getPermissionObject()->Check('user','edit_bank') ) {
						Debug::Text('Specified User', __FILE__, __LINE__, __METHOD__,10);
						//Specified User
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
					} elseif ( $row['company_id'] != '' AND $row['user_id'] == '' AND $this->getPermissionObject()->Check('company','edit_own_bank') ) {
						Debug::Text('Specified Company', __FILE__, __LINE__, __METHOD__,10);
						//Company bank.
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
					} else {
						Debug::Text('No Company or User ID specified...', __FILE__, __LINE__, __METHOD__,10);
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
						//$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied') );
					}

					$lf->setObjectFromArray( $row );

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
	 * Delete one or more branchs.
	 * @param array $data branch data
	 * @return array
	 */
	function deleteBankAccount( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('user','enabled')
				OR !( $this->getPermissionObject()->Check('user','delete') OR $this->getPermissionObject()->Check('user','delete_own') OR $this->getPermissionObject()->Check('user','delete_child') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text('Received data for: '. count($data) .' BankAccounts', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'BankAccountListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get branch object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('user','delete')
								OR ( $this->getPermissionObject()->Check('user','delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === TRUE )
								OR ( $this->getPermissionObject()->Check('user','delete_child') AND $this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ) === TRUE )) {
						//if ( $this->getPermissionObject()->Check('user','delete')
						//		OR ( $this->getPermissionObject()->Check('user','delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
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
