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
 * $Id: EditPunch.php 9993 2013-05-24 20:16:41Z ipso $
 * $Date: 2013-05-24 13:16:41 -0700 (Fri, 24 May 2013) $
 */
require_once('../../includes/global.inc.php');

//Debug::setVerbosity(11);

$skip_message_check = TRUE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('punch','enabled')
		OR !( $permission->Check('punch','edit')
				OR $permission->Check('punch','edit_own')
				OR $permission->Check('punch','edit_child')
				 ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Punch')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'punch_control_id',
												'user_id',
												'date_stamp',
												'status_id',
												'pc_data'
												) ) );

$punch_full_time_stamp = NULL;
if ( isset($pc_data) ) {
	if ( $pc_data['date_stamp'] != '' AND $pc_data['time_stamp'] != '') {
		$punch_full_time_stamp = TTDate::parseDateTime($pc_data['date_stamp'].' '.$pc_data['time_stamp']);
		$pc_data['punch_full_time_stamp'] = $punch_full_time_stamp;
		$pc_data['time_stamp'] = $punch_full_time_stamp;
	} else {
		$pc_data['punch_full_time_stamp'] = NULL;
	}

	if ( $pc_data['date_stamp'] != '') {
		$pc_data['date_stamp'] = TTDate::parseDateTime($pc_data['date_stamp']);
	}
}

$pcf = TTnew( 'PunchControlFactory' );
$pf = TTnew( 'PunchFactory' );
$ulf = TTnew( 'UserListFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'delete':
		Debug::Text('Delete!', __FILE__, __LINE__, __METHOD__,10);

		$plf = TTnew( 'PunchListFactory' );
		$plf->getById( $pc_data['punch_id'] );
		if ( $plf->getRecordCount() > 0 ) {
			foreach($plf as $p_obj) {
				$p_obj->setUser( $p_obj->getPunchControlObject()->getUserDateObject()->getUser() );
				$p_obj->setDeleted(TRUE);

				//These aren't doing anything because they aren't acting on the PunchControl object?
				$p_obj->setEnableCalcTotalTime( TRUE );
				$p_obj->setEnableCalcSystemTotalTime( TRUE );
				$p_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
				$p_obj->setEnableCalcUserDateTotal( TRUE );
				$p_obj->setEnableCalcException( TRUE );
				if (  $p_obj->isValid() ) {
					$p_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );

		break;
	case 'submit':
		//Debug::setBufferOutput(FALSE);
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$fail_transaction=FALSE;

		$pf->StartTransaction();

		//Limit it to 31 days, just in case someone makes an error entering the dates or something.
		if ( $pc_data['repeat'] > 31 ) {
			$pc_data['repeat'] = 31;
		}
		Debug::Text('Repeating Punch For: '. $pc_data['repeat'] .' Days', __FILE__, __LINE__, __METHOD__,10);

		for( $i=0; $i <= (int)$pc_data['repeat']; $i++ ) {
			$pf = TTnew( 'PunchFactory' );

			Debug::Text('Punch Repeat: '. $i, __FILE__, __LINE__, __METHOD__,10);
			if ( $i == 0 ) {
				$time_stamp = $punch_full_time_stamp;
			} else {
				$time_stamp = $punch_full_time_stamp + (86400 * $i);
			}

			Debug::Text('Punch Full Time Stamp: '. date('r', $time_stamp) .'('.$time_stamp.')', __FILE__, __LINE__, __METHOD__,10);

			//Set User before setTimeStamp so rounding can be done properly.
			$pf->setUser( $pc_data['user_id'] );

			if ( $i == 0 ) {
				$pf->setId( $pc_data['punch_id'] );
			}
			if ( isset($data['transfer']) ) {
				$pf->setTransfer( TRUE, $time_stamp ); //Include timestamp so we can tell if its the first punch or not.
			}

			$pf->setType( $pc_data['type_id'] );
			$pf->setStatus( $pc_data['status_id'] );
			if ( isset($pc_data['disable_rounding']) ) {
				$enable_rounding = FALSE;
			} else {
				$enable_rounding = TRUE;
			}

			$pf->setTimeStamp( $time_stamp, $enable_rounding );

			if ( $i == 0 AND isset( $pc_data['id'] ) AND $pc_data['id']  != '' ) {
				Debug::Text('Using existing Punch Control ID: '. $pc_data['id'], __FILE__, __LINE__, __METHOD__,10);
				$pf->setPunchControlID( $pc_data['id'] );
			} else {
				Debug::Text('Finding Punch Control ID: '. $pc_data['id'], __FILE__, __LINE__, __METHOD__,10);
				$pf->setPunchControlID( $pf->findPunchControlID() );
			}

			if ( $pf->isNew() ) {
				$pf->setActualTimeStamp( $time_stamp );
				//$pf->setOriginalTimeStamp( $pf->getTimeStamp() ); //set in preSave()
			}

			if ( $pf->isValid() == TRUE ) {

				if ( $pf->Save( FALSE ) == TRUE ) {
					$pcf = TTnew( 'PunchControlFactory' );
					$pcf->setId( $pf->getPunchControlID() );
					$pcf->setPunchObject( $pf );

					if ( $i == 0 AND $pc_data['user_date_id'] != '' ) {
						//This is important when editing a punch, without it there can be issues calculating exceptions
						//because if a specific punch was modified that caused the day to change, smartReCalculate
						//may only be able to recalculate a single day, instead of both.
						$pcf->setUserDateID( $pc_data['user_date_id'] );
					}

					if ( isset($pc_data['branch_id']) ) {
						$pcf->setBranch( $pc_data['branch_id'] );
					}
					if ( isset($pc_data['department_id']) ) {
						$pcf->setDepartment( $pc_data['department_id'] );
					}
					if ( isset($pc_data['job_id']) ) {
						$pcf->setJob( $pc_data['job_id'] );
					}
					if ( isset($pc_data['job_item_id']) ) {
						$pcf->setJobItem( $pc_data['job_item_id'] );
					}
					if ( isset($pc_data['quantity']) ) {
						$pcf->setQuantity( $pc_data['quantity'] );
					}
					if ( isset($pc_data['bad_quantity']) ) {
						$pcf->setBadQuantity( $pc_data['bad_quantity'] );
					}
					if ( isset($pc_data['note']) ) {
						$pcf->setNote( $pc_data['note'] );
					}

					if ( isset($pc_data['other_id1']) ) {
						$pcf->setOtherID1( $pc_data['other_id1'] );
					}
					if ( isset($pc_data['other_id2']) ) {
						$pcf->setOtherID2( $pc_data['other_id2'] );
					}
					if ( isset($pc_data['other_id3']) ) {
						$pcf->setOtherID3( $pc_data['other_id3'] );
					}
					if ( isset($pc_data['other_id4']) ) {
						$pcf->setOtherID4( $pc_data['other_id4'] );
					}
					if ( isset($pc_data['other_id5']) ) {
						$pcf->setOtherID5( $pc_data['other_id5'] );
					}

					$pcf->setEnableStrictJobValidation( TRUE );
					$pcf->setEnableCalcUserDateID( TRUE );
					$pcf->setEnableCalcTotalTime( TRUE );
					$pcf->setEnableCalcSystemTotalTime( TRUE );
					$pcf->setEnableCalcWeeklySystemTotalTime( TRUE );
					$pcf->setEnableCalcUserDateTotal( TRUE );
					$pcf->setEnableCalcException( TRUE );

					if ( $pcf->isValid() == TRUE ) {
						Debug::Text(' Punch Control is valid, saving...: ', __FILE__, __LINE__, __METHOD__,10);

						if ( $pcf->Save( TRUE, TRUE ) != TRUE ) { //Force isNew() lookup.
							Debug::Text(' aFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
							$fail_transaction = TRUE;
							break;
						}
					} else {
						Debug::Text(' bFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
						$fail_transaction = TRUE;
						break;
					}
				} else {
					Debug::Text(' cFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
					$fail_transaction = TRUE;
					break;
				}
			} else {
				Debug::Text(' dFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
				$fail_transaction = TRUE;
				break;
			}
		}

		if ( $fail_transaction == FALSE ) {
			//$pf->FailTransaction();
			$pf->CommitTransaction();

			Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
			break;
		} else {
			$pf->FailTransaction();
		}
	default:
		if ( $id != '' AND $action != 'submit' ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$pclf = TTnew( 'PunchControlListFactory' );
			$pclf->getByPunchId( $id );

			foreach ($pclf as $pc_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				//Get punches
				$plf = TTnew( 'PunchListFactory' );
				//$plf->getByPunchControlId( $pc_obj->getId() );
				$plf->getById( $id );
				if ( $plf->getRecordCount() > 0 ) {
					$p_obj = $plf->getCurrent();
				} else {
					$punch_data = NULL;
				}

				//Get Station data.
				$station_data = FALSE;
				$slf = TTnew( 'StationListFactory' );
				if ( $p_obj->getStation() != FALSE ) {
					$slf->getById( $p_obj->getStation() );
					if ( $slf->getRecordCount() > 0 ) {
						$s_obj = $slf->getCurrent();

						$station_data = array(
											'id' => $s_obj->getId(),
											'type_id' => $s_obj->getType(),
											'type' => Option::getByKey($s_obj->getType(), $s_obj->getOptions('type') ),
											'station_id' => $s_obj->getStation(),
											'source' => $s_obj->getSource(),
											'description' => Misc::TruncateString( $s_obj->getDescription(), 20 )
											);
					}
				}

				$pc_data = array(
									'id' => $pc_obj->getId(),
									'user_date_id' => $pc_obj->getUserDateId(),
									'user_id' => $pc_obj->getUserDateObject()->getUser(),
									'user_full_name' => $pc_obj->getUserDateObject()->getUserObject()->getFullName(),
									'pay_period_id' => $pc_obj->getUserDateObject()->getPayPeriod(),
									//This causes punches that span 24hrs to not be edited correct
									//the date being the date of the first punch, not the last if we're
									//editing the last.
									//'date_stamp' => $pc_obj->getUserDateObject()->getDateStamp(),
									'branch_id' => $pc_obj->getBranch(),
									'department_id' => $pc_obj->getDepartment(),
									'job_id' => $pc_obj->getJob(),
									'job_item_id' => $pc_obj->getJobItem(),
									'quantity' => (float)$pc_obj->getQuantity(),
									'bad_quantity' => (float)$pc_obj->getBadQuantity(),
									'note' => $pc_obj->getNote(),

									'other_id1' => $pc_obj->getOtherID1(),
									'other_id2' => $pc_obj->getOtherID2(),
									'other_id3' => $pc_obj->getOtherID3(),
									'other_id4' => $pc_obj->getOtherID4(),
									'other_id5' => $pc_obj->getOtherID5(),

									//Punch Data
									'punch_id' => $p_obj->getId(),
									'status_id' => $p_obj->getStatus(),
									'type_id' => $p_obj->getType(),
									'station_id' => $p_obj->getStation(),
									'station_data' => $station_data,
									'time_stamp' => $p_obj->getTimeStamp(),
									//Use this so the date is always insync with the time.
									'date_stamp' => $p_obj->getTimeStamp(),
									'original_time_stamp' => $p_obj->getOriginalTimeStamp(),
									'actual_time_stamp' => $p_obj->getActualTimeStamp(),
									'longitude' => $p_obj->getLongitude(),
									'latitude' => $p_obj->getLatitude(),

									'created_date' => $p_obj->getCreatedDate(),
									'created_by' => $p_obj->getCreatedBy(),
									'created_by_name' => (string)$ulf->getFullNameById( $p_obj->getCreatedBy() ),
									'updated_date' => $p_obj->getUpdatedDate(),
									'updated_by' => $p_obj->getUpdatedBy(),
									'updated_by_name' => (string)$ulf->getFullNameById( $p_obj->getUpdatedBy() ),
									'deleted_date' => $p_obj->getDeletedDate(),
									'deleted_by' => $p_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			//UserID has to be set at minimum
			if ( $punch_control_id != '' ) {
				Debug::Text(' Punch Control ID was passed: '. $punch_control_id, __FILE__, __LINE__, __METHOD__,10);

				//Get previous punch, and default timestamp to that.
				$plf = TTnew( 'PunchListFactory' );
				$plf->getPreviousPunchByPunchControlID( $punch_control_id );
				if ( $plf->getRecordCount() > 0 ) {
					$prev_punch_obj = $plf->getCurrent();
					$time_stamp = $prev_punch_obj->getTimeStamp()+3600;
					$date_stamp = $prev_punch_obj->getTimeStamp(); //Match date with previous punch as well, incase a new day hasnt been triggered yet.
				} else {
					$time_stamp = TTDate::getTime();
					$date_stamp = NULL;
				}

				$pclf = TTnew( 'PunchControlListFactory' );
				$pclf->getById( $punch_control_id );
				if ( $pclf->getRecordCount() > 0 ) {
					$pc_obj = $pclf->getCurrent();

					if ( $date_stamp == NULL ) {
						$date_stamp = $pc_obj->getUserDateObject()->getDateStamp();
					}

					$pc_data = array(
									'id' => $pc_obj->getId(),
									'user_id' => $pc_obj->getUserDateObject()->getUser(),
									'user_full_name' => $pc_obj->getUserDateObject()->getUserObject()->getFullName(),
									'date_stamp' => $date_stamp,
									'user_date_id' => $pc_obj->getUserDateObject()->getId(),
									'time_stamp' => $time_stamp,
									'branch_id' => $pc_obj->getBranch(),
									'department_id' => $pc_obj->getDepartment(),
									'job_id' => $pc_obj->getJob(),
									'job_item_id' => $pc_obj->getJobItem(),
									'quantity' => (float)$pc_obj->getQuantity(),
									'bad_quantity' => (float)$pc_obj->getBadQuantity(),
									'note' => $pc_obj->getNote(),

									'other_id1' => $pc_obj->getOtherID1(),
									'other_id2' => $pc_obj->getOtherID2(),
									'other_id3' => $pc_obj->getOtherID3(),
									'other_id4' => $pc_obj->getOtherID4(),
									'other_id5' => $pc_obj->getOtherID5(),

									'status_id' => $status_id
									);
				}
			} elseif ( $user_id != '' ) {
				Debug::Text(' User ID was passed: '. $user_id .' Date Stamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__,10);

				//Don't guess too much. If they click a day to add a punch. Make sure that punch is on that day.
				if ( isset($date_stamp) AND $date_stamp != '' ) {
					$time_stamp = $date_stamp + (3600*12); //Noon
				} else {
					$time_stamp = TTDate::getBeginDayEpoch( TTDate::getTime() ) + (3600*12); //Noon
				}

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
				if ( $ulf->getRecordCount() > 0 ) {
					$user_obj = $ulf->getCurrent();

					$pc_data = array(
									'user_id' => $user_obj->getId(),
									'user_full_name' => $user_obj->getFullName(),
									'date_stamp' => $date_stamp,
									'time_stamp' => $time_stamp,
									'status_id' => $status_id,
									'branch_id' => $user_obj->getDefaultBranch(),
									'department_id' => $user_obj->getDefaultDepartment(),
									'quantity' => 0,
									'bad_quantity' => 0
									);
				}

				unset($time_stamp, $plf);
			}
		}


		$blf = TTnew( 'BranchListFactory' );
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = TTnew( 'DepartmentListFactory' );
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		if ( $current_company->getProductEdition() >= 20 ) {
			$jlf = TTnew( 'JobListFactory' );
			$jlf->getByCompanyIdAndUserIdAndStatus( $current_company->getId(), $pc_data['user_id'], array(10,20,30,40) );
			$pc_data['job_options'] = $jlf->getArrayByListFactory( $jlf, TRUE, TRUE );
			$pc_data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

			$jilf = TTnew( 'JobItemListFactory' );
			$jilf->getByCompanyId( $current_company->getId() );
			$pc_data['job_item_options'] = $jilf->getArrayByListFactory( $jilf, TRUE, TRUE );
			$pc_data['job_item_manual_id_options'] = $jilf->getManualIdArrayByListFactory( $jilf, TRUE );
		}

		//Select box options;
		$pc_data['status_options'] = $pf->getOptions('status');
		$pc_data['type_options'] = $pf->getOptions('type');
		$pc_data['branch_options'] = $branch_options;
		$pc_data['department_options'] = $department_options;

		//Get other field names
		$oflf = TTnew( 'OtherFieldListFactory' );
		$pc_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getId(), 15 );

		//Debug::Text('pc_data[date_stamp]: '. TTDate::getDate('DATE+TIME', $pc_data['date_stamp']), __FILE__, __LINE__, __METHOD__,10);
		$smarty->assign_by_ref('pc_data', $pc_data);

		break;
}

$smarty->assign_by_ref('pcf', $pcf);
$smarty->assign_by_ref('pf', $pf);

$smarty->display('punch/EditPunch.tpl');
?>