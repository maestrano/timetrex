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
 * $Id: EditUserTax.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('user_tax','enabled')
		OR !( $permission->Check('user_tax','edit') OR $permission->Check('user_tax','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Employee Tax Options')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'user_id',
												'tax_data',
												'data_saved'
												) ) );

$utf = TTnew( 'UserTaxFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$utf->setId($tax_data['id']);
		$utf->setUser($tax_data['user_id']);


		if ( isset($tax_data['federal_claim']) ) {
			$utf->setFederalClaim($tax_data['federal_claim']);
		}
		if ( isset($tax_data['provincial_claim']) ) {
			$utf->setProvincialClaim($tax_data['provincial_claim']);
		}

		if ( $tax_data['federal_additional_deduction'] != '' ) {
			$utf->setFederalAdditionalDeduction($tax_data['federal_additional_deduction']);
		} else {
			$utf->setFederalAdditionalDeduction('0.00');
		}

		if ( isset($tax_data['wcb_rate']) ) {
			$utf->setWCBRate($tax_data['wcb_rate']);
		}
		if ( isset($tax_data['vacation_rate']) ) {
			$utf->setVacationRate($tax_data['vacation_rate']);
		}

		if ( isset($tax_data['release_vacation']) ) {
			$utf->setReleaseVacation($tax_data['release_vacation']);
		} else {
			$utf->setReleaseVacation(FALSE);
		}

		if ( isset($tax_data['ei_exempt']) ) {
			$utf->setEIExempt($tax_data['ei_exempt']);
		} else {
			$utf->setEIExempt(FALSE);
		}

		if ( isset($tax_data['cpp_exempt']) ) {
			$utf->setCPPExempt($tax_data['cpp_exempt']);
		} else {
			$utf->setCPPExempt(FALSE);
		}

		if ( isset($tax_data['federal_tax_exempt']) ) {
			$utf->setFederalTaxExempt($tax_data['federal_tax_exempt']);
		} else {
			$utf->setFederalTaxExempt(FALSE);
		}

		if ( isset($tax_data['provincial_tax_exempt']) ) {
			$utf->setProvincialTaxExempt($tax_data['provincial_tax_exempt']);
		} else {
			$utf->setProvincialTaxExempt(FALSE);
		}

		if ( isset($tax_data['federal_filing_status']) ) {
			$utf->setFederalFilingStatus( $tax_data['federal_filing_status'] );
		}

		if ( isset($tax_data['state_filing_status']) ) {
			$utf->setStateFilingStatus( $tax_data['state_filing_status'] );
		}

		if ( isset($tax_data['federal_allowance']) ) {
			$utf->setFederalAllowance( $tax_data['federal_allowance'] );
		}

		if ( isset($tax_data['federal_exemption']) ) {
			$utf->setFederalExemption( $tax_data['federal_exemption'] );
		}

		if ( isset($tax_data['state_allowance']) ) {
			$utf->setStateAllowance( $tax_data['state_allowance'] );
		}

		if ( isset($tax_data['state_additional_deduction']) ) {
			$utf->setStateAdditionalDeduction( $tax_data['state_additional_deduction'] );
		}

		if ( isset($tax_data['state_ui_rate']) ) {
			$utf->setStateUIRate( $tax_data['state_ui_rate'] );
		}

		if ( isset($tax_data['state_ui_wage_base']) ) {
			$utf->setStateUIWageBase( $tax_data['state_ui_wage_base'] );
		}

		if ( isset($tax_data['social_security_exempt']) ) {
			$utf->setSocialSecurityExempt( $tax_data['social_security_exempt'] );
		} else {
			$utf->setSocialSecurityExempt( FALSE );
		}

		if ( isset($tax_data['ui_exempt']) ) {
			$utf->setUIExempt( $tax_data['ui_exempt'] );
		} else {
			$utf->setUIExempt( FALSE );
		}

		if ( isset($tax_data['medicare_exempt']) ) {
			$utf->setMedicareExempt( $tax_data['medicare_exempt'] );
		} else {
			$utf->setMedicareExempt( FALSE );
		}

		if ( $utf->isValid() ) {
			$utf->Save();

			Redirect::Page( URLBuilder::getURL( array('user_id' => $tax_data['user_id'], 'data_saved' => TRUE), 'EditUserTax.php') );
			//Redirect::Page( URLBuilder::getURL( NULL , 'UserList.php') );

			break;
		}
	default:
		if ( isset($user_id) AND $action != 'submit') {
			unset($tax_data);

			BreadCrumb::setCrumb($title);

			Debug::Text('User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);

			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
			if ($ulf->getRecordCount() > 0 ) {
				$user_obj = $ulf->getCurrent();
			}

			$utlf = TTnew( 'UserTaxListFactory' );

			//$uwlf->GetByUserIdAndCompanyId($current_user->getId(), $current_company->getId() );
			$utlf->GetByUserId($user_id);

			foreach ($utlf as $tax) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				//$user_id = $tax->getUser();

				$tax_data = array(
									'id' => $tax->getId(),
									'user_id' => $tax->getUser(),
									'country' => $user_obj->getCountry(),

									'federal_claim' => $tax->getFederalClaim(),
									'provincial_claim' => $tax->getProvincialClaim(),
									'federal_additional_deduction' => $tax->getFederalAdditionalDeduction(),
									'wcb_rate' => $tax->getWCBRate(),
									'vacation_rate' => $tax->getVacationRate(),
									'release_vacation' => $tax->getReleaseVacation(),
									'ei_exempt' => $tax->getEIExempt(),
									'cpp_exempt' => $tax->getCPPExempt(),
									'federal_tax_exempt' => $tax->getFederalTaxExempt(),
									'provincial_tax_exempt' => $tax->getProvincialTaxExempt(),

									'federal_filing_status_id' => $tax->getFederalFilingStatus(),
									'state_filing_status_id' => $tax->getStateFilingStatus(),
									'federal_allowance' => $tax->getFederalAllowance(),
									'state_allowance' => $tax->getStateAllowance(),
									'state_additional_deduction' => $tax->getStateAdditionalDeduction(),

									'state_ui_rate' => $tax->getStateUIRate(),
									'state_ui_wage_base' => $tax->getStateUIWageBase(),

									'social_security_exempt' => $tax->getSocialSecurityExempt(),
									'ui_exempt' => $tax->getUIExempt(),
									'medicare_exempt' => $tax->getMedicareExempt(),

									'created_date' => $tax->getCreatedDate(),
									'created_by' => $tax->getCreatedBy(),
									'updated_date' => $tax->getUpdatedDate(),
									'updated_by' => $tax->getUpdatedBy(),
									'deleted_date' => $tax->getDeletedDate(),
									'deleted_by' => $tax->getDeletedBy()
								);
			}

			if ( !isset($tax_data)) {
				$tax_data = array(
									'country' => $user_obj->getCountry(),
									'wcb_rate' => 0,
									'vacation_rate' => 0,
									'federal_claim' => 0,
									'provincial_claim' => 0,
									'federal_additional_deduction' => 0,

									'federal_allowance' => 0,
									'state_allowance' => 0,
									'state_additional_deduction' => 0,
									'state_ui_rate' => 0,
									'state_ui_wage_base' => 0,
								);
			}
		} else {
			if ( $tax_data['user_id'] != '' ) {
				$user_id = $tax_data['user_id'];
			}

		}

		$tax_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		$tax_data['federal_filing_status_options'] = $utf->getOptions('federal_filing_status');
		$tax_data['state_filing_status_options'] = $utf->getOptions('state_filing_status');

		//var_dump($tax_data);

		$smarty->assign_by_ref('tax_data', $tax_data);
		$smarty->assign_by_ref('user_id', $user_id);

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		$user_data = $ulf->getCurrent();

		$smarty->assign_by_ref('full_name', $user_data->getFullName() );
		$smarty->assign_by_ref('data_saved', $data_saved );

		break;
}

$smarty->assign_by_ref('utf', $utf);

$smarty->display('users/EditUserTax.tpl');
?>