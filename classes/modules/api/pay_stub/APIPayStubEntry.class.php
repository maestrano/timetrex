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
 * @package API\PayStub
 */
class APIPayStubEntry extends APIFactory {
	protected $main_class = 'PayStubEntryFactory';

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
			AND ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub', 'view') OR $this->getPermissionObject()->Check('pay_stub', 'view_own') OR $this->getPermissionObject()->Check('pay_stub', 'view_child') ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default paystub_entry_account data for creating new paystub_entry_accountes.
	 * @return array
	 */
	function getPayStubEntryDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text('Getting pay stub entry default data...', __FILE__, __LINE__, __METHOD__, 10);

		$data = array(
			10 => array(
				array(  'tmp_type' => 10 ),
				array(  'tmp_type' => 10,
						'type_id' => 40,
						'name' => TTi18n::getText('Total Gross')
				)
			),
			20 => array(
				array( 'tmp_type' => 20 ),
				array( 'tmp_type' => 20,
						'type_id' => 40,
						'name' => TTi18n::getText('Total Deductions')
				)
			),
			30 => array(
				array( 'tmp_type' => 30 ),
				array( 'tmp_type' => 30,
						'type_id' => 40,
						'name' => TTi18n::getText('Employer Total Contributions')
				)
			),
			40 => array(
				array( 'tmp_type' => 40,
						'type_id' => 40,
						'name' => TTi18n::getText('Net Pay')
				),
			),
			50 => array(
				array( 'tmp_type' => 50 ) ),
		);

		return $this->returnHandler( $data );
	}

	/**
	 * Get paystub_entry_account data for one or more paystub_entry_accountes.
	 * @param array $data filter data
	 * @return array
	 */
	function getPayStubEntry( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
			OR !( $this->getPermissionObject()->Check('pay_stub', 'view') OR $this->getPermissionObject()->Check('pay_stub', 'view_child')	) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view' );

		$blf = TTnew( 'PayStubEntryListFactory' );
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->setPagerObject( $blf );

			$prev_type = NULL;
			$retarr = array();

			foreach( $blf as $b_obj ) {
				if ( $prev_type == 40 OR $b_obj->getPayStubEntryAccountObject()->getType() != 40 ) {
					$type = $b_obj->getPayStubEntryAccountObject()->getType();
				}

				if ( isset( $type )  ) {
					$retarr[$type][] = array_merge( $b_obj->getObjectAsArray( $data['filter_columns'] ), array( 'tmp_type' => $type ) );
				}

				$prev_type = $b_obj->getPayStubEntryAccountObject()->getType();
			}

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayStubEntryData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayStubEntry( $data, TRUE ) ) );
	}

	/**
	 * Validate paystub_entry_account data for one or more paystub_entry_accountes.
	 * @param array $data paystub_entry_account data
	 * @return array
	 */
	function validatePayStubEntry( $data ) {
		return $this->setPayStubEntry( $data, TRUE );
	}

	/**
	 * Set paystub_entry_account data for one or more paystub_entry_accountes.
	 * @param array $data paystub_entry_account data
	 * @return array
	 */
	/*
	function setPayStubEntry( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
			OR !( $this->getPermissionObject()->Check('pay_stub', 'edit') OR $this->getPermissionObject()->Check('pay_stub', 'edit_own') OR $this->getPermissionObject()->Check('pay_stub', 'edit_child') OR $this->getPermissionObject()->Check('pay_stub', 'add') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' PayStubEntries', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$lf = TTnew( 'PayStubEntryListFactory' );
			$lf->StartTransaction(); //Need to wrap the entire batch in its own transaction.

			$i = 0;
			foreach( $data as $key => $row ) {
				if ( ( isset($row['type']) AND $row['type'] == 40 ) OR ( isset($row['type_id']) AND $row['type_id'] == 40 ) ) {
					$validator_stats['total_records']--;
					continue;
				}

				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubEntryListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					if ( $i == 0 AND $row['pay_stub_id'] > 0 ) {
						$pslf = TTnew('PayStubListFactory');
						$pslf->getByCompanyIdAndId( $this->getCurrentCompanyObject()->getId(), (int)$row['pay_stub_id'] );
						if ( $pslf->getRecordCount() == 1 ) {
							$pay_stub_obj = $pslf->getCurrent();

							if ( $pay_stub_obj->getStatus() == 25 ) {
								$pay_stub_obj->setTainted(TRUE); //So we know it was modified.

								//Load previous pay stub
								$pay_stub_obj->loadPreviousPayStub();

								//Delete all entries, so they can be re-added.
								$pay_stub_obj->deleteEntries( TRUE );

								//When editing pay stubs we can't re-process linked accruals.
								$pay_stub_obj->setEnableLinkedAccruals( FALSE );
								Debug::Text('Loaded pay stub: ', $row['pay_stub_id'], __FILE__, __LINE__, __METHOD__, 10);
							} else {
								$primary_validator->isTrue( 'status_id', FALSE, TTi18n::getText('Pay Stub must be marked as OPEN before any changes can be made') );
							}
						} else {
							Debug::Text('ERROR: Unable to find pay stub: ', $row['pay_stub_id'], __FILE__, __LINE__, __METHOD__, 10);
							break;
						}
						unset($pslf);
					}

					
					//Modifying existing object.
					//Get paystub_entry_account object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndPayStubIdAndCompanyId( $row['id'], $row['pay_stub_id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							$validate_only == TRUE
							OR
							(
								$this->getPermissionObject()->Check('pay_stub', 'edit')
								OR ( $this->getPermissionObject()->Check('pay_stub', 'edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('pay_stub', 'add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					
					if ( isset($row['pay_stub_entry_name_id']) AND $row['pay_stub_entry_name_id'] > 0 ) {
						Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

						if ( !isset($row['units']) OR $row['units'] == '' ) {
							$row['units'] = 0;
						}
						if ( !isset($row['rate']) OR $row['rate'] == '' ) {
							$row['rate'] = 0;
						}
						if ( !isset($row['description']) OR $row['description'] == '' ) {
							$row['description'] = NULL;
						}
						if ( !isset($row['pay_stub_amendment_id']) OR $row['pay_stub_amendment_id'] == '' ) {
							$row['pay_stub_amendment_id'] = NULL;
						}
						if ( !isset($row['user_expense_id']) OR $row['user_expense_id'] == '' ) {
							$row['user_expense_id'] = NULL;
						}

						$ytd_adjustment = FALSE;
						if ( $row['pay_stub_amendment_id'] > 0 ) {
							$psamlf = TTNew('PayStubAmendmentListFactory');
							$psamlf->getByIdAndCompanyId( (int)$row['pay_stub_amendment_id'], $this->getCurrentCompanyObject()->getId() );
							if ( $psamlf->getRecordCount() > 0 ) {
								$ytd_adjustment = $psamlf->getCurrent()->getYTDAdjustment();
							}
						}
						Debug::Text(' Pay Stub Amendment Id: '. $row['pay_stub_amendment_id'] .' YTD Adjusment: '. (int)$ytd_adjustment, __FILE__, __LINE__, __METHOD__,10);

						$is_valid = $pay_stub_obj->addEntry( $row['pay_stub_entry_name_id'], $row['amount'], $row['units'], $row['rate'], $row['description'], $row['pay_stub_amendment_id'], NULL, NULL, $ytd_adjustment, $row['user_expense_id'] );
						if ( $is_valid == TRUE ) {
							Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
							$save_result[$key] = TRUE;
							$validator_stats['valid_records']++;
						}
					} else {
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

				$i++;
			}

			if ( isset($pay_stub_obj) ) {
				Debug::Text('Final processing of pay stub...', __FILE__, __LINE__, __METHOD__, 10);
				$pay_stub_obj->setEnableCalcYTD( TRUE );
				$pay_stub_obj->setEnableProcessEntries( TRUE );
				$pay_stub_obj->processEntries();

				if ( $pay_stub_obj->isValid() ) {
					$pay_stub_obj->Save();
				} else {
					$lf->FailTransaction();
				}
			} else {
				$lf->FailTransaction();
				Debug::Text('ERROR: Unable to perform final processing of pay stub...', __FILE__, __LINE__, __METHOD__, 10);
			}

			$lf->CommitTransaction();

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
	*/

	/**
	 * Delete one or more paystub_entry_accounts.
	 * @param array $data paystub_entry_account data
	 * @return array
	 */
	/*
	function deletePayStubEntry( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
			OR !( $this->getPermissionObject()->Check('pay_stub', 'delete') OR $this->getPermissionObject()->Check('pay_stub', 'delete_own') OR $this->getPermissionObject()->Check('pay_stub', 'delete_child') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' PayStubEntrys', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubEntryListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get paystub_entry_account object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('pay_stub', 'delete')
							OR ( $this->getPermissionObject()->Check('pay_stub', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
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
	*/
}
?>
