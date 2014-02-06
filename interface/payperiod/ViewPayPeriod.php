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
 * $Revision: 5387 $
 * $Id: ViewPayPeriod.php 5387 2011-10-25 16:23:28Z ipso $
 * $Date: 2011-10-25 09:23:28 -0700 (Tue, 25 Oct 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
//require_once(Environment::getBasePath() .'classes/class.progressbar.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('pay_period_schedule','enabled')
		OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'View Pay Period')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'pay_period_id',
												'status_id'
												) ) );

$ppf = TTnew( 'PayPeriodFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() );
		foreach ($pplf as $pay_period_obj) {
			$pay_period_obj->setStatus( $status_id );
			$pay_period_obj->save();
		}

		Redirect::Page( URLBuilder::getURL( array('pay_period_id' => $pay_period_id), 'ViewPayPeriod.php') );

		break;
	case 'generate_paystubs':
		Debug::Text('Generate Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);

		Redirect::Page( URLBuilder::getURL( array('action' => 'generate_paystubs', 'pay_period_ids' => $pay_period_id, 'next_page' => URLBuilder::getURL( array('filter_pay_period_id' => $pay_period_id ), '../pay_stub/PayStubList.php') ), '../progress_bar/ProgressBarControl.php') );

		break;
	case 'import':
		//Imports already created shifts in to this pay period, from another pay period.
		//Get all users assigned to this pay period schedule.
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pay_period_obj = $pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() )->getCurrent();

		$pay_period_obj->importData();

		Redirect::Page( URLBuilder::getURL( array('pay_period_id' => $pay_period_id), 'ViewPayPeriod.php') );

		break;
	case 'delete_data':
		//Deletes all data assigned to this pay period.
		//Get all users assigned to this pay period schedule.
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pay_period_obj = $pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() )->getCurrent();

		$pay_period_obj->deleteData();

		Redirect::Page( URLBuilder::getURL( array('pay_period_id' => $pay_period_id), 'ViewPayPeriod.php') );

		break;
	default:
		if ( isset($pay_period_id) ) {
			BreadCrumb::setCrumb($title);

			$status_options = $ppf->getOptions('status');

			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() );

			foreach ($pplf as $pay_period_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$pay_period_data = array(
													'id' => $pay_period_obj->getId(),
													'company_id' => $pay_period_obj->getCompany(),
													'pay_period_schedule_id' => $pay_period_obj->getPayPeriodSchedule(),
													'pay_period_schedule_type' => $pay_period_obj->getPayPeriodScheduleObject()->getType(),
													'status_id' => $pay_period_obj->getStatus(),
													'status' => $status_options[$pay_period_obj->getStatus()],
													'start_date' => $pay_period_obj->getStartDate(),
													'end_date' => $pay_period_obj->getEndDate(),
													'transaction_date' => $pay_period_obj->getTransactionDate(),
													'is_primary' => $pay_period_obj->getPrimary(),

													'deleted' => $pay_period_obj->getDeleted(),
													'tainted' => $pay_period_obj->getTainted(),
													'tainted_date' => $pay_period_obj->getTaintedDate(),
													'tainted_by' => $pay_period_obj->getTaintedBy(),
													'created_date' => $pay_period_obj->getCreatedDate(),
													'created_by' => $pay_period_obj->getCreatedBy(),
													'updated_date' => $pay_period_obj->getUpdatedDate(),
													'updated_by' => $pay_period_obj->getUpdatedBy(),
													'deleted_date' => $pay_period_obj->getDeletedDate(),
													'deleted_by' => $pay_period_obj->getDeletedBy()
												);
			}
			Debug::Text('Current Pay Period Status: '. $pay_period_obj->getStatus(), __FILE__, __LINE__, __METHOD__,10);

			$status_options = $pay_period_obj->getOptions('status');

			if ( $pay_period_obj->getStatus() == 20
					OR $pay_period_obj->getStatus() == 30 ) {
				//Once pay period is closed, do not allow it to re-open.
				$status_filter_arr = array(20,30);
			} else {
				//Only allow to close pay period if AFTER end date.
				if ( TTDate::getTime() >= $pay_period_obj->getEndDate() ) {
					$status_filter_arr = array(10,12,$pay_period_obj->getStatus(), 20);
				} else {
					$status_filter_arr = array(10,12,$pay_period_obj->getStatus() );
				}
			}

			$status_options = Option::getByArray( $status_filter_arr, $status_options);

			$smarty->assign_by_ref('status_options', $status_options);

			$elf = TTnew( 'ExceptionListFactory' );
			$elf->getSumExceptionsByPayPeriodIdAndBeforeDate($pay_period_obj->getId(), $pay_period_obj->getEndDate() );
			$exceptions = array(
								'low' => 0,
								'med' => 0,
								'high' => 0,
								'critical' => 0,
								);
			if ( $elf->getRecordCount() > 0 ) {
				Debug::Text(' Found Exceptions: '. $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
				foreach($elf as $e_obj ) {
					if ( $e_obj->getColumn('severity_id') == 10 ) {
						$exceptions['low'] = $e_obj->getColumn('count');
					}
					if ( $e_obj->getColumn('severity_id') == 20 ) {
						$exceptions['med'] = $e_obj->getColumn('count');
					}
					if ( $e_obj->getColumn('severity_id') == 25 ) {
						$exceptions['high'] = $e_obj->getColumn('count');
					}
					if ( $e_obj->getColumn('severity_id') == 30 ) {
						$exceptions['critical'] = $e_obj->getColumn('count');
					}

				}
			} else {
				Debug::Text(' No Exceptions!', __FILE__, __LINE__, __METHOD__,10);
			}

			//Get all pending requests
			$pending_requests = 0;
			$rlf = TTnew( 'RequestListFactory' );
			$rlf->getSumByPayPeriodIdAndStatus( $pay_period_obj->getId(), 30 );
			if ( $rlf->getRecordCount() > 0 ) {
				$pending_requests = $rlf->getCurrent()->getColumn('total');
			}
			$pay_period_data['pending_requests'] = $pending_requests;

			//Count how many punches are in this pay period.
			$plf = TTnew( 'PunchListFactory' );
			$pay_period_data['total_punches'] = $plf->getByPayPeriodId( $pay_period_id )->getRecordCount();
			Debug::Text(' Total Punches: '. $pay_period_data['total_punches'], __FILE__, __LINE__, __METHOD__,10);
		}
		//var_dump($pay_period_data);

		$smarty->assign_by_ref('exceptions', $exceptions);
		$smarty->assign_by_ref('pay_period_data', $pay_period_data);
		$smarty->assign_by_ref('current_epoch', TTDate::getTime() );

		break;
}

$smarty->assign_by_ref('ppf', $ppf);

$smarty->display('payperiod/ViewPayPeriod.tpl');
?>