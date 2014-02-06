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
 * $Revision: 5355 $
 * $Id: ClosePayPeriod.php 5355 2011-10-19 14:58:15Z ipso $
 * $Date: 2011-10-19 07:58:15 -0700 (Wed, 19 Oct 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('pay_period_schedule','enabled')
		OR !( $permission->Check('pay_period_schedule','view') OR $permission->Check('pay_period_schedule','view_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity(11);

$smarty->assign('title', TTi18n::gettext($title = 'End of Pay Period')); // See index.php
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
												'pay_period_ids',
												'pay_stub_pay_period_ids'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

Debug::Arr($pay_period_ids,'Selected Pay Periods', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'close':
	case 'unlock':
	case 'lock':
		//Lock selected pay periods
		Debug::Text('Lock Selected Pay Periods... Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$pplf = TTnew( 'PayPeriodListFactory' );

		$pplf->StartTransaction();
		if ( isset($pay_period_ids) AND count($pay_period_ids) > 0 ) {
			foreach($pay_period_ids as $pay_period_id) {
				$pay_period_obj = $pplf->getById( $pay_period_id )->getCurrent();

				if ( $pay_period_obj->getStatus() != 20 ) {
					if ( $action == 'close' ) {
						$pay_period_obj->setStatus(20);
					} elseif ( $action == 'lock' ) {
						$pay_period_obj->setStatus(12);
					} else {
						$pay_period_obj->setStatus(10);
					}

					$pay_period_obj->Save();
				}
			}
		}
		$pplf->CommitTransaction();

		Redirect::Page( URLBuilder::getURL(NULL, 'ClosePayPeriod.php') );

		break;
	case 'generate_pay_stubs':
		Debug::Text('Generate Pay Stubs ', __FILE__, __LINE__, __METHOD__,10);
		//var_dump($pay_stub_pay_period_ids);
		Redirect::Page( URLBuilder::getURL( array('action' => 'generate_paystubs', 'pay_period_ids' => $pay_stub_pay_period_ids, 'next_page' => '../payperiod/ClosePayPeriod.php' ), '../progress_bar/ProgressBarControl.php') );

		break;
	default:
		//Step 1, get all open pay periods that have ended and are before the transaction date.
		$pplf = TTnew( 'PayPeriodListFactory' );
		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

		$open_pay_periods = FALSE;

		//$pplf->getByCompanyIdAndTransactionDate( $current_company->getId(), TTDate::getTime() );
		$pplf->getByCompanyIdAndStatus( $current_company->getId(), array(10,12,15) );

		if ( $pplf->getRecordCount() > 0 ) {
			foreach ($pplf as $pay_period_obj) {
				$pay_period_schedule = $ppslf->getById( $pay_period_obj->getPayPeriodSchedule() )->getCurrent();

				if ( $pay_period_schedule != FALSE
						AND (
							$pay_period_obj->getEndDate() < TTDate::getTime()
							)
							) {

					$elf = TTnew( 'ExceptionListFactory' );
					$elf->getSumExceptionsByPayPeriodIdAndBeforeDate($pay_period_obj->getId(), $pay_period_obj->getEndDate() );

					$low_severity_exceptions = 0;
					$med_severity_exceptions = 0;
					$high_severity_exceptions = 0;
					$critical_severity_exceptions = 0;
					if ( $elf->getRecordCount() > 0 ) {
						Debug::Text(' Found Exceptions: '. $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
						foreach($elf as $e_obj ) {
							if ( $e_obj->getColumn('severity_id') == 10 ) {
								$low_severity_exceptions = $e_obj->getColumn('count');
							}
							if ( $e_obj->getColumn('severity_id') == 20 ) {
								$med_severity_exceptions = $e_obj->getColumn('count');
							}
							if ( $e_obj->getColumn('severity_id') == 25 ) {
								$high_severity_exceptions = $e_obj->getColumn('count');
							}
							if ( $e_obj->getColumn('severity_id') == 30 ) {
								$critical_severity_exceptions = $e_obj->getColumn('count');
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

					//Get PS Amendments.
					$psalf = TTnew( 'PayStubAmendmentListFactory' );
					$psalf->getByUserIdAndAuthorizedAndStartDateAndEndDate( $pay_period_schedule->getUser(), TRUE, $pay_period_obj->getStartDate(), $pay_period_obj->getEndDate() );
					$total_ps_amendments = 0;
					if ( is_object($psalf) ) {
						$total_ps_amendments = $psalf->getRecordCount();
					}

					//Get verified timesheets
					$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
					$pptsvlf->getByPayPeriodIdAndCompanyId( $pay_period_obj->getId(), $current_company->getId() );
					$verified_time_sheets = 0;
					$pending_time_sheets = 0;
					if ( $pptsvlf->getRecordCount() > 0 ) {
						foreach( $pptsvlf as $pptsv_obj ) {
							if ( $pptsv_obj->getAuthorized() == TRUE ) {
								$verified_time_sheets++;
							} elseif (  $pptsv_obj->getStatus() == 30 OR $pptsv_obj->getStatus() == 45 ) {
								$pending_time_sheets++;
							}
						}
					}

					//Get total employees with time for this pay period.
					$udtlf = TTnew( 'UserDateTotalListFactory' );
					$total_worked_users = $udtlf->getWorkedUsersByPayPeriodId( $pay_period_obj->getId() );

					//Count how many pay stubs for each pay period.
					$pslf = TTnew( 'PayStubListFactory' );
					$total_pay_stubs = $pslf->getByPayPeriodId( $pay_period_obj->getId() )->getRecordCount();

					if ( $pay_period_obj->getStatus() != 20 ) {
						$open_pay_periods = TRUE;
					}

					$pay_periods[] = array(
													'id' => $pay_period_obj->getId(),
													'company_id' => $pay_period_obj->getCompany(),
													'pay_period_schedule_id' => $pay_period_obj->getPayPeriodSchedule(),
													'name' => $pay_period_schedule->getName(),
													'type' => Option::getByKey($pay_period_schedule->getType(), $pay_period_schedule->getOptions('type') ),
													'status' => Option::getByKey($pay_period_obj->getStatus(), $pay_period_obj->getOptions('status') ),
													'start_date' => TTDate::getDate( 'DATE+TIME', $pay_period_obj->getStartDate() ),
													'end_date' => TTDate::getDate( 'DATE+TIME', $pay_period_obj->getEndDate() ),
													'transaction_date' => TTDate::getDate( 'DATE+TIME', $pay_period_obj->getTransactionDate() ),
													'low_severity_exceptions' => $low_severity_exceptions,
													'med_severity_exceptions' => $med_severity_exceptions,
													'high_severity_exceptions' => $high_severity_exceptions,
													'critical_severity_exceptions' => $critical_severity_exceptions,
													'pending_requests' => $pending_requests,
													'verified_time_sheets' => $verified_time_sheets,
													'pending_time_sheets' => $pending_time_sheets,
													'total_worked_users' => $total_worked_users,
													'total_ps_amendments' => $total_ps_amendments,
													'total_pay_stubs' => $total_pay_stubs,
													'deleted' => $pay_period_obj->getDeleted()
													);
				}
				unset(	$total_shifts,
						$total_ps_amendments,
						$total_pay_stubs,
						$verified_time_sheets,
						$total_worked_users);
			}

		} else {
			Debug::Text('No pay periods pending transaction ', __FILE__, __LINE__, __METHOD__,10);
		}


		$smarty->assign_by_ref('open_pay_periods', $open_pay_periods);
		$smarty->assign_by_ref('pay_periods', $pay_periods);
		$total_pay_periods = count($pay_periods);
		$smarty->assign_by_ref('total_pay_periods', $total_pay_periods);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		//$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('payperiod/ClosePayPeriod.tpl');
?>