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

require_once( 'Numbers/Words.php');

/**
 * @package Modules\PayStubAmendment
 */
class PayStubAmendmentFactory extends Factory {
	protected $table = 'pay_stub_amendment';
	protected $pk_sequence_name = 'pay_stub_amendment_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $pay_stub_entry_account_link_obj = NULL;
	var $pay_stub_entry_name_obj = NULL;
	var $pay_stub_obj = NULL;
	var $percent_amount_entry_name_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'filtered_status':
				//Select box options;
				$status_options_filter = array(50);
				if ( $this->getStatus() == 55 ) {
					$status_options_filter = array(55);
				} elseif ( $this->getStatus() == 52 ) {
					$status_options_filter = array(52);
				}

				$retval = Option::getByArray( $status_options_filter, $this->getOptions('status') );
				break;
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('NEW'),
										20 => TTi18n::gettext('OPEN'),
										30 => TTi18n::gettext('PENDING AUTHORIZATION'),
										40 => TTi18n::gettext('AUTHORIZATION OPEN'),
										50 => TTi18n::gettext('ACTIVE'),
										52 => TTi18n::gettext('IN USE'),
										55 => TTi18n::gettext('PAID'),
										60 => TTi18n::gettext('DISABLED')
									);
				break;
			case 'type':
				$retval = array(
											10 => TTi18n::gettext('Fixed'),
											20 => TTi18n::gettext('Percent')
										);
				break;
			case 'pay_stub_account_type':
				$retval = array(10, 20, 30, 50, 60, 65, 80);
				break;
			case 'percent_pay_stub_account_type':
				$retval = array(10, 20, 30, 40, 50, 60, 65, 80);
				break;
			case 'export_type':
			case 'export_eft':
			case 'export_cheque':
				$psf = TTNew('PayStubFactory');
				$retval = $psf->getOptions( $name );
				break;
			case 'columns':
				$retval = array(
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-user_status' => TTi18n::gettext('Employee Status'),
										'-1010-title' => TTi18n::gettext('Title'),
										'-1020-user_group' => TTi18n::gettext('Group'),
										'-1030-default_branch' => TTi18n::gettext('Default Branch'),
										'-1040-default_department' => TTi18n::gettext('Default Department'),

										'-1110-status' => TTi18n::gettext('Status'),
										'-1120-type' => TTi18n::gettext('Type'),
										'-1130-pay_stub_entry_name' => TTi18n::gettext('Account'),
										'-1140-effective_date' => TTi18n::gettext('Effective Date'),
										'-1150-amount' => TTi18n::gettext('Amount'),
										'-1160-rate' => TTi18n::gettext('Rate'),
										'-1170-units' => TTi18n::gettext('Units'),
										'-1180-description' => TTi18n::gettext('Pay Stub Note (Public)'),
										'-1182-private_description' => TTi18n::gettext('Description (Private)'),
										'-1190-ytd_adjustment' => TTi18n::gettext('YTD Adjustment'),

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
								'status',
								'pay_stub_entry_name',
								'effective_date',
								'amount',
								'description',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
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
										'user_id' => 'User',

										'first_name' => FALSE,
										'last_name' => FALSE,
										'user_status_id' => FALSE,
										'user_status' => FALSE,
										'group_id' => FALSE,
										'user_group' => FALSE,
										'title_id' => FALSE,
										'title' => FALSE,
										'default_branch_id' => FALSE,
										'default_branch' => FALSE,
										'default_department_id' => FALSE,
										'default_department' => FALSE,

										'pay_stub_entry_name_id' => 'PayStubEntryNameId',
										'pay_stub_entry_name' => FALSE,
										'recurring_ps_amendment_id' => 'RecurringPayStubAmendmentId',
										'effective_date' => 'EffectiveDate',
										'status_id' => 'Status',
										'status' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'rate' => 'Rate',
										'units' => 'Units',
										'amount' => 'Amount',
										'percent_amount' => 'PercentAmount',
										'percent_amount_entry_name_id' => 'PercentAmountEntryNameId',
										'ytd_adjustment' => 'YTDAdjustment',
										'description' => 'Description',
										'private_description' => 'PrivateDescription',
										'authorized' => 'Authorized',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getPayStubObject() {
		if ( is_object($this->pay_stub_obj) ) {
			return $this->pay_stub_obj;
		} else {
			$pslf = TTnew( 'PayStubListFactory' );
			$pslf->getByUserIdAndPayStubAmendmentId( $this->getUser(), $this->getID() );
			if ( $pslf->getRecordCount() > 0 ) {
				$this->pay_stub_obj = $pslf->getCurrent();
				return $this->pay_stub_obj;
			}

			return FALSE;
		}
	}

	function getPayStubEntryAccountLinkObject() {
		if ( is_object($this->pay_stub_entry_account_link_obj) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
			$pseallf->getByCompanyID( $this->getUserObject()->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();
				return $this->pay_stub_entry_account_link_obj;
			}

			return FALSE;
		}
	}

	function getPayStubEntryNameObject() {
		if ( is_object($this->pay_stub_entry_name_obj) ) {
			return $this->pay_stub_entry_name_obj;
		} else {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' );
			$psealf->getByID( $this->getPayStubEntryNameId() );
			if ( $psealf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_name_obj = $psealf->getCurrent();
				return $this->pay_stub_entry_name_obj;
			}

			return FALSE;
		}
	}

	function getPercentAmountEntryNameObject() {
		if ( is_object($this->percent_amount_entry_name_obj) ) {
			return $this->percent_amount_entry_name_obj;
		} else {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' );
			$psealf->getByID( $this->getPercentAmountEntryNameId() );
			if ( $psealf->getRecordCount() > 0 ) {
				$this->percent_amount_entry_name_obj = $psealf->getCurrent();
				return $this->percent_amount_entry_name_obj;
			}

			return FALSE;
		}
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}

		return FALSE;
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

	function getPayStubEntryNameId() {
		if ( isset($this->data['pay_stub_entry_name_id']) ) {
			return (int)$this->data['pay_stub_entry_name_id'];
		}

		return FALSE;
	}
	function setPayStubEntryNameId($id) {
		$id = trim($id);

		//$psenlf = TTnew( 'PayStubEntryNameListFactory' );
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$result = $psealf->getById( $id );
		//Debug::Arr($result, 'Result: ID: '. $id .' Rows: '. $result->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

		if (  $this->Validator->isResultSetWithRows(	'pay_stub_entry_name_id',
														$result,
														TTi18n::gettext('Invalid Pay Stub Account')
														) ) {

			$this->data['pay_stub_entry_name_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function setName($name) {
		$name = trim($name);

		$psenlf = TTnew( 'PayStubEntryNameListFactory' );
		$result = $psenlf->getByName($name);

		if (  $this->Validator->isResultSetWithRows(	'name',
														$result,
														TTi18n::gettext('Invalid Entry Name')
														) ) {

			$this->data['pay_stub_entry_name_id'] = $result->getCurrent()->getId();

			return TRUE;
		}

		return FALSE;
	}

	function getRecurringPayStubAmendmentId() {
		if ( isset($this->data['recurring_ps_amendment_id']) ) {
			return (int)$this->data['recurring_ps_amendment_id'];
		}

		return FALSE;
	}
	function setRecurringPayStubAmendmentId($id) {
		$id = trim($id);

		$rpsalf = TTnew( 'RecurringPayStubAmendmentListFactory' );
		$rpsalf->getById( $id );
		//Not sure why we tried to use $result here, as if the ID passed is NULL, it causes a fatal error.
		//$result = $rpsalf->getById( $id )->getCurrent();

		if (	( $id == NULL OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'recurring_ps_amendment_id',
														$rpsalf,
														TTi18n::gettext('Invalid Recurring Pay Stub Amendment ID')
														) ) {

			$this->data['recurring_ps_amendment_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getEffectiveDate() {
		if ( isset($this->data['effective_date']) ) {
			return $this->data['effective_date'];
		}

		return FALSE;
	}
	function setEffectiveDate($epoch) {
		$epoch = trim($epoch);

		//Adjust effective date, because we won't want it to be a
		//day boundary and have issues with pay period start/end dates.
		//Although with employees in timezones that differ from the pay period timezones, there can still be issues.
		$epoch = TTDate::getMiddleDayEpoch( $epoch );

		if	(	$this->Validator->isDate(		'effective_date',
												$epoch,
												TTi18n::gettext('Incorrect effective date')) ) {

			$this->data['effective_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
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

			return FALSE;
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
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$type,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $type;

			return FALSE;
		}

		return FALSE;
	}

	function getRate() {
		if ( isset($this->data['rate']) ) {
			return $this->data['rate'];
		}

		return NULL;
	}
	function setRate($value) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if ($value == 0 OR $value == '') {
			$value = NULL;
		}

		if (	empty($value) OR
				(
				$this->Validator->isFloat(				'rate',
														$value,
														TTi18n::gettext('Invalid Rate') )
				AND
				$this->Validator->isLength(				'rate',
											$value,
											TTi18n::gettext('Rate has too many digits'),
											0,
											21) //Need to include decimal.
				AND
				$this->Validator->isLengthBeforeDecimal('rate',
											$value,
											TTi18n::gettext('Rate has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'rate',
											$value,
											TTi18n::gettext('Rate has too many digits after the decimal'),
											0,
											4)
				)
			) {
			Debug::text('Setting Rate to: '. $value, __FILE__, __LINE__, __METHOD__, 10);
			//Must round to 2 decimals otherwise discreptancy can occur when generating pay stubs.
			//$this->data['rate'] = Misc::MoneyFormat( $value, FALSE );
			$this->data['rate'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUnits() {
		if ( isset($this->data['units']) ) {
			return $this->data['units'];
		}

		return NULL;
	}
	function setUnits($value) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if ($value == 0 OR $value == '') {
			$value = NULL;
		}

		if (	empty($value) OR
				(
				$this->Validator->isFloat(				'units',
														$value,
														TTi18n::gettext('Invalid Units') )
				AND
				$this->Validator->isLength(				'units',
											$value,
											TTi18n::gettext('Units has too many digits'),
											0,
											21) //Need to include decimal
				AND
				$this->Validator->isLengthBeforeDecimal('units',
											$value,
											TTi18n::gettext('Units has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'units',
											$value,
											TTi18n::gettext('Units has too many digits after the decimal'),
											0,
											4)
				)
			) {
			//Must round to 2 decimals otherwise discreptancy can occur when generating pay stubs.
			//$this->data['units'] = Misc::MoneyFormat( $value, FALSE );
			$this->data['units'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPayStubId() {
		//Find which pay period this effective date belongs too
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getByUserIdAndEndDate( $this->getUser(), $this->getEffectiveDate() );
		if ( $pplf->getRecordCount() > 0 ) {
			$pp_obj = $pplf->getCurrent();
			Debug::text('Found Pay Period ID: '. $pp_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

			//Percent PS amendments can't work on advances.
			$pslf = TTnew( 'PayStubListFactory' );
			$pslf->getByUserIdAndPayPeriodIdAndAdvance( $this->getUser(), $pp_obj->getId(), FALSE );
			if ( $pslf->getRecordCount() > 0 ) {
				$ps_obj = $pslf->getCurrent();
				Debug::text('Found Pay Stub for this effective date: '. $ps_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

				return $ps_obj->getId();
			}
		}

		return FALSE;
	}

	function getPayStubEntryAmountSum( $pay_stub_obj, $ids ) {
		if ( !is_object($pay_stub_obj) ) {
			return FALSE;
		}

		if ( !is_array($ids) ) {
			return FALSE;
		}

		$pself = TTnew( 'PayStubEntryListFactory' );

		//Get Linked accounts so we know which IDs are totals.
		$total_gross_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalGross(), $ids);
		if ( $total_gross_key !== FALSE ) {
			$type_ids[] = 10;
			$type_ids[] = 60; //Automatically inlcude Advance Earnings here?
			unset($ids[$total_gross_key]);
		}
		unset($total_gross_key);

		$total_employee_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction(), $ids);
		if ( $total_employee_deduction_key !== FALSE ) {
			$type_ids[] = 20;
			unset($ids[$total_employee_deduction_key]);
		}
		unset($total_employee_deduction_key);

		$total_employer_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction(), $ids);
		if ( $total_employer_deduction_key !== FALSE ) {
			$type_ids[] = 30;
			unset($ids[$total_employer_deduction_key]);
		}
		unset($total_employer_deduction_key);

		$type_amount_arr['amount'] = 0;
		if ( isset($type_ids) ) {
			//$type_amount_arr = $pself->getSumByPayStubIdAndType( $pay_stub_id, $type_ids );
			$type_amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', $type_ids );
		}

		$amount_arr['amount'] = 0;
		if ( count($ids) > 0 ) {
			//Still other IDs left to total.
			//$amount_arr = $pself->getAmountSumByPayStubIdAndEntryNameID( $pay_stub_id, $ids );
			$amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $ids );
		}

		$retval = bcadd($type_amount_arr['amount'], $amount_arr['amount'] );

		Debug::text('Type Amount: '. $type_amount_arr['amount'] .' Regular Amount: '. $amount_arr['amount'] .' Total: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getCalculatedAmount( $pay_stub_obj ) {
		if ( !is_object($pay_stub_obj) ) {
			return FALSE;
		}

		if ( $this->getType() == 10 ) {
			//Fixed
			return $this->getAmount();
		} else {
			//Percent
			if ( $this->getPercentAmountEntryNameId() != '' ) {
				$ps_amendment_percent_amount = $this->getPayStubEntryAmountSum( $pay_stub_obj, array($this->getPercentAmountEntryNameId()) );

				$pay_stub_entry_account = $pay_stub_obj->getPayStubEntryAccountArray( $this->getPercentAmountEntryNameId() );
				if ( isset($pay_stub_entry_account['type_id']) AND $pay_stub_entry_account['type_id'] == 50 ) {
					//Get balance amount from previous pay stub so we can include that in our percent calculation.
					$previous_pay_stub_amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, array($this->getPercentAmountEntryNameId()) );

					$ps_amendment_percent_amount = bcadd( $ps_amendment_percent_amount, $previous_pay_stub_amount_arr['ytd_amount']);
					Debug::text('Pay Stub Amendment is a Percent of an Accrual, add previous pay stub accrual balance to amount: '. $previous_pay_stub_amount_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10);
				}
				unset($pay_stub_entry_account, $previous_pay_stub_amount_arr);

				Debug::text('Pay Stub Amendment Total Amount: '. $ps_amendment_percent_amount .' Percent Amount: '. $this->getPercentAmount(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $ps_amendment_percent_amount != 0 AND $this->getPercentAmount() != 0 ) { //Allow negative values.
					$amount = bcmul($ps_amendment_percent_amount, bcdiv($this->getPercentAmount(), 100) );

					return $amount;
				}
			}
		}

		return FALSE;
	}

	function getAmount() {
		if ( isset($this->data['amount']) ) {
			return Misc::removeTrailingZeros( $this->data['amount'], 2);
		}

		return NULL;
	}
	function setAmount($value) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		Debug::text('Amount: '. $value .' Name: '. $this->getPayStubEntryNameId(), __FILE__, __LINE__, __METHOD__, 10);

		if ($value == NULL OR $value == '') {
			return FALSE;
		}

		if (  $this->Validator->isFloat(				'amount',
														$value,
														TTi18n::gettext('Invalid Amount') )
				AND
				$this->Validator->isLength(				'amount',
											$value,
											TTi18n::gettext('Amount has too many digits'),
											0,
											21) //Need to include decimal
				AND
				$this->Validator->isLengthBeforeDecimal('amount',
											$value,
											TTi18n::gettext('Amount has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'amount',
											$value,
											TTi18n::gettext('Amount has too many digits after the decimal'),
											0,
											4)
			) {
			$this->data['amount'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPercentAmount() {
		if ( isset($this->data['percent_amount']) ) {
			return $this->data['percent_amount'];
		}

		return NULL;
	}
	function setPercentAmount($value) {
		$value = trim($value);

		Debug::text('Amount: '. $value .' Name: '. $this->getPayStubEntryNameId(), __FILE__, __LINE__, __METHOD__, 10);

		if ($value == NULL OR $value == '') {
			return FALSE;
		}

		if (  $this->Validator->isFloat(				'percent_amount',
														$value,
														TTi18n::gettext('Invalid Percent')
														) ) {
			$this->data['percent_amount'] = round( $value, 2);

			return TRUE;
		}
		return FALSE;
	}

	function getPercentAmountEntryNameId() {
		if ( isset($this->data['percent_amount_entry_name_id']) ) {
			return (int)$this->data['percent_amount_entry_name_id'];
		}

		return FALSE;
	}
	function setPercentAmountEntryNameId($id) {
		$id = trim($id);

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$psealf->getById( $id );
		//Not sure why we tried to use $result here, as if the ID passed is NULL, it causes a fatal error.
		//$result = $psealf->getById( $id )->getCurrent();

		if (	( $id == NULL OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'percent_amount_entry_name',
														$psealf,
														TTi18n::gettext('Invalid Percent Of')
														) ) {

			$this->data['percent_amount_entry_name_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($text) {
		$text = trim($text);

		if	(	strlen($text) == 0
				OR
				$this->Validator->isLength(		'description',
												$text,
												TTi18n::gettext('Invalid Description Length'),
												2,
												100) ) {

			$this->data['description'] = htmlspecialchars( $text );

			return TRUE;
		}

		return FALSE;
	}

	function getPrivateDescription() {
		if ( isset($this->data['private_description']) ) {
			return $this->data['private_description'];
		}

		return FALSE;
	}
	function setPrivateDescription($text) {
		$text = trim($text);

		if	(	strlen($text) == 0
				OR
				$this->Validator->isLength(		'description',
												$text,
												TTi18n::gettext('Invalid Description Length'),
												2,
												250) ) {

			$this->data['private_description'] = htmlspecialchars( $text );

			return TRUE;
		}

		return FALSE;
	}

	function getAuthorized() {
		if ( isset($this->data['authorized']) ) {
			return $this->fromBool( $this->data['authorized'] );
		}

		return FALSE;
	}
	function setAuthorized($bool) {
		$this->data['authorized'] = $this->toBool($bool);

		return TRUE;
	}

	function getYTDAdjustment() {
		if ( isset($this->data['ytd_adjustment']) ) {
			return $this->fromBool( $this->data['ytd_adjustment'] );
		}

		return FALSE;
	}
	function setYTDAdjustment($bool) {
		$this->data['ytd_adjustment'] = $this->toBool($bool);

		return TRUE;
	}

	//Used to determine if the pay stub is changing the status, so we can ignore some validation checks.
	function getEnablePayStubStatusChange() {
		if ( isset($this->pay_stub_status_change) ) {
			return $this->pay_stub_status_change;
		}

		return FALSE;
	}
	function setEnablePayStubStatusChange($bool) {
		$this->pay_stub_status_change = $bool;

		return TRUE;
	}

	static function releaseAllAccruals($user_id, $effective_date = NULL) {
		Debug::Text('Release 100% of all accruals!', __FILE__, __LINE__, __METHOD__, 10);

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $effective_date == '' ) {
			$effective_date = TTDate::getTime();
		}
		Debug::Text('Effective Date: '. TTDate::getDate('DATE+TIME', $effective_date), __FILE__, __LINE__, __METHOD__, 10);

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();
		} else {
			return FALSE;
		}

		//Get all PSE acccount accruals
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$psealf->getByCompanyIdAndStatusIdAndTypeId( $user_obj->getCompany(), 10, 50);
		if ( $psealf->getRecordCount() > 0 ) {
			$ulf->StartTransaction();
			foreach( $psealf as $psea_obj ) {
				//Get PSE account that affects this accrual.
				//What if there are two accounts? It takes the first one in the list.
				$psealf_tmp = TTnew( 'PayStubEntryAccountListFactory' );
				$psealf_tmp->getByCompanyIdAndAccrualId( $user_obj->getCompany(), $psea_obj->getId() );
				if ( $psealf_tmp->getRecordCount() > 0 ) {
					$release_account_id = $psealf_tmp->getCurrent()->getId();

					$psaf = TTnew( 'PayStubAmendmentFactory' );
					$psaf->setStatus( 50 ); //Active
					$psaf->setType( 20 ) ; //Percent
					$psaf->setUser( $user_obj->getId() );
					$psaf->setPayStubEntryNameId( $release_account_id );
					$psaf->setPercentAmount(100);
					$psaf->setPercentAmountEntryNameId( $psea_obj->getId() );
					$psaf->setEffectiveDate( $effective_date );
					$psaf->setDescription('Release Accrual Balance');

					if ( $psaf->isValid() ) {
						Debug::Text('Release Accrual Is Valid!!: ', __FILE__, __LINE__, __METHOD__, 10);
						$psaf->Save();
					}
				} else {
					Debug::Text('No Release Account for this Accrual!!', __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			//$ulf->FailTransaction();
			$ulf->CommitTransaction();
		} else {
			Debug::Text('No Accruals to release...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	function calcAmount() {
		$retval = bcmul( $this->getRate(), $this->getUnits(), 4 );
		if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCurrencyObject() ) ) {
			$retval = $this->getUserObject()->getCurrencyObject()->round( $retval );
		} //else { //Debug::Text('No currency object found, amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Text('Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function isUnique() {
		$ph = array(
					'user_id' => (int)$this->getUser(),
					//'status_id' => $this->getStatus(), //This allows IN USE vs ACTIVE PSA to exists, which shouldn't.
					'pay_stub_entry_name_id' => (int)$this->getPayStubEntryNameId(),
					'effective_date' => (int)$this->getEffectiveDate(),
					'amount' => (float)$this->getAmount(),
					);

		$query = 'select id from '. $this->getTable() .' where user_id = ? AND pay_stub_entry_name_id = ? AND effective_date = ? AND amount = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique PSA: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function preSave() {
		//Authorize all pay stub amendments until we decide they will actually go through an authorization process
		if ( $this->getAuthorized() == FALSE ) {
			$this->setAuthorized(TRUE);
		}

		//Make sure we always have a status and type set.
		if ( $this->getStatus() == FALSE ) {
			$this->setStatus(50);
		}
		if ( $this->getType() == FALSE ) {
			$this->setType(10);
		}

		/*
		//Handle YTD adjustments just like any other amendment.
		if ( $this->getYTDAdjustment() == TRUE
				AND $this->getStatus() != 55
				AND $this->getStatus() != 60) {
			Debug::Text('Calculating Amount...', __FILE__, __LINE__, __METHOD__, 10);
			$this->setStatus( 52 );
		}
		*/

		//If amount isn't set, but Rate and units are, calc amount for them.
		if ( ( $this->getAmount() == NULL OR $this->getAmount() == 0 OR $this->getAmount() == '' )
				AND $this->getRate() !== NULL AND $this->getUnits() !== NULL
				AND $this->getRate() != 0 AND $this->getUnits() != 0
				AND $this->getRate() != '' AND $this->getUnits() != ''
				) {
			Debug::Text('Calculating Amount...', __FILE__, __LINE__, __METHOD__, 10);
			//$this->setAmount( bcmul( $this->getRate(), $this->getUnits(), 4 ) );
			$this->setAmount( $this->calcAmount() );
		}

		return TRUE;
	}

	function Validate() {
		if ( $this->getDeleted() == FALSE ) {
			if ( $this->validate_only == FALSE AND $this->getUser() == FALSE AND $this->Validator->hasError('user_id') == FALSE) {
				$this->Validator->isTrue(		'user_id',
												FALSE,
												TTi18n::gettext('Invalid Employee'));
			}

			if ( is_object( $this->getUserObject() ) AND $this->getUserObject()->getHireDate() != '' AND TTDate::getMiddleDayEpoch( $this->getEffectiveDate() ) < TTDate::getMiddleDayEpoch( $this->getUserObject()->getHireDate() ) ) {
				$this->Validator->isTrue(		'effective_date',
												FALSE,
												TTi18n::gettext('Effective date is before the employees hire date.'));
			}
			if ( is_object( $this->getUserObject() ) AND $this->getUserObject()->getTerminationDate() != '' AND TTDate::getMiddleDayEpoch( $this->getEffectiveDate() ) > TTDate::getMiddleDayEpoch( $this->getUserObject()->getTerminationDate() ) ) {
				$this->Validator->isTrue(		'effective_date',
												FALSE,
												TTi18n::gettext('Effective date is after the employees termination date.'));
			}

			$this->Validator->isTrue(		'user_id',
											$this->isUnique(),
											TTi18n::gettext('Another Pay Stub Amendment already exists for the same employee, account, effective date and amount'));
		}

		//Only show this error if it wasn't already triggered earlier.
		if ( $this->validate_only == FALSE AND is_object($this->Validator) AND $this->Validator->hasError('pay_stub_entry_name_id') == FALSE AND $this->getPayStubEntryNameId() == FALSE ) {
			$this->Validator->isTrue(		'pay_stub_entry_name_id',
											FALSE,
											TTi18n::gettext('Invalid Pay Stub Account'));
		}

		if ( $this->getType() == 10 ) {
			//If rate and units are set, and not amount, calculate the amount for us.
			if ( $this->getRate() !== NULL AND $this->getUnits() !== NULL AND $this->getAmount() == NULL ) {
				$this->preSave();
			}

			//Make sure rate * units = amount
			if ( $this->getAmount() === NULL ) {
				Debug::Text('Amount is NULL...', __FILE__, __LINE__, __METHOD__, 10);
				$this->Validator->isTrue(		'amount',
												FALSE,
												TTi18n::gettext('Invalid Amount'));
			}

			//Make sure amount is sane given the rate and units.
			if ( $this->getRate() !== NULL AND $this->getUnits() !== NULL
					AND $this->getRate() != 0 AND $this->getUnits() != 0
					AND $this->getRate() != '' AND $this->getUnits() != ''
					//AND ( Misc::MoneyFormat( bcmul( $this->getRate(), $this->getUnits() ), FALSE) ) != Misc::MoneyFormat( $this->getAmount(), FALSE )
					AND ( Misc::MoneyFormat( $this->calcAmount(), FALSE ) != Misc::MoneyFormat( $this->getAmount(), FALSE ) ) //Use MoneyFormat here as the legacy interface doesn't handle more than two decimal places.
				) {
				Debug::text('Validate: Rate: '. $this->getRate() .' Units: '. $this->getUnits() .' Amount: '. $this->getAmount() .' Calc: Amount: '. $this->calcAmount() .' Raw: '.	 bcmul( $this->getRate(), $this->getUnits(), 4), __FILE__, __LINE__, __METHOD__, 10);
				$this->Validator->isTrue(		'amount',
												FALSE,
												TTi18n::gettext('Invalid Amount, calculation is incorrect'));
			}
		}

		//Check the status of any pay stub this is attached too. If its PAID then don't allow editing/deleting.
		if ( $this->getEnablePayStubStatusChange() == FALSE
				AND ( $this->getStatus() == 55
					OR ( is_object( $this->getPayStubObject() ) AND $this->getPayStubObject()->getStatus() == 40) ) ) {
			$this->Validator->isTrue(		'user_id',
											FALSE,
											TTi18n::gettext('Unable to modify Pay Stub Amendment that is currently be used by a Pay Stub marked PAID'));
		}

		//Don't allow these to be deleted in closed pay periods either.
		//Make sure effective date isn't in a CLOSED pay period?
		$pplf = TTNew('PayPeriodListFactory');
		$pplf->getByUserIdAndEndDate( $this->getUser(), $this->getEffectiveDate() );
		if ( $pplf->getRecordCount() == 1 ) {
			$pp_obj = $pplf->getCurrent();

			//Only check for CLOSED (not locked) pay periods when the
			//status of the PSA is *not* 52 and 55
			if ( $pp_obj->getStatus() == 20 AND ( $this->getStatus() != 52 AND $this->getStatus() != 55 ) ) {
				$this->Validator->isTrue(		'effective_date',
												FALSE,
												TTi18n::gettext('Pay Period that this effective date falls within is currently closed'));
			}
		}
		unset($pplf, $pp_obj);

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {
					$function = 'set'.$function;
					switch( $key ) {
						case 'effective_date':
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$uf = TTnew( 'UserFactory' );

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'user_status_id':
						case 'group_id':
						case 'user_group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'pay_stub_entry_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'status':
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'effective_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
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
			$this->getPermissionColumns( $data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}


	function getFormObject() {
		if ( !isset($this->form_obj['cf']) OR !is_object($this->form_obj['cf']) ) {
			//
			//Get all data for the form.
			//
			require_once( Environment::getBasePath() .'/classes/ChequeForms/ChequeForms.class.php');

			$cf = new ChequeForms();
			$this->form_obj['cf'] = $cf;
			return $this->form_obj['cf'];
		}

		return $this->form_obj['cf'];
	}


	function getChequeFormsObject( $format ) {
		if ( !isset($this->form_obj[$format]) OR !is_object($this->form_obj[$format]) ) {
			$this->form_obj[$format] = $this->getFormObject()->getFormObject( strtoupper( $format ) );
			return $this->form_obj[$format];
		}

		return $this->form_obj[$format];
	}

	function exportPayStubAmendment( $psalf = NULL, $export_type = NULL ) {
		global $current_company;

		if ( !is_object($psalf) AND $this->getId() != '' ) {
			$psalf = TTnew( 'PayStubAmendmentListFactory' );
			$psalf->getById( $this->getId() );
		}

		if ( get_class( $psalf ) !== 'PayStubAmendmentListFactory' ) {
			return FALSE;
		}

		if ( $export_type == '' ) {
			return FALSE;
		}

		if ( $psalf->getRecordCount() > 0 ) {

			Debug::Text('aExporting...', __FILE__, __LINE__, __METHOD__, 10);
			switch (strtolower($export_type)) {
				case 'eft_hsbc':
				case 'eft_1464':
				case 'eft_105':
				case 'eft_ach':
				case 'eft_beanstream':
					//Get file creation number
					$ugdlf = TTnew( 'UserGenericDataListFactory' );
					$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), 'PayStubFactory', TRUE );
					if ( $ugdlf->getRecordCount() > 0 ) {
						$ugd_obj = $ugdlf->getCurrent();
						$setup_data = $ugd_obj->getData();
					} else {
						$ugd_obj = TTnew( 'UserGenericDataFactory' );
					}

					Debug::Text('bExporting...', __FILE__, __LINE__, __METHOD__, 10);
					//get User Bank account info
					$balf = TTnew( 'BankAccountListFactory' );
					$balf->getCompanyAccountByCompanyId( $current_company->getID() );
					if ( $balf->getRecordCount() > 0 ) {
						$company_bank_obj = $balf->getCurrent();
						//Debug::Arr($company_bank_obj, 'Company Bank Object', __FILE__, __LINE__, __METHOD__, 10);
					}

					if ( isset( $setup_data['file_creation_number'] ) ) {
						$setup_data['file_creation_number']++;
					} else {
						//Start at a high number, in attempt to eliminate conflicts.
						$setup_data['file_creation_number'] = 500;
					}
					Debug::Text('bFile Creation Number: '. $setup_data['file_creation_number'], __FILE__, __LINE__, __METHOD__, 10);

					//Increment file creation number in DB
					if ( $ugd_obj->getId() == '' ) {
							$ugd_obj->setID( $ugd_obj->getId() );
					}
					$ugd_obj->setCompany( $current_company->getId() );
					$ugd_obj->setScript( 'PayStubFactory' );
					$ugd_obj->setName( 'PayStubFactory' );
					$ugd_obj->setData( $setup_data );
					$ugd_obj->setDefault( TRUE );
					if ( $ugd_obj->isValid() ) {
							$ugd_obj->Save();
					}

					$eft = new EFT();
					$eft->setFileFormat( str_replace('eft_', '', $export_type ) );

					$eft->setBusinessNumber( $current_company->getBusinessNumber() ); //ACH
					$eft->setOriginatorID( $current_company->getOriginatorID() );
					$eft->setFileCreationNumber( $setup_data['file_creation_number'] );
					$eft->setInitialEntryNumber( $current_company->getOtherID5() ); //ACH
					$eft->setDataCenter( $current_company->getDataCenterID() );
					$eft->setDataCenterName( $current_company->getOtherID4() ); //ACH
					$eft->setOriginatorShortName( $current_company->getShortName() );

					foreach( $psalf as $key => $psa_obj ) {
						//Can only export fixed amount PS amendemnts?
						if ( $psa_obj->getType() == 10 ) {
							Debug::Text('Looping over Pay Stub Amendment... ID: '. $psa_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

							//Get User information
							$ulf = TTnew( 'UserListFactory' );
							$user_obj = $ulf->getById( $psa_obj->getUser() )->getCurrent();

							//Get company information
							$clf = TTnew( 'CompanyListFactory' );
							$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();

							//get User Bank account info
							$balf = TTnew( 'BankAccountListFactory' );
							$user_bank_obj = $balf->getUserAccountByCompanyIdAndUserId( $user_obj->getCompany(), $user_obj->getId() );
							if ( $user_bank_obj->getRecordCount() > 0 ) {
								$user_bank_obj = $user_bank_obj->getCurrent();
							} else {
								continue;
							}

							$amount = $psa_obj->getAmount();
							if ( $amount > 0 ) {
								$record = new EFT_Record();
								$record->setType('C');
								$record->setCPACode(200);
								$record->setAmount( $amount );

								$record->setDueDate( TTDate::getBeginDayEpoch( $psa_obj->getEffectiveDate() ) );
								$record->setInstitution( $user_bank_obj->getInstitution() );
								$record->setTransit( $user_bank_obj->getTransit() );
								$record->setAccount( $user_bank_obj->getAccount() );
								$record->setName( $user_obj->getFullName() );

								$record->setOriginatorShortName( $company_obj->getShortName() );
								$record->setOriginatorLongName( substr($company_obj->getName(), 0, 30) );
								$record->setOriginatorReferenceNumber( 'PSA'.$psa_obj->getId() );

								if ( isset($company_bank_obj) AND is_object($company_bank_obj) ) {
									$record->setReturnInstitution( $company_bank_obj->getInstitution() );
									$record->setReturnTransit( $company_bank_obj->getTransit() );
									$record->setReturnAccount( $company_bank_obj->getAccount() );
								}
								$eft->setRecord( $record );
							}
							unset($amount);

							$this->getProgressBarObject()->set( NULL, $key );
						} else {
							Debug::Text('Skipping percent PS amendment... ID: '. $psa_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
						}
					}
					$eft->compile();
					$output = $eft->getCompiledData();
					break;
				case 'cheque_9085':
				case 'cheque_9209p':
				case 'cheque_dlt103':
				case 'cheque_dlt104':
				case 'cheque_cr_standard_form_1':
				case 'cheque_cr_standard_form_2':
					//Wait until Cheque class is created first.
					$cheque_form_obj = $this->getChequeFormsObject( str_replace('cheque_', '', $export_type) );
					$psealf = TTnew( 'PayStubEntryAccountListFactory' );

					$i = 0;
					foreach( $psalf as $key => $psa_obj ) {
						//Can only export fixed amount PS amendemnts?

						if ( $psa_obj->getType() == 10 ) {
							
							//Get User information
							$ulf = TTnew( 'UserListFactory' );
							$user_obj = $ulf->getById( $psa_obj->getUser() )->getCurrent();

							//Get company information
							$clf = TTnew( 'CompanyListFactory' );
							$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();

							if ( $user_obj->getCountry() == 'CA' ) {
								$date_format = 'd/m/Y';
							} else {
								$date_format = 'm/d/Y';
							}
							$pay_stub_amendment = array(
											'id' => $psa_obj->getId(),
											'display_id' => str_pad($psa_obj->getId(), 15, 0, STR_PAD_LEFT),
											'user_id' => $psa_obj->getUser(),											
											'status' => $psa_obj->getStatus(),
											'amount' => $psa_obj->getAmount(),
											'date' => TTDate::getTime(),

											'full_name' => $user_obj->getFullName(),
											'address1' => $user_obj->getAddress1(),
											'address2' => $user_obj->getAddress2(),
											'city' => $user_obj->getCity(),
											'province' => $user_obj->getProvince(),
											'postal_code' => $user_obj->getPostalCode(),
											'country' => $user_obj->getCountry(),

											'company_name' => $company_obj->getName(),

											'symbol' => $psa_obj->getUserObject()->getCurrencyObject()->getSymbol(),

											'created_date' => $psa_obj->getCreatedDate(),
											'created_by' => $psa_obj->getCreatedBy(),
											'updated_date' => $psa_obj->getUpdatedDate(),
											'updated_by' => $psa_obj->getUpdatedBy(),
											'deleted_date' => $psa_obj->getDeletedDate(),
											'deleted_by' => $psa_obj->getDeletedBy()
										);
				
							$cheque_form_obj->addRecord( $pay_stub_amendment );
							$this->getFormObject()->addForm( $cheque_form_obj );

							$this->getProgressBarObject()->set( NULL, $key );
						} else {
							Debug::Text('Skipping percent PS amendment... ID: '. $psa_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
						}

						$this->getProgressBarObject()->set( NULL, $i );
						$i++;
					}

					if ( stristr( $export_type, 'cheque') ) {
						$output_format = 'PDF';
					}
					$output = $this->getFormObject()->output( $output_format );

					break;
			}
		}

		if ( isset($output) ) {
			return $output;
		}

		return FALSE;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Pay Stub Amendment - Employee').': '. UserListFactory::getFullNameById( $this->getUser() ) .' '.	TTi18n::getText('Amount').': '. $this->getAmount(), NULL, $this->getTable(), $this );
	}
}
?>
