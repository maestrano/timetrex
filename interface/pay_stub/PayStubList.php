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
 * $Revision: 6936 $
 * $Id: PayStubList.php 6936 2012-06-05 15:44:50Z ipso $
 * $Date: 2012-06-05 08:44:50 -0700 (Tue, 05 Jun 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('pay_stub','enabled')
		OR !( $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view_own') OR $permission->Check('pay_stub','view_child') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Pay Stub List')); // See index.php
/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'form',
												'page',
												'filter_data',
												'sort_column',
												'sort_order',
												'saved_search_id',
												'filter_user_id',
												'filter_pay_period_id',
												'hide_employer_rows',
												'export_type',
												'id',
												'ids',
												) ) );

$columns = array(
											'-1010-first_name' => TTi18n::gettext('First Name'),
											'-1020-middle_name' => TTi18n::gettext('Middle Name'),
											'-1030-last_name' => TTi18n::gettext('Last Name'),
											'-1040-status' => TTi18n::gettext('Status'),
											'-1070-start_date' => TTi18n::gettext('Start Date'),
											'-1080-end_date' => TTi18n::gettext('End Date'),
											'-1090-transaction_date' => TTi18n::gettext('Transaction Date'),
											);

if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
	//Default columns.
	if ( $permission->Check('pay_stub','view') == TRUE OR $permission->Check('pay_stub','view_child')) {
		$filter_data['columns'] = array(
									'-1010-first_name',
									'-1030-last_name',
									'-1040-status',
									'-1070-start_date',
									'-1080-end_date',
									'-1090-transaction_date',
									);
	} else {
		$filter_data['columns'] = array(
									'-1040-status',
									'-1070-start_date',
									'-1080-end_date',
									'-1090-transaction_date',
									);
	}
	if ( $sort_column == '' ) {
		$sort_column = $filter_data['sort_column'] = 'transaction_date';
		$sort_order = $filter_data['sort_order'] = 'desc';
	}
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );
$pplf = TTnew( 'PayPeriodListFactory' );

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

Debug::Text('Form: '. $form, __FILE__, __LINE__, __METHOD__,10);
//Handle different actions for different forms.

$action = Misc::findSubmitButton();
if ( isset($form) AND $form != '' ) {
	$action = strtolower($form.'_'.$action);
} else {
	$action = strtolower($action);
}
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

switch ($action) {
	case 'export':
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
			$pslf = TTnew( 'PayStubListFactory' );
			if ( $permission->Check('pay_stub','view') == FALSE ) {
				if ( $permission->Check('pay_stub','view_child') ) {
					$filter_data['permission_children_ids'] = $permission_children_ids;
				}
				if ( $permission->Check('pay_stub','view_own') ) {
					$filter_data['permission_children_ids'][] = $current_user->getId();
				}
			}
			$filter_data['id'] = $ids;

			$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );

			$output = $pslf->exportPayStub( $pslf, $export_type );
			if ( $output !== FALSE ) {
				if ( Debug::getVerbosity() < 11 ) {
					if ( stristr( $export_type, 'cheque') ) {
						Misc::FileDownloadHeader('checks_'. str_replace(array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.pdf', 'application/pdf', strlen($output));
					} else {
						//Include file creation number in the exported file name, so the user knows what it is without opening the file,
						//and can generate multiple files if they need to match a specific number.
						$ugdlf = TTnew( 'UserGenericDataListFactory' );
						$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), 'PayStubFactory', TRUE );
						if ( $ugdlf->getRecordCount() > 0 ) {
							$ugd_obj = $ugdlf->getCurrent();
							$setup_data = $ugd_obj->getData();
						}

						if ( isset($setup_data) ) {
							$file_creation_number = $setup_data['file_creation_number']++;
						} else {
							$file_creation_number = 0;
						}
						Misc::FileDownloadHeader('eft_'. $file_creation_number .'_'. str_replace(array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.txt', 'application/text', strlen($output));
					}
					echo $output;
					//Debug::Display();
					Debug::writeToLog();
					exit;
				} else {
					Debug::Display();
				}
			} else {
				echo TTi18n::gettext("ERROR: No Data to Export!")."<br>\n";
				exit;
			}
		}
		break;
	case 'view':
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
			$pslf = TTnew( 'PayStubListFactory' );

			if ( $permission->Check('pay_stub','view') == FALSE ) {
				if ( $permission->Check('pay_stub','view_child') ) {
					$filter_data['permission_children_ids'] = $permission_children_ids;
				}
				if ( $permission->Check('pay_stub','view_own') == TRUE ) {
					$hide_employer_rows = TRUE;
					$filter_data['permission_children_ids'][] = $current_user->getId();
				}
			}

			$filter_data['id'] = $ids;

			$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );

			$output = $pslf->getPayStub( $pslf, (bool)$hide_employer_rows );

			if ( $output !== FALSE AND Debug::getVerbosity() < 11 ) {
				Misc::FileDownloadHeader('pay_stub.pdf', 'application/pdf', strlen($output));
				echo $output;
				Debug::writeToLog();
				exit;
			} else {
				echo TTi18n::gettext("ERROR: Pay stub not available, you may not have permissions to view this pay stub or it may be deleted!")."<br>\n";
				exit;
			}
		}

		break;
	case 'delete':
	case 'undelete':
		//Debug::setVerbosity(11);
		Debug::Text('bAction: Delete!', __FILE__, __LINE__, __METHOD__,10);
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		if ( count($ids) == 0 ) {
			echo TTi18n::gettext("ERROR: No Items Selected!")."<br>\n";
			exit;
		}

		$pslf = TTnew( 'PayStubListFactory' );

		if ( is_array( $ids ) AND count($ids) > 0 ) {
			foreach ($ids as $id) {
				$pslf->getByCompanyIdAndId($current_company->getId(),$id);
				foreach ($pslf as $pay_stub_obj) {
					//Only delete pay stubs in OPEN/Post Adjustment pay periods.
					//Also allow deleting pay stubs attached to a pay period that has been deleted.
					//Make sure pay stub is NOT marked PAID before deleting.
					if ( ( $pay_stub_obj->getPayPeriodObject() == FALSE OR ( is_object( $pay_stub_obj->getPayPeriodObject() ) AND $pay_stub_obj->getPayPeriodObject()->getStatus() != 20 ) )
							AND $pay_stub_obj->getStatus() != 40 ) { //Closed/Paid
						$pay_stub_obj->setDeleted($delete);
						if ( $pay_stub_obj->isValid() ) {
							$pay_stub_obj->Save();
						}
					} else {
						Debug::Text('Not deleting pay stub: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					}
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( array('saved_search_id' => $saved_search_id, 'filter_pay_period_id' => $filter_pay_period_id, 'filter_user_id' => $filter_user_id), 'PayStubList.php') );

		break;
	case 'mark_paid':
		//Debug::setVerbosity(11);
		Debug::Text('bAction: Mark Paid!', __FILE__, __LINE__, __METHOD__,10);

		if ( count($ids) == 0 ) {
			echo TTi18n::gettext("ERROR: No Items Selected!")."<br>\n";
			exit;
		}

		$pslf = TTnew( 'PayStubListFactory' );

		if ( $permission->Check('pay_stub','edit') OR $permission->Check('pay_stub','edit_child') ) {
			if ( is_array( $ids ) AND count($ids) > 0 ) {
				foreach ($ids as $id) {
					$pslf->getById($id);
					foreach ($pslf as $pay_stub_obj) {
						//Only delete NEW pay stubs.!
						if ( ( $pay_stub_obj->getPayPeriodObject() == FALSE OR ( is_object( $pay_stub_obj->getPayPeriodObject() ) AND $pay_stub_obj->getPayPeriodObject()->getStatus() != 20 ) ) //Open/Adjustment
								AND ( $pay_stub_obj->getStatus() == 10 OR $pay_stub_obj->getStatus() == 25 ) ) {
							$pay_stub_obj->setStatus( 40 ); //Paid
							if ( $pay_stub_obj->isValid() ) {
								$pay_stub_obj->Save();
							}
						}
					}
				}
			}
		}

		//Redirect::Page( URLBuilder::getURL(NULL, 'PayStubList.php') );
		Redirect::Page( URLBuilder::getURL( array('saved_search_id' => $saved_search_id, 'filter_pay_period_id' => $filter_pay_period_id, 'filter_user_id' => $filter_user_id), 'PayStubList.php') );

		break;
	case 'mark_unpaid':
		//Debug::setVerbosity(11);
		Debug::Text('bAction: Mark UnPaid!', __FILE__, __LINE__, __METHOD__,10);

		if ( count($ids) == 0 ) {
			echo TTi18n::gettext("ERROR: No Items Selected!")."<br>\n";
			exit;
		}

		$pslf = TTnew( 'PayStubListFactory' );

		if ( $permission->Check('pay_stub','edit') OR $permission->Check('pay_stub','edit_child') ) {
			if ( is_array( $ids ) AND count($ids) > 0 ) {
				foreach ($ids as $id) {
					$pslf->getById($id);
					foreach ($pslf as $pay_stub_obj) {
						//Only delete pay stubs in OPEN/Post Adjustment pay periods.
						if ( ( $pay_stub_obj->getPayPeriodObject() == FALSE OR ( is_object( $pay_stub_obj->getPayPeriodObject() ) AND $pay_stub_obj->getPayPeriodObject()->getStatus() != 20 ) ) //Open/Adjustment
								AND $pay_stub_obj->getStatus() == 40 ) { //Paid/Closed
							$pay_stub_obj->setStatus( 25 ); //Open
							if ( $pay_stub_obj->isValid() ) {
								$pay_stub_obj->Save();
							}
						}
					}
				}
			}
		}

		//Redirect::Page( URLBuilder::getURL(NULL, 'PayStubList.php') );
		Redirect::Page( URLBuilder::getURL( array('saved_search_id' => $saved_search_id, 'filter_pay_period_id' => $filter_pay_period_id, 'filter_user_id' => $filter_user_id), 'PayStubList.php') );

		break;
	case 'search_form_delete':
	case 'search_form_update':
	case 'search_form_save':
	case 'search_form_clear':
	case 'search_form_search':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'PayStubList.php') );
	default:
		BreadCrumb::setCrumb($title);

		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, $sort_column ) );
		Debug::Text('Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Saved Search ID: '. $saved_search_id, __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_user_id) AND $filter_user_id != '' ) {
			$filter_data['user_id'] = $filter_user_id;
		}

		if ( isset($filter_pay_period_id) AND $filter_pay_period_id != '' ) {
			$filter_data['pay_period_id'] = $filter_pay_period_id;
		}

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],	array(
															'sort_column' => Misc::trimSortPrefix($sort_column),
															'sort_order' => $sort_order,
															'saved_search_id' => $saved_search_id,
															'page' => $page
														) );
		$pslf = TTnew( 'PayStubListFactory' );
		$ulf = TTnew( 'UserListFactory' );

		if ( $permission->Check('pay_stub','view') == FALSE ) {
			if ( $permission->Check('pay_stub','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('pay_stub','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getPayPeriodsWithPayStubsByCompanyId( $current_company->getId(), NULL, array('a.start_date' => 'desc') );
		$pay_period_options = $pplf->getArrayByListFactory( $pplf, FALSE, FALSE );
		$pay_period_ids = array_keys((array)$pay_period_options);

		//Make sure regular employees see all pay periods by default as they don't get filter criteria.
		if ( $permission->Check('pay_stub','view') == FALSE AND $permission->Check('pay_stub','view_child') == FALSE ) {
			//Only display PAID pay stubs.
			$filter_data['pay_period_id'] = -1;
			$filter_data['pay_stub_status_id'] = array(40);
		} elseif ( isset($pay_period_ids[0]) AND ( !isset($filter_data['pay_period_id']) OR $filter_data['pay_period_id'] == '' ) ) {
			$filter_data['pay_period_id'] = array($pay_period_ids[0]);
		}

		$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($pslf);

		$utlf = TTnew( 'UserTitleListFactory' );
		$utlf->getByCompanyId( $current_company->getId() );
		$title_options = $utlf->getArrayByListFactory( $utlf, FALSE, TRUE );

		$blf = TTnew( 'BranchListFactory' );
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );

		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );

		$uglf = TTnew( 'UserGroupListFactory' );
		$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) );

		foreach ($pslf as $pay_stub) {
			//Get pay period info
			$user_obj = $ulf->getById( $pay_stub->getUser() )->getCurrent();

			$pay_stubs[] = array(
								'id' => $pay_stub->getId(),
								'user_id' => $pay_stub->getUser(),
								'first_name' => $user_obj->getFirstName(),
								'middle_name' => $user_obj->getMiddleName(),
								'last_name' => $user_obj->getLastName(),
								'status_id' => $pay_stub->getStatus(),
								'status' => Option::getByKey($pay_stub->getStatus(), $pay_stub->getOptions('status') ),
								'start_date' => TTDate::getDate('DATE', $pay_stub->getStartDate() ),
								'end_date' => TTDate::getDate('DATE', $pay_stub->getEndDate() ),
								'transaction_date' => TTDate::getDate('DATE', $pay_stub->getTransactionDate() ),

								'is_owner' => $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getId() ),
								'is_child' => $permission->isChild( $user_obj->getId(), $permission_children_ids ),

								'deleted' => $pay_stub->getDeleted()
							);
			unset($start_date);
			unset($end_date);
			unset($transaction_date);
		}

		$export_type_options = Misc::trimSortPrefix( $pslf->getOptions('export_type') );

		$all_array_option = array('-1' => TTi18n::gettext('-- Any --'));

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

		//Select box options;
		$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
		$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
		$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
		$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
		$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['pay_period_options'] = Misc::prependArray( $all_array_option, $pay_period_options );

		$filter_data['saved_search_options'] = $ugdlf->getArrayByListFactory( $ugdlf->getByUserIdAndScript( $current_user->getId(), $_SERVER['SCRIPT_NAME']), FALSE );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['columns'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['columns'], $columns );

		$filter_data['sort_options'] = Misc::trimSortPrefix($columns);
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray(TRUE);

		foreach( $filter_data['columns'] as $column_key ) {
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
		}
		unset($column_key);

		$smarty->assign_by_ref('pay_stubs', $pay_stubs);
		$smarty->assign_by_ref('export_type_options', $export_type_options );
		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign('total_columns', count($filter_columns)+3 );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );
		$smarty->assign_by_ref('saved_search_id', $saved_search_id );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('pay_stub/PayStubList.tpl');
?>