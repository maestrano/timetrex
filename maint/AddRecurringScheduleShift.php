<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
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

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

$minimum_add_shift_offset = (3600 * 2) ; //Add shifts at least 2hrs before they start.
$lookup_shift_offset = (3600 * 10); //Lookup shifts that started X hrs before now.

$current_epoch = TTDate::getTime();
//$current_epoch = strtotime('05-Sep-2013 10:00 PM');
Debug::text('Current Epoch: '. TTDate::getDate('DATE+TIME', $current_epoch ), __FILE__, __LINE__, __METHOD__, 10);

//Initial Start/End dates need to cover all timezones, we narrow it done further once we change to each users timezone later on.
$initial_start_date = TTDate::getBeginDayEpoch( $current_epoch - $lookup_shift_offset );
$initial_end_date = ($current_epoch + 86400);
Debug::text('Initial Start Date: '. TTDate::getDate('DATE+TIME', $initial_start_date ) .' End Date: '. TTDate::getDate('DATE+TIME', $initial_end_date ), __FILE__, __LINE__, __METHOD__, 10);

$clf = new CompanyListFactory();
$clf->getAll();
if ( $clf->getRecordCount() > 0 ) {
	foreach ( $clf as $c_obj ) {
		if ( $c_obj->getStatus() != 30 ) {
			Debug::text('Company: '. $c_obj->getName() .' ID: '. $c_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

			$rsclf = new RecurringScheduleControlListFactory();
			$rsclf->getByCompanyIdAndStartDateAndEndDate( $c_obj->getId(), $initial_start_date, $initial_end_date );
			if ( $rsclf->getRecordCount() > 0 ) {

				Debug::text('Recurring Schedule Control List Record Count: '. $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

				foreach( $rsclf as $rsc_obj ) {
					//$rsclf->StartTransaction(); Wrap each individual schedule in its own transaction instead.

					Debug::text('Recurring Schedule ID: '. $rsc_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					//Debug::Arr($rsc_obj->getUser(), 'Users assigned to Schedule', __FILE__, __LINE__, __METHOD__, 10);

					$user_ids = $rsc_obj->getUser();
					$total_user_ids = count($user_ids);

					if ( is_array($user_ids) AND $total_user_ids > 0 ) {
						$i = 0;
						foreach( $user_ids as $user_id ) {
							Debug::text('I: '. $i .'/'. $total_user_ids .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

							//Get User object.
							if ( $user_id > 0 ) {
								$ulf = new UserListFactory();
								$ulf->getById( $user_id );
								if ( $ulf->getRecordCount() > 0 ) {
									$user_obj = $ulf->getCurrent();
									$user_obj_prefs = $user_obj->getUserPreferenceObject();
									if ( is_object( $user_obj_prefs ) ) {
										$user_obj_prefs->setTimeZonePreferences();
									} else {
										//Use system timezone.
										TTDate::setTimeZone();
									}
									Debug::text('User Name: '. $user_obj->getUserName(), __FILE__, __LINE__, __METHOD__, 10);
								} else {
									Debug::text('Skipping User...'. $user_id, __FILE__, __LINE__, __METHOD__, 10);
									continue; //Skip user.
								}
							} elseif ( $user_id == 0 ) {
								Debug::text('OPEN User...'. $user_id .' Not committed open shifts to prevent duplicates.', __FILE__, __LINE__, __METHOD__, 10);

								//We can't commit OPEN shifts, as there is no conflict checking for these and they this will create 10's or 100's per day each time this runs.
								continue;

								//Use raw times from recurring schedules in the timezone.
								//TTDate::setTimeZone();
							}

							//Get maximum schedule policy start/stop window, so we know when to create the scheduled shift.
							//This allows for large start/stop windows like 6-8hrs. So shifts are always created one hour before the schedule start/stop window.
							$splf = TTnew('SchedulePolicyListFactory');
							$max_start_stop_window = ( (int)$splf->getMaximumStartStopWindowByCompanyIdAndRecurringScheduleControlID( $c_obj->getID(), $rsc_obj->getRecurringScheduleTemplateControl() ) + 3600 );
							if ( $max_start_stop_window < $minimum_add_shift_offset ) {
								$max_start_stop_window = $minimum_add_shift_offset;
							}

							Debug::text('Current Epoch: '. TTDate::getDate('DATE+TIME', $current_epoch ), __FILE__, __LINE__, __METHOD__, 10);
							$start_date = TTDate::getBeginDayEpoch( $current_epoch - $lookup_shift_offset );
							$end_date = ($current_epoch + $max_start_stop_window);
							Debug::text('Start Date: '. TTDate::getDate('DATE+TIME', $start_date ) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date ) .' Max Start/Stop Window: '. $max_start_stop_window, __FILE__, __LINE__, __METHOD__, 10);

							//Make sure employee is employed in this time frame.
							//Need to handle the hire_date and lookup_shift_offset properly so shifts can be created on the employees hire date.
							if ( isset($user_obj) AND
									(
										( $user_obj->getHireDate() != '' AND $start_date <= TTDate::getBeginDayEpoch( ($user_obj->getHireDate() - $lookup_shift_offset) ) )
										OR
										( $user_obj->getTerminationDate() != '' AND $start_date > $user_obj->getTerminationDate() )
									)
									) {
								Debug::text('Skipping User due to hire/termination date... '. $user_id  .' Start Date: '. TTDate::getDate('DATE+TIME', $start_date ) .' Hire Date: '. TTDate::getDate('DATE+TIME', $user_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10);
								continue; //Skip user.
							}

							//Set the timezone before getting the recurring schedule shifts
							//so we prevent timezone issues and DST issues from arising.
							$recurring_schedule_days = $rsc_obj->getShiftsByStartDateAndEndDate( $start_date, $end_date );
							//Debug::Arr($recurring_schedule_days, 'Recurring Schedule Shifts', __FILE__, __LINE__, __METHOD__, 10);

							if ( $recurring_schedule_days !== FALSE ) {
								foreach( $recurring_schedule_days as $date_stamp => $recurring_schedule_shifts ) {
									Debug::text('Recurring Schedule Shift Date Stamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);
									foreach($recurring_schedule_shifts as $recurring_schedule_shift ) {

										$recurring_schedule_shift_start_time = TTDate::strtotime( $recurring_schedule_shift['start_time'] );
										$recurring_schedule_shift_end_time = TTDate::strtotime( $recurring_schedule_shift['end_time'] );

										Debug::text('(After User TimeZone)Recurring Schedule Shift Start Time: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ) .' End Time: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_end_time ), __FILE__, __LINE__, __METHOD__, 10);
										//Make sure punch pairs fall within limits

										if ( $recurring_schedule_shift_start_time < ( $current_epoch + $max_start_stop_window ) ) {
											Debug::text('Recurring Schedule Shift Start Time falls within Limits: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ), __FILE__, __LINE__, __METHOD__, 10);

											//Need to support recurring scheduled absences.
											$status_id = $recurring_schedule_shift['status_id'];
											$absence_policy_id = $recurring_schedule_shift['absence_policy_id'];

											//Make sure we not already added this schedule shift.
											//And that no schedule shifts overlap this one.
											//Use the isValid() function for this
											$sf = new ScheduleFactory();

											$sf->StartTransaction();

											$sf->setCompany( $c_obj->getID() );
											$sf->setUser( $user_id );

											//Find the date that the shift will be assigned to so we know if its a holiday or not.
											if ( is_object( $sf->getPayPeriodScheduleObject() ) ) {
												$date_stamp = $sf->getPayPeriodScheduleObject()->getShiftAssignedDate( $recurring_schedule_shift_start_time, $recurring_schedule_shift_end_time, $sf->getPayPeriodScheduleObject()->getShiftAssignedDay() );
											} else {
												$date_stamp = $recurring_schedule_shift_start_time;
											}

											//Is this a holiday?
											$hlf = new HolidayListFactory();
											$hlf->getByPolicyGroupUserIdAndDate( $user_id, TTDate::getBeginDayEpoch( $date_stamp ) );
											if ( $hlf->getRecordCount() > 0 ) {
												$h_obj = $hlf->getCurrent();

												Debug::text('Found Holiday! Name: '. $h_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

												//Ignore after holiday eligibility when scheduling, since it will always fail.
												if ( $h_obj->isEligible( $user_id, TRUE ) ) {
													Debug::text('User is Eligible...', __FILE__, __LINE__, __METHOD__, 10);

													//Get Holiday Policy info
													$status_id = $h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus();
													$absence_policy_id = $h_obj->getHolidayPolicyObject()->getAbsencePolicyID();
													Debug::text('Default Schedule Status: '. $status_id, __FILE__, __LINE__, __METHOD__, 10);
												} else {
													Debug::text('User is NOT Eligible...', __FILE__, __LINE__, __METHOD__, 10);
												}
											} else {
												Debug::text('No Holidays on this day: ', __FILE__, __LINE__, __METHOD__, 10);
											}
											unset($hlf, $h_obj);
											Debug::text('Schedule Status ID: '. $status_id, __FILE__, __LINE__, __METHOD__, 10);

											$profiler->startTimer( "Add Schedule");

											//No longer need to call findUserDate()
											//$sf->findUserDate( $user_id, $recurring_schedule_shift_start_time );
											$sf->setStatus( $status_id ); //Working
											$sf->setStartTime( $recurring_schedule_shift_start_time );
											$sf->setEndTime( $recurring_schedule_shift_end_time );
											$sf->setSchedulePolicyID( $recurring_schedule_shift['schedule_policy_id'] );

											if ( isset($absence_policy_id) AND $absence_policy_id > 0 ) {
												$sf->setAbsencePolicyID( $absence_policy_id );
											}
											unset($absence_policy_id);

											if ( isset( $user_obj ) AND $recurring_schedule_shift['branch_id'] == -1 ) {
												$sf->setBranch( $user_obj->getDefaultBranch() );
											} else {
												$sf->setBranch( $recurring_schedule_shift['branch_id'] );
											}

											if ( isset( $user_obj ) AND $recurring_schedule_shift['department_id'] == -1 ) {
												$sf->setDepartment( $user_obj->getDefaultDepartment() );
											} else {
												$sf->setDepartment( $recurring_schedule_shift['department_id'] );
											}

											if ( isset( $user_obj ) AND $recurring_schedule_shift['job_id'] == -1 ) {
												$sf->setJob( $user_obj->getDefaultJob() );
											} else {
												$sf->setJob( $recurring_schedule_shift['job_id'] );
											}

											if ( isset( $user_obj ) AND $recurring_schedule_shift['job_item_id'] == -1 ) {
												$sf->setJobItem( $user_obj->getDefaultJobItem() );
											} else {
												$sf->setJobItem( $recurring_schedule_shift['job_item_id'] );
											}
											
											if ( $sf->isValid() ) {

												//Recalculate if its a absence schedule, so the holiday
												//policy takes effect.
												//Always re-calculate, this way it automatically applies dock time and holiday time.
												//Recalculate at the end of the day in a cronjob.
												//Part of the reason is that if they have a dock policy, it will show up as
												//docking them time during the entire day.
												//$sf->setEnableReCalculateDay(FALSE);

												//Only for holidays do we calculate the day right away.
												//So they don't have to wait 24hrs to see stat time.
												//Also need to recalculate if the schedule was added after the schedule has already started.
												if ( ( $status_id == 20 AND $rsc_obj->getAutoFill() == FALSE )
														OR $sf->getStartTime() <= $current_epoch ) {
													$sf->setEnableReCalculateDay(TRUE);
												} else {
													$sf->setEnableReCalculateDay(FALSE); //Don't need to re-calc right now?
												}

												$schedule_result = $sf->Save();
												$sf->CommitTransaction();

												$profiler->startTimer( "Add Punch");

												Debug::text('Schedule Checks out, saving: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ), __FILE__, __LINE__, __METHOD__, 10);
												if ( $schedule_result == TRUE
														AND $user_id > 0
														AND $rsc_obj->getAutoFill() == TRUE
														AND $status_id == 10 ) { //Make sure they are working for Auto-fill to kickin.
													Debug::text('Schedule has AutoFill enabled!', __FILE__, __LINE__, __METHOD__, 10);

													$commit_punch_transaction = FALSE;

													Debug::text('Punch In: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ), __FILE__, __LINE__, __METHOD__, 10);
													$pf_in = new PunchFactory();

													$pf_in->StartTransaction();

													$pf_in->setUser( $user_id );
													$pf_in->setType( 10 ); //Normal
													$pf_in->setStatus( 10 ); //In
													$pf_in->setTimeStamp( $recurring_schedule_shift_start_time, TRUE  );
													$pf_in->setPunchControlID( $pf_in->findPunchControlID() );
													$pf_in->setActualTimeStamp( $pf_in->getTimeStamp() );
													$pf_in->setOriginalTimeStamp( $pf_in->getTimeStamp() );

													if ( $pf_in->isValid() ) {
														Debug::text('Punch In: Valid!', __FILE__, __LINE__, __METHOD__, 10);
														$pf_in->setEnableCalcTotalTime( FALSE );
														$pf_in->setEnableCalcSystemTotalTime( FALSE );
														$pf_in->setEnableCalcUserDateTotal( FALSE );
														$pf_in->setEnableCalcException( FALSE );

														$pf_in->Save( FALSE );
													} else {
														Debug::text('Punch In: InValid!', __FILE__, __LINE__, __METHOD__, 10);
													}

													Debug::text('Punch Out: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_end_time ), __FILE__, __LINE__, __METHOD__, 10);
													$pf_out = new PunchFactory();
													$pf_out->setUser( $user_id );
													$pf_out->setType( 10 ); //Normal
													$pf_out->setStatus( 20 ); //Out
													$pf_out->setTimeStamp( $recurring_schedule_shift_end_time, TRUE  );
													$pf_out->setPunchControlID( $pf_in->findPunchControlID() ); //Use the In punch object to find the punch_control_id.
													$pf_out->setActualTimeStamp( $pf_out->getTimeStamp() );
													$pf_out->setOriginalTimeStamp( $pf_out->getTimeStamp() );

													if ( $pf_out->isValid() ) {
														Debug::text('Punch Out: Valid!', __FILE__, __LINE__, __METHOD__, 10);
														$pf_out->setEnableCalcTotalTime( TRUE );
														$pf_out->setEnableCalcSystemTotalTime( TRUE );
														$pf_out->setEnableCalcUserDateTotal( TRUE );
														$pf_out->setEnableCalcException( TRUE );

														$pf_out->Save( FALSE );
													} else {
														Debug::text('Punch Out: InValid!', __FILE__, __LINE__, __METHOD__, 10);
													}

													if ( $pf_in->isValid() == TRUE OR $pf_out->isValid() == TRUE ) {
														Debug::text('Punch In and Out succeeded, saving punch control!', __FILE__, __LINE__, __METHOD__, 10);

														$pcf = new PunchControlFactory();
														$pcf->setId( $pf_in->getPunchControlID() );

														if ( $pf_in->isValid() == TRUE ) {
															$pcf->setPunchObject( $pf_in );
														} elseif ( $pf_out->isValid() == TRUE ) {
															$pcf->setPunchObject( $pf_out );
														}

														if ( $recurring_schedule_shift['branch_id'] == -1 ) {
															$pcf->setBranch( $user_obj->getDefaultBranch() );
														} else {
															$pcf->setBranch( $recurring_schedule_shift['branch_id'] );
														}

														if ( $recurring_schedule_shift['department_id'] == -1 ) {
															$pcf->setDepartment( $user_obj->getDefaultDepartment() );
														} else {
															$pcf->setDepartment( $recurring_schedule_shift['department_id'] );
														}

														if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
															if ( $recurring_schedule_shift['job_id'] == -1 ) {
																$pcf->setJob( $user_obj->getDefaultJob() );
															} else {
																$pcf->setJob( $recurring_schedule_shift['job_id'] );
															}

															if ( $recurring_schedule_shift['job_item_id'] == -1 ) {
																$pcf->setJobItem( $user_obj->getDefaultJobItem() );
															} else {
																$pcf->setJobItem( $recurring_schedule_shift['job_item_id'] );
															}
														}

														$pcf->setEnableStrictJobValidation( TRUE );
														$pcf->setEnableCalcUserDateID( TRUE );
														$pcf->setEnableCalcTotalTime( TRUE );
														$pcf->setEnableCalcSystemTotalTime( TRUE );
														$pcf->setEnableCalcUserDateTotal( TRUE );
														$pcf->setEnableCalcException( TRUE );
														$pcf->setEnablePreMatureException( FALSE ); //Disable pre-mature exceptions at this point.

														if ( $pcf->isValid() ) {
															$pcf->Save( TRUE, TRUE );

															$commit_punch_transaction = TRUE;
														} 
													} else {
														Debug::text('Punch In and Out failed, not saving punch control!', __FILE__, __LINE__, __METHOD__, 10);
													}

													if ( $commit_punch_transaction == TRUE ) {
														Debug::text('Committing Punch Transaction!', __FILE__, __LINE__, __METHOD__, 10);
														$pf_in->CommitTransaction();
													} else {
														Debug::text('Rolling Back Punch Transaction!', __FILE__, __LINE__, __METHOD__, 10);
														$pf_in->FailTransaction();
														$pf_in->CommitTransaction();
													}

													unset($pf_in, $pf_out, $pcf);
												}

												$profiler->stopTimer( "Add Punch");
											} else {
												$sf->FailTransaction();
												$sf->CommitTransaction();
												Debug::text('Bad or conflicting Schedule: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ), __FILE__, __LINE__, __METHOD__, 10);
											}

											$profiler->stopTimer( "Add Schedule");

										} else {
											Debug::text('Recurring Schedule Shift Start Time DOES NOT fall within Limits: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ), __FILE__, __LINE__, __METHOD__, 10);
										}
									}
								}
							} else {
								Debug::text('No Recurring Schedule Days To Add! User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
							}

							//Set timezone back to default before we loop to the next user.
							//Without this the next start/end date will be in the last users timezone
							//and cause schedules to be included.
							TTDate::setTimeZone();

							unset($ulf, $user_obj, $user_obj_prefs, $sf, $pf, $pcf);

							$i++;
						}
					}
					//$rsclf->FailTransaction();
					//$rsclf->CommitTransaction();
				}
			}
		} else {
			Debug::text('Company is not ACTIVE: '. $c_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
		}
	}
}
//$profiler->printTimers(TRUE);
Debug::writeToLog();
Debug::Display();
?>