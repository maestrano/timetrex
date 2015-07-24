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
 * @package Core
 */
class PermissionControlFactory extends Factory {
	protected $table = 'permission_control';
	protected $pk_sequence_name = 'permission_control_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $tmp_previous_user_ids = array();

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'preset':
				$pf = TTnew( 'PermissionFactory' );
				$retval = $pf->getOptions('preset');
				break;
			case 'level':
				$retval = array(
										1 => 1,
										2 => 2,
										3 => 3,
										4 => 4,
										5 => 5,
										6 => 6,
										7 => 7,
										8 => 8,
										9 => 9,
										10 => 10,
										11 => 11,
										12 => 12,
										13 => 13,
										14 => 14,
										15 => 15,
										16 => 16,
										17 => 17,
										18 => 18,
										19 => 19,
										20 => 20,
										21 => 21,
										22 => 22,
										23 => 23,
										24 => 24,
										25 => 25,
							);
				break;
			case 'columns':
				$retval = array(
										'-1000-name' => TTi18n::gettext('Name'),
										'-1010-description' => TTi18n::gettext('Description'),
										'-1020-level' => TTi18n::gettext('Level'),
										'-1030-total_users' => TTi18n::gettext('Employees'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('name', 'description', 'level'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'description',
								'level',
								'total_users',
								'updated_by',
								'updated_date',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
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
										'level' => 'Level',
										'total_users' => FALSE,
										'user' => 'User',
										'permission' => 'Permission',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
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
					'name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND name = ? AND deleted=0';
		$permission_control_id = $this->db->GetOne($query, $ph);
		Debug::Arr($permission_control_id, 'Unique Permission Control ID: '. $permission_control_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $permission_control_id === FALSE ) {
			return TRUE;
		} else {
			if ($permission_control_id == $this->getId() ) {
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
											2, 50)
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
											1, 255) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}


	function getLevel() {
		if ( isset($this->data['level']) ) {
			return (int)$this->data['level'];
		}

		return FALSE;
	}
	function setLevel($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'level',
											$value,
											TTi18n::gettext('Incorrect Level'),
											$this->getOptions('level')) ) {

			$this->data['level'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		$pulf = TTnew( 'PermissionUserListFactory' );
		$pulf->getByPermissionControlId( $this->getId() );
		foreach ($pulf as $obj) {
			$list[] = $obj->getUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setUser($ids) {
		Debug::text('Setting User IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) AND count($ids) > 0 ) {
			global $current_user;

			//Remove any of the selected employees from other permission control objects first.
			//So there we can switch employees from one group to another in a single action.
			$pulf = TTnew( 'PermissionUserListFactory' );
			$pulf->getByCompanyIdAndUserIdAndNotPermissionControlId( $this->getCompany(), $ids, (int)$this->getId() );
			if ( $pulf->getRecordCount() > 0 ) {
				Debug::text('Found User IDs assigned to another Permission Group, unassigning them!', __FILE__, __LINE__, __METHOD__, 10);
				foreach( $pulf as $pu_obj ) {
					if ( !is_object($current_user) OR ( is_object($current_user) AND $current_user->getID() != $pu_obj->getUser() ) ) { //Not Acting on currently logged in user.
						$pu_obj->Delete();
					}
				}
			}
			unset($pulf, $pu_obj);

			$tmp_ids = array();

			$pf = TTnew( 'PermissionFactory' );
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$pulf = TTnew( 'PermissionUserListFactory' );
				$pulf->getByPermissionControlId( $this->getId() );
				foreach ($pulf as $obj) {
					$id = $obj->getUser();
					Debug::text('Permission Control ID: '. $obj->getPermissionControl() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						if ( is_object($current_user) AND $current_user->getID() == $id ) { //Not Acting on currently logged in user.
							$this->Validator->isTrue(		'user',
															FALSE,
															TTi18n::gettext('Unable to remove your own record from a permission group') );

						} else {
							Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
							$this->tmp_previous_user_ids[] = $id;
							$obj->Delete();
						}
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
					//Remove users from any other permission control object
					//first, otherwise there is a gap where an employee has
					//no permissions, this is especially bad for administrators
					//who are currently logged in.
					$puf = TTnew( 'PermissionUserFactory' );
					$puf->setPermissionControl( $this->getId() );
					$puf->setUser( $id );

					$obj = $ulf->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'user',
														$puf->Validator->isValid(),
														TTi18n::gettext('Selected employee is invalid, or already assigned to another permission group').' ('. $obj->getFullName() .')' )) {
						$puf->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getPermissionOptions() {
		$product_edition = $this->getCompanyObject()->getProductEdition();

		$retval = array();

		$pf = TTnew( 'PermissionFactory' );
		$sections = $pf->getOptions('section');
		$names = $pf->getOptions('name');
		if ( is_array($names) ) {
			foreach ($names as $section => $permission_arr) {
				if ( ( $pf->isIgnore( $section, NULL, $product_edition ) == FALSE ) ) {
					foreach($permission_arr as $name => $display_name) {
						if ( $pf->isIgnore( $section, $name, $product_edition ) == FALSE ) {
							if ( isset($sections[$section]) ) {
								$retval[$section][$name] = 0;
							}
						}
					}
				}
			}
		}

		return $retval;
	}

	function getPermission() {
		$plf = TTnew( 'PermissionListFactory' );
		$plf->getByCompanyIdAndPermissionControlId( $this->getCompany(), $this->getId() );
		if ( $plf->getRecordCount() > 0 ) {
			Debug::Text('Found Permissions: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach($plf as $p_obj) {
				$current_permissions[$p_obj->getSection()][$p_obj->getName()] = $p_obj->getValue();
			}

			return $current_permissions;
		}

		return FALSE;
	}
	function setPermission( $permission_arr, $old_permission_arr = array() ) {
		if ( $this->getId() == FALSE ) {
			return FALSE;
		}

		if ( $this->validate_only == TRUE ) {
			return TRUE;
		}

		global $profiler, $config_vars;
		$profiler->startTimer( 'setPermission' );

		//Since implementing the HTML5 Install Wizard, which uses the API, we have to check to see if the installer is enabled, and if so skip this next block of code.
		if ( defined('TIMETREX_API') AND TIMETREX_API == TRUE
				AND ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == 0 ) ) {
			//When creating a new permission group this causes it to be really slow as it creates a record for every permission that is set to DENY.
			
			//If we do the permission diff it messes up the HTML interface.
			if ( !is_array($old_permission_arr) OR ( is_array($old_permission_arr) AND count($old_permission_arr) == 0 ) ) {
				$old_permission_arr = $this->getPermission();
				//Debug::Text(' Old Permissions: '. count($old_permission_arr), __FILE__, __LINE__, __METHOD__, 10);
			}

			$permission_options = $this->getPermissionOptions();
			//Debug::Arr($permission_options, ' Permission Options: '. count($permission_options), __FILE__, __LINE__, __METHOD__, 10);

			$permission_arr = Misc::arrayMergeRecursiveDistinct( (array)$permission_options, (array)$permission_arr );
			//Debug::Text(' New Permissions: '. count($permission_arr), __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($permission_arr, ' Final Permissions: '. count($permission_arr), __FILE__, __LINE__, __METHOD__, 10);
		}
		$pf = TTnew( 'PermissionFactory' );

		//Don't Delete all previous permissions, do that in the Permission class.
		if ( isset($permission_arr) AND is_array($permission_arr) AND count($permission_arr) > 0 ) {
			foreach ($permission_arr as $section => $permissions) {
				//Debug::Text('	 Section: '. $section, __FILE__, __LINE__, __METHOD__, 10);

				foreach ($permissions as $name => $value) {
					//Debug::Text('		Name: '. $name .' - Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
					if (	(
							!isset($old_permission_arr[$section][$name])
								OR (isset($old_permission_arr[$section][$name]) AND $value != $old_permission_arr[$section][$name] )
							)
							AND $pf->isIgnore( $section, $name, $this->getCompanyObject()->getProductEdition() ) == FALSE
							) {

						if ( $value == 0 OR $value == 1 ) {
							Debug::Text('	 Modifying/Adding Section: '. $section .' Permission: '. $name .' - Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
							$tmp_pf = TTnew( 'PermissionFactory' );
							$tmp_pf->setCompany( $this->getCompanyObject()->getId() );
							$tmp_pf->setPermissionControl( $this->getId() );
							$tmp_pf->setSection( $section, TRUE ); //Disable error checking for performance optimization.
							$tmp_pf->setName( $name, TRUE ); //Disable error checking for performance optimization.
							$tmp_pf->setValue( (int)$value );
							if ( $tmp_pf->isValid() ) {
								$tmp_pf->save();
							}
						}
					} //else { //Debug::Text('	   Permission didnt change... Skipping', __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		$profiler->stopTimer( 'setPermission' );

		return TRUE;
	}

	//Quick way to touch the updated_date, updated_by when adding/removing employees from the UserFactory.
	function touchUpdatedByAndDate( $permission_control_id = NULL ) {
		global $current_user;

		if ( is_object($current_user) ) {
			$user_id = $current_user->getID();
		} else {
			return FALSE;
		}

		$ph = array(
					'updated_date' => TTDate::getTime(),
					'updated_by' => $user_id,
					'id' => ( $permission_control_id == '' ) ? (int)$this->getID() : (int)$permission_control_id,
					);

		$query = 'update '. $this->getTable() .' set updated_date = ?, updated_by = ? where id = ?';

		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

	}

	function preSave() {
		if ( $this->getLevel() == '' OR $this->getLevel() == 0 ) {
			$this->setLevel( 1 );
		}

		return TRUE;
	}

	function postSave() {
		$pf = TTnew( 'PermissionFactory' );

		$clear_cache_user_ids = array_merge( (array)$this->getUser(), (array)$this->tmp_previous_user_ids);
		foreach( $clear_cache_user_ids as $user_id ) {
			$pf->clearCache( $user_id, $this->getCompany() );
		}
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
						case 'total_users':
							$data[$variable] = $this->getColumn( $variable );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Permission Group').': '. $this->getName(), NULL, $this->getTable(), $this );
	}
}
?>
