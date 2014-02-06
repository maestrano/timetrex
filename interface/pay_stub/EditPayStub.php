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
 * $Revision: 6085 $
 * $Id: EditPayStub.php 6085 2012-01-20 22:46:06Z ipso $
 * $Date: 2012-01-20 14:46:06 -0800 (Fri, 20 Jan 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('pay_stub','enabled')
		OR !( $permission->Check('pay_stub','edit') OR $permission->Check('pay_stub','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Employee Pay Stub')); // See index.php
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
												'data',
												'id',
												'filter_pay_period_id',
												'modified_entry',
												) ) );


if ( isset($data) ) {
	$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
	$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
	$data['transaction_date'] = TTDate::parseDateTime( $data['transaction_date'] );
}
$modified_entry = (int)$modified_entry;

$psf = TTnew( 'PayStubFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ( $action ) {
	case 'submit':
		//Debug::setVerbosity(11);

		/*

			Add pay_stub_amendment_id to the pay_stub_entry table, so we can link them back.
			Disable editing entries from pay stub amendments.

			Modified pay stub entries get deleted, and new ones are inserted? This will keep
			a history of edits?

		*/
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		if ( isset($id) ) {
			$pslf = TTnew( 'PayStubListFactory' );

			$psf = $pslf->getByID( $id )->getCurrent();
			$psf->StartTransaction();

			$psf->setCurrency( $data['currency_id'] );
			$psf->setStartDate( $data['start_date'] );
			$psf->setEndDate( $data['end_date'] );
			$psf->setTransactionDate( $data['transaction_date'] );

			$psf->setStatus( $data['status_id'] );
			$psf->setTainted(TRUE); //So we know it was modified.

			if ( $modified_entry == 1 AND isset($data['entries']) ) {
				Debug::Text(' Found modified entries!', __FILE__, __LINE__, __METHOD__,10);

				//Load previous pay stub
				$psf->loadPreviousPayStub();

				//Delete all entries, so they can be re-added.
				$psf->deleteEntries( TRUE );

				//When editing pay stubs we can't re-process linked accruals.
				$psf->setEnableLinkedAccruals( FALSE );

				foreach($data['entries'] as $pay_stub_entry_type_id => $pay_stub_entry_arr ) {
					foreach($pay_stub_entry_arr as $pay_stub_entry_id => $pay_stub_entry ) {
						if ( $pay_stub_entry['type'] != 40 ) {
							Debug::Text('Pay Stub Entry ID: '. $pay_stub_entry_id , __FILE__, __LINE__, __METHOD__,10);
							Debug::Text(' Amount: '. $pay_stub_entry['amount'] , __FILE__, __LINE__, __METHOD__,10);

							$pself = TTnew( 'PayStubEntryListFactory' );
							$pay_stub_entry_obj = $pself->getById( $pay_stub_entry_id )->getCurrent();

							if ( !isset($pay_stub_entry['units']) OR $pay_stub_entry['units'] == '' ) {
								$pay_stub_entry['units'] = 0;
							}
							if ( !isset($pay_stub_entry['rate']) OR $pay_stub_entry['rate'] == '' ) {
								$pay_stub_entry['rate'] = 0;
							}
							if ( !isset($pay_stub_entry['description']) OR $pay_stub_entry['description'] == '' ) {
								$pay_stub_entry['description'] = NULL;
							}
							if ( !isset($pay_stub_entry['pay_stub_amendment_id']) OR $pay_stub_entry['pay_stub_amendment_id'] == '' ) {
								$pay_stub_entry['pay_stub_amendment_id'] = NULL;
							}

							$psamlf = TTNew('PayStubAmendmentListFactory');
							$psamlf->getByIdAndCompanyId( $pay_stub_entry['pay_stub_amendment_id'], $current_company->getId() );
							if ( $psamlf->getRecordCount() > 0 ) {
								$ytd_adjustment = $psamlf->getCurrent()->getYTDAdjustment();
							} else {
								$ytd_adjustment = FALSE;
							}
							Debug::Text(' Pay Stub Amendment Id: '. $pay_stub_entry['pay_stub_amendment_id'] .' YTD Adjusment: '. (int)$ytd_adjustment, __FILE__, __LINE__, __METHOD__,10);

							$psf->addEntry( $pay_stub_entry_obj->getPayStubEntryNameId(), $pay_stub_entry['amount'], $pay_stub_entry['units'], $pay_stub_entry['rate'], $pay_stub_entry['description'], $pay_stub_entry['pay_stub_amendment_id'], NULL, NULL, $ytd_adjustment );
						} else {
							Debug::Text(' Skipping Total Entry. ', __FILE__, __LINE__, __METHOD__,10);
						}
					}
				}
				unset($pay_stub_entry_id, $pay_stub_entry);

				$psf->setEnableCalcYTD( TRUE );
				$psf->setEnableProcessEntries( TRUE );
				$psf->processEntries();
			}

			Debug::Text(' Saving pay stub ', __FILE__, __LINE__, __METHOD__,10);
			//Can't check isValid here, because preSave hasn't been called.
			if ( $psf->isValid() ) {
				if ( $psf->Save() ) {
					//$psf->FailTransaction();

					$psf->CommitTransaction();

					//Redirect::Page( URLBuilder::getURL( array('action' => 'recalculate_paystub_ytd', 'pay_stub_ids' => array($id), 'next_page' => urlencode( URLBuilder::getURL( array('filter_pay_period_id' => $filter_pay_period_id ), '../pay_stub/PayStubList.php') ) ), '../progress_bar/ProgressBarControl.php') );
					Redirect::Page( URLBuilder::getURL( array('filter_pay_period_id' => $filter_pay_period_id ), '../pay_stub/PayStubList.php') );

					break;
				} else {
					$psf->FailTransaction();
				}
			}

		}
	default:
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		if ( $id != '' AND $action != 'submit' ) {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' );
			$pslf = TTnew( 'PayStubListFactory' );

			$pslf->getByCompanyIdAndId( $current_company->getId(), $id );
			if ( $pslf->getRecordCount() > 0 ) {
				foreach ($pslf as $ps_obj) {
					//Get pay stub entries.
					$pself = TTnew( 'PayStubEntryListFactory' );
					$pself->getByPayStubId( $ps_obj->getId() );

					$prev_type = NULL;
					$description_subscript_counter = 1;
					$pay_stub_entries = NULL;
					$pay_stub_entry_descriptions = NULL;
					foreach ($pself as $pay_stub_entry) {
						$description_subscript = NULL;
						$pay_stub_entry_account_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();

						if ( $prev_type == 40 OR $pay_stub_entry_account_obj->getType() != 40 ) {
							$type = $pay_stub_entry_account_obj->getType();
						}

						//var_dump( $pay_stub_entry->getDescription() );
						if ( $pay_stub_entry->getDescription() !== NULL
								AND $pay_stub_entry->getDescription() !== FALSE
								AND strlen($pay_stub_entry->getDescription()) > 0) {
							$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,
																	'description' => $pay_stub_entry->getDescription() );

							$description_subscript = $description_subscript_counter;

							$description_subscript_counter++;
						}

						$pay_stub_entries[$type][] = array(
													'id' => $pay_stub_entry->getId(),
													'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),
													'pay_stub_amendment_id' => $pay_stub_entry->getPayStubAmendment(),
													'tmp_type' => $type,
													'type' => $pay_stub_entry_account_obj->getType(),
													'name' => $pay_stub_entry_account_obj->getName(),
													'display_name' => TTi18n::gettext($pay_stub_entry_account_obj->getName()),
													'rate' => $pay_stub_entry->getRate(),
													'units' => $pay_stub_entry->getUnits(),
													'ytd_units' => $pay_stub_entry->getYTDUnits(),
													'amount' => $pay_stub_entry->getAmount(),
													'ytd_amount' => $pay_stub_entry->getYTDAmount(),

													'description' => $pay_stub_entry->getDescription(),
													'description_subscript' => $description_subscript,

													'created_date' => $pay_stub_entry->getCreatedDate(),
													'created_by' => $pay_stub_entry->getCreatedBy(),
													'updated_date' => $pay_stub_entry->getUpdatedDate(),
													'updated_by' => $pay_stub_entry->getUpdatedBy(),
													'deleted_date' => $pay_stub_entry->getDeletedDate(),
													'deleted_by' => $pay_stub_entry->getDeletedBy()
													);
						$prev_type = $pay_stub_entry_account_obj->getType();
					}
					//var_dump($pay_stub_entries);

					$data = array(
										'id' => $ps_obj->getId(),
										'display_id' => str_pad($ps_obj->getId(),12,0, STR_PAD_LEFT),
										'user_id' => $ps_obj->getUser(),
										'pay_period_id' => $ps_obj->getPayPeriod(),
										'currency_id' => $ps_obj->getCurrency(),
										'start_date' => $ps_obj->getStartDate(),
										'end_date' => $ps_obj->getEndDate(),
										'transaction_date' => $ps_obj->getTransactionDate(),
										//'advance' => $ps_obj->getAdvance(),
										'status_id' => $ps_obj->getStatus(),
										'entries' => $pay_stub_entries,
										'entry_descriptions' => $pay_stub_entry_descriptions,

										'created_date' => $ps_obj->getCreatedDate(),
										'created_by' => $ps_obj->getCreatedBy(),
										'updated_date' => $ps_obj->getUpdatedDate(),
										'updated_by' => $ps_obj->getUpdatedBy(),
										'deleted_date' => $ps_obj->getDeletedDate(),
										'deleted_by' => $ps_obj->getDeletedBy()
									);
					unset($pay_stub_entries, $pay_stub_entry_descriptions);

					//Get Pay Period information
					$pplf = TTnew( 'PayPeriodListFactory' );
					$pay_period_obj = $pplf->getById( $ps_obj->getPayPeriod() )->getCurrent();

					//Get pay period numbers
					$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
					$pay_period_schedule_obj = $ppslf->getById( $pay_period_obj->getPayPeriodSchedule() )->getCurrent();


					$pay_period_data = array(
											//'advance' => $ps_obj->getAdvance(),
											'start_date' => TTDate::getDate('DATE', $pay_period_obj->getStartDate() ),
											'end_date' => TTDate::getDate('DATE',  $pay_period_obj->getEndDate() ),
											'transaction_date' => TTDate::getDate('DATE', $pay_period_obj->getTransactionDate() ),
											//'pay_period_number' => $pay_period_schedule_obj->getCurrentPayPeriodNumber( $pay_period_obj->getTransactionDate(), $pay_period_obj->getEndDate() ),
											'annual_pay_periods' => $pay_period_schedule_obj->getAnnualPayPeriods()
											);

					//Get User information
					$ulf = TTnew( 'UserListFactory' );
					$user_obj = $ulf->getById( $ps_obj->getUser() )->getCurrent();
					$data['user_full_name'] = $user_obj->getFullName();

					//Get company information
					/*
					$clf = TTnew( 'CompanyListFactory' );
					$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();
					*/
				}
			}
		}
		$pay_stub_status_options = $psf->getOptions('status');

		$data['pay_stub_status_options'] = Option::getByArray( array(25,40), $pay_stub_status_options);

		$culf = TTnew( 'CurrencyListFactory' );
        $culf->getByCompanyId( $current_company->getId() );
		$data['currency_options'] = $culf->getArrayByListFactory( $culf, FALSE, TRUE );

		//var_dump($data);
		$smarty->assign_by_ref('data', $data);
		$smarty->assign_by_ref('pay_stub_id', $id);
		$smarty->assign_by_ref('filter_pay_period_id', $filter_pay_period_id);
		$smarty->assign_by_ref('modified_entry', $modified_entry);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );


		break;
}
$smarty->assign_by_ref('psf', $psf);

$smarty->display('pay_stub/EditPayStub.tpl');
?>