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
 * $Id: APIPayStubAmendment.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\PayStubAmendment
 */
class APIPayStubAmendment extends APIFactory {
	protected $main_class = 'PayStubAmendmentFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get default branch data for creating new branches.
	 * @return array
	 */
	function getPayStubAmendmentDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text('Getting pay stub amendment default data...', __FILE__, __LINE__, __METHOD__,10);

		$data = array(
						'company_id' => $company_obj->getId(),
						'user_id' => array(),
						'status_id' => 50,
						'type_id' => 10,
						'effective_date' => TTDate::getAPIDate('DATE', TTDate::getTime() )
					);

		return $this->returnHandler( $data );
	}

	/**
	 * Get branch data for one or more branches.
	 * @param array $data filter data
	 * @return array
	 */
	function getPayStubAmendment( $data = NULL, $disable_paging = FALSE, $format = FALSE ) {
		if ( !$this->getPermissionObject()->Check('pay_stub_amendment','enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub_amendment','view') OR $this->getPermissionObject()->Check('pay_stub_amendment','view_own') OR $this->getPermissionObject()->Check('pay_stub_amendment','view_child')  ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub_amendment', 'view' );

		$blf = TTnew( 'PayStubAmendmentListFactory' );
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

		$format = Misc::trimSortPrefix( $format );
		if ( $format != '' ) {
			$export_options = Misc::trimSortPrefix( $blf->getOptions('export_type') );
			if ( isset($export_options[$format] ) ) {
				if ( $blf->getRecordCount() > 0 ) {
					$this->getProgressBarObject()->setDefaultKey( $this->getAMFMessageID() );
					$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );
					$blf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

					$output = $blf->exportPayStubAmendment( $blf, $format );

					$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

					if ( stristr( $format, 'cheque') ) {
						return Misc::APIFileDownload( 'checks_'. str_replace(array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.pdf', 'application/pdf', $output );
					} else {
						//Include file creation number in the exported file name, so the user knows what it is without opening the file,
						//and can generate multiple files if they need to match a specific number.
						$ugdlf = TTnew( 'UserGenericDataListFactory' );
						$ugdlf->getByCompanyIdAndScriptAndDefault( $this->getCurrentCompanyObject()->getId(), 'PayStubFactory', TRUE );
						if ( $ugdlf->getRecordCount() > 0 ) {
							$ugd_obj = $ugdlf->getCurrent();
							$setup_data = $ugd_obj->getData();
						}

						if ( isset($setup_data) ) {
							$file_creation_number = $setup_data['file_creation_number']++;
						} else {
							$file_creation_number = 0;
						}
						return Misc::APIFileDownload( 'eft_'. $file_creation_number .'_'. str_replace(array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.txt', 'application/pdf', $output );
					}
				}
			} else {
				Debug::Text('Invalid format '. $format, __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			if ( $blf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

				$this->setPagerObject( $blf );

				foreach( $blf as $b_obj ) {
					$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
				}

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				return $this->returnHandler( $retarr );
			}
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayStubAmendmentData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayStubAmendment( $data, TRUE ) ) );
	}

	/**
	 * Validate branch data for one or more branches.
	 * @param array $data branch data
	 * @return array
	 */
	function validatePayStubAmendment( $data ) {
		return $this->setPayStubAmendment( $data, TRUE );
	}

	/**
	 * Set branch data for one or more branches.
	 * @param array $data branch data
	 * @return array
	 */
	function setPayStubAmendment( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('pay_stub_amendment','enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub_amendment','edit') OR $this->getPermissionObject()->Check('pay_stub_amendment','edit_own') OR $this->getPermissionObject()->Check('pay_stub_amendment','edit_child') OR $this->getPermissionObject()->Check('pay_stub_amendment','add') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' PayStubAmendments', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubAmendmentListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					//Modifying existing object.
					//Get branch object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							  $validate_only == TRUE
							  OR
								(
								$this->getPermissionObject()->Check('pay_stub_amendment','edit')
									OR ( $this->getPermissionObject()->Check('pay_stub_amendment','edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('pay_stub_amendment','add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( $validate_only == TRUE ) {
					$lf->validate_only = TRUE;
				}

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->setObjectFromArray( $row );

					//Force Company ID to current company.
					//$lf->setCompany( $this->getCurrentCompanyObject()->getId() );

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
	function deletePayStubAmendment( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('pay_stub_amendment','enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub_amendment','delete') OR $this->getPermissionObject()->Check('pay_stub_amendment','delete_own') OR $this->getPermissionObject()->Check('pay_stub_amendment','delete_child') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' PayStubAmendments', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubAmendmentListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get branch object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('pay_stub_amendment','delete')
								OR ( $this->getPermissionObject()->Check('pay_stub_amendment','delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
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
	 * Copy one or more branches.
	 * @param array $data branch IDs
	 * @return array
	 */
	function copyPayStubAmendment( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Text('Received data for: '. count($data) .' PayStubAmendments', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$src_rows = $this->stripReturnHandler( $this->getPayStubAmendment( array('filter_data' => array('id' => $data) ), TRUE ) );
		if ( is_array( $src_rows ) AND count($src_rows) > 0 ) {
			Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $src_rows as $key => $row ) {
				unset($src_rows[$key]['id'] ); //Clear fields that can't be copied
			}
			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setPayStubAmendment( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Calculate the PS Amendment amount based on the user, rate and units.
	 * @param int $user_id User ID
	 * @param float $rate Rate
	 * @param float $units Units
	 * @return float
	 */
	function calcAmount( $user_id, $rate, $units ) {
		$psf = TTnew( 'PayStubAmendmentFactory' );
		$psf->setUser( $user_id );
		$psf->setRate( $rate );
		$psf->setUnits( $units );
		return $this->returnHandler( $psf->calcAmount() );
	}
}
?>
