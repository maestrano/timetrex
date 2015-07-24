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
class OverTimePolicyFactory extends Factory {
	protected $table = 'over_time_policy';
	protected $pk_sequence_name = 'over_time_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $contributing_shift_policy_obj = NULL;
	protected $pay_code_obj = NULL;

	//Use the ordering of Type_ID
	//We basically convert all types to Daily OT prior to calculation.
	//Daily time always takes precedence, because more then 12hrs in a day deserves double time.
	//Then Weekly time
	//Then Bi Weekly
	//Then Day Of Week
	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Daily'),
										20 => TTi18n::gettext('Weekly'),
										30 => TTi18n::gettext('Bi-Weekly'), //Need to recalculate two weeks ahead, instead of just one.
										//32 => TTi18n::gettext('Pay Period'), //Need to recalculate in the future as necessary
										//34 => TTi18n::gettext('Monthly'), //Need to recalculate in the future as necessary
										40 => TTi18n::gettext('Sunday'),
										50 => TTi18n::gettext('Monday'),
										60 => TTi18n::gettext('Tuesday'),
										70 => TTi18n::gettext('Wednesday'),
										80 => TTi18n::gettext('Thursday'),
										90 => TTi18n::gettext('Friday'),
										100 => TTi18n::gettext('Saturday'),

										150 => TTi18n::gettext('2 Or More Days/Week Consecutively Worked'),
										151 => TTi18n::gettext('3 Or More Days/Week Consecutively Worked'),
										152 => TTi18n::gettext('4 Or More Days/Week Consecutively Worked'),
										153 => TTi18n::gettext('5 Or More Days/Week Consecutively Worked'),
										154 => TTi18n::gettext('6 Or More Days/Week Consecutively Worked'),
										155 => TTi18n::gettext('7 Or More Days/Week Consecutively Worked'),

										180 => TTi18n::gettext('Holiday'), //Handled in conjunction with Contributing Shift Policies and Daily OT policies.
										200 => TTi18n::gettext('Over Schedule (Daily) / No Schedule'),
										210 => TTi18n::gettext('Over Schedule (Weekly) / No Schedule'),
										//220 => TTi18n::gettext('Over Schedule (Pay Period) / No Schedule'),
										//230 => TTi18n::gettext('Over Schedule (Monthly) / No Schedule'),

										300 => TTi18n::gettext('2 Or More Days Consecutively Worked'),
										301 => TTi18n::gettext('3 Or More Days Consecutively Worked'),
										302 => TTi18n::gettext('4 Or More Days Consecutively Worked'),
										303 => TTi18n::gettext('5 Or More Days Consecutively Worked'),
										304 => TTi18n::gettext('6 Or More Days Consecutively Worked'),
										305 => TTi18n::gettext('7 Or More Days Consecutively Worked'),

										350 => TTi18n::gettext('2nd Consecutive Day Worked'),
										351 => TTi18n::gettext('3rd Consecutive Day Worked'),
										352 => TTi18n::gettext('4th Consecutive Day Worked'),
										353 => TTi18n::gettext('5th Consecutive Day Worked'),
										354 => TTi18n::gettext('6th Consecutive Day Worked'),
										355 => TTi18n::gettext('7th Consecutive Day Worked'),

										//This has to be just by week, otherwise there is no boundary to figure it out?
										400 => TTi18n::gettext('2 Or More Days/Week Worked'),
										401 => TTi18n::gettext('3 Or More Days/Week Worked'),
										402 => TTi18n::gettext('4 Or More Days/Week Worked'),
										403 => TTi18n::gettext('5 Or More Days/Week Worked'),
										404 => TTi18n::gettext('6 Or More Days/Week Worked'),
										405 => TTi18n::gettext('7 Or More Days/Week Worked'),
									);
				break;
			case 'calculation_order':
				$retval = array(
										10 => 90, //Daily
										20 => 200, //Weekly
										30 => 300, //Bi-Weekly
										40 => 20, //Sunday
										50 => 30, //Monday
										60 => 40, //Tuesday
										70 => 50, //Wednesday
										80 => 60, //Thursday
										90 => 70, //Friday
										100 => 80, //Saturday

										150 => 92, //After 2-Days/Week Consecutive Worked
										151 => 91, //After 3-Days/Week Consecutive Worked
										152 => 90, //After 4-Days/Week Consecutive Worked
										153 => 89, //After 5-Days/Week Consecutive Worked
										154 => 88, //After 6-Days/Week Consecutive Worked
										155 => 87, //After 7-Days/Week Consecutive Worked

										300 => 98, //After 2-Days Consecutive Worked
										301 => 97, //After 3-Days Consecutive Worked
										302 => 96, //After 4-Days Consecutive Worked
										303 => 95, //After 5-Days Consecutive Worked
										304 => 94, //After 6-Days Consecutive Worked
										305 => 93, //After 7-Days Consecutive Worked

										//Since these are specific to certain days, they should be calculated before above consecutive policies.
										350 => 86, //2nd Consecutive Day Worked
										351 => 85, //3rd Consecutive Day Worked
										352 => 84, //4th Consecutive Day Worked
										353 => 83, //5th Consecutive Day Worked
										354 => 82, //6th Consecutive Day Worked
										355 => 81, //7th Consecutive Day Worked

										//This these are no consecutive, they should be calculated after consecutive policies.
										400 => 105, //After 2-Days/Week Worked
										401 => 104, //After 3-Days/Week Worked
										402 => 103, //After 4-Days/Week Worked
										403 => 102, //After 5-Days/Week Worked
										404 => 101, //After 6-Days/Week Worked
										405 => 100, //After 7-Days/Week Worked

										180 => 190, //Holiday - This must come after all Daily types, as this usually applies >0hrs and Daily >8 hrs should still apply too.
										200 => 100, //Over Schedule (Daily) / No Schedule
										210 => 210, //Over Schedule (Weekly) / No Schedule
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
										'-1010-type' => TTi18n::gettext('Type'),
										'-1020-name' => TTi18n::gettext('Name'),
										'-1025-description' => TTi18n::gettext('Description'),

										'-1030-trigger_time' => TTi18n::gettext('Active After'),
										'-1040-rate' => TTi18n::gettext('Rate'),
										'-1050-accrual_rate' => TTi18n::gettext('Accrual Rate'),

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
								'type',
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
										'description' => 'Description',
										'trigger_time' => 'TriggerTime',

										'contributing_shift_policy_id' => 'ContributingShiftPolicy',
										'contributing_shift_policy' => FALSE,
										'pay_code_id' => 'PayCode',
										'pay_code' => FALSE,
										'pay_formula_policy_id' => 'PayFormulaPolicy',
										'pay_formula_policy' => FALSE,

										'rate' => 'Rate',
										'wage_group_id' => 'WageGroup',
										'accrual_rate' => 'AccrualRate',
										'accrual_policy_id' => 'AccrualPolicyID',
										'pay_stub_entry_account_id' => 'PayStubEntryAccountId',

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
										
										'in_use' => FALSE,
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	function getContributingShiftPolicyObject() {
		return $this->getGenericObject( 'ContributingShiftPolicyListFactory', $this->getContributingShiftPolicy(), 'contributing_shift_policy_obj' );
	}

	function getPayCodeObject() {
		return $this->getGenericObject( 'PayCodeListFactory', $this->getPayCode(), 'pay_code_obj' );
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

	function getContributingShiftPolicy() {
		if ( isset($this->data['contributing_shift_policy_id']) ) {
			return (int)$this->data['contributing_shift_policy_id'];
		}

		return FALSE;
	}
	function setContributingShiftPolicy($id) {
		$id = trim($id);

		$csplf = TTnew( 'ContributingShiftPolicyListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'contributing_shift_policy_id',
													$csplf->getByID($id),
													TTi18n::gettext('Contributing Shift Policy is invalid')
													) ) {

			$this->data['contributing_shift_policy_id'] = $id;

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

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'trigger_time',
													$int,
													TTi18n::gettext('Incorrect Trigger Time')) ) {
			$this->data['trigger_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getPayCode() {
		if ( isset($this->data['pay_code_id']) ) {
			return (int)$this->data['pay_code_id'];
		}

		return FALSE;
	}
	function setPayCode($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = 0;
		}

		$pclf = TTnew( 'PayCodeListFactory' );

		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'pay_code_id',
														$pclf->getById($id),
														TTi18n::gettext('Invalid Pay Code')
														) ) {
			$this->data['pay_code_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayFormulaPolicy() {
		if ( isset($this->data['pay_formula_policy_id']) ) {
			return (int)$this->data['pay_formula_policy_id'];
		}

		return FALSE;
	}
	function setPayFormulaPolicy($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = 0;
		}

		$pfplf = TTnew( 'PayFormulaPolicyListFactory' );

		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'pay_formula_policy_id',
													$pfplf->getByID($id),
													TTi18n::gettext('Pay Formula Policy is invalid')
													) ) {

			$this->data['pay_formula_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}


	function getHourlyRate( $hourly_rate ) {
		return bcmul( $hourly_rate, $this->getRate() );
	}

	function getRate() {
		if ( isset($this->data['rate']) ) {
			return $this->data['rate'];
		}

		return FALSE;
	}
	function setRate($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isFloat(		'rate',
												$int,
												TTi18n::gettext('Incorrect Rate')) ) {
			$this->data['rate'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getWageGroup() {
		if ( isset($this->data['wage_group_id']) ) {
			return (int)$this->data['wage_group_id'];
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

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isFloat(		'accrual_rate',
												$int,
												TTi18n::gettext('Incorrect Accrual Rate')) ) {
			$this->data['accrual_rate'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrualPolicyID() {
		if ( isset($this->data['accrual_policy_id']) ) {
			return (int)$this->data['accrual_policy_id'];
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
			return (int)$this->data['pay_stub_entry_account_id'];
		}

		return FALSE;
	}
	function setPayStubEntryAccountId($id) {
		$id = trim($id);

		Debug::text('Entry Account ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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

	function isDifferentialCriteriaDefined() {
		if ( $this->getBranchSelectionType() == 10 AND $this->getDepartmentSelectionType() == 10 AND $this->getJobGroupSelectionType() == 10 AND $this->getJobSelectionType() == 10 AND $this->getJobItemGroupSelectionType() == 10 AND $this->getJobItemSelectionType() == 10
			AND $this->getExcludeDefaultBranch() == FALSE AND $this->getExcludeDefaultDepartment() == FALSE AND $this->getExcludeDefaultJob() == FALSE AND $this->getExcludeDefaultJobItem() == FALSE ) {
			return FALSE;
		}

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
				OR $this->Validator->inArrayKey(	'branch_selection_type',
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
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 591, $this->getID() );
	}
	function setBranch($ids) {
		Debug::text('Setting Branch IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 591, $this->getID(), (array)$ids );
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
				OR $this->Validator->inArrayKey(	'department_selection_type',
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
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 592, $this->getID() );
	}
	function setDepartment($ids) {
		Debug::text('Setting Department IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 592, $this->getID(), (array)$ids );
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
				OR $this->Validator->inArrayKey(	'job_group_selection_type',
											$value,
											TTi18n::gettext('Incorrect Job Group Selection Type'),
											$this->getOptions('job_group_selection_type')) ) {

			$this->data['job_group_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJobGroup() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 593, $this->getID() );
	}
	function setJobGroup($ids) {
		Debug::text('Setting Job Group IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 593, $this->getID(), (array)$ids );
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
				OR $this->Validator->inArrayKey(	'job_selection_type',
											$value,
											TTi18n::gettext('Incorrect Job Selection Type'),
											$this->getOptions('job_selection_type')) ) {

			$this->data['job_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJob() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 594, $this->getID() );
	}
	function setJob($ids) {
		Debug::text('Setting Job IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 594, $this->getID(), (array)$ids );
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
				OR $this->Validator->inArrayKey(	'job_item_group_selection_type',
											$value,
											TTi18n::gettext('Incorrect Task Group Selection Type'),
											$this->getOptions('job_item_group_selection_type')) ) {

			$this->data['job_item_group_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItemGroup() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 595, $this->getID() );
	}
	function setJobItemGroup($ids) {
		Debug::text('Setting Task Group IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 595, $this->getID(), (array)$ids );
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
				OR $this->Validator->inArrayKey(	'job_item_selection_type',
											$value,
											TTi18n::gettext('Incorrect Task Selection Type'),
											$this->getOptions('job_item_selection_type')) ) {

			$this->data['job_item_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItem() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 596, $this->getID() );
	}
	function setJobItem($ids) {
		Debug::text('Setting Task IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 596, $this->getID(), (array)$ids );
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

	function Validate() {
		if ( $this->getDeleted() != TRUE ) {
			if ( $this->getPayCode() == 0 ) {
				$this->Validator->isTRUE(	'pay_code_id',
											FALSE,
											TTi18n::gettext('Please choose a Pay Code') );
			}

			//Make sure Pay Formula Policy is defined somewhere.
			if ( $this->getPayFormulaPolicy() == 0 AND $this->getPayCode() > 0 AND ( !is_object( $this->getPayCodeObject() ) OR ( is_object( $this->getPayCodeObject() ) AND $this->getPayCodeObject()->getPayFormulaPolicy() == 0 ) ) ) {
					$this->Validator->isTRUE(	'pay_formula_policy_id',
												FALSE,
												TTi18n::gettext('Selected Pay Code does not have a Pay Formula Policy defined'));
			}
		}

		return TRUE;
	}

	function preSave() {
		//Rate is still a NOT NULL column, so make sure its set no matter what
		if ( $this->getRate() == '' ) {
			$this->setRate( 0 );
		}
		//Accrual Rate is still a NOT NULL column, so make sure its set no matter what
		if ( $this->getAccrualRate() == '' ) {
			$this->setAccrualRate( 0 );
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
						/* Once Flex interface is discontinued we can remove parseTimeUnit from HTML5 interface and do it in the API instead.
						case 'trigger_time':
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
						case 'in_use':
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('OverTime Policy'), NULL, $this->getTable(), $this );
	}
}
?>
