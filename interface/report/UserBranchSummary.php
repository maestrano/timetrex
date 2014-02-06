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
 * $Id: UserBranchSummary.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_branch_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Branch Summary')); // See index.php
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
												'pay_period_id',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'pay_period_id' => $pay_period_id,
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

switch ($action) {
	case 'Submit':
	default:

		$pplf = TTnew( 'PayPeriodListFactory' );
		$slf = TTnew( 'ShiftListFactory' );

		if (!isset($pay_period_id) ) {
			Debug::text(' Pay Period ID NOT SET: '. $pay_period_id , __FILE__, __LINE__, __METHOD__,10);
			$pay_period_id = $pplf->getByCompanyId( $current_company->getId(), 1, 2, NULL, array('start_date' => 'desc') )->getCurrent()->getId();
		}
		Debug::text(' Pay Period ID: '. $pay_period_id , __FILE__, __LINE__, __METHOD__,10);

		$psenlf = TTnew( 'PayStubEntryNameListFactory' );
		$ulf = TTnew( 'UserListFactory' );
		$blf = TTnew( 'BranchListFactory' );

		//Get all pay stubs for this pay period
		$pslf = TTnew( 'PayStubListFactory' );
		$pslf->getByPayPeriodId( $pay_period_id, NULL, array('advance' => '= \'f\'') );

		$pager = new Pager($pslf);

		$entry_name_ids = array(10,22);
		foreach($pslf as $pay_stub_obj) {
			Debug::text(' Pay Stub ID: '. $pay_stub_obj->getId() , __FILE__, __LINE__, __METHOD__,10);

			$pself = TTnew( 'PayStubEntryListFactory' );
			//Order is very important here. We want the "last" entries to go last, as they should
			//have the most up to date YTD values.
			$pself->getByPayStubId( $pay_stub_obj->getId() );

			$entries = NULL;

			foreach ($pself as $pay_stub_entry_obj) {
				$pay_stub_entry_name_obj = $psenlf->getById( $pay_stub_entry_obj->getPayStubEntryNameId() ) ->getCurrent();

				if ( in_array( $pay_stub_entry_obj->getPayStubEntryNameId(), $entry_name_ids ) ) {
					Debug::text(' Found valid entry name ID: '. $pay_stub_entry_name_obj->getName() .' Amount: '. $pay_stub_entry_obj->getAmount() , __FILE__, __LINE__, __METHOD__,10);

					if (  isset($show_ytd) AND $show_ytd == 1 ) {
						$amount = $pay_stub_entry_obj->getYTDAmount();
					} else {
						$amount = $pay_stub_entry_obj->getAmount();
					}

					if ( isset($show_ytd) AND $show_ytd == 1 ) {
						$entries[$pay_stub_entry_name_obj->getName()] = $amount;
					} else {
						//When we're not showing YTD, we have to add up all the entries, as there
						//could be two or more of the same name.
						if ( isset($entries[$pay_stub_entry_name_obj->getName()]) ) {
							//Debug::text(' Adding amount: '. $pay_stub_entry_name_obj->getName() .' Amount: '. $amount , __FILE__, __LINE__, __METHOD__,10);
							$entries[$pay_stub_entry_name_obj->getName()] += $amount;
							$entries[$pay_stub_entry_name_obj->getName()] = number_format($entries[$pay_stub_entry_name_obj->getName()], 2, '.','');
							//Debug::text(' Final amount: '. $pay_stub_entry_name_obj->getName() .' Amount: '. $entries[$pay_stub_entry_name_obj->getName()] , __FILE__, __LINE__, __METHOD__,10);
						} else {
							//Debug::text(' Setting amount: '. $pay_stub_entry_name_obj->getName() .' Amount: '. $amount , __FILE__, __LINE__, __METHOD__,10);
							$entries[$pay_stub_entry_name_obj->getName()] = $amount;
						}
					}

					unset($amount);
				} else {
					Debug::text(' INVALID entry name ID: '. $pay_stub_entry_obj->getPayStubEntryNameId() , __FILE__, __LINE__, __METHOD__,10);
				}
			}
			unset($prev_entries);



			if ( $entries !== NULL ) {
				//Do this so pay periods with both advanc, and full pay stubs only show the full pay stub.
				$pay_stub_rows[$pay_stub_obj->getUser()] = array(
							'user_id' => $pay_stub_obj->getUser(),
							'entries' => $entries
							);
			}
		}

		$total_time = 0;

		//Get shift total times for each user/branch
		$slf->getUserBranchTotalTimeByPayPeriodId( $pay_period_id );
		foreach($slf as $user_total_time_obj) {
			//Debug::text(' User ID: '. $user_total_time_obj->getColumn('user_id') .' Branch ID: '. $user_total_time_obj->getColumn('branch_id') .' Total Time: '.$user_total_time_obj->getColumn('branch_total_time') , __FILE__, __LINE__, __METHOD__,10);
			if ( isset($totals['users'][$user_total_time_obj->getColumn('user_id')])) {
				$totals['users'][$user_total_time_obj->getColumn('user_id')] += $user_total_time_obj->getColumn('branch_total_time');
			} else {
				$totals['users'][$user_total_time_obj->getColumn('user_id')] = $user_total_time_obj->getColumn('branch_total_time');
			}

			if ( isset($totals['branches'][$user_total_time_obj->getColumn('branch_id')]) ) {
				$totals['branches'][$user_total_time_obj->getColumn('branch_id')] += $user_total_time_obj->getColumn('branch_total_time');
			} else {
				$totals['branches'][$user_total_time_obj->getColumn('branch_id')] = $user_total_time_obj->getColumn('branch_total_time');
			}

			if ( isset($totals['branches']['total']) ) {
				$totals['branches']['total'] += $user_total_time_obj->getColumn('branch_total_time');
			} else {
				$totals['branches']['total'] = $user_total_time_obj->getColumn('branch_total_time');
			}

			$branch_ids[] = $user_total_time_obj->getColumn('branch_id');
		}
		if ( isset($branch_ids) ) {
			$branch_ids = array_unique($branch_ids);
		}
		//var_dump($totals);

		foreach($slf as $user_total_time_obj) {
			$user_obj = $ulf->getById( $user_total_time_obj->getColumn('user_id') )->getCurrent();
			Debug::text(' User Name: '. $user_obj->getFullName() , __FILE__, __LINE__, __METHOD__,10);

			$user_percent = $user_total_time_obj->getColumn('branch_total_time') / $totals['users'][$user_total_time_obj->getColumn('user_id')];

			if ( isset($pay_stub_rows[$user_total_time_obj->getColumn('user_id')]) ) {
				$user_gross_pay = $pay_stub_rows[$user_total_time_obj->getColumn('user_id')]['entries']['gross_pay'] * $user_percent;
			} else {
				$user_gross_pay = 0;
			}

			$user_entries[$user_total_time_obj->getColumn('branch_id')][] = array(
								'user_id' => $user_total_time_obj->getColumn('user_id'),
								'branch_id' => $user_total_time_obj->getColumn('branch_id'),
								'full_name' => $user_obj->getFullName(),
								'total_time' => $user_total_time_obj->getColumn('branch_total_time'),
								'percent' => $user_percent,
								'percent_display' => round( ($user_percent*100), 2),
								'gross_pay' => number_format($user_gross_pay, 2, '.','')
							);
			unset($user_percent, $user_gross_pay);
		}

		if ( isset($branch_ids) ) {
			foreach($branch_ids as $branch_id) {
				Debug::text(' Branch Done! Branch ID: '. $branch_id, __FILE__, __LINE__, __METHOD__,10);
				$branch_obj = $blf->getById( $branch_id )->getCurrent();
				$branch_percent = $totals['branches'][$branch_id] / $totals['branches']['total'];

				$user_totals = Misc::ArrayAssocSum($user_entries[$branch_id], NULL, 2);

				$user_entries[$branch_id][] = array(
										'full_name' => 'Total',
										'total_time' => $totals['branches'][$branch_id],
										'percent' => $branch_percent*100,
										'percent_display' => round( ($branch_percent*100),2),
										'gross_pay' => number_format($user_totals['gross_pay'], 2, '.','')
										);

				$rows[] = array(
																		'id' => $branch_id,
																		'name' => $branch_obj->getName(),
																		'percent' => $branch_percent,
																		'percent_display' => round( ($branch_percent*100),2),
																		'users' => $user_entries[$branch_id]
																					);

				unset($branch_obj, $branch_percent, $user_totals);

			}
		}
		unset($branch_ids);
		//var_dump($rows);
/*
		if ( isset($tmp_rows) ) {
			foreach($tmp_rows as $row) {
				$rows[] = $row;
			}
			$rows = Sort::Multisort($rows, 'last_name');
			//var_dump($rows);

			$total_entries = Misc::ArrayAssocSum($rows, 'entries', 2);

			$rows[] = array(
							'full_name' => 'Total',
							'entries' => $total_entries
							);
		}
*/
		$smarty->assign_by_ref('rows', $rows );

		$pplf->getByCompanyId( $current_company->getId() );
		foreach ($pplf as $pay_period_obj) {
			$pay_period_ids[] = $pay_period_obj->getId();
		}

		$pplf = TTnew( 'PayPeriodListFactory' );
		$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));

		$smarty->assign_by_ref('pay_period_options', $pay_period_options);
		$smarty->assign_by_ref('pay_period_id', $pay_period_id);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('report/UserBranchSummary.tpl');
?>