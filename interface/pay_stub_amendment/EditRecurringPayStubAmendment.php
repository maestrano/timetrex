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
 * $Id: EditRecurringPayStubAmendment.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('pay_stub_amendment','enabled')
		OR !( $permission->Check('pay_stub_amendment','edit') OR $permission->Check('pay_stub_amendment','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Recurring Pay Stub Amendment')); // See index.php

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
	if ( $pay_stub_amendment_data['start_date'] != '' ) {
		$pay_stub_amendment_data['start_date'] = TTDate::parseDateTime($pay_stub_amendment_data['start_date']);
	}
	if ( $pay_stub_amendment_data['end_date'] != '' ) {
		$pay_stub_amendment_data['end_date'] = TTDate::parseDateTime($pay_stub_amendment_data['end_date']);
	}

}

$rpsaf = TTnew( 'RecurringPayStubAmendmentFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'recalculate':
		//Debug::setVerbosity(11);
		$rpsalf = TTnew( 'RecurringPayStubAmendmentListFactory' );
		$rpsalf->getById( $pay_stub_amendment_data['id'] );
		if ( $rpsalf->getRecordCount() > 0 ) {
			$rpsa_obj = $rpsalf->getCurrent();
			$rpsa_obj->createPayStubAmendments();
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringPayStubAmendmentList.php') );

		break;
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$rpsaf->setId($pay_stub_amendment_data['id']);
		$rpsaf->setCompany( $current_company->getId() );

		$rpsaf->setStatus( $pay_stub_amendment_data['status_id'] );

		$rpsaf->setName( $pay_stub_amendment_data['name'] );
		$rpsaf->setDescription( $pay_stub_amendment_data['description'] );

		$rpsaf->setStartDate( $pay_stub_amendment_data['start_date'] );
		if ( $pay_stub_amendment_data['end_date'] != '' ) {
			$rpsaf->setEndDate( $pay_stub_amendment_data['end_date'] );
		}
		$rpsaf->setFrequency( $pay_stub_amendment_data['frequency_id'] );

		$rpsaf->setPayStubEntryNameId($pay_stub_amendment_data['pay_stub_entry_name_id']);

		$rpsaf->setType( $pay_stub_amendment_data['type_id'] );

		if ( $pay_stub_amendment_data['type_id'] == 10 ) {
			Debug::Text('Fixed Amount!', __FILE__, __LINE__, __METHOD__,10);
			$rpsaf->setRate($pay_stub_amendment_data['rate']);
			$rpsaf->setUnits($pay_stub_amendment_data['units']);
			if ( isset($pay_stub_amendment_data['amount']) ) {
				$rpsaf->setAmount($pay_stub_amendment_data['amount']);
			}
		} else {
			Debug::Text('Percent Amount!', __FILE__, __LINE__, __METHOD__,10);
			$rpsaf->setPercentAmount($pay_stub_amendment_data['percent_amount']);
			$rpsaf->setPercentAmountEntryNameID($pay_stub_amendment_data['percent_amount_entry_name_id']);
		}

		$rpsaf->setPayStubAmendmentDescription($pay_stub_amendment_data['ps_amendment_description']);

		if ( $rpsaf->isValid() ) {
			$rpsaf->Save(FALSE);

			if ( isset($pay_stub_amendment_data['user_ids']) ) {
				$rpsaf->setUser( $pay_stub_amendment_data['user_ids'] );
			} else {
				$rpsaf->setUser( array() );
			}

			$rpsaf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'RecurringPayStubAmendmentList.php') );

			break;
		}
	default:
		BreadCrumb::setCrumb($title);

		if ( isset($id) ) {
			$rpsalf = TTnew( 'RecurringPayStubAmendmentListFactory' );

			//$uwlf->GetByUserIdAndCompanyId($current_user->getId(), $current_company->getId() );
			$rpsalf->GetById($id);

			foreach ($rpsalf as $recurring_pay_stub_amendment) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				//$user_id = $recurring_pay_stub_amendment->getUser();
				$amount_type_id = 10;
				if ( $recurring_pay_stub_amendment->getPercentAmount() > 0 ) {
					$amount_type_id = 20;
				}

				$pay_stub_amendment_data = array(
									'id' => $recurring_pay_stub_amendment->getId(),
									'company_id' => $recurring_pay_stub_amendment->getCompany(),

									'status_id'	=> $recurring_pay_stub_amendment->getStatus(),

									'name' => $recurring_pay_stub_amendment->getName(),
									'description' => $recurring_pay_stub_amendment->getDescription(),

									'start_date' => $recurring_pay_stub_amendment->getStartDate(),
									'end_date' => $recurring_pay_stub_amendment->getEndDate(),
									'frequency_id' => $recurring_pay_stub_amendment->getFrequency(),

									'user_ids' => $recurring_pay_stub_amendment->getUser(),

									'type_id' => $recurring_pay_stub_amendment->getType(),
									'pay_stub_entry_name_id' => $recurring_pay_stub_amendment->getPayStubEntryNameId(),

									'amount_type_id' => $amount_type_id,

									'rate' => $recurring_pay_stub_amendment->getRate(),
									'units' => $recurring_pay_stub_amendment->getUnits(),
									'amount' => $recurring_pay_stub_amendment->getAmount(),

									'percent_amount' => $recurring_pay_stub_amendment->getPercentAmount(),
									'percent_amount_entry_name_id' => $recurring_pay_stub_amendment->getPercentAmountEntryNameId(),

									'ps_amendment_description' => $recurring_pay_stub_amendment->getPayStubAmendmentDescription(),

									'created_date' => $recurring_pay_stub_amendment->getCreatedDate(),
									'created_by' => $recurring_pay_stub_amendment->getCreatedBy(),
									'updated_date' => $recurring_pay_stub_amendment->getUpdatedDate(),
									'updated_by' => $recurring_pay_stub_amendment->getUpdatedBy(),
									'deleted_date' => $recurring_pay_stub_amendment->getDeletedDate(),
									'deleted_by' => $recurring_pay_stub_amendment->getDeletedBy()
								);
			}
		} else {
			if ( $pay_stub_amendment_data['start_date'] == '' ) {
				$pay_stub_amendment_data['start_date'] = TTDate::getTime();
			}
		}

		//Select box options;
		$status_options_filter = array(50,60);
		/*
		if ( isset($pay_stub_amendment) AND $pay_stub_amendment->getStatus() == 55 ) {
			$status_options_filter = array(55);
		} elseif ( isset($pay_stub_amendment) AND $pay_stub_amendment->getStatus() == 52 ) {
			$status_options_filter = array(52);
		}
		*/
		$status_options = Option::getByArray( $status_options_filter, $rpsaf->getOptions('status') );
		$pay_stub_amendment_data['status_options'] = $status_options;

		$frequency_options = $rpsaf->getOptions('frequency');
		$pay_stub_amendment_data['frequency_options'] = $frequency_options;

		$percent_amount_options = $rpsaf->getOptions('percent_amount');
		$pay_stub_amendment_data['percent_amount_options'] = $percent_amount_options;

		$pay_stub_amendment_data['type_options'] = $rpsaf->getOptions('type');

		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
		$pseallf->getByCompanyId( $current_company->getId() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$net_pay_psea_id = $pseal_obj = $pseallf->getCurrent()->getTotalNetPay();
		}

		//$psenlf = TTnew( 'PayStubEntryNameListFactory' );

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$pay_stub_amendment_data['pay_stub_entry_name_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50,60,65) );
		$pay_stub_amendment_data['percent_amount_entry_name_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,60,65) );
		if ( isset($net_pay_psea_id) ) {
			unset($pay_stub_amendment_data['percent_amount_entry_name_options'][$net_pay_psea_id]);
		}

		$smarty->assign_by_ref('pay_stub_amendment_data', $pay_stub_amendment_data);

		$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		$user_options = Misc::prependArray( array( -1 => TTi18n::gettext('-- ALL --')), $user_options );
		$pay_stub_amendment_data['user_options'] = $user_options;

		if ( isset($pay_stub_amendment_data['user_ids']) AND is_array($pay_stub_amendment_data['user_ids']) ) {
			$tmp_user_options = $user_options;
			foreach( $pay_stub_amendment_data['user_ids'] as $user_id ) {
				if( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		break;
}

$smarty->assign_by_ref('rpsaf', $rpsaf);

$smarty->display('pay_stub_amendment/EditRecurringPayStubAmendment.tpl');
?>