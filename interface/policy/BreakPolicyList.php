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
 * $Revision: 2035 $
 * $Id: BreakPolicyList.php 2035 2008-08-08 20:36:51Z ipso $
 * $Date: 2008-08-08 13:36:51 -0700 (Fri, 08 Aug 2008) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('break_policy','enabled')
		OR !( $permission->Check('break_policy','view') OR $permission->Check('break_policy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Break Policy List')); // See index.php
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditBreakPolicy.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$bplf = TTnew( 'BreakPolicyListFactory' );

		foreach ($ids as $id) {
			$bplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($bplf as $bp_obj) {
				$bp_obj->setDeleted($delete);
				if ( $bp_obj->isValid() ) {
					$bp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'BreakPolicyList.php') );

		break;

	default:
		$bplf = TTnew( 'BreakPolicyListFactory' );
		$bplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($bplf);

		$type_options = $bplf->getOptions('type');

		$show_no_policy_group_notice = FALSE;
		foreach ($bplf as $bp_obj) {
			if ( (int)$bp_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $bp_obj->getId(),
								'name' => $bp_obj->getName(),
								'type_id' => $bp_obj->getType(),
								'type' => $type_options[$bp_obj->getType()],
								'amount' => $bp_obj->getAmount(),
								'trigger_time' => $bp_obj->getTriggerTime(),
								'assigned_policy_groups' => (int)$bp_obj->getColumn('assigned_policy_groups'),
								'deleted' => $bp_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('policies', $policies);

		$smarty->assign_by_ref('show_no_policy_group_notice', $show_no_policy_group_notice );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/BreakPolicyList.tpl');
?>