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
 * @package Modules\Accrual
 */
class AccrualFactory extends Factory {
	protected $table = 'accrual';
	protected $pk_sequence_name = 'accrual_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	protected $system_type_ids = array(10, 20, 75, 76); //These all special types reserved for system use only.

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Banked'), //System: Can never be deleted/edited/added
										20 => TTi18n::gettext('Used'), //System: Can never be deleted/edited/added
										30 => TTi18n::gettext('Awarded'),
										40 => TTi18n::gettext('Un-Awarded'),
										50 => TTi18n::gettext('Gift'),
										55 => TTi18n::gettext('Paid Out'),
										60 => TTi18n::gettext('Rollover Adjustment'),
										70 => TTi18n::gettext('Initial Balance'),
										75 => TTi18n::gettext('Calendar-Based Accrual Policy'), //System: Can never be added or edited.
										76 => TTi18n::gettext('Hour-Based Accrual Policy'), //System: Can never be added or edited.
										80 => TTi18n::gettext('Other')
									);
				break;
			case 'system_type':
				$retval = array_intersect_key( $this->getOptions('type'), array_flip( $this->system_type_ids ) );
				break;
			case 'add_type':
			case 'edit_type':
			case 'user_type':
				$retval = array_diff_key( $this->getOptions('type'), array_flip( $this->system_type_ids ) );
				break;
			case 'delete_type': //Types that can be deleted
				$retval = $this->getOptions('type');
				unset($retval[10], $retval[20]); //Remove just Banked/Used as those can't be deleted.
				break;
			case 'accrual_policy_type':
				$apf = TTNew('AccrualPolicyFactory');
				$retval = $apf->getOptions('type');
				break;
			case 'columns':
				$retval = array(

										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),

										'-1030-accrual_policy_account' => TTi18n::gettext('Accrual Account'),
										'-1040-type' => TTi18n::gettext('Type'),
										//'-1050-time_stamp' => TTi18n::gettext('Date'),
										'-1050-date_stamp' => TTi18n::gettext('Date'), //Date stamp is combination of time_stamp and user_date.date_stamp columns.
										'-1060-amount' => TTi18n::gettext('Amount'),

										'-1090-title' => TTi18n::gettext('Title'),
										'-1099-user_group' => TTi18n::gettext('Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('accrual_policy_account', 'type', 'date_stamp', 'amount'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'accrual_policy_account',
								'type',
								'amount',
								'date_stamp'
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_id' => 'User',
										'first_name' => FALSE,
										'last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,
										'accrual_policy_account_id' => 'AccrualPolicyAccount',
										'accrual_policy_account' => FALSE,
										'accrual_policy_id' => 'AccrualPolicy',
										'accrual_policy' => FALSE,
										'accrual_policy_type' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'user_date_total_id' => 'UserDateTotalID',
										'date_stamp' => FALSE,
										'time_stamp' => 'TimeStamp',
										'amount' => 'Amount',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();
				return $this->user_obj;
			}

			return FALSE;
		}
	}
	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'user_id',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid Employee')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrualPolicyAccount() {
		if ( isset($this->data['accrual_policy_account_id']) ) {
			return (int)$this->data['accrual_policy_account_id'];
		}

		return FALSE;
	}
	function setAccrualPolicyAccount($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$apalf = TTnew( 'AccrualPolicyAccountListFactory' );

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'accrual_policy_account',
													$apalf->getByID($id),
													TTi18n::gettext('Accrual Account is invalid')
													) ) {

			$this->data['accrual_policy_account_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrualPolicy() {
		if ( isset($this->data['accrual_policyid']) ) {
			return (int)$this->data['accrual_policy_id'];
		}

		return FALSE;
	}
	function setAccrualPolicy($id) {
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

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return (int)$this->data['type_id'];
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

			return TRUE;
		}

		return FALSE;
	}

	function isSystemType() {
		if ( in_array( $this->getType(), $this->system_type_ids ) ) {
			return TRUE;
		}

		return FALSE;
	}

	function getUserDateTotalID() {
		if ( isset($this->data['user_date_total_id']) ) {
			return (int)$this->data['user_date_total_id'];
		}

		return FALSE;
	}
	function setUserDateTotalID($id) {
		$id = trim($id);

		$udtlf = TTnew( 'UserDateTotalListFactory' );

		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'user_date_total',
															$udtlf->getByID($id),
															TTi18n::gettext('User Date Total ID is invalid')
															) ) {
			$this->data['user_date_total_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeStamp( $raw = FALSE ) {
		if ( isset($this->data['time_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['time_stamp'];
			} else {
				return TTDate::strtotime( $this->data['time_stamp'] );
			}
		}

		return FALSE;
	}
	function setTimeStamp($epoch) {
		$epoch = trim($epoch);

		if	(	$this->Validator->isDate(		'times_tamp',
												$epoch,
												TTi18n::gettext('Incorrect time stamp'))

			) {

			$this->data['time_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function isValidAmount($amount) {
		Debug::text('Type: '. $this->getType() .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
		//Based on type, set Amount() pos/neg
		switch ( $this->getType() ) {
			case 10: // Banked
			case 30: // Awarded
			case 50: // Gifted
				if ( $amount >= 0 ) {
					return TRUE;
				}
				break;
			case 20: // Used
			case 55: // Paid Out
			case 40: // Un Awarded
				if ( $amount <= 0 ) {
					return TRUE;
				}
				break;
			default:
				return TRUE;
				break;
		}

		return FALSE;

	}

	function getAmount() {
		if ( isset($this->data['amount']) ) {
			return $this->data['amount'];
		}

		return FALSE;
	}
	function setAmount($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'amount',
													$int,
													TTi18n::gettext('Incorrect Amount'))
				AND
				$this->Validator->isTrue(		'amount',
													$this->isValidAmount($int),
													TTi18n::gettext('Amount does not match type, try using a negative or positive value instead'))
				) {
			$this->data['amount'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getEnableCalcBalance() {
		if ( isset($this->calc_balance) ) {
			return $this->calc_balance;
		}

		return FALSE;
	}
	function setEnableCalcBalance($bool) {
		$this->calc_balance = $bool;

		return TRUE;
	}

	function Validate() {
		if ( $this->validate_only == FALSE ) { //Don't do the follow validation checks during Mass Edit.
			if ( $this->getUser() == FALSE OR $this->getUser() == 0 ) {
				$this->Validator->isTrue(		'user_id',
												FALSE,
												TTi18n::gettext('Please specify an employee'));
			}

			if ( $this->getType() == FALSE OR $this->getType() == 0 ) {
				$this->Validator->isTrue(		'type_id',
												FALSE,
												TTi18n::gettext('Please specify accrual type'));
			}

			if ( $this->getAccrualPolicyAccount() == FALSE OR $this->getAccrualPolicyAccount() == 0 ) {
				$this->Validator->isTrue(		'accrual_policy_account_id',
												FALSE,
												TTi18n::gettext('Please select an accrual account'));
			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getTimeStamp() == FALSE ) {
			$this->setTimeStamp( TTDate::getTime() );
		}

		//Delete duplicates before saving.
		//Or orphaned entries on Sum'ing?
		//Would have to do it on view as well though.
		if ( $this->getUserDateTotalID() > 0 ) {
			$alf = TTnew( 'AccrualListFactory' );
			$alf->getByUserIdAndAccrualPolicyAccountAndAccrualPolicyAndUserDateTotalID( $this->getUser(), $this->getAccrualPolicyAccount(), $this->getAccrualPolicy(), $this->getUserDateTotalID() );
			Debug::text('Found Duplicate Records: '. (int)$alf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $alf->getRecordCount() > 0 ) {
				foreach($alf as $a_obj ) {
					$a_obj->Delete();
				}
			}
		}

		return TRUE;
	}

	function postSave() {
		//Calculate balance
		if ( $this->getEnableCalcBalance() == TRUE ) {
			Debug::text('Calculating Balance is enabled! ', __FILE__, __LINE__, __METHOD__, 10);
			AccrualBalanceFactory::calcBalance( $this->getUser(), $this->getAccrualPolicyAccount() );
		}

		return TRUE;
	}

	static function deleteOrphans($user_id, $date_stamp ) {
		Debug::text('Attempting to delete Orphaned Records for User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
		//Remove orphaned entries
		$alf = TTnew( 'AccrualListFactory' );
		//$alf->getOrphansByUserId( $user_id );
		$alf->getOrphansByUserIdAndDate( $user_id, $date_stamp );
		Debug::text('Found Orphaned Records: '. $alf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $alf->getRecordCount() > 0 ) {
			foreach( $alf as $a_obj ) {
				Debug::text('Orphan Record ID: '. $a_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
				$accrual_policy_ids[] = $a_obj->getAccrualPolicyAccount();
				$a_obj->Delete();
			}

			//ReCalc balances
			if ( isset($accrual_policy_ids) ) {
				foreach($accrual_policy_ids as $accrual_policy_id) {
					AccrualBalanceFactory::calcBalance( $user_id, $accrual_policy_id );
				}
			}
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
						case 'user_date_total_id': //Skip this, as it should never be set from the API.
							break;
						case 'time_stamp':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'accrual_policy_account':
						case 'accrual_policy':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'accrual_policy_type':
							$data[$variable] = Option::getByKey( $this->getColumn( 'accrual_policy_type_id' ), $this->getOptions( $variable ) );
							break;
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'time_stamp':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'date_stamp': //This is a combination of the time_stamp and user_date.date_stamp columns.
							$data[$variable] = TTDate::getAPIDate( 'DATE', strtotime( $this->getColumn( $variable ) ) );
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

		//Debug::Arr($data, 'Data Object: ', __FILE__, __LINE__, __METHOD__, 10);

		return $data;
	}

	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Accrual') .' - '. TTi18n::getText('Employee').': '. $u_obj->getFullName( FALSE, TRUE ) .' '. TTi18n::getText('Type') .': '. Option::getByKey( $this->getType(), $this->getOptions('type') ) .' '. TTi18n::getText('Date') .': '.	TTDate::getDate('DATE', $this->getTimeStamp() ) .' '. TTi18n::getText('Total Time') .': '. TTDate::getTimeUnit( $this->getAmount() ), NULL, $this->getTable(), $this );
		}

		return FALSE;
	}

}
?>
