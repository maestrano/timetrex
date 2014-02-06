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
 * $Revision: 10376 $
 * $Id: EditPayStubEntryAccount.php 10376 2013-07-05 20:34:11Z ipso $
 * $Date: 2013-07-05 13:34:11 -0700 (Fri, 05 Jul 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('pay_stub_account','enabled')
		OR !( $permission->Check('pay_stub_account','edit') OR $permission->Check('pay_stub_account','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Pay Stub Account')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

$pseaf = TTnew( 'PayStubEntryAccountFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		$pseaf->setId( $data['id'] );
		$pseaf->setCompany( $current_company->getId() );
		$pseaf->setStatus( $data['status_id'] );
		$pseaf->setType( $data['type_id'] );
		$pseaf->setName( $data['name'] );
		$pseaf->setOrder( $data['order'] );
		$pseaf->setAccrual( $data['accrual_id'] );
		$pseaf->setAccrualType( $data['accrual_type_id'] );
		$pseaf->setDebitAccount( $data['debit_account'] );
		$pseaf->setCreditAccount( $data['credit_account'] );

		if ( $pseaf->isValid() ) {
			$pseaf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'PayStubEntryAccountList.php') );

			break;
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$psealf = TTnew( 'PayStubEntryAccountListFactory' );
			$psealf->getById($id);

			foreach ($psealf as $psea_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $psea_obj->getId(),
									'status_id' => $psea_obj->getStatus(),
									'type_id' => $psea_obj->getType(),
									'name' => $psea_obj->getName(),
									'order' => $psea_obj->getOrder(),
									'accrual_id' => $psea_obj->getAccrual(),
									'accrual_type_id' => $psea_obj->getAccrualType(),
									'debit_account' => $psea_obj->getDebitAccount(),
									'credit_account' => $psea_obj->getCreditAccount(),
									'created_date' => $psea_obj->getCreatedDate(),
									'created_by' => $psea_obj->getCreatedBy(),
									'updated_date' => $psea_obj->getUpdatedDate(),
									'updated_by' => $psea_obj->getUpdatedBy(),
									'deleted_date' => $psea_obj->getDeletedDate(),
									'deleted_by' => $psea_obj->getDeletedBy()
								);
			}
		}

		//Select box options;
		$data['status_options'] = $pseaf->getOptions('status');
		$data['type_options'] = $pseaf->getOptions('type');
		$data['accrual_type_options'] = $pseaf->getOptions('accrual_type');

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$data['accrual_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(50), TRUE );

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('pseaf', $pseaf);

$smarty->display('pay_stub/EditPayStubEntryAccount.tpl');
?>