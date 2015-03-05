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
 * @package Modules\Hierarchy
 */
class HierarchyControlFactory extends Factory {
	protected $table = 'hierarchy_control';
	protected $pk_sequence_name = 'hierarchy_control_id_seq'; //PK Sequence name

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$hotlf = TTnew( 'HierarchyObjectTypeListFactory' );
				$retval = $hotlf->getOptions('object_type');
				break;
			case 'short_object_type':
				$hotlf = TTnew( 'HierarchyObjectTypeListFactory' );
				$retval = $hotlf->getOptions('short_object_type');
				break;
			case 'columns':
				$retval = array(
										'-1010-name' => TTi18n::gettext('Name'),
										'-1020-description' => TTi18n::gettext('Description'),
										'-1030-superiors' => TTi18n::gettext('Superiors'),
										'-1030-subordinates' => TTi18n::gettext('Subordinates'),
										'-1050-object_type_display' => TTi18n::gettext('Objects'),

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
								'superiors',
								'subordinates',
								'object_type_display'
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
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
										'superiors' => 'TotalSuperiors',
										'subordinates' => 'TotalSubordinates',
										'object_type' => 'ObjectType',
										'object_type_display' => FALSE,
										'user' => 'User',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompany() {
		return (int)$this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Invalid Company')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					'company_id' => $this->getCompany(),
					'name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND name = ? AND deleted = 0';
		$hierarchy_control_id = $this->db->GetOne($query, $ph);
		Debug::Arr($hierarchy_control_id, 'Unique Hierarchy Control ID: '. $hierarchy_control_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $hierarchy_control_id === FALSE ) {
			return TRUE;
		} else {
			if ($hierarchy_control_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getName() {
		return $this->data['name'];
	}
	function setName($name) {
		$name = trim($name);

		if (	$this->Validator->isLength(	'name',
											$name,
											TTi18n::gettext('Name is invalid'),
											2, 250)
				AND	$this->Validator->isTrue(	'name',
												$this->isUniqueName($name),
												TTi18n::gettext('Name is already in use')
												)
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		return $this->data['description'];
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

	function getObjectTypeDisplay() {
		$object_type_ids = $this->getObjectType();
		$object_types = $this->getOptions('short_object_type');

		$retval = array();
		foreach( $object_type_ids as $object_type_id ) {
			$retval[] = Option::getByKey( $object_type_id, $object_types );
		}
		sort($retval); //Maintain consistent order.

		return implode(',', $retval );
	}

	function getObjectType() {
		$hotlf = TTnew( 'HierarchyObjectTypeListFactory' );
		$hotlf->getByHierarchyControlId( $this->getId() );

		foreach ($hotlf as $object_type) {
			$object_type_list[] = $object_type->getObjectType();
		}

		if ( isset($object_type_list) ) {
			return $object_type_list;
		}

		return FALSE;
	}

	function setObjectType($ids) {
		if ( is_array($ids) AND count($ids) > 0 ) {
			$tmp_ids = array();
			Debug::Arr($ids, 'IDs: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'HierarchyObjectTypeListFactory' );
				$lf_a->getByHierarchyControlId( $this->getId() );
				Debug::text('Existing Object Type Rows: '. $lf_a->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

				foreach ($lf_a as $obj) {
					//$id = $obj->getId();
					$id = $obj->getObjectType(); //Need to use object_types rather than row IDs.
					Debug::text('Hierarchy Object Type ID: '. $obj->getId() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: Object Type: '. $id .' ID: '. $obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting: Object Type: '. $id .' ID: '. $obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = TTnew( 'HierarchyObjectTypeFactory' );
					$f->setHierarchyControl( $this->getId() );
					$f->setObjectType( $id );

					if ($this->Validator->isTrue(		'object_type',
														$f->Validator->isValid(),
														TTi18n::gettext('Object type is already assigned to another hierarchy'))) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		$this->Validator->isTrue(		'object_type',
										FALSE,
										TTi18n::gettext('At least one object must be selected'));

		return FALSE;
	}

	function getUser() {
		$hulf = TTnew( 'HierarchyUserListFactory' );
		$hulf->getByHierarchyControlID( $this->getId() );
		foreach ($hulf as $obj) {
			$list[] = $obj->getUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setUser($ids) {
		if ( !is_array($ids) ) {
			$ids = array($ids);
		}

		Debug::text('Setting User IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$hulf = TTnew( 'HierarchyUserListFactory' );
				$hulf->getByHierarchyControlID( $this->getId() );

				foreach ($hulf as $obj) {
					$id = $obj->getUser();
					Debug::text('HierarchyControl ID: '. $obj->getHierarchyControl() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
			}

			//Insert new mappings.
			$ulf = TTnew( 'UserListFactory' );

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$huf = TTnew( 'HierarchyUserFactory' );
					$huf->setHierarchyControl( $this->getId() );
					$huf->setUser( $id );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						if ($this->Validator->isTrue(		'user',
															$huf->Validator->isValid(),
															TTi18n::gettext('Selected subordinate is invalid or already assigned to another hierarchy with the same objects ').' ('. $obj->getFullName() .')' )) {
							$huf->save();
						}
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	function getTotalSubordinates() {
		$hulf = TTnew( 'HierarchyUserListFactory' );
		$hulf->getByHierarchyControlID( $this->getId() );
		return $hulf->getRecordCount();
	}
	function getTotalSuperiors() {
		$hllf = TTnew('HierarchyLevelListFactory');
		$hllf->getByHierarchyControlId( $this->getID() );
		return $hllf->getRecordCount();
	}

	function Validate() {
		//When the user changes just the hierarchy objects, we need to loop through ALL users and confirm no conflicting hierarchies exist.
		//Only do this for existing hierarchies and ones that are already valid up to this point.
		if ( !$this->isNew() AND $this->Validator->isValid() == TRUE ) {

			$user_ids = $this->getUser();
			if ( is_array( $user_ids ) ) {
				$huf = TTNew('HierarchyUserFactory');
				$huf->setHierarchyControl( $this->getID() );

				foreach( $user_ids as $user_id ) {
					if ( $huf->isUniqueUser( $user_id ) == FALSE ) {
						$ulf = TTnew( 'UserListFactory' );
						$ulf->getById( $user_id );
						if ( $ulf->getRecordCount() > 0 ) {
							$obj = $ulf->getCurrent();
							TTi18n::gettext('Selected subordinate is invalid or already assigned to another hierarchy with the same objects ').' ('. $obj->getFullName() .')';
							$this->Validator->isTrue(		'user',
															$huf->isUniqueUser( $user_id, $this->getID() ),
															TTi18n::gettext('Selected subordinate is invalid or already assigned to another hierarchy with the same objects ').' ('. $obj->getFullName() .')' );
						} else {
							TTi18n::gettext('Selected subordinate is invalid or already assigned to another hierarchy with the same object. User ID: '. $user_id);
						}
					}
				}
			}
		}

		return TRUE;
	}

	function postSave() {
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
						//case 'superiors':
						//case 'subordinates':
						//	$data[$variable] = $this->getColumn($variable);
						//	break;
						case 'object_type_display':
							$data[$variable] = $this->getObjectTypeDisplay();
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Hierarchy'), NULL, $this->getTable(), $this );
	}
}
?>
