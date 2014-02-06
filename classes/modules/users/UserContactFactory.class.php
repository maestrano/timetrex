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
 * $Revision: 6005 $
 * $Id: UserContactFactory.class.php 6005 2012-01-11 23:32:39Z ipso $
 * $Date: 2012-01-12 07:32:39 +0800 (Thu, 12 Jan 2012) $
 */

/**
 * @package Module_Users
*/

class UserContactFactory extends Factory {
	protected $table = 'user_contact';
	protected $pk_sequence_name = 'user_contact_id_seq'; //PK Sequence name

	protected $tmp_data = NULL;
    protected $user_obj = NULL;
	protected $name_validator_regex = '/^[a-zA-Z -\.\'|\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('ENABLED'),
										20 => TTi18n::gettext('DISABLED'),
									);
				break;
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Spouse/Partner'),
										20 => TTi18n::gettext('Parent/Guardian'),
										30 => TTi18n::gettext('Sibling'),
										40 => TTi18n::gettext('Child'),
                                        50 => TTi18n::gettext('Relative'),
										60 => TTi18n::gettext('Dependant'),
                                        70 => TTi18n::gettext('Emergency Contact'),
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
                                        '-1090-employee_first_name' => TTi18n::gettext('Employee First Name'),
                                        //'-1100-employee_middle_name' => TTi18n::gettext('Employee Middle Name'),
                                        '-1110-employee_last_name' => TTi18n::gettext('Employee Last Name'),
                                        
                                        '-1010-title' => TTi18n::gettext('Employee Title'),
										'-1099-user_group' => TTi18n::gettext('Employee Group'),
										'-1100-default_branch' => TTi18n::gettext('Employee Branch'),
										'-1030-default_department' => TTi18n::gettext('Employee Department'),
                                        
										'-1060-first_name' => TTi18n::gettext('First Name'),
                                        '-1070-middle_name' => TTi18n::gettext('Middle Name'),
										'-1080-last_name' => TTi18n::gettext('Last Name'),
										'-1020-status' => TTi18n::gettext('Status'),
                                        '-1050-type' => TTi18n::getText('Type'),

										'-1120-sex' => TTi18n::gettext('Gender'),
										'-1125-ethnic_group' => TTi18n::gettext('Ethnic Group'),

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
										'-1280-sin' => TTi18n::gettext('SIN/SSN'),
										'-1290-note' => TTi18n::gettext('Note'),
										'-1300-tag' => TTi18n::gettext('Tags'),
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
								//'status',
                                'employee_first_name',
                                'employee_last_name',
                                'title',
                                'user_group',
                                'default_branch',
                                'default_department',
                                'type',
								'first_name',
								'last_name',
								'home_phone',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
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
										'user_id' => 'User',
										'status_id' => 'Status',
										'status' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'employee_first_name' => FALSE,
										'employee_last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,
										'first_name' => 'FirstName',
										'middle_name' => 'MiddleName',
										'last_name' => 'LastName',
										'sex_id' => 'Sex',
										'sex' => FALSE,
										'ethnic_group_id' => 'EthnicGroup',
										'ethnic_group' => FALSE,
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
										'work_email' => 'WorkEmail',
										'birth_date' => 'BirthDate',
										'sin' => 'SIN',
										'note' => 'Note',
										'tag' => 'Tag',
										'deleted' => 'Deleted',
 										);
		return $variable_function_map;
	}

    function getUserObject() {
        return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
    }

     function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
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

		if ( $this->Validator->inArrayKey(	'status_id',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

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
    function setType($type) {
        $type = trim($type);
        $key = Option::getByValue($type, $this->getOptions('type'));
        if ($key !== FALSE) {
            $type = $key ;
        }

        if ( $this->Validator->inArrayKey( 'type_id',
                                            $type,
                                            TTi18n::gettext('Incorrect Type'),
                                            $this->getOptions('type') ) ) {
            $this->data['type_id'] = $type;
            return TRUE;
        }

        return FALSE;
    }


	function getFirstName() {
		if ( isset($this->data['first_name']) ) {
			return $this->data['first_name'];
		}

		return FALSE;
	}
	function setFirstName($first_name) {
		$first_name = trim($first_name);

		if 	(	$this->Validator->isRegEx(		'first_name',
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

			return TRUE;
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
		$middle_name = trim($middle_name);

		if 	(
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
		$last_name = trim($last_name);

		if 	(	$this->Validator->isRegEx(		'last_name',
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

    function getFullName($reverse = FALSE, $include_middle = TRUE ) {
		return Misc::getFullName($this->getFirstName(), $this->getMiddleInitial(), $this->getLastName(), $reverse, $include_middle);
	}

	function getSex() {
		if ( isset($this->data['sex_id']) ) {
			return $this->data['sex_id'];
		}

		return FALSE;
	}
	function setSex($sex) {
		$sex = trim($sex);

		if ( $this->Validator->inArrayKey(	'sex_id',
											$sex,
											TTi18n::gettext('Invalid gender'),
											$this->getOptions('sex') ) ) {

			$this->data['sex_id'] = $sex;

			return TRUE;
		}

		return FALSE;
	}

    function getEthnicGroup() {
        if ( isset( $this->data['ethnic_group_id'] ) ) {
            return $this->data['ethnic_group_id'];
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

	function getAddress1() {
		if ( isset($this->data['address1']) ) {
			return $this->data['address1'];
		}

		return FALSE;
	}
	function setAddress1($address1) {
		$address1 = trim($address1);

		if 	(
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

		if 	(	$address2 == ''
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

		//Debug::Text('Country: '. $this->getCountry() .' Province: '. $province, __FILE__, __LINE__, __METHOD__,10);

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

		if 	(
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

	function getHomePhone() {
		if ( isset($this->data['home_phone']) ) {
			return $this->data['home_phone'];
		}

		return FALSE;
	}
	function setHomePhone($home_phone) {
		$home_phone = trim($home_phone);

		if 	(	$home_phone == ''
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

		if 	(	$mobile_phone == ''
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

		if 	(	$fax_phone == ''
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

		$error_threshold = 7; //No DNS checks.
		if ( DEPLOYMENT_ON_DEMAND === TRUE ) {
			$error_threshold = 0; //DNS checks on email address.
		}
		if 	(	$home_email == ''
					OR $this->Validator->isEmailAdvanced(	'home_email',
													$home_email,
													TTi18n::gettext('Home Email address is invalid'),
													$error_threshold ) ) {

			$this->data['home_email'] = $home_email;

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

		$error_threshold = 7; //No DNS checks.
		if ( DEPLOYMENT_ON_DEMAND === TRUE ) {
			$error_threshold = 0; //DNS checks on email address.
		}
		if 	(	$work_email == ''
					OR	$this->Validator->isEmailAdvanced(	'work_email',
													$work_email,
													TTi18n::gettext('Work Email address is invalid'),
													$error_threshold) ) {

			$this->data['work_email'] = $work_email;

			return TRUE;
		}

		return FALSE;
	}


	function getBirthDate() {
		if ( isset($this->data['birth_date']) ) {
			return $this->data['birth_date'];
		}

		return FALSE;
	}
	function setBirthDate($epoch) {
		if 	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'birth_date',
												$epoch,
												TTi18n::gettext('Birth date is invalid, try specifying the year with four digits.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['birth_date'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}

	function getSecureSIN( $sin = NULL ) {
		if ( $sin == '' ) {
			$sin = $this->getSIN();
		}
		if ( $sin != '' ) {
			//Grab the first 1, and last 3 digits.
			$first_four = substr( $sin, 0, 1 );
			$last_four = substr( $sin, -3 );

			$total = strlen($sin)-4;

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
		if ( stripos( $sin, 'X') !== FALSE  ) {
			return FALSE;
		}

		$sin = $this->Validator->stripNonNumeric( trim($sin) );

		if 	(
				$sin == ''
				OR
				$this->Validator->isLength(		'sin',
												$sin,
												TTi18n::gettext('SIN is invalid'),
												6,
												20)
				) {

			$this->data['sin'] = $sin;

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

	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['tags']) ) {
			return $this->tmp_data['tags'];
		} elseif ( is_object($this->getUserObject()) AND $this->getUserObject()->getCompany() > 0 AND $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getUserObject()->getCompany(), 230, $this->getID() );
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

	function Validate() {
		//When doing a mass edit of employees, user name is never specified, so we need to avoid this validation issue.

		//Re-validate the province just in case the country was set AFTER the province.
		$this->setProvince( $this->getProvince() );
																																												if ( $this->isNew() == TRUE ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); } }
		return TRUE;
	}

	function preSave() {

		if ( $this->getStatus() == FALSE ) {
			$this->setStatus( 10 ); //ENABLE
		}

		if ( $this->getSex() == FALSE ) {
			$this->setSex( 5 ); //UnSpecified
		}

		if ( $this->getEthnicGroup() == FALSE ) {
			$this->setEthnicGroup( 0 );
		}

		//Remember if this is a new user for postSave()
		if ( $this->isNew() ) {
			$this->is_new = TRUE;
		}

		return TRUE;
	}

	function postSave( ) {
		$this->removeCache( $this->getId() );

	    if ( $this->getDeleted() == FALSE ) {
            Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getUserObject()->getCompany(), 230, $this->getID(), $this->getTag() );
		}

		return TRUE;
	}

	function getMapURL() {
		return Misc::getMapURL( $this->getAddress1(), $this->getAddress2(), $this->getCity(), $this->getProvince(), $this->getPostalCode(), $this->getCountry() );
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
						case 'birth_date':
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

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
					    case 'employee_first_name':
                        case 'employee_last_name':
                        case 'title':
						case 'user_group':
						case 'ethnic_group':
                        case 'default_branch':
						case 'default_department':
                            $data[$variable] = $this->getColumn( $variable );
                            break;
						case 'full_name':
							$data[$variable] = $this->getFullName(TRUE);
                            break;
						case 'status':
                        case 'type':
						case 'sex':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'sin':
							$data[$variable] = $this->getSecureSIN();
							break;
						case 'birth_date':
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
				unset($function);
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
            
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee Contact ').': '. $this->getFullName( FALSE, TRUE ) , NULL, $this->getTable(), $this );
	}
}
?>
