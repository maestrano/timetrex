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
 * @package Modules\PayStub
 */
class PayStubEntryAccountFactory extends Factory {
	protected $table = 'pay_stub_entry_account';
	protected $pk_sequence_name = 'pay_stub_entry_account_id_seq'; //PK Sequence name


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('Enabled'),
										20 => TTi18n::gettext('Disabled'),
									);
				break;
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Earning'),
										20 => TTi18n::gettext('Employee Deduction'),
										30 => TTi18n::gettext('Employer Deduction'),
										40 => TTi18n::gettext('Total'),
										50 => TTi18n::gettext('Accrual'),
										//60 => TTi18n::gettext('Advance Earning'),
										//65 => TTi18n::gettext('Advance Deduction'),
										80 => TTi18n::gettext('Miscellaneous'), //Neither earnings or deductions, just for record keeping, ie: Employer parts of RRSP's, or other items that employees need to see.

									);
				break;
			case 'accrual_type':
				$retval = array(
										10 => TTi18n::gettext('Earning Subtracts, Deduction Adds'),
										20 => TTi18n::gettext('Earning Adds, Deduction Subtracts'),
									);
				break;
			case 'type_calculation_order':
				//If any of these exceed 3 digits, need to update CalculatePayStub->getDeductionObjectSortValue() to handle more digits. 
				$retval = array(
										10 => 40,
										20 => 50,
										30 => 60,
										40 => 70,
										50 => 30,
										60 => 10,
										65 => 20,
										80 => 65,
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-status' => TTi18n::gettext('Status'),
										'-1020-type' => TTi18n::gettext('Type'),
										'-1030-name' => TTi18n::gettext('Name'),

										'-1140-ps_order' => TTi18n::gettext('Order'),
										'-1150-debit_account' => TTi18n::gettext('Debit Account'),
										'-1150-credit_account' => TTi18n::gettext('Credit Account'),

										'-1900-in_use' => TTi18n::gettext('In Use'),

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
								'status',
								'type',
								'name',
								'ps_order',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								'type',
								'accrual',
								);
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
			$variable_function_map = array(
											'id' => 'ID',
											'company_id' => 'Company',
											'status_id' => 'Status',
											'status' => FALSE,
											'type_id' => 'Type',
											'type' => FALSE,
											'name' => 'Name',
											'ps_order' => 'Order',
											'debit_account' => 'DebitAccount',
											'credit_account' => 'CreditAccount',
											'accrual_pay_stub_entry_account_id' => 'Accrual',
											'accrual_type_id' => 'AccrualType',
											'in_use' => FALSE,
											'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
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

	function getStatus() {
		return (int)$this->data['status_id'];
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return TRUE;
		}

		return FALSE;
	}

	//Returns the order in which accounts should be calculated
	//given a circular dependency scenario
	function getTypeCalculationOrder() {
		if ( $this->getType() !== FALSE ) {
			$order_arr = $this->getOptions('type_calculation_order');

			if ( isset($order_arr[$this->getType()] ) ) {
				return $order_arr[$this->getType()];
			}
		}

		return FALSE;
	}

	function isInUse($id) {
		$pslf = new PayStubListFactory();
		$pself = new PayStubEntryListFactory();
		$psalf = new PayStubAmendmentListFactory();

		$ph = array(
					'pay_stub_account_id' => $id,
					'pay_stub_account_idb' => $id,
					);

		$query = '
					select	a.id
					from	'. $pself->getTable() .' as a
						LEFT JOIN '. $pslf->getTable() .' as b ON ( a.pay_stub_id = b.id )
					where	a.pay_stub_entry_name_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					UNION ALL
					select	a.id
					from	'. $psalf->getTable() .' as a
					where	a.pay_stub_entry_name_id = ? AND a.deleted = 0
					LIMIT 1';

		$retval = $this->db->GetOne($query, $ph);
		Debug::Arr($retval, 'In Use... ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $retval === FALSE ) {
			return FALSE;
		}

		return TRUE;
	}

	function getCurrentType( $id ) {
		$psealf = TTNew('PayStubEntryAccountListFactory');
		$psealf->getByIdAndCompanyId( $id, $this->getCompany() );
		if ( $psealf->getRecordCount() == 1 ) {
			$retval = $psealf->getCurrent()->getType();
			Debug::Text('Current Type: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return (int)$this->data['type_id'];
		}

		return FALSE;
	}
	function setType($type) {
		$type = (int)trim($type);

		if ( $this->Validator->inArrayKey(	'type_id',
											$type,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type'))
			) {

			Debug::Text('Type: '. $type .' isNew: '. (int)$this->isNew(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->isNew() == FALSE AND $this->getCurrentType( $this->getId() ) != $type AND $this->isInUse( $this->getId() ) == TRUE ) {
				$this->Validator->isTrue(	'type_id',
											FALSE,
											TTi18n::gettext('Type cannot be modified when Pay Stub Account is in use')
										);
			} else {
				$this->data['type_id'] = $type;
			}

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					'company_id' => $this->getCompany(),
					'type_id' => $this->getType(),
					'name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND type_id = ? AND name = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique Pay Stub Account: '. $name, __FILE__, __LINE__, __METHOD__, 10);

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
			/*I18n:	apply gettext in the result of this function
					to be use in the getByIdArray() function in
					the PayStubEntryAccountListFactory.class.php
					file.
			*/
			return TTi18n::gettext($this->data['name']);
		}

		return FALSE;
	}
	function setName($value) {
		$value = trim($value);

		if	(
				$this->Validator->isLength(		'name',
												$value,
												TTi18n::gettext('Name is too short or too long'),
												2,
												100)
				AND
				$this->Validator->isTrue(				'name',
														$this->isUniqueName($value),
														TTi18n::gettext('Name is already in use')
													)
													) {

			$this->data['name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOrder() {
		if ( isset($this->data['ps_order']) ) {
			return $this->data['ps_order'];
		}

		return FALSE;
	}
	function setOrder($value) {
		$value = trim($value);

		if ( $this->Validator->isNumeric(		'ps_order',
												$value,
												TTi18n::gettext('Invalid Order')
										) ) {


			$this->data['ps_order'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDebitAccount() {
		if ( isset($this->data['debit_account']) ) {
			return $this->data['debit_account'];
		}

		return FALSE;
	}
	function setDebitAccount($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'debit_account',
												$value,
												TTi18n::gettext('Invalid Debit Account'),
												2,
												250) ) {

			$this->data['debit_account'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getCreditAccount() {
		if ( isset($this->data['credit_account']) ) {
			return $this->data['credit_account'];
		}

		return FALSE;
	}
	function setCreditAccount($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'credit_account',
												$value,
												TTi18n::gettext('Invalid Credit Account'),
												2,
												250) ) {

			$this->data['credit_account'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrual() {
		if ( isset($this->data['accrual_pay_stub_entry_account_id']) ) {
			return (int)$this->data['accrual_pay_stub_entry_account_id'];
		}

		return FALSE;
	}
	function setAccrual($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$psealf->getByID($id);
		if ( $psealf->getRecordCount() > 0 ) {
			if ( $psealf->getCurrent()->getType() != 50 ) {
				//Reset Result set so an error occurs.
				$psealf = TTnew( 'PayStubEntryAccountListFactory' );
			}
		}

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'accrual_pay_stub_entry_account_id',
														$psealf,
														TTi18n::gettext('Accrual Account is invalid')
													) ) {

			$this->data['accrual_pay_stub_entry_account_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
	function getAccrualType() {
		if ( isset($this->data['accrual_type_id']) ) {
			return (int)$this->data['accrual_type_id'];
		}

		return FALSE;
	}
	function setAccrualType($type) {
		$type = (int)trim($type);

		if ( $this->Validator->inArrayKey(	'accrual_type_id',
											$type,
											TTi18n::gettext('Incorrect Accrual Type'),
											$this->getOptions('accrual_type')) ) {

			$this->data['accrual_type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->getType() == 50 ) {
			//If the PSE account is an accrual, it can't link to one as well.
			$this->setAccrual(NULL);
		}

		//Make sure this account doesn't point to itself as an accrual.
		if ( $this->isNew() == FALSE AND $this->getAccrual() == $this->getId() ) {
			$this->Validator->isTrue(				'accrual',
													FALSE,
													TTi18n::gettext('Accrual account is invalid')
												);
		}

		//Make sure PS order is correct, in that types can't be separated by total or accrual accounts.
		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
		$pseallf->getByCompanyId( $this->getCompany() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pseal_obj = $pseallf->getCurrent();

			$psealf = TTnew( 'PayStubEntryAccountListFactory' );
			$psealf->getByCompanyIdAndTypeId( $this->getCompany(), 40 );
			if ( $psealf->getRecordCount() > 0 ) {
				foreach( $psealf as $psea_obj ) {
					$psea_map[$psea_obj->getId()] = $psea_obj->getOrder();
				}
				unset($psea_obj);
			}

			switch( $this->getType() ) {
				case 10: //Earning
					//Greater the 0, less then Total Gross Account
					if ( isset($psea_map[$pseal_obj->getTotalGross()]) ) {
						$min_ps_order = 0;
						$max_ps_order = $psea_map[$pseal_obj->getTotalGross()];
					}
					break;
				case 20: //EE Deduction
					//Greater then Total Gross Account, less then Total Employee Deduction
					if ( isset($psea_map[$pseal_obj->getTotalGross()]) AND isset($psea_map[$pseal_obj->getTotalEmployeeDeduction()]) ) {
						$min_ps_order = $psea_map[$pseal_obj->getTotalGross()];
						$max_ps_order = $psea_map[$pseal_obj->getTotalEmployeeDeduction()];
					}
					break;
				case 30: //ER Deduction
					//Greater then Net Pay Account, less then Total Employer Deduction
					if ( isset($psea_map[$pseal_obj->getTotalNetPay()]) AND isset($psea_map[$pseal_obj->getTotalEmployerDeduction()]) ) {
						$min_ps_order = $psea_map[$pseal_obj->getTotalNetPay()];
						$max_ps_order = $psea_map[$pseal_obj->getTotalEmployerDeduction()];
					}
					break;
				case 50: //Accrual
				case 80: //Misc
					//Greater then Total Employer Deduction
					if ( isset($psea_map[$pseal_obj->getTotalEmployerDeduction()]) ) {
						$min_ps_order = $psea_map[$pseal_obj->getTotalEmployerDeduction()];
						$max_ps_order = 10001;
					}
					break;
			}

			if ( isset($min_ps_order) AND isset($max_ps_order) AND ( $this->getOrder() <= $min_ps_order OR $this->getOrder() >= $max_ps_order ) ) {
				Debug::text('PS Order... Min: '. $min_ps_order .' Max: '. $max_ps_order, __FILE__, __LINE__, __METHOD__, 10);
				$this->Validator->isTrue(				'ps_order',
														FALSE,
														TTi18n::gettext('Order is invalid for this type of account, it must be between'). ' '. ($min_ps_order + 1) . ' ' . TTi18n::gettext('and') . ' ' . ($max_ps_order - 1) );
			}
		}

		return TRUE;

	}

	function migrate( $company_id, $src_ids, $dst_id, $effective_date ) {
		$dst_id = (int)$dst_id;
		$src_ids = array_unique( (array)$src_ids );

		if ( empty($dst_id) ) {
			return FALSE;
		}

		Debug::Arr($src_ids, 'Attempting to migrate to: '. $dst_id, __FILE__, __LINE__, __METHOD__, 10);

		$current_epoch = time();

		$pself = TTNew('PayStubEntryListFactory');

		//Loop over just ACTIVE employees.
		$ulf = TTNew('UserListFactory');

		//Get names of all Pay Stub Accounts
		$psealf = TTNew('PayStubEntryAccountListFactory');
		$psealf->getByCompanyId( $company_id );
		$pay_stub_account_arr = $psealf->getArrayByListFactory( $psealf, FALSE );

		$ulf->StartTransaction();

		$ulf->getByCompanyIdAndStatus( $company_id, 10 );
		if ( is_array($pay_stub_account_arr) AND count($pay_stub_account_arr) > 0 AND $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				//Get current YTD values assigned to the src_ids.
				foreach( $src_ids as $src_id ) {
					$pse_row = $pself->getYTDAmountSumByUserIdAndEntryNameIdAndDate( $u_obj->getId(), $src_id, $current_epoch );
					if ( isset($pse_row['amount']) AND $pse_row['amount'] != 0 ) {
						Debug::Text('Found existing YTD amount for User ID: '. $u_obj->getID() .' PayStubEntryNameID: '. $src_id .' Amount: '. $pse_row['amount'], __FILE__, __LINE__, __METHOD__, 10);

						if ( isset($pay_stub_account_arr[$dst_id]) ) {
							$from_description = TTi18n::getText('Migrated YTD Amount to').': '. $pay_stub_account_arr[$dst_id];
						} else {
							$from_description = TTi18n::getText('Migrated YTD Amount to other account');
						}

						if ( isset($pay_stub_account_arr[$src_id]) ) {
							$to_description = TTi18n::getText('Migrated YTD Amount from').': '. $pay_stub_account_arr[$src_id];
						} else {
							$to_description = TTi18n::getText('Migrated YTD Amount from other account');
						}
						Debug::Text('Description: From: '. $from_description.' To: '. $to_description, __FILE__, __LINE__, __METHOD__, 10);

						//Create Pay Stub Amendments to reduce current values to 0.
						$psaf = TTNew('PayStubAmendmentFactory');
						$psaf->setStatus( 50 );
						$psaf->setType( 10 );
						$psaf->setUser( $u_obj->getID() );
						$psaf->setPayStubEntryNameId( $src_id );
						$psaf->setAmount( ( $pse_row['amount'] * -1 ) );
						$psaf->setEffectiveDate( $effective_date );
						$psaf->setDescription( $from_description );
						if ( $psaf->isValid() ) {
							$psaf->Save();
						}

						//Create Pay Stub Amendments to copy amounts to new dst_id
						$psaf = TTNew('PayStubAmendmentFactory');
						$psaf->setStatus( 50 );
						$psaf->setType( 10 );
						$psaf->setUser( $u_obj->getID() );
						$psaf->setPayStubEntryNameId( $dst_id );
						$psaf->setAmount( $pse_row['amount'] );
						$psaf->setEffectiveDate( $effective_date );
						$psaf->setDescription( $to_description );
						if ( $psaf->isValid() ) {
							$psaf->Save();
						}
					}
				}
			}
		}

		$ulf->CommitTransaction();

		return TRUE;
	}


	function preSave() {
		if ( $this->getDeleted() == TRUE ) {
			Debug::text('Attempting to delete PSE Account', __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->isInUse( $this->getId() ) ) {
				Debug::text('PSE Account is in use by Pay Stubs... Disabling instead.', __FILE__, __LINE__, __METHOD__, 10);
				$this->setDeleted(FALSE); //Can't delete, account is in use.
				$this->setStatus(20); //Disable instead
			} else {
				Debug::text('aPSE Account is NOT in use... Deleting...', __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			if ( $this->getAccrualType() == '' ) {
				$this->setAccrualType( 10 );
			}
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( 'company_id-'.$this->getCompany() );
		$this->removeCache( $this->getId() );
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
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status':
						case 'type':
						case 'accrual_type':
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Pay Stub Account'), NULL, $this->getTable(), $this );
	}
}
?>
