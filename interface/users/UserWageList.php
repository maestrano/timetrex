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
 * $Revision: 5457 $
 * $Id: UserWageList.php 5457 2011-11-04 20:49:58Z ipso $
 * $Date: 2011-11-04 13:49:58 -0700 (Fri, 04 Nov 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('wage','enabled')
		OR !( $permission->Check('wage','view') OR $permission->Check('wage','view_child') OR $permission->Check('wage','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Employee Wage List')); // See index.php
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
												'saved_search_id',
												'ids',
												'user_id'
												) ) );

$ulf = TTnew( 'UserListFactory' );
//$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
//$user_data = $ulf->getCurrent();
//$smarty->assign('title', $user_data->getFullName().'\'s Wage List' );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'user_id' => $user_id,
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

		Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id, 'saved_search_id' => $saved_search_id ), 'EditUserWage.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$uwlf = TTnew( 'UserWageListFactory' );

		if ( $ids != '' ) {
			foreach ($ids as $id) {
				$uwlf->getByIdAndCompanyId($id, $current_company->getId() );
				foreach ($uwlf as $wage) {
					$wage->setDeleted($delete);
					$wage->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id), 'UserWageList.php') );

		break;

	default:
		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$user_has_default_wage = FALSE;

		$hlf = TTnew( 'HierarchyListFactory' );
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

		$uwlf = TTnew( 'UserWageListFactory' );
		$uwlf->GetByUserIdAndCompanyId($user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($uwlf);

		$wglf = TTnew( 'WageGroupListFactory' );
		$wage_group_options = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
		if ( is_object($user_obj) ) {
			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

			$currency_symbol = NULL;
			if ( is_object($user_obj->getCurrencyObject()) ) {
				$currency_symbol = $user_obj->getCurrencyObject()->getSymbol();
			}

			if ( $permission->Check('wage','view')
					OR ( $permission->Check('wage','view_own') AND $is_owner === TRUE )
					OR ( $permission->Check('wage','view_child') AND $is_child === TRUE ) ) {

				foreach ($uwlf as $wage) {
					$wages[] = array(
										'id' => $wage->getId(),
										'user_id' => $wage->getUser(),
										'wage_group_id' => $wage->getWageGroup(),
										'wage_group' => Option::getByKey($wage->getWageGroup(), $wage_group_options ),
										'type' => Option::getByKey($wage->getType(), $wage->getOptions('type') ),
										'wage' => Misc::MoneyFormat( Misc::removeTrailingZeros($wage->getWage()), TRUE ),
										'currency_symbol' => $currency_symbol,
										'effective_date' => TTDate::getDate( 'DATE', $wage->getEffectiveDate() ),
										'is_owner' => $is_owner,
										'is_child' => $is_child,
										'deleted' => $wage->getDeleted()
									);

					if ( $wage->getWageGroup() == 0 ) {
						$user_has_default_wage = TRUE;
					}
				}
			}
		}

		$ulf = TTnew( 'UserListFactory' );

		$filter_data = NULL;
		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, NULL ) );

		if ( $permission->Check('wage','view') == FALSE ) {
			if ( $permission->Check('wage','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('wage','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );

		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

		$smarty->assign_by_ref('user_options', $user_options);

		$smarty->assign_by_ref('wages', $wages);
		$smarty->assign_by_ref('user_id', $user_id );
		$smarty->assign_by_ref('user_has_default_wage', $user_has_default_wage );

		$smarty->assign_by_ref('saved_search_id', $saved_search_id );
		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('users/UserWageList.tpl');
?>