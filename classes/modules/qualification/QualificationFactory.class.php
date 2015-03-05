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
class QualificationFactory extends Factory {
	protected $table = 'qualification';
	protected $pk_sequence_name = 'qualification_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $tmp_data = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Skill'),
										20 => TTi18n::gettext('Education'),
										30 => TTi18n::gettext('License'),
										40 => TTi18n::gettext('Language'),
										50 => TTi18n::gettext('Membership')
									);
				break;
			case 'columns':
				$retval = array(
										'-1030-name' => TTi18n::gettext('Name'),
										'-1040-description' => TTi18n::getText('Description'),
										'-1050-type' => TTi18n::getText('Type'),

										'-2040-group' => TTi18n::gettext('Group'),
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
								'type',
								'name',
								'description',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name'
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'type_id' => 'Type',
										'type' => FALSE,
										'company_id' => 'Company',
										'group_id' => 'Group',
										'group' => FALSE,
										'name' => 'Name',
										'name_metaphone' => 'NameMetaphone',
										'description' => 'Description',

										'tag' => 'Tag',

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return (int)$this->data['type_id'];
		}
		return FALSE;
	}

	function setType($type_id) {
		$type_id = trim($type_id);

		if (  $this->Validator->inArrayKey(	'type_id',
											$type_id,
											TTi18n::gettext('Type is invalid'),
											$this->getOptions('type')) ) {
			$this->data['type_id'] = $type_id;

			return FALSE;
		}

		return FALSE;
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

		$clf = TTnew( 'CompanyListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'company_id',
															$clf->getByID($id),
															TTi18n::gettext('Company is invalid')
															) ) {
			$this->data['company_id'] = $id;

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

		Debug::Text('Group ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$qglf = TTnew( 'QualificationGroupListFactory' );
		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'group_id',
														$qglf->getByID($id),
														TTi18n::gettext('Group is invalid')
													) ) {

			$this->data['group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}


	function isUniqueName($name) {

		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $this->getCompany(),
					'name' => $name,
					);

		$query = 'select id from '. $this->table .'
					where company_id = ?
						AND name = ?
						AND deleted = 0';
		$name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($name_id, 'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
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

		if	(	$this->Validator->isLength( 'name',
											$name,
											TTi18n::gettext('Qualification name is invalid'),
											1 )
				AND
				$this->Validator->isTrue(	'name',
										$this->isUniqueName($name),
										TTi18n::gettext('Qualification name already exists')) ) {

			$this->data['name'] = $name;

			$this->setNameMetaphone( $name );

			return TRUE;
		}

		return FALSE;
	}

	function getNameMetaphone() {
		if ( isset($this->data['name_metaphone']) ) {
			return $this->data['name_metaphone'];
		}

		return FALSE;
	}
	function setNameMetaphone($value) {
		$value = metaphone( trim($value) );

		if	( $value != '' ) {
			$this->data['name_metaphone'] = $value;

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
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 250, $this->getID() );
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
		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.

		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {

		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 250, $this->getID(), $this->getTag() );
		}

		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('UnAssign Hours from Qualification: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			//Unassign hours from this qualification.

			$sf = TTnew( 'UserSkillFactory' );
			$ef = TTnew( 'UserEducationFactory' );
			$lf = TTnew( 'UserLicenseFactory' );
			$lg = TTnew( 'UserLanguageFactory' );
			$mf = TTnew( 'UserMembershipFactory' );

			$query = 'update '. $sf->getTable() .' set qualification_id = 0 where qualification_id = '. (int)$this->getId();
			$this->db->Execute($query);

			$query = 'update '. $ef->getTable() .' set qualification_id = 0 where qualification_id = '. (int)$this->getId();
			$this->db->Execute($query);

			$query = 'update '. $lf->getTable() .' set qualification_id = 0 where qualification_id = '. (int)$this->getId();
			$this->db->Execute($query);

			$query = 'update '. $lg->getTable() .' set qualification_id = 0 where qualification_id = '. (int)$this->getId();
			$this->db->Execute($query);

			$query = 'update '. $mf->getTable() .' set qualification_id = 0 where qualification_id = '. (int)$this->getId();
			$this->db->Execute($query);
			//Job employee criteria
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE   ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;

					switch( $variable ) {
						case 'group':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'name_metaphone':
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getCreatedBy(), FALSE, $permission_children_ids, $include_columns );

			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Qualification') .': '. $this->getName(), NULL, $this->getTable(), $this );
	}

}
?>
