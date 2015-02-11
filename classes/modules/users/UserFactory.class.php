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
 * @package Modules\Users
 */
class UserFactory extends Factory {
	protected $table = 'users';
	protected $pk_sequence_name = 'users_id_seq'; //PK Sequence name

	protected $tmp_data = NULL;
	protected $user_preference_obj = NULL;
	protected $user_tax_obj = NULL;
	protected $company_obj = NULL;
	protected $title_obj = NULL;
	protected $branch_obj = NULL;
	protected $department_obj = NULL;
	protected $group_obj = NULL;
	protected $currency_obj = NULL;

	protected $username_validator_regex = '/^[a-z0-9-_\.@]{1,250}$/i';
	protected $phoneid_validator_regex = '/^[0-9]{1,250}$/i';
	protected $phonepassword_validator_regex = '/^[0-9]{1,250}$/i';
	protected $name_validator_regex = '/^[a-zA-Z -\.\'|\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										//Add System users (for APIs and reseller admin accounts)
										//Add "New Hire" status for employees going through the onboarding process or newly imported employees.
										10 => TTi18n::gettext('Active'),
										11 => TTi18n::gettext('Inactive'), //Add option that isn't terminated/leave but is still not billed/active.
										12 => TTi18n::gettext('Leave - Illness/Injury'),
										14 => TTi18n::gettext('Leave - Maternity/Parental'),
										16 => TTi18n::gettext('Leave - Other'),
										20 => TTi18n::gettext('Terminated'),
									);
				break;
			case 'sex':
				$retval = array(
										5 => TTi18n::gettext('Unspecified'),
										10 => TTi18n::gettext('Male'),
										20 => TTi18n::gettext('Female'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1005-company' => TTi18n::gettext('Company'),
										'-1010-employee_number' => TTi18n::gettext('Employee #'),
										'-1020-status' => TTi18n::gettext('Status'),
										'-1030-user_name' => TTi18n::gettext('User Name'),
										'-1040-phone_id' => TTi18n::gettext('Quick Punch ID'),

										'-1060-first_name' => TTi18n::gettext('First Name'),
										'-1070-middle_name' => TTi18n::gettext('Middle Name'),
										'-1080-last_name' => TTi18n::gettext('Last Name'),
										'-1082-full_name' => TTi18n::gettext('Full Name'),

										'-1090-title' => TTi18n::gettext('Title'),
										'-1099-user_group' => TTi18n::gettext('Group'), //Update ImportUser class if sort order is changed for this.
										'-1100-ethnic_group' => TTi18n::gettext('Ethnicity'),
										'-1102-default_branch' => TTi18n::gettext('Branch'),
										'-1103-default_department' => TTi18n::gettext('Department'),
										'-1104-default_job' => TTi18n::gettext('Job'),
										'-1105-default_job_item' => TTi18n::gettext('Task'),
										'-1106-currency' => TTi18n::gettext('Currency'),

										'-1108-permission_control' => TTi18n::gettext('Permission Group'),
										'-1110-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1112-policy_group' => TTi18n::gettext('Policy Group'),

										'-1120-sex' => TTi18n::gettext('Gender'),

										'-1130-address1' => TTi18n::gettext('Address 1'),
										'-1140-address2' => TTi18n::gettext('Address 2'),

										'-1150-city' => TTi18n::gettext('City'),
										'-1160-province' => TTi18n::gettext('Province/State'),
										'-1170-country' => TTi18n::gettext('Country'),
										'-1180-postal_code' => TTi18n::gettext('Postal Code'),
										'-1190-work_phone' => TTi18n::gettext('Work Phone'),
										'-1191-work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
										'-1200-home_phone' => TTi18n::gettext('Home Phone'),
										'-1210-mobile_phone' => TTi18n::gettext('Mobile Phone'),
										'-1220-fax_phone' => TTi18n::gettext('Fax Phone'),
										'-1230-home_email' => TTi18n::gettext('Home Email'),
										'-1240-work_email' => TTi18n::gettext('Work Email'),
										'-1250-birth_date' => TTi18n::gettext('Birth Date'),
										'-1251-birth_date_age' => TTi18n::gettext('Age'),
										'-1260-hire_date' => TTi18n::gettext('Hire Date'),
										'-1261-hire_date_age' => TTi18n::gettext('Length of Service'),
										'-1270-termination_date' => TTi18n::gettext('Termination Date'),
										'-1280-sin' => TTi18n::gettext('SIN/SSN'),
										'-1290-note' => TTi18n::gettext('Note'),
										'-1300-tag' => TTi18n::gettext('Tags'),
										'-1400-hierarchy_control_display' => TTi18n::gettext('Hierarchy'),
										'-1401-hierarchy_level_display' => TTi18n::gettext('Hierarchy Superiors'),
										'-1500-last_login_date' => TTi18n::gettext('Last Login Date'),
										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'user_secure_columns': //Regular employee secure columns (Used in MessageFactory)
				$retval = array(
								'first_name',
								'middle_name',
								'last_name',
								);
				$retval = Misc::arrayIntersectByKey( $retval, Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'user_child_secure_columns': //Superior employee secure columns (Used in MessageFactory)
				$retval = array(
								'first_name',
								'middle_name',
								'last_name',
								'title',
								'user_group',
								'default_branch',
								'default_department',
								);
				$retval = Misc::arrayIntersectByKey( $retval, Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'status',
								'employee_number',
								'first_name',
								'last_name',
								'home_phone',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'user_name',
								'phone_id',
								'employee_number',
								'sin'
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								'country',
								'province',
								'postal_code'
								);
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'company' => FALSE,
										'status_id' => 'Status',
										'status' => FALSE,
										'group_id' => 'Group',
										'user_group' => FALSE,
										'ethnic_group_id' => 'EthnicGroup',
										'ethnic_group' => FALSE,
										'user_name' => 'UserName',
										'password' => 'Password',
										'phone_id' => 'PhoneId',
										'phone_password' => 'PhonePassword',
										'employee_number' => 'EmployeeNumber',
										'title_id' => 'Title',
										'title' => FALSE,
										'default_branch_id' => 'DefaultBranch',
										'default_branch' => FALSE,
										'default_branch_manual_id' => FALSE,
										'default_department_id' => 'DefaultDepartment',
										'default_department' => FALSE,
										'default_department_manual_id' => FALSE,
										'default_job_id' => 'DefaultJob',
										'default_job' => FALSE,
										'default_job_manual_id' => FALSE,
										'default_job_item_id' => 'DefaultJobItem',
										'default_job_item' => FALSE,
										'default_job_item_manual_id' => FALSE,
										'permission_control_id' => 'PermissionControl',
										'permission_control' => FALSE,
										'pay_period_schedule_id' => 'PayPeriodSchedule',
										'pay_period_schedule' => FALSE,
										'policy_group_id' => 'PolicyGroup',
										'policy_group' => FALSE,
										'hierarchy_control' => 'HierarchyControl',
										'first_name' => 'FirstName',
										'first_name_metaphone' => 'FirstNameMetaphone',
										'middle_name' => 'MiddleName',
										'last_name' => 'LastName',
										'last_name_metaphone' => 'LastNameMetaphone',
										'full_name' => 'FullName',
										'second_last_name' => 'SecondLastName',
										'sex_id' => 'Sex',
										'sex' => FALSE,
										'address1' => 'Address1',
										'address2' => 'Address2',
										'city' => 'City',
										'country' => 'Country',
										'province' => 'Province',
										'postal_code' => 'PostalCode',
										'work_phone' => 'WorkPhone',
										'work_phone_ext' => 'WorkPhoneExt',
										'home_phone' => 'HomePhone',
										'mobile_phone' => 'MobilePhone',
										'fax_phone' => 'FaxPhone',
										'home_email' => 'HomeEmail',
										'home_email_is_valid' => 'HomeEmailIsValid',
										'home_email_is_valid_key' => 'HomeEmailIsValidKey',
										'home_email_is_valid_date' => 'HomeEmailIsValidDate',

										'work_email' => 'WorkEmail',
										'work_email_is_valid' => 'WorkEmailIsValid',
										'work_email_is_valid_key' => 'WorkEmailIsValidKey',
										'work_email_is_valid_date' => 'WorkEmailIsValidDate',

										'birth_date' => 'BirthDate',
										'birth_date_age' => FALSE,
										'hire_date' => 'HireDate',
										'hire_date_age' => FALSE,
										'termination_date' => 'TerminationDate',
										'currency_id' => 'Currency',
										'currency' => FALSE,
										'currency_rate' => FALSE,
										'sin' => 'SIN',
										'other_id1' => 'OtherID1',
										'other_id2' => 'OtherID2',
										'other_id3' => 'OtherID3',
										'other_id4' => 'OtherID4',
										'other_id5' => 'OtherID5',
										'note' => 'Note',
										'longitude' => 'Longitude',
										'latitude' => 'Latitude',
										'tag' => 'Tag',
										'last_login_date' => 'LastLoginDate',
										'hierarchy_control_display' => FALSE,
										'hierarchy_level_display' => FALSE,

										//These must be defined, but they are ignored in setObjectFromArray() due to security risks.
										'password_reset_key' => 'PasswordResetKey', 
										'password_reset_date' => 'PasswordResetDate',
										'password_updated_date' => 'PasswordUpdatedDate', //Needs to be defined otherwise password_updated_date never gets set. Also needs to go before setPassword() as it updates the date too.

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserPreferenceObject() {
		$retval = $this->getGenericObject( 'UserPreferenceListFactory', $this->getID(), 'user_preference_obj', 'getByUserId', 'getUser' );

		//Always bootstrap the user preferences if none exist.
		if ( !is_object( $retval ) ) {
			Debug::Text('NO PREFERENCES SET FOR USER ID: '. $this->getID() .' Using Defaults...', __FILE__, __LINE__, __METHOD__, 10);
			$this->user_preference_obj = TTnew( 'UserPreferenceFactory' );
			$this->user_preference_obj->setUser( $this->getID() );

			return $this->user_preference_obj;
		}

		return $retval;
	}

	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	function getTitleObject() {
		return $this->getGenericObject( 'UserTitleListFactory', $this->getTitle(), 'title_obj' );
	}

	function getDefaultBranchObject() {
		return $this->getGenericObject( 'BranchListFactory', $this->getDefaultBranch(), 'branch_obj' );
	}

	function getDefaultDepartmentObject() {
		return $this->getGenericObject( 'DepartmentListFactory', $this->getDefaultDepartment(), 'department_obj' );
	}

	function getGroupObject() {
		return $this->getGenericObject( 'UserGroupListFactory', $this->getGroup(), 'group_obj' );
	}

	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
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

		$modify_status = FALSE;
		if ( $this->getCurrentUserPermissionLevel() >= $this->getPermissionLevel() ) {
			$modify_status = TRUE;
		} elseif (	$this->getStatus() == $status ) { //No modification made.
			$modify_status = TRUE;
		}

		if ( $this->Validator->inArrayKey(	'status_id',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status'))
				AND
				$this->Validator->isTrue(		'status_id',
												$modify_status,
												TTi18n::gettext('Insufficient access to modify status for this employee')
												)
			) {

			$this->data['status_id'] = $status;

			return TRUE;
		}

		return FALSE;
	}

	function getGroup() {
		if ( isset($this->data['group_id']) ) {
			return (int)$this->data['group_id'];
		}

		return FALSE;
	}
	function setGroup($id) {
		$id = (int)trim($id);

		$uglf = TTnew( 'UserGroupListFactory' );
		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'group',
														$uglf->getByID($id),
														TTi18n::gettext('Group is invalid')
													) ) {

			$this->data['group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPermissionLevel() {
		$permission = new Permission();
		return $permission->getLevel( $this->getID(), $this->getCompany() );
	}

	function getCurrentUserPermissionLevel() {
		//Get currently logged in users permission level, so we can ensure they don't assign another user to a higher level.
		global $current_user;
		if ( isset($current_user) AND is_object($current_user) ) {
			$permission = new Permission();
			$current_user_permission_level = $permission->getLevel( $current_user->getId(), $current_user->getCompany() );
		} else {
			//If we can't find the current_user object, we need to allow any permission group to be assigned, in case
			//its being modified from raw factory calls.
			$current_user_permission_level = 100;
		}

		Debug::Text('Current User Permission Level: '. $current_user_permission_level, __FILE__, __LINE__, __METHOD__, 10);
		return $current_user_permission_level;
	}

	function getPermissionControl() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['permission_control_id']) ) {
			return $this->tmp_data['permission_control_id'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			$pclfb = TTnew( 'PermissionControlListFactory' );
			$pclfb->getByCompanyIdAndUserId( $this->getCompany(), $this->getID() );
			if ( $pclfb->getRecordCount() > 0 ) {
				return $pclfb->getCurrent()->getId();
			}
		}

		return FALSE;
	}
	function setPermissionControl($id) {
		$id = (int)trim($id);

		$pclf = TTnew( 'PermissionControlListFactory' );

		$current_user_permission_level = $this->getCurrentUserPermissionLevel();

		$modify_permissions = FALSE;
		if ( $current_user_permission_level >= $this->getPermissionLevel() ) {
			$modify_permissions = TRUE;
		}

		global $current_user;
		if ( is_object($current_user) AND $current_user->getID() == $this->getID() AND $id != $this->getPermissionControl() ) { //Acting on currently logged in user.
			$logged_in_modify_permissions = FALSE; //Must be false for validation to fail.
		} else {
			$logged_in_modify_permissions = TRUE;
		}


		//Don't allow permissions to be modified if the currently logged in user has a lower permission level.
		//As such if someone with a lower level is able to edit the user of higher level, they must not call this function at all, or use a blank value.
		if (	$id != ''
				AND
				$this->Validator->isResultSetWithRows(		'permission_control_id',
															$pclf->getByIDAndLevel($id, $current_user_permission_level),
															TTi18n::gettext('Permission Group is invalid')
															)
				AND
				$this->Validator->isTrue(		'permission_control_id',
												$modify_permissions,
												TTi18n::gettext('Insufficient access to modify permissions for this employee')
												)
				AND
				$this->Validator->isTrue(		'permission_control_id',
												$logged_in_modify_permissions,
												TTi18n::gettext('Unable to change permissions of your own record')
												)
				) {
			$this->tmp_data['permission_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayPeriodSchedule() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['pay_period_schedule_id']) ) {
			return $this->tmp_data['pay_period_schedule_id'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			$ppslfb = TTnew( 'PayPeriodScheduleListFactory' );
			$ppslfb->getByUserId( $this->getID() );
			if ( $ppslfb->getRecordCount() > 0 ) {
				return $ppslfb->getCurrent()->getId();
			}
		}

		return FALSE;
	}
	function setPayPeriodSchedule($id) {
		$id = (int)trim($id);

		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'pay_period_schedule_id',
															$ppslf->getByID($id),
															TTi18n::gettext('Pay Period schedule is invalid')
															) ) {
			$this->tmp_data['pay_period_schedule_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPolicyGroup() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['policy_group_id']) ) {
			return $this->tmp_data['policy_group_id'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getByUserIds( $this->getID());
			if ( $pglf->getRecordCount() > 0 ) {
				return $pglf->getCurrent()->getId();
			}
		}

		return FALSE;
	}
	function setPolicyGroup($id) {
		$id = (int)trim($id);

		$pglf = TTnew( 'PolicyGroupListFactory' );

		if (	$id != ''
				AND
				(
					$id == 0
					OR $this->Validator->isResultSetWithRows(	'policy_group_id',
																$pglf->getByID($id),
																TTi18n::gettext('Policy Group is invalid')
																)
				)
				) {
			$this->tmp_data['policy_group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//Display each superior that the employee is assigned too.
	function getHierarchyLevelDisplay() {
		$hllf = new HierarchyLevelListFactory();
		$hllf->getObjectTypeAndHierarchyAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
		if ( $hllf->getRecordCount() > 0 ) {
			foreach( $hllf as $hl_obj ) {
				if ( is_object($hl_obj->getUserObject() ) ) {
					$hierarchy_control_retval[$hl_obj->getColumn('hierarchy_control_name')][] = $hl_obj->getLevel().'.'. $hl_obj->getUserObject()->getFullName(); //Don't add space after "." to prevent word wrap after the level.
				}
			}

			if ( isset($hierarchy_control_retval) ) {
				$enable_display_hierarchy_control_name = FALSE;
				if ( count($hierarchy_control_retval) > 1 ) {
					$enable_display_hierarchy_control_name = TRUE;
				}
				$retval = '';
				foreach( $hierarchy_control_retval as $hierarchy_control_name => $levels ) {
					if ( $enable_display_hierarchy_control_name == TRUE ) {
						$retval .= $hierarchy_control_name.': ['.implode(', ', $levels ) .'] '; //Include space after, so wordwrap can function better.
					} else {
						$retval .= implode(', ', $levels ); //Include space after, so wordwrap can function better.
					}
				}

				return trim($retval);
			}
		}

		return FALSE;
	}

	//Display each hierarchy that the employee is assigned too.
	function getHierarchyControlDisplay() {
		$hclf = TTnew( 'HierarchyControlListFactory' );
		$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
		$data = $hclf->getArrayByListFactory( $hclf, FALSE, FALSE, TRUE );

		if ( is_array($data) ) {
			$retval = array();
			foreach( $data as $id => $name ) {
				$retval[] = $name;
			}

			sort($retval); //Maintain consistent order.

			return implode(', ', $retval ); //Add space so wordwrap has a chance.
		}

		return FALSE;
	}

	function getHierarchyControl() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['hierarchy_control']) ) {
			return $this->tmp_data['hierarchy_control'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			$hclf = TTnew( 'HierarchyControlListFactory' );
			$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
			return $hclf->getArrayByListFactory( $hclf, FALSE, TRUE, FALSE );
		}

		return FALSE;
	}
	function setHierarchyControl($data) {
		if ( !is_array($data) ) {
			return FALSE;
		}

		//array passed in is hierarchy_object_type_id => hierarchy_control_id
		if ( is_array($data) ) {
			$hclf = TTnew( 'HierarchyControlListFactory' );
			Debug::Arr($data, 'Hierarchy Control Data: ', __FILE__, __LINE__, __METHOD__, 10);

			foreach( $data as $hierarchy_object_type_id => $hierarchy_control_id ) {
				$hierarchy_control_id = Misc::trimSortPrefix( $hierarchy_control_id );

				if (	$hierarchy_control_id == 0
						OR
						$this->Validator->isResultSetWithRows(		'hierarchy_control_id',
																	$hclf->getByID($hierarchy_control_id),
																	TTi18n::gettext('Hierarchy is invalid')
																	) ) {
					$this->tmp_data['hierarchy_control'][$hierarchy_object_type_id] = $hierarchy_control_id;
				} else {
					return FALSE;
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueUserName($user_name) {
		$ph = array(
					'user_name' => trim(strtolower($user_name)),
					);

		$query = 'select id from '. $this->getTable() .' where user_name = ? AND deleted=0';
		$user_name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_name_id, 'Unique User Name: '. $user_name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $user_name_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getUserName() {
		if ( isset($this->data['user_name']) ) {
			return $this->data['user_name'];
		}

		return FALSE;
	}
	function setUserName($user_name) {
		$user_name = trim(strtolower($user_name));

		if	(	$this->Validator->isRegEx(		'user_name',
												$user_name,
												TTi18n::gettext('Incorrect characters in user name'),
												$this->username_validator_regex)
					AND
						$this->Validator->isLength(		'user_name',
														$user_name,
														TTi18n::gettext('Incorrect user name length'),
														3,
														250)
					AND
						$this->Validator->isTrue(		'user_name',
														$this->isUniqueUserName($user_name),
														TTi18n::gettext('User name is already taken')
														)
			) {

			$this->data['user_name'] = $user_name;

			return TRUE;
		}

		return FALSE;
	}

	function getPasswordSalt() {
		global $config_vars;

		if ( isset($config_vars['other']['salt']) AND $config_vars['other']['salt'] != '' ) {
			$retval = $config_vars['other']['salt'];
		} else {
			$retval = 'ttsalt03198238';
		}

		return trim($retval);
	}

	function getPasswordVersion( $encrypted_password = FALSE ) {
		if ( $encrypted_password == '' ) {
			$encrypted_password = $this->getPassword();
		}
		
		$split_password = explode(':', $encrypted_password );
		if ( is_array($split_password) AND count($split_password) == 2 ) {
			$version = $split_password[0];
		} else {
			$version = 1;
		}

		return $version;
	}
	
	//Always default to latest password version.
	function encryptPassword( $password, $version = 2 ) {
		$password = trim($password);
		
		//Handle password migration/versioning
		switch( (int)$version ) {
			case 2: //v2
				//Case sensitive, uses sha512 and company/user specific salt.
				//Prepend with password version.
				//
				//IMPORTANT: When creating a new user, the ID must be defined before this is called, otherwise the hash is incorrect.
				//           This manifests itself as an incorrect password when its first created, but can be changed and then starts working.
				//
				$encrypted_password = '2:'. hash( 'sha512', $this->getPasswordSalt() . (int)$this->getCompany() . (int)$this->getID() . $password );
				break;
			default: //v1
				//Case insensitive, uses sha1 and global salt.
				$encrypted_password = sha1( $this->getPasswordSalt() . strtolower($password) );
				break;
		}
		unset($password);

		return $encrypted_password;
	}

	function checkPassword($password, $check_password_policy = TRUE ) {
		global $config_vars;

		$password = trim( html_entity_decode( $password ) );

		//Don't bother checking a blank password, this can help avoid issues with LDAP settings.
		if ( $password == '' ) {
			Debug::Text('Password is blank, ignoring...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Check if LDAP is enabled
		$ldap_authentication_type_id = 0;
		if ( DEMO_MODE != TRUE AND function_exists('ldap_connect') AND !isset($config_vars['other']['enable_ldap']) OR ( isset($config_vars['other']['enable_ldap']) AND $config_vars['other']['enable_ldap'] == TRUE ) ) {
			//Check company object to make sure LDAP is enabled.
			$c_obj = $this->getCompanyObject();
			if ( is_object($this->getCompanyObject()) ) {
				$ldap_authentication_type_id = $this->getCompanyObject()->getLDAPAuthenticationType();
				if ( $ldap_authentication_type_id > 0 ) {
					$ldap = TTnew('TTLDAP');
					$ldap->setHost( $this->getCompanyObject()->getLDAPHost() );
					$ldap->setPort( $this->getCompanyObject()->getLDAPPort() );
					$ldap->setBindUserName( $this->getCompanyObject()->getLDAPBindUserName() );
					$ldap->setBindPassword( $this->getCompanyObject()->getLDAPBindPassword() );
					$ldap->setBaseDN( $this->getCompanyObject()->getLDAPBaseDN() );
					$ldap->setBindAttribute( $this->getCompanyObject()->getLDAPBindAttribute() );
					$ldap->setUserFilter( $this->getCompanyObject()->getLDAPUserFilter() );
					$ldap->setLoginAttribute( $this->getCompanyObject()->getLDAPLoginAttribute() );
					if (  $ldap->authenticate( $this->getUserName(), $password ) === TRUE ) {
						return TRUE;
					} elseif ( $ldap_authentication_type_id == 1 ) {
						Debug::Text('LDAP authentication failed, falling back to local password...', __FILE__, __LINE__, __METHOD__, 10);
						TTLog::addEntry( $this->getId(), 510, TTi18n::getText('LDAP Authentication failed, falling back to local password for username').': '. $this->getUserName() . TTi18n::getText('IP Address') .': '.$_SERVER['REMOTE_ADDR'], $this->getId(), $this->getTable() );
					}
					unset($ldap);
				} else {
					Debug::Text('LDAP authentication is not enabled...', __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		} else {
			Debug::Text('LDAP authentication disabled due to config or extension missing...', __FILE__, __LINE__, __METHOD__, 10);
		}

		$password_version = $this->getPasswordVersion();
		$encrypted_password = $this->encryptPassword( $password, $password_version );

		//Don't check local TT passwords if LDAP Only authentication is enabled. Still accept override passwords though.
		if ( $ldap_authentication_type_id != 2 AND $encrypted_password === $this->getPassword() ) {
			//If the passwords match, confirm that the password hasn't exceeded its maximum age.
			//Allow override passwords always.
			if ( $check_password_policy == TRUE AND $this->checkPasswordAge() == FALSE ) {
				Debug::Text('Password Policy: Password exceeds maximum age, denying access...', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			} else {
				//If password version is not the latest, update the password version when it successfully matches.
				if ( $password_version < 2 ) {
					Debug::Text('Converting password to latest encryption version...', __FILE__, __LINE__, __METHOD__, 10);
					$this->db->Execute( 'UPDATE '. $this->getTable() .' SET password = ? where id = ?', array( 'password' => $this->encryptPassword( $password ), 'id' => (int)$this->getID() ) );
					unset($password);
				}

				return TRUE; //Password accepted.
			}
		} elseif ( isset($config_vars['other']['override_password_prefix'])
						AND $config_vars['other']['override_password_prefix'] != '' ) {
			//Check override password
			if ( $encrypted_password == $this->encryptPassword( trim( trim( $config_vars['other']['override_password_prefix'] ).substr($this->getUserName(), 0, 2) ), $password_version ) ) {
				TTLog::addEntry( $this->getId(), 510, TTi18n::getText('Override Password successful from IP Address').': '. $_SERVER['REMOTE_ADDR'], NULL, $this->getTable() );
				return TRUE;
			}
		}

		return FALSE;
	}
	function getPassword() {
		if ( isset($this->data['password']) ) {
			return $this->data['password'];
		}

		return FALSE;
	}
	function setPassword($password, $password_confirm = NULL ) {
		$password = trim($password);
		$password_confirm = ( $password_confirm !== NULL ) ? trim($password_confirm) : $password_confirm;

		//Make sure we accept just $password being set otherwise setObjectFromArray() won't work correctly.
		if ( ( $password != '' AND $password_confirm != '' AND $password === $password_confirm ) OR ( $password != '' AND $password_confirm === NULL ) ) {
			$passwords_match = TRUE;
		} else {
			$passwords_match = FALSE;
		}
		Debug::Text('Password: '. $password .' Confirm: '. $password_confirm .' Match: '. (int)$passwords_match, __FILE__, __LINE__, __METHOD__, 10);

		$modify_password = FALSE;
		if ( $this->getCurrentUserPermissionLevel() >= $this->getPermissionLevel() ) {
			$modify_password = TRUE;
		}

		if	(	$password != ''
				AND
				$this->Validator->isLength(		'password',
												$password,
												TTi18n::gettext('Incorrect password length'),
												4,
												64)
				AND
				$this->Validator->isTrue(		'password',
												$passwords_match,
												TTi18n::gettext('Passwords don\'t match') )
				AND
				$this->Validator->isTrue(		'password',
												$modify_password,
												TTi18n::gettext('Insufficient access to modify passwords for this employee')
												)
				) {

			$update_password = TRUE;

			//When changing the password, we need to check if a Password Policy is defined.
			$c_obj = $this->getCompanyObject();
			if ( is_object( $c_obj ) AND $c_obj->getPasswordPolicyType() == 1 AND $this->getPermissionLevel() >= $c_obj->getPasswordMinimumPermissionLevel() AND $c_obj->getProductEdition() > 10 ) {
				Debug::Text('Password Policy: Minimum Length: '. $c_obj->getPasswordMinimumLength() .' Min. Strength: '. $c_obj->getPasswordMinimumStrength() .' ('.  Misc::getPasswordStrength( $password ) .') Age: '. $c_obj->getPasswordMinimumAge(), __FILE__, __LINE__, __METHOD__, 10);

				if ( strlen( $password ) < $c_obj->getPasswordMinimumLength() ) {
					$update_password = FALSE;
					$this->Validator->isTrue(		'password',
													FALSE,
													TTi18n::gettext('Password is too short') );
				}

				if ( Misc::getPasswordStrength( $password ) <= $c_obj->getPasswordMinimumStrength() ) {
					$update_password = FALSE;
					$this->Validator->isTrue(		'password',
													FALSE,
													TTi18n::gettext('Password is too weak, add additional numbers or special characters') );
				}

				if ( $this->getPasswordUpdatedDate() != '' AND $this->getPasswordUpdatedDate() >= ( time() - ($c_obj->getPasswordMinimumAge() * 86400) ) ) {
					$update_password = FALSE;
					$this->Validator->isTrue(		'password',
													FALSE,
													TTi18n::gettext('Password must reach its minimum age before it can be changed again') );
				}

				if ( $this->getId() > 0 ) {
					$uilf = TTnew( 'UserIdentificationListFactory' );
					$uilf->getByUserIdAndTypeIdAndValue( $this->getId(), 5, $this->encryptPassword( $password ) );
					if ( $uilf->getRecordCount() > 0 ) {
						$update_password = FALSE;
						$this->Validator->isTrue(		'password',
														FALSE,
														TTi18n::gettext('Password has already been used in the past, please choose a new one') );
					}
					unset($uilf);
				}
			} //else { //Debug::Text('Password Policy disabled or does not apply to this user.', __FILE__, __LINE__, __METHOD__, 10);

			if ( $update_password === TRUE ) {
				Debug::Text('Setting new password...', __FILE__, __LINE__, __METHOD__, 10);
				$this->data['password'] = $this->encryptPassword( $password ); //Assumes latest password version is used.
				$this->setPasswordUpdatedDate( time() );
				$this->setEnableClearPasswordResetData( TRUE ); //Clear any outstanding password reset key to prevent unexpected changes later on.
			}

			return TRUE;
		}

		return FALSE;
	}

	function checkPasswordAge() {
		$c_obj = $this->getCompanyObject();
		//Always add 1 to the PasswordMaximumAge so if its set to 0 by mistake it will still allow the user to login after changing their password.
		Debug::Text('Password Policy: Type: '. $c_obj->getPasswordPolicyType() .'('.$c_obj->getProductEdition().') Current Age: '. TTDate::getDays( (time() - $this->getPasswordUpdatedDate()) ) .'('.$this->getPasswordUpdatedDate().') Maximum Age: '. $c_obj->getPasswordMaximumAge() .' days Permission Level: '. $this->getPermissionLevel(), __FILE__, __LINE__, __METHOD__, 10);
		if ( PRODUCTION == TRUE AND is_object( $c_obj ) AND $c_obj->getPasswordPolicyType() == 1 AND $this->getPermissionLevel() >= $c_obj->getPasswordMinimumPermissionLevel() AND (int)$this->getPasswordUpdatedDate() < ( time() - (($c_obj->getPasswordMaximumAge() + 1) * 86400) ) AND $c_obj->getProductEdition() > 10 ) {
			Debug::Text('Password Policy: Password exceeds maximum age, denying access...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}
		return TRUE;
	}
	function getPasswordUpdatedDate() {
		if ( isset($this->data['password_updated_date']) ) {
			return $this->data['password_updated_date'];
		}

		return FALSE;
	}
	function setPasswordUpdatedDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if	(	$epoch == ''
				OR
				$this->Validator->isDate(		'password_updated_date',
												$epoch,
												TTi18n::gettext('Password updated date is invalid')) ) {

			Debug::Text('Setting new password date: '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);
			$this->data['password_updated_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function isUniquePhoneId($phone_id) {
		$ph = array(
					'phone_id' => $phone_id,
					);

		$query = 'select id from '. $this->getTable() .' where phone_id = ? and deleted = 0';
		$phone_id = $this->db->GetOne($query, $ph);
		Debug::Arr($phone_id, 'Unique Phone ID:', __FILE__, __LINE__, __METHOD__, 10);

		if ( $phone_id === FALSE ) {
			return TRUE;
		} else {
			if ($phone_id == $this->getId() ) {
				return TRUE;
			}
		}
		return FALSE;
	}
	function getPhoneId() {
		if ( isset($this->data['phone_id']) ) {
			return (string)$this->data['phone_id']; //Should not be cast to INT
		}

		return FALSE;
	}
	function setPhoneId($phone_id) {
		$phone_id = trim($phone_id);

		if	(
				$phone_id == ''
				OR
				(
					$this->Validator->isRegEx(		'phone_id',
													$phone_id,
													TTi18n::gettext('Quick Punch ID must be digits only'),
													$this->phoneid_validator_regex)
				AND
					$this->Validator->isLength(		'phone_id',
													$phone_id,
													TTi18n::gettext('Incorrect Quick Punch ID length'),
													4,
													8)
				AND
					$this->Validator->isTrue(		'phone_id',
													$this->isUniquePhoneId($phone_id),
													TTi18n::gettext('Quick Punch ID is already in use, please try a different one')
													)
				)
			) {

			$this->data['phone_id'] = $phone_id;

			return TRUE;
		}

		return FALSE;
	}

	function checkPhonePassword($password) {
		$password = trim($password);

		if ( $password == $this->getPhonePassword() ) {
			return TRUE;
		}

		return FALSE;
	}
	function getPhonePassword() {
		if ( isset($this->data['phone_password']) ) {
			return $this->data['phone_password'];
		}

		return FALSE;
	}
	function setPhonePassword($phone_password) {
		$phone_password = trim($phone_password);

		//Phone passwords are now displayed the administrators to make things easier.
		//NOTE: Phone passwords are used for passwords on the timeclock as well, and need to be able to be cleared sometimes.
		//Limit phone password to max of 9 digits so we don't overflow an integer on the timeclocks. (10 digits, but maxes out at 2billion)
		if	(	$phone_password == ''
				OR (
				$this->Validator->isRegEx(		'phone_password',
												$phone_password,
												TTi18n::gettext('Quick Punch password must be digits only'),
												$this->phonepassword_validator_regex)
				AND
					$this->Validator->isLength(		'phone_password',
													$phone_password,
													TTi18n::gettext('Quick Punch password must be between 4 and 9 digits'),
													4,
													9) ) ) {

			$this->data['phone_password'] = $phone_password;

			return TRUE;
		}

		return FALSE;
	}

	static function getNextAvailableEmployeeNumber( $company_id = NULL ) {
		global $current_company;

		if ( $company_id == '' ANd is_object($current_company) ) {
			$company_id = $current_company->getId();
		} elseif ( $company_id == '' AND isset($this) AND is_object($this) ) {
			$company_id = $this->getCompany();
		}

		$ulf = TTNew('UserListFactory');
		$ulf->getHighestEmployeeNumberByCompanyId( $company_id );
		if ( $ulf->getRecordCount() > 0 ) {
			Debug::Text('Highest Employee Number: '. $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__, 10);
			if ( is_numeric( $ulf->getCurrent()->getEmployeeNumber() ) == TRUE ) {
				return ($ulf->getCurrent()->getEmployeeNumber() + 1);
			} else {
				Debug::Text('Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__, 10);
				return NULL;
			}
		} else {
			return 1;
		}
	}

	function isUniqueEmployeeNumber($id) {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		if ( $id == 0 ) {
			return FALSE;
		}

		$ph = array(
					'manual_id' => (int)$id, //Make sure cast this to an int so we can handle overflows above PHP_MAX_INT properly.
					'company_id' =>	$this->getCompany(),
					);

		$query = 'select id from '. $this->getTable() .' where employee_number = ? AND company_id = ? AND deleted = 0';
		$user_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_id, 'Unique Employee Number: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $user_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function checkEmployeeNumber($id) {
		$id = trim($id);

		//Use employee ID for now.
		//if ( $id == $this->getID() ) {
		if ( $id == $this->getEmployeeNumber() ) {
			return TRUE;
		}

		return FALSE;
	}
	function getEmployeeNumber() {
		if ( isset($this->data['employee_number']) AND $this->data['employee_number'] != '' ) {
			return (int)$this->data['employee_number'];
		}

		return FALSE;
	}
	function setEmployeeNumber($value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );

		//Allow setting a blank employee number, so we can use Validate() to check employee number against the status_id
		//To allow terminated employees to have a blank employee number, but active ones always have a number.
		if (
				$value == ''
				OR (
					$this->Validator->isNumeric(	'employee_number',
													$value,
													TTi18n::gettext('Employee number must only be digits'))
					AND
					$this->Validator->isTrue(		'employee_number',
													$this->isUniqueEmployeeNumber($value),
													TTi18n::gettext('Employee number is already in use, please enter a different one'))
				)
												) {
			if ( $value != '' AND $value >= 0 ) {
				$value = (int)$value;
			}

			$this->data['employee_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getTitle() {
		if ( isset($this->data['title_id']) ) {
			return (int)$this->data['title_id'];
		}

		return FALSE;
	}
	function setTitle($id) {
		$id = (int)trim($id);

		$utlf = TTnew( 'UserTitleListFactory' );
		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'title',
														$utlf->getByID($id),
														TTi18n::gettext('Title is invalid')
													) ) {

			$this->data['title_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getEthnicGroup() {
		if ( isset( $this->data['ethnic_group_id'] ) ) {
			return (int)$this->data['ethnic_group_id'];
		}
		return FALSE;
	}

	function setEthnicGroup($id) {
		$id = (int)trim($id);
		$eglf = TTnew( 'EthnicGroupListFactory' );

		if ( $id == 0
				OR
			$this->Validator->isResultSetWithRows( 'ethnic_group',
													$eglf->getById($id),
													TTi18n::gettext('Ethnic Group is invalid')
												) ) {
			$this->data['ethnic_group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultJob() {
		if ( isset($this->data['default_job_id']) ) {
			return (int)$this->data['default_job_id'];
		}

		return FALSE;
	}
	function setDefaultJob($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		Debug::Text('Default Job ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' );
		}

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'default_job_id',
														$jlf->getByID($id),
														TTi18n::gettext('Invalid Default Job')
													) ) {

			$this->data['default_job_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultJobItem() {
		if ( isset($this->data['default_job_item_id']) ) {
			return (int)$this->data['default_job_item_id'];
		}

		return FALSE;
	}
	function setDefaultJobItem($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		Debug::Text('Default Job Item ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jilf = TTnew( 'JobItemListFactory' );
		}

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'default_job_item_id',
														$jilf->getByID($id),
														TTi18n::gettext('Invalid Default Task')
													) ) {

			$this->data['default_job_item_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultBranch() {
		if ( isset($this->data['default_branch_id']) ) {
			return (int)$this->data['default_branch_id'];
		}

		return FALSE;
	}
	function setDefaultBranch($id) {
		$id = (int)trim($id);

		$blf = TTnew( 'BranchListFactory' );
		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'default_branch',
														$blf->getByID($id),
														TTi18n::gettext('Invalid Default Branch')
													) ) {

			$this->data['default_branch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultDepartment() {
		if ( isset($this->data['default_department_id']) ) {
			return (int)$this->data['default_department_id'];
		}

		return FALSE;
	}
	function setDefaultDepartment($id) {
		$id = (int)trim($id);

		$dlf = TTnew( 'DepartmentListFactory' );
		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'default_department',
														$dlf->getByID($id),
														TTi18n::gettext('Invalid Default Department')
													) ) {

			$this->data['default_department_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getFullName($reverse = FALSE, $include_middle = TRUE ) {
		return Misc::getFullName($this->getFirstName(), $this->getMiddleInitial(), $this->getLastName(), $reverse, $include_middle);
	}

	function getFirstName() {
		if ( isset($this->data['first_name']) ) {
			return $this->data['first_name'];
		}

		return FALSE;
	}
	function setFirstName($first_name) {
		$first_name = ucwords( trim($first_name) );

		if	(	$this->Validator->isRegEx(		'first_name',
												$first_name,
												TTi18n::gettext('First name contains invalid characters'),
												$this->name_validator_regex)
				AND
					$this->Validator->isLength(		'first_name',
													$first_name,
													TTi18n::gettext('First name is too short or too long'),
													2,
													50) ) {

			$this->data['first_name'] = $first_name;
			$this->setFirstNameMetaphone( $first_name );

			return TRUE;
		}

		return FALSE;
	}
	function getFirstNameMetaphone() {
		if ( isset($this->data['first_name_metaphone']) ) {
			return $this->data['first_name_metaphone'];
		}

		return FALSE;
	}
	function setFirstNameMetaphone($first_name) {
		$first_name = metaphone( trim($first_name) );

		if	(	$first_name != '' ) {

			$this->data['first_name_metaphone'] = $first_name;

			return TRUE;
		}

		return FALSE;
	}


	function getMiddleInitial() {
		if ( $this->getMiddleName() != '' ) {
			$middle_name = $this->getMiddleName();
			return $middle_name[0];
		}

		return FALSE;
	}
	function getMiddleName() {
		if ( isset($this->data['middle_name']) ) {
			return $this->data['middle_name'];
		}

		return FALSE;
	}
	function setMiddleName($middle_name) {
		$middle_name = ucwords( trim($middle_name) );

		if	(
				$middle_name == ''
				OR
				(
				$this->Validator->isRegEx(		'middle_name',
												$middle_name,
												TTi18n::gettext('Middle name contains invalid characters'),
												$this->name_validator_regex)
				AND
					$this->Validator->isLength(		'middle_name',
													$middle_name,
													TTi18n::gettext('Middle name is too short or too long'),
													1,
													50)
				)
			) {

			$this->data['middle_name'] = $middle_name;

			return TRUE;
		}


		return FALSE;
	}

	function getLastName() {
		if ( isset($this->data['last_name']) ) {
			return $this->data['last_name'];
		}

		return FALSE;
	}
	function setLastName($last_name) {
		$last_name = ucwords( trim($last_name) );

		if	(	$this->Validator->isRegEx(		'last_name',
												$last_name,
												TTi18n::gettext('Last name contains invalid characters'),
												$this->name_validator_regex)
				AND
					$this->Validator->isLength(		'last_name',
													$last_name,
													TTi18n::gettext('Last name is too short or too long'),
													2,
													50) ) {

			$this->data['last_name'] = $last_name;
			$this->setLastNameMetaphone( $last_name );

			return TRUE;
		}

		return FALSE;
	}
	function getLastNameMetaphone() {
		if ( isset($this->data['last_name_metaphone']) ) {
			return $this->data['last_name_metaphone'];
		}

		return FALSE;
	}
	function setLastNameMetaphone($last_name) {
		$last_name = metaphone( trim($last_name) );

		if	( $last_name != '' ) {

			$this->data['last_name_metaphone'] = $last_name;
			
			return TRUE;
		}

		return FALSE;
	}

	function getSecondLastName() {
		if ( isset($this->data['second_last_name']) ) {
			return $this->data['second_last_name'];
		}

		return FALSE;
	}

	function setSecondLastName($second_last_name) {
		$last_name = trim($second_last_name);

		if	(
				$second_last_name == ''
				OR
				(
					$this->Validator->isRegEx(		'second_last_name',
													$second_last_name,
													TTi18n::gettext('Second last name contains invalid characters'),
													$this->name_validator_regex)
					AND
						$this->Validator->isLength(		'second_last_name',
														$second_last_name,
														TTi18n::gettext('Second last name is too short or too long'),
														2,
														50)
				)
			) {

			$this->data['second_last_name'] = $second_last_name;

			return TRUE;
		}

		return FALSE;
	}

	function getSex() {
		if ( isset($this->data['sex_id']) ) {
			return (int)$this->data['sex_id'];
		}

		return FALSE;
	}
	function setSex($sex) {
		$sex = trim($sex);

		if ( $this->Validator->inArrayKey(	'sex',
											$sex,
											TTi18n::gettext('Invalid gender'),
											$this->getOptions('sex') ) ) {

			$this->data['sex_id'] = $sex;

			return TRUE;
		}

		return FALSE;
	}

	function getAddress1() {
		if ( isset($this->data['address1']) ) {
			return $this->data['address1'];
		}

		return FALSE;
	}
	function setAddress1($address1) {
		$address1 = trim($address1);

		if	(
				$address1 == ''
				OR
				(
				$this->Validator->isRegEx(		'address1',
												$address1,
												TTi18n::gettext('Address1 contains invalid characters'),
												$this->address_validator_regex)
				AND
					$this->Validator->isLength(		'address1',
													$address1,
													TTi18n::gettext('Address1 is too short or too long'),
													2,
													250)
				)
				) {

			$this->data['address1'] = $address1;

			return TRUE;
		}

		return FALSE;
	}

	function getAddress2() {
		if ( isset($this->data['address2']) ) {
			return $this->data['address2'];
		}

		return FALSE;
	}
	function setAddress2($address2) {
		$address2 = trim($address2);

		if	(	$address2 == ''
				OR
				(
					$this->Validator->isRegEx(		'address2',
													$address2,
													TTi18n::gettext('Address2 contains invalid characters'),
													$this->address_validator_regex)
				AND
					$this->Validator->isLength(		'address2',
													$address2,
													TTi18n::gettext('Address2 is too short or too long'),
													2,
													250) ) ) {

			$this->data['address2'] = $address2;

			return TRUE;
		}

		return FALSE;

	}

	function getCity() {
		if ( isset($this->data['city']) ) {
			return $this->data['city'];
		}

		return FALSE;
	}
	function setCity($city) {
		$city = trim($city);

		if	(
				$city == ''
				OR
				(
				$this->Validator->isRegEx(		'city',
												$city,
												TTi18n::gettext('City contains invalid characters'),
												$this->city_validator_regex)
				AND
					$this->Validator->isLength(		'city',
													$city,
													TTi18n::gettext('City name is too short or too long'),
													2,
													250)
				)
				) {

			$this->data['city'] = $city;

			return TRUE;
		}

		return FALSE;
	}

	function getCountry() {
		if ( isset($this->data['country']) ) {
			return $this->data['country'];
		}

		return FALSE;
	}
	function setCountry($country) {
		$country = trim($country);

		$cf = TTnew( 'CompanyFactory' );

		if ( $this->Validator->inArrayKey(		'country',
												$country,
												TTi18n::gettext('Invalid Country'),
												$cf->getOptions('country') ) ) {

			$this->data['country'] = $country;

			return TRUE;
		}

		return FALSE;
	}

	function getProvince() {
		if ( isset($this->data['province']) ) {
			return $this->data['province'];
		}

		return FALSE;
	}
	function setProvince($province) {
		$province = trim($province);

		//Debug::Text('Country: '. $this->getCountry() .' Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);

		$cf = TTnew( 'CompanyFactory' );

		$options_arr = $cf->getOptions('province');
		if ( isset($options_arr[$this->getCountry()]) ) {
			$options = $options_arr[$this->getCountry()];
		} else {
			$options = array();
		}

		//If country isn't set yet, accept the value and re-validate on save.
		if ( $this->getCountry() == FALSE
				OR
				$this->Validator->inArrayKey(	'province',
												$province,
												TTi18n::gettext('Invalid Province/State'),
												$options ) ) {

			$this->data['province'] = $province;

			return TRUE;
		}

		return FALSE;
	}

	function getPostalCode() {
		if ( isset($this->data['postal_code']) ) {
			return $this->data['postal_code'];
		}

		return FALSE;
	}
	function setPostalCode($postal_code) {
		$postal_code = strtoupper( $this->Validator->stripSpaces($postal_code) );

		if	(
				$postal_code == ''
				OR
				(
				$this->Validator->isPostalCode(		'postal_code',
													$postal_code,
													TTi18n::gettext('Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State'),
													$this->getCountry(), $this->getProvince() )
				AND
					$this->Validator->isLength(		'postal_code',
													$postal_code,
													TTi18n::gettext('Postal/ZIP Code is too short or too long'),
													1,
													10)
				)
				) {

			$this->data['postal_code'] = $postal_code;

			return TRUE;
		}

		return FALSE;
	}

	function getLongitude() {
		if ( isset($this->data['longitude']) ) {
			return (float)$this->data['longitude'];
		}

		return FALSE;
	}
	function setLongitude($value) {
		$value = trim((float)$value);

		if (	$value == 0
				OR
				$this->Validator->isFloat(	'longitude',
											$value,
											TTi18n::gettext('Longitude is invalid')
											) ) {
			$this->data['longitude'] = number_format( $value, 10 ); //Always use 10 decimal places, this also prevents audit logging 0 vs 0.000000000

			return TRUE;
		}

		return FALSE;
	}

	function getLatitude() {
		if ( isset($this->data['latitude']) ) {
			return (float)$this->data['latitude'];
		}

		return FALSE;
	}
	function setLatitude($value) {
		$value = trim((float)$value);

		if (	$value == 0
				OR
				$this->Validator->isFloat(	'latitude',
											$value,
											TTi18n::gettext('Latitude is invalid')
											) ) {
			$this->data['latitude'] = number_format( $value, 10 ); //Always use 10 decimal places, this also prevents audit logging 0 vs 0.000000000

			return TRUE;
		}

		return FALSE;
	}

	function getWorkPhone() {
		if ( isset($this->data['work_phone']) ) {
			return $this->data['work_phone'];
		}

		return FALSE;
	}
	function setWorkPhone($work_phone) {
		$work_phone = trim($work_phone);

		if	(
				$work_phone == ''
				OR
				$this->Validator->isPhoneNumber(		'work_phone',
														$work_phone,
														TTi18n::gettext('Work phone number is invalid')) ) {

			$this->data['work_phone'] = $work_phone;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkPhoneExt() {
		if ( isset($this->data['work_phone_ext']) ) {
			return $this->data['work_phone_ext'];
		}

		return FALSE;
	}
	function setWorkPhoneExt($work_phone_ext) {
		$work_phone_ext = $this->Validator->stripNonNumeric( trim($work_phone_ext) );

		if (	$work_phone_ext == ''
				OR $this->Validator->isLength(		'work_phone_ext',
													$work_phone_ext,
													TTi18n::gettext('Work phone number extension is too short or too long'),
													2,
													10) ) {

			$this->data['work_phone_ext'] = $work_phone_ext;

			return TRUE;
		}

		return FALSE;

	}

	function getHomePhone() {
		if ( isset($this->data['home_phone']) ) {
			return $this->data['home_phone'];
		}

		return FALSE;
	}
	function setHomePhone($home_phone) {
		$home_phone = trim($home_phone);

		if	(	$home_phone == ''
				OR
				$this->Validator->isPhoneNumber(		'home_phone',
														$home_phone,
														TTi18n::gettext('Home phone number is invalid')) ) {

			$this->data['home_phone'] = $home_phone;

			return TRUE;
		}

		return FALSE;
	}

	function getMobilePhone() {
		if ( isset($this->data['mobile_phone']) ) {
			return $this->data['mobile_phone'];
		}

		return FALSE;
	}
	function setMobilePhone($mobile_phone) {
		$mobile_phone = trim($mobile_phone);

		if	(	$mobile_phone == ''
					OR $this->Validator->isPhoneNumber(	'mobile_phone',
															$mobile_phone,
															TTi18n::gettext('Mobile phone number is invalid')) ) {

			$this->data['mobile_phone'] = $mobile_phone;

			return TRUE;
		}

		return FALSE;
	}

	function getFaxPhone() {
		if ( isset($this->data['fax_phone']) ) {
			return $this->data['fax_phone'];
		}

		return FALSE;
	}
	function setFaxPhone($fax_phone) {
		$fax_phone = trim($fax_phone);

		if	(	$fax_phone == ''
					OR $this->Validator->isPhoneNumber(	'fax_phone',
															$fax_phone,
															TTi18n::gettext('Fax phone number is invalid')) ) {

			$this->data['fax_phone'] = $fax_phone;

			return TRUE;
		}

		return FALSE;
	}

	function getHomeEmail() {
		if ( isset($this->data['home_email']) ) {
			return $this->data['home_email'];
		}

		return FALSE;
	}
	function setHomeEmail($home_email) {
		$home_email = trim($home_email);

		$modify_email = FALSE;
		if ( $this->getCurrentUserPermissionLevel() >= $this->getPermissionLevel() ) {
			$modify_email = TRUE;
		} elseif ( $this->getHomeEmail() == $home_email ) { //No modification made.
			$modify_email = TRUE;
		}

		$error_threshold = 7; //No DNS checks.
		if ( PRODUCTION === TRUE AND DEPLOYMENT_ON_DEMAND === TRUE AND DEMO_MODE === FALSE ) {
			$error_threshold = 0; //DNS checks on email address.
		}
		if	(	( $home_email == ''
					OR $this->Validator->isEmailAdvanced(	'home_email',
													$home_email,
													TTi18n::gettext('Home Email address is invalid'),
													$error_threshold )
				)
				AND
				$this->Validator->isTrue(		'home_email',
												$modify_email,
												TTi18n::gettext('Insufficient access to modify home email for this employee')
												)
				) {

			$this->data['home_email'] = $home_email;
			$this->setEnableClearPasswordResetData( TRUE ); //Clear any outstanding password reset key to prevent unexpected changes later on.

			return TRUE;
		}

		return FALSE;
	}

	function getHomeEmailIsValid() {
		return $this->fromBool( $this->data['home_email_is_valid'] );
	}
	function setHomeEmailIsValid($bool) {
		$this->data['home_email_is_valid'] = $this->toBool($bool);

		return TRUE;
	}

	function getHomeEmailIsValidKey() {
		if ( isset($this->data['home_email_is_valid_key']) ) {
			return $this->data['home_email_is_valid_key'];
		}

		return FALSE;
	}
	function setHomeEmailIsValidKey($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'home_email_is_valid_key',
											$value,
											TTi18n::gettext('Email validation key is invalid'),
											1, 255) ) {

			$this->data['home_email_is_valid_key'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getHomeEmailIsValidDate() {
		if ( isset($this->data['home_email_is_valid_date']) ) {
			return $this->data['home_email_is_valid_date'];
		}
	}
	function setHomeEmailIsValidDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if	(	$epoch == ''
				OR
				$this->Validator->isDate(		'home_email_is_valid_date',
												$epoch,
												TTi18n::gettext('Email validation date is invalid')) ) {

			$this->data['home_email_is_valid_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkEmail() {
		if ( isset($this->data['work_email']) ) {
			return $this->data['work_email'];
		}

		return FALSE;
	}
	function setWorkEmail($work_email) {
		$work_email = trim($work_email);

		$modify_email = FALSE;
		if ( $this->getCurrentUserPermissionLevel() >= $this->getPermissionLevel() ) {
			$modify_email = TRUE;
		} elseif ( $this->getWorkEmail() == $work_email ) { //No modification made.
			$modify_email = TRUE;
		}

		$error_threshold = 7; //No DNS checks.
		if ( PRODUCTION === TRUE AND DEPLOYMENT_ON_DEMAND === TRUE AND DEMO_MODE === FALSE ) {
			$error_threshold = 0; //DNS checks on email address.
		}
		if	(	( $work_email == ''
					OR	$this->Validator->isEmailAdvanced(	'work_email',
													$work_email,
													TTi18n::gettext('Work Email address is invalid'),
													$error_threshold)
				)
				AND
				$this->Validator->isTrue(		'work_email',
												$modify_email,
												TTi18n::gettext('Insufficient access to modify work email for this employee')
												)
					) {

			$this->data['work_email'] = $work_email;
			$this->setEnableClearPasswordResetData( TRUE ); //Clear any outstanding password reset key to prevent unexpected changes later on.

			return TRUE;
		}

		return FALSE;
	}

	function getWorkEmailIsValid() {
		return $this->fromBool( $this->data['work_email_is_valid'] );
	}
	function setWorkEmailIsValid($bool) {
		$this->data['work_email_is_valid'] = $this->toBool($bool);

		return TRUE;
	}

	function getWorkEmailIsValidKey() {
		if ( isset($this->data['work_email_is_valid_key']) ) {
			return $this->data['work_email_is_valid_key'];
		}

		return FALSE;
	}
	function setWorkEmailIsValidKey($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'work_email_is_valid_key',
											$value,
											TTi18n::gettext('Email validation key is invalid'),
											1, 255) ) {

			$this->data['work_email_is_valid_key'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkEmailIsValidDate() {
		if ( isset($this->data['work_email_is_valid_date']) ) {
			return $this->data['work_email_is_valid_date'];
		}
	}
	function setWorkEmailIsValidDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if	(	$epoch == ''
				OR
				$this->Validator->isDate(		'work_email_is_valid_date',
												$epoch,
												TTi18n::gettext('Email validation date is invalid')) ) {

			$this->data['work_email_is_valid_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getAge() {
		return round( TTDate::getYearDifference( $this->getBirthDate(), TTDate::getTime() ), 1 );
	}

	function getBirthDate() {
		if ( isset($this->data['birth_date']) ) {
			return $this->data['birth_date'];
		}

		return FALSE;
	}
	function setBirthDate($epoch) {
		if	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'birth_date',
												$epoch,
												TTi18n::gettext('Birth date is invalid, try specifying the year with four digits.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['birth_date'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}

	function isValidWageForHireDate( $epoch ) {
		if ( $this->getID() > 0 AND $epoch != '' ) {
			$uwlf = TTnew( 'UserWageListFactory' );

			//Check to see if any wage entries exist for this employee
			$uwlf->getLastWageByUserId( $this->getID() );
			if ( $uwlf->getRecordCount() >= 1 ) {
				Debug::Text('No wage entries exist...', __FILE__, __LINE__, __METHOD__, 10);

				$uwlf->getByUserIdAndGroupIDAndBeforeDate( $this->getID(), 0, $epoch );
				if ( $uwlf->getRecordCount() == 0 ) {
					Debug::Text('No wage entry on or before : '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	function getHireDate() {
		if ( isset($this->data['hire_date']) ) {
			return $this->data['hire_date'];
		}

		return FALSE;
	}
	function setHireDate($epoch) {
		//( $epoch !== FALSE AND $epoch == '' ) //Check for strict FALSE causes data from UserDefault to fail if its not set.
		if	(	( $epoch == '' )
				OR
					(
						$this->Validator->isDate(		'hire_date',
														$epoch,
														TTi18n::gettext('Hire date is invalid'))
						AND
						$this->Validator->isTrue(		'hire_date',
														$this->isValidWageForHireDate( $epoch ),
														TTi18n::gettext('Hire date must be on or after the employees first wage entry, you may need to change their wage effective date first'))
					)
				) {

			//Use the beginning of the day epoch, so accrual policies that apply on the hired date still work.
			$this->data['hire_date'] = TTDate::getBeginDayEpoch( $epoch );

			return TRUE;
		}

		return FALSE;
	}

	function getTerminationDate() {
		if ( isset($this->data['termination_date']) ) {
			return $this->data['termination_date'];
		}

		return FALSE;
	}
	function setTerminationDate($epoch) {
		if	(	( $epoch == '' )
				OR
				$this->Validator->isDate(		'termination_date',
												$epoch,
												TTi18n::gettext('Termination date is invalid')) ) {

			if ( $epoch == '' ) {
				$epoch = NULL; //Force to NULL if no termination date is set, this prevents "0" from being entered and causing problems with "is NULL" SQL queries.
			}
			$this->data['termination_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getLastLoginDate() {
		if ( isset($this->data['last_login_date']) ) {
			return $this->data['last_login_date'];
		}

		return FALSE;
	}
	function setLastLoginDate($epoch) {
		if	(	( $epoch == '' )
				OR
				$this->Validator->isDate(		'last_login_date',
												$epoch,
												TTi18n::gettext('Last Login date is invalid')) ) {

			if ( $epoch == '' ) {
				$epoch = NULL; //Force to NULL if no termination date is set, this prevents "0" from being entered and causing problems with "is NULL" SQL queries.
			}
			$this->data['last_login_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getCurrency() {
		if ( isset($this->data['currency_id']) ) {
			return (int)$this->data['currency_id'];
		}

		return FALSE;
	}
	function setCurrency($id) {
		$id = trim($id);

		Debug::Text('Currency ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$culf = TTnew( 'CurrencyListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'currency_id',
														$culf->getByID($id),
														TTi18n::gettext('Invalid currency')
													) ) {

			$this->data['currency_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getSecureSIN( $sin = NULL ) {
		if ( $sin == '' ) {
			$sin = $this->getSIN();
		}
		if ( $sin != '' ) {
			//Grab the first 1, and last 4 digits.
			$first_four = substr( $sin, 0, 1 );
			$last_four = substr( $sin, -4 );

			$total = (strlen($sin) - 5);

			$retval = $first_four.str_repeat('X', $total).$last_four;

			return $retval;
		}

		return FALSE;
	}
	function getSIN() {
		if ( isset($this->data['sin']) ) {
			return $this->data['sin'];
		}

		return FALSE;
	}
	function setSIN($sin) {
		//If *'s are in the SIN number, skip setting it
		//This allows them to change other data without seeing the SIN number.
		if ( stripos( $sin, 'X') !== FALSE	) {
			return FALSE;
		}

		$sin = $this->Validator->stripNonNumeric( trim($sin) );

		if	(
				$sin == ''
				OR
				DEMO_MODE === TRUE
				OR
				$this->Validator->isSIN(		'sin',
												$sin,
												TTi18n::gettext('SIN/SSN is invalid'),
												$this->getCountry() )
				) {

			$this->data['sin'] = $sin;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID1() {
		if ( isset($this->data['other_id1']) ) {
			return $this->data['other_id1'];
		}

		return FALSE;
	}
	function setOtherID1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id1',
											$value,
											TTi18n::gettext('Other ID 1 is invalid'),
											1, 255) ) {

			$this->data['other_id1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID2() {
		if ( isset($this->data['other_id2']) ) {
			return $this->data['other_id2'];
		}

		return FALSE;
	}
	function setOtherID2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id2',
											$value,
											TTi18n::gettext('Other ID 2 is invalid'),
											1, 255) ) {

			$this->data['other_id2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID3() {
		if ( isset($this->data['other_id3']) ) {
			return $this->data['other_id3'];
		}

		return FALSE;
	}
	function setOtherID3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id3',
											$value,
											TTi18n::gettext('Other ID 3 is invalid'),
											1, 255) ) {

			$this->data['other_id3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID4() {
		if ( isset($this->data['other_id4']) ) {
			return $this->data['other_id4'];
		}

		return FALSE;
	}
	function setOtherID4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id4',
											$value,
											TTi18n::gettext('Other ID 4 is invalid'),
											1, 255) ) {

			$this->data['other_id4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID5() {
		if ( isset($this->data['other_id5']) ) {
			return $this->data['other_id5'];
		}

		return FALSE;
	}
	function setOtherID5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id5',
											$value,
											TTi18n::gettext('Other ID 5 is invalid'),
											1, 255) ) {

			$this->data['other_id5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getNote() {
		if ( isset($this->data['note']) ) {
			return $this->data['note'];
		}

		return FALSE;
	}
	function setNote($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'note',
														$value,
														TTi18n::gettext('Note is too long'),
														1,
														2048)
			) {

			$this->data['note'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function checkPasswordResetKey($key) {
		if ( $this->getPasswordResetDate() != ''
				AND $this->getPasswordResetDate() > (time() - 86400)
				AND $this->getPasswordResetKey() == $key ) {

			return TRUE;
		}

		return FALSE;
	}

	function sendValidateEmail( $type = 'work' ) {
		if ( $this->getHomeEmail() != FALSE
				OR $this->getWorkEmail() != FALSE ) {

			if ( $this->getWorkEmail() != FALSE AND $type == 'work' ) {
				$primary_email = $this->getWorkEmail();
			} elseif( $this->getHomeEmail() != FALSE AND $type == 'home' ) {
				$primary_email = $this->getHomeEmail();
			} else {
				Debug::text('ERROR: Home/Work email not defined or matching type, unable to send validation email...', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}

			if ( $type == 'work' ) {
				$this->setWorkEmailIsValidKey( md5( uniqid() ) );
				$this->setWorkEmailIsValidDate( time() );
				$email_is_valid_key = $this->getWorkEmailIsValidKey();
			} else {
				$this->setHomeEmailIsValidKey( md5( uniqid() ) );
				$this->setHomeEmailIsValidDate( time() );
				$email_is_valid_key = $this->getHomeEmailIsValidKey();
			}

			$this->Save(FALSE);

			$subject = APPLICATION_NAME .' - '. TTi18n::gettext('Confirm email address');

			$body = '<html><body>';
			$body .= TTi18n::gettext('The email address %1 has been added to your %2 account', array($primary_email, APPLICATION_NAME) ).', ';
			$body .= ' <a href="'. Misc::getURLProtocol() .'://'.Misc::getHostName().Environment::getBaseURL() .'ConfirmEmail.php?action:confirm_email=1&email='. $primary_email .'&key='. $email_is_valid_key .'">'. TTi18n::gettext('please click here to confirm and activate this email address') .'</a>.';
			$body .= '<br><br>';
			$body .= '--<br>';
			$body .= APPLICATION_NAME;
			$body .= '</body></html>';

			TTLog::addEntry( $this->getId(), 500, TTi18n::getText('Employee email confirmation sent for').': '. $primary_email, NULL, $this->getTable() );

			$headers = array(
								'From'	  => '"'. APPLICATION_NAME .' - '. TTi18n::gettext('Email Confirmation') .'"<DoNotReply@'. Misc::getEmailDomain() .'>',
								'Subject' => $subject,
								'X-TimeTrex-Email-Validate' => 'YES', //Help filter validation emails.
							);

			$mail = new TTMail();
			$mail->setTo( $primary_email );
			$mail->setHeaders( $headers );

			@$mail->getMIMEObject()->setHTMLBody($body);

			$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
			$retval = $mail->Send();

			return $retval;
		}

		return FALSE;
	}

	function sendPasswordResetEmail() {
		if ( $this->getHomeEmail() != FALSE
				OR $this->getWorkEmail() != FALSE ) {

			if ( $this->getWorkEmail() != FALSE ) {
				$primary_email = $this->getWorkEmail();
				if ( $this->getHomeEmail() != FALSE ) {
					$secondary_email = $this->getHomeEmail();
				} else {
					$secondary_email = NULL;
				}
			} else {
				$primary_email = $this->getHomeEmail();
				$secondary_email = NULL;
			}

			$this->setPasswordResetKey( md5( uniqid() ) );
			$this->setPasswordResetDate( time() );
			$this->Save(FALSE);

			$subject = APPLICATION_NAME .' '. TTi18n::gettext('password reset requested at '). TTDate::getDate('DATE+TIME', time() ) .' '. TTi18n::gettext('from') .' '. $_SERVER['REMOTE_ADDR'];

			$body = '<html><body>';
			$body .= TTi18n::gettext('A password reset has been requested for') .' "'. $this->getUserName() .'", ';
			$body .= ' <a href="'. Misc::getURLProtocol() .'://'.Misc::getHostName().Environment::getBaseURL() .'ForgotPassword.php?action:password_reset=1&key='. $this->getPasswordResetKey().'">'. TTi18n::gettext('please click here to reset your password now') .'</a>.';
			$body .= '<br><br>';
			$body .= TTi18n::gettext('If you did not request your password to be reset, you may ignore this email.');
			$body .= '<br><br>';
			$body .= '--<br>';
			$body .= APPLICATION_NAME;
			$body .= '</body></html>';

			//Don't record the reset key in the audit log for security reasons.
			TTLog::addEntry( $this->getId(), 500, TTi18n::getText('Employee Password Reset By').': '. $_SERVER['REMOTE_ADDR'], NULL, $this->getTable() );

			$headers = array(
								'From'	  => '"'. APPLICATION_NAME .' - '. TTi18n::gettext('Password Reset') .'"<DoNotReply@'. Misc::getEmailDomain() .'>',
								'Subject' => $subject,
								'Cc'	  => $secondary_email,
							);

			$mail = new TTMail();
			$mail->setTo( $primary_email );
			$mail->setHeaders( $headers );

			@$mail->getMIMEObject()->setHTMLBody($body);

			$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
			$retval = $mail->Send();

			return $retval;
		}

		return FALSE;
	}

	function getPasswordResetKey() {
		if ( isset($this->data['password_reset_key']) ) {
			return $this->data['password_reset_key'];
		}

		return FALSE;
	}
	function setPasswordResetKey($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'password_reset_key',
											$value,
											TTi18n::gettext('Password reset key is invalid'),
											1, 255) ) {

			$this->data['password_reset_key'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPasswordResetDate() {
		if ( isset($this->data['password_reset_date']) ) {
			return $this->data['password_reset_date'];
		}
	}
	function setPasswordResetDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if	(	$epoch == ''
				OR
				$this->Validator->isDate(		'password_reset_date',
												$epoch,
												TTi18n::gettext('Password reset date is invalid')) ) {

			$this->data['password_reset_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function setEnableClearPasswordResetData( $value = TRUE ) {
		$this->tmp_data['enable_clear_password_reset_data'] = $value;
		return TRUE;
	}
	function getEnableClearPasswordResetData() {
		if ( isset($this->tmp_data['enable_clear_password_reset_data']) ) {
			return $this->tmp_data['enable_clear_password_reset_data'];
		}
		return FALSE;
	}
	
	function isPhotoExists() {
		return file_exists( $this->getPhotoFileName() );
	}

	function getPhotoFileName( $company_id = NULL, $user_id = NULL, $include_default_photo = TRUE ) {
		//Test for both jpg and png
		$base_name = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR . $user_id;
		if ( file_exists( $base_name.'.jpg') ) {
			$photo_file_name = $base_name.'.jpg';
		} elseif ( file_exists( $base_name.'.png') ) {
			$photo_file_name = $base_name.'.png';
		} elseif ( file_exists( $base_name.'.img') ) {
			$photo_file_name = $base_name.'.img';
		} else {
			if ( $include_default_photo == TRUE ) {
				//$photo_file_name = Environment::getImagesPath().'unknown_photo.png';
				$photo_file_name = Environment::getImagesPath().'s.gif';
			} else {
				return FALSE;
			}
		}

		//Debug::Text('Logo File Name: '. $photo_file_name .' Base Name: '. $base_name .' User ID: '. $user_id .' Include Default: '. (int)$include_default_photo, __FILE__, __LINE__, __METHOD__, 10);
		return $photo_file_name;
	}

	function cleanStoragePath( $company_id = NULL, $user_id = NULL ) {
		if ( $company_id == '' ) {
			$company_id = $this->getCompany();
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$dir = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR;
		if ( $dir != '' ) {
			if ( $user_id != '' ) {
				@unlink( $this->getPhotoFileName( $company_id, $user_id, FALSE ) ); //Delete just users photo.
			} else {
				//Delete tmp files.
				foreach(glob($dir.'*') as $filename) {
					unlink($filename);
				}
			}
		}

		return TRUE;
	}

	function getStoragePath( $company_id = NULL, $user_id = NULL ) {
		if ( $company_id == '' ) {
			$company_id = $this->getID();
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		return Environment::getStorageBasePath() . DIRECTORY_SEPARATOR .'user_photo'. DIRECTORY_SEPARATOR . $company_id;
	}

	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['tags']) ) {
			return $this->tmp_data['tags'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 200, $this->getID() );
		}

		return FALSE;
	}
	function setTag( $tags ) {
		$tags = trim($tags);

		//Save the tags in temporary memory to be committed in postSave()
		$this->tmp_data['tags'] = $tags;

		return TRUE;
	}

	function isInformationComplete() {
		//Make sure the users information is all complete.
		//No longer check for SIN, as employees can't change it anyways.
		//Don't check for postal code, as some countries don't have that.
		if ( $this->getAddress1() == ''
				OR $this->getCity() == ''
				OR $this->getHomePhone() == '' ) {
			Debug::text('User Information is NOT Complete: ', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		Debug::text('User Information is Complete: ', __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
	}

	static function UnsubscribeEmail( $email ) {
		$email = trim(strtolower($email));

		try {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByHomeEmailOrWorkEmail( $email );
			if ( $ulf->getRecordCount() > 0 ) {
				foreach( $ulf as $u_obj ) {
					Debug::Text('Unsubscribing: '. $email .' User ID: '. $u_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					if ( strtolower( $u_obj->getWorkEmail() ) == $email AND $u_obj->getWorkEmailIsValid() == TRUE ) {
						//$u_obj->setWorkEmail( '' );
						$u_obj->setWorkEmailIsValid( FALSE );
						$u_obj->sendValidateEmail( 'work' );
					}

					if ( strtolower( $u_obj->getHomeEmail() ) == $email AND $u_obj->getHomeEmailIsValid() == TRUE ) {
						//$u_obj->setHomeEmail( '' );
						$u_obj->setHomeEmailIsValid( FALSE );
						$u_obj->sendValidateEmail( 'home' );
					}

					TTLog::addEntry( $u_obj->getId(), 500, TTi18n::gettext('Requiring validation for invalid or bouncing email address').': '. $email, $u_obj->getId(), 'users' );
					if ( $u_obj->isValid() ) {
						$u_obj->Save();
					}
				}
				return TRUE;
			}
		} catch( Exception $e ) {
			Debug::text('ERROR: Unable to unsubscribe email: '. $email, __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	function Validate() {
		//When doing a mass edit of employees, user name is never specified, so we need to avoid this validation issue.
		if ( $this->getUserName() == '' ) {
			$this->Validator->isTrue(		'user_name',
											FALSE,
											TTi18n::gettext('User name not specified'));
		}

		//Re-validate the province just in case the country was set AFTER the province.
		$this->setProvince( $this->getProvince() );

		if ( $this->getCompany() == FALSE ) {
			$this->Validator->isTrue(		'company',
											FALSE,
											TTi18n::gettext('Company is invalid'));
		}

		//When mass editing, don't require currency to be set.
		if ( $this->validate_only == FALSE AND $this->getCurrency() == FALSE ) {
			$this->Validator->isTrue(		'currency_id',
											FALSE,
											TTi18n::gettext('Invalid currency'));
		}

		if ( $this->getTerminationDate() != '' AND $this->getHireDate() != '' AND TTDate::getBeginDayEpoch( $this->getTerminationDate() ) < TTDate::getBeginDayEpoch( $this->getHireDate() ) ) {
			$this->Validator->isTrue(		'termination_date',
											FALSE,
											TTi18n::gettext('Termination date is before hire date, consider removing the termination date entirely for re-hires'));
		}

		//Need to require password on new employees as the database column is NOT NULL.
		//However when mass editing, no IDs are set so this always fails during the only validation phase.
		if ( $this->validate_only == FALSE AND $this->isNew( TRUE ) == TRUE AND ( $this->getPassword() == FALSE OR $this->getPassword() == '' ) ) {
			$this->Validator->isTrue(		'password',
											FALSE,
											TTi18n::gettext('Please specify a password'));
		}

		if ( $this->validate_only == FALSE AND $this->getEmployeeNumber() == FALSE AND $this->getStatus() == 10 ) {
			$this->Validator->isTrue(		'employee_number',
											FALSE,
											TTi18n::gettext('Employee number must be specified for ACTIVE employees') );
		}

		global $current_user;
		if ( is_object($current_user) AND $current_user->getID() == $this->getID() ) { //Acting on currently logged in user.
			if ( $this->getDeleted() == TRUE ) {
				$this->Validator->isTrue(		'user_name',
													FALSE,
													TTi18n::gettext('Unable to delete your own record') );
			}

			if ( $this->getStatus() != 10 ) {
				$this->Validator->isTrue(		'status_id',
													FALSE,
													TTi18n::gettext('Unable to change status of your own record') );
			}
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND $this->isNew( TRUE ) == FALSE ) {
			if ( $this->getDefaultJob() > 0 ) {
				$jlf = TTnew( 'JobListFactory' );
				$jlf->getById( $this->getDefaultJob() );
				if ( $jlf->getRecordCount() > 0 ) {
					$j_obj = $jlf->getCurrent();

					if ( $j_obj->isAllowedUser( $this->getID() ) == FALSE ) {
						$this->Validator->isTRUE(	'job',
													FALSE,
													TTi18n::gettext('Employee is not assigned to this job') );
					}

					if ( $j_obj->isAllowedItem( $this->getDefaultJobItem() ) == FALSE ) {
						$this->Validator->isTRUE(	'job_item',
													FALSE,
													TTi18n::gettext('Task is not assigned to this job') );
					}
				}
			}
		}
																																												if ( $this->isNew() == TRUE ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); } }
		return TRUE;
	}

	function preSave() {
		if ( $this->getDefaultBranch() == FALSE ) {
			$this->setDefaultBranch(0);
		}
		if ( $this->getDefaultDepartment() == FALSE ) {
			$this->setDefaultDepartment(0);
		}

		if ( $this->getStatus() == FALSE ) {
			$this->setStatus( 10 ); //Active
		}

		if ( $this->getSex() == FALSE ) {
			$this->setSex( 5 ); //UnSpecified
		}

		if ( $this->getEthnicGroup() == FALSE ) {
			$this->setEthnicGroup( 0 );
		}

		if ( $this->getEnableClearPasswordResetData() == TRUE ) {
			Debug::text('Clearing password reset data...', __FILE__, __LINE__, __METHOD__, 10);
			$this->setPasswordResetKey('');
			$this->setPasswordResetDate('');
		}

		//Remember if this is a new user for postSave()
		if ( $this->isNew( TRUE ) ) {
			$this->is_new = TRUE;
		}

		return TRUE;
	}

	function postSave( $data_diff = NULL ) {
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == FALSE AND $this->getPermissionControl() !== FALSE ) {
			Debug::text('Permission Group is set...', __FILE__, __LINE__, __METHOD__, 10);

			$pclf = TTnew( 'PermissionControlListFactory' );
			$pclf->getByCompanyIdAndUserID( $this->getCompany(), $this->getId() );
			if ( $pclf->getRecordCount() > 0 ) {
				Debug::text('Already assigned to a Permission Group...', __FILE__, __LINE__, __METHOD__, 10);

				$pc_obj = $pclf->getCurrent();

				if ( $pc_obj->getId() == $this->getPermissionControl() ) {
					$add_permission_control = FALSE;
				} else {
					Debug::text('Permission Group has changed...', __FILE__, __LINE__, __METHOD__, 10);

					$pulf = TTnew( 'PermissionUserListFactory' );
					$pulf->getByPermissionControlIdAndUserID( $pc_obj->getId(), $this->getId() );
					Debug::text('Record Count: '. $pulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					if ( $pulf->getRecordCount() > 0 ) {
						foreach( $pulf as $pu_obj ) {
							Debug::text('Deleteing from Permission Group: '. $pu_obj->getPermissionControl(), __FILE__, __LINE__, __METHOD__, 10);
							$pu_obj->Delete();
						}

						$pc_obj->touchUpdatedByAndDate();
					}

					$add_permission_control = TRUE;
				}
			} else {
				Debug::text('NOT Already assigned to a Permission Group...', __FILE__, __LINE__, __METHOD__, 10);
				$add_permission_control = TRUE;
			}

			if ( $this->getPermissionControl() !== FALSE AND $add_permission_control == TRUE ) {
				Debug::text('Adding user to Permission Group...', __FILE__, __LINE__, __METHOD__, 10);

				//Add to new permission group
				$puf = TTnew( 'PermissionUserFactory' );
				$puf->setPermissionControl( $this->getPermissionControl() );
				$puf->setUser( $this->getID() );

				if ( $puf->isValid() ) {
					if ( is_object( $puf->getPermissionControlObject() ) ) {
						$puf->getPermissionControlObject()->touchUpdatedByAndDate();
					}
					$puf->Save();

					//Clear permission class for this employee.
					$pf = TTnew( 'PermissionFactory' );
					$pf->clearCache( $this->getID(), $this->getCompany() );
				}
			}
			unset($add_permission_control);
		}

		if ( $this->getDeleted() == FALSE AND $this->getPayPeriodSchedule() !== FALSE ) {
			Debug::text('Pay Period Schedule is set: '. $this->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10);

			$add_pay_period_schedule = FALSE;

			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
			$ppslf->getByUserId( $this->getId() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$pps_obj = $ppslf->getCurrent();

				if ( $this->getPayPeriodSchedule() == $pps_obj->getId() ) {
					Debug::text('Already assigned to this Pay Period Schedule...', __FILE__, __LINE__, __METHOD__, 10);
					$add_pay_period_schedule = FALSE;
				} else {
					Debug::text('Changing Pay Period Schedule...', __FILE__, __LINE__, __METHOD__, 10);

					//Remove user from current schedule.
					$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
					$ppsulf->getByPayPeriodScheduleIdAndUserID( $pps_obj->getId(), $this->getId() );
					Debug::text('Record Count: '. $ppsulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					if ( $ppsulf->getRecordCount() > 0 ) {
						foreach( $ppsulf as $ppsu_obj ) {
							Debug::text('Deleteing from Pay Period Schedule: '. $ppsu_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10);
							$ppsu_obj->Delete();
						}
					}
					$add_pay_period_schedule = TRUE;
				}
			} elseif ( $this->getPayPeriodSchedule() > 0 ) {
				Debug::text('Not assigned to ANY Pay Period Schedule...', __FILE__, __LINE__, __METHOD__, 10);
				$add_pay_period_schedule = TRUE;
			}

			if ( $this->getPayPeriodSchedule() !== FALSE AND $add_pay_period_schedule == TRUE ) {
				//Add to new pay period schedule
				$ppsuf = TTnew( 'PayPeriodScheduleUserFactory' );
				$ppsuf->setPayPeriodSchedule( $this->getPayPeriodSchedule() );
				$ppsuf->setUser( $this->getID() );

				if ( $ppsuf->isValid() ) {
					$ppsuf->Save();
				}
			}
			unset($add_pay_period_schedule);
		}

		if ( $this->getDeleted() == FALSE AND $this->getPolicyGroup() !== FALSE ) {
			Debug::text('Policy Group is set...', __FILE__, __LINE__, __METHOD__, 10);

			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getByUserIds( $this->getId() );
			if ( $pglf->getRecordCount() > 0 ) {
				$pg_obj = $pglf->getCurrent();

				if ( $this->getPolicyGroup() == $pg_obj->getId() ) {
					Debug::text('Already assigned to this Policy Group...', __FILE__, __LINE__, __METHOD__, 10);
					$add_policy_group = FALSE;
				} else {
					Debug::text('Changing Policy Group...', __FILE__, __LINE__, __METHOD__, 10);

					//Remove user from current schedule.
					$pgulf = TTnew( 'PolicyGroupUserListFactory' );
					$pgulf->getByPolicyGroupIdAndUserId( $pg_obj->getId(), $this->getId() );
					Debug::text('Record Count: '. $pgulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					if ( $pgulf->getRecordCount() > 0 ) {
						foreach( $pgulf as $pgu_obj ) {
							Debug::text('Deleting from Policy Group: '. $pgu_obj->getPolicyGroup(), __FILE__, __LINE__, __METHOD__, 10);
							$pgu_obj->Delete();
						}
					}
					$add_policy_group = TRUE;
				}
			} else {
				Debug::text('Not assigned to ANY Policy Group...', __FILE__, __LINE__, __METHOD__, 10);
				$add_policy_group = TRUE;
			}

			if ( $this->getPolicyGroup() !== FALSE AND $add_policy_group == TRUE ) {
				//Add to new policy group
				$pguf = TTnew( 'PolicyGroupUserFactory' );
				$pguf->setPolicyGroup( $this->getPolicyGroup() );
				$pguf->setUser( $this->getID() );

				if ( $pguf->isValid() ) {
					$pguf->Save();
				}
			}
			unset($add_policy_group);
		}

		if ( $this->getDeleted() == FALSE AND $this->getHierarchyControl() !== FALSE ) {
			Debug::text('Hierarchies are set...', __FILE__, __LINE__, __METHOD__, 10);

			$hierarchy_control_data = array_unique( array_values( (array)$this->getHierarchyControl() ) );
			//Debug::Arr($hierarchy_control_data, 'Setting hierarchy control data...', __FILE__, __LINE__, __METHOD__, 10);

			if ( is_array( $hierarchy_control_data ) ) {
				$hclf = TTnew( 'HierarchyControlListFactory' );
				$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
				$existing_hierarchy_control_data = array_unique( array_values( (array)$hclf->getArrayByListFactory( $hclf, FALSE, TRUE, FALSE ) ) );
				//Debug::Arr($existing_hierarchy_control_data, 'Existing hierarchy control data...', __FILE__, __LINE__, __METHOD__, 10);

				$hierarchy_control_delete_diff = array_diff( $existing_hierarchy_control_data, $hierarchy_control_data );
				//Debug::Arr($hierarchy_control_delete_diff, 'Hierarchy control delete diff: ', __FILE__, __LINE__, __METHOD__, 10);

				//Remove user from existing hierarchy control
				if ( is_array($hierarchy_control_delete_diff) ) {
					foreach( $hierarchy_control_delete_diff as $hierarchy_control_id ) {
						if ( $hierarchy_control_id != 0 ) {
							$hulf = TTnew( 'HierarchyUserListFactory' );
							$hulf->getByHierarchyControlAndUserID( $hierarchy_control_id, $this->getID() );
							if ( $hulf->getRecordCount() > 0 ) {
								Debug::text('Deleting user from hierarchy control ID: '. $hierarchy_control_id, __FILE__, __LINE__, __METHOD__, 10);
								$hulf->getCurrent()->Delete();
							}
						}
					}
				}
				unset($hierarchy_control_delete_diff, $hulf, $hclf, $hierarchy_control_id);

				$hierarchy_control_add_diff = array_diff( $hierarchy_control_data, $existing_hierarchy_control_data	 );
				//Debug::Arr($hierarchy_control_add_diff, 'Hierarchy control add diff: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( is_array($hierarchy_control_add_diff) ) {
					foreach( $hierarchy_control_add_diff as $hierarchy_control_id ) {
						Debug::text('Hierarchy data changed...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $hierarchy_control_id != 0 ) {
							$huf = TTnew( 'HierarchyUserFactory' );
							$huf->setHierarchyControl( $hierarchy_control_id );
							$huf->setUser( $this->getId() );
							if ( $huf->isValid() ) {
								Debug::text('Adding user to hierarchy control ID: '. $hierarchy_control_id, __FILE__, __LINE__, __METHOD__, 10);
								$huf->Save();
							}
						}
					}
				}
				unset($hierarchy_control_add, $huf, $hierarchy_control_id);
			}
		}

		if ( DEMO_MODE != TRUE AND $this->getDeleted() == FALSE AND $this->getPasswordUpdatedDate() >= (time() - 10) ) { //If the password was updated in the last 10 seconds.
			Debug::text('Password changed, saving it for historical purposes... Password: '. $this->getPassword(), __FILE__, __LINE__, __METHOD__, 10);

			$uif = TTnew( 'UserIdentificationFactory' );
			$uif->setUser( $this->getID() );
			$uif->setType( 5 ); //Password History
			$uif->setNumber( 0 );
			$uif->setValue( $this->getPassword() );
			if ( $uif->isValid() ) {
				$uif->Save();
			}
			unset($uif);
		}

		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 200, $this->getID(), $this->getTag() );

			if ( is_array($data_diff) AND ( isset($data_diff['address1']) OR isset($data_diff['address2']) OR isset($data_diff['city']) OR isset($data_diff['province']) OR isset($data_diff['country']) OR isset($data_diff['postal_code']) ) ) {
				//Run a separate custom query to clear the geocordinates. Do we really want to do this for so many objects though...
				Debug::text('Address has changed, clear geocordinates!', __FILE__, __LINE__, __METHOD__, 10);
				$query = 'UPDATE '. $this->getTable() .' SET longitude = NULL, latitude = NULL where id = ?';
				$this->db->Execute( $query, array( 'id' => $this->getID() ) );
			}
		}

		if ( isset($this->is_new) AND $this->is_new == TRUE ) {
			$udlf = TTnew( 'UserDefaultListFactory' );
			$udlf->getByCompanyId( $this->getCompany() );
			if ( $udlf->getRecordCount() > 0 ) {
				Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__, 10);
				$udf_obj = $udlf->getCurrent();

				Debug::text('Inserting Default Deductions...', __FILE__, __LINE__, __METHOD__, 10);

				$company_deduction_ids = $udf_obj->getCompanyDeduction();
				if ( is_array($company_deduction_ids) AND count($company_deduction_ids) > 0 ) {
					foreach( $company_deduction_ids as $company_deduction_id ) {
						$udf = TTnew( 'UserDeductionFactory' );
						$udf->setUser( $this->getId() );
						$udf->setCompanyDeduction( $company_deduction_id );
						if ( $udf->isValid() ) {
							$udf->Save();
						}
					}
				}
				unset($company_deduction_ids, $company_deduction_id, $udf);

				Debug::text('Inserting Default Prefs (a)...', __FILE__, __LINE__, __METHOD__, 10);
				$upf = TTnew( 'UserPreferenceFactory' );
				$upf->setUser( $this->getId() );
				$upf->setLanguage( $udf_obj->getLanguage() );
				$upf->setDateFormat( $udf_obj->getDateFormat() );
				$upf->setTimeFormat( $udf_obj->getTimeFormat() );
				$upf->setTimeUnitFormat( $udf_obj->getTimeUnitFormat() );

				$upf->setTimeZone( $upf->getLocationTimeZone( $this->getCountry(), $this->getProvince(), $this->getWorkPhone(), $this->getHomePhone(), $udf_obj->getTimeZone() ) );
				Debug::text('Time Zone: '. $upf->getTimeZone(), __FILE__, __LINE__, __METHOD__, 9);

				$upf->setItemsPerPage( $udf_obj->getItemsPerPage() );
				$upf->setStartWeekDay( $udf_obj->getStartWeekDay() );
				$upf->setEnableEmailNotificationException( $udf_obj->getEnableEmailNotificationException() );
				$upf->setEnableEmailNotificationMessage( $udf_obj->getEnableEmailNotificationMessage() );
				$upf->setEnableEmailNotificationPayStub( $udf_obj->getEnableEmailNotificationPayStub() );
				$upf->setEnableEmailNotificationHome( $udf_obj->getEnableEmailNotificationHome() );

				if ( $upf->isValid() ) {
					$upf->Save();
				}
			} else {
				//No New Hire defaults, use global defaults.
				Debug::text('Inserting Default Prefs (b)...', __FILE__, __LINE__, __METHOD__, 10);
				$upf = TTnew( 'UserPreferenceFactory' );
				$upf->setUser( $this->getId() );
				$upf->setLanguage( 'en' );
				$upf->setDateFormat( 'd-M-y' );
				$upf->setTimeFormat( 'g:i A' );
				$upf->setTimeUnitFormat( 10 );

				$upf->setTimeZone( $upf->getLocationTimeZone( $this->getCountry(), $this->getProvince(), $this->getWorkPhone(), $this->getHomePhone() ) );
				Debug::text('Time Zone: '. $upf->getTimeZone(), __FILE__, __LINE__, __METHOD__, 9);

				$upf->setItemsPerPage( 25 );
				$upf->setStartWeekDay( 0 );
				$upf->setEnableEmailNotificationException( TRUE );
				$upf->setEnableEmailNotificationMessage( TRUE );
				$upf->setEnableEmailNotificationPayStub( TRUE );
				$upf->setEnableEmailNotificationHome( TRUE );
				if ( $upf->isValid() ) {
					$upf->Save();
				}
			}
		}

		if ( $this->getDeleted() == TRUE ) {
			//Remove them from the authorization hierarchy, policy group, pay period schedule, stations, jobs, etc...
			//Delete any accruals for them as well.

			//Pay Period Schedule
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
			$ppslf->getByUserId( $this->getId() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$pps_obj = $ppslf->getCurrent();

				//Remove user from current schedule.
				$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
				$ppsulf->getByPayPeriodScheduleIdAndUserID( $pps_obj->getId(), $this->getId() );
				Debug::text('Record Count: '. $ppsulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $ppsulf->getRecordCount() > 0 ) {
					foreach( $ppsulf as $ppsu_obj ) {
						Debug::text('Deleting from Pay Period Schedule: '. $ppsu_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10);
						$ppsu_obj->Delete();
					}
				}
			}

			//Policy Group
			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getByUserIds( $this->getId() );
			if ( $pglf->getRecordCount() > 0 ) {
				$pg_obj = $pglf->getCurrent();

				$pgulf = TTnew( 'PolicyGroupUserListFactory' );
				$pgulf->getByPolicyGroupIdAndUserId( $pg_obj->getId(), $this->getId() );
				Debug::text('Record Count: '. $pgulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $pgulf->getRecordCount() > 0 ) {
					foreach( $pgulf as $pgu_obj ) {
						Debug::text('Deleteing from Policy Group: '. $pgu_obj->getPolicyGroup(), __FILE__, __LINE__, __METHOD__, 10);
						$pgu_obj->Delete();
					}
				}
			}

			//Hierarchy
			$hclf = TTnew( 'HierarchyControlListFactory' );
			$hclf->getByCompanyId( $this->getCompany() );
			if ( $hclf->getRecordCount() > 0 ) {
				foreach( $hclf as $hc_obj ) {
					$hf = TTnew( 'HierarchyListFactory' );
					$hf->setUser( $this->getID() );
					$hf->setHierarchyControl( $hc_obj->getId() );
					$hf->Delete();
				}
				$hf->removeCache( NULL, $hf->getTable(TRUE) ); //On delete we have to delete the entire group.
				unset($hf);
			}

			/*
			//Accrual balances - DON'T DO THIS ANYMORE, AS IT CAUSES PROBLEMS WITH RESTORING DELETED USERS. I THINK IT WAS JUST AN OPTIMIZATION ANYWAYS.
			$alf = TTnew( 'AccrualListFactory' );
			$alf->getByUserIdAndCompanyId( $this->getId(), $this->getCompany() );
			if ( $alf->getRecordCount() > 0 ) {
				foreach( $alf as $a_obj ) {
					$a_obj->setDeleted(TRUE);
					if ( $a_obj->isValid() ) {
						$a_obj->Save();
					}
				}
			}
			*/

			//Station employee critiera
			$siuf = TTnew( 'StationIncludeUserFactory' );
			$seuf = TTnew( 'StationExcludeUserFactory' );

			$query = 'delete from '. $siuf->getTable() .' where user_id = '. (int)$this->getId();
			$this->db->Execute($query);

			$query = 'delete from '. $seuf->getTable() .' where user_id = '. (int)$this->getId();
			$this->db->Execute($query);

			//Job employee criteria
			$cgmlf = TTnew( 'CompanyGenericMapListFactory' );
			$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getCompany(), array(1040, 1050), $this->getID() );
			if ( $cgmlf->getRecordCount() > 0 ) {
				foreach( $cgmlf as $cgm_obj ) {
					Debug::text('Deleteing from Company Generic Map: '. $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					$cgm_obj->Delete();
				}
			}
		}

		if ( $this->getDeleted() == TRUE OR $this->getStatus() != 10 ) {
			//Employee is being deleted or inactivated, make sure they are not a company contact, and if so replace them with a new contact.
			$default_company_contact_user_id = FALSE;
			if ( in_array( $this->getId(), array( $this->getCompanyObject()->getAdminContact(), $this->getCompanyObject()->getBillingContact(), $this->getCompanyObject()->getSupportContact() ) ) ) {
				$default_company_contact_user_id = $this->getCompanyObject()->getDefaultContact();
				Debug::text('User is primary company contact, remove and replace them with: '. $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10);

				if ( $default_company_contact_user_id != FALSE AND $this->getId() == $this->getCompanyObject()->getAdminContact() ) {
					$this->getCompanyObject()->setAdminContact( $default_company_contact_user_id );
					Debug::text('Replacing Admin Contact with: '. $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10);

				}
				if ( $default_company_contact_user_id != FALSE AND $this->getId() == $this->getCompanyObject()->getBillingContact() ) {
					$this->getCompanyObject()->setBillingContact( $default_company_contact_user_id );
					Debug::text('Replacing Billing Contact with: '. $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10);
				}
				if ( $default_company_contact_user_id != FALSE AND $this->getId() == $this->getCompanyObject()->getSupportContact() ) {
					$this->getCompanyObject()->setSupportContact( $default_company_contact_user_id );
					Debug::text('Replacing Support Contact with: '. $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10);
				}
				if ( $default_company_contact_user_id != FALSE AND $this->getCompanyObject()->isValid() ) {
					$this->getCompanyObject()->Save();
				}
			}
			unset($default_company_contact_user_id);
		}
		
		return TRUE;
	}

	function getMapURL() {
		return Misc::getMapURL( $this->getAddress1(), $this->getAddress2(), $this->getCity(), $this->getProvince(), $this->getPostalCode(), $this->getCountry() );
	}

	//Support setting created_by, updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'hire_date':
						case 'birth_date':
						case 'termination_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						case 'password':
							$password_confirm = NULL;
							if ( isset($data['password_confirm']) ) {
								$password_confirm = $data['password_confirm'];
							}
							$this->setPassword( $data[$key], $password_confirm );
							break;
						case 'last_login_date': //SKip this as its set by the system.
						case 'first_name_metaphone':
						case 'last_name_metaphone':
						case 'password_reset_date': //Password columns must not be changed from the API.
						case 'password_reset_key':
						case 'password_updated_date':
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
		/*
		$include_columns = array(
								'id' => TRUE,
								'company_id' => TRUE,
								...
								)

		*/

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'full_name':
							$data[$variable] = $this->getFullName(TRUE);
						case 'status':
						case 'sex':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'company':
						case 'title':
						case 'user_group':
						case 'ethnic_group':
						case 'currency':
						case 'currency_rate':
						case 'default_branch':
						case 'default_branch_manual_id':
						case 'default_department':
						case 'default_department_manual_id':
						case 'default_job':
						case 'default_job_manual_id':
						case 'default_job_item':
						case 'default_job_item_manual_id':
						case 'permission_control':
						case 'pay_period_schedule':
						case 'policy_group':
						case 'password_updated_date':
							$data[$variable] = $this->getColumn( $variable );
							break;
						//The below fields may be set if APISearch ListFactory is used to obtain the data originally,
						//but if it isn't, use the explicit function to get the data instead.
						case 'permission_control_id':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//Flex currently doesn't specify these fields in the Edit view though, so this breaks Flex.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->getColumn( $variable );
								if ( $data[$variable] == FALSE ) {
									$data[$variable] = $this->getPermissionControl();
								}
							//}
							break;
						case 'pay_period_schedule_id':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//Flex currently doesn't specify these fields in the Edit view though, so this breaks Flex.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->getColumn( $variable );
								if ( $data[$variable] == FALSE ) {
									$data[$variable] = $this->getPayPeriodSchedule();
								}
							//}
							break;
						case 'policy_group_id':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//Flex currently doesn't specify these fields in the Edit view though, so this breaks Flex.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->getColumn( $variable );
								if ( $data[$variable] == FALSE ) {
									$data[$variable] = $this->getPolicyGroup();
								}
							//}
							break;
						case 'hierarchy_control':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//Flex currently doesn't specify these fields in the Edit view though, so this breaks Flex.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->getHierarchyControl();
							//}
							break;
						case 'hierarchy_control_display':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->getHierarchyControlDisplay();
							//}
							break;
						case 'hierarchy_level_display':
							$data[$variable] = $this->getHierarchyLevelDisplay();
							break;
						case 'password': //Don't return password
							break;
						//case 'sin': //This is handled in the API class instead.
						//	$data[$variable] = $this->getSecureSIN();
						//	break;
						case 'last_login_date':
						case 'hire_date':
						case 'birth_date':
						case 'termination_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'birth_date_age':
							$data[$variable] = (int)floor( TTDate::getYearDifference( TTDate::getBeginDayEpoch( $this->getBirthDate() ), TTDate::getEndDayEpoch( time() ) ) );
							break;
						case 'hire_date_age':
							if ( $this->getTerminationDate() != '' ) {
								$end_epoch = $this->getTerminationDate();
							} else {
								$end_epoch = time();
							}
							//Staffing agencies may have employees for only a few days, so need to show partial years.
							$data[$variable] = number_format( TTDate::getYearDifference( TTDate::getBeginDayEpoch( $this->getHireDate() ), TTDate::getEndDayEpoch( $end_epoch ) ), 2 ); //Years (two decimals)
							unset($end_epoch);
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
				unset($function);
			}
			$this->getPermissionColumns( $data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee').': '. $this->getFullName( FALSE, TRUE ), NULL, $this->getTable(), $this );
	}
}
?>
