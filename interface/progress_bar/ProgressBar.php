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
 * $Id: ProgressBar.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once('HTML/Progress.php');

//Debug::setVerbosity(11);

/*
 *
 * Make SURE you turn off mod_deflate for JUST this script in order for the progress bar to work.
 *       SetEnvIfNoCase Request_URI \.(?:gif|jpe?g|png)$ no-gzip dont-vary
 *       SetEnvIfNoCase Request_URI \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
 *       SetEnvIfNoCase Request_URI \.pdf$ no-gzip dont-vary
 *       SetEnvIfNoCase Request_URI ProgressBar\.php no-gzip dont-vary
 *
 * 		Can be done in any conf file, but in Debian the deflate.conf file.
 */

//Don't stop execution if user hits their stop button on their browser!
ignore_user_abort(TRUE);


/*
 *
 * START Progress Bar Header...
 *
 */

function InitProgressBar( $increment = 1 ) {
	global $progress_bar;

	$progress_bar = new HTML_Progress();
	$progress_bar->setAnimSpeed(100);

	//$progress_bar->setIncrement( (int)$increment );
	//$progress_bar->setIndeterminate(true);
	$progress_bar->setBorderPainted(true);


	$ui =& $progress_bar->getUI();
	$ui->setCellAttributes('active-color=#3874B4 inactive-color=#CCCCCC width=10');
	$ui->setBorderAttributes('width=1 color=navy');
	$ui->setStringAttributes('width=60 font-size=14 background-color=#FFFFFF align=center');

	?>
	<html>
	<head>
	<style type="text/css">
	<!--
	<?php echo $progress_bar->getStyle(); ?>

	body {
			background-color: #FFFFFF;
			color: #FFFFFF;
			font-family: Verdana, freesans;
	}

	a:visited, a:active, a:link {
			color: yellow;
	}
	// -->
	</style>
	<script type="text/javascript">
	<!--
	<?php echo $progress_bar->getScript(); ?>
	//-->
	</script>
	</head>
	<body>

	<div align="center">
	<?php
	echo $progress_bar->toHtml();
}

/*
 *
 * END Progress Bar Header...
 *
 */




/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'next_page',
												'pay_period_ids',
												'filter_user_id',
												'pay_stub_ids',
												'data'
												) ) );


Debug::text('Next Page: '. $next_page, __FILE__, __LINE__, __METHOD__,10);

$ppf = TTnew( 'PayPeriodFactory' );

$action = strtolower($action);
switch ($action) {
	case 'recalculate_company':
	case 'recalculate_employee':
		Debug::text('Recalculating Employee Timesheet: User ID: '. $filter_user_id .' Pay Period ID: '. $pay_period_ids, __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		//Make sure pay period is not CLOSED.
		//We can re-calc on locked though.
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getById( $pay_period_ids );
		if ( $pplf->getRecordCount() > 0 ) {
			$pp_obj = $pplf->getCurrent();

			if ( $pp_obj->getStatus() != 20 ) {
				$udlf = TTnew( 'UserDateListFactory' );
				if ( $action == 'recalculate_company' ) {
					TTLog::addEntry( $current_company->getId(), 500, TTi18n::gettext('Recalculating Company TimeSheet'), $current_user->getId(), 'user_date_total' );
					$udlf->getByCompanyIdAndPayPeriodID( $current_company->getId(), $pay_period_ids );
				} else {
					TTLog::addEntry( $filter_user_id, 500, TTi18n::gettext('Recalculating Employee TimeSheet'), $current_user->getId(), 'user_date_total' );
					$udlf->getByUserIdAndPayPeriodID( $filter_user_id, $pay_period_ids );
				}

				if ( $udlf->getRecordCount() > 0 ) {
					InitProgressBar();
					$progress_bar->setValue(0);
					$progress_bar->display();


					Debug::text('Found days to re-calculate: '.$udlf->getRecordCount() , __FILE__, __LINE__, __METHOD__, 10);



					$x=1;
					foreach($udlf as $ud_obj ) {
						//Debug::text($x .' / '. $udlf->getRecordCount() .' - User Date Id: '. $ud_obj->getId() .' Date: '.$ud_obj->getDateStamp(TRUE) .' User ID: '. $ud_obj->getUser() , __FILE__, __LINE__, __METHOD__, 10);

						$udlf->StartTransaction(); //If a transaction wraps the entire recalculation process, a deadlock is likely to occur for large batches.
						UserDateTotalFactory::reCalculateDay( $ud_obj->getId(), TRUE );
						$udlf->CommitTransaction();

						$progress_bar->setValue( Misc::calculatePercent( $x, $udlf->getRecordCount() ) );
						$progress_bar->display();

						$x++;
					}
				} else {
					Debug::text('No User Date rows to calculate!', __FILE__, __LINE__, __METHOD__, 10);
				}

			} else {
				Debug::text('Pay Period is CLOSED: ', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		break;
	case 'generate_paystubs':
		//Debug::setVerbosity(11);

		Debug::Text('Generate Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		if ( !is_array($pay_period_ids) ) {
			$pay_period_ids = array($pay_period_ids);
		}

		TTLog::addEntry( $current_company->getId(), 500, TTi18n::gettext('Recalculating Company Pay Stubs for Pay Periods:').' '. implode(',', $pay_period_ids) , $current_user->getId(), 'pay_stub' );

		$init_progress_bar = TRUE;
		foreach($pay_period_ids as $pay_period_id) {
			Debug::text('Pay Period ID: '. $pay_period_id, __FILE__, __LINE__, __METHOD__,10);

			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() );

			$epoch = TTDate::getTime();

			foreach ($pplf as $pay_period_obj) {
				Debug::text('Pay Period Schedule ID: '. $pay_period_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__,10);
				if ( $pay_period_obj->isPreviousPayPeriodClosed() == TRUE ) {
					//Grab all users for pay period
					$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
					$ppsulf->getByPayPeriodScheduleId( $pay_period_obj->getPayPeriodSchedule() );

					$total_pay_stubs = $ppsulf->getRecordCount();
					//echo "Total Pay Stubs: $total_pay_stubs - ". ceil(100 / $total_pay_stubs) ."<Br>\n";

					if ( $init_progress_bar == TRUE ) {
						//InitProgressBar( ceil(100 / $total_pay_stubs) );
						InitProgressBar();
						$init_progress_bar = FALSE;
					}

					$progress_bar->setValue(0);
					$progress_bar->display();

					//Delete existing pay stub. Make sure we only
					//delete pay stubs that are the same as what we're creating.
					$pslf = TTnew( 'PayStubListFactory' );
					$pslf->getByPayPeriodId( $pay_period_obj->getId() );
					foreach ( $pslf as $pay_stub_obj ) {

						Debug::text('Existing Pay Stub: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__,10);

						//Check PS End Date to match with PP End Date
						//So if an ROE was generated, it won't get deleted when they generate all other Pay Stubs
						//later on.
						if ( $pay_stub_obj->getStatus() <= 25
								AND $pay_stub_obj->getTainted() === FALSE
								AND $pay_stub_obj->getEndDate() == $pay_period_obj->getEndDate() ) {
							Debug::text('Pay stub matched advance flag, deleting: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
							$pay_stub_obj->setDeleted(TRUE);
							$pay_stub_obj->Save();
						} else {
							Debug::text('Pay stub does not need regenerating, or it is LOCKED!', __FILE__, __LINE__, __METHOD__,10);
						}
					}

					$i=1;
					foreach ($ppsulf as $pay_period_schdule_user_obj) {
						Debug::text('Pay Period User ID: '. $pay_period_schdule_user_obj->getUser(), __FILE__, __LINE__, __METHOD__,10);
						Debug::text('Total Pay Stubs: '. $total_pay_stubs .' - '. ceil( 1 / (100 / $total_pay_stubs) ) , __FILE__, __LINE__, __METHOD__,10);

						$profiler->startTimer( 'Calculating Pay Stub' );
						//Calc paystubs.
						$cps = new CalculatePayStub();
						$cps->setUser( $pay_period_schdule_user_obj->getUser() );
						$cps->setPayPeriod( $pay_period_obj->getId() );
						$cps->calculate();
						unset($cps);
						$profiler->stopTimer( 'Calculating Pay Stub' );

						$progress_bar->setValue( Misc::calculatePercent( $i, $total_pay_stubs ) );
						$progress_bar->display();

						$i++;
					}
					unset($ppsulf);
				} else {
					UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext('ERROR'), 10, TTi18n::gettext('Pay period prior to %1 is not closed, please close all previous pay periods and try again...', array( TTDate::getDate('DATE', $pay_period_obj->getStartDate() ).' -> '. TTDate::getDate('DATE', $pay_period_obj->getEndDate() ) ) ), NULL );
				}
			}
		}

		$ugsf = TTnew( 'UserGenericStatusFactory' );
		$ugsf->setUser( $current_user->getId() );
		$ugsf->setBatchID( $ugsf->getNextBatchId() );
		$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
		$ugsf->saveQueue();
		$next_page = URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Generating Pay Stubs', 'batch_next_page' => $next_page), '../users/UserGenericStatusList.php');

		unset($ugsf);

		break;
	case 'recalculate_paystub_ytd':
		//Debug::setVerbosity(11);

		Debug::Text('Recalculating Pay Stub YTDs!', __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Pay Stub ID: '. $pay_stub_ids, __FILE__, __LINE__, __METHOD__,10);

		$init_progress_bar = TRUE;

		//Just need the pay_stub_id of the modified pay stub.
		$pslf = TTnew( 'PayStubListFactory' );

		$pslf->StartTransaction();
		if ( is_array($pay_stub_ids) ) {
			foreach( $pay_stub_ids as $pay_stub_id) {
				$pslf->getByIdAndCompanyIdAndIgnoreDeleted( $pay_stub_id, $current_company->getId() );

				if ( $pslf->getRecordCount() > 0 ) {

					$main_ps_obj = $pslf->getCurrent();

					//Get all pay stubs NEWER then this one.
					$pslf->getByUserIdAndStartDateAndEndDate( $main_ps_obj->getUser() , $main_ps_obj->getTransactionDate(), TTDate::getEndYearEpoch( $main_ps_obj->getTransactionDate() ) );
					$total_pay_stubs = $pslf->getRecordCount();
					if ( $total_pay_stubs > 0 ) {

						if ( $init_progress_bar == TRUE ) {
							InitProgressBar();
							$init_progress_bar = FALSE;
						}

						$progress_bar->setValue(0);
						$progress_bar->display();

						$x=1;
						foreach($pslf as $ps_obj ) {
							Debug::Text('ReCalculating Pay Stub ID: '. $ps_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
							$ps_obj->reCalculatePayStubYTD( $ps_obj->getId() );

							$progress_bar->setValue( Misc::calculatePercent( $x, $total_pay_stubs ) );
							$progress_bar->display();

							$x++;
						}

					} else {
						Debug::Text('No Newer Pay Stubs found!', __FILE__, __LINE__, __METHOD__,10);
					}
					unset($main_ps_obj);

				} else {
					Debug::Text('Pay Stub ID invalid!', __FILE__, __LINE__, __METHOD__,10);
				}
			}

		}

		//$pslf->FailTransaction();
		$pslf->CommitTransaction();

		break;
	case 'add_mass_punch':
		if ( isset($filter_user_id) AND is_array($filter_user_id) AND count($filter_user_id) > 0 ) {
			//Debug::setVerbosity(11);

			$init_progress_bar = TRUE;

			if ( $init_progress_bar == TRUE ) {
				InitProgressBar();
				$init_progress_bar = FALSE;
			}

			$progress_bar->setValue(0);
			$progress_bar->display();

			//This will be slightly off depending on which days of the week they choose.
			$total_punches = count($filter_user_id) * TTDate::getDays($data['end_punch_full_time_stamp'] - $data['start_punch_full_time_stamp']);
			Debug::Text('Total Punches: '. $total_punches .' Users: '.  count($filter_user_id) .' Days: '.  TTDate::getDays($data['end_punch_full_time_stamp'] - $data['start_punch_full_time_stamp']), __FILE__, __LINE__, __METHOD__,10);

			$pcf = TTnew( 'PunchControlFactory' );
			$pf = TTnew( 'PunchFactory' );
			$ulf = TTnew( 'UserListFactory' );

			$pf->StartTransaction();

			TTLog::addEntry( $current_user->getId(), 500, TTi18n::getText('Mass Punch: Time').': '. TTDate::getDate('TIME', $data['start_punch_full_time_stamp']) .' Total Employees: '.  count($filter_user_id) .' Total Days: '. round( TTDate::getDays($data['end_punch_full_time_stamp'] - $data['start_punch_full_time_stamp']) ), $current_user->getId(), $pf->getTable() );

			$time_stamp = $data['start_punch_full_time_stamp'];

			$x=0;
			while ( $time_stamp <= $data['end_punch_full_time_stamp'] ) {
				if ( isset($data['dow'][TTDate::getDayOfWeek( $time_stamp )]) AND $data['dow'][TTDate::getDayOfWeek( $time_stamp )] == 1 ) {
					foreach( $filter_user_id as $user_id ) {

						$ulf->getByIdAndCompanyId($user_id,  $current_company->getId() );
						if ( $ulf->getRecordCount() == 1 ) {
							$user_obj = $ulf->getCurrent();
							$user_generic_status_label = $user_obj->getFullName(TRUE) .' @ '. TTDate::getDate('DATE+TIME', $time_stamp);
						} else {
							$user_obj = NULL;
							$user_generic_status_label = 'N/A @ '. TTDate::getDate('DATE+TIME', $time_stamp);
						}

						$pcf = TTnew( 'PunchControlFactory' );
						$pf = TTnew( 'PunchFactory' );

						Debug::Text('Punch Full Time Stamp: '. TTDate::getDate('DATE+TIME', $time_stamp), __FILE__, __LINE__, __METHOD__,10);

						//Set User before setTimeStamp so rounding can be done properly.
						$pf->setUser( $user_id );

						$pf->setType( $data['type_id'] );
						$pf->setStatus( $data['status_id'] );
						if ( isset($data['disable_rounding']) ) {
							$enable_rounding = FALSE;
						} else {
							$enable_rounding = TRUE;
						}
						$pf->setTimeStamp( $time_stamp, $enable_rounding );

						$pf->setPunchControlID( $pf->findPunchControlID() );

						$pf->setStation( $current_station->getID() );

						if ( $pf->isNew() ) {
							$pf->setActualTimeStamp( $time_stamp );
							$pf->setOriginalTimeStamp( $pf->getTimeStamp() );
						}

						if ( $pf->isValid() == TRUE ) {
							if ( $pf->Save( FALSE ) == TRUE ) {
								$pcf = TTnew( 'PunchControlFactory' );
								$pcf->setId( $pf->getPunchControlID() );
								$pcf->setPunchObject( $pf );

								if ( isset($data['branch_id']) ) {
									$pcf->setBranch( $data['branch_id'] );
								}
								if ( isset($data['department_id']) ) {
									$pcf->setDepartment( $data['department_id'] );
								}

								if ( isset($data['job_id']) ) {
									$pcf->setJob( $data['job_id'] );
								}
								if ( isset($data['job_item_id']) ) {
									$pcf->setJobItem( $data['job_item_id'] );
								}
								if ( isset($data['quantity']) ) {
									$pcf->setQuantity( $data['quantity'] );
								}
								if ( isset($data['bad_quantity']) ) {
									$pcf->setBadQuantity( $data['bad_quantity'] );
								}
								if ( isset($data['note']) ) {
									$pcf->setNote( $data['note'] );
								}

								if ( isset($data['other_id1']) ) {
									$pcf->setOtherID1( $data['other_id1'] );
								}
								if ( isset($data['other_id2']) ) {
									$pcf->setOtherID2( $data['other_id2'] );
								}
								if ( isset($data['other_id3']) ) {
									$pcf->setOtherID3( $data['other_id3'] );
								}
								if ( isset($data['other_id4']) ) {
									$pcf->setOtherID4( $data['other_id4'] );
								}
								if ( isset($data['other_id5']) ) {
									$pcf->setOtherID5( $data['other_id5'] );
								}

								$pcf->setEnableStrictJobValidation( TRUE );
								$pcf->setEnableCalcUserDateID( TRUE );
								$pcf->setEnableCalcTotalTime( TRUE );
								$pcf->setEnableCalcSystemTotalTime( TRUE );
								$pcf->setEnableCalcUserDateTotal( TRUE );
								$pcf->setEnableCalcException( TRUE );

								if ( $pcf->isValid() == TRUE ) {
									Debug::Text(' Punch Control is valid, saving...: ', __FILE__, __LINE__, __METHOD__,10);

									if ( $pcf->Save( TRUE, TRUE ) == TRUE ) { //Force isNew() lookup.
										UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 30, NULL, NULL );
									} else {
										Debug::Text(' aFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
										$fail_transaction = TRUE;
										break;
									}
								} else {
									Debug::Text(' bFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
									UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $pcf->Validator->getTextErrors(), NULL );

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
							UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $pf->Validator->getTextErrors(), NULL );

							$fail_transaction = TRUE;
							break;
						}

						Debug::Text('Setting Percent: '. Misc::calculatePercent( $x, $total_punches ), __FILE__, __LINE__, __METHOD__,10);
						$progress_bar->setValue( Misc::calculatePercent( $x, $total_punches ) );
						$progress_bar->display();

						$x++;
					}
				} else {
					Debug::Text(' Skipping Day Of Week: ('. TTDate::getDayOfWeek( $time_stamp) .') '. TTDate::getDate('DATE+TIME', $time_stamp), __FILE__, __LINE__, __METHOD__,10);

					$x++;
				}
				$time_stamp = $time_stamp + 86400;
			}

			//$pf->FailTransaction();
			$pf->CommitTransaction();

			$ugsf = TTnew( 'UserGenericStatusFactory' );
			$ugsf->setUser( $current_user->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();

			$progress_bar->setValue( 100 );
			$progress_bar->display();

			$next_page = URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Mass Punch', 'batch_next_page' => urlencode( URLBuilder::getURL( array('pc_data' => $data, 'filter_user_id' => $filter_user_id ), '../punch/AddMassPunch.php' ) ) ), '../users/UserGenericStatusList.php');
		}

		break;
	case 'add_mass_schedule':
		if ( isset($filter_user_id) AND is_array($filter_user_id) AND count($filter_user_id) > 0 ) {
			$init_progress_bar = TRUE;

			if ( $init_progress_bar == TRUE ) {
				InitProgressBar();
				$init_progress_bar = FALSE;
			}

			$progress_bar->setValue(0);
			$progress_bar->display();

			//This will be slightly off depending on which days of the week they choose.
			$total_shifts = count($filter_user_id) * TTDate::getDays($data['end_full_time_stamp'] - $data['start_full_time_stamp']);
			Debug::Text('Total Shifts: '. $total_shifts .' Users: '.  count($filter_user_id) .' Days: '.  TTDate::getDays($data['end_full_time_stamp'] - $data['start_full_time_stamp']), __FILE__, __LINE__, __METHOD__,10);

			$sf = TTnew( 'ScheduleFactory' );
			$ulf = TTnew( 'UserListFactory' );

			$sf->StartTransaction();

			TTLog::addEntry( $current_user->getId(), 500, 'Mass Schedule: Start Time: '. TTDate::getDate('TIME', $data['start_full_time_stamp']) .' End Time: '. TTDate::getDate('TIME', $data['end_full_time_stamp']) .' Total Employees: '.  count($filter_user_id) .' Total Days: '. round( TTDate::getDays($data['end_full_time_stamp'] - $data['start_full_time_stamp']) ), $current_user->getId(), $sf->getTable() );

			$time_stamp = $data['start_full_time_stamp'];

			$fail_transaction = FALSE;

			$x=0;
			while ( $time_stamp <= $data['end_full_time_stamp'] ) {
				if ( isset($data['dow'][TTDate::getDayOfWeek( $time_stamp )]) AND $data['dow'][TTDate::getDayOfWeek( $time_stamp )] == 1 ) {
					foreach( $filter_user_id as $user_id ) {
						if ( $data['start_time'] != '') {
							$start_time = strtotime( $data['start_time'], $time_stamp ) ;
						} else {
							$start_time = NULL;
						}
						if ( $data['end_time'] != '') {
							Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $time_stamp , __FILE__, __LINE__, __METHOD__,10);
							$end_time = strtotime( $data['end_time'], $time_stamp ) ;
							//Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data['end_time']) , __FILE__, __LINE__, __METHOD__,10);
						} else {
							$end_time = NULL;
						}

						//$user_date_id = UserDateFactory::findOrInsertUserDate($user_id, $time_stamp);
						Debug::Text('User ID: '. $user_id .' Date Stamp: '. TTDate::getDate('DATE', $time_stamp), __FILE__, __LINE__, __METHOD__,10);

						$conflicting_shifts = FALSE;
						if ( isset($data['overwrite']) AND $data['overwrite'] == 1 ) {
							Debug::Text('Overwriting Existing Shifts Enabled...', __FILE__, __LINE__, __METHOD__,10);
							$slf = TTnew( 'ScheduleListFactory' );
							//$slf->getConflictingByUserDateIdAndStartDateAndEndDate($user_date_id, $start_time, $end_time);
							$slf->getConflictingByUserIdAndStartDateAndEndDate( $user_id, $start_time, $end_time );
							if ( $slf->getRecordCount() > 0 ) {
								$conflicting_shifts = TRUE;
								Debug::Text('Found Conflicting Shift!!', __FILE__, __LINE__, __METHOD__,10);
								//Delete shifts.
								foreach( $slf as $s_obj ) {
									Debug::Text('Deleting Schedule Shift ID: '. $s_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
									$s_obj->setDeleted(TRUE);
									if ( $s_obj->isValid() ) {
										$s_obj->Save();
									}
								}
							} else {
								Debug::Text('NO Conflicting Shift found...', __FILE__, __LINE__, __METHOD__,10);
							}
						}
						unset($slf, $s_obj);

						$ulf->getByIdAndCompanyId($user_id,  $current_company->getId() );
						if ( $ulf->getRecordCount() == 1 ) {
							$user_obj = $ulf->getCurrent();
							$user_generic_status_label = $user_obj->getFullName(TRUE) .' @ '. TTDate::getDate('DATE', $start_time) .': '. TTDate::getDate('TIME', $start_time) .' - '. TTDate::getDate('TIME', $end_time);
							if ( $conflicting_shifts == TRUE ) {
								$user_generic_status_label .= ' - '. TTi18n::gettext('DELETED CONFLICTING SHIFT');
							}
						} else {
							$user_obj = NULL;
							$user_generic_status_label = 'N/A @ '. TTDate::getDate('DATE', $start_time) .': '. TTDate::getDate('TIME', $start_time) .' - '. TTDate::getDate('TIME', $end_time);
						}

						//Re-initialize schedule factory here so we clear any errors preventing the next schedule from being inserted.
						$sf = TTnew( 'ScheduleFactory' );
						$sf->setCompany( $current_user->getCompany() );
						$sf->setUser( $user_id );
						//$sf->setUserDate($user_id, $time_stamp);
						$sf->setStatus( $data['status_id'] );
						$sf->setSchedulePolicyID( $data['schedule_policy_id'] );
						$sf->setAbsencePolicyID( $data['absence_policy_id'] );

						if ( isset($data['branch_id']) AND $data['branch_id'] == -1 ) {
							$sf->setBranch( $user_obj->getDefaultBranch() );
						} elseif ( isset($data['branch_id']) ) {
							$sf->setBranch( $data['branch_id'] );
						}
						if ( isset($data['department_id']) AND $data['department_id'] == -1 ) {
							$sf->setDepartment( $user_obj->getDefaultDepartment() );
						} elseif ( isset($data['department_id']) ) {
							$sf->setDepartment( $data['department_id'] );
						}

						if ( isset($data['job_id']) ) {
							$sf->setJob( $data['job_id'] );
						}

						if ( isset($data['job_item_id'] ) ) {
							$sf->setJobItem( $data['job_item_id'] );
						}

						$sf->setStartTime( $start_time );
						$sf->setEndTime( $end_time );

						if ( $sf->isValid() ) {
							$sf->setEnableReCalculateDay(TRUE);
							if ( $sf->Save() != TRUE ) {
								UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $sf->Validator->getTextErrors(), NULL );
								$fail_transaction = TRUE;
							} else {
								if ( $conflicting_shifts == TRUE ) {
									UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 20, NULL, NULL );
								} else {
									UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 30, NULL, NULL );
								}
							}
						} else {
							UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $sf->Validator->getTextErrors(), NULL );
							$fail_transaction = TRUE;
						}

						//Debug::Text('Setting Percent: '. Misc::calculatePercent( $x, $total_shifts ), __FILE__, __LINE__, __METHOD__,10);
						$progress_bar->setValue( Misc::calculatePercent( $x, $total_shifts ) );
						$progress_bar->display();

						$x++;
					}
				} else {
					Debug::Text(' Skipping Day Of Week: ('. TTDate::getDayOfWeek( $time_stamp) .') '. TTDate::getDate('DATE+TIME', $time_stamp), __FILE__, __LINE__, __METHOD__,10);

					$x++;
				}
				$time_stamp = $time_stamp + 86400;
			}

			//$sf->FailTransaction();
			$sf->CommitTransaction();

			$ugsf = TTnew( 'UserGenericStatusFactory' );
			$ugsf->setUser( $current_user->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();

			$progress_bar->setValue( 100 );
			$progress_bar->display();

			$next_page = URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Mass Schedule', 'batch_next_page' => urlencode( URLBuilder::getURL( array('data' => $data, 'filter_user_id' => $filter_user_id ), '../schedule/AddMassSchedule.php' ) ) ), '../users/UserGenericStatusList.php');
		}

		break;
	case 'recalculate_accrual_policy':
		//Debug::setVerbosity(11);
		if ( isset($data['accrual_policy_id']) AND isset( $data['start_date'] ) AND isset( $data['end_date'] ) ) {
			if ( $data['start_date'] <= $data['end_date']) {
				$start_date = TTDate::getMiddleDayEpoch( $data['start_date'] );
				$end_date = TTDate::getMiddleDayEpoch( $data['end_date'] );
				$total_days = TTDate::getDays( ($end_date-$start_date) );
				$offset = (86400/2);

				$init_progress_bar = TRUE;

				if ( $init_progress_bar == TRUE ) {
					InitProgressBar();
					$init_progress_bar = FALSE;
				}

				$progress_bar->setValue(0);
				$progress_bar->display();

				$apf = TTnew( 'AccrualPolicyFactory' );
				$aplf = TTnew( 'AccrualPolicyListFactory' );

				$aplf->getByIdAndCompanyId( (int)$data['accrual_policy_id'], $current_company->getId() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach( $aplf as $ap_obj ) {
						$aplf->StartTransaction();

						TTLog::addEntry( $current_user->getId(), 500, 'Recalculate Accrual Policy: '. $ap_obj->getName() .' Start Date: '. TTDate::getDate('TIME', $data['start_date']) .' End Date: '. TTDate::getDate('TIME', $data['end_date']) .' Total Days: '. round( $total_days ), $current_user->getId(), $ap_obj->getTable() );

						$x=0;
						for( $i=$start_date; $i < $end_date; $i+=(86400) ) {
							//$i = TTDate::getBeginDayEpoch( $i ); //This causes infinite loops during DST transitions.
							Debug::Text('Recalculating Accruals for Date: '. TTDate::getDate('DATE+TIME', TTDate::getBeginDayEpoch( $i ) ), __FILE__, __LINE__, __METHOD__,10);
							$ap_obj->addAccrualPolicyTime( TTDate::getBeginDayEpoch( $i ), $offset );

							$progress_bar->setValue( Misc::calculatePercent( $x, $total_days ) );
							$progress_bar->display();

							$x++;
						}

						//$aplf->FailTransaction();
						$aplf->CommitTransaction();
					}
				}

				$progress_bar->setValue( 100 );
				$progress_bar->display();
			}
		}
		break;
	default:
		//Test
		InitProgressBar( 10 );

		for($i=0; $i < 11; $i++) {
			$progress_bar->display();
			$progress_bar->incValue();

			if ( $i % 2 == 0 ) {
				sleep(1);
			}

		}
		break;
}
?>
</div>
<?php
sleep(2);

if ( Debug::getVerbosity() <= 10 ) {
	if (isset($next_page) AND $next_page != '') {
		?>
		<script type="text/javascript">parent.location.href='<?php echo $next_page;?>'</script>
		<?php
	}
}

if ( Debug::getVerbosity() > 10 ) {
	Debug::Display();
}
Debug::writeToLog();
?>
</body>
</html>