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
 * @package Modules\Policy
 */
class RoundIntervalPolicyFactory extends Factory {
	protected $table = 'round_interval_policy';
	protected $pk_sequence_name = 'round_interval_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	//Just need relations for each actual Punch Type
	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'round_type':
				$retval = array(
										10 => TTi18n::gettext('Down'),
										20 => TTi18n::gettext('Average'),
										30 => TTi18n::gettext('Up')
									);
				break;
			case 'punch_type':
				$retval = array(
										10 => TTi18n::gettext('All Punches'),
										20 => TTi18n::gettext('All In (incl. Lunch/Break)'),
										30 => TTi18n::gettext('All Out (incl. Lunch/Break)'),
										40 => TTi18n::gettext('Normal - In'),
										50 => TTi18n::gettext('Normal - Out'),
										60 => TTi18n::gettext('Lunch - In'),
										70 => TTi18n::gettext('Lunch - Out'),
										80 => TTi18n::gettext('Break - In'),
										90 => TTi18n::gettext('Break - Out'),
										100 => TTi18n::gettext('Lunch Total'),
										110 => TTi18n::gettext('Break Total'),
										120 => TTi18n::gettext('Day Total'),
									);
				break;
			case 'punch_type_relation':
				$retval = array(
										40 => array(10, 20),
										50 => array(10, 30, 120),
										60 => array(10, 20, 100),
										70 => array(10, 30),
										80 => array(10, 20, 110),
										90 => array(10, 30),
									);
				break;
			case 'condition_type':
				$retval = array(
										0 => TTi18n::gettext('Disabled'),
										10 => TTi18n::gettext('Scheduled Time'),
										20 => TTi18n::gettext('Scheduled Time or Not Scheduled'),
										30 => TTi18n::gettext('Static Time'), //For specific time of day, ie: 8AM
										40 => TTi18n::gettext('Static Total Time'), //For Day/Lunch/Break total.
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-punch_type' => TTi18n::gettext('Punch Type'),
										'-1020-round_type' => TTi18n::gettext('Round Type'),
										'-1030-name' => TTi18n::gettext('Name'),
										'-1035-description' => TTi18n::gettext('Description'),

										'-1040-round_interval' => TTi18n::gettext('Interval'),

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
								'name',
								'description',
								'punch_type',
								'round_type',
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
										'name' => 'Name',
										'description' => 'Description',
										'round_type_id' => 'RoundType',
										'round_type' => FALSE,
										'punch_type_id' => 'PunchType',
										'punch_type' => FALSE,
										'round_interval' => 'Interval',
										'grace' => 'Grace',
										'strict' => 'Strict',

										'condition_type_id' => 'ConditionType',
										'condition_static_time' => 'ConditionStaticTime',
										'condition_static_total_time' => 'ConditionStaticTotalTime',
										'condition_start_window' => 'ConditionStartWindow',
										'condition_stop_window' => 'ConditionStopWindow',

										'in_use' => FALSE,
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
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

	function getPunchTypeFromPunchStatusAndType($status, $type) {
		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		switch($type) {
			case 10: //Normal
				if ( $status == 10 ) { //In
					$punch_type = 40;
				} else {
					$punch_type = 50;
				}
				break;
			case 20: //Lunch
				if ( $status == 10 ) { //In
					$punch_type = 60;
				} else {
					$punch_type = 70;
				}
				break;
			case 30: //Break
				if ( $status == 10 ) { //In
					$punch_type = 80;
				} else {
					$punch_type = 90;
				}
				break;
		}

		return $punch_type;
	}

	function isUniqueName($name) {
		$ph = array(
					'company_id' => $this->getCompany(),
					'name' => strtolower($name),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique: '. $name, __FILE__, __LINE__, __METHOD__, 10);

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
											2, 50)
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

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($description) {
		$description = trim($description);

		if (	$description == ''
				OR $this->Validator->isLength(	'description',
												$description,
												TTi18n::gettext('Description is invalid'),
												1, 250) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function getRoundType() {
		if ( isset($this->data['round_type_id']) ) {
			return (int)$this->data['round_type_id'];
		}

		return FALSE;
	}
	function setRoundType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('round_type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'round_type',
											$value,
											TTi18n::gettext('Incorrect Round Type'),
											$this->getOptions('round_type')) ) {

			$this->data['round_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPunchType() {
		if ( isset($this->data['punch_type_id']) ) {
			return (int)$this->data['punch_type_id'];
		}

		return FALSE;
	}
	function setPunchType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('punch_type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'punch_type',
											$value,
											TTi18n::gettext('Incorrect Punch Type'),
											$this->getOptions('punch_type')) ) {

			$this->data['punch_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getInterval() {
		if ( isset($this->data['round_interval']) ) {
			return $this->data['round_interval'];
		}

		return FALSE;
	}
	function setInterval($value) {
		$value = trim($value);

		if	(	$this->Validator->isNumeric(		'interval',
													$value,
													TTi18n::gettext('Incorrect Interval')) ) {

			//If someone is using hour parse format ie: 0.12 we need to round to the nearest
			//minute other wise it'll be like 7mins and 23seconds messing up rounding.
			//$this->data['round_interval'] = $value;
			$this->data['round_interval'] = TTDate::roundTime($value, 60, 20);


			return TRUE;
		}

		return FALSE;
	}

	function getGrace() {
		if ( isset($this->data['grace']) ) {
			return $this->data['grace'];
		}

		return FALSE;
	}
	function setGrace($value) {
		$value = trim($value);

		if	(	$this->Validator->isNumeric(		'grace',
													$value,
													TTi18n::gettext('Incorrect grace value')) ) {

			//If someone is using hour parse format ie: 0.12 we need to round to the nearest
			//minute other wise it'll be like 7mins and 23seconds messing up rounding.
			//$this->data['grace'] = $value;
			$this->data['grace'] = TTDate::roundTime($value, 60, 20);

			return TRUE;
		}

		return FALSE;
	}

	function getStrict() {
		return $this->fromBool( $this->data['strict'] );
	}
	function setStrict($bool) {
		$this->data['strict'] = $this->toBool($bool);

		return TRUE;
	}

	function inConditionWindow( $epoch, $window_epoch ) {
		if (
				$epoch >= ( $window_epoch - $this->getConditionStartWindow() )
				AND
				$epoch <= ( $window_epoch + $this->getConditionStopWindow() )
			) {
			return TRUE;
		}

		Debug::Text('Not in Condition Window... Epoch: '. TTDate::getDate('DATE+TIME', $epoch ) .' Window Epoch: '. TTDate::getDate('DATE+TIME', $window_epoch ) .' Window Start: '. $this->getConditionStartWindow() .' Stop: '. $this->getConditionStopWindow(), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	function isConditionTrue( $epoch, $schedule_time ) {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return TRUE;
		}

		Debug::Text('Punch Time: '. TTDate::getDate('DATE+TIME', $epoch ) .' Schedule Time: '. $schedule_time, __FILE__, __LINE__, __METHOD__, 10);
		$retval = FALSE;
		switch( $this->getConditionType() ) {
			case 10: //Scheduled Time
			case 20: //Scheduled Time or Not Scheduled.
				if ( $this->getConditionType() == 20 AND $schedule_time == '' ) {
					Debug::Text('Not scheduled, returning TRUE...', __FILE__, __LINE__, __METHOD__, 10);
					$retval = TRUE;
				} else {
					Debug::Text('Scheduled...', __FILE__, __LINE__, __METHOD__, 10);
					if ( $this->inConditionWindow( $epoch, $schedule_time ) == TRUE ) {
						$retval = TRUE;
					}
				}
				break;
			case 30: //Static Time
				if ( $this->inConditionWindow( $epoch, TTDate::getTimeLockedDate( $this->getConditionStaticTime(), $epoch) ) == TRUE ) {
					$retval = TRUE;
				}
				break;
			case 40: //Static Total Time
				if ( $this->inConditionWindow( $epoch, $this->getConditionStaticTotalTime() ) == TRUE ) {
					$retval = TRUE;
				}
				break;
			case 0: //Disabled
				$retval = TRUE;
				break;
		}

		Debug::Text('Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}
	function getConditionType() {
		if ( isset($this->data['condition_type_id']) ) {
			return (int)$this->data['condition_type_id'];
		}

		return FALSE;
	}
	function setConditionType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'condition_type',
											$value,
											TTi18n::gettext('Incorrect Condition Type'),
											$this->getOptions('condition_type')) ) {

			$this->data['condition_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getConditionStaticTime( $raw = FALSE ) {
		if ( isset($this->data['condition_static_time']) ) {
			if ( $raw === TRUE) {
				return $this->data['condition_static_time'];
			} else {
				return TTDate::strtotime( $this->data['condition_static_time'] );
			}
		}

		return FALSE;
	}
	function setConditionStaticTime($epoch) {
		$epoch = trim($epoch);

		if	(	$epoch == ''
				OR
				$this->Validator->isDate(		'condition_static_time',
												$epoch,
												TTi18n::gettext('Incorrect Static time'))
			) {

			$this->data['condition_static_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getConditionStaticTotalTime() {
		if ( isset($this->data['condition_static_total_time']) ) {
			return $this->data['condition_static_total_time'];
		}

		return FALSE;
	}
	function setConditionStaticTotalTime($value) {
		$value = trim($value);

		if	(	$this->Validator->isNumeric(		'condition_static_total_time',
													$value,
													TTi18n::gettext('Incorrect Static Total Time')) ) {

			//If someone is using hour parse format ie: 0.12 we need to round to the nearest
			//minute other wise it'll be like 7mins and 23seconds messing up rounding.
			//$this->data['round_interval'] = $value;
			$this->data['condition_static_total_time'] = TTDate::roundTime($value, 60, 20);


			return TRUE;
		}

		return FALSE;
	}

	function getConditionStartWindow() {
		if ( isset($this->data['condition_start_window']) ) {
			return $this->data['condition_start_window'];
		}

		return FALSE;
	}
	function setConditionStartWindow($value) {
		$value = trim($value);

		if	(	$this->Validator->isNumeric(		'condition_start_window',
													$value,
													TTi18n::gettext('Incorrect Start Window')) ) {

			//If someone is using hour parse format ie: 0.12 we need to round to the nearest
			//minute other wise it'll be like 7mins and 23seconds messing up rounding.
			//$this->data['round_interval'] = $value;
			$this->data['condition_start_window'] = TTDate::roundTime($value, 60, 20);


			return TRUE;
		}

		return FALSE;
	}

	function getConditionStopWindow() {
		if ( isset($this->data['condition_stop_window']) ) {
			return $this->data['condition_stop_window'];
		}

		return FALSE;
	}
	function setConditionStopWindow($value) {
		$value = trim($value);

		if	(	$this->Validator->isNumeric(		'condition_stop_window',
													$value,
													TTi18n::gettext('Incorrect Stop Window')) ) {

			//If someone is using hour parse format ie: 0.12 we need to round to the nearest
			//minute other wise it'll be like 7mins and 23seconds messing up rounding.
			//$this->data['round_interval'] = $value;
			$this->data['condition_stop_window'] = TTDate::roundTime($value, 60, 20);


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
						case 'condition_static_time':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
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
						case 'punch_type':
						case 'round_type':
							$function = 'get'.str_replace('_', '', $variable);
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'condition_static_time':
							$data[$variable] = ( defined('TIMETREX_API') ) ? TTDate::getAPIDate( 'TIME', TTDate::strtotime( $this->$function() ) ) : $this->$function();
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Round Interval Policy'), NULL, $this->getTable(), $this );
	}
}
?>
