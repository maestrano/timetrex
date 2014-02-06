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
 * $Revision: 7487 $
 * $Id: EditRecurringSchedule.php 7487 2012-08-15 22:35:09Z ipso $
 * $Date: 2012-08-15 15:35:09 -0700 (Wed, 15 Aug 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('recurring_schedule','enabled')
		OR !( $permission->Check('recurring_schedule','edit') OR $permission->Check('recurring_schedule','edit_own') OR $permission->Check('recurring_schedule','edit_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity(11);

$title = 'Edit Recurring Schedule';
$smarty->assign('title', TTi18n::gettext('Edit Recurring Schedule'));

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

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$filter_data = NULL;
$permission_children_ids = array();
if ( $permission->Check('recurring_schedule','view') == FALSE ) {
	$hlf = TTnew( 'HierarchyListFactory' );
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('recurring_schedule','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('recurring_schedule','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$rscf = TTnew( 'RecurringScheduleControlFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$rscf->StartTransaction();

		$fail_transaction = FALSE;
		if ( is_array($data['template_id']) ) {
			foreach( $data['template_id'] as $template_id ) {
				$rscf->setId( $data['id'] );
				$rscf->setCompany( $current_company->getId() );
				$rscf->setRecurringScheduleTemplateControl( $template_id );
				$rscf->setStartWeek( $data['start_week'] );
				$rscf->setStartDate( $data['start_date'] );
				$rscf->setEndDate( $data['end_date'] );
				if ( isset($data['auto_fill']) ) {
					$rscf->setAutoFill( TRUE );
				} else {
					$rscf->setAutoFill( FALSE );
				}

				if ( $rscf->isValid() ) {
					if ( $rscf->Save(FALSE) === FALSE ) {
						$fail_transaction = TRUE;
						break;
					}

					if ( isset($data['user_ids']) ) {
						$rscf->setUser( $data['user_ids'] );
					}

					if ( $rscf->isValid() ) {
						if ( $rscf->Save() === FALSE ) {
							$fail_transaction = TRUE;
							break;
						}
					} else {
						$fail_transaction = TRUE;
						break;
					}
				} else {
					$fail_transaction = TRUE;
					break;
				}
			}
		}

		if ( $fail_transaction == FALSE ) {
			//$rscf->FailTransaction();
			$rscf->CommitTransaction();

			Redirect::Page( URLBuilder::getURL( NULL, 'RecurringScheduleControlList.php') );
			break;
		} else {
			$rscf->FailTransaction();
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$rsclf = TTnew( 'RecurringScheduleControlListFactory' );
			$rsclf->getByIdAndCompanyId( $id, $current_company->getID() );

			foreach ($rsclf as $rsc_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $rsc_obj->getId(),
									'template_id' => $rsc_obj->getRecurringScheduleTemplateControl(),
									'start_week' => $rsc_obj->getStartWeek(),
									'start_date' => $rsc_obj->getStartDate(),
									'end_date' => $rsc_obj->getEndDate(),
									'auto_fill' => $rsc_obj->getAutoFill(),
									'user_ids' => $rsc_obj->getUser(),
									'created_date' => $rsc_obj->getCreatedDate(),
									'created_by' => $rsc_obj->getCreatedBy(),
									'updated_date' => $rsc_obj->getUpdatedDate(),
									'updated_by' => $rsc_obj->getUpdatedBy(),
									'deleted_date' => $rsc_obj->getDeletedDate(),
									'deleted_by' => $rsc_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			Debug::Text('New Schedule', __FILE__, __LINE__, __METHOD__,10);
				$data = array(
									'start_week' => 1,
									'start_date' => TTDate::getBeginWeekEpoch( TTDate::getTime() ),
									'end_date' => NULL
								);

		}

		//Select box options;
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );
		if ( $current_company->getProductEdition() > 10 ) {
			$user_options = Misc::prependArray( array( 0 => '- '. TTi18n::getText('OPEN') .' -' ), $user_options );
		}

		$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' );
		$template_options = $rstclf->getByCompanyIdArray( $current_company->getId() );

		//Select box options;
		$data['template_options'] = $template_options;
		$data['user_options'] = $user_options;

		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = $user_options;
			foreach( $data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('rscf', $rscf);

$smarty->display('schedule/EditRecurringSchedule.tpl');
?>