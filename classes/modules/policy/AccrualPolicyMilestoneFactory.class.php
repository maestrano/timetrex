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
class AccrualPolicyMilestoneFactory extends Factory {
	protected $table = 'accrual_policy_milestone';
	protected $pk_sequence_name = 'accrual_policy_milestone_id_seq'; //PK Sequence name

	protected $accrual_policy_obj = NULL;

	protected $length_of_service_multiplier = array(
										0  => 0,
										10 => 1,
										20 => 7,
										30 => 30.4167,
										40 => 365.25,
										50 => 0.04166666666666666667, // 1/24th of a day.
									);

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'length_of_service_unit':
				$retval = array(
										10 => TTi18n::gettext('Day(s)'),
										20 => TTi18n::gettext('Week(s)'),
										30 => TTi18n::gettext('Month(s)'),
										40 => TTi18n::gettext('Year(s)'),
										50 => TTi18n::gettext('Hour(s)'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-length_of_service' => TTi18n::gettext('Length Of Service'),
										'-1020-length_of_service_unit' => TTi18n::gettext('Units'),
										'-1030-accrual_rate' => TTi18n::gettext('Accrual Rate'),
										'-1050-maximum_time' => TTi18n::gettext('Maximum Time'),
										'-1050-rollover_time' => TTi18n::gettext('Rollover Time'),

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
								'length_of_service',
								'length_of_service_unit',
								'accrual_rate',
								'maximum_time',
								'rollover_time',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array();
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array();
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
			$variable_function_map = array(
											'id' => 'ID',
											'accrual_policy_id' => 'AccrualPolicy',
											'length_of_service_days' => 'LengthOfServiceDays',
											'length_of_service' => 'LengthOfService',
											'length_of_service_unit_id' => 'LengthOfServiceUnit',
											//'length_of_service_unit' => FALSE,
											'accrual_rate' => 'AccrualRate',
											'maximum_time' => 'MaximumTime',
											'minimum_time' => 'MinimumTime',
											'rollover_time' => 'RolloverTime',
											'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	function getAccrualPolicyObject() {
		if ( is_object($this->accrual_policy_obj) ) {
			return $this->accrual_policy_obj;
		} else {
			$aplf = TTnew( 'AccrualPolicyListFactory' );
			$aplf->getById( $this->getAccrualPolicyID() );
			if ( $aplf->getRecordCount() > 0 ) {
				$this->accrual_policy_obj = $aplf->getCurrent();
				return $this->accrual_policy_obj;
			}

			return FALSE;
		}
	}

	function getAccrualPolicy() {
		if ( isset($this->data['accrual_policy_id']) ) {
			return (int)$this->data['accrual_policy_id'];
		}

		return FALSE;
	}
	function setAccrualPolicy($id) {
		$id = trim($id);

		$aplf = TTnew( 'AccrualPolicyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'accrual_policy',
													$aplf->getByID($id),
													TTi18n::gettext('Accrual Policy is invalid')
													) ) {

			$this->data['accrual_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//If we just base LengthOfService on days, leap years and such can cause off-by-one errors.
	//So we need to determine the exact dates when the milestones rollover and base it on that instead.
	function getLengthOfServiceDate( $milestone_rollover_date ) {
		switch ( $this->getLengthOfServiceUnit() ) {
			case 10: //Days
				$unit_str = 'Days';
				break;
			case 20: //Weeks
				$unit_str = 'Weeks';
				break;
			case 30: //Months
				$unit_str = 'Months';
				break;
			case 40: //Years
				$unit_str = 'Years';
				break;
		}

		if ( isset($unit_str) ) {
			$retval = TTDate::getBeginDayEpoch( strtotime( '+'. $this->getLengthOfService() .' '. $unit_str, $milestone_rollover_date ) );
			Debug::text('MileStone Rollover Days based on Length Of Service: '. TTDate::getDate('DATE+TIME', $retval), __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}

		return FALSE;
	}

	function getLengthOfServiceDays() {
		if ( isset($this->data['length_of_service_days']) ) {
			return (int)$this->data['length_of_service_days'];
		}

		return FALSE;
	}
	function setLengthOfServiceDays($int) {
		$int = (int)trim($int);

		Debug::text('aLength of Service Days: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if	(	$int >= 0
				AND
				$this->Validator->isFloat(			'length_of_service'.$this->getLabelID(),
													$int,
													TTi18n::gettext('Length of service is invalid')) ) {

			$this->data['length_of_service_days'] = bcmul( $int, $this->length_of_service_multiplier[$this->getLengthOfServiceUnit()], 4);

			return TRUE;
		}

		return FALSE;
	}

	function getLengthOfService() {
		if ( isset($this->data['length_of_service']) ) {
			return (int)$this->data['length_of_service'];
		}

		return FALSE;
	}
	function setLengthOfService($int) {
		$int = (int)trim($int);

		Debug::text('bLength of Service: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if	(	$int >= 0
				AND
				$this->Validator->isFloat(			'length_of_service'.$this->getLabelID(),
													$int,
													TTi18n::gettext('Length of service is invalid')) ) {

			$this->data['length_of_service'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getLengthOfServiceUnit() {
		if ( isset($this->data['length_of_service_unit_id']) ) {
			return (int)$this->data['length_of_service_unit_id'];
		}

		return FALSE;
	}
	function setLengthOfServiceUnit($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('length_of_service_unit') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'length_of_service_unit_id'.$this->getLabelID(),
											$value,
											TTi18n::gettext('Incorrect Length of service unit'),
											$this->getOptions('length_of_service_unit')) ) {

			$this->data['length_of_service_unit_id'] = $value;

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

		if	(	$int > 0
				AND
				$this->Validator->isNumeric(		'accrual_rate'.$this->getLabelID(),
													$int,
													TTi18n::gettext('Incorrect Accrual Rate')) ) {
			$this->data['accrual_rate'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumTime() {
		if ( isset($this->data['maximum_time']) ) {
			return (int)$this->data['maximum_time'];
		}

		return FALSE;
	}
	function setMaximumTime($int) {
		$int = trim($int);

		if	(	$int == 0
				OR
				$this->Validator->isNumeric(		'maximum_time'.$this->getLabelID(),
													$int,
													TTi18n::gettext('Incorrect Maximum Time')) ) {
			$this->data['maximum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumTime() {
		if ( isset($this->data['minimum_time']) ) {
			return (int)$this->data['minimum_time'];
		}

		return FALSE;
	}
	function setMinimumTime($int) {
		$int = trim($int);

		if	(	$int == 0
				OR
				$this->Validator->isNumeric(		'minimum_time'.$this->getLabelID(),
													$int,
													TTi18n::gettext('Incorrect Minimum Time')) ) {
			$this->data['minimum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getRolloverTime() {
		if ( isset($this->data['rollover_time']) ) {
			return (int)$this->data['rollover_time'];
		}

		return FALSE;
	}
	function setRolloverTime($int) {
		$int = trim($int);

		if	(	$int == 0
				OR
				$this->Validator->isNumeric(		'rollover_time'.$this->getLabelID(),
													$int,
													TTi18n::gettext('Incorrect Rollover Time')) ) {
			$this->data['rollover_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->validate_only == FALSE AND $this->getAccrualPolicy() == FALSE ) {
			$this->Validator->isTRUE(	'accrual_policy_id'.$this->getLabelID(),
										FALSE,
										TTi18n::gettext('Accrual Policy is invalid') );
		}

		return TRUE;
	}

	function preSave() {
		//Set Length of service in days.
		$this->setLengthOfServiceDays( $this->getLengthOfService() );

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						/* Once Flex interface is discontinued we can remove parseTimeUnit from HTML5 interface and do it in the API instead.
						case 'accrual_rate':
						case 'maximum_time':
						case 'minimum_time':
						case 'rollover_time':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseTimeUnit( $data[$key] ) );
							}
							break;
						*/
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
						/*
						//This is not displayed anywhere that needs it in text rather then from the options.
						case 'length_of_service_unit':
							//$function = 'getLengthOfServiceUnit';
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->getLengthOfServiceUnit(), $this->getOptions( $variable ) );
							}
							break;
						*/
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
		return TTLog::addEntry( $this->getAccrualPolicy(), $log_action, TTi18n::getText('Accrual Policy Milestone') .' (ID: '. $this->getID() .')', NULL, $this->getTable(), $this );
	}
}
?>
