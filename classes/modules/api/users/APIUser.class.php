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
 * @package API\Users
 */
class APIUser extends APIFactory {
	protected $main_class = 'UserFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get default user data for creating new users.
	 * @return array
	 */
	function getUserDefaultData( $tmp_company_id = NULL ) {

		//Allow getting default data from other companies, so it makes it easier to create the first employee of a company.
		if ( $tmp_company_id != '' AND $tmp_company_id > 0 AND $this->getPermissionObject()->Check('company', 'enabled') AND $this->getPermissionObject()->Check('company', 'view') ) {
			$company_id = $tmp_company_id;
		} else {
			$company_id = $this->getCurrentCompanyObject()->getId();
		}
		Debug::Text('Getting user default data for Company ID: '. $company_id .' TMP Company ID: '. $tmp_company_id, __FILE__, __LINE__, __METHOD__, 10);

		//Get New Hire Defaults.
		$udlf = TTnew( 'UserDefaultListFactory' );
		$udlf->getByCompanyId( $company_id );
		if ( $udlf->getRecordCount() > 0 ) {
			Debug::Text('Using User Defaults, as they exist...', __FILE__, __LINE__, __METHOD__, 10);
			$udf_obj = $udlf->getCurrent();

			$data = array(
							'company_id' => $company_id,
							'status_id' => 10, //Active.
							'title_id' => $udf_obj->getTitle(),
							'employee_number' => UserFactory::getNextAvailableEmployeeNumber( $company_id ),
							'city' => $udf_obj->getCity(),
							'country' => $udf_obj->getCountry(),
							'province' => $udf_obj->getProvince(),
							'work_phone' => $udf_obj->getWorkPhone(),
							'work_phone_ext' => $udf_obj->getWorkPhoneExt(),
							'work_email' => $udf_obj->getWorkEmail(),
							'hire_date' => TTDate::getAPIDate( 'DATE', $udf_obj->getHireDate() ),
							'sex_id' => 5, //Unspecified.
							'default_branch_id' => $udf_obj->getDefaultBranch(),
							'default_department_id' => $udf_obj->getDefaultDepartment(),
							'permission_control_id' => $udf_obj->getPermissionControl(),
							'pay_period_schedule_id' => $udf_obj->getPayPeriodSchedule(),
							'policy_group_id' => $udf_obj->getPolicyGroup(),
							'currency_id' => $udf_obj->getCurrency(),
						);
		}

		if ( !isset( $data['company_id'] ) ) {
			$data['company_id'] = $company_id;
		}

		if ( !isset( $data['status_id'] ) ) {
			$data['status_id'] = 10; //Active
		}

		if ( !isset( $data['currency_id'] ) ) {
			$data['currency_id'] = 0;
		}

		if ( !isset( $data['country'] ) ) {
			$data['country'] = 'US';
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getHighestEmployeeNumberByCompanyId( $company_id );
		if ( $ulf->getRecordCount() > 0 ) {
			Debug::Text('Highest Employee Number: '. $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__, 10);
			if ( is_numeric( $ulf->getCurrent()->getEmployeeNumber() ) == TRUE ) {
				$data['next_available_employee_number'] = ( $ulf->getCurrent()->getEmployeeNumber() + 1 );
			} else {
				Debug::Text('Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__, 10);
				$data['next_available_employee_number'] = NULL;
			}
		} else {
			$data['next_available_employee_number'] = 1;
		}

		if ( !isset($data['hire_date']) OR $data['hire_date'] == '' ) {
			$data['hire_date'] = TTDate::getAPIDate( 'DATE', time() );
		}

		return $this->returnHandler( $data );
	}

	/**
	 * Get user data for one or more users.
	 * @param array $data filter data, see reference for details.
	 * @see \Modules\Users\UserListFactory::getAPISearchByCompanyIdAndArrayCriteria()
	 * @param boolean $disable_paging disables paging and returns all records.
	 * @return array
	 */
	function getUser( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('user', 'enabled')
				OR !( $this->getPermissionObject()->Check('user', 'view') OR $this->getPermissionObject()->Check('user', 'view_own') OR $this->getPermissionObject()->Check('user', 'view_child')  ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		//We need to take into account different permissions, ie: punch->view, view_child, view_own when displaying the dropdown
		//box in the TimeSheet view and other views as well. Allow the caller of this function to pass a "permission_section"
		//that can be used to determine this.
		if ( isset($data['permission_section']) AND $data['permission_section'] != '' ) {
			$permission_section = trim(strtolower($data['permission_section']));
		} else {
			$permission_section = 'user';
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		//$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( $permission_section, 'view' );
		$data['filter_data'] = array_merge( (array)$data['filter_data'], $this->getPermissionObject()->getPermissionFilterData( $permission_section, 'view' ) );
		//Debug::Arr($data['filter_data']['permission_children_ids'], 'Permission Section: '. $permission_section .' Child IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		//Allow getting users from other companies, so we can change admin contacts when using the master company.
		//Need to allow -1 to be accepted for Edit Company view to not show any employees in Contact dropdowns when creating a new company.
		//But show the proper employees (for that company) in Contact dropdowns when editing an existing company.
		if ( isset($data['filter_data']['company_id'])
				AND !empty($data['filter_data']['company_id'])
				AND ( $this->getPermissionObject()->Check('company', 'enabled') AND $this->getPermissionObject()->Check('company', 'view') ) ) {
			$company_id = $data['filter_data']['company_id'];
		} else {
			$company_id = $this->getCurrentCompanyObject()->getId();
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $ulf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount() );

			$this->setPagerObject( $ulf );

			foreach( $ulf as $u_obj ) {
				//$user_data = $u_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );
				$user_data = $u_obj->getObjectAsArray( $data['filter_columns'] );

				//Hide SIN if user doesn't have permissions to see it.
				if ( isset($user_data['sin']) AND $user_data['sin'] != '' AND $this->getPermissionObject()->Check('user', 'view_sin') == FALSE ) {
					$user_data['sin'] = $u_obj->getSecureSIN();
				}

				$retarr[] = $user_data;

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $ulf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			//Debug::Arr($retarr, 'User Data: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonUserData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getUser( $data, TRUE ) ) );
	}

	/**
	 * Validate user data for one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function validateUser( $data ) {
		return $this->setUser( $data, TRUE );
	}

	/**
	 * Set user data for one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function setUser( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('user', 'enabled')
				OR !( $this->getPermissionObject()->Check('user', 'edit') OR $this->getPermissionObject()->Check('user', 'edit_own') OR $this->getPermissionObject()->Check('user', 'edit_child') OR $this->getPermissionObject()->Check('user', 'add') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
			$permission_children_ids = FALSE;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' Users', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserListFactory' );
				$lf->StartTransaction();

				//Force Company ID to current company.
				if ( !isset($row['company_id']) OR ( isset($row['company_id']) AND $row['company_id'] == '' ) OR !$this->getPermissionObject()->Check('company', 'view') ) {
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();
				}

				if ( isset($row['id']) AND $row['id'] > 0 ) {
					//Modifying existing object.
					//Get user object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $row['company_id'] );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						//Debug::Text('User ID: '. $row['id'] .' Created By: '. $lf->getCurrent()->getCreatedBy() .' Is Owner: '. (int)$this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) .' Is Child: '. (int)$this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ), __FILE__, __LINE__, __METHOD__, 10);
						if (
							$validate_only == TRUE
							OR
								(
								$this->getPermissionObject()->Check('user', 'edit')
									OR ( $this->getPermissionObject()->Check('user', 'edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
									OR ( $this->getPermissionObject()->Check('user', 'edit_child') AND $this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ) === TRUE )
								) ) {

							Debug::Text('Row Exists, getting current data: ', $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							//$row = array_merge( $lf->getCurrent()->getObjectAsArray(), $row );
							$lf = $lf->getCurrent(); //Make the current $lf variable the current object, so we can ignore some fields if needed.
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('user', 'add'), TTi18n::gettext('Add permission denied') );

					//Because password encryption requires the user_id, we need to get it first when creating a new employee.
					$row['id'] = $lf->getNextInsertId();
				}

				//When doing a mass edit of employees, user name is never specified, so we need to avoid this validation issue.
				//Generate random user name if its validate only and not otherwise specified.
				if ( $validate_only == TRUE AND ( !isset($row['user_name']) OR $row['user_name'] == '' ) ) {
					$row['user_name'] = 'random'.rand(10000000, 99999999);
				}
				//Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to save data... AMF Message ID: '. $this->getAMFMessageID(), __FILE__, __LINE__, __METHOD__, 10);

					if ( DEMO_MODE == TRUE AND $lf->isNew() == FALSE ) { //Allow changing these if DEMO is enabled, but they are adding new records.
						Debug::Text('DEMO Mode ENABLED, disable modifying some data...', __FILE__, __LINE__, __METHOD__, 10);
						unset($row['permission_control_id'], $row['status_id'], $row['phone_id'], $row['user_name'], $row['password']);
					}

					if ( $this->getPermissionObject()->Check('user', 'edit_advanced') == FALSE ) {
						Debug::Text('NOT allowing advanced edit...', __FILE__, __LINE__, __METHOD__, 10);
						//Unset all advanced fields.
						unset(
							$row['user_name'],
							$row['currency_id'],
							$row['employee_number'], //This must always be set
							$row['default_branch_id'],
							$row['default_department_id'],
							$row['group_id'],
							$row['title_id'],
							$row['first_name'],
							$row['middle_name'],
							$row['last_name'],
							$row['city'],
							$row['country'],
							$row['province'],
							$row['hire_date'],
							$row['birth_date'],
							$row['termination_date'],
							$row['sin'],
							$row['other_id1'],
							$row['other_id2'],
							$row['other_id3'],
							$row['other_id4'],
							$row['other_id5'],
							$row['note'],
							$row['tags']
						);
					}

					//If the user doesn't have permissions to change the hierarchy_control, unset that data.
					if ( isset($row['hierarchy_control']) AND ( $this->getPermissionObject()->Check('hierarchy', 'edit') OR $this->getPermissionObject()->Check('user', 'edit_hierarchy') ) ) {
						Debug::Text('Allowing change of hierarchy...', __FILE__, __LINE__, __METHOD__, 10);
					} else {
						Debug::Text('NOT allowing change of hierarchy...', __FILE__, __LINE__, __METHOD__, 10);
						unset($row['hierarchy_control']);
					}

					//Handle additional permission checks for setPermissionControl().
					if ( isset($row['permission_control_id'])
						AND ( $lf->getPermissionLevel() <= $this->getPermissionObject()->getLevel() AND ( $this->getPermissionObject()->Check('permission', 'edit') OR $this->getPermissionObject()->Check('permission', 'edit_own') OR $this->getPermissionObject()->Check('user', 'edit_permission_group') ) ) ) {
						Debug::Text('Allowing change of permissions...', __FILE__, __LINE__, __METHOD__, 10);
					} else {
						Debug::Text('NOT allowing change of permissions...', __FILE__, __LINE__, __METHOD__, 10);
						unset($row['permission_control_id']);
					}

					if ( isset($row['pay_period_schedule_id']) AND ( $this->getPermissionObject()->Check('pay_period_schedule', 'edit') OR $this->getPermissionObject()->Check('user', 'edit_pay_period_schedule') ) ) {
						Debug::Text('Allowing change of pay period schedule...', __FILE__, __LINE__, __METHOD__, 10);
					} else {
						Debug::Text('NOT allowing change of pay period schedule...', __FILE__, __LINE__, __METHOD__, 10);
						unset($row['pay_period_schedule_id']);
					}

					if ( isset($row['policy_group_id']) AND ( $this->getPermissionObject()->Check('policy_group', 'edit') OR $this->getPermissionObject()->Check('user', 'edit_policy_group') ) ) {
						Debug::Text('Allowing change of policy group...', __FILE__, __LINE__, __METHOD__, 10);
					} else {
						Debug::Text('NOT allowing change of policy group...', __FILE__, __LINE__, __METHOD__, 10);
						unset($row['policy_group_id']);
					}

					$lf->setObjectFromArray( $row );

					//This must go below setObjectFromArray.
					if ( $lf->isNew() == TRUE ) {
						//Get New Hire Defaults of the company that the user is being added too. This is critical when adding a user to a new company.
						$udlf = TTnew( 'UserDefaultListFactory' );
						$udlf->getByCompanyId( $row['company_id'] );
						if ( $udlf->getRecordCount() > 0 ) {
							$udf_obj = $udlf->getCurrent();
						}

						if ( isset($udf_obj) AND is_object($udf_obj) ) {
							if ( !isset($row['permission_control_id']) AND $udf_obj->getPermissionControl() > 0 ) {
								Debug::Text('Using default permissions...', __FILE__, __LINE__, __METHOD__, 10);
								$lf->setPermissionControl( $udf_obj->getPermissionControl() );
							}

							if ( !isset($row['pay_period_schedule_id']) AND $udf_obj->getPayPeriodSchedule() > 0 ) {
								Debug::Text('Using default pay period schedule...', __FILE__, __LINE__, __METHOD__, 10);
								$lf->setPayPeriodSchedule( $udf_obj->getPayPeriodSchedule() );
							}
						}
					}

					if ( $validate_only == TRUE ) {
						$lf->validate_only = TRUE;
					}

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $validate_only == TRUE ) {
							$save_result[$key] = TRUE;
						} else {
							$save_result[$key] = $lf->Save( TRUE, TRUE );
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
					//Always fail transaction when valididate only is used, as	is saved to different tables immediately.
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
	 * Delete one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function deleteUser( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( DEMO_MODE == TRUE ) {
			return $this->returnHandler( TRUE );
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('user', 'enabled')
				OR !( $this->getPermissionObject()->Check('user', 'delete') OR $this->getPermissionObject()->Check('user', 'delete_own') OR $this->getPermissionObject()->Check('user', 'delete_child') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text('Received data for: '. count($data) .' Users', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					if ( $this->getPermissionObject()->Check('company', 'view') == TRUE ) {
						$lf->getById( $id );//Allow deleting employees in other companies.
					} else {
						$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					}
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						//Debug::Text('User ID: '. $user['id'] .' Created By: '. $lf->getCurrent()->getCreatedBy() .' Is Owner: '. (int)$this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) .' Is Child: '. (int)$this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ), __FILE__, __LINE__, __METHOD__, 10);
						if ( $this->getPermissionObject()->Check('user', 'delete')
								OR ( $this->getPermissionObject()->Check('user', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
								OR ( $this->getPermissionObject()->Check('user', 'delete_child') AND $this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ) === TRUE )) {

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
	 * Copy one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function copyUser( $data ) {
		//Can only Copy as New, not just a regular copy, as too much data needs to be changed,
		//such as username, password, employee_number, SIN, first/last name address...
		return $this->returnHandler( FALSE );
	}

	/**
	 * Check if username is unique or not.
	 * @param string $user_name user name
	 * @return bool
	 */
	function isUniqueUserName( $user_name ) {
		Debug::Text('Checking for unique user name: '. $user_name, __FILE__, __LINE__, __METHOD__, 10);

		$uf = TTNew('UserFactory');
		$retval = $uf->isUniqueUserName( $user_name );
		return $this->returnHandler( $retval );
	}

	/**
	 * Allows currently logged in user to change their password.
	 * @param string $current_password
	 * @param string $new_password
	 * @param string $new_password2
	 * @param string $type
	 * @return bool
	 */
	function changePassword( $current_password, $new_password, $new_password2, $type = 'web' ) {
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByIdAndCompanyId( $this->getCurrentUserObject()->getId(), $this->getCurrentCompanyObject()->getId() );
		if ( $ulf->getRecordCount() == 1 ) {
			$uf = $ulf->getCurrent();

			global $authentication;
			if ( $authentication->rl->check() == FALSE ) {
				Debug::Text('Excessive failed password attempts... Preventing password change from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
				sleep(5); //Excessive password attempts, sleep longer.

				$uf->Validator->isTrue(	'current_password',
										FALSE,
										TTi18n::gettext('Current password is incorrect') .' (z)' );
			} else {
				switch ( strtolower($type) ) {
					case 'quick_punch':
					case 'phone':
						if ( $this->getPermissionObject()->Check('user', 'edit_own_phone_password') == FALSE ) {
							return $this->getPermissionObject()->PermissionDenied();
						}

						$log_description = TTi18n::getText('Password - Phone');
						if ( $current_password != '' ) {
							if ( $uf->checkPhonePassword($current_password) !== TRUE ) {
								Debug::text('Password check failed! Attempt: '. $authentication->rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
								sleep( ($authentication->rl->getAttempts() * 0.5) ); //If password is incorrect, sleep for some time to slow down brute force attacks.

								$uf->Validator->isTrue(	'current_password',
														FALSE,
														TTi18n::gettext('Current password is incorrect') );
							}
						} else {
							Debug::Text('Current password not specified', __FILE__, __LINE__, __METHOD__, 10);
							$uf->Validator->isTrue(	'current_password',
													FALSE,
													TTi18n::gettext('Current password is incorrect') );
						}

						if ( $new_password != '' OR $new_password2 != ''  ) {
							if ( $new_password === $new_password2 ) {
								$uf->setPhonePassword($new_password);
							} else {
								$uf->Validator->isTrue(	'password',
														FALSE,
														TTi18n::gettext('Passwords don\'t match') );
							}
						}
						break;
					case 'web':
						if ( $this->getPermissionObject()->Check('user', 'edit_own_password') == FALSE ) {
							return $this->getPermissionObject()->PermissionDenied();
						}

						$log_description = TTi18n::getText('Password - Web');
						if ( $current_password != '' ) {
							if ( $uf->checkPassword($current_password) !== TRUE ) {
								Debug::text('Password check failed! Attempt: '. $authentication->rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
								sleep( ($authentication->rl->getAttempts() * 0.5) ); //If password is incorrect, sleep for some time to slow down brute force attacks.
								$uf->Validator->isTrue(	'current_password',
														FALSE,
														TTi18n::gettext('Current password is incorrect') );
							}
						} else {
							Debug::Text('Current password not specified', __FILE__, __LINE__, __METHOD__, 10);
							$uf->Validator->isTrue(	'current_password',
													FALSE,
													TTi18n::gettext('Current password is incorrect') );
						}

						if ( $new_password != '' OR $new_password2 != ''  ) {
							if ( $new_password === $new_password2 ) {
								$uf->setPassword($new_password);
							} else {
								$uf->Validator->isTrue(	'password',
														FALSE,
														TTi18n::gettext('Passwords don\'t match') );
							}
						}
						break;
				}
			}

			if ( $uf->isValid() ) {
				if ( DEMO_MODE == TRUE ) {
					//Return TRUE even in demo mode, but nothing happens.
					return $this->returnHandler( TRUE );
				} else {
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 20, $log_description, NULL, $uf->getTable() );

					$authentication->rl->delete(); //Clear failed password rate limit upon successful login.

					return $this->returnHandler( $uf->Save() ); //Single valid record
				}
			} else {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $uf->Validator->getErrorsArray(), array('total_records' => 1, 'valid_records' => 0) );
			}

		}

		return $this->returnHandler( FALSE );
	}


	/**
	 * Returns a list of unique provinces that employees are assigned to.
	 * @return array
	 */
	function getUniqueUserProvinces() {
		//Get a unique list of states each employee belongs to
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByCompanyId( $this->getCurrentCompanyObject()->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$retarr[$u_obj->getProvince()] = $u_obj->getProvince();
			}
		} else {
			$retarr = FALSE;
		}

		return $retarr;
	}

	function UnsubscribeEmail( $email ) {
		if ( $email != '' AND $this->getPermissionObject()->Check('company', 'edit') ) {
			return UserFactory::UnsubscribeEmail( $email );
		}

		return FALSE;
	}


	/**
	 * Get user data for one or more users. This is an alias for getUser() that can be overridden by a plugin for getting data on remote servers.
	 * @param array $data filter data, see reference for details.
	 * @see \Modules\Users\UserListFactory::getAPISearchByCompanyIdAndArrayCriteria()
	 * @param boolean $disable_paging disables paging and returns all records.
	 * @return array
	 */
	function getCompanyUser( $data = NULL, $disable_paging = FALSE ) {
		return $this->getUser( $data, $disable_paging );
	}
}
?>
