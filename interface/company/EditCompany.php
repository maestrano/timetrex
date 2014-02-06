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
 * $Revision: 8051 $
 * $Id: EditCompany.php 8051 2012-10-22 18:52:36Z ipso $
 * $Date: 2012-10-22 11:52:36 -0700 (Mon, 22 Oct 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('company','enabled')
		OR !( $permission->Check('company','edit') OR $permission->Check('company','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Company')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'company_data'
												) ) );

$cf = TTnew( 'CompanyFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		$cf->StartTransaction();

		if ( $permission->Check('company','edit') ) {
			$cf->setId( $company_data['id'] );
			$cf->setParent( $company_data['parent'] );
			$cf->setStatus( $company_data['status'] );
		} else {
			$cf->setId( $current_company->getId() );
		}

		if ( isset($company_data['product_edition']) AND $company_data['product_edition'] != '' ) {
			$cf->setProductEdition($company_data['product_edition']);
		}

		if ( isset($company_data['name']) ) {
			$cf->setName($company_data['name']);
		}
		$cf->setShortName($company_data['short_name']);
		$cf->setIndustry($company_data['industry_id']);
		$cf->setBusinessNumber($company_data['business_number']);
		$cf->setOriginatorID($company_data['originator_id']);
		$cf->setDataCenterID($company_data['data_center_id']);
		$cf->setAddress1($company_data['address1']);
		$cf->setAddress2($company_data['address2']);
		$cf->setCity($company_data['city']);
		$cf->setCountry($company_data['country']);
		if ( isset($company_data['province']) ) {
			$cf->setProvince($company_data['province']);
		}
		$cf->setPostalCode($company_data['postal_code']);
		$cf->setWorkPhone($company_data['work_phone']);
		$cf->setFaxPhone($company_data['fax_phone']);
		$cf->setAdminContact($company_data['admin_contact']);
		$cf->setBillingContact($company_data['billing_contact']);
		$cf->setSupportContact($company_data['support_contact']);

		if ( isset($company_data['enable_second_last_name']) AND $company_data['enable_second_last_name'] == 1 ) {
			$cf->setEnableSecondLastName( TRUE );
		} else {
			$cf->setEnableSecondLastName( FALSE );
		}

		if ( isset($company_data['other_id1']) ) {
			$cf->setOtherID1( $company_data['other_id1'] );
		}
		if ( isset($company_data['other_id2']) ) {
			$cf->setOtherID2( $company_data['other_id2'] );
		}
		if ( isset($company_data['other_id3']) ) {
			$cf->setOtherID3( $company_data['other_id3'] );
		}
		if ( isset($company_data['other_id4']) ) {
			$cf->setOtherID4( $company_data['other_id4'] );
		}
		if ( isset($company_data['other_id5']) ) {
			$cf->setOtherID5( $company_data['other_id5'] );
		}

		$cf->setLDAPAuthenticationType($company_data['ldap_authentication_type_id']);
		$cf->setLDAPHost($company_data['ldap_host']);
		$cf->setLDAPPort($company_data['ldap_port']);
		$cf->setLDAPBindUserName($company_data['ldap_bind_user_name']);
		$cf->setLDAPBindPassword($company_data['ldap_bind_password']);
		$cf->setLDAPBaseDN($company_data['ldap_base_dn']);
		$cf->setLDAPBindAttribute($company_data['ldap_bind_attribute']);
		$cf->setLDAPUserFilter($company_data['ldap_user_filter']);
		$cf->setLDAPLoginAttribute($company_data['ldap_login_attribute']);

		if ( $cf->isNew() == TRUE ) {
			$cf->setEnableAddCurrency( TRUE );
			$cf->setEnableAddPermissionGroupPreset( TRUE );
			$cf->setEnableAddStation( TRUE );
			$cf->setEnableAddPayStubEntryAccountPreset( TRUE );
			$cf->setEnableAddRecurringHolidayPreset( TRUE );
		}

		if ( $cf->isValid() ) {
			$cf->Save();

			//$cf->FailTransaction();
			$cf->CommitTransaction();

			if ( $permission->Check('company','edit') ) {
				Redirect::Page( URLBuilder::getURL(NULL, 'CompanyList.php') );
			} else {
				Redirect::Page( URLBuilder::getURL(NULL, '../index.php') );
			}

			break;
		}
		$cf->FailTransaction();
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$clf = TTnew( 'CompanyListFactory' );

			if ( $permission->Check('company','edit') ) {
				$clf->GetByID($id);
			} else {
				$id = $current_company->getId();
				$clf->GetByID( $id );
			}

			foreach ($clf as $company) {
				//Debug::Arr($company,'Company', __FILE__, __LINE__, __METHOD__,10);

				$company_data = array(
									'id' => $company->getId(),
									'parent' => $company->getParent(),
									'status' => $company->getStatus(),
									'product_edition' => $company->getProductEdition(),
									'name' => $company->getName(),
									'short_name' => $company->getShortName(),
									'industry_id' => $company->getIndustry(),
									'business_number' => $company->getBusinessNumber(),
									'originator_id' => $company->getOriginatorID(),
									'data_center_id' => $company->getDataCenterID(),
									'address1' => $company->getAddress1(),
									'address2' => $company->getAddress2(),
									'city' => $company->getCity(),
									'province' => $company->getProvince(),
									'country' => $company->getCountry(),
									'postal_code' => $company->getPostalCode(),
									'work_phone' => $company->getWorkPhone(),
									'fax_phone' => $company->getFaxPhone(),
									'admin_contact' => $company->getAdminContact(),
									'billing_contact' => $company->getBillingContact(),
									'support_contact' => $company->getSupportContact(),
									'logo_file_name' => $company->getLogoFileName( NULL, FALSE ),
									'enable_second_last_name' => $company->getEnableSecondLastName(),
									'other_id1' => $company->getOtherID1(),
									'other_id2' => $company->getOtherID2(),
									'other_id3' => $company->getOtherID3(),
									'other_id4' => $company->getOtherID4(),
									'other_id5' => $company->getOtherID5(),
									'ldap_authentication_type_id' => $company->getLDAPAuthenticationType(),
									'ldap_host' => $company->getLDAPHost(),
									'ldap_port' => $company->getLDAPPort(),
									'ldap_bind_user_name' => $company->getLDAPBindUserName(),
									'ldap_bind_password' => $company->getLDAPBindPassword(),
									'ldap_base_dn' => $company->getLDAPBaseDN(),
									'ldap_bind_attribute' => $company->getLDAPBindAttribute(),
									'ldap_user_filter' => $company->getLDAPUserFilter(),
									'ldap_login_attribute' => $company->getLDAPLoginAttribute(),

									'created_date' => $company->getCreatedDate(),
									'created_by' => $company->getCreatedBy(),
									'updated_date' => $company->getUpdatedDate(),
									'updated_by' => $company->getUpdatedBy(),
									'deleted_date' => $company->getDeletedDate(),
									'deleted_by' => $company->getDeletedBy(),
								);
			}
		} elseif ( $action != 'submit' ) {
			$company_data = array(
								  'parent' => $current_company->getId(),
								  );
		}

		//Select box options;
		$company_data['status_options'] = $cf->getOptions('status');
		$company_data['country_options'] = $cf->getOptions('country');
		$company_data['industry_options'] = $cf->getOptions('industry');

		//Company list.
		$company_data['company_list_options'] = CompanyListFactory::getAllArray();
		$company_data['product_edition_options'] = $cf->getOptions('product_edition');

		//Get other field names
		$oflf = TTnew( 'OtherFieldListFactory' );
		$company_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getID(), 2 );

		$company_data['ldap_authentication_type_options'] = $cf->getOptions('ldap_authentication_type');

		if (!isset($id) AND isset($company_data['id']) ) {
			$id = $company_data['id'];
		}
		$company_data['user_list_options'] = UserListFactory::getByCompanyIdArray($id);

		$smarty->assign_by_ref('company_data', $company_data);

		break;
}

$smarty->assign_by_ref('cf', $cf);

$smarty->display('company/EditCompany.tpl');
?>