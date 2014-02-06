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
 * $Revision: 5164 $
 * $Id: EditBranch.php 5164 2011-08-26 23:00:02Z ipso $
 * $Date: 2011-08-26 16:00:02 -0700 (Fri, 26 Aug 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('branch','enabled')
		OR !( $permission->Check('branch','edit') OR $permission->Check('branch','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Branch')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'branch_data'
												) ) );

$bf = TTnew( 'BranchFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$bf->setId($branch_data['id']);
		$bf->setCompany( $current_company->getId() );
		$bf->setStatus($branch_data['status']);
		$bf->setName($branch_data['name']);
		$bf->setManualId($branch_data['manual_id']);

		if ($branch_data['address1'] != '') {
			$bf->setAddress1($branch_data['address1']);
		}
		if ($branch_data['address2'] != '') {
			$bf->setAddress2($branch_data['address2']);
		}

		$bf->setCity($branch_data['city']);
		$bf->setCountry($branch_data['country']);
		$bf->setProvince($branch_data['province']);

		if ($branch_data['postal_code'] != '') {
			$bf->setPostalCode($branch_data['postal_code']);
		}
		if ($branch_data['work_phone'] != '') {
			$bf->setWorkPhone($branch_data['work_phone']);
		}
		if ($branch_data['fax_phone'] != '') {
			$bf->setFaxPhone($branch_data['fax_phone']);
		}

		if ( isset($branch_data['other_id1']) ) {
			$bf->setOtherID1( $branch_data['other_id1'] );
		}
		if ( isset($branch_data['other_id2']) ) {
			$bf->setOtherID2( $branch_data['other_id2'] );
		}
		if ( isset($branch_data['other_id3']) ) {
			$bf->setOtherID3( $branch_data['other_id3'] );
		}
		if ( isset($branch_data['other_id4']) ) {
			$bf->setOtherID4( $branch_data['other_id4'] );
		}
		if ( isset($branch_data['other_id5']) ) {
			$bf->setOtherID5( $branch_data['other_id5'] );
		}

		if ( $bf->isValid() ) {
			$bf->Save();

			Redirect::Page( URLBuilder::getURL(NULL, 'BranchList.php') );

			break;
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$blf = TTnew( 'BranchListFactory' );

			$blf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($blf as $branch) {
				$branch_data = array(
									'id' => $branch->getId(),
									'status' => $branch->getStatus(),
									'manual_id' => $branch->getManualID(),
									'name' => $branch->getName(),
									'address1' => $branch->getAddress1(),
									'address2' => $branch->getAddress2(),
									'city' => $branch->getCity(),
									'province' => $branch->getProvince(),
									'country' => $branch->getCountry(),
									'postal_code' => $branch->getPostalCode(),
									'work_phone' => $branch->getWorkPhone(),
									'fax_phone' => $branch->getFaxPhone(),
									'other_id1' => $branch->getOtherID1(),
									'other_id2' => $branch->getOtherID2(),
									'other_id3' => $branch->getOtherID3(),
									'other_id4' => $branch->getOtherID4(),
									'other_id5' => $branch->getOtherID5(),
									'created_date' => $branch->getCreatedDate(),
									'created_by' => $branch->getCreatedBy(),
									'updated_date' => $branch->getUpdatedDate(),
									'updated_by' => $branch->getUpdatedBy(),
									'deleted_date' => $branch->getDeletedDate(),
									'deleted_by' => $branch->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			$next_available_manual_id = BranchListFactory::getNextAvailableManualId( $current_company->getId() );

			$branch_data = array(
							'country' => $current_company->getCountry(),
							'province' => $current_company->getProvince(),
							'next_available_manual_id' => $next_available_manual_id,
							);
		}

		//Select box options;
		$branch_data['status_options'] = $bf->getOptions('status');

		$cf = TTnew( 'CompanyFactory' );
		$branch_data['country_options'] = $cf->getOptions('country');
		$branch_data['province_options'] = $cf->getOptions('province', $branch_data['country'] );

		//Get other field names
		$oflf = TTnew( 'OtherFieldListFactory' );
		$branch_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getID(), 4 );

		$smarty->assign_by_ref('branch_data', $branch_data);

		break;
}

$smarty->assign_by_ref('bf', $bf);

$smarty->display('branch/EditBranch.tpl');
?>