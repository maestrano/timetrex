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
 * $Revision: 9521 $
 * $Id: AbsencePolicyFactory.class.php 9521 2013-04-08 23:09:52Z ipso $
 * $Date: 2013-04-08 16:09:52 -0700 (Mon, 08 Apr 2013) $
 */

/**
 * @package Modules\Policy
 */
class AbsencePolicyFactory extends Factory {
	protected $table = 'absence_policy';
	protected $pk_sequence_name = 'absence_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Paid'),
										12 => TTi18n::gettext('Paid (Above Salary)'),
										20 => TTi18n::gettext('Unpaid'),
										30 => TTi18n::gettext('Dock'),
									);
				break;
			case 'paid_type': //Types that are considered paid.
				$retval = array(10,12);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => TTi18n::gettext('Type'),
										'-1020-name' => TTi18n::gettext('Name'),

										'-1030-rate' => TTi18n::gettext('Rate'),

										'-1080-accrual_policy' => TTi18n::gettext('Accrual Policy'),
										'-1090-accrual_rate' => TTi18n::gettext('Accrual Rate'),

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
								'type',
								'name',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
			$variable_function_map = array(
											'id' => 'ID',
											'company_id' => 'Company',
											'type_id' => 'Type',
											'type' => FALSE,
											'name' => 'Name',
											'rate' => 'Rate',
											'wage_group_id' => 'WageGroup',
											'accrual_rate' => 'AccrualRate',
											'accrual_policy_id' => 'AccrualPolicyID',
											'accrual_policy' => FALSE,
											'pay_stub_entry_account_id' => 'PayStubEntryAccountId',
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

	function isPaid() {
		if ( in_array( $this->getType(), $this->getOptions('paid_type') ) ) {
			return TRUE;
		}
		return FALSE;
	}
	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$value,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return FALSE;
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

	function getHourlyRate( $hourly_rate ) {
		if ( $this->getType() == 20 ) { //Unpaid
			$rate = 0;
		} elseif( $this->getType() == 30 ) { //Dock
			$rate = $this->getRate()*-1;
		} else {
			$rate = $this->getRate();
		}
		return bcmul( $hourly_rate, $rate );
	}

	function getRate() {
		if ( isset($this->data['rate']) ) {
			return $this->data['rate'];
		}

		return FALSE;
	}
	function setRate($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isFloat(		'rate',
												$int,
												TTi18n::gettext('Incorrect Rate')) ) {
			$this->data['rate'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getWageGroup() {
		if ( isset($this->data['wage_group_id']) ) {
			return $this->data['wage_group_id'];
		}

		return FALSE;
	}
	function setWageGroup($id) {
		$id = trim($id);

		$wglf = TTnew( 'WageGroupListFactory' );

		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'wage_group',
													$wglf->getByID($id),
													TTi18n::gettext('Wage Group is invalid')
													) ) {

			$this->data['wage_group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrualRate() {
		if ( isset($this->data['accrual_rate']) ) {
			return $this->data['accrual_rate'];
		}

		return FALSE;
	}
	function setAccrualRate($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isFloat(		'accrual_rate',
												$int,
												TTi18n::gettext('Incorrect Accrual Rate')) ) {
			$this->data['accrual_rate'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrualPolicyID() {
		if ( isset($this->data['accrual_policy_id']) ) {
			return $this->data['accrual_policy_id'];
		}

		return FALSE;
	}
	function setAccrualPolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$aplf = TTnew( 'AccrualPolicyListFactory' );

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'accrual_policy',
													$aplf->getByID($id),
													TTi18n::gettext('Accrual Policy is invalid')
													) ) {

			$this->data['accrual_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayStubEntryAccountId() {
		if ( isset($this->data['pay_stub_entry_account_id']) ) {
			return $this->data['pay_stub_entry_account_id'];
		}

		return FALSE;
	}
	function setPayStubEntryAccountId($id) {
		$id = trim($id);

		Debug::text('Entry Account ID: '. $id , __FILE__, __LINE__, __METHOD__,10);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );

		if (	$id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'pay_stub_entry_account_id',
														$psealf->getById($id),
														TTi18n::gettext('Invalid Pay Stub Account')
														) ) {
			$this->data['pay_stub_entry_account_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->getDeleted() == TRUE ) {
			//Check to make sure there are no hours using this OT policy.
			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getByAbsencePolicyId( $this->getId() );
			if ( $udtlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This absence policy is in use'));

			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getWageGroup() === FALSE ) {
			$this->setWageGroup( 0 );
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

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
						case 'accrual_policy':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
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
		return TTLog::addEntry( $this->getId(), $log_action,  TTi18n::getText('Absence Policy'), NULL, $this->getTable(), $this );
	}
}
?>
