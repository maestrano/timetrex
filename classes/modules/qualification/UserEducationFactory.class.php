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
class UserEducationFactory extends Factory {
	protected $table = 'user_education';
	protected $pk_sequence_name = 'user_education_id_seq'; //PK Sequence name
	protected $qualification_obj = NULL;
	//protected $grade_score_validator_regex = '/^[0-9]{1,250}$/i';
	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),

										'-2050-qualification' => TTi18n::gettext('Course'),

										'-2040-group' => TTi18n::gettext('Group'),

										'-3030-institute' => TTi18n::gettext('Institute'),
										'-3040-major' => TTi18n::gettext('Major/Specialization'),
										'-3050-minor' => TTi18n::gettext('Minor'),
										'-3060-graduate_date' => TTi18n::gettext('Graduation Date'),
										'-3070-grade_score' => TTi18n::gettext('Grade/Score'),
										'-1170-start_date' => TTi18n::gettext('Start Date'),
										'-1180-end_date' => TTi18n::gettext('End Date'),

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
								'institute',
								'major',
								'minor',
								'graduate_date',
								'grade_score',
								'start_date',
								'end_date',
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
										'institute' => 'Institute',
										'major' => 'Major',
										'minor' => 'Minor',
										'graduate_date' => 'GraduateDate',
										'grade_score' => 'GradeScore',
										'start_date' => 'StartDate',
										'end_date' => 'EndDate',

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

	function getInstitute() {
		if ( isset($this->data['institute']) ) {
			return $this->data['institute'];
		}
		return FALSE;
	}

	function setInstitute( $institute ) {
		$institute = trim($institute);

		if (	$institute == ''
				OR
				$this->Validator->isLength( 'institute',
											$institute,
											TTi18n::gettext('Institute is invalid'),
											2, 255 )  ) {
				$this->data['institute'] = $institute;
				return	TRUE;
		}

		return FALSE;
	}



	function getMajor() {
		if ( isset( $this->data['major'] ) ) {
			return $this->data['major'];
		}
		return FALSE;
	}

	function setMajor( $major ) {
		$major = trim($major);

		if (	$major == ''
				OR
				$this->Validator->isLength( 'major',
											$major,
											TTi18n::gettext('Major/Specialization is invalid'),
											2, 255 )  ) {
				$this->data['major'] = $major;
				return	TRUE;
		}

		return FALSE;
	}

	function getMinor() {
		if ( isset( $this->data['minor'] ) ) {
			return $this->data['minor'];
		}
		return FALSE;
	}


	function setMinor( $minor ) {
		$minor = trim($minor);

		if (	$minor == ''
				OR
				$this->Validator->isLength( 'minor',
											$minor,
											TTi18n::gettext('Minor is invalid'),
											2, 255 )  ) {
				$this->data['minor'] = $minor;
				return	TRUE;
		}

		return FALSE;
	}

	function getGraduateDate( ) {
		if ( isset($this->data['graduate_date']) ) {
			return (int)$this->data['graduate_date'];
		}

		return FALSE;
	}
	function setGraduateDate($epoch) {
		$epoch = trim($epoch);
		if ( $epoch == '' ) {
			$epoch = NULL;
		}
		if	( $epoch == NULL
				OR
				$this->Validator->isDate(		'graduate_date',
												$epoch,
												TTi18n::gettext('Incorrect graduation date'))
			) {

			$this->data['graduate_date'] = $epoch;

			return TRUE;


		}

		return FALSE;
	}

	function getGradeScore() {
		if ( isset( $this->data['grade_score'] ) ) {
			return $this->data['grade_score'];
		}
		return FALSE;
	}

	function setGradeScore( $grade_score ) {
		$grade_score = trim($grade_score);
		// $grade_score = $this->Validator->stripNonFloat( $grade_score );
		if ( (	$grade_score != ''
				AND
				( $this->Validator->isNumeric(	'grade_score',
													$grade_score,
													TTi18n::gettext('Grade/Score must only be digits')
										)
				AND
				$this->Validator->isLengthAfterDecimal( 'grade_score',
													$grade_score,
													TTi18n::gettext('Invalid Grade/Score'),
													0,
													2
										) ) )
				OR $grade_score == ''

			) {
				$this->data['grade_score'] = $grade_score;
				return	TRUE;
		}

		return FALSE;
	}

	function getStartDate() {
		if ( isset($this->data['start_date']) ) {
			return (int)$this->data['start_date'];
		}

		return FALSE;
	}
	function setStartDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ) {
			$epoch = NULL;
		}

		if	( $epoch == NULL
				OR
				$this->Validator->isDate(		'start_date',
												$epoch,
												TTi18n::gettext('Incorrect start date'))
			) {

			$this->data['start_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndDate() {
		if ( isset($this->data['end_date']) ) {
			return (int)$this->data['end_date'];
		}

		return FALSE;
	}
	function setEndDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ) {
			$epoch = NULL;
		}

		if	(	$epoch == NULL
				OR
				$this->Validator->isDate(		'end_date',
												$epoch,
												TTi18n::gettext('Incorrect end date'))
			) {

			$this->data['end_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['tags']) ) {
			return $this->tmp_data['tags'];
		} elseif ( is_object( $this->getQualificationObject() ) AND $this->getQualificationObject()->getCompany() > 0 AND $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getQualificationObject()->getCompany(), 252, $this->getID() );
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
		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( $this->getUser().$this->getQualification() );

		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getQualificationObject()->getCompany(), 252, $this->getID(), $this->getTag() );
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
						case 'start_date':
							$this->setStartDate( TTDate::parseDateTime( $data['start_date'] ) );
							break;
						case 'end_date':
							$this->setEndDate( TTDate::parseDateTime( $data['end_date'] ) );
							break;
						case 'graduate_date':
							$this->setGraduateDate( TTDate::parseDateTime( $data['graduate_date'] ) );
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
						case 'start_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getStartDate() );
							break;
						case 'end_date':
							$data['end_date'] = TTDate::getAPIDate( 'DATE', $this->getEndDate() );
							break;
						case 'graduate_date':
							$data['graduate_date'] = TTDate::getAPIDate( 'DATE', $this->getGraduateDate() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Education'), NULL, $this->getTable(), $this );
	}

}
?>
