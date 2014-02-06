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
 * $Revision: 11018 $
 * $Id: EditSchedule.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('schedule','enabled')
		OR !( $permission->Check('schedule','edit')
				OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Schedule')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'date_stamp',
												'status_id',
												'start_time',
												'end_time',
												'schedule_policy_id',
												'absence_policy_id',
												'data'
												) ) );

if ( isset($data) ) {
	if ( $data['date_stamp'] != '') {
		$data['date_stamp'] = TTDate::parseDateTime( $data['date_stamp'] ) ;
	}
	if ( $data['start_time'] != '') {
		$data['parsed_start_time'] = strtotime( $data['start_time'], $data['date_stamp'] ) ;
	}
	if ( $data['end_time'] != '') {
		Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $data['date_stamp'] , __FILE__, __LINE__, __METHOD__,10);
		$data['parsed_end_time'] = strtotime( $data['end_time'], $data['date_stamp'] ) ;
		Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data['end_time']) , __FILE__, __LINE__, __METHOD__,10);
	}
}


$filter_data = array();
$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
if ( $permission->Check('schedule','edit') == FALSE ) {
	if ( $permission->Check('schedule','edit_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('schedule','edit_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$sf = TTnew( 'ScheduleFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'delete':
		Debug::Text('Delete!', __FILE__, __LINE__, __METHOD__,10);

		$slf = TTnew( 'ScheduleListFactory' );
		$slf->getById( $data['id'] );
		if ( $slf->getRecordCount() > 0 ) {
			foreach($slf as $s_obj) {
				$s_obj->setDeleted(TRUE);
				if ( $s_obj->isValid() ) {
					$s_obj->setEnableReCalculateDay(TRUE); //Need to remove absence time when deleting a schedule.
					$s_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );

		break;

	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$fail_transaction = FALSE;

		$sf->StartTransaction();

		//Limit it to 31 days.
		if ( $data['repeat'] > 31 ) {
			$data['repeat'] = 31;
		}
		Debug::Text('Repeating Punch For: '. $data['repeat'] .' Days', __FILE__, __LINE__, __METHOD__,10);

		for($i=0; $i <= (int)$data['repeat']; $i++ ) {
			Debug::Text('Punch Repeat: '. $i, __FILE__, __LINE__, __METHOD__,10);
			if ( $i == 0 ) {
				$date_stamp = $data['date_stamp'];
			} else {
				$date_stamp = $data['date_stamp'] + (86400 * $i);
			}

			Debug::Text('Date Stamp: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__,10);


			$sf = TTnew( 'ScheduleFactory' );

			if ( $i == 0 ) {
				$sf->setID( $data['id'] );
			}
			$sf->setCompany( $current_user->getCompany() );
			$sf->setUser( $data['user_id'] );
			//$sf->setUserDate($data['user_id'], $date_stamp);
			$sf->setOldUserDateID($data['user_date_id']); //Must go after setUserDate(). This is so if the date is modified both dates are recalculated properly.
			$sf->setStatus( $data['status_id'] );
			$sf->setSchedulePolicyID( $data['schedule_policy_id'] );
			$sf->setAbsencePolicyID( $data['absence_policy_id'] );
			$sf->setBranch( $data['branch_id'] );
			$sf->setDepartment( $data['department_id'] );

			if ( isset($data['job_id']) ) {
				$sf->setJob( $data['job_id'] );
			}

			if ( isset($data['job_item_id'] ) ) {
				$sf->setJobItem( $data['job_item_id'] );
			}

			if ( $data['start_time'] != '') {
				$start_time = strtotime( $data['start_time'], $date_stamp ) ;
			} else {
				$start_time = NULL;
			}
			if ( $data['end_time'] != '') {
				Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $date_stamp , __FILE__, __LINE__, __METHOD__,10);
				$end_time = strtotime( $data['end_time'], $date_stamp ) ;

				Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data['end_time']) , __FILE__, __LINE__, __METHOD__,10);

			} else {
				$end_time = NULL;
			}

			$sf->setStartTime( $start_time );
			$sf->setEndTime( $end_time );

			$sf->setNote( $data['note']);

			if ( $sf->isValid() ) {
				$sf->setEnableTimeSheetVerificationCheck(TRUE); //Unverify timesheet if its already verified.
				$sf->setEnableReCalculateDay(TRUE);
				if ( $sf->Save() != TRUE ) {
					$fail_transaction = TRUE;
					break;
				}
			} else {
				$fail_transaction = TRUE;
			}
		}

		if ( $fail_transaction == FALSE ) {
			//$sf->FailTransaction();
			$sf->CommitTransaction();

			Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
			break;
		} else {
			$sf->FailTransaction();
		}

	default:
		if ( $id != '' ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$slf = TTnew( 'ScheduleListFactory' );
			$slf->getById( $id );
			foreach ($slf as $s_obj) {
				$data = array(
									'id' => $s_obj->getId(),
									'user_date_id' => $s_obj->getUserDateId(),
									'user_id' => $s_obj->getUserDateObject()->getUser(),
									'user_full_name' => ( is_object( $s_obj->getUserDateObject()->getUserObject() ) ) ? $s_obj->getUserDateObject()->getUserObject()->getFullName() : NULL,
									'date_stamp' => $s_obj->getUserDateObject()->getDateStamp(),
									'status_id' => $s_obj->getStatus(),
									'start_time' => $s_obj->getStartTime(),
									'parsed_start_time' => $s_obj->getStartTime(),
									'end_time' => $s_obj->getEndTime(),
									'parsed_end_time' => $s_obj->getEndTime(),
									'total_time' => $s_obj->getTotalTime(),
									'schedule_policy_id' => $s_obj->getSchedulePolicyID(),
									'absence_policy_id' => $s_obj->getAbsencePolicyID(),
									'branch_id' => $s_obj->getBranch(),
									'department_id' => $s_obj->getDepartment(),
									'job_id' => $s_obj->getJob(),
									'job_item_id' => $s_obj->getJobItem(),
									'note' => $s_obj->getNote(),
									'pay_period_is_locked' => ( is_object( $s_obj->getUserDateObject() ) AND is_object( $s_obj->getUserDateObject()->getPayPeriodObject() ) ) ? $s_obj->getUserDateObject()->getPayPeriodObject()->getIsLocked() : FALSE,
									'created_date' => $s_obj->getCreatedDate(),
									'created_by' => $s_obj->getCreatedBy(),
									'updated_date' => $s_obj->getUpdatedDate(),
									'updated_by' => $s_obj->getUpdatedBy(),
									'deleted_date' => $s_obj->getDeletedDate(),
									'deleted_by' => $s_obj->getDeletedBy(),
									'is_owner' => $permission->isOwner( ( is_object( $s_obj->getUserDateObject()->getUserObject() ) ) ? $s_obj->getUserDateObject()->getUserObject()->getCreatedBy() : $s_obj->getCreatedBy(), ( is_object( $s_obj->getUserDateObject()->getUserObject() ) ) ? $s_obj->getUserDateObject()->getUserObject()->getId() : 0 ),
									'is_child' => $permission->isChild( ( is_object( $s_obj->getUserDateObject()->getUserObject() ) ) ? $s_obj->getUserDateObject()->getUserObject()->getId() : 0, $permission_children_ids ),
								);
			}
		} elseif ( $action != 'submit' ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			//Get user full name
			if ( $user_id != '' ) {
				$ulf = TTnew( 'UserListFactory' );
				$user_obj = $ulf->getById( $user_id )->getCurrent();
				$user_full_name = $user_obj->getFullName();
				$user_default_branch = $user_obj->getDefaultBranch();
				$user_default_department = $user_obj->getDefaultDepartment();

				$user_date_id = UserDateFactory::getUserDateID($user_id, $date_stamp);

				$pplf = TTnew( 'PayPeriodListFactory' );
				$pplf->getByUserIdAndEndDate( $user_id, $date_stamp );
				if ( $pplf->getRecordCount() > 0 ) {
					$pay_period_is_locked = $pplf->getCurrent()->getIsLocked();
				} else {
					$pay_period_is_locked = FALSE;
				}

			} else {
				$user_id = NULL;
				$user_date_id = NULL;
				$user_full_name = NULL;
				$user_default_branch = NULL;
				$user_default_department = NULL;
				$pay_period_is_locked = FALSE;
			}

			if ( !is_numeric($start_time) ) {
				$start_time = strtotime('08:00 AM');
				$parsed_start_time = $start_time;
			}
			if ( !is_numeric($end_time) ) {
				$end_time = strtotime('05:00 PM');
				$parsed_end_time = $start_time;
			}

			$total_time = $end_time - $start_time;

			$data = array(
								'user_id' => $user_id,
								'status_id' => $status_id,
								'date_stamp' => $date_stamp,
								'user_date_id' => $user_date_id,
								'user_full_name' => $user_full_name,
								'start_time' => $start_time,
								'parsed_start_time' => $start_time,
								'end_time' => $end_time,
								'parsed_end_time' => $end_time,
								'total_time' => $total_time,
								'branch_id' => $user_default_branch,
								'department_id' => $user_default_department,
								'schedule_policy_id' => $schedule_policy_id,
								'absence_policy_id' => $absence_policy_id,
								'pay_period_is_locked' => $pay_period_is_locked
							);
		} else {
			//Get user full name.
			if ( $data['user_id'] != '' ) {
				$ulf = TTnew( 'UserListFactory' );
				$user_obj = $ulf->getById( $data['user_id'] )->getCurrent();
				$user_full_name = $user_obj->getFullName();

				$data['user_id'] = $data['user_id'];
				$data['user_full_name'] = $user_full_name;
			}
		}

		$splf = TTnew( 'SchedulePolicyListFactory' );
		$schedule_policy_options = $splf->getByCompanyIdArray( $current_company->getId() );

		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$absence_policy_options = $aplf->getByCompanyIdArray( $current_company->getId() );

		$blf = TTnew( 'BranchListFactory' );
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = TTnew( 'DepartmentListFactory' );
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		if ( $current_company->getProductEdition() >= 20 ) {
			$jlf = TTnew( 'JobListFactory' );
			$jlf->getByCompanyIdAndUserIdAndStatus( $current_company->getId(),  $current_user->getId(), array(10) );
			$data['job_options'] = $jlf->getArrayByListFactory( $jlf, TRUE, TRUE );
			$data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

			$jilf = TTnew( 'JobItemListFactory' );
			$jilf->getByCompanyId( $current_company->getId() );
			$data['job_item_options'] = $jilf->getArrayByListFactory( $jilf, TRUE );
			$data['job_item_manual_id_options'] = $jilf->getManualIdArrayByListFactory( $jilf, TRUE );
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$data['user_options'] = UserListFactory::getArrayByListFactory( $ulf, TRUE, TRUE );
		if ( $current_company->getProductEdition() > 10 ) {
			$data['user_options'] = Misc::prependArray( array( 0 => '- '. TTi18n::getText('OPEN') .' -' ), $data['user_options'] );
		}

		//Select box options;
		$data['status_options'] = $sf->getOptions('status');
		$data['schedule_policy_options'] = $schedule_policy_options;
		$data['absence_policy_options'] = $absence_policy_options;
		$data['branch_options'] = $branch_options;
		$data['department_options'] = $department_options;

		$smarty->assign_by_ref('data', $data);
		$smarty->assign_by_ref('date_stamp', $date_stamp);

		break;
}

$smarty->assign_by_ref('sf', $sf);

$smarty->display('schedule/EditSchedule.tpl');
?>