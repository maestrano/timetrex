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
 * $Id: EditUserTitle.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','edit') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title',  TTi18n::gettext($title = 'Edit Employee Title')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'title_data'
												) ) );

$utf = TTnew( 'UserTitleFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$utf->setId($title_data['id']);
		$utf->setCompany( $current_company->getId() );
		$utf->setName($title_data['name']);

		if ( $utf->isValid() ) {
			$utf->Save();

			Redirect::Page( URLBuilder::getURL(NULL, 'UserTitleList.php') );

			break;
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$utlf = TTnew( 'UserTitleListFactory' );

			$utlf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($utlf as $title_obj) {
				//Debug::Arr($title_obj,'Title Object', __FILE__, __LINE__, __METHOD__,10);

				$title_data = array(
									'id' => $title_obj->getId(),
									'name' => $title_obj->getName(),
									'created_date' => $title_obj->getCreatedDate(),
									'created_by' => $title_obj->getCreatedBy(),
									'updated_date' => $title_obj->getUpdatedDate(),
									'updated_by' => $title_obj->getUpdatedBy(),
									'deleted_date' => $title_obj->getDeletedDate(),
									'deleted_by' => $title_obj->getDeletedBy()
								);
			}
		}

		$smarty->assign_by_ref('title_data', $title_data);

		break;
}

$smarty->assign_by_ref('utf', $utf);

$smarty->display('users/EditUserTitle.tpl');
?>