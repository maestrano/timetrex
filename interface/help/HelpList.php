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
 * $Id: HelpList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('help','enabled')
		OR !( $permission->Check('help','view') OR $permission->Check('help','view_own')
				OR $permission->Check('help','edit') OR $permission->Check('help','edit_own')) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Help List')); // See index.php
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
$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL(NULL, 'EditHelp.php') );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$hlf = TTnew( 'HelpListFactory' );

		foreach ($ids as $id) {
			$hlf->getById($id );
			foreach ($hlf as $help_obj) {
				$help_obj->setDeleted($delete);
				$help_obj->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'HelpList.php') );

/*
		$slf = TTnew( 'StationListFactory' );

		foreach ($ids as $id) {
			$slf->GetByIdAndCompanyId($id, $current_company->getId() );
			foreach ($slf as $station) {
				$station->setDeleted($delete);
				$station->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'StationList.php') );
*/
		break;

	default:
		$hlf = TTnew( 'HelpListFactory' );

		$hlf->getAll($current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($hlf);

		foreach ($hlf as $help_obj) {

			$help[] = array(
								'id' => $help_obj->GetId(),
								'type' => Option::getByKey($help_obj->getType(), $help_obj->getOptions('type') ),
								'status' => Option::getByKey($help_obj->getStatus(), $help_obj->getOptions('status') ),
								'heading' => $help_obj->getHeading(),
								'private' => $help_obj->getPrivate(),
								'deleted' => $help_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('help_entries', $help);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('help/HelpList.tpl');
?>