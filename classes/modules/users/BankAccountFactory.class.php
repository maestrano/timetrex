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
 * $Id: BankAccountFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\Users
 */
class BankAccountFactory extends Factory {
	protected $table = 'bank_account';
	protected $pk_sequence_name = 'bank_account_id_seq'; //PK Sequence name

	protected $user_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'ach_transaction_type': //ACH transactions require a transaction code that matches the bank account.
				$retval = array(
								22 => TTi18n::getText('Checking'),
								32 => TTi18n::getText('Savings'),
								);
				break;
			case 'columns':
				$retval = array(

										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),

										'-1090-title' => TTi18n::gettext('Title'),
										'-1099-user_group' => TTi18n::gettext('Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),

										'-5010-transit' => TTi18n::gettext('Transit/Routing'),
										'-5020-account' => TTi18n::gettext('Account'),
										'-5030-institution' => TTi18n::gettext('Institution'),

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
								'first_name',
								'last_name',
								'account',
								'institution',
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
										'user_id' => 'User',
										'first_name' => FALSE,
										'last_name' => FALSE,

										'institution' => 'Institution',
										'transit' => 'Transit',
										'account' => 'Account',

										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

    function getUserObject() {
        return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
    }
	
	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

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

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid Employee')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUnique() {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		if ( $this->getUser() > 0 ) {
			$ph = array(
						'company_id' =>  (int)$this->getCompany(),
						'user_id' => (int)$this->getUser(),
						);

			$query = 'select id from '. $this->getTable() .' where company_id = ? AND user_id = ? AND deleted = 0';
		} else {
			$ph = array(
						'company_id' =>  (int)$this->getCompany(),
						);

			$query = 'select id from '. $this->getTable() .' where company_id = ? AND user_id is NULL AND deleted = 0';
		}
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id,'Unique ID: '. $id .' Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getInstitution() {
		if ( isset($this->data['institution']) ) {
			return $this->data['institution'];
		}

		return FALSE;
	}
	function setInstitution($value) {
		$value = trim($value);

		if (
				$value == ''
				OR
				(
					$this->Validator->isNumeric(	'institution',
													$value,
													TTi18n::gettext('Invalid institution number, must be digits only'))
					AND
					$this->Validator->isLength(		'institution',
													$value,
													TTi18n::gettext('Invalid institution number length'),
													2,
													3)
				)
			) {

			$this->data['institution'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getTransit() {
		if ( isset($this->data['transit']) ) {
			return $this->data['transit'];
		}

		return FALSE;
	}
	function setTransit($value) {
		$value = trim($value);

		if (
						$this->Validator->isNumeric(	'transit',
														$value,
														TTi18n::gettext('Invalid transit number, must be digits only'))
				AND
						$this->Validator->isLength(		'transit',
														$value,
														TTi18n::gettext('Invalid transit number length'),
														2,
														15)
			) {

			$this->data['transit'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getSecureAccount( $value = NULL ) {
		if ( $value == '' ) {
			$value = $this->getAccount();
		}

		//Replace the middle digits leaving only 2 digits on each end, or just 1 digit on each end if the account is too short.
		$replace_length = ( (strlen($value)-4) >= 4 ) ? strlen($value)-4 : 3;
		$start_digit = ( strlen($value) >= 7 ) ? 2 : 1;

		$account = str_replace( substr($value, $start_digit, $replace_length), str_repeat('X', $replace_length) , $value );
		return $account;
	}
	function getAccount() {
		if ( isset($this->data['account']) ) {
			return $this->data['account'];
		}

		return FALSE;
	}
	function setAccount($value) {
		//If *'s are in the account number, skip setting it
		//This allows them to change other data without seeing the account number.
		if ( stripos( $value, 'X') !== FALSE  ) {
			return FALSE;
		}

		$value = $this->Validator->stripNonNumeric( trim($value) );
		if (
						$this->Validator->isLength(		'account',
														$value,
														TTi18n::gettext('Invalid account number length'),
														3,
														20)
			) {

			$this->data['account'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->getAccount() == FALSE ) {
			$this->Validator->isTRUE(		'account',
											FALSE,
											TTi18n::gettext('Bank account not specified') );
		}

		//Make sure this entry is unique.
		if ( $this->getDeleted() == FALSE AND $this->isUnique() == FALSE ) {
			$this->Validator->isTRUE(		'user_id',
											FALSE,
											TTi18n::gettext('Bank account already exists for this employee') );

			return FALSE;
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getUser() == FALSE ) {
			Debug::Text('Clearing User value, because this is strictly a company record', __FILE__, __LINE__, __METHOD__,10);
			//$this->setUser( 0 ); //COMPANY record.
		}

		//PGSQL has a NOT NULL constraint on Instituion number prior to schema v1014A.
		if ( $this->getInstitution() == FALSE ) {
			$this->setInstitution( '000' );
		}

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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'account':
							$data[$variable] = $this->getSecureAccount();
							break;
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'currency':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		if ( $this->getUser() == '' ) {
			$log_description = TTi18n::getText('Company');
		} else {
			$log_description = TTi18n::getText('Employee');

			$u_obj = $this->getUserObject();
			if ( is_object($u_obj) ) {
				$log_description .= ': '. $u_obj->getFullName(FALSE, TRUE);
			}
		}
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Bank Account') .' - '. $log_description, NULL, $this->getTable(), $this );
	}

}
?>
