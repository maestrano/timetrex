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
 * $Revision: 6675 $
 * $Id: ROEList.php 6675 2012-04-24 21:04:40Z ipso $
 * $Date: 2012-04-24 14:04:40 -0700 (Tue, 24 Apr 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('roe','enabled')
		OR !( $permission->Check('roe','view') OR $permission->Check('roe','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'ROE List')); // See index.php
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
												'id',
												'ids',
												'user_id'
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
	case 'export':
	case 'view':
	case 'print':
		//Debug::setVerbosity(11);
		Debug::Text('aAction: View!', __FILE__, __LINE__, __METHOD__,10);
		if ( isset($id) AND !isset($ids) ) {
			$ids = array($id);
		}

		if ( count($ids) == 0 ) {
			echo TTi18n::gettext("ERROR: No Items Selected!")."<br>\n";
			exit;
		}

		if ( count($ids) > 0 ) {
			$rlf = TTnew( 'ROEListFactory' );
			$rlf->getByIdAndCompanyId( $ids, $current_company->getId() );

			if ( $action == 'export' ) {
				$output = $rlf->exportROE( $rlf );
				//echo "<pre>$output</pre>";
				//Debug::Display();
				if ( $output !== FALSE AND Debug::getVerbosity() < 11 ) {
					Misc::FileDownloadHeader('roe.blk', 'application/octetstream', strlen($output));
					echo $output;
					exit;
				} else {
					echo TTi18n::gettext("ERROR: ROE not available, it may be deleted!")."<br>\n";
					exit;
				}
			} else {
				$show_background = TRUE;
				if ( $action == 'print' ) {
					$show_background = FALSE;
				}
				$output = $rlf->getROE( $rlf, (bool)$show_background );

				//Debug::Display();
				if ( $output !== FALSE AND Debug::getVerbosity() < 11 ) {
					Misc::FileDownloadHeader('roe.pdf', 'application/pdf', strlen($output));
					echo $output;
				} else {
					echo TTi18n::gettext("ERROR: ROE not available, it may be deleted!")."<br>\n";
				}

				Debug::WriteToLog();
				exit;
			}
		}

		break;
	case 'add':

		Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id), 'EditROE.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$roelf = TTnew( 'ROEListFactory' );

		foreach ($ids as $id) {
			$roelf->GetById( $id );
			foreach ($roelf as $roe) {
				$roe->setDeleted($delete);
				$roe->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id), 'ROEList.php') );

		break;

	default:
		$roelf = TTnew( 'ROEListFactory' );

		/*
		if ( $permission->Check('company','view') ) {
			//View all default_schedules
			//$dsclf->GetByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
			$ulf->GetAll( $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
		} else {
            //$dsclf->GetByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, array($sort_column => $sort_order) );
			//$dsclf->GetByUserId($current_user->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
			$ulf->GetByCompanyID($current_company->getID(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
		}
		*/
		$roelf->getByUserId( $user_id, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array  );

		$pager = new Pager($roelf);

		$roe_code_options = $roelf->getOptions('code');

		foreach ($roelf as $roe) {
			//$company_name = $clf->getById( $user->getCompany() )->getCurrent()->getName();

			$roes[] = array(
									'id' => $roe->getId(),
									'user_id' => $roe->getUser(),
									'pay_period_type_id' => $roe->getPayPeriodType(),
									'code_id' => $roe->getCode(),
									'code' => $roe_code_options[$roe->getCode()],
									'first_date' => $roe->getFirstDate(),
									'last_date' => $roe->getLastDate(),
									'pay_period_end_date' => $roe->getPayPeriodEndDate(),
									'recall_date' => $roe->getRecallDate(),
									'insurable_hours' => $roe->getInsurableHours(),
									'insurable_earnings' => $roe->getInsurableEarnings(),
									'vacation_pay' => $roe->getVacationPay(),
									'serial' => $roe->getSerial(),
									'comments' => $roe->getComments(),
									'created_date' => $roe->getCreatedDate(),
									'created_by' => $roe->getCreatedBy(),
									'updated_date' => $roe->getUpdatedDate(),
									'updated_by' => $roe->getUpdatedBy(),
									'deleted_date' => $roe->getDeletedDate(),
									'deleted_by' => $roe->getDeletedBy()
								);

		}
		$smarty->assign_by_ref('roes', $roes);
		$smarty->assign_by_ref('user_id', $user_id);

		if ( isset($user_id) ) {
			$ulf = TTnew( 'UserListFactory' );
			$user_obj = $ulf->getById($user_id)->getCurrent();


			$smarty->assign_by_ref('user_full_name', $user_obj->getFullName() );
		}

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('roe/ROEList.tpl');
?>