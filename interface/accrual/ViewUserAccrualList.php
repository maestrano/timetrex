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
 * $Revision: 6791 $
 * $Id: ViewUserAccrualList.php 6791 2012-05-17 19:46:31Z ipso $
 * $Date: 2012-05-17 12:46:31 -0700 (Thu, 17 May 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('accrual','enabled')
		OR !( $permission->Check('accrual','view') OR $permission->Check('accrual','view_own') OR $permission->Check('accrual','view_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}


$smarty->assign('title', TTi18n::gettext($title = 'Accrual List')); // See index.php
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
												'user_id',
												'accrual_policy_id',
												'ids',
												) ) );

if ( $permission->Check('accrual','view') OR $permission->Check('accrual','view_child')) {
	$user_id = $user_id;
} else {
	$user_id = $current_user->getId();
}

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'user_id' => $user_id,
													'accrual_policy_id' => $accrual_policy_id,
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditUserAccrual.php') );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$alf = TTnew( 'AccrualListFactory' );

		$alf->StartTransaction();
		if ( is_array($ids) ) {
			foreach ($ids as $id) {

				$alf->getById( $id );
				foreach ($alf as $a_obj) {
					//Allow user to delete AccrualPolicy entries, but not Banked/Used entries.
					if ( $a_obj->getUserDateTotalID() == FALSE ) {
						$a_obj->setEnableCalcBalance(FALSE);
						$a_obj->setDeleted($delete);
						if ( $a_obj->isValid() ) {
							$a_obj->Save();
						}
					}
				}
			}

			AccrualBalanceFactory::calcBalance( $user_id, $accrual_policy_id );
		}
		$alf->CommitTransaction();

		Redirect::Page( URLBuilder::getURL( NULL, 'ViewUserAccrualList.php') );

		break;

	default:
		$alf = TTnew( 'AccrualListFactory' );
		$alf->getByCompanyIdAndUserIdAndAccrualPolicyID( $current_company->getId(), $user_id, $accrual_policy_id, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array);

		$pager = new Pager($alf);

		foreach ($alf as $a_obj) {

			$date_stamp = $a_obj->getColumn('date_stamp');
			if ( $date_stamp != '' ) {
				$date_stamp = TTDate::strtotime($date_stamp);
			}
			$accruals[] = array(
								'id' => $a_obj->getId(),
								'user_id' => $a_obj->getUser(),
								'accrual_policy_id' => $a_obj->getAccrualPolicyId(),
								'type_id' => $a_obj->getType(),
								'type' => Option::getByKey( $a_obj->getType(), $a_obj->getOptions('type') ),
								'user_date_total_id' => $a_obj->getUserDateTotalId(),
								'user_date_total_date_stamp' => $date_stamp,
								'time_stamp' => $a_obj->getTimeStamp(),
								'amount' => $a_obj->getAmount(),
								'system_type' => $a_obj->isSystemType(),
								'deleted' => $a_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('accruals', $accruals);

		$ulf = TTnew( 'UserListFactory' );
		$user_obj = $ulf->getById( $user_id )->getCurrent();

		$aplf = TTnew( 'AccrualPolicyListFactory' );
		$accrual_policy_obj = $aplf->getById( $accrual_policy_id )->getCurrent();

		$smarty->assign_by_ref('user_id', $user_id);
		$smarty->assign_by_ref('user_full_name', $user_obj->getFullName() );
		$smarty->assign_by_ref('accrual_policy_id', $accrual_policy_id);
		$smarty->assign_by_ref('accrual_policy', $accrual_policy_obj->getName() );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('accrual/ViewUserAccrualList.tpl');
?>