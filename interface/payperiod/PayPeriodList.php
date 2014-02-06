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
 * $Id: PayPeriodList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('pay_period_schedule','enabled')
		OR !( $permission->Check('pay_period_schedule','view') OR $permission->Check('pay_period_schedule','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Pay Period List')); // See index.php
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
												'id',
												'projected_pay_periods',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'id' => $id,
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );


$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

//$ppslf = TTnew( 'PayPeriodScheduleFactory' );

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL( array('pay_period_schedule_id' => $id ), 'EditPayPeriod.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$pplf = TTnew( 'PayPeriodListFactory' );

		$pplf->StartTransaction();

		foreach ($ids as $pay_period_id) {
			$pplf->GetByIdAndCompanyId($pay_period_id, $current_company->getId() );
			foreach ($pplf as $pay_period) {
				$pay_period->setDeleted($delete);
				$pay_period->Save();
			}
		}

		//$pplf->FailTransaction();
		$pplf->CommitTransaction();

		Redirect::Page( URLBuilder::getURL( array('id' => $id), 'PayPeriodList.php') );

		break;

	default:
		$pplf = TTnew( 'PayPeriodListFactory' );
		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

		//$pplf->GetByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, array($sort_column => $sort_order) );
		//$pplf->GetByPayPeriodScheduleId($id, $current_user_prefs->getItemsPerPage(), $page, NULL, array($sort_column => $sort_order) );
		$pplf->getByPayPeriodScheduleId($id, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($pplf);

		if ( $pplf->getRecordCount() >= 1 ) {
			if ( is_numeric($projected_pay_periods) ) {
				$max_projected_pay_periods = $projected_pay_periods;
			} else {
				$max_projected_pay_periods = 1;
			}
		} else {
			$max_projected_pay_periods = 24;
		}

		Debug::Text('Projected Pay Periods: '. $max_projected_pay_periods, __FILE__, __LINE__, __METHOD__,10);

		//Now project in to the future X pay periods...
		if ( $sort_column == '' AND $page == '' OR $page == 1 ) {
			$ppslf->getById($id);
			foreach ($ppslf as $pay_period_schedule) {
				if ( $pay_period_schedule->getType() != 5 ) {
					for ($i=0; $i < $max_projected_pay_periods;$i++) {
						if ($i == 0) {
							if ( !isset( $last_end_date ) ) {
								$last_end_date = NULL;
							}

							$pay_period_schedule->getNextPayPeriod( $last_end_date );
						} else {
							$pay_period_schedule->getNextPayPeriod( $pay_period_schedule->getNextEndDate() );
						}


						//$start_date = $pay_period_schedule->getNextStartDate();
						//$end_date = $pay_period_schedule->getNextEndDate();
						//$transaction_date = $pay_period_schedule->getNextTransactionDate();
						//echo "Start Date: $start_date<br>\n";

						$pay_periods[] = array(
		//												'id' => 'N/A',
														'company_id' => $pay_period_schedule->getCompany(),
														'pay_period_schedule_id' => $pay_period_schedule->getId(),
														'name' => $pay_period_schedule->getName(),
														'type' => Option::getByKey($pay_period_schedule->getType(), $pay_period_schedule->getOptions('type') ),
														'status' => 'N/A',
														'start_date' => TTDate::getDate( 'DATE+TIME', $pay_period_schedule->getNextStartDate() ),
														'end_date' => TTDate::getDate( 'DATE+TIME', $pay_period_schedule->getNextEndDate() ),
														'transaction_date' => TTDate::getDate( 'DATE+TIME', $pay_period_schedule->getNextTransactionDate() ),
														'deleted' => FALSE
														);

					}
				}
			}
		}


		foreach ($pplf as $pay_period) {
			$pay_period_schedule = $ppslf->getById( $pay_period->getPayPeriodSchedule() )->getCurrent();
			//$pay_period_schedule = $ppslf->getCurrent();

			$pay_periods[] = array(
											'id' => $pay_period->getId(),
											'company_id' => $pay_period->getCompany(),
											'pay_period_schedule_id' => $pay_period->getPayPeriodSchedule(),
											'name' => $pay_period_schedule->getName(),
											'type' => Option::getByKey($pay_period_schedule->getType(), $pay_period_schedule->getOptions('type') ),
											'status' => Option::getByKey($pay_period->getStatus(), $pay_period->getOptions('status') ),
											'start_date' => TTDate::getDate( 'DATE+TIME', $pay_period->getStartDate() ),
											'end_date' => TTDate::getDate( 'DATE+TIME', $pay_period->getEndDate() ),
											'transaction_date' => TTDate::getDate( 'DATE+TIME', $pay_period->getTransactionDate() ),
											'deleted' => $pay_period->getDeleted()
											);

			$last_end_date = $pay_period->getEndDate();

		}
		unset($pay_period_schedule);

		$smarty->assign_by_ref('pay_periods', $pay_periods);

		$smarty->assign_by_ref('id', $id );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('payperiod/PayPeriodList.tpl');
?>