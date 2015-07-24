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
 * @package API\Policy
 */
class APIAbsencePolicy extends APIFactory {
	protected $main_class = 'AbsencePolicyFactory';

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
				AND ( !$this->getPermissionObject()->Check('absence_policy', 'enabled')
					OR !( $this->getPermissionObject()->Check('absence_policy', 'view') OR $this->getPermissionObject()->Check('absence_policy', 'view_own') OR $this->getPermissionObject()->Check('absence_policy', 'view_child') ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default absence policy data for creating new absence policyes.
	 * @return array
	 */
	function getAbsencePolicyDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text('Getting absence policy default data...', __FILE__, __LINE__, __METHOD__, 10);

		$data = array(
						'company_id' => $company_obj->getId(),
						'rate' => '1.00',
						'accrual_rate' => '1.00',
					);

		return $this->returnHandler( $data );
	}

	/**
	 * Get absence policy data for one or more absence policyes.
	 * @param array $data filter data
	 * @return array
	 */
	function getAbsencePolicy( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('absence_policy', 'enabled')
				OR !( $this->getPermissionObject()->Check('absence_policy', 'view') OR $this->getPermissionObject()->Check('absence_policy', 'view_own') OR $this->getPermissionObject()->Check('absence_policy', 'view_child')	 ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			$data['filter_columns'] = $this->handlePermissionFilterColumns( (isset($data['filter_columns'])) ? $data['filter_columns'] : NULL, Misc::trimSortPrefix( $this->getOptions('list_columns') ) );
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );
		/*
		//Handle this in the SQL query directly with the user_id filter.
		//Make sure we filter absence policies to just those assigned to the policy group when user_id filter is passed.
		if ( isset( $data['filter_data']['user_id'] ) ) {
			$user_ids = (array)$data['filter_data']['user_id'];

			$pgulf = new PolicyGroupUserListFactory();
			$pgulf->getByUserId( $user_ids );
			if ( $pgulf->getRecordCount() > 0 ) {
				$pguf_obj = $pgulf->getCurrent();
				$policy_group_id = $pguf_obj->getPolicyGroup();
			}
			if ( isset($policy_group_id) ) {
				$cgmlf = new CompanyGenericMapListFactory();
				$cgmlf->getByObjectTypeAndObjectID( 170, $policy_group_id );
				if ( $cgmlf->getRecordCount() > 0 ) {
					foreach( $cgmlf as $cgm_obj ) {
						$absence_policy_ids[] = $cgm_obj->getMapID();
					}
				}
			}
			
			if ( isset( $absence_policy_ids ) ) {
				$data['filter_data']['id'] = $absence_policy_ids;
			} else {
				//Make sure that is no absence policies are assigned to the policy group, we don't display any.
				$data['filter_data']['id'] = array(0);
			}
			unset( $data['filter_data']['user_id'] );
		}
		*/

		if ( isset($data['filter_data']['user_id']) AND !is_array($data['filter_data']['user_id']) ) {
			$data['filter_data']['user_id'] = (array)$data['filter_data']['user_id'];
		}

		//Remove any user_id=0 as its for an OPEN shift and no absence policy is ever assigned to this user in the policy groups.
		if ( isset($data['filter_data']['user_id']) AND in_array( 0, $data['filter_data']['user_id'] ) ) {
			$open_user_id_key = array_search( 0, $data['filter_data']['user_id']);
			if ( $open_user_id_key !== FALSE ) {
				Debug::Text('Removing user_id=0 from filter...', __FILE__, __LINE__, __METHOD__, 10);
				unset($data['filter_data']['user_id'][$open_user_id_key]);
			}
		}
		
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'absence_policy', 'view' );

		$blf = TTnew( 'AbsencePolicyListFactory' );
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->setPagerObject( $blf );

			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'] );
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
	function getCommonAbsencePolicyData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getAbsencePolicy( $data, TRUE ) ) );
	}

	/**
	 * Validate absence policy data for one or more absence policyes.
	 * @param array $data absence policy data
	 * @return array
	 */
	function validateAbsencePolicy( $data ) {
		return $this->setAbsencePolicy( $data, TRUE );
	}

	/**
	 * Set absence policy data for one or more absence policyes.
	 * @param array $data absence policy data
	 * @return array
	 */
	function setAbsencePolicy( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('absence_policy', 'enabled')
				OR !( $this->getPermissionObject()->Check('absence_policy', 'edit') OR $this->getPermissionObject()->Check('absence_policy', 'edit_own') OR $this->getPermissionObject()->Check('absence_policy', 'edit_child') OR $this->getPermissionObject()->Check('absence_policy', 'add') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' AbsencePolicys', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'AbsencePolicyListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					//Modifying existing object.
					//Get absence policy object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							$validate_only == TRUE
							OR
								(
								$this->getPermissionObject()->Check('absence_policy', 'edit')
									OR ( $this->getPermissionObject()->Check('absence_policy', 'edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('absence_policy', 'add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					//Force Company ID to current company.
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();

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
	 * Delete one or more absence policys.
	 * @param array $data absence policy data
	 * @return array
	 */
	function deleteAbsencePolicy( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('absence_policy', 'enabled')
				OR !( $this->getPermissionObject()->Check('absence_policy', 'delete') OR $this->getPermissionObject()->Check('absence_policy', 'delete_own') OR $this->getPermissionObject()->Check('absence_policy', 'delete_child') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' AbsencePolicys', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'AbsencePolicyListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get absence policy object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('absence_policy', 'delete')
								OR ( $this->getPermissionObject()->Check('absence_policy', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
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

	/**
	 * Copy one or more absence policyes.
	 * @param array $data absence policy IDs
	 * @return array
	 */
	function copyAbsencePolicy( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Text('Received data for: '. count($data) .' AbsencePolicys', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$src_rows = $this->stripReturnHandler( $this->getAbsencePolicy( array('filter_data' => array('id' => $data) ), TRUE ) );
		if ( is_array( $src_rows ) AND count($src_rows) > 0 ) {
			Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $src_rows as $key => $row ) {
				unset($src_rows[$key]['id'], $src_rows[$key]['manual_id'] ); //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name
			}
			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setAbsencePolicy( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( FALSE );
	}

	function getAccrualBalance( $accrual_policy_id, $user_id ) {
		if ( $accrual_policy_id == '' ) {
			return FALSE;
		}
		if ( $user_id == '' ) {
			return FALSE;
		}

		$ablf = TTnew( 'AccrualBalanceListFactory' );
		$ablf->getByUserIdAndAccrualPolicyId( (int)$user_id, (int)$accrual_policy_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		return $this->returnHandler(  TTDate::getTimeUnit($accrual_balance) );
	}

	function getAbsencePolicyBalance( $absence_policy_id, $user_id ) {
		if ( $absence_policy_id == '' ) {
			return $this->returnHandler( FALSE );
		}

		if ( $user_id == '' ) {
			return $this->returnHandler( FALSE );
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$aplf->getByIdAndCompanyId( $absence_policy_id, $this->getCurrentCompanyObject()->getId() );
		if ( $aplf->getRecordCount() > 0 ) {
			$ap_obj = $aplf->getCurrent();
			if ( $ap_obj->getAccrualPolicyID() != '' ) {
				return $this->returnHandler( $this->getAccrualBalance( $ap_obj->getAccrualPolicyID(), $user_id ) );
			}
		}

		return $this->returnHandler( FALSE );
	}

	function getProjectedAbsencePolicyBalance( $absence_policy_id, $user_id, $epoch, $amount, $previous_amount = 0 ) {
		if ( $absence_policy_id == '' ) {
			return $this->returnHandler( FALSE );
		}

		if ( $user_id == '' ) {
			return $this->returnHandler( FALSE );
		}
		
		$epoch = TTDate::parseDateTime( $epoch );

		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$aplf->getByIdAndCompanyId( $absence_policy_id, $this->getCurrentCompanyObject()->getId() );
		if ( $aplf->getRecordCount() > 0 ) {
			$ap_obj = $aplf->getCurrent();

			$pfp_obj = $ap_obj->getPayFormulaPolicyObject();
			if ( is_object($pfp_obj) AND $pfp_obj->getAccrualPolicyAccount() != '' ) {
				$aplf = new AccrualPolicyListFactory();
				$aplf->getByPolicyGroupUserIdAndAccrualPolicyAccount( (int)$user_id, (int)$pfp_obj->getAccrualPolicyAccount() );
				Debug::Text('Accrual Policy Records: '. $aplf->getRecordCount() .' User ID: '. $user_id .' Accrual Policy Account: '. $pfp_obj->getAccrualPolicyAccount(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $aplf->getRecordCount() > 0 ) {
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByIDAndCompanyID( $user_id, $this->getCurrentCompanyObject()->getId() );
					if ( $ulf->getRecordCount() == 1 ) {
						$u_obj = $ulf->getCurrent();

						$retval = FALSE;
						foreach( $aplf as $acp_obj ) {
							Debug::Text('  Accrual Policy ID: '. $acp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
							//Pass $retval back into itself so additional balance can be calculated when accrual policy accounts are used in multiple policies.
							$retval = $acp_obj->getAccrualBalanceWithProjection( $u_obj, $epoch, $amount, $previous_amount, $retval );
							//Debug::Arr($retval, '  Retval: ', __FILE__, __LINE__, __METHOD__, 10);
						}

						return $this->returnHandler( $retval );
					}
				} else {
					Debug::Text('No Accrual Policies to return projection for, just get current balance then...', __FILE__, __LINE__, __METHOD__, 10);
					$available_balance = $pfp_obj->getAccrualPolicyAccountObject()->getCurrentAccrualBalance( (int)$user_id );

					$retarr = array(
									'available_balance' => $available_balance,
									'current_time' => $amount,
									'remaining_balance' => ( $available_balance - $amount ),
									'projected_balance' => $available_balance,
									'projected_remaining_balance' => ( $available_balance - $amount ),
									);

					Debug::Arr($retarr, 'Current Accrual Arr: ', __FILE__, __LINE__, __METHOD__, 10);
					return $this->returnHandler( $retarr );
				}
			}
		}

		Debug::Text('No projections to return...', __FILE__, __LINE__, __METHOD__, 10);

		return $this->returnHandler( FALSE );
	}
}
?>
