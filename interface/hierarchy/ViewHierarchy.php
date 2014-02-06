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
 * $Id: ViewHierarchy.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('hierarchy','enabled')
		OR !( $permission->Check('hierarchy','view') OR $permission->Check('hierarchy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'View Hierarchy')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'hierarchy_id',
												'id'
												) ) );

switch ($action) {
	default:
		if ( isset($id) ) {

			$hlf = TTnew( 'HierarchyListFactory' );

			$tmp_id = $id;
			$i=0;
			do {
				Debug::Text(' Iteration...', __FILE__, __LINE__, __METHOD__,10);
				$parents = $hlf->getParentLevelIdArrayByHierarchyControlIdAndUserId( $hierarchy_id, $tmp_id);

				$level = $hlf->getFastTreeObject()->getLevel( $tmp_id )-1;

				if ( is_array($parents) AND count($parents) > 0 ) {
					$parent_users = array();
					foreach($parents as $user_id) {
						//Get user information
						$ulf = TTnew( 'UserListFactory' );
						$ulf->getById( $user_id );
						$user = $ulf->getCurrent();
						unset($ulf);

						$parent_users[] = array( 'name' => $user->getFullName() );
						unset($user);
					}

					$parent_groups[] = array( 'users' => $parent_users, 'level' => $level );
					unset($parent_users);
				}

				if ( isset($parents[0]) ) {
					$tmp_id = $parents[0];
				}
				
				$i++;
			} while ( is_array($parents) AND count($parents) > 0 AND $i < 100 );
		}

		$smarty->assign_by_ref('parent_groups', $parent_groups);

		break;
}
$smarty->display('hierarchy/ViewHierarchy.tpl');
?>