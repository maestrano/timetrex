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
 * $Id: EditHierarchy.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('hierarchy','enabled')
		OR !( $permission->Check('hierarchy','edit') OR $permission->Check('hierarchy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Hierarchy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'hierarchy_id',
												'id',
												'old_id',
												'user_data'
												) ) );

$ft = new FastTree($fast_tree_options);
$ft->setTree( $hierarchy_id );

$hf = TTnew( 'HierarchyFactory' );

$redirect=0;

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($user_data['user_id']) ) {
			foreach( $user_data['user_id'] as $user_id ) {
				if ( isset($id) AND $id != '') {
					$hf->setId( $id );
				}

				$hf->setHierarchyControl( $hierarchy_id );
				$hf->setPreviousUser( $old_id );
				//$hf->setUser( $user_data['user_id'] );
				$hf->setUser( $user_id );
				$hf->setParent( $user_data['parent_id'] );

				if ( isset($user_data['share']) ) {
					Debug::Text(' Setting share!: ', __FILE__, __LINE__, __METHOD__,10);
					$hf->setShared( TRUE );
				} else {
					$hf->setShared( FALSE );
				}

				if ( $hf->isValid() ) {
					Debug::Text(' Valid!: ', __FILE__, __LINE__, __METHOD__,10);

					if ( $hf->Save() === FALSE ) {
						$redirect++;
					}
				} else {
					$redirect++;
				}
			}
		}

		if ( $redirect == 0 ) {
			Redirect::Page( URLBuilder::getURL( array('hierarchy_id' => $hierarchy_id) , 'HierarchyList.php') );

			break;
		}

	default:
		//BreadCrumb::setCrumb($title);
		if ( isset($id) AND !isset($user_data['user_id']) ) {
			$user_data['user_id'] = $id;
		}

		$hlf = TTnew( 'HierarchyListFactory' );

		//$nodes = $hlf->FormatArray( $hlf->getByHierarchyControlId( $hierarchy_id ), 'TEXT', TRUE);
		//$nodes = FastTree::FormatArray( $hlf->getByHierarchyControlId( $hierarchy_id ), 'TEXT', TRUE);
		$nodes = FastTree::FormatArray( $hlf->getByCompanyIdAndHierarchyControlId( $current_company->getId(), $hierarchy_id ), 'TEXT', TRUE);

		foreach($nodes as $node) {
			$parent_list_options[$node['id']] = $node['text'];
		}

		//Get include employee list.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByCompanyId( $current_company->getId() );
		$raw_user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
		//$raw_user_list_options = UserListFactory::getByCompanyIdArray( $current_company->getId() );

		//Only allow them to select employees not already in the tree.
		unset($parent_list_options[$id]); //If we're editing a single entry, include that user in the list.
		$parent_list_keys = array_keys($parent_list_options);
		$user_options = Misc::arrayDiffByKey( (array)$parent_list_keys, $raw_user_options );

		$src_user_options = Misc::arrayDiffByKey( (array)$user_data['user_id'], $user_options );
		$selected_user_options = Misc::arrayIntersectByKey( (array)$user_data['user_id'], $user_options );

		//$smarty->assign_by_ref('user_list_options', $user_list_options);
		$smarty->assign_by_ref('src_user_options', $src_user_options);
		$smarty->assign_by_ref('selected_user_options', $selected_user_options);
		$smarty->assign_by_ref('parent_list_options', $parent_list_options);


		if ( isset($id) AND $id != '' AND $redirect == 0) {
			Debug::Text(' ID: '. $id , __FILE__, __LINE__, __METHOD__,10);
			$node = $hlf->getByHierarchyControlIdAndUserId( $hierarchy_id, $id);

			$smarty->assign_by_ref('selected_node', $node );
		} else {
			$id = $user_data['user_id'][0];
		}

		break;
}

$smarty->assign_by_ref('hierarchy_id', $hierarchy_id);
$smarty->assign_by_ref('id', $id);
$smarty->assign_by_ref('old_id', $id);

$smarty->assign_by_ref('hf', $hf);

$smarty->display('hierarchy/EditHierarchy.tpl');
?>