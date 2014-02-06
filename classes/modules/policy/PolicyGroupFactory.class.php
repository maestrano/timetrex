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
 * $Revision: 11018 $
 * $Id: PolicyGroupFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\Policy
 */
class PolicyGroupFactory extends Factory {
	protected $table = 'policy_group';
	protected $pk_sequence_name = 'policy_group_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1000-name' => TTi18n::gettext('Name'),
										'-1100-total_users' => TTi18n::gettext('Employees'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'total_users',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								'user',
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'name' => 'Name',
										'user' => 'User',
										'total_users' => 'TotalUsers',
										'over_time_policy' => 'OverTimePolicy',
										'round_interval_policy' => 'RoundIntervalPolicy',
										'premium_policy' => 'PremiumPolicy',
										'meal_policy' => 'MealPolicy',
										'break_policy' => 'BreakPolicy',
										'holiday_policy' => 'HolidayPolicy',
										'accrual_policy' => 'AccrualPolicy',
                                        'expense_policy' => 'ExpensePolicy',
                                        'absence_policy' => 'AbsencePolicy',
										'exception_policy_control_id' => 'ExceptionPolicyControlID',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = TTnew( 'CompanyListFactory' );
			$this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

			return $this->company_obj;
		}
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					'company_id' => $this->getCompany(),
					'name' => strtolower($name),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id,'Unique: '. $name, __FILE__, __LINE__, __METHOD__,10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name) {
		$name = trim($name);
		if (	$this->Validator->isLength(	'name',
											$name,
											TTi18n::gettext('Name is too short or too long'),
											2,50)
				AND
				$this->Validator->isTrue(	'name',
											$this->isUniqueName($name),
											TTi18n::gettext('Name is already in use') )
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		$pgulf = TTnew( 'PolicyGroupUserListFactory' );
		$pgulf->getByPolicyGroupId( $this->getId() );
		foreach ($pgulf as $obj) {
			$list[] = $obj->getUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setUser($ids) {
		if ( !is_array($ids) ) {
			$ids = array($ids);
		}

		if ( is_array($ids) ) {
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$pgulf = TTnew( 'PolicyGroupUserListFactory' );
				$pgulf->getByPolicyGroupId( $this->getId() );

				$tmp_ids = array();
				foreach ($pgulf as $obj) {
					$id = $obj->getUser();
					Debug::text('Policy ID: '. $obj->getPolicyGroup() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$ulf = TTnew( 'UserListFactory' );
			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$pguf = TTnew( 'PolicyGroupUserFactory' );
					$pguf->setPolicyGroup( $this->getId() );
					$pguf->setUser( $id );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						if ($this->Validator->isTrue(		'user',
															$pguf->Validator->isValid(),
															TTi18n::gettext('Selected employee is invalid or already assigned to another policy group').' ('. $obj->getFullName() .')' )) {
							$pguf->save();
						}
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getTotalUsers() {
		$pgulf = TTnew( 'PolicyGroupUserListFactory' );
		$pgulf->getByPolicyGroupId( $this->getId() );
		return $pgulf->getRecordCount();
	}

	function getOverTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 110, $this->getID() );
	}
	function setOverTimePolicy($ids) {
		Debug::text('Setting OverTime Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 110, $this->getID(), $ids );
	}

	function getPremiumPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 120, $this->getID() );
	}
	function setPremiumPolicy($ids) {
		Debug::text('Setting Premium Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 120, $this->getID(), $ids );
	}

	function getRoundIntervalPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 130, $this->getID() );
	}
	function setRoundIntervalPolicy($ids) {
		Debug::text('Setting Round Interval Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 130, $this->getID(), $ids );
	}

	function getAccrualPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 140, $this->getID() );
	}
	function setAccrualPolicy($ids) {
		Debug::text('Setting Accrual Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 140, $this->getID(), $ids );
	}

	function getMealPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 150, $this->getID() );
	}
	function setMealPolicy($ids) {
		Debug::text('Setting Meal Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 150, $this->getID(), $ids );
	}

	function getBreakPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 160, $this->getID() );
	}
	function setBreakPolicy($ids) {
		Debug::text('Setting Break Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 160, $this->getID(), $ids );
	}

    function getAbsencePolicy() {
        return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 170, $this->getID() );
    }

    function setAbsencePolicy($ids) {
        Debug::text('Setting Absence Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 170, $this->getID(), (array)$ids );
    }

	function getHolidayPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 180, $this->getID() );
	}
	function setHolidayPolicy($ids) {
		Debug::text('Setting Holiday Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 180, $this->getID(), (array)$ids );
	}

    function getExpensePolicy() {
        return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 200, $this->getID() );
    }

    function setExpensePolicy($ids) {
        Debug::text('Setting Expense Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 200, $this->getID(), (array)$ids );
    }


	function getExceptionPolicyControlID() {
		if ( isset($this->data['exception_policy_control_id']) ) {
			return $this->data['exception_policy_control_id'];
		}

		return FALSE;
	}
	function setExceptionPolicyControlID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$epclf = TTnew( 'ExceptionPolicyControlListFactory' );

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'exception_policy',
														$epclf->getByID($id),
														TTi18n::gettext('Exception Policy is invalid')
													) ) {

			$this->data['exception_policy_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('UnAssign Policy Group from User Defaults...'. $this->getId(), __FILE__, __LINE__, __METHOD__,10);
			$udf = TTnew( 'UserDefaultFactory' );

			$query = 'update '. $udf->getTable() .' set policy_group_id = 0 where company_id = '. (int)$this->getCompany() .' AND policy_group_id = '. (int)$this->getId();
			$this->db->Execute($query);
		}

		return TRUE;
	}

	//Support setting created_by,updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
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

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						//case 'total_users':
						//	$data[$variable] = $this->getColumn( $variable );
						//	break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  TTi18n::getText('Policy Group'), NULL, $this->getTable(), $this );
	}
}
?>
