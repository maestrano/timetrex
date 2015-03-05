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
class ContributingShiftPolicyFactory extends Factory {
	protected $table = 'contributing_shift_policy';
	protected $pk_sequence_name = 'contributing_shift_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $contributing_time_policy_obj = NULL;
	protected $branch_map = NULL;
	protected $department_map = NULL;
	protected $job_group_map = NULL;
	protected $job_map = NULL;
	protected $job_item_group_map = NULL;
	protected $job_item_map = NULL;

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'include_schedule_shift_type':
				$retval = array(
										10 => TTi18n::gettext('Schedules have no effect'),
										20 => TTi18n::gettext('Only Scheduled Shifts'),
										30 => TTi18n::gettext('Never Scheduled Shifts'),
									);
				break;
			case 'include_holiday_type':
				$retval = array(
										10 => TTi18n::gettext('Have no effect'),
										20 => TTi18n::gettext('Always on Holidays'), //Eligible or not.
										25 => TTi18n::gettext('Always on Eligible Holidays'), //Only Eligible
										30 => TTi18n::gettext('Never on Holidays'),
									);
				break;
			case 'branch_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Branches'),
										20 => TTi18n::gettext('Only Selected Branches'),
										30 => TTi18n::gettext('All Except Selected Branches'),
									);
				break;
			case 'department_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Departments'),
										20 => TTi18n::gettext('Only Selected Departments'),
										30 => TTi18n::gettext('All Except Selected Departments'),
									);
				break;
			case 'job_group_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Job Groups'),
										20 => TTi18n::gettext('Only Selected Job Groups'),
										30 => TTi18n::gettext('All Except Selected Job Groups'),
									);
				break;
			case 'job_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Jobs'),
										20 => TTi18n::gettext('Only Selected Jobs'),
										30 => TTi18n::gettext('All Except Selected Jobs'),
									);
				break;
			case 'job_item_group_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Task Groups'),
										20 => TTi18n::gettext('Only Selected Task Groups'),
										30 => TTi18n::gettext('All Except Selected Task Groups'),
									);
				break;
			case 'job_item_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Tasks'),
										20 => TTi18n::gettext('Only Selected Tasks'),
										30 => TTi18n::gettext('All Except Selected Tasks'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-name' => TTi18n::gettext('Name'),
										'-1020-description' => TTi18n::gettext('Description'),

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

										'contributing_pay_code_policy_id' => 'ContributingPayCodePolicy',

										'filter_start_date' => 'FilterStartDate',
										'filter_end_date' => 'FilterEndDate',
										'filter_start_time' => 'FilterStartTime',
										'filter_end_time' => 'FilterEndTime',
										'filter_minimum_time' => 'FilterMinimumTime',
										'filter_maximum_time' => 'FilterMaximumTime',
										'include_partial_shift' => 'IncludePartialShift',

										'branch' => 'Branch',
										'branch_selection_type_id' => 'BranchSelectionType',
										'branch_selection_type' => FALSE,
										'exclude_default_branch' => 'ExcludeDefaultBranch',
										'department' => 'Department',
										'department_selection_type_id' => 'DepartmentSelectionType',
										'department_selection_type' => FALSE,
										'exclude_default_department' => 'ExcludeDefaultDepartment',
										'job_group' => 'JobGroup',
										'job_group_selection_type_id' => 'JobGroupSelectionType',
										'job_group_selection_type' => FALSE,
										'job' => 'Job',
										'job_selection_type_id' => 'JobSelectionType',
										'job_selection_type' => FALSE,
										'exclude_default_job' => 'ExcludeDefaultJob',
										'job_item_group' => 'JobItemGroup',
										'job_item_group_selection_type_id' => 'JobItemGroupSelectionType',
										'job_item_group_selection_type' => FALSE,
										'job_item' => 'JobItem',
										'job_item_selection_type_id' => 'JobItemSelectionType',
										'job_item_selection_type' => FALSE,
										'exclude_default_job_item' => 'ExcludeDefaultJobItem',

										'sun' => 'Sun',
										'mon' => 'Mon',
										'tue' => 'Tue',
										'wed' => 'Wed',
										'thu' => 'Thu',
										'fri' => 'Fri',
										'sat' => 'Sat',

										'include_holiday_type_id' => 'IncludeHolidayType',
										'holiday_policy' => 'HolidayPolicy',
										
										'in_use' => FALSE,
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	function getContributingPayCodePolicyObject() {
		return $this->getGenericObject( 'ContributingPayCodePolicyListFactory', $this->getContributingPayCodePolicy(), 'contributing_pay_code_policy_obj' );
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
					'company_id' => (int)$this->getCompany(),
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
											2, 75)
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

	function getContributingPayCodePolicy() {
		if ( isset($this->data['contributing_pay_code_policy_id']) ) {
			return (int)$this->data['contributing_pay_code_policy_id'];
		}

		return FALSE;
	}
	function setContributingPayCodePolicy($id) {
		$id = trim($id);

		$cpcplf = TTnew( 'ContributingPayCodePolicyListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'contributing_pay_code_policy_id',
													$cpcplf->getByID($id),
													TTi18n::gettext('Contributing Pay Code Policy is invalid')
													) ) {

			$this->data['contributing_pay_code_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getFilterStartDate( $raw = FALSE ) {
		if ( isset($this->data['filter_start_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['filter_start_date'];
			} else {
				return TTDate::strtotime( $this->data['filter_start_date'] );
			}
		}

		return FALSE;
	}
	function setFilterStartDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ) {
			$epoch = NULL;
		}

		if	(
				$epoch == NULL
				OR
				$this->Validator->isDate(		'filter_start_date',
												$epoch,
												TTi18n::gettext('Incorrect start date'))
			) {

			$this->data['filter_start_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getFilterEndDate( $raw = FALSE ) {
		if ( isset($this->data['filter_end_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['filter_end_date'];
			} else {
				return TTDate::strtotime( $this->data['filter_end_date'] );
			}
		}

		return FALSE;
	}
	function setFilterEndDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ) {
			$epoch = NULL;
		}

		if	(	$epoch == NULL
				OR
				$this->Validator->isDate(		'filter_end_date',
												$epoch,
												TTi18n::gettext('Incorrect end date'))
			) {

			$this->data['filter_end_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getFilterStartTime( $raw = FALSE ) {
		if ( isset($this->data['filter_start_time']) ) {
			if ( $raw === TRUE) {
				return $this->data['filter_start_time'];
			} else {
				return TTDate::strtotime( $this->data['filter_start_time'] );
			}
		}

		return FALSE;
	}
	function setFilterStartTime($epoch) {
		$epoch = trim($epoch);

		if	(	$epoch == ''
				OR
				$this->Validator->isDate(		'filter_start_time',
												$epoch,
												TTi18n::gettext('Incorrect Start time'))
			) {

			$this->data['filter_start_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getFilterEndTime( $raw = FALSE ) {
		if ( isset($this->data['filter_end_time']) ) {
			if ( $raw === TRUE) {
				return $this->data['filter_end_time'];
			} else {
				return TTDate::strtotime( $this->data['filter_end_time'] );
			}
		}

		return FALSE;
	}
	function setFilterEndTime($epoch) {
		$epoch = trim($epoch);

		if	(	$epoch == ''
				OR
				$this->Validator->isDate(		'filter_end_time',
												$epoch,
												TTi18n::gettext('Incorrect End time'))
			) {

			$this->data['filter_end_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getFilterMinimumTime() {
		if ( isset($this->data['filter_minimum_time']) ) {
			return (int)$this->data['filter_minimum_time'];
		}

		return FALSE;
	}
	function setFilterMinimumTime($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'filter_minimum_time',
													$int,
													TTi18n::gettext('Incorrect Minimum Time')) ) {
			$this->data['filter_minimum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getFilterMaximumTime() {
		if ( isset($this->data['filter_maximum_time']) ) {
			return (int)$this->data['filter_maximum_time'];
		}

		return FALSE;
	}
	function setFilterMaximumTime($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'filter_maximum_time',
													$int,
													TTi18n::gettext('Incorrect Maximum Time')) ) {
			$this->data['filter_maximum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getIncludePartialShift() {
		if ( isset($this->data['include_partial_shift']) ) {
			return $this->fromBool( $this->data['include_partial_shift'] );
		}

		return FALSE;
	}
	function setIncludePartialShift($bool) {
		$this->data['include_partial_shift'] = $this->toBool($bool);

		return TRUE;
	}

	
	/*

	Branch/Department/Job/Task filter functions

	*/
	function getBranchSelectionType() {
		if ( isset($this->data['branch_selection_type_id']) ) {
			return (int)$this->data['branch_selection_type_id'];
		}

		return FALSE;
	}
	function setBranchSelectionType($value) {
		$value = (int)trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'branch_selection_type_id',
											$value,
											TTi18n::gettext('Incorrect Branch Selection Type'),
											$this->getOptions('branch_selection_type')) ) {

			$this->data['branch_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getExcludeDefaultBranch() {
		if ( isset($this->data['exclude_default_branch']) ) {
			return $this->fromBool( $this->data['exclude_default_branch'] );
		}

		return FALSE;
	}
	function setExcludeDefaultBranch($bool) {
		$this->data['exclude_default_branch'] = $this->toBool($bool);

		return TRUE;
	}

	function getBranch() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 610, $this->getID(), 'branch_map' );
	}
	function setBranch($ids) {
		Debug::text('Setting Branch IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 610, $this->getID(), (array)$ids );
	}

	function getDepartmentSelectionType() {
		if ( isset($this->data['department_selection_type_id']) ) {
			return (int)$this->data['department_selection_type_id'];
		}

		return FALSE;
	}
	function setDepartmentSelectionType($value) {
		$value = (int)trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'department_selection_type_id',
											$value,
											TTi18n::gettext('Incorrect Department Selection Type'),
											$this->getOptions('department_selection_type')) ) {

			$this->data['department_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getExcludeDefaultDepartment() {
		if ( isset($this->data['exclude_default_department']) ) {
			return $this->fromBool( $this->data['exclude_default_department'] );
		}

		return FALSE;
	}
	function setExcludeDefaultDepartment($bool) {
		$this->data['exclude_default_department'] = $this->toBool($bool);

		return TRUE;
	}

	function getDepartment() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 620, $this->getID(), 'department_map' );
	}
	function setDepartment($ids) {
		Debug::text('Setting Department IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 620, $this->getID(), (array)$ids );
	}

	function getJobGroupSelectionType() {
		if ( isset($this->data['job_group_selection_type_id']) ) {
			return (int)$this->data['job_group_selection_type_id'];
		}

		return FALSE;
	}
	function setJobGroupSelectionType($value) {
		$value = (int)trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'job_group_selection_type_id',
											$value,
											TTi18n::gettext('Incorrect Job Group Selection Type'),
											$this->getOptions('job_group_selection_type')) ) {

			$this->data['job_group_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJobGroup() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 640, $this->getID(), 'job_group_map' );
	}
	function setJobGroup($ids) {
		Debug::text('Setting Job Group IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 640, $this->getID(), (array)$ids );
	}

	function getJobSelectionType() {
		if ( isset($this->data['job_selection_type_id']) ) {
			return (int)$this->data['job_selection_type_id'];
		}

		return FALSE;
	}
	function setJobSelectionType($value) {
		$value = (int)trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'job_selection_type_id',
											$value,
											TTi18n::gettext('Incorrect Job Selection Type'),
											$this->getOptions('job_selection_type')) ) {

			$this->data['job_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJob() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 630, $this->getID(), 'job_map' );
	}
	function setJob($ids) {
		Debug::text('Setting Job IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 630, $this->getID(), (array)$ids );
	}

	function getExcludeDefaultJob() {
		if ( isset($this->data['exclude_default_job']) ) {
			return $this->fromBool( $this->data['exclude_default_job'] );
		}

		return FALSE;
	}
	function setExcludeDefaultJob($bool) {
		$this->data['exclude_default_job'] = $this->toBool($bool);

		return TRUE;
	}

	function getJobItemGroupSelectionType() {
		if ( isset($this->data['job_item_group_selection_type_id']) ) {
			return (int)$this->data['job_item_group_selection_type_id'];
		}

		return FALSE;
	}
	function setJobItemGroupSelectionType($value) {
		$value = (int)trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'job_item_group_selection_type_id',
											$value,
											TTi18n::gettext('Incorrect Task Group Selection Type'),
											$this->getOptions('job_item_group_selection_type')) ) {

			$this->data['job_item_group_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItemGroup() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 660, $this->getID(), 'job_item_group_map' );
	}
	function setJobItemGroup($ids) {
		Debug::text('Setting Task Group IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 660, $this->getID(), (array)$ids );
	}

	function getJobItemSelectionType() {
		if ( isset($this->data['job_item_selection_type_id']) ) {
			return (int)$this->data['job_item_selection_type_id'];
		}

		return FALSE;
	}
	function setJobItemSelectionType($value) {
		$value = (int)trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'job_item_selection_type_id',
											$value,
											TTi18n::gettext('Incorrect Task Selection Type'),
											$this->getOptions('job_item_selection_type')) ) {

			$this->data['job_item_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItem() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 650, $this->getID(), 'job_item_map' );
	}
	function setJobItem($ids) {
		Debug::text('Setting Task IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 650, $this->getID(), (array)$ids );
	}

	function getExcludeDefaultJobItem() {
		if ( isset($this->data['exclude_default_job_item']) ) {
			return $this->fromBool( $this->data['exclude_default_job_item'] );
		}

		return FALSE;
	}
	function setExcludeDefaultJobItem($bool) {
		$this->data['exclude_default_job_item'] = $this->toBool($bool);

		return TRUE;
	}
	
	function getSun() {
		if ( isset($this->data['sun']) ) {
			return $this->fromBool( $this->data['sun'] );
		}

		return FALSE;
	}
	function setSun($bool) {
		$this->data['sun'] = $this->toBool($bool);

		return TRUE;
	}

	function getMon() {
		if ( isset($this->data['mon']) ) {
			return $this->fromBool( $this->data['mon'] );
		}

		return FALSE;
	}
	function setMon($bool) {
		$this->data['mon'] = $this->toBool($bool);

		return TRUE;
	}
	function getTue() {
		if ( isset($this->data['tue']) ) {
			return $this->fromBool( $this->data['tue'] );
		}

		return FALSE;
	}
	function setTue($bool) {
		$this->data['tue'] = $this->toBool($bool);

		return TRUE;
	}
	function getWed() {
		if ( isset($this->data['wed']) ) {
			return $this->fromBool( $this->data['wed'] );
		}

		return FALSE;
	}
	function setWed($bool) {
		$this->data['wed'] = $this->toBool($bool);

		return TRUE;
	}
	function getThu() {
		if ( isset($this->data['thu']) ) {
			return $this->fromBool( $this->data['thu'] );
		}

		return FALSE;
	}
	function setThu($bool) {
		$this->data['thu'] = $this->toBool($bool);

		return TRUE;
	}
	function getFri() {
		if ( isset($this->data['fri']) ) {
			return $this->fromBool( $this->data['fri'] );
		}

		return FALSE;
	}
	function setFri($bool) {
		$this->data['fri'] = $this->toBool($bool);

		return TRUE;
	}
	function getSat() {
		if ( isset($this->data['sat']) ) {
			return $this->fromBool( $this->data['sat'] );
		}

		return FALSE;
	}
	function setSat($bool) {
		$this->data['sat'] = $this->toBool($bool);

		return TRUE;
	}

	function getIncludeScheduleShiftType() {
		if ( isset($this->data['include_schedule_shift_type_id']) ) {
			return (int)$this->data['include_schedule_shift_type_id'];
		}

		return FALSE;
	}
	function setIncludeScheduleShiftType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'include_schedule_shift_type_id',
											$value,
											TTi18n::gettext('Incorrect Include Schedule Shift Type'),
											$this->getOptions('include_schedule_shift_type')) ) {

			$this->data['include_schedule_shift_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getIncludeHolidayType() {
		if ( isset($this->data['include_holiday_type_id']) ) {
			return (int)$this->data['include_holiday_type_id'];
		}

		return FALSE;
	}
	function setIncludeHolidayType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'include_holiday_type_id',
											$value,
											TTi18n::gettext('Incorrect Include Holiday Type'),
											$this->getOptions('include_holiday_type')) ) {

			$this->data['include_holiday_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getHolidayPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 690, $this->getID() );
	}
	function setHolidayPolicy($ids) {
		Debug::text('Setting Holiday Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 690, $this->getID(), (array)$ids );
	}

	function isHoliday( $epoch, $calculate_policy_obj ) {
		if ( $epoch == '' OR !is_object($calculate_policy_obj) ) {
			return FALSE;
		}

		if ( $this->isHolidayRestricted() == TRUE ) {
			//Get holidays from all holiday policies assigned to this contributing shift policy
			$holiday_policy_ids = $this->getHolidayPolicy();
			if ( is_array($holiday_policy_ids) AND count($holiday_policy_ids) > 0 ) {
				foreach( $holiday_policy_ids as $holiday_policy_id ) {
					if ( isset($calculate_policy_obj->holiday_policy[$holiday_policy_id]) ) {
						$holiday_obj = $calculate_policy_obj->filterHoliday( $epoch, $calculate_policy_obj->holiday_policy[$holiday_policy_id], NULL );
						if ( is_object($holiday_obj) ) {
							Debug::text(' Is Holiday: User ID: '. $calculate_policy_obj->getUserObject()->getID() .' Date: '. TTDate::getDate('DATE', $epoch), __FILE__, __LINE__, __METHOD__, 10);

							//Check if its only eligible holidays or all holidays.
							if ( $this->getIncludeHolidayType() == 20 OR $this->getIncludeHolidayType() == 30 ) {
								Debug::text(' Active for all Holidays', __FILE__, __LINE__, __METHOD__, 10);
								return TRUE;
							} elseif ( $this->getIncludeHolidayType() == 25 AND $calculate_policy_obj->isEligibleForHoliday( $epoch, $calculate_policy_obj->holiday_policy[$holiday_policy_id] ) == TRUE ) {
								Debug::text(' Is Eligible for Holiday', __FILE__, __LINE__, __METHOD__, 10);
								return TRUE;
							}
						}
					}
				}
			}
			unset($holiday_policy_objs);
		}

		Debug::text(' Not Holiday: User ID: '. $calculate_policy_obj->getUserObject()->getID() .' Date: '. TTDate::getDate('DATE', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	
	function isHolidayRestricted() {
		if ( $this->getIncludeHolidayType() == 20 OR $this->getIncludeHolidayType() == 25 OR $this->getIncludeHolidayType() == 30 ) {
			return TRUE;
		}

		return FALSE;
	}

	function isActive( $date_epoch, $in_epoch = NULL, $out_epoch = NULL, $calculate_policy_obj = NULL ) {
		//Debug::text(' Date Epoch: '. $date_epoch .' In: '. $in_epoch .' Out: '. $out_epoch, __FILE__, __LINE__, __METHOD__, 10);
		//Make sure date_epoch is always specified so we can still determine isActive even if in_epoch/out_epoch are not specified themselves.
		if ( $date_epoch == '' AND $in_epoch == '' ) {
			Debug::text(' ERROR: Date/In epoch not specified...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( $date_epoch != '' AND $in_epoch == '' ) {
			$in_epoch = $date_epoch;
		}

		if ( $out_epoch == '' ) {
			$out_epoch = $in_epoch;
		}

		//Debug::text(' In: '. TTDate::getDate('DATE+TIME', $in_epoch) .' Out: '. TTDate::getDate('DATE+TIME', $out_epoch), __FILE__, __LINE__, __METHOD__, 10);
		$i = $in_epoch;
		$last_iteration = 0;
		//Make sure we loop on the in_epoch, out_epoch and every day inbetween. $last_iteration allows us to always hit the out_epoch.
		while( $i <= $out_epoch AND $last_iteration <= 1 ) {
			//Debug::text(' I: '. TTDate::getDate('DATE+TIME', $i) .' Include Holiday Type: '. $this->getIncludeHolidayType(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->getIncludeHolidayType() > 10 AND is_object( $calculate_policy_obj ) ) {
				$is_holiday = $this->isHoliday( TTDate::getMiddleDayEpoch( $in_epoch ), $calculate_policy_obj );
			} else {
				$is_holiday = FALSE;
			}

			if ( ( $this->getIncludeHolidayType() == 10 AND $this->isActiveFilterDate($i) == TRUE AND $this->isActiveFilterDayOfWeek($i) == TRUE )
					OR ( ( $this->getIncludeHolidayType() == 20 OR $this->getIncludeHolidayType() == 25 ) AND ( ( $this->isActiveFilterDate($i) == TRUE AND $this->isActiveFilterDayOfWeek($i) == TRUE ) OR $is_holiday == TRUE ) )
					OR ( $this->getIncludeHolidayType() == 30 AND ( ( $this->isActiveFilterDate($i) == TRUE AND $this->isActiveFilterDayOfWeek($i) == TRUE ) AND $is_holiday == FALSE ) )
				) {
				//Debug::text('Active Date/DayOfWeek: '. TTDate::getDate('DATE+TIME', $i), __FILE__, __LINE__, __METHOD__, 10);

				return TRUE;
			}

			//If there is more than one day between $i and $out_epoch, add one day to $i.
			if ( $i < ( $out_epoch - 86400 ) ) {
				$i += 86400;
			} else {
				//When less than one day untl $out_epoch, skip to $out_epoch and loop once more.
				$i = $out_epoch;
				$last_iteration++;
			}
		}

		//Debug::text('NOT Active Date/DayOfWeek: '. TTDate::getDate('DATE+TIME', $i), __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	//Check if this premium policy is restricted by time.
	//If its not, we can apply it to non-punched hours.
	function isTimeRestricted() {
		//If time restrictions account for over 23.5 hours, then we assume
		//that this policy is not time restricted at all.
		//The above is flawed, as a time restriction of 6AM to 6AM the next day is perfectly valid.
		if ( $this->getFilterStartTime() != '' AND $this->getFilterEndTime() != '' ) {
			Debug::text('IS time restricted...', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::text('NOT time restricted...Filter Start Time: '. TTDate::getDate('DATE+TIME', $this->getFilterStartTime() ) .' End Time: '. TTDate::getDate('DATE+TIME', $this->getFilterEndTime() ), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Check if this time is within the start/end time.
	function isActiveFilterTime( $in_epoch, $out_epoch, $calculate_policy_obj = NULL ) {
		//Debug::text(' Checking for Active Time with: In: '. TTDate::getDate('DATE+TIME', $in_epoch) .' Out: '. TTDate::getDate('DATE+TIME', $out_epoch), __FILE__, __LINE__, __METHOD__, 10);
		if ( $in_epoch == '' OR $out_epoch == '' ) {
			//Debug::text(' Empty time stamps, returning TRUE.', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//Debug::text(' PP Raw Start TimeStamp('.$this->getFilterStartTime(TRUE).'): '. TTDate::getDate('DATE+TIME', $this->getFilterStartTime() ) .' Raw End TimeStamp: '. TTDate::getDate('DATE+TIME', $this->getFilterEndTime() ), __FILE__, __LINE__, __METHOD__, 10);
		$start_time_stamp = TTDate::getTimeLockedDate( $this->getFilterStartTime(), $in_epoch); //Base the end time on day of the in_epoch.
		$end_time_stamp = TTDate::getTimeLockedDate( $this->getFilterEndTime(), $in_epoch); //Base the end time on day of the in_epoch.

		//Check if end timestamp is before start, if it is, move end timestamp to next day.
		if ( $end_time_stamp < $start_time_stamp ) {
			Debug::text(' Moving End TimeStamp to next day.', __FILE__, __LINE__, __METHOD__, 10);
			$end_time_stamp = TTDate::getTimeLockedDate( $this->getFilterEndTime(), ( TTDate::getMiddleDayEpoch($end_time_stamp) + 86400 ) ); //Due to DST, jump ahead 1.5 days, then jump back to the time locked date.
		}

		//Debug::text(' Start TimeStamp: '. TTDate::getDate('DATE+TIME', $start_time_stamp) .' End TimeStamp: '. TTDate::getDate('DATE+TIME', $end_time_stamp), __FILE__, __LINE__, __METHOD__, 10);
		//Check to see if start/end time stamps are not set or are equal, we always return TRUE if they are.
		if ( $this->getIncludeHolidayType() == 10
				AND ( $start_time_stamp == '' OR $end_time_stamp == '' OR $start_time_stamp == $end_time_stamp ) ) {
			//Debug::text(' Start/End time not set, assume it always matches.', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		} else {
			//If the premium policy start/end time spans midnight, there could be multiple windows to check
			//where the premium policy applies, make sure we check all windows.
			for( $i = (TTDate::getMiddleDayEpoch($start_time_stamp) - 86400); $i <= (TTDate::getMiddleDayEpoch($end_time_stamp) + 86400); $i += 86400 ) {
				//$tmp_start_time_stamp = TTDate::getTimeLockedDate( $this->getFilterStartTime(), $i);
				//$tmp_end_time_stamp = TTDate::getTimeLockedDate( $this->getFilterEndTime(), ( $tmp_start_time_stamp + ($end_time_stamp - $start_time_stamp ) ) );

				$tmp_start_time_stamp = TTDate::getTimeLockedDate( $this->getFilterStartTime(), $i);
				$next_i = ( $tmp_start_time_stamp + ($end_time_stamp - $start_time_stamp) ); //Get next date to base the end_time_stamp on, and to calculate if we need to adjust for DST.
				$tmp_end_time_stamp = TTDate::getTimeLockedDate( $end_time_stamp, ( $next_i + ( TTDate::getDSTOffset( $tmp_start_time_stamp, $next_i ) * -1 ) ) ); //Use $end_time_stamp as it can be modified above due to being near midnight. Also adjust for DST by reversing it.
				if ( $this->isActive( $tmp_start_time_stamp, $tmp_start_time_stamp, $tmp_end_time_stamp, $calculate_policy_obj ) == TRUE ) {
					Debug::text(' Checking against Start TimeStamp: '. TTDate::getDate('DATE+TIME', $tmp_start_time_stamp) .'('.$tmp_start_time_stamp.') End TimeStamp: '. TTDate::getDate('DATE+TIME', $tmp_end_time_stamp) .'('.$tmp_end_time_stamp.')', __FILE__, __LINE__, __METHOD__, 10);
					if ( $this->getIncludePartialShift() == TRUE AND TTDate::isTimeOverLap( $in_epoch, $out_epoch, $tmp_start_time_stamp, $tmp_end_time_stamp) == TRUE ) {
						//When dealing with partial punches, any overlap whatsoever activates the policy.
						Debug::text(' Partial Punch Within Active Time!', __FILE__, __LINE__, __METHOD__, 10);
						return TRUE;
					} elseif ( $in_epoch >= $tmp_start_time_stamp AND $in_epoch <= $tmp_end_time_stamp ) {
						//Non partial punches, they must punch in within the time window.
						Debug::text(' Within Active Time!', __FILE__, __LINE__, __METHOD__, 10);
						return TRUE;
					} elseif ( ( $start_time_stamp == '' OR $end_time_stamp == '' OR $start_time_stamp == $end_time_stamp ) ) { //Must go AFTER the above IF statements.
						//When IncludeHolidayType != 10 this trigger here.
						Debug::text(' No Start/End Date/Time!', __FILE__, __LINE__, __METHOD__, 10);
						return TRUE;
					} //else { //Debug::text(' No match...', __FILE__, __LINE__, __METHOD__, 10);
				} else {
					Debug::text(' Not Active on this day: Start: '. TTDate::getDate('DATE+TIME', $tmp_start_time_stamp) .' End: '. TTDate::getDate('DATE+TIME', $tmp_end_time_stamp), __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		Debug::text(' NOT Within Active Time!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Check if this date is within the effective date range
	function isActiveFilterDate( $epoch ) {
		//Debug::text(' Checking for Active Date: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		$epoch = TTDate::getBeginDayEpoch( $epoch );

		if ( $this->getFilterStartDate() == '' AND $this->getFilterEndDate() == '') {
			return TRUE;
		}

		if ( $epoch >= (int)$this->getFilterStartDate()
				AND ( $epoch <= (int)$this->getFilterEndDate() OR $this->getFilterEndDate() == '' ) ) {
			return TRUE;
		}

		Debug::text(' Not active FilterDate!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Check if this day of the week is active
	function isActiveFilterDayOfWeek( $epoch ) {
		//Debug::Arr($epoch, ' Checking for Active Day of Week: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);
		$day_of_week = strtolower( date('D', $epoch) );

		switch ($day_of_week) {
			case 'sun':
				if ( $this->getSun() == TRUE ) {
					return TRUE;
				}
				break;
			case 'mon':
				if ( $this->getMon() == TRUE ) {
					return TRUE;
				}
				break;
			case 'tue':
				if ( $this->getTue() == TRUE ) {
					return TRUE;
				}
				break;
			case 'wed':
				if ( $this->getWed() == TRUE ) {
					return TRUE;
				}
				break;
			case 'thu':
				if ( $this->getThu() == TRUE ) {
					return TRUE;
				}
				break;
			case 'fri':
				if ( $this->getFri() == TRUE ) {
					return TRUE;
				}
				break;
			case 'sat':
				if ( $this->getSat() == TRUE ) {
					return TRUE;
				}
				break;
		}

		Debug::text(' Not active FilterDayOfWeek!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getPartialUserDateTotalObject( $udt_obj, $calculate_policy_obj = NULL ) {
		if ( !is_object($udt_obj) ) {
			return FALSE;
		}

		Debug::text(' Checking for Active Time for '. $this->getName() .': In: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp()) .' Out: '. TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp()), __FILE__, __LINE__, __METHOD__, 10);
		if ( $udt_obj->getStartTimeStamp() == '' OR $udt_obj->getEndTimeStamp() == '' ) {
			Debug::text(' Empty time stamps, returning object untouched...', __FILE__, __LINE__, __METHOD__, 10);
			return $udt_obj;
		}

		if ( $this->getIncludePartialShift() == TRUE AND $this->isTimeRestricted() == TRUE AND $this->isActive( $udt_obj->getStartTimeStamp(), $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp(), $calculate_policy_obj ) ) {
			//Debug::text(' Contrib Shift ('.$this->getName().') Raw Start TimeStamp('. $this->getFilterStartTime(TRUE) .'): '. TTDate::getDate('DATE+TIME', $this->getFilterStartTime() ) .' Raw End TimeStamp: '. TTDate::getDate('DATE+TIME', $this->getFilterEndTime() ), __FILE__, __LINE__, __METHOD__, 10);
			$start_time_stamp = TTDate::getTimeLockedDate( $this->getFilterStartTime(), $udt_obj->getStartTimeStamp() ); //Base the end time on day of the in_epoch.
			$end_time_stamp = TTDate::getTimeLockedDate( $this->getFilterEndTime(), $udt_obj->getStartTimeStamp() ); //Base the end time on day of the in_epoch.
			//Debug::text(' bChecking for Active Time with: In: '. TTDate::getDate('DATE+TIME', $start_time_stamp ) .' Out: '. TTDate::getDate('DATE+TIME', $end_time_stamp ), __FILE__, __LINE__, __METHOD__, 10);

			//Check if end timestamp is before start, if it is, move end timestamp to next day.
			if ( $end_time_stamp < $start_time_stamp ) {
				Debug::text(' Moving End TimeStamp to next day.', __FILE__, __LINE__, __METHOD__, 10);
				$end_time_stamp = TTDate::getTimeLockedDate( $this->getFilterEndTime(), ( TTDate::getMiddleDayEpoch($end_time_stamp) + 86400 ) ); //Due to DST, jump ahead 1.5 days, then jump back to the time locked date.
			}

			//Handle the last second of the day, so punches that span midnight like 11:00PM to 6:00AM get a full 1 hour for the time before midnight, rather than 59mins and 59secs.
			if ( TTDate::getHour( $end_time_stamp ) == 23 AND TTDate::getMinute( $end_time_stamp ) == 59 ) {
				$end_time_stamp = ( TTDate::getEndDayEpoch( $end_time_stamp ) + 1 );
				Debug::text(' End time stamp is within the last minute of day, make sure we include the last second of the day as well.', __FILE__, __LINE__, __METHOD__, 10);
			}

			if ( $start_time_stamp == $end_time_stamp ) {
				Debug::text(' Start/End time filters match, nothing to do...', __FILE__, __LINE__, __METHOD__, 10);
				return $udt_obj;
			}

			Debug::text(' Checking for Active Time with: In: '. TTDate::getDate('DATE+TIME', $start_time_stamp ) .' Out: '. TTDate::getDate('DATE+TIME', $end_time_stamp ), __FILE__, __LINE__, __METHOD__, 10);
			if ( TTDate::isTimeOverLap( $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp(), $start_time_stamp, $end_time_stamp) == TRUE ) {
				Debug::text(' UDT Object needs to be split...', __FILE__, __LINE__, __METHOD__, 10);

				$original_start_time_stamp = $udt_obj->getStartTimeStamp();
				$original_end_time_stamp = $udt_obj->getEndTimeStamp();

				//Take the existing UDT Object, modify it, then clone it up to twice to handle the before and after parts if necessary as new records.

				//Handle original record by modifying it.
				//This makes it so if it was already included in other Regular Time policies, it won't be included again, but the remaining fragments still could be.


				//Check if start time overlaps, or end time overlaps, or both.
				if ( $original_start_time_stamp < $start_time_stamp ) {
					$udt_obj->setStartTimeStamp( $start_time_stamp );
				}

				if ( $original_end_time_stamp > $end_time_stamp ) {
					$udt_obj->setEndTimeStamp( $end_time_stamp );
				}

				$udt_obj->setTotalTime( $udt_obj->calcTotalTime() );
				$udt_obj->setIsPartialShift( TRUE );

				$udt_obj->setEnableCalcSystemTotalTime(FALSE);
				if ( $udt_obj->isValid() ) {
					$udt_obj->preSave(); //Call this so TotalTime, TotalTimeAmount is calculated immediately, as we don't save these records until later.
					Debug::text(' CURRENT: UDT Object times changed to: Start: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End: '.  TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
				}

				//Handle BEFORE remaining fragment
				if ( $original_start_time_stamp < $start_time_stamp ) {
					$before_udt_obj = clone $udt_obj; //Make sure we clone the object so we don't modify the original record for all subsequent accesses.

					$before_udt_obj->setID( FALSE );

					$before_udt_obj->setStartTimeStamp( $original_start_time_stamp );
					$before_udt_obj->setEndTimeStamp( $start_time_stamp );

					$before_udt_obj->setTotalTime( $before_udt_obj->calcTotalTime() );
					$before_udt_obj->setIsPartialShift( TRUE );

					$before_udt_obj->setEnableCalcSystemTotalTime(FALSE);
					if ( $before_udt_obj->isValid() ) {
						$before_udt_obj->preSave(); //Call this so TotalTime, TotalTimeAmount is calculated immediately, as we don't save these records until later.
						Debug::text(' BEFORE: UDT Object times changed to: Start: '. TTDate::getDate('DATE+TIME', $before_udt_obj->getStartTimeStamp() ) .' End: '.  TTDate::getDate('DATE+TIME', $before_udt_obj->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
						if ( !isset( $calculate_policy_obj->user_date_total[$calculate_policy_obj->user_date_total_insert_id] ) ) {
							$calculate_policy_obj->user_date_total[$calculate_policy_obj->user_date_total_insert_id] = $before_udt_obj;
						} else {
							Debug::text('ERROR: Invalid UserDateTotal Entry!', __FILE__, __LINE__, __METHOD__, 10);
						}
						$calculate_policy_obj->user_date_total_insert_id--;
					}
				} else {
					Debug::text(' No before remaining fragment...', __FILE__, __LINE__, __METHOD__, 10);
				}
				unset($before_udt_obj);

				//Handle AFTER remaining fragment
				if ( $original_end_time_stamp > $end_time_stamp ) {
					$after_udt_obj = clone $udt_obj; //Make sure we clone the object so we don't modify the original record for all subsequent accesses.

					$after_udt_obj->setID( FALSE );

					$after_udt_obj->setStartTimeStamp( $end_time_stamp );
					$after_udt_obj->setEndTimeStamp( $original_end_time_stamp );

					$after_udt_obj->setTotalTime( $after_udt_obj->calcTotalTime() );
					$after_udt_obj->setIsPartialShift( TRUE );

					$after_udt_obj->setEnableCalcSystemTotalTime(FALSE);
					if ( $after_udt_obj->isValid() ) {
						$after_udt_obj->preSave(); //Call this so TotalTime, TotalTimeAmount is calculated immediately, as we don't save these records until later.
						Debug::text(' AFTER: UDT Object times changed to: Start: '. TTDate::getDate('DATE+TIME', $after_udt_obj->getStartTimeStamp() ) .' End: '.  TTDate::getDate('DATE+TIME', $after_udt_obj->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
						if ( !isset( $calculate_policy_obj->user_date_total[$calculate_policy_obj->user_date_total_insert_id] ) ) {
							$calculate_policy_obj->user_date_total[$calculate_policy_obj->user_date_total_insert_id] = $after_udt_obj;
						} else {
							Debug::text('ERROR: Invalid UserDateTotal Entry!', __FILE__, __LINE__, __METHOD__, 10);
						}
						$calculate_policy_obj->user_date_total_insert_id--;
					}
				} else {
					Debug::text(' No after remaining fragment...', __FILE__, __LINE__, __METHOD__, 10);
				}
				unset($after_udt_obj);

				return $udt_obj;
			}
		}

		Debug::text(' No need to split UDT Object...', __FILE__, __LINE__, __METHOD__, 10);
		return $udt_obj;
	}

	function checkIndividualDifferentialCriteria( $selection_type, $exclude_default_item, $current_item, $allowed_items, $default_item = NULL ) {
		//Debug::Arr($allowed_items, '    Allowed Items: Selection Type: '. $selection_type .' Current Item: '. $current_item, __FILE__, __LINE__, __METHOD__, 10);

		//Used to use AND ( $allowed_items === FALSE OR ( is_array( $allowed_items ) AND in_array( $current_item, $allowed_items ) ) ) )
		// But checking $allowed_items === FALSE  makes it so if $selection_type = 20 and no selection is made it will still be accepted,
		// which is the exact opposite of what we want.
		// If $selection_type = (20,30) a selection must be made for it to match.
		if ( 	( $selection_type == 10
						AND ( $exclude_default_item == FALSE
								OR ( $exclude_default_item == TRUE AND $current_item != $default_item ) ) )

				OR ( $selection_type == 20
						AND ( is_array( $allowed_items ) AND in_array( $current_item, $allowed_items ) ) )
						AND ( $exclude_default_item == FALSE
								OR ( $exclude_default_item == TRUE AND $current_item != $default_item ) )

				OR ( $selection_type == 30
						AND ( is_array( $allowed_items ) AND !in_array( $current_item, $allowed_items ) ) )
						AND ( $exclude_default_item == FALSE
								OR ( $exclude_default_item == TRUE AND $current_item != $default_item ) )

				) {
			return TRUE;
		}

		//Debug::text('    Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	
	function isActiveDifferential( $udt_obj, $user_obj ) {
		//Debug::Arr( array( $this->getBranchSelectionType(), (int)$this->getExcludeDefaultBranch(), $udt_obj->getBranch(), $user_obj->getDefaultBranch() ), ' Branch Selection: ', __FILE__, __LINE__, __METHOD__, 10);

		$retval = FALSE;

		//Optimization if all selection types are set to "All".
		if ( $this->getBranchSelectionType() == 10 AND $this->getDepartmentSelectionType() == 10 AND $this->getJobGroupSelectionType() == 10 AND $this->getJobSelectionType() == 10 AND $this->getJobItemGroupSelectionType() == 10 AND $this->getJobItemSelectionType() == 10
			AND $this->getExcludeDefaultBranch() == FALSE AND $this->getExcludeDefaultDepartment() == FALSE AND $this->getExcludeDefaultJob() == FALSE AND $this->getExcludeDefaultJobItem() == FALSE ) {
			return TRUE;
		}

		if ( $this->checkIndividualDifferentialCriteria( $this->getBranchSelectionType(), $this->getExcludeDefaultBranch(), $udt_obj->getBranch(), $this->getBranch(), $user_obj->getDefaultBranch() ) ) {
			//Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $this->getBranchSelectionType() .' Exclude Default Branch: '. (int)$this->getExcludeDefaultBranch() .' Default Branch: '.  $user_obj->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->checkIndividualDifferentialCriteria( $this->getDepartmentSelectionType(), $this->getExcludeDefaultDepartment(), $udt_obj->getDepartment(), $this->getDepartment(), $user_obj->getDefaultDepartment() ) ) {
				//Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $this->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$this->getExcludeDefaultDepartment() .' Default Department: '.  $user_obj->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);

				$job_group = ( is_object( $udt_obj->getJobObject() ) ) ? $udt_obj->getJobObject()->getGroup() : NULL;
				if ( $this->checkIndividualDifferentialCriteria( $this->getJobGroupSelectionType(), NULL, $job_group, $this->getJobGroup() ) ) {
					//Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $this->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

					if ( $this->checkIndividualDifferentialCriteria( $this->getJobSelectionType(), $this->getExcludeDefaultJob(), $udt_obj->getJob(), $this->getJob(), $user_obj->getDefaultJob() ) ) {
						//Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $this->getJobSelectionType() .' Exclude Default Job: '. (int)$this->getExcludeDefaultJob() .' Default Job: '.  $user_obj->getDefaultJob(), __FILE__, __LINE__, __METHOD__, 10);

						$job_item_group = ( is_object( $udt_obj->getJobItemObject() ) ) ? $udt_obj->getJobItemObject()->getGroup() : NULL;
						if ( $this->checkIndividualDifferentialCriteria( $this->getJobItemGroupSelectionType(), NULL, $job_item_group, $this->getJobItemGroup() ) ) {
							//Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $this->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

							if ( $this->checkIndividualDifferentialCriteria( $this->getJobItemSelectionType(), $this->getExcludeDefaultJobItem(), $udt_obj->getJobItem(), $this->getJobItem(), $user_obj->getDefaultJobItem() ) ) {
								//Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $this->getJobSelectionType() .' Exclude Default Task: '. (int)$this->getExcludeDefaultJobItem() .' Default Task: '.  $user_obj->getDefaultJobItem(), __FILE__, __LINE__, __METHOD__, 10);
								$retval = TRUE;
							}
						}
					}
				}
			}
		}
		unset($job_group, $job_item_group);

		//Debug::text(' Active Shift Differential Result: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	
	function Validate() {
		if ( $this->getDeleted() == TRUE ) {
			$rtplf = TTNew('RegularTimePolicyListFactory');
			$rtplf->getByCompanyIdAndContributingShiftPolicyId( $this->getCompany(), $this->getId() );
			if ( $rtplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This contributing shift policy is currently in use') .' '. TTi18n::gettext('by regular time policies') );
			}

			$otplf = TTNew('OverTimePolicyListFactory');
			$otplf->getByCompanyIdAndContributingShiftPolicyId( $this->getCompany(), $this->getId() );
			if ( $otplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This contributing shift policy is currently in use') .' '. TTi18n::gettext('by overtime policies') );
			}

			$pplf = TTNew('PremiumPolicyListFactory');
			$pplf->getByCompanyIdAndContributingShiftPolicyId( $this->getCompany(), $this->getId() );
			if ( $pplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This contributing shift policy is currently in use') .' '. TTi18n::gettext('by premium policies') );
			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getBranchSelectionType() === FALSE OR $this->getBranchSelectionType() < 10 ) {
			$this->setBranchSelectionType(10); //All
		}
		if ( $this->getDepartmentSelectionType() === FALSE OR $this->getDepartmentSelectionType() < 10 ) {
			$this->setDepartmentSelectionType(10); //All
		}
		if ( $this->getJobGroupSelectionType() === FALSE OR $this->getJobGroupSelectionType() < 10 ) {
			$this->setJobGroupSelectionType(10); //All
		}
		if ( $this->getJobSelectionType() === FALSE OR $this->getJobSelectionType() < 10 ) {
			$this->setJobSelectionType(10); //All
		}
		if ( $this->getJobItemGroupSelectionType() === FALSE OR $this->getJobItemGroupSelectionType() < 10 ) {
			$this->setJobItemGroupSelectionType(10); //All
		}
		if ( $this->getJobItemSelectionType() === FALSE OR $this->getJobItemSelectionType() < 10 ) {
			$this->setJobItemSelectionType(10); //All
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
						case 'filter_start_date':
						case 'filter_end_date':
						case 'filter_start_time':
						case 'filter_end_time':
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
						case 'filter_start_date':
						case 'filter_end_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'filter_start_time':
						case 'filter_end_time':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'TIME', $this->$function() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Contributing Shift Policy'), NULL, $this->getTable(), $this );
	}
}
?>
