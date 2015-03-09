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
 * @package Modules\Qualification
 */
class UserSkillFactory extends Factory {
	protected $table = 'user_skill';
	protected $pk_sequence_name = 'user_skill_id_seq'; //PK Sequence name
	protected $qualification_obj = NULL;
	//protected $experience_validator_regex = '/^[0-9]{1,250}$/i';
	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'proficiency':
				$retval = array(
										10 => TTi18n::gettext('Excellent'),
										20 => TTi18n::gettext('Very Good'),
										30 => TTi18n::gettext('Good'),
										40 => TTi18n::gettext('Above Average'),

										50 => TTi18n::gettext('Average'),

										60 => TTi18n::gettext('Below Average'),
										70 => TTi18n::gettext('Fair'),
										80 => TTi18n::gettext('Poor'),
										90 => TTi18n::gettext('Bad'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),
										'-2050-qualification' => TTi18n::gettext('Skill'),
										'-2040-group' => TTi18n::gettext('Group'),
										'-2060-proficiency' => TTi18n::gettext('Proficiency'),
										'-2070-experience' => TTi18n::gettext('Experience'),
										'-2080-first_used_date' => TTi18n::gettext('First Used Date'),
										'-2090-last_used_date' => TTi18n::gettext('Last Used Date'),
										'-3010-enable_calc_experience' => TTi18n::gettext('Automatic Experience'),
										'-3020-expiry_date' => TTi18n::gettext('Expiry Date'),
										'-1040-description' => TTi18n::getText('Description'),

										'-1300-tag' => TTi18n::gettext('Tags'),


										'-1090-title' => TTi18n::gettext('Title'),
										'-1099-user_group' => TTi18n::gettext('Employee Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),

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
								'qualification',
								'proficiency',
								'experience',
								'first_used_date',
								'last_used_date',
								//'enable_calc_experience',
								'expiry_date',
								'description',
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
										'qualification_id' => 'Qualification',
										'qualification' => FALSE,
										'group' => FALSE,
										'proficiency_id' => 'Proficiency',
										'proficiency' => FALSE,
										'experience' => 'Experience',
										'first_used_date' => 'FirstUsedDate',
										'last_used_date' => 'LastUsedDate',
										'enable_calc_experience' => 'EnableCalcExperience',
										'expiry_date' => 'ExpiryDate',
										'description' => 'Description',
										'tag' => 'Tag',

										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}


	function getQualificationObject() {
		return $this->getGenericObject( 'QualificationListFactory', $this->getQualification(), 'qualification_obj' );
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
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getQualification() {
		if ( isset( $this->data['qualification_id'] ) ) {
			return (int)$this->data['qualification_id'];
		}
		return FALSE;
	}

	function setQualification( $id ) {
		$id = trim( $id );

		$qlf = TTnew( 'QualificationListFactory' );

		if( $this->Validator->isResultSetWithRows( 'qualification_id',
																	$qlf->getById( $id ),
																	TTi18n::gettext('Invalid Qualification')
																	) ) {
			$this->data['qualification_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getProficiency() {
		if ( isset( $this->data['proficiency_id'] ) ) {
			return (int)$this->data['proficiency_id'];
		}
		return FALSE;
	}

	function setProficiency( $proficiency_id ) {
		$proficiency_id = trim( $proficiency_id );

		if( $this->Validator->inArrayKey( 'proficiency_id',
										$proficiency_id,
										TTi18n::gettext( 'Proficiency is invalid' ),
										$this->getOptions( 'proficiency' ) ) ) {
			$this->data['proficiency_id'] = $proficiency_id;

			return TRUE;
		}

		return FALSE;
	}

	function getExperience() {
		if ( isset($this->data['experience']) AND $this->data['experience'] != '' ) {

			//Because experience is stored in a different column in the database, it doesn't get updated
			//in real-time. So each time this function is called and EnableCalcExperience is enabled,
			//calculate the experience again to its always accurate.
			//This is especially required when no last_used_date is set.
			$retval = ( $this->getEnableCalcExperience() == TRUE ) ? $this->calcExperience() : ($this->data['experience'] / 1000); //Divide by 1000 to convert to non-float value.

			return Misc::removeTrailingZeros( round( $retval, 4 ), 2 );
		}

		return FALSE;
	}
	function setExperience($value) {
		//This should always be set as years.
		$value = $this->Validator->stripNonFloat( trim($value) );

		//Assume they passed in number of seconds, convert to years.
		if ( $value >= 1000 ) {
			$value = TTDate::getYears( $value );
		}

		if ( $value < 0 ) {
			$value = 0;
		}

		if (  ( $value != ''
				AND
				$this->Validator->isNumeric(	'experience',
											$value,
											TTi18n::gettext('Experience number must only be digits')
										)
				AND
				$this->Validator->isLessThan( 'experience',
											$value,
											TTi18n::gettext('Years experience is too high'),
											110
										)
				)
				OR $value == ''
			) {

			$this->data['experience'] = $this->Validator->stripNon32bitInteger( $value * 1000 ); //Multiply by 1000 to convert to non-float value.

			return TRUE;
		}

		return FALSE;
	}

	function getFirstUsedDate( $raw = FALSE ) {
		if ( isset($this->data['first_used_date']) ) {
			return (int)$this->data['first_used_date'];
		}

		return FALSE;
	}
	function setFirstUsedDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ) {
			$epoch = NULL;
		}

		if	( $epoch == NULL
				OR
				$this->Validator->isDate(		'first_used_date',
												$epoch,
												TTi18n::gettext('First used date is invalid'))
			) {

			$this->data['first_used_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getLastUsedDate( $raw = FALSE ) {
		if ( isset($this->data['last_used_date']) ) {
			return (int)$this->data['last_used_date'];
		}

		return FALSE;
	}
	function setLastUsedDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ) {
			$epoch = NULL;
		}

		if	( $epoch == NULL
				OR
				$this->Validator->isDate(		'last_used_date',
												$epoch,
												TTi18n::gettext('Last used date is invalid'))
			) {

			$this->data['last_used_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function calcExperience() {
		if ( $this->getFirstUsedDate() != '' ) {
			$last_used_date = $this->getLastUsedDate();
			if ( $this->getLastUsedDate() == '' ) {
				$last_used_date = TTDate::getEndDayEpoch( time() );
			}

			$total_time = round( TTDate::getYears( ( $last_used_date - TTDate::getBeginDayEpoch( $this->getFirstUsedDate() ) ) ), 2);
			if ( $total_time < 0 ) {
				$total_time = 0;
			}

			Debug::text(' First Used Date: '. $this->getFirstUsedDate() .' Last Used Date: '. $last_used_date .' Total Yrs: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

			return $total_time;
		}

		return FALSE;
	}

	function getEnableCalcExperience() {
		if ( isset( $this->data['enable_calc_experience'] ) ) {
			return $this->fromBool( $this->data['enable_calc_experience'] );
		}

		return FALSE;
	}

	function setEnableCalcExperience( $bool ) {
		$this->data['enable_calc_experience'] = $this->toBool($bool);

		return TRUE;
	}

	function getExpiryDate( $raw = FALSE ) {
		if ( isset($this->data['expiry_date']) ) {
			return (int)$this->data['expiry_date'];
		}

		return FALSE;
	}
	function setExpiryDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ) {
			$epoch = NULL;
		}

		if	(	$epoch == NULL
				OR
				$this->Validator->isDate(		'expiry_date',
												$epoch,
												TTi18n::gettext('Expiry time stamp is invalid'))

			) {

			$this->data['expiry_date'] = $epoch;

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
				OR
				$this->Validator->isLength( 'description',
											$description,
											TTi18n::gettext('Description is invalid'),
											2, 255 )  ) {
				$this->data['description'] = $description;
				return	TRUE;
		}

		return FALSE;
	}

	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['tags']) ) {
			return $this->tmp_data['tags'];
		} elseif ( is_object( $this->getQualificationObject() ) AND $this->getQualificationObject()->getCompany() > 0 AND $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getQualificationObject()->getCompany(), 251, $this->getID() );
		}

		return FALSE;
	}
	function setTag( $tags ) {
		$tags = trim($tags);

		//Save the tags in temporary memory to be committed in postSave()
		$this->tmp_data['tags'] = $tags;

		return TRUE;
	}


	function Validate() {
		return TRUE;
	}

	function preSave() {
		if ( $this->getEnableCalcExperience() == TRUE ) {
			$this->setExperience( $this->calcExperience() );
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( $this->getUser().$this->getQualification() );

		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getQualificationObject()->getCompany(), 251, $this->getID(), $this->getTag() );
		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {

		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'first_used_date':
							$this->setFirstUsedDate( TTDate::parseDateTime( $data['first_used_date'] ) );
							break;
						case 'last_used_date':
							$this->setLastUsedDate( TTDate::parseDateTime( $data['last_used_date'] ) );
							break;
						case 'expiry_date':
							$this->setExpiryDate( TTDate::parseDateTime( $data['expiry_date'] ) );
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$variable_function_map = $this->getVariableToFunctionMap();

		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;

					switch( $variable ) {
						case 'qualification':
						case 'group':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'proficiency':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'first_used_date':
							$data['first_used_date'] = TTDate::getAPIDate( 'DATE', $this->getFirstUsedDate() );
							break;
						case 'last_used_date':
							$data['last_used_date'] = TTDate::getAPIDate( 'DATE', $this->getLastUsedDate() );
							break;
						case 'expiry_date':
							$data['expiry_date'] = TTDate::getAPIDate( 'DATE', $this->getExpiryDate() );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );

			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Skill'), NULL, $this->getTable(), $this );
	}

}
?>
