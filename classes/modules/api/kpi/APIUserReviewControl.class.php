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
 * $Id: APIUserReviewControl.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API_APIUserReviewControl
 */
class APIUserReviewControl extends APIFactory {
	protected $main_class = 'UserReviewControlFactory';

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
		if ( $name == 'columns'
				AND ( !$this->getPermissionObject()->Check('user_review','enabled') 
					OR !( $this->getPermissionObject()->Check('user_review','view') OR $this->getPermissionObject()->Check('user_review','view_own') OR $this->getPermissionObject()->Check('user_review','view_child') ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * @return array
	 */
	function getUserReviewControlDefaultData() {
        $user_obj = $this->getCurrentUserObject();
		Debug::Text('Getting user review control default data...', __FILE__, __LINE__, __METHOD__,10);

		$data = array(
                        'reviewer_user_id' => $user_obj->getId(),
					);

		return $this->returnHandler( $data );
	}

	/**
	 * @param array $data filter data
	 * @return array
	 */
	function getUserReviewControl( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('user_review','enabled')
				OR !( $this->getPermissionObject()->Check('user_review','view') OR $this->getPermissionObject()->Check('user_review','view_own')  OR $this->getPermissionObject()->Check('user_review','view_child')  ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			$data['filter_columns'] = $this->handlePermissionFilterColumns( (isset($data['filter_columns'])) ? $data['filter_columns'] : NULL, Misc::trimSortPrefix( $this->getOptions('list_columns') ) );
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user_review', 'view' );
        
		$urclf = TTnew( 'UserReviewControlListFactory' );
		$urclf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $urclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $urclf->getRecordCount() > 0 ) {
			$this->setPagerObject( $urclf );
            Debug::Arr($data,'Searching Data: ',__FILE__, __LINE__, __METHOD__, 10);
			foreach( $urclf as $urc_obj ) {
				$retarr[] = $urc_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] ); 
                
			}
            Debug::Arr($retarr, 'Getting Data: ',__FILE__, __LINE__, __METHOD__,10);
			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonUserReviewControlData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getUserReviewControl( $data, TRUE ) ) );
	}

	/**
	 * @param array $data KPI data
	 * @return array
	 */
	function validateUserReviewControl( $data ) {
		return $this->setUserReviewControl( $data, TRUE );
	}

	/**
	 * @param array $data KPI data
	 * @return array
	 */
	function setUserReviewControl( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;
        
		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}
        
		if ( !$this->getPermissionObject()->Check('user_review','enabled')
				OR !( $this->getPermissionObject()->Check('user_review','edit') OR $this->getPermissionObject()->Check('user_review','edit_own') OR $this->getPermissionObject()->Check('user_review','edit_child') OR $this->getPermissionObject()->Check('user_review','add') ) ) {
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
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserReviewControlListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					//Modifying existing object.
					//Get Kpi object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
                    
                    if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
                        if (
							  $validate_only == TRUE                                
							  OR
								(
								$this->getPermissionObject()->Check('user_review','edit')
									OR ( $this->getPermissionObject()->Check('user_review','edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === TRUE )
								    OR ( $this->getPermissionObject()->Check('user_review','edit_child') AND $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === TRUE )
                                ) ) {

							Debug::Text('Row Exists, getting current data: ', $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent(); 
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'user_review', FALSE, TTi18n::gettext('Edit permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, record does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					if (    !( $validate_only == TRUE
								OR
								( $this->getPermissionObject()->Check('user_review','add')
									AND
									(
										$this->getPermissionObject()->Check('user_review','edit')
										OR ( isset($row['user_id']) AND $this->getPermissionObject()->Check('user_review','edit_own') AND $this->getPermissionObject()->isOwner( FALSE, $row['user_id'] ) === TRUE ) //We don't know the created_by of the user at this point, but only check if the user is assigned to the logged in person.
										OR ( isset($row['user_id']) AND $this->getPermissionObject()->Check('user_review','edit_child') AND $this->getPermissionObject()->isChild( $row['user_id'], $permission_children_ids ) === TRUE )
									)
								)
							) ) {
						$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Add permission denied') );
					}

					//Because this class has sub-classes that depend on it, when adding a new record we need to make sure the ID is set first,
					//so the sub-classes can depend on it. We also need to call Save( TRUE, TRUE ) to force a lookup on isNew()
					$row['id'] = $lf->getNextInsertId();
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);
                    Debug::Arr($row,'Setting object data...',__FILE__,__LINE__,__METHOD__,10);
                    
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();
                    $lf->setObjectFromArray( $row );
                    
					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $validate_only == TRUE ) {
							$save_result[$key] = TRUE;
						} else {
							$save_result[$key] = $lf->Save( TRUE, TRUE ); //Force lookup on isNew()
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
	 * @param array $data user review control data
	 * @return array
	 */
	function deleteUserReviewControl( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('user_review','enabled')
				OR !( $this->getPermissionObject()->Check('user_review','delete') OR $this->getPermissionObject()->Check('user_review','delete_own') OR $this->getPermissionObject()->Check('user_review','delete_child') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}
        
        //Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();
        
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);
        
		$total_records = count($data);
        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
		  
			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserReviewControlListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get Kpi object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('user_review','delete')
								OR ( $this->getPermissionObject()->Check('user_review','delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === TRUE )
                                OR ( $this->getPermissionObject()->Check('user_review','delete_child') AND $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === TRUE ) ) {
							Debug::Text('Record Exists, deleting record: ', $id, __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'user_review', FALSE, TTi18n::gettext('Delete permission denied') );
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
	 * @param array $data user review control IDs
	 * @return array
	 */
	function copyUserReviewControl( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$src_rows = $this->stripReturnHandler( $this->getUserReviewControl( array('filter_data' => array('id' => $data) ), TRUE ) );
		if ( is_array( $src_rows ) AND count($src_rows) > 0 ) {
			Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $src_rows as $key => $row ) {
				unset($src_rows[$key]['id']); //Clear fields that can't be copied
			}
			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			return $this->setUserReviewControl( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( FALSE );
	}
}
?>
