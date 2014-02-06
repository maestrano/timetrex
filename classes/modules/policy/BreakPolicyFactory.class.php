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
 * $Revision: 2534 $
 * $Id: BreakPolicyFactory.class.php 2534 2009-05-13 00:02:20Z ipso $
 * $Date: 2009-05-12 17:02:20 -0700 (Tue, 12 May 2009) $
 */

/**
 * @package Modules\Policy
 */
class BreakPolicyFactory extends Factory {
	protected $table = 'break_policy';
	protected $pk_sequence_name = 'break_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Auto-Deduct'),
										15 => TTi18n::gettext('Auto-Add'),
										20 => TTi18n::gettext('Normal')
									);
				break;
			case 'auto_detect_type':
				$retval = array(
										10 => TTi18n::gettext('Time Window'),
										20 => TTi18n::gettext('Punch Time'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => TTi18n::gettext('Type'),
										'-1020-name' => TTi18n::gettext('Name'),
										'-1030-amount' => TTi18n::gettext('Break Time'),
										'-1040-trigger_time' => TTi18n::gettext('Active After'),

										'-1050-auto_detect_type' => TTi18n::gettext('Auto Detect Breaks By'),
										//'-1060-start_window' => TTi18n::gettext('Start Window'),
										//'-1070-window_length' => TTi18n::gettext('Window Length'),
										//'-1080-minimum_punch_time' => TTi18n::gettext('Minimum Punch Time'),
										//'-1090-maximum_punch_time' => TTi18n::gettext('Maximum Punch Time'),

										'-1100-include_break_punch_time' => TTi18n::gettext('Include Break Punch'),
										'-1110-include_multiple_breaks' => TTi18n::gettext('Include Multiple Breaks'),

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
								'type',
								'amount',
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
										'trigger_time' => 'TriggerTime',
										'amount' => 'Amount',
										'auto_detect_type_id' => 'AutoDetectType',
										'auto_detect_type' => FALSE,
										'start_window' => 'StartWindow',
										'window_length' => 'WindowLength',
										'minimum_punch_time' => 'MinimumPunchTime',
										'maximum_punch_time' => 'MaximumPunchTime',
										'include_break_punch_time' => 'IncludeBreakPunchTime',
										'include_multiple_breaks' => 'IncludeMultipleBreaks',
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

	function getTriggerTime() {
		if ( isset($this->data['trigger_time']) ) {
			return (int)$this->data['trigger_time'];
		}

		return FALSE;
	}
	function setTriggerTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'trigger_time',
													$int,
													TTi18n::gettext('Incorrect Trigger Time')) ) {
			$this->data['trigger_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getAmount() {
		if ( isset($this->data['amount']) ) {
			return $this->data['amount'];
		}

		return FALSE;
	}
	function setAmount($value) {
		$value = trim($value);

		if 	(	$this->Validator->isNumeric(		'amount',
													$value,
													TTi18n::gettext('Incorrect Deduction Amount')) ) {

			$this->data['amount'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getAutoDetectType() {
		if ( isset($this->data['auto_detect_type_id']) ) {
			return $this->data['auto_detect_type_id'];
		}

		return FALSE;
	}
	function setAutoDetectType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('auto_detect_type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'auto_detect_type',
											$value,
											TTi18n::gettext('Incorrect Auto-Detect Type'),
											$this->getOptions('auto_detect_type')) ) {

			$this->data['auto_detect_type_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getStartWindow() {
		if ( isset($this->data['start_window']) ) {
			return $this->data['start_window'];
		}

		return FALSE;
	}
	function setStartWindow($value) {
		$value = (int)trim($value);

		if 	(	$value == 0
				OR
				$this->Validator->isNumeric(		'start_window',
													$value,
													TTi18n::gettext('Incorrect Start Window')) ) {

			$this->data['start_window'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getWindowLength() {
		if ( isset($this->data['window_length']) ) {
			return $this->data['window_length'];
		}

		return FALSE;
	}
	function setWindowLength($value) {
		$value = (int)trim($value);

		if 	(	$value == 0
				OR
				$this->Validator->isNumeric(		'window_length',
													$value,
													TTi18n::gettext('Incorrect Window Length')) ) {

			$this->data['window_length'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumPunchTime() {
		if ( isset($this->data['minimum_punch_time']) ) {
			return $this->data['minimum_punch_time'];
		}

		return FALSE;
	}
	function setMinimumPunchTime($value) {
		$value = (int)trim($value);

		if 	(	$value == 0
				OR
				$this->Validator->isNumeric(		'minimum_punch_time',
													$value,
													TTi18n::gettext('Incorrect Minimum Punch Time')) ) {

			$this->data['minimum_punch_time'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumPunchTime() {
		if ( isset($this->data['maximum_punch_time']) ) {
			return $this->data['maximum_punch_time'];
		}

		return FALSE;
	}
	function setMaximumPunchTime($value) {
		$value = (int)trim($value);

		if 	(	$value == 0
				OR
				$this->Validator->isNumeric(		'maximum_punch_time',
													$value,
													TTi18n::gettext('Incorrect Maximum Punch Time')) ) {

			$this->data['maximum_punch_time'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/*
		This takes into account any break punches when calculating the break policy.
		If enabled for:
			Auto-Deduct:	It will only deduct the amount that is not taken in break time.
							So if they auto-deduct 60mins, and an employee takes 30mins of break,
							it will deduct the remaining 30mins to equal 60mins. If they don't
							take any break, it deducts the full 60mins.
			Auto-Include:	It will include the amount taken in break time, up to the amount given.
							So if they auto-include 30mins and an employee takes a 60min break
							only 30mins will be included, and 30mins is automatically deducted
							as a regular break punch.
							If they don't take a break, it doesn't include any time.

		If not enabled for:
		  Auto-Deduct: Always deducts the amount.
		  Auto-Inlcyde: Always includes the amount.
	*/
	function getIncludeBreakPunchTime() {
		if ( isset($this->data['include_break_punch_time']) ) {
			return $this->fromBool( $this->data['include_break_punch_time'] );
		}

		return FALSE;
	}
	function setIncludeBreakPunchTime($bool) {
		$this->data['include_break_punch_time'] = $this->toBool($bool);

		return TRUE;
	}

	function getIncludeMultipleBreaks() {
		if ( isset($this->data['include_multiple_breaks']) ) {
			return $this->fromBool( $this->data['include_multiple_breaks'] );
		}

		return FALSE;
	}
	function setIncludeMultipleBreaks($bool) {
		$this->data['include_multiple_breaks'] = $this->toBool($bool);

		return TRUE;
	}

	function Validate() {
		if ( $this->getDeleted() == TRUE ){
			//Check to make sure there are no hours using this break policy.
			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getByBreakPolicyId( $this->getId() );
			if ( $udtlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This break policy is in use'));

			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getAutoDetectType() == FALSE ) {
			$this->setAutoDetectType( 10 );
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
						case 'type':
						case 'auto_detect_type':
							$function = 'get'.str_replace('_','',$variable);
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
		return TTLog::addEntry( $this->getId(), $log_action,  TTi18n::getText('Break Policy'), NULL, $this->getTable(), $this );
	}
}
?>
