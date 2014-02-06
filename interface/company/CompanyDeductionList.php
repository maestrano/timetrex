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
 * $Id: CompanyDeductionList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('company_tax_deduction','enabled')
		OR !( $permission->Check('company_tax_deduction','view') OR $permission->Check('company_tax_deduction','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Tax / Deduction List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'ids',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add_presets':
		//Debug::setVerbosity(11);
		CompanyDeductionFactory::addPresets( $current_company->getId() );

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );
	case 'add':

		Redirect::Page( URLBuilder::getURL( NULL, 'EditCompanyDeduction.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$cdlf = TTnew( 'CompanyDeductionListFactory' );

		foreach ($ids as $id) {
			$cdlf->getByCompanyIdAndId($current_company->getId(), $id );
			foreach ($cdlf as $cd_obj) {
				$cd_obj->setDeleted($delete);
				if ( $cd_obj->isValid() ) {
					$cd_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );

		break;
	case 'copy':
		$cdlf = TTnew( 'CompanyDeductionListFactory' );

		foreach ($ids as $id) {
			$cdlf->getByCompanyIdAndId($current_company->getId(), $id );
			foreach ($cdlf as $cd_obj) {
				$tmp_cd_obj = clone $cd_obj;

				$tmp_cd_obj->setId( FALSE );
				$tmp_cd_obj->setName( Misc::generateCopyName( $cd_obj->getName() )  );
				if ( $tmp_cd_obj->isValid() ) {
					$tmp_cd_obj->Save( FALSE );

					$tmp_cd_obj->setIncludePayStubEntryAccount( $cd_obj->getIncludePayStubEntryAccount() );
					$tmp_cd_obj->setExcludePayStubEntryAccount( $cd_obj->getExcludePayStubEntryAccount() );
					$tmp_cd_obj->setUser( $cd_obj->getUser() );

					if ( $tmp_cd_obj->isValid() ) {
						$tmp_cd_obj->Save();
					}
				}
			}
		}
		unset($tmp_cd_obj, $cd_obj);

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );

		break;
	default:
		BreadCrumb::setCrumb($title);

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$cdlf = TTnew( 'CompanyDeductionListFactory' );
		$cdlf->getByCompanyId( $current_company->getId(), NULL, $sort_array );

		$pager = new Pager($cdlf);

		$status_options = $cdlf->getOptions('status');
		$type_options = $cdlf->getOptions('type');
		$calculation_options = $cdlf->getOptions('calculation');

		foreach ($cdlf as $cd_obj) {

			$rows[] = array(
								'id' => $cd_obj->getId(),
								'status_id' => $cd_obj->getStatus(),
								'status' => $status_options[$cd_obj->getStatus()],
								'type_id' => $cd_obj->getType(),
								'type' => $type_options[$cd_obj->getType()],
								'calculation_id' => $cd_obj->getCalculation(),
								'calculation' => $calculation_options[$cd_obj->getCalculation()],
								'calculation_order' => $cd_obj->getCalculationOrder(),
								'name' => $cd_obj->getName(),
								'deleted' => $cd_obj->getDeleted()
							);
		}
		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('company/CompanyDeductionList.tpl');
?>