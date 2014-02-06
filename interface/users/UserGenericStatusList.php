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
 * $Id: UserGenericStatusList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );
/*
if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','view') OR $permission->Check('user','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}
*/

$smarty->assign('title', TTi18n::gettext($title = 'Status Report')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'batch_id',
												'batch_title',
												'batch_next_page',
												'action',
												'page',
												'sort_column',
												'sort_order',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page,
													'batch_id' => $batch_id,
													'batch_title' => $batch_title,
													'batch_next_page' => $batch_next_page
												) );

$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

switch ($action) {
	default:
		Debug::Text('Next Page: '. urldecode( $batch_next_page ) , __FILE__, __LINE__, __METHOD__,10);
		if ( $batch_id != '' ) {
			$ugslf = TTnew( 'UserGenericStatusListFactory' );
			$ugslf->getByUserIdAndBatchId( $current_user->getId(), $batch_id,  $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
			//var_dump($ugslf);
			Debug::Text('Record Count: '. $ugslf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

			$pager = new Pager($ugslf);

			if ( $ugslf->getRecordCount() > 0 ) {
				$status_count_arr = $ugslf->getStatusCountArrayByUserIdAndBatchId( $current_user->getId(), $batch_id );

				foreach ($ugslf as $ugs_obj) {
					$rows[] = array(
										'id' => $ugs_obj->getId(),
										'user_id' => $ugs_obj->getUser(),
										'batch_id' => $ugs_obj->getBatchId(),
										'status_id' => $ugs_obj->getStatus(),
										'status' => Option::getByKey( $ugs_obj->getStatus(), $ugs_obj->getOptions('status') ),
										'label' => $ugs_obj->getLabel(),
										'description' => $ugs_obj->getDescription(),
										'link' => $ugs_obj->getLink(),
										'deleted' => $ugs_obj->getDeleted()
									);
				}

				//var_dump($rows);
				//var_dump($status_count_arr);
			}
		}
		$smarty->assign_by_ref('rows', $rows);
		$smarty->assign_by_ref('status_count', $status_count_arr);

		$smarty->assign_by_ref('batch_title', $batch_title);
		$smarty->assign_by_ref('batch_next_page', $batch_next_page);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('users/UserGenericStatusList.tpl');
?>