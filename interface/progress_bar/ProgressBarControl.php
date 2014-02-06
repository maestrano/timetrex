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
 * $Revision: 2413 $
 * $Id: ProgressBarControl.php 2413 2009-02-06 21:59:57Z ipso $
 * $Date: 2009-02-06 13:59:57 -0800 (Fri, 06 Feb 2009) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');


//Debug::setVerbosity(11);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'next_page',
												'pay_period_ids',
												'filter_user_id',
												'pay_stub_ids',
												'data',
												) ) );

$action = strtolower($action);
switch ($action) {
	case 'recalculate_company':
		Debug::Text('ProgressBarControl: Recalculating Company TimeSheet!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit') OR $permission->Check('punch','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Recalculating Company TimeSheet...');

		break;
	case 'recalculate_employee':
		Debug::Text('ProgressBarControl: Recalculating Employee / Company TimeSheet!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit') OR $permission->Check('punch','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Recalculating Employee TimeSheet...');

		break;
	case 'generate_paystubs':
		Debug::Text('Generate PayStubs!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Generating Pay Stubs...');

		//$smarty->assign_by_ref('action', $action);
		//$smarty->assign_by_ref('pay_period_ids', $pay_period_ids);

		break;
	case 'recalculate_paystub_ytd':
		Debug::Text('Re-Calculating PayStub YTD values!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Recalculating Pay Stub Year To Date (YTD) amounts...');

		break;
	case 'recalculate_accrual_policy':
		Debug::Text('Recalculate Accrual Policy!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('accrual_policy','enabled')
				OR !( $permission->Check('accrual_policy','edit')
						OR $permission->Check('accrual_policy','edit_own')
						OR $permission->Check('accrual_policy','edit_child')
						 ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Recalculating Accrual Policy...');

		break;
	case 'add_mass_punch':
		Debug::Text('Add Mass Punch!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit')
						OR $permission->Check('punch','edit_own')
						OR $permission->Check('punch','edit_child')
						 ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Adding Punches...');

		break;
	case 'add_mass_schedule':
		Debug::Text('Add Mass Schedule!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('schedule','enabled')
				OR !( $permission->Check('schedule','edit')
						OR $permission->Check('schedule','edit_own')
						OR $permission->Check('schedule','edit_child')
						 ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Adding Schedule Shifts...');

		break;
	default:
		$comment = TTi18n::gettext('Test Progress Bar...');
		//$smarty->assign_by_ref('user_data', $user_data);

		break;


}

/*
	This suffers from URLs that are too long, especially when coming from Mass Punch/Schedule.
	Offer a method to store the data in the user_generic_data table, and retreive it on the ProgressBar.php page, bypassing the URL completely.
*/
$url = URLBuilder::getURL( array('action' => $action, 'pay_period_ids' => $pay_period_ids, 'filter_user_id' => $filter_user_id, 'pay_stub_ids' => $pay_stub_ids, 'data' => $data, 'next_page' => urlencode($next_page) ), Environment::getBaseURL().'/progress_bar/ProgressBar.php');

$smarty->assign_by_ref('comment', $comment);
//$smarty->assign_by_ref('next_page', $next_page);
$smarty->assign_by_ref('url', $url);

$smarty->display('progress_bar/ProgressBarControl.tpl');
?>