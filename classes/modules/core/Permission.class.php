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
class Permission {
	function getPermissions( $user_id, $company_id ) {

		$plf = TTnew( 'PermissionListFactory' );

		$cache_id = 'permission_all'.$user_id.$company_id;
		$perm_arr = $plf->getCache($cache_id);
		//Debug::Arr($perm_arr, 'Cached Perm Arr:', __FILE__, __LINE__, __METHOD__, 9);
		if ( $perm_arr === FALSE ) {
			$plf->getAllPermissionsByCompanyIdAndUserId( $company_id, $user_id );
			if ( $plf->getRecordCount() > 0 ) {
				//Debug::Text('Found Permissions in DB!', __FILE__, __LINE__, __METHOD__, 9);
				$perm_arr['_system']['last_updated_date'] = NULL;
				foreach($plf as $p_obj) {
					//Debug::Text('Perm -  Section: '. $p_obj->getSection(), __FILE__, __LINE__, __METHOD__, 9);
					if ( $p_obj->getUpdatedDate() > $perm_arr['_system']['last_updated_date'] ) {
						$perm_arr['_system']['last_updated_date'] = $p_obj->getUpdatedDate();
					}
					$perm_arr[$p_obj->getSection()][$p_obj->getName()] = $p_obj->getValue();
				}
				//Last iteration, grab the permission level.
				$perm_arr['_system']['level'] = $p_obj->getColumn('level');

				$plf->saveCache($perm_arr, $cache_id);

				return $perm_arr;
			}
		}

		return $perm_arr;
	}

	function Check($section, $name, $user_id = NULL, $company_id = NULL) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		//Debug::Text('Permission Check - Section: '. $section .' Name: '. $name .' User ID: '. $user_id .' Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 9);
		$permission_arr = $this->getPermissions( $user_id, $company_id );

		if ( isset($permission_arr[$section][$name]) ) {
			//Debug::Text('Permission is Set!', __FILE__, __LINE__, __METHOD__, 9);
			$result = $permission_arr[$section][$name];
		} else {
			//Debug::Text('Permission is NOT Set!', __FILE__, __LINE__, __METHOD__, 9);
			$result = FALSE;
		}

		return $result;
	}

	function getLevel( $user_id = NULL, $company_id = NULL ) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		$permission_arr = $this->getPermissions( $user_id, $company_id );

		if ( isset($permission_arr['_system']['level']) ) {
			return $permission_arr['_system']['level'];
		}

		return 1; //Lowest level.
	}

	function Redirect($result) {
		if ( $result !== TRUE ) {
			Redirect::Page( URLBuilder::getURL( NULL, Environment::getBaseURL().'/permission/PermissionDenied.php') );
		}

		return TRUE;
	}

	function PermissionDenied( $result = FALSE, $description = 'Permission Denied' ) {
		if ( $result !== TRUE ) {
			Debug::Text('Permission Denied! Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
			$af = TTnew('APIPermission');
			return $af->returnHandler( FALSE, 'PERMISSION', $description );
		}

		return TRUE;
	}

	function Query($section, $name, $user_id = NULL, $company_id = NULL) {
		Debug::Text('Permission Query!', __FILE__, __LINE__, __METHOD__, 9);
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		$plf = TTnew( 'PermissionListFactory' );

		return $plf->getBySectionAndNameAndUserIdAndCompanyId($section, $name, $user_id, $company_id)->getCurrent();
	}

	//Checks if the row_object_id is created by the current user
	function isOwner( $object_created_by, $object_assigned_to = NULL, $current_user_id = NULL ) {
		if ( $current_user_id == NULL OR $current_user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$current_user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		//Allow object_assigned_to to be an array, then make sure *all* records in the array match.
		if ( ($object_created_by != '' AND $object_created_by == $current_user_id)
				OR ($object_assigned_to != '' AND $object_assigned_to == $current_user_id)
				OR ( is_array($object_assigned_to) AND count( array_unique($object_assigned_to) ) == 1 AND $object_assigned_to[0] == $current_user_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	//Checks if the row_object_id is in the src_object_list array,
	function isChild( $row_object_id, $src_object_list, $current_user_id = NULL ) {
		if ( !is_numeric($row_object_id) AND !is_array($row_object_id) ) {
			return FALSE;
		}

		if ( $current_user_id == NULL OR $current_user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$current_user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}
		//Can never be a child of themselves, so remove the current user from the child list.
		if ( $row_object_id == $current_user_id ) {
			return FALSE;
		}

		if ( !is_array($src_object_list) AND $src_object_list != '' ) {
			$src_object_list = array( $src_object_list );
		}

		//If row_object_id is an array (ie: a subordinate list), then they must *all* match the src_object_list for this to be valid.
		//This is used by recurring_schedule for supervisor (subordinates only)
		if ( is_array( $row_object_id ) AND is_array($src_object_list) ) {
			foreach( $row_object_id as $tmp_row_object_id ) {
				if ( !in_array( $tmp_row_object_id, $src_object_list ) ) {
					return FALSE;
				}
			}
			//All items match, return TRUE.
			return TRUE;
		} elseif ( is_array($src_object_list) AND in_array( $row_object_id, $src_object_list ) ) {
			return TRUE;
		}

		return FALSE;
	}

	static function getPermissionIsChildIsOwnerSQL( $id, $inner_column ) {
		$query = '
				CASE WHEN phc.is_child is NOT NULL THEN 1 ELSE 0 END as is_child,
				CASE WHEN '. $inner_column .' = '. (int)$id .' THEN 1 ELSE 0 END as is_owner,
				';
		return $query;
	}
	static function getPermissionHierarchySQL( $company_id, $user_id, $outer_column ) {
		$hlf = new HierarchyLevelFactory();
		$huf = new HierarchyUserFactory();
		$hotf = new HierarchyObjectTypeFactory();
		$hcf = new HierarchyControlFactory();

		$query = '
						LEFT JOIN (
							select phc_huf.user_id as user_id, 1 as is_child
							from '. $huf->getTable() .' as phc_huf
							LEFT JOIN '. $hlf->getTable() .' as phc_hlf ON phc_huf.hierarchy_control_id = phc_hlf.hierarchy_control_id
							LEFT JOIN '. $hotf->getTable() .' as phc_hotf ON phc_huf.hierarchy_control_id = phc_hotf.hierarchy_control_id
							LEFT JOIN '. $hcf->getTable() .' as phc_hcf ON phc_huf.hierarchy_control_id = phc_hcf.id
							WHERE
								phc_hlf.user_id = '. (int)$user_id .'
								AND phc_hcf.company_id = '. (int)$company_id .'
								AND phc_hotf.object_type_id = 100
								AND phc_huf.user_id != phc_hlf.user_id
								AND ( phc_hlf.deleted = 0 AND phc_hcf.deleted = 0 )
						) as phc ON '. $outer_column .' = phc.user_id
					';
					
		return $query;
	}
	static function getPermissionIsChildIsOwnerFilterSQL( $filter_data, $outer_column_name ) {
		if ( isset($filter_data['permission_is_own']) AND $filter_data['permission_is_own'] == TRUE AND isset($filter_data['permission_current_user_id']) ) {
			$query[] = $outer_column_name .' = '. (int)$filter_data['permission_current_user_id'];
		}
		if ( isset($filter_data['permission_is_child']) AND $filter_data['permission_is_child'] == TRUE ) {
			$query[] = 'phc.is_child = 1';
		}

		if ( isset($query) AND is_array($query) ) {
			return ' AND ( '. implode(' OR ', $query ) .') ';
		}

		return FALSE;
	}

	function getPermissionFilterData($section, $name, $user_id = NULL, $company_id = NULL) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		/*
			permission_children_ids
			permission_current_user_id
			permission_is_child = 1
			permission_is_own = 1
		*/

		$retarr['permission_current_user_id'] = $user_id;
		if ( $this->Check( $section, $name ) == FALSE ) {
			if ( $this->Check( $section, $name.'_child') ) {
				$retarr['permission_is_child'] = TRUE;
			}
			if ( $this->Check( $section, $name.'_own') ) {
				$retarr['permission_is_own'] = TRUE; //Return user_id so we can match that specifically
			}
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return array();
	}

	function getPermissionHierarchyChildren( $company_id, $user_id ) {
		$hlf = TTnew( 'HierarchyListFactory' );
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $company_id, $user_id, 100 );
		//Debug::Arr($permission_children_ids, 'Permission Child IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		return $permission_children_ids;
	}

	function getPermissionChildren($section, $name, $user_id = NULL, $company_id = NULL) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		$permission_children_ids = $this->getPermissionHierarchyChildren( $company_id, $user_id );

		//If 'view' is FALSE, we are not returning children to check for edit/delete permissions, so those are denied.
		//This can be tested with 'users', 'view', 'users', 'edit_subordinate' allowed. They won't be able to edit subordinates.
		if ( $this->Check( $section, $name ) == FALSE ) {
			if ( $this->Check( $section, $name.'_child') ) {
				$retarr = $permission_children_ids;
			}
			//Why are we including the current user in the "child" list, if they can view their own records.
			//This essentially makes edit_child permissions include edit_own as well. Which for the editing punches
			//there may be cases where they can edit subordinates but not themselves.
			//Because in the SQL query, we restrict to just the child_ids.
			//Its different view view_own/view_child as compared to edit_own/edit_child.
			//	So we need to include the current user if they can only view their own, but exclude the current user when doing is_child checks above.
			//Another way we could handle this is to return an array of children and owner separately, then in SQL queries combine them together.
			if ( $this->Check( $section, $name.'_own') ) {
				$retarr[] = $user_id;
			}
		} else {
			//This must return NULL, otherwise the SQL query will restrict returned records just to the children.
			$retarr = NULL;
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return NULL;
	}

	function getLastUpdatedDate( $user_id = NULL, $company_id = NULL ) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( isset($current_user) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		//Debug::Text('Permission Check - Section: '. $section .' Name: '. $name .' User ID: '. $user_id .' Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 9);
		$permission_arr = $this->getPermissions( $user_id, $company_id );

		if ( isset($permission_arr['_system']['last_updated_date']) ) {
			return $permission_arr['_system']['last_updated_date'];
		}

		return FALSE;
	}
}
?>
