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
 * $Revision: 9804 $
 * $Id: UserDefaultFactory.class.php 9804 2013-05-08 15:59:47Z ipso $
 * $Date: 2013-05-08 08:59:47 -0700 (Wed, 08 May 2013) $
 */

/**
 * @package Modules\Users
 */
class UserDefaultFactory extends Factory {
	protected $table = 'user_default';
	protected $pk_sequence_name = 'user_default_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $title_obj = NULL;

	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

	function _getVariableToFunctionMap( $data ) {
			$variable_function_map = array(
											'id' => 'ID',
											'company_id' => 'Company',
											'permission_control_id' => 'PermissionControl',
											'pay_period_schedule_id' => 'PayPeriodSchedule',
											'policy_group_id' => 'PolicyGroup',
											'employee_number' => 'EmployeeNumber',
											'title_id' => 'Title',
											'default_branch_id' => 'DefaultBranch',
											'default_department_id' => 'DefaultDepartment',
											'currency_id' => 'Currency',
											'city' => 'City',
											'country' => 'Country',
											'province' => 'Province',
											'work_phone' => 'WorkPhone',
											'work_phone_ext' => 'WorkPhoneExt',
											'work_email' => 'WorkEmail',
											'hire_date' => 'HireDate',
											'language' => 'Language',
											'date_format' => 'DateFormat',
											'time_format' => 'TimeFormat',
											'time_zone' => 'TimeZone',
											'time_unit_format' => 'TimeUnitFormat',
											'items_per_page' => 'ItemsPerPage',
											'start_week_day' => 'StartWeekDay',
											'enable_email_notification_exception' => 'EnableEmailNotificationException',
											'enable_email_notification_message' => 'EnableEmailNotificationMessage',
											'enable_email_notification_home' => 'EnableEmailNotificationHome',
											'company_deduction' => 'CompanyDeduction',
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

	function getTitleObject() {
		if ( is_object($this->title_obj) ) {
			return $this->title_obj;
		} else {

			$utlf = TTnew( 'UserTitleListFactory' );
			$utlf->getById( $this->getTitle() );

			if ( $utlf->getRecordCount() == 1 ) {
				$this->title_obj = $utlf->getCurrent();

				return $this->title_obj;
			}

			return FALSE;
		}
	}

	function isUniqueCompany($company_id) {
		$ph = array(
					'company_id' => $company_id,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND deleted=0';
		$unique_company_id = $this->db->GetOne($query, $ph);
		Debug::Arr($unique_company_id,'Unique Company: '. $this->getID(), __FILE__, __LINE__, __METHOD__,10);

		if ( $unique_company_id === FALSE ) {
			return TRUE;
		} else {
			if ($unique_company_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
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

		if ( 	$this->Validator->isResultSetWithRows(	'company',
														$clf->getByID($id),
														TTi18n::gettext('Company is invalid')
				AND
				$this->Validator->isTrue(		'company',
												$this->isUniqueCompany($id),
												TTi18n::gettext('Default settings for this company already exist') )
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPermissionControl() {
		if ( isset($this->data['permission_control_id']) ) {
			return $this->data['permission_control_id'];
		}

		return FALSE;
	}
	function setPermissionControl($id) {
		$id = trim($id);

		$pclf = TTnew( 'PermissionControlListFactory' );

		if (  $this->Validator->isResultSetWithRows(		'permission_control_id',
															$pclf->getByID($id),
															TTi18n::gettext('Permission Group is invalid')
															) ) {
			$this->data['permission_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayPeriodSchedule() {
		if ( isset($this->data['pay_period_schedule_id']) ) {
			return $this->data['pay_period_schedule_id'];
		}

		return FALSE;
	}
	function setPayPeriodSchedule($id) {
		$id = trim($id);

		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'pay_period_schedule_id',
															$ppslf->getByID($id),
															TTi18n::gettext('Pay Period schedule is invalid')
															) ) {
			$this->data['pay_period_schedule_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPolicyGroup() {
		if ( isset($this->data['policy_group_id']) ) {
			return $this->data['policy_group_id'];
		}

		return FALSE;
	}
	function setPolicyGroup($id) {
		$id = trim($id);

		$pglf = TTnew( 'PolicyGroupListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'policy_group_id',
															$pglf->getByID($id),
															TTi18n::gettext('Policy Group is invalid')
															) ) {
			$this->data['policy_group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getEmployeeNumber() {
		if ( isset($this->data['employee_number']) ) {
			return $this->data['employee_number'];
		}

		return FALSE;
	}
	function setEmployeeNumber($value) {
		$value = trim($value);

		if 	(
				$value == ''
				OR
					$this->Validator->isLength(		'employee_number',
													$value,
													TTi18n::gettext('Employee number is too short or too long'),
													1,
													100) ) {

			$this->data['employee_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getTitle() {
		if ( isset($this->data['title_id']) ) {
			return $this->data['title_id'];
		}

		return FALSE;
	}
	function setTitle($id) {
		$id = trim($id);

		Debug::Text('Title ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
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

	function getDefaultBranch() {
		if ( isset($this->data['default_branch_id']) ) {
			return $this->data['default_branch_id'];
		}

		return FALSE;
	}
	function setDefaultBranch($id) {
		$id = trim($id);

		Debug::Text('Branch ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
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
			return $this->data['default_department_id'];
		}

		return FALSE;
	}
	function setDefaultDepartment($id) {
		$id = trim($id);

		Debug::Text('Department ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
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

	function getCurrency() {
		if ( isset($this->data['currency_id']) ) {
			return $this->data['currency_id'];
		}

		return FALSE;
	}
	function setCurrency($id) {
		$id = trim($id);

		Debug::Text('Currency ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$culf = TTnew( 'CurrencyListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'currency',
														$culf->getByID($id),
														TTi18n::gettext('Invalid Currency')
													) ) {

			$this->data['currency_id'] = $id;

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

		if 	(
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

		Debug::Text('Country: '. $this->getCountry() .' Province: '. $province, __FILE__, __LINE__, __METHOD__,10);

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

	function getWorkPhone() {
		if ( isset($this->data['work_phone']) ) {
			return $this->data['work_phone'];
		}

		return FALSE;
	}
	function setWorkPhone($work_phone) {
		$work_phone = trim($work_phone);

		if 	(
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

		if ( 	$work_phone_ext == ''
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

	function getWorkEmail() {
		if ( isset($this->data['work_email']) ) {
			return $this->data['work_email'];
		}

		return FALSE;
	}
	function setWorkEmail($work_email) {
		$work_email = trim($work_email);

		if 	(	$work_email == ''
					OR	$this->Validator->isEmail(	'work_email',
													$work_email,
													TTi18n::gettext('Work Email address is invalid')) ) {

			$this->data['work_email'] = $work_email;

			return TRUE;
		}

		return FALSE;
	}

	function getHireDate() {
		if ( isset($this->data['hire_date']) ) {
			return $this->data['hire_date'];
		}

		return FALSE;
	}
	function setHireDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'hire_date',
												$epoch,
												TTi18n::gettext('Hire date is invalid')) ) {

			$this->data['hire_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	/*

		User Preferences

	*/
	function getLanguage() {
		if ( isset($this->data['language']) ) {
			return $this->data['language'];
		}

		return FALSE;
	}
	function setLanguage($value) {
		$value = trim($value);

		$language_options = TTi18n::getLanguageArray();

		$key = Option::getByValue($value, $language_options );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'language',
											$value,
											TTi18n::gettext('Incorrect language'),
											$language_options ) ) {

			$this->data['language'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDateFormat() {
		if ( isset($this->data['date_format']) ) {
			return $this->data['date_format'];
		}

		return FALSE;
	}
	function setDateFormat($date_format) {
		$date_format = trim($date_format);
		$upf = TTnew( 'UserPreferenceFactory' );

		if ( $this->Validator->inArrayKey(	'date_format',
											$date_format,
											TTi18n::gettext('Incorrect date format'),
											Misc::trimSortPrefix( $upf->getOptions('date_format') )) ) {

			$this->data['date_format'] = $date_format;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeFormat() {
		if ( isset($this->data['time_format']) ) {
			return $this->data['time_format'];
		}

		return FALSE;
	}
	function setTimeFormat($time_format) {
		$time_format = trim($time_format);

		$upf = TTnew( 'UserPreferenceFactory' );

		$key = Option::getByValue($time_format, $upf->getOptions('time_format') );
		if ($key !== FALSE) {
			$time_format = $key;
		}

		if ( $this->Validator->inArrayKey(	'time_format',
											$time_format,
											TTi18n::gettext('Incorrect time format'),
											$upf->getOptions('time_format')) ) {

			$this->data['time_format'] = $time_format;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeZone() {
		if ( isset($this->data['time_zone']) ) {
			return $this->data['time_zone'];
		}

		return FALSE;
	}
	function setTimeZone($time_zone) {
		$time_zone = Misc::trimSortPrefix( trim($time_zone) );

		$upf = TTnew( 'UserPreferenceFactory' );

		if ( $this->Validator->inArrayKey(	'time_zone',
											$time_zone,
											TTi18n::gettext('Incorrect time zone'),
											Misc::trimSortPrefix( $upf->getOptions('time_zone') ) ) ) {

			$this->data['time_zone'] = $time_zone;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeUnitFormatExample() {
		$options = $this->getOptions('time_unit_format');

		return $options[$this->getTimeUnitFormat()];
	}
	function getTimeUnitFormat() {
		return $this->data['time_unit_format'];
	}
	function setTimeUnitFormat($time_unit_format) {
		$time_unit_format = trim($time_unit_format);

		$upf = TTnew( 'UserPreferenceFactory' );

		$key = Option::getByValue($time_unit_format, $upf->getOptions('time_unit_format') );
		if ($key !== FALSE) {
			$time_unit_format = $key;
		}

		if ( $this->Validator->inArrayKey(	'time_unit_format',
											$time_unit_format,
											TTi18n::gettext('Incorrect time units'),
											$upf->getOptions('time_unit_format')) ) {

			$this->data['time_unit_format'] = $time_unit_format;

			return TRUE;
		}

		return FALSE;
	}

	function getItemsPerPage() {
		if ( isset($this->data['items_per_page']) ) {
			return $this->data['items_per_page'];
		}

		return FALSE;
	}
	function setItemsPerPage($items_per_page) {
		$items_per_page = trim($items_per_page);

		if 	($items_per_page != '' AND $items_per_page >= 1 AND $items_per_page <= 200) {

			$this->data['items_per_page'] = $items_per_page;

			return TRUE;
		} else {

			$this->Validator->isTrue(		'items_per_page',
											FALSE,
											TTi18n::gettext('Items per page must be between 10 and 200'));
		}

		return FALSE;
	}

	function getStartWeekDay() {
		if ( isset($this->data['start_week_day']) ) {
			return $this->data['start_week_day'];
		}

		return FALSE;
	}
	function setStartWeekDay($value) {
		$value = trim($value);

		$upf = TTnew( 'UserPreferenceFactory' );

		$key = Option::getByValue($value, $upf->getOptions('start_week_day') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'start_week_day',
											$value,
											TTi18n::gettext('Incorrect day to start a week on'),
											$upf->getOptions('start_week_day')) ) {

			$this->data['start_week_day'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getEnableEmailNotificationException() {
		return $this->fromBool( $this->data['enable_email_notification_exception'] );
	}
	function setEnableEmailNotificationException($bool) {
		$this->data['enable_email_notification_exception'] = $this->toBool($bool);

		return TRUE;
	}
	function getEnableEmailNotificationMessage() {
		return $this->fromBool( $this->data['enable_email_notification_message'] );
	}
	function setEnableEmailNotificationMessage($bool) {
		$this->data['enable_email_notification_message'] = $this->toBool($bool);

		return TRUE;
	}
	function getEnableEmailNotificationHome() {
		return $this->fromBool( $this->data['enable_email_notification_home'] );
	}
	function setEnableEmailNotificationHome($bool) {
		$this->data['enable_email_notification_home'] = $this->toBool($bool);

		return TRUE;
	}

	/*

		Company Deductions

	*/
	function getCompanyDeduction() {
		$udcdlf = TTnew( 'UserDefaultCompanyDeductionListFactory' );
		$udcdlf->getByUserDefaultId( $this->getId() );
		foreach ($udcdlf as $obj) {
			$list[] = $obj->getCompanyDeduction();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setCompanyDeduction($ids) {
		Debug::text('Setting Company Deduction IDs : ', __FILE__, __LINE__, __METHOD__, 10);

		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		if ( is_array($ids) ) {
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$udcdlf = TTnew( 'UserDefaultCompanyDeductionListFactory' );
				$udcdlf->getByUserDefaultId( $this->getId() );

				$tmp_ids = array();
				foreach ($udcdlf as $obj) {
					$id = $obj->getCompanyDeduction();
					Debug::text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			} else {
				$tmp_ids = array();
			}

			//Insert new mappings.
			//$lf = TTnew( 'UserListFactory' );
			$cdlf = TTnew( 'CompanyDeductionListFactory' );

			foreach ($ids as $id) {
				if ( $id != FALSE AND isset($ids) AND !in_array($id, $tmp_ids) ) {
					$udcdf = TTnew( 'UserDefaultCompanyDeductionFactory' );
					$udcdf->setUserDefault( $this->getId() );
					$udcdf->setCompanyDeduction( $id );

					$obj = $cdlf->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'company_deduction',
														$udcdf->Validator->isValid(),
														TTi18n::gettext('Deduction is invalid').' ('. $obj->getName() .')' )) {
						$udcdf->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function Validate() {
		if ( $this->getCompany() == FALSE ) {
			$this->Validator->isTrue(		'company',
											FALSE,
											TTi18n::gettext('Company is invalid'));
		}

		return TRUE;
	}

	function postSave() {
		return TRUE;
	}

	//Support setting created_by,updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
					    case 'hire_date':
                            $this->setHireDate( TTDate::parseDateTime( $data['hire_date'] ) );
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
		$uf = TTnew( 'UserFactory' );

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
					    case 'hire_date':
                            $data[$variable] = TTDate::getAPIDate( 'DATE', $this->getHireDate() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee Default Information'), NULL, $this->getTable(), $this );
	}

}
?>
