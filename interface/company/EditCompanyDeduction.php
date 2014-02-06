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
 * $Revision: 10386 $
 * $Id: EditCompanyDeduction.php 10386 2013-07-08 22:31:21Z ipso $
 * $Date: 2013-07-08 15:31:21 -0700 (Mon, 08 Jul 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('company_tax_deduction','enabled')
		OR !( $permission->Check('company_tax_deduction','edit') OR $permission->Check('company_tax_deduction','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Tax / Deduction')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data)) {
	if ( $data['start_date'] != '' ) {
		$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
	}
	if ( $data['end_date'] != '' ) {
		$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
	}
}

$cdf = TTnew( 'CompanyDeductionFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		$cdf->StartTransaction();

		$cdf->setId( $data['id'] );
		$cdf->setCompany( $current_company->getId() );
		$cdf->setStatus( $data['status_id'] );
		$cdf->setType( $data['type_id'] );
		$cdf->setName( $data['name'] );
		$cdf->setCalculation( $data['calculation_id'] );
		$cdf->setCalculationOrder( $data['calculation_order'] );

		if ( isset($data['country']) ) {
			$cdf->setCountry($data['country']);
		}

		if ( isset($data['province']) ) {
			$cdf->setProvince($data['province']);
		} else {
			$cdf->setProvince(NULL);
		}

		if ( isset($data['district']) ) {
			$cdf->setDistrict($data['district']);
		} else {
			$cdf->setDistrict(NULL);
		}

		if ( isset($data['company_value1']) ) {
			$cdf->setCompanyValue1( $data['company_value1'] );
		}
		if ( isset($data['company_value2']) ) {
			$cdf->setCompanyValue2( $data['company_value2'] );
		}

		$cdf->setPayStubEntryAccount( $data['pay_stub_entry_account_id'] );
		if ( isset($data['user_value1']) ) {
			$cdf->setUserValue1( $data['user_value1'] );
		}
		if ( isset($data['user_value2']) ) {
			$cdf->setUserValue2( $data['user_value2'] );
		}
		if ( isset($data['user_value3']) ) {
			$cdf->setUserValue3( $data['user_value3'] );
		}
		if ( isset($data['user_value4']) ) {
			$cdf->setUserValue4( $data['user_value4'] );
		}
		if ( isset($data['user_value5']) ) {
			$cdf->setUserValue5( $data['user_value5'] );
		}
		if ( isset($data['user_value6']) ) {
			$cdf->setUserValue6( $data['user_value6'] );
		}
		if ( isset($data['user_value7']) ) {
			$cdf->setUserValue7( $data['user_value7'] );
		}
		if ( isset($data['user_value8']) ) {
			$cdf->setUserValue8( $data['user_value8'] );
		}
		if ( isset($data['user_value9']) ) {
			$cdf->setUserValue9( $data['user_value9'] );
		}
		if ( isset($data['user_value10']) ) {
			$cdf->setUserValue10( $data['user_value10'] );
		}


		if ( isset($data['start_date']) ) {
			$cdf->setStartDate( $data['start_date'] );
		}
		if ( isset($data['end_date']) ) {
			$cdf->setEndDate( $data['end_date'] );
		}

		if ( isset($data['minimum_length_of_service']) ) {
			$cdf->setMinimumLengthOfService( $data['minimum_length_of_service'] );
			$cdf->setMinimumLengthOfServiceUnit( $data['minimum_length_of_service_unit_id'] );
		}
		if ( isset($data['maximum_length_of_service']) ) {
			$cdf->setMaximumLengthOfService( $data['maximum_length_of_service'] );
			$cdf->setMaximumLengthOfServiceUnit( $data['maximum_length_of_service_unit_id'] );
		}

		if ( isset($data['minimum_user_age']) ) {
			$cdf->setMinimumUserAge( $data['minimum_user_age'] );
		}
		if ( isset($data['maximum_user_age']) ) {
			$cdf->setMaximumUserAge( $data['maximum_user_age'] );
		}

		if ( isset($data['include_account_amount_type_id']) ) {
			$cdf->setIncludeAccountAmountType( $data['include_account_amount_type_id'] );
		}
		if ( isset($data['exclude_account_amount_type_id']) ) {
			$cdf->setExcludeAccountAmountType( $data['exclude_account_amount_type_id'] );
		}

		if ( $cdf->isValid() ) {
			$cdf->Save(FALSE);

			if ( isset($data['include_pay_stub_entry_account_ids']) ){
				$cdf->setIncludePayStubEntryAccount( $data['include_pay_stub_entry_account_ids'] );
			} else {
				$cdf->setIncludePayStubEntryAccount( array() );
			}

			if ( isset($data['exclude_pay_stub_entry_account_ids']) ){
				$cdf->setExcludePayStubEntryAccount( $data['exclude_pay_stub_entry_account_ids'] );
			} else {
				$cdf->setExcludePayStubEntryAccount( array() );
			}

			if ( isset($data['user_ids']) ){
				$cdf->setUser( $data['user_ids'] );
			} else {
				$cdf->setUser( array() );
			}

			if ( $cdf->isValid() ) {
				$cdf->Save(TRUE);

				$cdf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );

				break;
			}
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$cdlf = TTnew( 'CompanyDeductionListFactory' );
			$cdlf->getByCompanyIdAndId( $current_company->getId(), $id );

			foreach ($cdlf as $cd_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $cd_obj->getId(),
									'company_id' => $cd_obj->getCompany(),
									'status_id' => $cd_obj->getStatus(),
									'type_id' => $cd_obj->getType(),
									'name' => $cd_obj->getName(),

									'start_date' => $cd_obj->getStartDate(),
									'end_date' => $cd_obj->getEndDate(),

									'minimum_length_of_service' => $cd_obj->getMinimumLengthOfService(),
									'minimum_length_of_service_unit_id' => $cd_obj->getMinimumLengthOfServiceUnit(),
									'maximum_length_of_service' => $cd_obj->getMaximumLengthOfService(),
									'maximum_length_of_service_unit_id' => $cd_obj->getMaximumLengthOfServiceUnit(),
									'minimum_user_age' => $cd_obj->getMinimumUserAge(),
									'maximum_user_age' => $cd_obj->getMaximumUserAge(),

									'calculation_id' => $cd_obj->getCalculation(),
									'calculation_order' => $cd_obj->getCalculationOrder(),

									'country' => $cd_obj->getCountry(),
									'province' => $cd_obj->getProvince(),
									'district' => $cd_obj->getDistrict(),

									'company_value1' => $cd_obj->getCompanyValue1(),
									'company_value2' => $cd_obj->getCompanyValue2(),

									'user_value1' => $cd_obj->getUserValue1(),
									'user_value2' => $cd_obj->getUserValue2(),
									'user_value3' => $cd_obj->getUserValue3(),
									'user_value4' => $cd_obj->getUserValue4(),
									'user_value5' => $cd_obj->getUserValue5(),
									'user_value6' => $cd_obj->getUserValue6(),
									'user_value7' => $cd_obj->getUserValue7(),
									'user_value8' => $cd_obj->getUserValue8(),
									'user_value9' => $cd_obj->getUserValue9(),
									'user_value10' => $cd_obj->getUserValue10(),

									'lock_user_value1' => $cd_obj->getLockUserValue1(),
									'lock_user_value2' => $cd_obj->getLockUserValue2(),
									'lock_user_value3' => $cd_obj->getLockUserValue3(),
									'lock_user_value4' => $cd_obj->getLockUserValue4(),
									'lock_user_value5' => $cd_obj->getLockUserValue5(),
									'lock_user_value6' => $cd_obj->getLockUserValue6(),
									'lock_user_value7' => $cd_obj->getLockUserValue7(),
									'lock_user_value8' => $cd_obj->getLockUserValue8(),
									'lock_user_value9' => $cd_obj->getLockUserValue9(),
									'lock_user_value10' => $cd_obj->getLockUserValue10(),

									'pay_stub_entry_account_id' => $cd_obj->getPayStubEntryAccount(),

									'include_pay_stub_entry_account_ids' => $cd_obj->getIncludePayStubEntryAccount(),
									'exclude_pay_stub_entry_account_ids' => $cd_obj->getExcludePayStubEntryAccount(),

									'include_account_amount_type_id' => $cd_obj->getIncludeAccountAmountType(),
									'exclude_account_amount_type_id' => $cd_obj->getExcludeAccountAmountType(),

									'user_ids' => $cd_obj->getUser(),

									'created_date' => $cd_obj->getCreatedDate(),
									'created_by' => $cd_obj->getCreatedBy(),
									'updated_date' => $cd_obj->getUpdatedDate(),
									'updated_by' => $cd_obj->getUpdatedBy(),
									'deleted_date' => $cd_obj->getDeletedDate(),
									'deleted_by' => $cd_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			$data = array(
						'country' => 0,
						'province' => 0,
						'district' => 0,
						'user_value1' => 0,
						'user_value2' => 0,
						'user_value3' => 0,
						'user_value4' => 0,
						'user_value5' => 0,
						'user_value6' => 0,
						'user_value7' => 0,
						'user_value8' => 0,
						'user_value9' => 0,
						'user_value10' => 0,
						'minimum_length_of_service' => 0,
						'maximum_length_of_service' => 0,
						'minimum_user_age' => 0,
						'maximum_user_age' => 0,
						'calculation_order' => 100,
						);
		}

		//Select box options;
		$data['status_options'] = $cdf->getOptions('status');
		$data['type_options'] = $cdf->getOptions('type');
		$data['length_of_service_unit_options'] = $cdf->getOptions('length_of_service_unit');
		$data['account_amount_type_options'] = $cdf->getOptions('account_amount_type');

		$cf = TTnew( 'CompanyFactory' );
		$data['country_options'] = Misc::prependArray( array( 0 => '--' ), $cf->getOptions('country') );
		if ( isset($data['country']) ) {
			$data['province_options'] = $cf->getOptions('province', $data['country'] );
		}
		if ( isset($data['district']) ) {
			$district_options = $cf->getOptions('district', $data['country'] );
			if ( isset($district_options[$data['province']]) ) {
				$data['district_options'] = $district_options[$data['province']];
			}
		}

		$data['us_medicare_filing_status_options'] = $cdf->getOptions('us_medicare_filing_status');
		$data['us_eic_filing_status_options'] = $cdf->getOptions('us_eic_filing_status');
		$data['federal_filing_status_options'] = $cdf->getOptions('federal_filing_status');
		$data['state_filing_status_options'] = $cdf->getOptions('state_filing_status');
		$data['state_ga_filing_status_options'] = $cdf->getOptions('state_ga_filing_status');
		$data['state_nj_filing_status_options'] = $cdf->getOptions('state_nj_filing_status');
		$data['state_nc_filing_status_options'] = $cdf->getOptions('state_nc_filing_status');
		$data['state_ma_filing_status_options'] = $cdf->getOptions('state_ma_filing_status');
		$data['state_al_filing_status_options'] = $cdf->getOptions('state_al_filing_status');
		$data['state_ct_filing_status_options'] = $cdf->getOptions('state_ct_filing_status');
		$data['state_wv_filing_status_options'] = $cdf->getOptions('state_wv_filing_status');
		$data['state_me_filing_status_options'] = $cdf->getOptions('state_me_filing_status');
		$data['state_de_filing_status_options'] = $cdf->getOptions('state_de_filing_status');
		$data['state_dc_filing_status_options'] = $cdf->getOptions('state_dc_filing_status');
		$data['state_la_filing_status_options'] = $cdf->getOptions('state_la_filing_status');

		$data['calculation_options'] = $cdf->getOptions('calculation');
		$data['js_arrays'] = $cdf->getJavaScriptArrays();

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$data['pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50,80), FALSE );
		//$data['pay_stub_entry_account_options'] = PayStubEntryAccountListFactory::getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(20,30), FALSE );

		$data['include_pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,80), FALSE );
		if ( isset($data['include_pay_stub_entry_account_ids']) AND is_array($data['include_pay_stub_entry_account_ids']) ) {
			$tmp_psea_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,80), FALSE );
			foreach( $data['include_pay_stub_entry_account_ids'] as $include_psea_id ) {
				if ( isset($tmp_psea_options[$include_psea_id]) ) {
					$filter_include_options[$include_psea_id] = $tmp_psea_options[$include_psea_id];
				}
			}
			unset($include_psea_id, $tmp_psea_options);
		}
		$smarty->assign_by_ref('filter_include_options', $filter_include_options);

		$data['exclude_pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,80), FALSE );
		if ( isset($data['exclude_pay_stub_entry_account_ids']) AND is_array($data['exclude_pay_stub_entry_account_ids']) ) {
			$tmp_psea_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,80), FALSE );
			foreach( $data['exclude_pay_stub_entry_account_ids'] as $exclude_psea_id ) {
				$filter_exclude_options[$exclude_psea_id] = $tmp_psea_options[$exclude_psea_id];
			}
			unset($exclude_psea_id, $tmp_psea_options);
		}
		$smarty->assign_by_ref('filter_exclude_options', $filter_exclude_options);

		//var_dump($data);

		//Employee Selection Options
		$data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE );
		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE );
			foreach( $data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id, $tmp_user_options);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('cdf', $cdf);

$smarty->display('company/EditCompanyDeduction.tpl');
?>