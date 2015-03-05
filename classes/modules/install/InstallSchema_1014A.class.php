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
 * @package Modules\Install
 */
class InstallSchema_1014A extends InstallSchema_Base {

	protected $permission_groups = NULL;
	protected $permission_group_users = NULL;

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		/*
			Permission System Upgrade.
				- Use direct query to get current permission data and store in memory for postInstall.
		*/
		$query = 'select company_id, user_id, section, name, value from permission where deleted = 0 order by company_id, user_id, section, name';
		$rs = $this->getDatabaseConnection()->Execute( $query );
		foreach( $rs as $row ) {
			$user_permission_data[$row['company_id']][$row['user_id']][$row['section']][$row['name']] = $row['value'];

			//If employee has Punch In/Out permission enabled, add individual punch permission too.
			if ( $row['section'] == 'punch'
					AND $row['name'] == 'punch_in_out'
					AND $row['value'] == 1 ) {
				$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_transfer'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_branch'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_department'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_note'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_other_id1'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_other_id2'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_other_id3'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_other_id4'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_other_id5'] = 1;

				if ( isset($user_permission_data[$row['company_id']][$row['user_id']]['job']['enabled'])
						AND $user_permission_data[$row['company_id']][$row['user_id']]['job']['enabled'] == 1) {
					$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_job'] = 1;
					$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_job_item'] = 1;
					$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_quantity'] = 1;
					$user_permission_data[$row['company_id']][$row['user_id']]['punch']['edit_bad_quantity'] = 1;
				}
			}

			$user_permission_data[$row['company_id']][$row['user_id']]['absence']['enabled'] = 1;
			$user_permission_data[$row['company_id']][$row['user_id']]['absence']['view_own'] = 1;

			//We added the "Absence" permission section, so we need to copy punch permissions to this.
			if ( $row['section'] == 'punch' AND $row['name'] == 'view_child' AND $row['value'] == 1 ) {
				$user_permission_data[$row['company_id']][$row['user_id']]['absence']['view_child'] = 1;
			}
			if ( $row['section'] == 'punch' AND $row['name'] == 'edit_child' AND $row['value'] == 1 ) {
				$user_permission_data[$row['company_id']][$row['user_id']]['absence']['edit_child'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['absence']['add'] = 1;
			}
			if ( $row['section'] == 'punch' AND $row['name'] == 'delete_child' AND $row['value'] == 1 ) {
				$user_permission_data[$row['company_id']][$row['user_id']]['absence']['delete_child'] = 1;
			}

			if ( $row['section'] == 'punch' AND $row['name'] == 'view' AND $row['value'] == 1 ) {
				$user_permission_data[$row['company_id']][$row['user_id']]['absence']['view'] = 1;
			}
			if ( $row['section'] == 'punch' AND $row['name'] == 'edit' AND $row['value'] == 1 ) {
				$user_permission_data[$row['company_id']][$row['user_id']]['absence']['edit'] = 1;
				$user_permission_data[$row['company_id']][$row['user_id']]['absence']['add'] = 1;
			}
			if ( $row['section'] == 'punch' AND $row['name'] == 'delete' AND $row['value'] == 1 ) {
				$user_permission_data[$row['company_id']][$row['user_id']]['absence']['delete'] = 1;
			}

		}

		//Group Permissions together
		if ( isset( $user_permission_data ) ) {
			foreach( $user_permission_data as $company_id => $user_ids ) {
				//Get default employee permissions to start from.
				if ( isset($user_permission_data[$company_id]['-1']) ) {
					$this->permission_groups[$company_id]['default'] = $user_permission_data[$company_id]['-1'];
				} else {
					$this->permission_groups[$company_id]['default'] = array();
				}
				unset($user_permission_data[$company_id]['-1']);

				$x = 1;
				foreach( $user_ids as $user_id => $permission_user_data ) {

					$permission_no_differences_found = FALSE;

					foreach( $this->permission_groups[$company_id] as $group_name => $permission_group_data ) {
						Debug::text('Company ID: '. $company_id .' Checking Permission Differences Between User ID: '. $user_id .' AND Group: '. $group_name, __FILE__, __LINE__, __METHOD__, 10);

						//Need to diff the arrays both directions, because the diff function only checks in one direction on its own.
						$forward_permission_diff_arr = Misc::arrayDiffAssocRecursive($permission_user_data, $permission_group_data);
						$reverse_permission_diff_arr = Misc::arrayDiffAssocRecursive($permission_group_data, $permission_user_data );
						Debug::text('Permission User Data Count: '. count($permission_user_data, COUNT_RECURSIVE) .' Permission Group Data Count: '. count($permission_group_data, COUNT_RECURSIVE), __FILE__, __LINE__, __METHOD__, 10);
						if ( $forward_permission_diff_arr == FALSE AND $reverse_permission_diff_arr == FALSE ) {
							Debug::text('No Differences Found in Permissions! ', __FILE__, __LINE__, __METHOD__, 10);
							$permission_no_differences_found = TRUE;
							if ( $user_id != -1 ) {
								$this->permission_group_users[$company_id][$group_name][] = $user_id;
							}
							break;
						} else {
							Debug::text('Differences Found in Permissions! ', __FILE__, __LINE__, __METHOD__, 10);
							//Debug::Arr($forward_permission_diff_arr, 'Forward Permission Differences:', __FILE__, __LINE__, __METHOD__, 10);
							//Debug::Arr($reverse_permission_diff_arr, 'Reverse Permission Differences:', __FILE__, __LINE__, __METHOD__, 10);
						}
					}
					unset($forward_permission_diff_arr, $reverse_permission_diff_arr);

					if ( $permission_no_differences_found == FALSE ) {
						Debug::text('Creating New Permission Group...: '. $x, __FILE__, __LINE__, __METHOD__, 10);

						$pf = TTnew( 'PermissionFactory' );
						$preset_arr = array(10, 18, 20, 30, 40);
						foreach( $preset_arr as $preset ) {
							$tmp_preset_permissions = $pf->getPresetPermissions( $preset, array() );
							$preset_permission_diff_arr = Misc::arrayDiffAssocRecursive($permission_user_data, $tmp_preset_permissions);

							$preset_permission_diff_count = count($preset_permission_diff_arr, COUNT_RECURSIVE);
							Debug::text('Preset Permission Diff Count...: '. $preset_permission_diff_count .' Preset ID: '. $preset, __FILE__, __LINE__, __METHOD__, 10);
							$preset_match[$preset] = $preset_permission_diff_count;
						}
						unset($preset_arr, $tmp_preset_permissions);

						krsort($preset_match);
						//Flip the array so if there are more then one preset with the same match_count, we use the smallest preset value.
						$preset_match = array_flip($preset_match);
						//Flip the array back so the key is the match_preset again.
						$preset_match = array_flip($preset_match);
						foreach( $preset_match as $best_match_preset => $match_value ) {
							break;
						}
						//Debug::Arr($preset_match, 'zPreset Match Array: ', __FILE__, __LINE__, __METHOD__, 10);

						//Create new group name, based on closest preset match
						$preset_name_options = $pf->getOptions('preset');
						if ( isset($preset_name_options[$best_match_preset]) ) {
							$group_name = $preset_name_options[$best_match_preset] .' ('.$match_value.') #'. $x;
						} else {
							$group_name = 'Group #'. $x;
						}
						Debug::text('Group Name: '. $group_name, __FILE__, __LINE__, __METHOD__, 10);
						$this->permission_groups[$company_id][$group_name] = $permission_user_data;

						if ( $user_id != -1 ) {
							$this->permission_group_users[$company_id][$group_name][] = $user_id;
						}
						unset($pf, $best_match_preset, $match_value);

						$x++;
					}
				}
				ksort($this->permission_group_users[$company_id]);
				ksort($this->permission_groups[$company_id]);
				unset($permission_user_data, $permission_group_data, $group_name, $company_id, $user_id);
			}
			unset($user_permission_data);
		}

		return TRUE;
	}

	function postInstall() {
		global $cache;

		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		Debug::text('l: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		/*
			Take permission groups we put into memory from preInstall and create them now,
			after schema has been updated.
		*/
		if ( isset($this->permission_groups) AND is_array($this->permission_groups) ) {
			//Create permission groups and assign proper employees to each.
			//Debug::Arr($this->permission_groups, 'All Permission Groups: ', __FILE__, __LINE__, __METHOD__, 9);
			foreach( $this->permission_groups as $company_id => $permission_group_data ) {
				//Get all active users for this company, so we can assign them
				//to the default permission group.
				$ulf = TTnew( 'UserListFactory' );
				$ulf->getByCompanyId( $company_id );
				$all_user_ids = array_keys( (array)$ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
				$assigned_user_ids = array();
				foreach( $permission_group_data as $group_name => $permission_data ) {
					Debug::text('zGroup Name: '. $group_name, __FILE__, __LINE__, __METHOD__, 10);

					$pcf = TTnew( 'PermissionControlFactory' );
					$pcf->StartTransaction();
					$pcf->setCompany( $company_id );
					$pcf->setName( ucfirst($group_name) );
					$pcf->setDescription( 'Automatically Created By Installer' );

					if ( $pcf->isValid() ) {
						$pcf_id = $pcf->Save(FALSE);

						if ( strtolower($group_name) == 'default' ) {
							//Assign all unassigned users to this permission group.
							$tmp_user_ids = array_merge( (array)$this->permission_group_users[$company_id][$group_name], array_diff($all_user_ids, $assigned_user_ids) );
							//Debug::Arr($all_user_ids, 'Default Group All User IDs:', __FILE__, __LINE__, __METHOD__, 10);
							//Debug::Arr($assigned_user_ids, 'Default Group All User IDs:', __FILE__, __LINE__, __METHOD__, 10);
							//Debug::Arr($tmp_user_ids, 'Default Group User IDs:', __FILE__, __LINE__, __METHOD__, 10);
							$pcf->setUser( $tmp_user_ids );
							unset( $tmp_user_ids);
						} else {
							if ( isset($this->permission_group_users[$company_id][$group_name]) AND is_array($this->permission_group_users[$company_id][$group_name]) ) {
								$pcf->setUser( $this->permission_group_users[$company_id][$group_name] );
								$assigned_user_ids = array_merge( $assigned_user_ids, $this->permission_group_users[$company_id][$group_name] );
							}
						}

						if ( is_array($permission_data) ) {
							$pcf->setPermission( $permission_data );
						}
					}
					//$pcf->FailTransaction();
					$pcf->CommitTransaction();
				}
				unset($all_user_ids, $assigned_user_ids);
			}
		}

		return TRUE;
	}
}
?>
