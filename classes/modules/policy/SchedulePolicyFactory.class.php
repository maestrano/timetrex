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
class SchedulePolicyFactory extends Factory {
	protected $table = 'schedule_policy';
	protected $pk_sequence_name = 'schedule_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $meal_policy_obj = NULL;
	protected $break_policy_obj = NULL;
	protected $absence_policy_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1020-name' => TTi18n::gettext('Name'),
										'-1025-description' => TTi18n::gettext('Description'),
										//'-1030-meal_policy' => TTi18n::gettext('Meal Policy'),
										'-1040-absence_policy' => TTi18n::gettext('Undertime Absence Policy'),
										//'-1050-over_time_policy' => TTi18n::gettext('Overtime Policy'),
										'-1060-start_stop_window' => TTi18n::gettext('Window'),

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
								'start_stop_window',
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
										
										'absence_policy_id' => 'AbsencePolicyID',
										'absence_policy' => FALSE,

										'meal_policy' => 'MealPolicy',
										'break_policy' => 'BreakPolicy',

										'include_regular_time_policy' => 'IncludeRegularTimePolicy',
										'exclude_regular_time_policy' => 'ExcludeRegularTimePolicy',
										'include_over_time_policy' => 'IncludeOverTimePolicy',
										'exclude_over_time_policy' => 'ExcludeOverTimePolicy',
										'include_premium_policy' => 'IncludePremiumPolicy',
										'exclude_premium_policy' => 'ExcludePremiumPolicy',

										'start_stop_window' => 'StartStopWindow',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	function getAbsencePolicyObject() {
		return $this->getGenericObject( 'AbsencePolicyListFactory', $this->getAbsencePolicyID(), 'absence_policy_obj' );
	}

	function getMealPolicyObject( $meal_policy_id ) {
		if ( $meal_policy_id == '' ) {
			return FALSE;
		}

		Debug::Text('Meal Policy ID: '. $meal_policy_id .' Schedule Policy ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($this->meal_policy_obj[$meal_policy_id])
			AND is_object($this->meal_policy_obj[$meal_policy_id]) ) {
			return $this->meal_policy_obj[$meal_policy_id];
		} else {
			$bplf = TTnew( 'MealPolicyListFactory' );
			$bplf->getById( $meal_policy_id );
			if ( $bplf->getRecordCount() > 0 ) {
				$this->meal_policy_obj[$meal_policy_id] = $bplf->getCurrent();
				return $this->meal_policy_obj[$meal_policy_id];
			}

			return FALSE;
		}
	}

	function getBreakPolicyObject( $break_policy_id ) {
		if ( $break_policy_id == '' ) {
			return FALSE;
		}

		Debug::Text('Break Policy ID: '. $break_policy_id .' Schedule Policy ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($this->break_policy_obj[$break_policy_id])
			AND is_object($this->break_policy_obj[$break_policy_id]) ) {
			return $this->break_policy_obj[$break_policy_id];
		} else {
			$bplf = TTnew( 'BreakPolicyListFactory' );
			$bplf->getById( $break_policy_id );
			if ( $bplf->getRecordCount() > 0 ) {
				$this->break_policy_obj[$break_policy_id] = $bplf->getCurrent();
				return $this->break_policy_obj[$break_policy_id];
			}

			return FALSE;
		}
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
/*
	function getMealPolicyID() {
		if ( isset($this->data['meal_policy_id']) ) {
			return (int)$this->data['meal_policy_id'];
		}

		return 0; //Default to Defined By Policy Group.
	}
	function setMealPolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = 0;
		}

		$mplf = TTnew( 'MealPolicyListFactory' );

		if ( ( $id == 0 OR $id == -1 )
				OR
				$this->Validator->isResultSetWithRows(	'meal_policy',
														$mplf->getByID($id),
														TTi18n::gettext('Meal Policy is invalid')
													) ) {

			$this->data['meal_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
*/
	//Checks to see if we need to revert to the meal policies defined in the policy group, or use the ones defined in the schedule policy.
	function isUsePolicyGroupMealPolicy() {
		if ( in_array( 0, (array)$this->getMealPolicy() ) ) {
			return TRUE;
		}
		return FALSE;
	}
	function getMealPolicy() {
		$retarr = CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 155, $this->getID() );

		//Check if no CompanyGenericMap is *not* set at all, if so assume No Meal (-1)
		if ( $retarr === FALSE ) {
			$retarr = array( -1 );
		}

		return $retarr;
	}
	function setMealPolicy($ids) {
		//If NONE(-1) or Use Policy Group(0) are defined, unset all other ids.
		if ( is_array( $ids ) ) {
			if ( in_array( 0, $ids )  ) {
				$ids = array(0);
			} elseif ( in_array( -1, $ids ) ) {
				$ids = array(-1);
			}
		}
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 155, $this->getID(), $ids, FALSE, TRUE ); //Use relaxed ID range.
	}

	//Checks to see if we need to revert to the break policies defined in the policy group, or use the ones defined in the schedule policy.
	function isUsePolicyGroupBreakPolicy() {
		if ( in_array( 0, (array)$this->getBreakPolicy() ) ) {
			return TRUE;
		}
		return FALSE;
	}
	function getBreakPolicy() {
		$retarr = CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 165, $this->getID() );

		//Check if no CompanyGenericMap is *not* set at all, if so assume No Break (-1)
		if ( $retarr === FALSE ) {
			$retarr = array( -1 );
		}

		return $retarr;

	}
	function setBreakPolicy($ids) {
		//If NONE(-1) or Use Policy Group (0) are defined, unset all other ids.
		if ( is_array( $ids ) ) {
			if ( in_array( 0, $ids )  ) {
				$ids = array(0);
			} elseif ( in_array( -1, $ids ) ) {
				$ids = array(-1);
			}
		}
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 165, $this->getID(), $ids, FALSE, TRUE ); //Use relaxed ID range.
	}

	function getAbsencePolicyID() {
		if ( isset($this->data['absence_policy_id']) ) {
			return (int)$this->data['absence_policy_id'];
		}

		return FALSE;
	}
	function setAbsencePolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' );

		if (
				$id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'absence_policy',
														$aplf->getByID($id),
														TTi18n::gettext('Invalid Absence Policy ID')
														) ) {
			$this->data['absence_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getIncludeRegularTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 105, $this->getID() );
	}
	function setIncludeRegularTimePolicy($ids) {
		Debug::text('Setting Include Regular Time Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 105, $this->getID(), $ids );
	}
	function getExcludeRegularTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 106, $this->getID() );
	}
	function setExcludeRegularTimePolicy($ids) {
		Debug::text('Setting Exclude Regular Time Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 106, $this->getID(), $ids );
	}

	function getIncludeOverTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 115, $this->getID() );
	}
	function setIncludeOverTimePolicy($ids) {
		Debug::text('Setting Include Over Time Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 115, $this->getID(), $ids );
	}
	function getExcludeOverTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 116, $this->getID() );
	}
	function setExcludeOverTimePolicy($ids) {
		Debug::text('Setting Exclude Over Time Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 116, $this->getID(), $ids );
	}

	function getIncludePremiumPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 125, $this->getID() );
	}
	function setIncludePremiumPolicy($ids) {
		Debug::text('Setting Include Premium Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 125, $this->getID(), $ids );
	}
	function getExcludePremiumPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 126, $this->getID() );
	}
	function setExcludePremiumPolicy($ids) {
		Debug::text('Setting Exclude Premium Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 126, $this->getID(), $ids );
	}

	function getStartStopWindow() {
		if ( isset($this->data['start_stop_window']) ) {
			return (int)$this->data['start_stop_window'];
		}
		return FALSE;
	}
	function setStartStopWindow($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'start_stop_window',
													$int,
													TTi18n::gettext('Incorrect Start/Stop window')) ) {
			$this->data['start_stop_window'] = $int;

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
			Debug::Text('UnAssign Schedule Policy from Schedule/Recurring Schedules...'. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			$sf = TTnew( 'ScheduleFactory' );
			$rstf = TTnew( 'RecurringScheduleTemplateFactory' );

			$query = 'update '. $sf->getTable() .' set schedule_policy_id = 0 where schedule_policy_id = '. (int)$this->getId();
			$this->db->Execute($query);

			$query = 'update '. $rstf->getTable() .' set schedule_policy_id = 0 where schedule_policy_id = '. (int)$this->getId();
			$this->db->Execute($query);
		}

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
						case 'absence_policy':
							$data[$variable] = $this->getColumn($variable);
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Schedule Policy'), NULL, $this->getTable(), $this );
	}
}
?>
