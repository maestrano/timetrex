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
 * $Revision: 2095 $
 * $Id: HierarchyObjectTypeFactory.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Modules\Hierarchy
 */
class HierarchyLevelFactory extends Factory {
	protected $table = 'hierarchy_level';
	protected $pk_sequence_name = 'hierarchy_level_id_seq'; //PK Sequence name

	var $hierarchy_control_obj = NULL;
	var $user_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-level' => TTi18n::gettext('Level'),
										'-1020-user' => TTi18n::gettext('Superior'),

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
								'level',
								'user',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array();
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array();
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'hierarchy_control_id' => 'HierarchyControl',
										'level' => 'Level',
										'user_id' => 'User',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();
				return $this->user_obj;
			}

			return FALSE;
		}
	}

	function getHierarchyControlObject() {
		if ( is_object($this->hierarchy_control_obj) ) {
			return $this->hierarchy_control_obj;
		} else {
			$hclf = TTnew( 'HierarchyControlListFactory' );
			$this->hierarchy_control_obj = $hclf->getById( $this->getHierarchyControl() )->getCurrent();

			return $this->hierarchy_control_obj;
		}
	}

	function getHierarchyControl() {
		if ( isset($this->data['hierarchy_control_id']) ) {
			return $this->data['hierarchy_control_id'];
		}

		return FALSE;
	}
	function setHierarchyControl($id) {
		$id = trim($id);

		$hclf = TTnew( 'HierarchyControlListFactory' );

		//This is a sub-class, need to support setting HierachyControlID before its created.
		if ( $id != 0
				OR
				$this->Validator->isResultSetWithRows(	'hierarchy_control_id',
															$hclf->getByID($id),
															TTi18n::gettext('Invalid Hierarchy Control')
															)
				) {
			$this->data['hierarchy_control_id'] = $id;

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
	function setLevel($int) {
		$int = trim($int);

		if ( $int <= 0 ) {
			$int = 1; //1 is the lowest level
		}

		if 	(	$int > 0
				AND
				$this->Validator->isNumeric(		'level',
													$int,
													TTi18n::gettext('Level is invalid')) ) {
			$this->data['level'] = $int;

			return TRUE;
		}

		return FALSE;
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
		$hllf = TTnew( 'HierarchyLevelListFactory' );
		$hulf = TTnew( 'HierarchyUserListFactory' );

		if ( $this->getHierarchyControl() == FALSE ) {
			return FALSE;
		}

		//Get user object so we can get the users full name to display as an error message.
		$ulf->getById( $id );

		//Shouldn't allow the same superior to be assigned at multiple levels. Can't check that properly here though, must be done at the Hierarchy Control level?


		//Don't allow a level to be set without a superior assigned to it.
		//$id == 0
		if (
				(
				$this->Validator->isResultSetWithRows(	'user',
														$ulf->getByID($id),
														TTi18n::gettext('No superior defined for level').' ('. (int)$this->getLevel().')'
														)
				AND
					/*
					//Allow superiors to be assigned as subordinates in the same hierarchy to make it easier to administer hierarchies
					//that have superiors sharing responsibility.
					//For example Super1 and Super2 look after 10 subordinates as well as each other. This would require 3 hierarchies normally,
					//but if we allow Super1 and Super2 to be subordinates in the same hierarchy, it can be done with a single hierarchy.
					//The key with this though is to have Permission->getPermissionChildren() *not* return the current user, even if they are a subordinates,
					//as that could cause a conflict with view_own and view_child permissions (as a child would imply view_own)
					(
					$ulf->getRecordCount() > 0
					AND
					$this->Validator->isNotResultSetWithRows(	'user',
																$hulf->getByHierarchyControlAndUserId( $this->getHierarchyControl(), $id ),
																$ulf->getCurrent()->getFullName() .' '. TTi18n::gettext('is assigned as both a superior and subordinate')
																)
					)
					AND
					*/
					(
						$this->Validator->hasError('user') == FALSE
						AND
						$this->Validator->isNotResultSetWithRows(	'user',
																$hllf->getByHierarchyControlIdAndUserIdAndExcludeId( $this->getHierarchyControl(), $id, $this->getID() ),
																$ulf->getCurrent()->getFullName() .' '. TTi18n::gettext('is already assigned as a superior')
																)

					)
				)
				) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	static function RemoveDuplicateHierarchyLevels( $hierarchy_level_data ) {
		if ( !is_array($hierarchy_level_data) ) {
			return FALSE;
		}
		Debug::Arr($hierarchy_level_data, ' aHierarchy Users:', __FILE__, __LINE__, __METHOD__,10);


		foreach( $hierarchy_level_data as $hierarchy_level_id => $hierarchy_level ) {
			$tmp_hierarchy_users[$hierarchy_level_id] = $hierarchy_level['user_id'];
		}

		//Remove duplicate superiors.
		$unique_hierarchy_users = array_unique( $tmp_hierarchy_users );
		if ( count($tmp_hierarchy_users) != count( $unique_hierarchy_users ) ) {
			//Duplicate superiors found.
			$diff_hierarchy_users = array_diff_assoc( $tmp_hierarchy_users, $unique_hierarchy_users );
			Debug::Arr($diff_hierarchy_users, ' Diff Hierarchy Users:', __FILE__, __LINE__, __METHOD__,10);
			if ( is_array($diff_hierarchy_users) ) {
				foreach( $diff_hierarchy_users as $diff_hierarchy_key => $diff_hierarchy_value ) {
					unset($hierarchy_level_data[$diff_hierarchy_key]);
				}
			}
		}
		unset($tmp_hierarchy_users, $unique_hierarchy_users, $diff_hierarchy_users, $diff_hierarchy_key, $diff_hierarchy_value);

		Debug::Arr($hierarchy_level_data, ' bHierarchy Users:', __FILE__, __LINE__, __METHOD__,10);

		return $hierarchy_level_data;
	}

	//Remaps raw hierarchy_levels so they always start from 1, and have no gaps in them.
	//Also remove any duplicate superiors from the hierarchy.
	static function ReMapHierarchyLevels( $hierarchy_level_data ) {
		if ( !is_array($hierarchy_level_data) ) {
			return FALSE;
		}

		$remapped_hierarchy_levels = FALSE;

		foreach( $hierarchy_level_data as $hierarchy_level_id => $hierarchy_level ) {
			$tmp_hierarchy_levels[] = $hierarchy_level['level'];
		}
		sort($tmp_hierarchy_levels);

		$level = 0;
		$prev_level = FALSE;
		foreach( $tmp_hierarchy_levels as $hierarchy_level ) {
			if ( $prev_level != $hierarchy_level ) {
				$level++;
			}

			$remapped_hierarchy_levels[$hierarchy_level] = $level;

			$prev_level = $hierarchy_level;
		}

		return $remapped_hierarchy_levels;
	}

	//Takes a hierarchy level map array and converts it to a SQL where clause.
	static function convertHierarchyLevelMapToSQL( $hierarchy_level_map, $object_table = 'a.', $hierarchy_user_table = 'z.', $type_id_column = NULL ) {
		/*
				( z.hierarchy_control_id = 469 AND a.authorization_level = 1 )
					OR ( z.hierarchy_control_id = 471 AND a.authorization_level = 2 )
					OR ( z.hierarchy_control_id = 470 AND a.authorization_level = 3 )

				OR

				( z.hierarchy_control_id = 469 AND a.authorization_level = 1 AND a.type_id in (10,20,30) )
					OR ( z.hierarchy_control_id = 471 AND a.authorization_level = 2 AND a.type_id in (10) )
					OR ( z.hierarchy_control_id = 470 AND a.authorization_level = 3 AND a.type_id in (100) )
		*/

		if ( is_array($hierarchy_level_map) ) {
			$rf = new RequestFactory();
			$clause_arr = array();
			foreach( $hierarchy_level_map as $hierarchy_data ) {
				if ( $hierarchy_data['last_level'] == TRUE ) {
					$operator = ' >= ';
				} else {
					$operator = ' = ';
				}

				$object_type_clause = NULL;
				if ( $type_id_column != '' AND isset($hierarchy_data['object_type_id']) AND count($hierarchy_data['object_type_id']) > 0 ) {
					$hierarchy_data['object_type_id'] = $rf->getTypeIdFromHierarchyTypeId( $hierarchy_data['object_type_id'] );
					$object_type_clause = ' AND '. $type_id_column .' in ('. implode(',', $hierarchy_data['object_type_id'] ) .')';
				}
				$clause_arr[] = '( '. $hierarchy_user_table.'hierarchy_control_id = '. (int)$hierarchy_data['hierarchy_control_id'] .' AND '.$object_table.'authorization_level '. $operator .' '. (int)$hierarchy_data['level'] . $object_type_clause .' )';
			}
			$retval = implode(' OR ', $clause_arr );
			//Debug::Text(' Hierarchy Filter SQL: '. $retval, __FILE__, __LINE__, __METHOD__,10);
			return $retval;
		}

		return FALSE;
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
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getHierarchyControl(), $log_action, TTi18n::getText('Superior') .': '. $u_obj->getFullName() .' '. TTi18n::getText('Level').': '. $this->getLevel(), NULL, $this->getTable(), $this );
		}

		return FALSE;
	}

}
?>
