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
 * $Revision: 4919 $
 * $Id: EditHierarchyControl.php 4919 2011-07-03 23:01:01Z ipso $
 * $Date: 2011-07-03 16:01:01 -0700 (Sun, 03 Jul 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('hierarchy','enabled')
		OR !( $permission->Check('hierarchy','edit') OR $permission->Check('hierarchy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

//Debug::setVerbosity(11);

$smarty->assign('title', TTi18n::gettext($title = 'Edit Hierarchy List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'ids',
												'hierarchy_control_id',
												'hierarchy_control_data',
												'hierarchy_level_data'
												) ) );

$hcf = TTnew( 'HierarchyControlFactory' );
$hlf = TTnew( 'HierarchyLevelFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$redirect=0;

		$hcf->StartTransaction();

		//Since this class has sub-classes, when creating a new row make sure we have the ID set.
		if ( isset($hierarchy_control_data['id']) AND $hierarchy_control_data['id'] > 0 ) {
			$hcf->setId( $hierarchy_control_data['id'] );
		} else {
			$hcf->setID( $hcf->getNextInsertId() );
		}
		Debug::Text('Hierarchy Control ID: '. $hcf->getID() , __FILE__, __LINE__, __METHOD__,10);

		$hcf->setCompany( $current_company->getId() );

		$hcf->setName($hierarchy_control_data['name']);
		$hcf->setDescription($hierarchy_control_data['description']);

		if ( isset($hierarchy_control_data['object_type_ids']) ) {
			$hcf->setObjectType( $hierarchy_control_data['object_type_ids'] );
		} else {
			$hcf->setObjectType( FALSE );
		}

		if ( isset($hierarchy_control_data['user_ids'] ) ) {
			$hcf->setUser( $hierarchy_control_data['user_ids'] );
		} else {
			$hcf->setUser( array() );
		}

		if ( count($hierarchy_level_data) > 0 ) {
			//ReMap levels
			$hierarchy_level_map = $hlf->ReMapHierarchyLevels( $hierarchy_level_data );
			Debug::Arr($hierarchy_level_map, 'Hierarchy Level Map: ', __FILE__, __LINE__, __METHOD__,10);

			foreach( $hierarchy_level_data as $hierarchy_level_id => $hierarchy_level ) {
				Debug::Text('Row ID: '. $hierarchy_level_id .' Level: '. $hierarchy_level['level'] , __FILE__, __LINE__, __METHOD__,10);

				if ( $hierarchy_level['level'] != '' AND $hierarchy_level['level'] >= 0 AND isset($hierarchy_level_map[$hierarchy_level['level']]) ) {
					if ( $hierarchy_level_id > 0 ) {
						$hlf->setID( $hierarchy_level_id );
					}

					$hlf->setHierarchyControl( $hcf->getID() );
					$hlf->setLevel( $hierarchy_level_map[$hierarchy_level['level']] );
					$hlf->setUser( $hierarchy_level['user_id'] );

					if ( $hlf->isValid() ) {
						Debug::Text('Saving Level Row ID: '. $hierarchy_level_id, __FILE__, __LINE__, __METHOD__,10);
						$hlf->Save();
					} else {
						$redirect++;
					}
				} else {
					//Delete level
					if ( $hierarchy_level_id > 0 ) {
						$hlf->setID( $hierarchy_level_id );
						$hlf->setDeleted(TRUE);
						$hlf->Save();
					} else {
						unset($hierarchy_level_data[$hierarchy_level_id]);
					}
				}
			}
		}

		if ( $redirect == 0 AND $hcf->isValid() ) {
			$hcf->Save( TRUE, TRUE );
			$hcf->CommitTransaction();

			Redirect::Page( URLBuilder::getURL( array(), 'HierarchyControlList.php') );

			break;
		}
		$hcf->FailTransaction();
	case 'delete_level':
		if ( count($ids) > 0) {
			foreach ($ids as $hl_id) {
				if ( $hl_id > 0 ) {
					Debug::Text('Deleting level Row ID: '. $hl_id, __FILE__, __LINE__, __METHOD__,10);

					$hllf = TTnew( 'HierarchyLevelListFactory' );
					$hllf->getById( $hl_id );
					if ( $hllf->getRecordCount() == 1 ) {
						foreach($hllf as $hl_obj ) {
							$hl_obj->setDeleted( TRUE );
							if ( $hl_obj->isValid() ) {
								$hl_obj->Save();
							}
						}
					}
				}
				unset($hierarchy_level_data[$hl_id]);

			}
			unset($hl_id);
		}
	default:
		if ( isset($hierarchy_control_id) ) {
			BreadCrumb::setCrumb($title);

			$hclf = TTnew( 'HierarchyControlListFactory' );
			$hclf->getByIdAndCompanyId($hierarchy_control_id, $current_company->getId() );

			foreach ($hclf as $hierarchy_control) {
				$hierarchy_control_data = array(
									'id' => $hierarchy_control->getId(),
									'name' => $hierarchy_control->getName(),
									'description' => $hierarchy_control->getDescription(),
									'object_type_ids' => $hierarchy_control->getObjectType(),
									'user_ids' => $hierarchy_control->getUser(),
									'created_date' => $hierarchy_control->getCreatedDate(),
									'created_by' => $hierarchy_control->getCreatedBy(),
									'updated_date' => $hierarchy_control->getUpdatedDate(),
									'updated_by' => $hierarchy_control->getUpdatedBy(),
									'deleted_date' => $hierarchy_control->getDeletedDate(),
									'deleted_by' => $hierarchy_control->getDeletedBy()
								);
			}

			$hllf = TTnew( 'HierarchyLevelListFactory' );
			$hllf->getByHierarchyControlId( $hierarchy_control_id );
			if ( $hllf->getRecordCount() > 0 ) {
				foreach( $hllf as $hl_obj ) {
					$hierarchy_level_data[] = array(
													'id' => $hl_obj->getId(),
													'level' => $hl_obj->getLevel(),
													'user_id' => $hl_obj->getUser(),
													);
				}
			} else {
				$hierarchy_level_data[-1] = array(
												  'id' => -1,
												  'level' => 1,
												  );
			}
		} elseif ( $action == 'add_level' ) {
			Debug::Text('Adding Blank Level', __FILE__, __LINE__, __METHOD__,10);
			if ( !isset($hierarchy_level_data) OR ( isset($hierarchy_level_data) AND !is_array( $hierarchy_level_data ) ) ) {
				//If they delete all weeks and try to add a new one.
				$hierarchy_level_data[0] = array(
								'id' => -1,
								'level' => 0,
								);

				$row_keys = array_keys($hierarchy_level_data);
				sort($row_keys);

				$next_blank_id = 0;
				$lowest_id = 0;
			} else {
				$row_keys = array_keys($hierarchy_level_data);
				sort($row_keys);

				Debug::Text('Lowest ID: '. $row_keys[0], __FILE__, __LINE__, __METHOD__,10);
				$lowest_id = $row_keys[0];
				if ( $lowest_id < 0 ) {
					$next_blank_id = $lowest_id-1;
				} else {
					$next_blank_id = -1;
				}
			}

			Debug::Text('Next Blank ID: '. $next_blank_id, __FILE__, __LINE__, __METHOD__,10);

			//Find next level
			$last_new_level = $hierarchy_level_data[$row_keys[0]]['level'];
			$last_saved_level = $hierarchy_level_data[array_pop($row_keys)]['level'];
			Debug::Text('Last New level: '. $last_new_level .' Last Saved level: '. $last_saved_level, __FILE__, __LINE__, __METHOD__,10);
			if ( $last_new_level > $last_saved_level) {
				$last_level = $last_new_level;
			} else {
				$last_level = $last_saved_level;
			}
			Debug::Text('Last level: '. $last_level, __FILE__, __LINE__, __METHOD__,10);

			$hierarchy_level_data[$next_blank_id] = array(
							'id' => $next_blank_id,
							'level' => $last_level+1,
							);
		} elseif ( $action != 'submit' AND $action != 'delete_level' ) {
			//New hierarchy.

			$hierarchy_level_data[-1] = array(
											  'id' => -1,
											  'level' => 1,
											  );
		}

		$prepend_array_option = array( 0 => TTi18n::gettext('-- Please Choose --') );

		$ulf = TTnew( 'UserListFactory' );
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), FALSE, TRUE );

		//Select box options;
		$hotlf = TTnew( 'HierarchyObjectTypeListFactory' );
		$hierarchy_control_data['user_options'] = $user_options;
		$hierarchy_control_data['level_user_options'] = Misc::prependArray( $prepend_array_option, $user_options);
		$hierarchy_control_data['object_type_options'] = $hotlf->getOptions('object_type');

		if ( isset($hierarchy_control_data['user_ids']) AND is_array($hierarchy_control_data['user_ids']) ) {
			$tmp_user_options = $user_options;
			foreach( $hierarchy_control_data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		$smarty->assign_by_ref('hierarchy_control_data', $hierarchy_control_data);
		$smarty->assign_by_ref('hierarchy_level_data', $hierarchy_level_data);

		break;
}

$smarty->assign_by_ref('hcf', $hcf);
$smarty->assign_by_ref('hlf', $hlf);

$smarty->display('hierarchy/EditHierarchyControl.tpl');
?>