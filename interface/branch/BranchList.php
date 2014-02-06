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
 * $Revision: 4822 $
 * $Id: BranchList.php 4822 2011-06-12 02:25:33Z ipso $
 * $Date: 2011-06-11 19:25:33 -0700 (Sat, 11 Jun 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('branch','enabled')
		OR !( $permission->Check('branch','view') OR $permission->Check('branch','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Branch List') ); // See index.php
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
												'ids'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL(NULL, 'EditBranch.php') );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$blf = TTnew( 'BranchListFactory' );

		if ( isset($ids) AND is_array($ids) ) {
			foreach ($ids as $id) {
				$blf->getByIdAndCompanyId($id, $current_company->getId() );
				foreach ($blf as $branch) {
					$branch->setDeleted($delete);
					$branch->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'BranchList.php') );

		break;
	default:
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$blf = TTnew( 'BranchListFactory' );
		$blf->GetByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(),$page, NULL, $sort_array );

		$pager = new Pager($blf);

		$branches = array();
		if ( $blf->getRecordCount() > 0 ) {
			foreach ($blf as $branch) {
				$branches[] = array(
									'id' => $branch->GetId(),
									'status_id' => $branch->getStatus(),
									'manual_id' => $branch->getManualID(),
									'name' => $branch->getName(),
									'city' => $branch->getCity(),
									'province' => $branch->getProvince(),
									'map_url' => $branch->getMapURL(),
									'deleted' => $branch->getDeleted()
								);
			}
		}
		$smarty->assign_by_ref('branches', $branches);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('branch/BranchList.tpl');
?>