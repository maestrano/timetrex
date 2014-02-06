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
 * $Id: EditDepartmentBranchUser.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('department','enabled')
		OR !( $permission->Check('department','assign') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Department Employees')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'department_data'

												) ) );

$dbuf = TTnew( 'DepartmentBranchUserFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		Debug::Text('Department ID: '. $department_data['id'] , __FILE__, __LINE__, __METHOD__,10);


		$dbulf = TTnew( 'DepartmentBranchUserListFactory' );

		//Delete all mappings first?
		$dblf = TTnew( 'DepartmentBranchListFactory' );
		$dblf->getByDepartmentId( $department_data['id'] );

		foreach ($dblf as $department_branch) {
			$dbulf->getByDepartmentBranchId( $department_branch->getId() );

			foreach($dbulf as $department_branch_user) {
				Debug::Text('Deleting Department Branch Mapping: '. $department_branch_user->getId() , __FILE__, __LINE__, __METHOD__,10);
				$department_branch_user->Delete();
			}
		}

		$dbulf = TTnew( 'DepartmentBranchUserListFactory' );

		if ( isset($department_data['branch_data']) AND is_array($department_data['branch_data']) ) {
			foreach($department_data['branch_data'] as $branch_id => $user_ids) {
				Debug::Text('BranchID: '. $branch_id , __FILE__, __LINE__, __METHOD__,10);
				Debug::Arr($user_ids, 'Branch User IDs: ', __FILE__, __LINE__, __METHOD__,10);

				//Get DepartmentBranchId
				$dblf->getByDepartmentIdAndBranchId($department_data['id'],$branch_id);
				$department_branch_id = $dblf->getIterator()->current()->getId();

				Debug::Text('DepartmentBranchID: '. $department_branch_id, __FILE__, __LINE__, __METHOD__,10);

				foreach ($user_ids as $user_id) {
					Debug::Text('Mapping User: '. $user_id .' To DepartmentBranchID: '. $department_branch_id, __FILE__, __LINE__, __METHOD__,10);
					$dbuf->setDepartmentBranch($department_branch_id);
					$dbuf->setUser($user_id);
					if ( $dbuf->isValid() ) {
						$dbuf->Save();
					}

				}
			}
		}

		if ( $dbuf->isValid() ) {

			Redirect::Page( URLBuilder::getURL(NULL, 'DepartmentList.php') );

			break;
		}

	default:
		BreadCrumb::setCrumb($title);

		$dlf = TTnew( 'DepartmentListFactory' );

		$dlf->GetByIdAndCompanyId($id, $current_company->getId() );

		foreach ($dlf as $department) {
			//Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

			$branch_data = array();

			$dblf = TTnew( 'DepartmentBranchListFactory' );
			$dblf->getByDepartmentId( $department->getId() );
			foreach($dblf as $department_branch) {
				$branch_id = $department_branch->getBranch();
				Debug::Text('DepartmentBranchId: '. $branch_id , __FILE__, __LINE__, __METHOD__,10);

				if ( isset($id) ) {
					//Get User ID's from database.
					$dbulf = TTnew( 'DepartmentBranchUserListFactory' );
					$dbulf->getByDepartmentBranchId( $department_branch->getId() );

					$department_branch_user_ids = array();
					foreach($dbulf as $department_branch_user) {
						$department_branch_user_ids[] = $department_branch_user->getUser();
						Debug::Text('DepartmentBranchUser: '. $department_branch_user->getUser(), __FILE__, __LINE__, __METHOD__,10);
					}
				} else {
					//Use selected User Id's.
					$department_branch_user_ids = $department_data['branch_data'][$branch_id];
				}

				$blf = TTnew( 'BranchListFactory' );
				$blf->getById( $branch_id );
				$branch = $blf->getIterator()->current();
				$branch_data[$branch_id] = array(
														'id' => $branch->getId(),
														'name' => $branch->getName(),
														'user_ids' => $department_branch_user_ids
													);
			}

			$department_data = array(
								'id' => $department->getId(),
								'company_name' => $current_company->getName(),
								'status' => $department->getStatus(),
								'name' => $department->getName(),
								'branch_list' => $department->getBranch(),
								'branch_data' => $branch_data,
								'created_date' => $department->getCreatedDate(),
								'created_by' => $department->getCreatedBy(),
								'updated_date' => $department->getUpdatedDate(),
								'updated_by' => $department->getUpdatedBy(),
								'deleted_date' => $department->getDeletedDate(),
								'deleted_by' => $department->getDeletedBy()
							);
		}


		//Select box options;
		$department_data['branch_list_options'] = BranchListFactory::getByCompanyIdArray($current_company->getId());

		//$ulf = new UserListFactory;
		$department_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		//var_dump($te);

		$smarty->assign_by_ref('department_data', $department_data);

		break;
}

$smarty->assign_by_ref('dbuf', $dbuf);

$smarty->display('department/EditDepartmentBranchUser.tpl');
?>