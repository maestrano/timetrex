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
 * $Id: PermissionControlList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('permission','enabled')
		OR !( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity(11);

$smarty->assign('title', TTi18n::gettext($title = 'Permission Group List')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'ids',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL( NULL, 'EditPermissionControl.php', FALSE) );

		break;
	case 'copy':
		$pclf = TTnew( 'PermissionControlListFactory' );

		$pclf->StartTransaction();

		foreach ($ids as $id) {
			$pclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($pclf as $pc_obj) {
				$permission_arr = $pc_obj->getPermission();

				$pc_obj->setId(FALSE);
				$pc_obj->setName( Misc::generateCopyName( $pc_obj->getName() ) );
				if ( $pc_obj->isValid() ) {
					$pc_obj->Save(FALSE);
					$pc_obj->setPermission( $permission_arr );
				}
				unset($pc_obj, $permission_arr);

			}
		}

		$pclf->CommitTransaction();

		Redirect::Page( URLBuilder::getURL( NULL, 'PermissionControlList.php') );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$pclf = TTnew( 'PermissionControlListFactory' );

		foreach ($ids as $id) {
			$pclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($pclf as $pc_obj) {
				$pc_obj->setDeleted($delete);
				if ( $pc_obj->isValid() ) {
					$pc_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'PermissionControlList.php') );

		break;

	default:
		$pclf = TTnew( 'PermissionControlListFactory' );
		$pclf->getByCompanyId( $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($pclf);

		foreach ($pclf as $pc_obj) {
			$rows[] = array(
								'id' => $pc_obj->getId(),
								'name' => $pc_obj->getColumn('name'),
								'description' => $pc_obj->getColumn('description'),
								'level' => $pc_obj->getLevel(),

								'deleted' => $pc_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('permission/PermissionControlList.tpl');
?>