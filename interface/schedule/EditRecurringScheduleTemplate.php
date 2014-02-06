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
 * $Revision: 9993 $
 * $Id: EditRecurringScheduleTemplate.php 9993 2013-05-24 20:16:41Z ipso $
 * $Date: 2013-05-24 13:16:41 -0700 (Fri, 24 May 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('recurring_schedule_template','enabled')
		OR !( $permission->Check('recurring_schedule_template','edit') OR $permission->Check('recurring_schedule_template','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Recurring Schedule Template')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'ids',
												'data',
												'week_rows'
												) ) );

if ( isset($week_rows)) {
	foreach( $week_rows as $week_row_id => $week_row ) {
		Debug::Text('Start Time: '. $week_row['start_time'] , __FILE__, __LINE__, __METHOD__,10);

		if ( isset($week_row['start_time']) AND $week_row['start_time'] != '') {
			$week_rows[$week_row_id]['start_time'] = TTDate::strtotime($week_row['start_time']);
		}
		if ( isset($week_row['end_time']) AND $week_row['end_time'] != '' ) {
			$week_rows[$week_row_id]['end_time'] = TTDate::strtotime($week_row['end_time']);
		}
	}
}

$rstcf = TTnew( 'RecurringScheduleTemplateControlFactory' );
$rstf = TTnew( 'RecurringScheduleTemplateFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);
		$redirect=0;

		$rstcf->StartTransaction();

		$rstcf->setId( $data['id'] );
		$rstcf->setCompany( $current_company->getId() );
		$rstcf->setName( $data['name'] );
		$rstcf->setDescription( $data['description'] );

		if ( $rstcf->isValid() ) {
			$rstc_id = $rstcf->Save();

			Debug::Text('aRecurring Schedule Template Control ID: '. $rstc_id , __FILE__, __LINE__, __METHOD__,10);

			if ( $rstc_id === TRUE ) {
				$rstc_id = $data['id'];
			}

			Debug::Text('bRecurring Schedule Template Control ID: '. $rstc_id , __FILE__, __LINE__, __METHOD__,10);

			//Save each weeks data.
			if ( count($week_rows) > 0 ) {
				foreach( $week_rows as $week_row_id => $week_row ) {
					Debug::Text('Row ID: '. $week_row_id .' Week: '. $week_row['week'] , __FILE__, __LINE__, __METHOD__,10);

					if ( $week_row['week'] != '' AND $week_row['week'] > 0 ) {
						if ( $week_row_id > 0 ) {
							$rstf->setID( $week_row_id );
						}
						$rstf->setRecurringScheduleTemplateControl( $rstc_id );
						$rstf->setWeek( $week_row['week'] );
						if ( isset($week_row['absence_policy_id']) ) {
							$rstf->setAbsencePolicyID( $week_row['absence_policy_id'] );
						}

						$rstf->setStatus( $week_row['status_id'] );

						if ( isset($week_row['sun']) ) {
							$rstf->setSun( TRUE );
						} else {
							$rstf->setSun( FALSE );
						}

						if ( isset($week_row['mon']) ) {
							$rstf->setMon( TRUE );
						} else {
							$rstf->setMon( FALSE );
						}

						if ( isset($week_row['tue']) ) {
							$rstf->setTue( TRUE );
						} else {
							$rstf->setTue( FALSE );
						}

						if ( isset($week_row['wed']) ) {
							$rstf->setWed( TRUE );
						} else {
							$rstf->setWed( FALSE );
						}

						if ( isset($week_row['thu']) ) {
							$rstf->setThu( TRUE );
						} else {
							$rstf->setThu( FALSE );
						}

						if ( isset($week_row['fri']) ) {
							$rstf->setFri( TRUE );
						} else {
							$rstf->setFri( FALSE );
						}

						if ( isset($week_row['sat']) ) {
							$rstf->setSat( TRUE );
						} else {
							$rstf->setSat( FALSE );
						}

						if ( isset($week_row['sun']) ) {
							$rstf->setSun( TRUE );
						} else {
							$rstf->setSun( FALSE );
						}

						$rstf->setStartTime( $week_row['start_time'] );
						$rstf->setEndTime( $week_row['end_time'] );

						$rstf->setSchedulePolicyID( $week_row['schedule_policy_id'] );
						$rstf->setBranch( $week_row['branch_id'] );
						$rstf->setDepartment( $week_row['department_id'] );

						if ( isset($week_row['job_id']) ) {
							$rstf->setJob( $week_row['job_id'] );
						}

						if ( isset($week_row['job_item_id']) ) {
							$rstf->setJobItem( $week_row['job_item_id'] );
						}

						if ( isset($week_row['open_shift_multiplier']) ) {
							$rstf->setOpenShiftMultiplier( $week_row['open_shift_multiplier'] );
						}

						if ( $rstf->isValid() ) {
							Debug::Text('Saving Week Row ID: '. $week_row_id, __FILE__, __LINE__, __METHOD__,10);
							$rstf->Save();
						} else {
							$redirect++;
						}
					} else {
						//Delete week
						if ( $week_row_id > 0 ) {
							$rstf->setID( $week_row_id );
							$rstf->setDeleted(TRUE);
							$rstf->Save();
						} else {
							unset($week_row[$week_row_id]);
						}
					}
				}
			}

			if ( $redirect == 0 ) {
				$rstcf->CommitTransaction();
				//$rstcf->FailTransaction();

				Redirect::Page( URLBuilder::getURL( NULL, 'RecurringScheduleTemplateControlList.php') );

				break;

			}
		}
		$rstcf->FailTransaction();
	case 'delete':
		if ( count($ids) > 0) {
			foreach ($ids as $rst_id) {
				if ( $rst_id > 0 ) {
					Debug::Text('Deleting Week Row ID: '. $rst_id, __FILE__, __LINE__, __METHOD__,10);

					$rstlf = TTnew( 'RecurringScheduleTemplateListFactory' );
					$rstlf->getById( $rst_id );

					if ( $rstlf->getRecordCount() == 1 ) {
						foreach($rstlf as $rst_obj ) {
							$rst_obj->setDeleted( TRUE );
							if ( $rst_obj->isValid() ) {
								$rst_obj->Save();
							}
						}
					}
				}
				unset($week_rows[$rst_id]);

			}
			unset($rst_id);
		}

		//Redirect::Page( URLBuilder::getURL( array('id' => $data['id']), 'EditRecurringScheduleTemplate.php') );

		//break;
	default:
		$rstlf = TTnew( 'RecurringScheduleTemplateListFactory' );
		$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' );

		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);
			$rstclf->getByIdAndCompanyId( $id, $current_company->getID() );

			foreach ($rstclf as $rstc_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $rstc_obj->getId(),
									'name' => $rstc_obj->getName(),
									'description' => $rstc_obj->getDescription(),
									'created_date' => $rstc_obj->getCreatedDate(),
									'created_by' => $rstc_obj->getCreatedBy(),
									'updated_date' => $rstc_obj->getUpdatedDate(),
									'updated_by' => $rstc_obj->getUpdatedBy(),
									'deleted_date' => $rstc_obj->getDeletedDate(),
									'deleted_by' => $rstc_obj->getDeletedBy()
								);

				//Get week data
				$rstlf->getByRecurringScheduleTemplateControlId( $rstc_obj->getId() );
				if ( $rstlf->getRecordCount() > 0 ) {
					foreach( $rstlf as $rst_obj) {
						$week_rows[$rst_obj->getId()] = array(
											'id' => $rst_obj->getId(),
											'week' => $rst_obj->getWeek(),
											'status_id' => $rst_obj->getStatus(),
											'absence_policy_id' => $rst_obj->getAbsencePolicyID(),
											'sun' => $rst_obj->getSun(),
											'mon' => $rst_obj->getMon(),
											'tue' => $rst_obj->getTue(),
											'wed' => $rst_obj->getWed(),
											'thu' => $rst_obj->getThu(),
											'fri' => $rst_obj->getFri(),
											'sat' => $rst_obj->getSat(),
											'start_time' => $rst_obj->getStartTime(),
											'end_time' => $rst_obj->getEndTime(),
											'total_time' => $rst_obj->getTotalTime(),
											'schedule_policy_id' => $rst_obj->getSchedulePolicyID(),
											'branch_id' => $rst_obj->getBranch(),
											'department_id' => $rst_obj->getDepartment(),
											'job_id' => $rst_obj->getJob(),
											'job_item_id' => $rst_obj->getJobItem(),
											'open_shift_multiplier' => $rst_obj->getOpenShiftMultiplier(),
											);
					}
				} else {
					$week_rows[-1] = array(
									'id' => -1,
									'week' => 1
									);

				}

			}
		} elseif ( $action == 'add_week' ) {
			Debug::Text('Adding Blank Week', __FILE__, __LINE__, __METHOD__,10);
			if ( !isset($week_rows) OR ( isset($week_rows) AND !is_array( $week_rows ) ) ) {
				//If they delete all weeks and try to add a new one.
				$week_rows[0] = array(
								'id' => -1,
								'week' => 0,
								'status_id' => 10, //Working
								'mon' => TRUE,
								'tue' => TRUE,
								'wed' => TRUE,
								'thu' => TRUE,
								'fri' => TRUE,
								'start_time' => strtotime('08:00'),
								'end_time' => strtotime('17:00'),
								'branch_id' => -1,
								'department_id' => -1,
								'schedule_policy_id' => 0,
								'open_shift_multiplier' => 1,
								);

				$row_keys = array_keys($week_rows);
				sort($row_keys);

				$next_blank_id = 0;
				$lowest_id = 0;
			} else {
				$row_keys = array_keys($week_rows);
				sort($row_keys);

				Debug::Text('Lowest ID: '. $row_keys[0], __FILE__, __LINE__, __METHOD__,10);
				$lowest_id = $row_keys[0];
				if ( $lowest_id < 0 ) {
					$next_blank_id = $lowest_id-1;
				} else {
					$next_blank_id = -1;
				}
			}

			Debug::Text('Next Blank ID: '. $next_blank_id, __FILE__, __LINE__, __METHOD__,10);

			//Find next week
			$last_new_week = $week_rows[$row_keys[0]]['week'];
			$last_saved_week = $week_rows[array_pop($row_keys)]['week'];
			Debug::Text('Last New Week: '. $last_new_week .' Last Saved Week: '. $last_saved_week, __FILE__, __LINE__, __METHOD__,10);
			if ( $last_new_week > $last_saved_week) {
				$last_week = $last_new_week;
			} else {
				$last_week = $last_saved_week;
			}
			Debug::Text('Last Week: '. $last_week, __FILE__, __LINE__, __METHOD__,10);

			$next_total_time = 0;
			if ( count($week_rows) > 0 ) {
				foreach( $week_rows as $week_row_id => $week_row ) {
					if ( $week_row['week'] != '' AND $week_row['week'] > 0 ) {
						Debug::Text('Row ID: '. $week_row_id .' Week: '. $week_row['week'] .' Schedule Policy ID: '. $week_row['schedule_policy_id'], __FILE__, __LINE__, __METHOD__,10);

						$rstf = TTnew( 'RecurringScheduleTemplateFactory' );
						$rstf->setStartTime( $week_row['start_time'] );
						$rstf->setEndTime( $week_row['end_time'] );

						$rstf->setSchedulePolicyID( $week_row['schedule_policy_id'] );
						$rstf->preSave();
						$week_rows[$week_row_id]['total_time'] = $rstf->getTotalTime();
						if ( $week_row_id == $lowest_id ) {
							$next_total_time = $week_rows[$week_row_id]['total_time'];
						}
					}

				}
			}

			$week_rows[$next_blank_id] = array(
							'id' => $next_blank_id,
							'week' => $last_week+1,
							'status_id' => 10, //Working
							'start_time' => $week_rows[$lowest_id]['start_time'],
							'end_time' => $week_rows[$lowest_id]['end_time'],
							'total_time' => $next_total_time,
							'schedule_policy_id' => $week_rows[$lowest_id]['schedule_policy_id'],
							'branch_id' => $week_rows[$lowest_id]['branch_id'],
							'department_id' => $week_rows[$lowest_id]['department_id'],
							'mon' => @$week_rows[$lowest_id]['mon'],
							'tue' => @$week_rows[$lowest_id]['tue'],
							'wed' => @$week_rows[$lowest_id]['wed'],
							'thu' => @$week_rows[$lowest_id]['thu'],
							'fri' => @$week_rows[$lowest_id]['fri'],
							'sat' => @$week_rows[$lowest_id]['sat'],
							'sun' => @$week_rows[$lowest_id]['sun'],
							'open_shift_multiplier' => @$week_rows[$lowest_id]['open_shift_multiplier'],
							);
		} elseif ( $action != 'submit' AND $action != 'delete' ) {
			$week_rows[-1] = array(
							'id' => -1,
							'week' => 1,
							'status_id' => 10, //Working
							'mon' => TRUE,
							'tue' => TRUE,
							'wed' => TRUE,
							'thu' => TRUE,
							'fri' => TRUE,
							'start_time' => strtotime('08:00'),
							'end_time' => strtotime('17:00'),
							'total_time' => (9*3600),
							'branch_id' => -1,
							'department_id' => -1,
							'schedule_policy_id' => 0,
							'open_shift_multiplier' => 1,
							);
		}

		$prepend_array_option = array( 0 => '--', -1 => TTi18n::gettext('-- Default --') );

		//Select box options;
		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$absence_policy_options = $aplf->getByCompanyIdArray( $current_company->getId() );

		$splf = TTnew( 'SchedulePolicyListFactory' );
		$data['schedule_options'] = $splf->getByCompanyIdArray( $current_company->getId() );

		$blf = TTnew( 'BranchListFactory' );
		$blf->getByCompanyId( $current_company->getId() );
		$data['branch_options'] = Misc::prependArray( $prepend_array_option,  $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		//$data['branch_options']  = Misc::prependArray( array( -1 => '-- Default --' ), $blf->getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );

		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$data['department_options'] = Misc::prependArray( $prepend_array_option,  $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		//$data['department_options'] = $dlf->getByCompanyIdArray( $current_company->getId() );

		if ( $current_company->getProductEdition() >= 20 ) {
			$jlf = TTnew( 'JobListFactory' );
			$data['job_options'] = $jlf->getByCompanyIdAndStatusArray( $current_company->getId(), array(10,20,30,40), TRUE );

			$jilf = TTnew( 'JobItemListFactory' );
			$data['job_item_options'] = $jilf->getByCompanyIdArray( $current_company->getId(), TRUE );
		}

		$data['status_options'] = $rstlf->getOptions('status');
		$data['absence_policy_options'] = Misc::prependArray( array( '- '. TTi18n::getText('Select Policy') .' -'), $absence_policy_options );

		//var_dump($week_rows);
		$smarty->assign_by_ref('data', $data);
		$smarty->assign_by_ref('week_rows', $week_rows);

		break;
}

$smarty->assign_by_ref('rstcf', $rstcf);
$smarty->assign_by_ref('rstf', $rstf);

$smarty->display('schedule/EditRecurringScheduleTemplate.tpl');
?>