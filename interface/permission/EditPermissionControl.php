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
 * $Revision: 4104 $
 * $Id: EditPermissionControl.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('permission','enabled')
		OR !( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Permission Group')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data',
												'group_id',
												'old_data',
												'src_user_id',
												) ) );

$pcf = TTnew( 'PermissionControlFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
	case 'apply_preset':
		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$pf = TTnew( 'PermissionFactory' );
		$pcf->StartTransaction();

		$pcf->setId( $data['id'] );
		$pcf->setCompany( $current_company->getId() );

		$pcf->setName($data['name']);
		$pcf->setDescription($data['description']);
		$pcf->setLevel($data['level']);

		//Check to make sure the currently logged in user is NEVER in the unassigned
		//user list. This prevents an administrator from accidently un-assigning themselves
		//from a group and losing all permissions.
		if ( in_array( $current_user->getId(), (array)$src_user_id) ) {
			//Check to see if current user is assigned to another permission group.
			$current_user_failed = FALSE;

			$pclf = TTnew( 'PermissionControlListFactory' );
			$pclf->getByCompanyIdAndUserId( $current_company->getId(), $current_user->getId() );
			if ( $pclf->getRecordCount() == 0 ) {
				$current_user_failed = TRUE;
			} else {
				foreach( $pclf as $pc_obj ) {
					if ( $pc_obj->getId() == $data['id'] ) {
						$current_user_failed = TRUE;
					}
				}

			}
			unset($pclf, $pc_obj);

			if ( $current_user_failed == TRUE ) {
				$pcf->Validator->isTrue( 'user',
										FALSE,
										TTi18n::gettext('You can not unassign yourself from a permission group, assign yourself to a new group instead') );
			}
		}

		if ( $pcf->isValid() ) {
			$pcf_id = $pcf->Save(FALSE);

			Debug::Text('aPermission Control ID: '. $pcf_id , __FILE__, __LINE__, __METHOD__,10);

			if ( $pcf_id === TRUE ) {
				$pcf_id = $data['id'];
			}

			if ( DEMO_MODE == FALSE ) {
				if ( isset($data['user_ids']) ){
					$pcf->setUser( $data['user_ids'] );
				} else {
					$pcf->setUser( array() );
				}

				//Don't Delete all previous permissions, do that in the Permission class.
				if ( isset($data['permissions']) AND is_array($data['permissions']) AND count($data['permissions']) > 0 ) {
					$pcf->setPermission( $data['permissions'], $old_data['permissions']);
				}
			}

			if ( $pcf->isValid() ) {
				$pcf->Save(TRUE);

				if ( DEMO_MODE == FALSE ) {
					if ( $action == 'apply_preset' ) {
						Debug::Text('Attempting to apply preset...', __FILE__, __LINE__, __METHOD__,10);

						if ( !isset($data['preset_flags']) ) {
							$data['preset_flags'] = array();
						}

						if ( $pcf_id != '' AND isset($data['preset']) ) {
							Debug::Text('Applying Preset!', __FILE__, __LINE__, __METHOD__,10);
							$pf = TTnew( 'PermissionFactory' );
							$pf->applyPreset($pcf_id, $data['preset'], $data['preset_flags']);
						}
					}
				}
				//$pcf->FailTransaction();
				$pcf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( array(), 'PermissionControlList.php') );

				break;
			}
		}

		$pcf->FailTransaction();
	default:
		$pf = TTnew( 'PermissionFactory' );
		$plf = TTnew( 'PermissionListFactory' );

		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$pclf = TTnew( 'PermissionControlListFactory' );

			$pclf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($pclf as $pc_obj) {
				$data = array(
									'id' => $pc_obj->getId(),
									'name' => $pc_obj->getName(),
									'description' => $pc_obj->getDescription(),
									'level' => $pc_obj->getLevel(),
									'user_ids' => $pc_obj->getUser(),
									'created_date' => $pc_obj->getCreatedDate(),
									'created_by' => $pc_obj->getCreatedBy(),
									'updated_date' => $pc_obj->getUpdatedDate(),
									'updated_by' => $pc_obj->getUpdatedBy(),
									'deleted_date' => $pc_obj->getDeletedDate(),
									'deleted_by' => $pc_obj->getDeletedBy()
								);
			}

			//$plf->getAllPermissionsByCompanyIdAndPermissionControlId($company_id, $id);
			$plf->getByCompanyIdAndPermissionControlId( $current_company->getId(), $id );
			if ( $plf->getRecordCount() > 0 ) {
				Debug::Text('Found Current Permissions!', __FILE__, __LINE__, __METHOD__,10);
				foreach($plf as $p_obj) {
					foreach($plf as $p_obj) {
						$current_permissions[$p_obj->getSection()][$p_obj->getName()] = $p_obj;
					}
				}
			}
			//print_r($current_permissions);

		}

		$section_groups = Misc::prependArray( array( -1 => TTi18n::gettext('-- None --')), $pf->getOptions('section_group') );
		$section_group_map = $pf->getOptions('section_group_map');
		$sections = $pf->getOptions('section');
		$names = $pf->getOptions('name');

		//Trim out ignored sections
		foreach( $section_groups as $section_group_key => $section_group_value ) {
			if ( $pf->isIgnore( $section_group_key, NULL, $current_company->getProductEdition() ) == TRUE ) {
				unset($section_groups[$section_group_key]);
			}
		}
		unset($section_group_key, $section_group_value);

		if ( !isset($group_id) OR !isset($section_groups[$group_id]) ) {
			$group_id = 0; //None
			//$group_id = 'all'; //None
		}
		Debug::Text('Group ID: '. $group_id, __FILE__, __LINE__, __METHOD__,10);

		foreach ($names as $section => $permission_arr) {
			if (
					( $pf->isIgnore( $section, NULL, $current_company->getProductEdition() ) == FALSE )
					AND
					( ( $group_id == 'all' AND $group_id !== 0 ) OR ( isset($section_group_map[$group_id]) AND in_array($section,$section_group_map[$group_id]) ) )
					) {

				foreach($permission_arr as $name => $display_name) {

						if ( isset($current_permissions[$section][$name]) ) {
							$permission_result_obj = $current_permissions[$section][$name];

							Debug::Text(' Permission Check Section: '. $section .' - Name: '. $name .' - Get Permission Control: '. $permission_result_obj->getPermissionControl(), __FILE__, __LINE__, __METHOD__,10);
							$permission_result = $permission_result_obj->getValue();

							$permissions[] = array('name' => $name, 'display_name' => $display_name, 'result' => $permission_result);
						} elseif ( $pf->isIgnore( $section, $name, $current_company->getProductEdition() ) == FALSE ) {
							$permissions[] = array('name' => $name, 'display_name' => $display_name, 'result' => NULL );
						}
				}

				//If you get a index error just below here, you forgot to
				//enter the section name in PayStubFactory.class.php
				$permission_data[] = array(
											'name' => $section,
											'display_name' => $sections[$section],
											'permissions' => $permissions
											);

				unset($permissions);
			}

		}

		//var_dump($permission_data);
		$preset_options = Misc::prependArray( array( -1 => TTi18n::gettext('--')), $pf->getOptions('preset') );


		$data['level_options'] = $pcf->getOptions('level');

		$data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE);

		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE );
			foreach( $data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		$smarty->assign_by_ref('data', $data);

		$smarty->assign_by_ref('preset_options', $preset_options );
		$smarty->assign_by_ref('section_group_options', $section_groups );
		$smarty->assign_by_ref('user_options', $user_options);
		$smarty->assign_by_ref('permission_data', $permission_data);
		$smarty->assign_by_ref('ignore_permissions', $ignore_permissions);
		$smarty->assign_by_ref('id', $id);
		$smarty->assign_by_ref('group_id', $group_id);
		$smarty->assign_by_ref('product_edition', $current_company->getProductEdition() );

		break;
}

$smarty->assign_by_ref('pcf', $pcf);

$smarty->display('permission/EditPermissionControl.tpl');
?>