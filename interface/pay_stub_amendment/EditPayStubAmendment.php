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
 * $Revision: 10417 $
 * $Id: EditPayStubAmendment.php 10417 2013-07-11 22:21:13Z ipso $
 * $Date: 2013-07-11 15:21:13 -0700 (Thu, 11 Jul 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('pay_stub_amendment','enabled')
		OR !( $permission->Check('pay_stub_amendment','edit') OR $permission->Check('pay_stub_amendment','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Pay Stub Amendment')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'pay_stub_amendment_data'
												) ) );
if ( isset($pay_stub_amendment_data) ) {
	if ( $pay_stub_amendment_data['effective_date'] != '' ) {
		$pay_stub_amendment_data['effective_date'] = TTDate::parseDateTime($pay_stub_amendment_data['effective_date']);
	}
}

$psaf = TTnew( 'PayStubAmendmentFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$psaf->StartTransaction();

		$fail_transaction = FALSE;
		if ( isset($pay_stub_amendment_data['filter_user_id']) AND is_array($pay_stub_amendment_data['filter_user_id']) AND count($pay_stub_amendment_data['filter_user_id']) > 0 ) {
			foreach( $pay_stub_amendment_data['filter_user_id'] as $user_id ) {
				$psaf->setId($pay_stub_amendment_data['id']);
				$psaf->setUser( $user_id );
				$psaf->setPayStubEntryNameId($pay_stub_amendment_data['pay_stub_entry_name_id']);
				$psaf->setStatus($pay_stub_amendment_data['status_id']);

				$psaf->setType( $pay_stub_amendment_data['type_id'] );

				if ( $pay_stub_amendment_data['type_id'] == 10 ) {
					$psaf->setRate($pay_stub_amendment_data['rate']);
					$psaf->setUnits($pay_stub_amendment_data['units']);
					if ( isset($pay_stub_amendment_data['amount']) ) {
						$psaf->setAmount($pay_stub_amendment_data['amount']);
					}
				} else {
					$psaf->setPercentAmount( $pay_stub_amendment_data['percent_amount'] );
					$psaf->setPercentAmountEntryNameId( $pay_stub_amendment_data['percent_amount_entry_name_id'] );
				}

				if ( isset($pay_stub_amendment_data['ytd_adjustment']) ) {
					$psaf->setYTDAdjustment(TRUE);
				} else {
					$psaf->setYTDAdjustment(FALSE);
				}

				$psaf->setDescription($pay_stub_amendment_data['description']);
				$psaf->setPrivateDescription($pay_stub_amendment_data['private_description']);

				$psaf->setEffectiveDate( $pay_stub_amendment_data['effective_date'] );

				//Authorize them all for now.
				$psaf->setAuthorized(TRUE);

				if ( $psaf->isValid() ) {
					if ( $psaf->Save() === FALSE ) {
						$fail_transaction = TRUE;
						break;
					}
				} else {
					$fail_transaction = TRUE;
					break;
				}
			}
		} else {
			$fail_transaction = TRUE;
			$psaf->Validator->isTrue(	'user_id',
									FALSE,
									TTi18n::gettext('No employees selected') );
		}

		if ( $fail_transaction == FALSE ) {
			//$pf->FailTransaction();
			$psaf->CommitTransaction();

			Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id), 'PayStubAmendmentList.php') );
			break;
		} else {
			$psaf->FailTransaction();
		}
	default:
		BreadCrumb::setCrumb($title);

		if ( isset($id) ) {
			$psalf = TTnew( 'PayStubAmendmentListFactory' );

			//$uwlf->GetByUserIdAndCompanyId($current_user->getId(), $current_company->getId() );
			$psalf->GetById($id);

			foreach ($psalf as $pay_stub_amendment) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$user_id = $pay_stub_amendment->getUser();

				$pay_stub_amendment_data = array(
									'id' => $pay_stub_amendment->getId(),
									'filter_user_id' => $pay_stub_amendment->getUser(),
									'pay_stub_entry_name_id' => $pay_stub_amendment->getPayStubEntryNameId(),
									'status_id'	=> $pay_stub_amendment->getStatus(),
									'effective_date' => $pay_stub_amendment->getEffectiveDate(),

									'type_id' => $pay_stub_amendment->getType(),

									'rate' => $pay_stub_amendment->getRate(),
									'units' => $pay_stub_amendment->getUnits(),
									'amount' => $pay_stub_amendment->getAmount(),

									'percent_amount' => $pay_stub_amendment->getPercentAmount(),
									'percent_amount_entry_name_id' => $pay_stub_amendment->getPercentAmountEntryNameId(),

									'description' => $pay_stub_amendment->getDescription(),
									'private_description' => $pay_stub_amendment->getPrivateDescription(),

									'authorized' => $pay_stub_amendment->getAuthorized(),
									'ytd_adjustment' => $pay_stub_amendment->getYTDAdjustment(),

									'created_date' => $pay_stub_amendment->getCreatedDate(),
									'created_by' => $pay_stub_amendment->getCreatedBy(),
									'updated_date' => $pay_stub_amendment->getUpdatedDate(),
									'updated_by' => $pay_stub_amendment->getUpdatedBy(),
									'deleted_date' => $pay_stub_amendment->getDeletedDate(),
									'deleted_by' => $pay_stub_amendment->getDeletedBy()
								);
			}
		} else {
			if ( $pay_stub_amendment_data['effective_date'] == '' ) {
				$pay_stub_amendment_data['effective_date'] = TTDate::getTime();
				$pay_stub_amendment_data['user_id'] = $user_id;
			}
		}

		//Select box options;
		$status_options_filter = array(50);
		if ( isset($pay_stub_amendment) AND $pay_stub_amendment->getStatus() == 55 ) {
			$status_options_filter = array(55);
		} elseif ( isset($pay_stub_amendment) AND $pay_stub_amendment->getStatus() == 52 ) {
			$status_options_filter = array(52);
		}

		if ( !isset($pay_stub_amendment_data['filter_user_id']) ) {
			$pay_stub_amendment_data['filter_user_id'] = array();
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), NULL );
		$src_user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, FALSE );

		$user_options = Misc::arrayDiffByKey( (array)$pay_stub_amendment_data['filter_user_id'], $src_user_options );
		$filter_user_options = Misc::arrayIntersectByKey( (array)$pay_stub_amendment_data['filter_user_id'], $src_user_options );


		$status_options = Option::getByArray( $status_options_filter, $psaf->getOptions('status') );
		$pay_stub_amendment_data['status_options'] = $status_options;

		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
		$pseallf->getByCompanyId( $current_company->getId() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$net_pay_psea_id = $pseallf->getCurrent()->getTotalNetPay();
		}

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$pay_stub_amendment_data['pay_stub_entry_name_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50,60,65) );
		$pay_stub_amendment_data['percent_amount_entry_name_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,60,65) );
		if ( isset($net_pay_psea_id) ) {
			unset($pay_stub_amendment_data['percent_amount_entry_name_options'][$net_pay_psea_id]);
		}
		//$pay_stub_amendment_data['pay_stub_entry_name_options'] = $psenlf->getByTypeIdArray( array(10,20,30,35) );

		//$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), TRUE );
		$pay_stub_amendment_data['user_options'] = $user_options;
		$pay_stub_amendment_data['filter_user_options'] = $filter_user_options;
		$pay_stub_amendment_data['type_options'] = $psaf->getOptions('type');

		$smarty->assign_by_ref('pay_stub_amendment_data', $pay_stub_amendment_data);
		break;
}

$smarty->assign_by_ref('psaf', $psaf);

$smarty->display('pay_stub_amendment/EditPayStubAmendment.tpl');
?>