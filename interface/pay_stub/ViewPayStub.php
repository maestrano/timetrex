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
 * $Id: ViewPayStub.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('pay_stub','enabled')
		OR !( $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Employee Pay Stub')); // See index.php
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
												'hide_employer_rows',
												'id',
												'ids'
												) ) );

switch ($action) {
	default:
		if ( isset($id) AND !isset($ids) ) {
			$ids = array($id);
		}

		if ( count($ids) > 0 ) {
			$pslf = TTnew( 'PayStubListFactory' );
			if ( $permission->Check('pay_stub','view') ) {
				$pslf->getByCompanyIdAndId( $current_company->getId(), $ids);
			} else {
				$pslf->getByUserIdAndId( $current_user->getId(), $ids);
			}

			//foreach ($ids as $id) {
			$i=0;
			foreach ($pslf as $pay_stub_obj) {
				$psealf = TTnew( 'PayStubEntryAccountListFactory' );

				//Get pay stub entries.
				$pself = TTnew( 'PayStubEntryListFactory' );
				$pself->getByPayStubId( $pay_stub_obj->getId() );
				Debug::text('Pay Stub Entries: '. $pself->getRecordCount()  , __FILE__, __LINE__, __METHOD__,10);

				$prev_type = NULL;
				$description_subscript_counter = 1;
				foreach ($pself as $pay_stub_entry) {
					Debug::text('Pay Stub Entry Account ID: '.$pay_stub_entry->getPayStubEntryNameId()  , __FILE__, __LINE__, __METHOD__,10);
					$description_subscript = NULL;

					//$pay_stub_entry_name_obj = $psenlf->getById( $pay_stub_entry->getPayStubEntryNameId() ) ->getCurrent();
					$pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() ) ->getCurrent();

					//Use this to put the total for each type at the end of the array.
					if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {
						$type = $pay_stub_entry_name_obj->getType();
					}
					//Debug::text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_obj->getId() .' Type ID: '. $pay_stub_entry_name_obj->getType() .' Type: '. $type, __FILE__, __LINE__, __METHOD__,10);

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
												'type' => $pay_stub_entry_name_obj->getType(),
												'name' => $pay_stub_entry_name_obj->getName(),
												'display_name' => $pay_stub_entry_name_obj->getName(),
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
					//Only for net pay, make a total YTD of Advance plus Net.
					/*
					if ( $type == 40 ) {
						$pay_stub_entries[$type][0]['ytd_net_plus_advance'] =
					}
					*/

					$prev_type = $pay_stub_entry_name_obj->getType();
				}

				$pay_stub = array(
									'id' => $pay_stub_obj->getId(),
									'display_id' => str_pad($pay_stub_obj->getId(),12,0, STR_PAD_LEFT),
									'user_id' => $pay_stub_obj->getUser(),
									'pay_period_id' => $pay_stub_obj->getPayPeriod(),
									'start_date' => $pay_stub_obj->getStartDate(),
									'end_date' => $pay_stub_obj->getEndDate(),
									'transaction_date' => $pay_stub_obj->getTransactionDate(),
									'advance' => $pay_stub_obj->getAdvance(),
									'status' => $pay_stub_obj->getStatus(),
									'entries' => $pay_stub_entries,
									'tainted' => $pay_stub_obj->getTainted(),

									'created_date' => $pay_stub_obj->getCreatedDate(),
									'created_by' => $pay_stub_obj->getCreatedBy(),
									'updated_date' => $pay_stub_obj->getUpdatedDate(),
									'updated_by' => $pay_stub_obj->getUpdatedBy(),
									'deleted_date' => $pay_stub_obj->getDeletedDate(),
									'deleted_by' => $pay_stub_obj->getDeletedBy()
								);
				unset($pay_stub_entries);

				Debug::text($i .'. Pay Stub Transaction Date: '. $pay_stub_obj->getTransactionDate(), __FILE__, __LINE__, __METHOD__,10);

				//Get Pay Period information
				$pplf = TTnew( 'PayPeriodListFactory' );
				$pay_period_obj = $pplf->getById( $pay_stub_obj->getPayPeriod() )->getCurrent();

				if ( $pay_stub_obj->getAdvance() == TRUE ) {
					$pp_start_date = $pay_period_obj->getStartDate();
					$pp_end_date = $pay_period_obj->getAdvanceEndDate();
					$pp_transaction_date = $pay_period_obj->getAdvanceTransactionDate();
				} else {
					$pp_start_date = $pay_period_obj->getStartDate();
					$pp_end_date = $pay_period_obj->getEndDate();
					$pp_transaction_date = $pay_period_obj->getTransactionDate();
				}

				//Get pay period numbers
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
				$pay_period_schedule_obj = $ppslf->getById( $pay_period_obj->getPayPeriodSchedule() )->getCurrent();


				$pay_period_data = array(
										'advance' => $pay_stub_obj->getAdvance(),
										'start_date' => TTDate::getDate('DATE', $pp_start_date ),
										'end_date' => TTDate::getDate('DATE', $pp_end_date ),
										'transaction_date' => TTDate::getDate('DATE', $pp_transaction_date ),
										//'pay_period_number' => $pay_period_schedule_obj->getCurrentPayPeriodNumber( $pay_period_obj->getTransactionDate(), $pay_period_obj->getEndDate() ),
										'annual_pay_periods' => $pay_period_schedule_obj->getAnnualPayPeriods()
										);

				//Get User information
				$ulf = TTnew( 'UserListFactory' );
				$user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();

				//Get company information
				$clf = TTnew( 'CompanyListFactory' );
				$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();

				//}

				//Figure out how much white space we need to fill the entire page.
				$max_rows = 29; //With borders you gotta drop this down to 28.
				$total_rows = floor( $pself->getRecordCount() + $description_subscript_counter );
				if ( $pay_stub_obj->getAdvance() === FALSE ) {
					//$total_rows -= 1;
					$total_rows += 1;
				}
				if ($description_subscript_counter > 1) {
					$total_rows += 2;
				}

				$max_types = 6;
				$total_types = count($pay_stub['entries']);

				$spacer_rows = ($max_rows - $total_rows) + ( ($max_types - $total_types) * 2);
				if ($spacer_rows < 0) {
					$spacer_rows = 0;
				}
				/*
				echo "Description Subscript counter: $description_subscript_counter<br>\n";
				echo "Total Rows: $total_rows<Br>\n";
				echo "Total Types: $total_types<Br>\n";
				echo "Spacer Rows: $spacer_rows<Br>\n";
				*/
				$smarty->assign_by_ref('spacer_rows', $spacer_rows );

				$smarty->assign_by_ref('pay_stub', $pay_stub);

				$smarty->assign_by_ref('company_obj', $company_obj);
				$smarty->assign_by_ref('user_obj', $user_obj);
				$smarty->assign_by_ref('pay_period_data', $pay_period_data);

				$smarty->assign_by_ref('pay_stub_entry_descriptions', $pay_stub_entry_descriptions);
				unset($pay_stub_entry_descriptions);

				$smarty->assign_by_ref('hide_employer_rows', $hide_employer_rows );

				$smarty->assign_by_ref('sort_column', $sort_column );
				$smarty->assign_by_ref('sort_order', $sort_order );

				//$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

				//If we're viewing a PDF, just change this.
				//$smarty->display('pay_stub/ViewPayStub.tpl');

				$pay_stub_html[str_pad($i,4,0,STR_PAD_LEFT)] = $smarty->fetch('pay_stub/ViewPDFPayStub.tpl');
				//$smarty->display('pay_stub/ViewPDFPayStub.tpl');
				//exit;

				$i++;
			}
		}

		break;
}

if ( isset($pay_stub_html) AND count($pay_stub_html) > 0 ) {

	$dir = '/tmp/'.uniqid('pay_stub_').'/';
	mkdir( $dir );
	foreach ($pay_stub_html as $id => $data) {
		$filename = $dir.'/'.$id;
		//echo "FileName: $filename<br>\n";
		if ( file_put_contents($filename.'.html', $data) > 0 ) {
			//echo "Writing file successfull<Br>\n";
		} else {
			//echo "Error writing file<Br>\n";
			exit;
		}
	}

	//Convert to PDF
	$cmd = 'htmldoc --no-title --footer . --left 20mm --right 20mm --bottom 5mm --top 5mm -f '. $dir .'out.pdf --webpage '. $dir .'*.html';
	//echo "Cmd: $cmd<br>\n";
	exec($cmd, $output, $retval);
	unset($output);
	//echo "Retval: $retval<br>\n";

	//Open PDF and display
	$pdf = file_get_contents($dir.'out.pdf');
	if ($pdf === FALSE) {
		//echo "Error reading PDF<br>\n";
		exit;
	}

	Misc::FileDownloadHeader('pay_stub.pdf', 'application/pdf', strlen($pdf));
	echo $pdf;

	//Delete tmp files.
	foreach(glob($dir.'*') as $filename) {
		unlink($filename);
	}
	rmdir($dir);
} else {
	echo TTi18n::gettext("ERROR: No Items Selected!")."<br>\n";
}

//Debug::Display();
?>