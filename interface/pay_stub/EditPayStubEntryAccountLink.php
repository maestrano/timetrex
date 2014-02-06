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
 * $Id: EditPayStubEntryAccountLink.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('pay_stub_account','enabled')
		OR !( $permission->Check('pay_stub_account','edit') OR $permission->Check('pay_stub_account','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Pay Stub Account Links')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data',
												'data_saved'
												) ) );

$psealf = TTnew( 'PayStubEntryAccountLinkFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$psealf->setId( $data['id'] );
		$psealf->setCompany( $current_company->getId() );

		$psealf->setTotalGross( $data['total_gross'] );
		$psealf->setTotalEmployeeDeduction( $data['total_employee_deduction'] );
		$psealf->setTotalEmployerDeduction( $data['total_employer_deduction'] );
		$psealf->setTotalNetPay( $data['total_net_pay'] );
		$psealf->setRegularTime( $data['regular_time'] );

		//$psealf->setMonthlyAdvance( $data['monthly_advance'] );
		//$psealf->setMonthlyAdvanceDeduction( $data['monthly_advance_deduction'] );

		if ( $current_company->getCountry() == 'CA' ) {
			$psealf->setEmployeeCPP( $data['employee_cpp'] );
			$psealf->setEmployeeEI( $data['employee_ei'] );
		}
		
		if ( $psealf->isValid() ) {
			$psealf->Save();

			Redirect::Page( URLBuilder::getURL( array( 'data_saved' => TRUE), 'EditPayStubEntryAccountLink.php') );

			break;
		}
	default:
		BreadCrumb::setCrumb($title);

		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
		$pseallf->getByCompanyId( $current_company->getId() );

		if ( $pseallf->getRecordCount() > 0 ) {
			$pseal_obj = $pseallf->getCurrent();

			$data = array(
							'id' => $pseal_obj->getId(),
							'total_gross' => $pseal_obj->getTotalGross(),
							'total_employee_deduction' => $pseal_obj->getTotalEmployeeDeduction(),
							'total_employer_deduction' => $pseal_obj->getTotalEmployerDeduction(),
							'total_net_pay' => $pseal_obj->getTotalNetPay(),
							'regular_time' => $pseal_obj->getRegularTime(),

							'monthly_advance' => $pseal_obj->getMonthlyAdvance(),
							'monthly_advance_deduction' => $pseal_obj->getMonthlyAdvanceDeduction(),

							'employee_cpp' => $pseal_obj->getEmployeeCPP(),
							'employee_ei' => $pseal_obj->getEmployeeEI(),
/*
							'federal_income_tax' => $pseal_obj->getFederalIncomeTax(),
							'provincial_income_tax' => $pseal_obj->getProvincialIncomeTax(),
							'federal_additional_income_tax' => $pseal_obj->getFederalAdditionalIncomeTax(),

							'employer_cpp' => $pseal_obj->getEmployerCPP(),

							'employer_ei' => $pseal_obj->getEmployerEI(),
							'employer_wcb' => $pseal_obj->getEmployerWCB(),
							'union_dues' => $pseal_obj->getUnionDues(),
							'vacation_accrual' => $pseal_obj->getVacationAccrual(),
							'vacation_accrual_release' => $pseal_obj->getVacationAccrualRelease(),

							'state_additional_income_tax' => $pseal_obj->getStateAdditionalIncomeTax(),
							'employee_social_security' => $pseal_obj->getEmployeeSocialSecurity(),
							'employer_social_security' => $pseal_obj->getEmployerSocialSecurity(),
							'federal_employer_ui' => $pseal_obj->getFederalEmployerUI(),
							'state_employer_ui' => $pseal_obj->getStateEmployerUI(),
							'employee_medicare' => $pseal_obj->getEmployeeMedicare(),
							'employer_medicare' => $pseal_obj->getEmployerMedicare(),
*/
							'created_date' => $pseal_obj->getCreatedDate(),
							'created_by' => $pseal_obj->getCreatedBy(),
							'updated_date' => $pseal_obj->getUpdatedDate(),
							'updated_by' => $pseal_obj->getUpdatedBy(),
							'deleted_date' => $pseal_obj->getDeletedDate(),
							'deleted_by' => $pseal_obj->getDeletedBy()

							);
		}

		$psealf_tmp = TTnew( 'PayStubEntryAccountListFactory' );

		$data['earning_account_options'] = $psealf_tmp->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10) );
		$data['employee_deduction_account_options'] = $psealf_tmp->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(20) );
		$data['employer_deduction_account_options'] = $psealf_tmp->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(30) );
		$data['total_account_options'] = $psealf_tmp->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(40) );
		$data['accrual_account_options'] = $psealf_tmp->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(50) );
		$data['other_account_options'] = $psealf_tmp->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(60,65) );

		//var_dump($data);
		$smarty->assign_by_ref('data', $data);
		$smarty->assign_by_ref('data_saved', $data_saved);

		break;
}

$smarty->assign_by_ref('psealf', $psealf);

$smarty->display('pay_stub/EditPayStubEntryAccountLink.tpl');
?>