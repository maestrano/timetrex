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
 * $Id: UserDeductionList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('user_tax_deduction','enabled')
		OR !( $permission->Check('user_tax_deduction','view') OR $permission->Check('user_tax_deduction','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Employee Tax / Deduction List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'saved_search_id',
												'user_id',
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


$ulf = TTnew( 'UserListFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id, 'saved_search_id' => $saved_search_id ), 'EditUserDeduction.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$udlf = TTnew( 'UserDeductionListFactory' );

		if ( isset($ids) AND is_array($ids) ) {
			foreach ($ids as $id) {
				$udlf->getByCompanyIdAndId($current_company->getId(), $id, $current_company->getId() );
				foreach ($udlf as $ud_obj) {
					$ud_obj->setDeleted($delete);
					if ( $ud_obj->isValid() ) {
						$ud_obj->Save();
					}
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id ), 'UserDeductionList.php') );

		break;
	default:
		BreadCrumb::setCrumb($title);

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$hlf = TTnew( 'HierarchyListFactory' );
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

		$udlf = TTnew( 'UserDeductionListFactory' );
		$udlf->getByCompanyIdAndUserId( $current_company->getId(), $user_id );

		$pager = new Pager($udlf);

		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();
			
			if ( is_object($user_obj) ) {
				$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
				$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

				if ( $permission->Check('user_tax_deduction','view')
						OR ( $permission->Check('user_tax_deduction','view_own') AND $is_owner === TRUE )
						OR ( $permission->Check('user_tax_deduction','view_child') AND $is_child === TRUE ) ) {

					foreach ($udlf as $ud_obj) {
						$cd_obj = $ud_obj->getCompanyDeductionObject();

						$rows[] = array(
											'id' => $ud_obj->getId(),
											'status_id' => $cd_obj->getStatus(),
											'user_id' => $ud_obj->getUser(),
											'name' => $cd_obj->getName(),
											'type_id' => $cd_obj->getType(),
											'type' => Option::getByKey( $cd_obj->getType(), $cd_obj->getOptions('type') ),
											'calculation' => Option::getByKey( $cd_obj->getCalculation(), $cd_obj->getOptions('calculation') ),
											'is_owner' => $is_owner,
											'is_child' => $is_child,
											'deleted' => $ud_obj->getDeleted()
										);
					}
				}
			}
		}

		$smarty->assign_by_ref('rows', $rows);
		$smarty->assign_by_ref('user_id', $user_id);

		$ulf = TTnew( 'UserListFactory' );

		$filter_data = NULL;
		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, NULL ) );

		if ( $permission->Check('user_tax_deduction','view') == FALSE ) {
			if ( $permission->Check('user_tax_deduction','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('user_tax_deduction','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

		$smarty->assign_by_ref('user_options', $user_options);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );
		$smarty->assign_by_ref('saved_search_id', $saved_search_id );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('users/UserDeductionList.tpl');
?>