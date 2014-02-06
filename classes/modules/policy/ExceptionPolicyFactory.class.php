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
 * $Revision: 10749 $
 * $Id: ExceptionPolicyFactory.class.php 10749 2013-08-26 22:00:42Z ipso $
 * $Date: 2013-08-26 15:00:42 -0700 (Mon, 26 Aug 2013) $
 */

/**
 * @package Modules\Policy
 */
class ExceptionPolicyFactory extends Factory {
	protected $table = 'exception_policy';
	protected $pk_sequence_name = 'exception_policy_id_seq'; //PK Sequence name

	protected $enable_grace = array('S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'L1', 'L2', 'B1','B2', 'V1', 'C1' );
	protected $enable_watch_window = array('S3', 'S4', 'S5', 'S6', 'O1','O2');

	//M1 - Missing In Punch is not considered pre-mature, as the employee can't go back and punch in after the fact anyways.
	protected static $premature_exceptions = array('M2', 'M3', 'M4', 'L3', 'B4', 'B5', 'S8');

	protected static $premature_delay = 57600;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										//Schedule Exceptions
										'S1' /* A */ => TTi18n::gettext('Unscheduled Absence'),
										'S2' /* B */ => TTi18n::gettext('Not Scheduled'),
										'S3' /* C */ => TTi18n::gettext('In Early'),
										'S4' /* D */ => TTi18n::gettext('In Late'),
										'S5' /* E */ => TTi18n::gettext('Out Early'),
										'S6' /* F */ => TTi18n::gettext('Out Late'),

										'S7' /* G */ => TTi18n::gettext('Over Daily Scheduled Time'),
										'S8' /* H */ => TTi18n::gettext('Under Daily Scheduled Time'),
										'S9' => TTi18n::gettext('Over Weekly Scheduled Time'),
										//'S10' => TTi18n::gettext('Under Weekly Scheduled Time'), //Is this needed?

										//Add setting to set some sort of "Grace" period, or early warning system? Approaching overtime?
										//Have exception where they can set the cutoff in hours, and it triggers once the employee has exceeded the weekly hours.
										'O1' => TTi18n::gettext('Over Daily Time'),
										'O2' => TTi18n::gettext('Over Weekly Time'),

										//Punch Exceptions
										'M1' /* K */ => TTi18n::gettext('Missing In Punch'),
										'M2' /* L */ => TTi18n::gettext('Missing Out Punch'),
										'M3' /* P */  => TTi18n::gettext('Missing Lunch In/Out Punch'),
										'M4' => TTi18n::gettext('Missing Break In/Out Punch'),

										'L1' /* M */ => TTi18n::gettext('Long Lunch'),
										'L2' /* N */ => TTi18n::gettext('Short Lunch'),
										'L3' /* O */ => TTi18n::gettext('No Lunch'),

										'B1' => TTi18n::gettext('Long Break'),
										'B2' => TTi18n::gettext('Short Break'),
										'B3' => TTi18n::gettext('Too Many Breaks'),
										'B4' => TTi18n::gettext('Too Few Breaks'),
										'B5' => TTi18n::gettext('No Break'),
										//Worked too long without break/lunch, allow to set the time frame.
										//Make grace period the amount of time a break has to exceed, and watch window the longest they can work without a break?
										//No Break exception essentially handles this.
										//'B6' => TTi18n::gettext('Worked Too Long without Break')

										//For security guards typically, must check in (punch/transfer punch) every X hours or this alert goes out.
										'C1' => TTi18n::gettext('Missed Check-In'),

										//Branch/Department exceptions
										'D1' => TTi18n::gettext('No Branch or Department'),

										//TimeSheet verification exceptions.
										'V1' => TTi18n::gettext('TimeSheet Not Verified'),

										//Job Exceptions
										'J1' /* T J1 */  => TTi18n::gettext('Not Allowed On Job'),
										'J2' /* U J2 */  => TTi18n::gettext('Not Allowed On Task'),
										'J3' /* V J3 */  => TTi18n::gettext('Job Completed'),
										'J4' /* W J4 */  => TTi18n::gettext('No Job or Task'),
										//'J5' => TTi18n::gettext('No Task'), //Make J4 No Job only?
										//Add location based exceptions, ie: Restricted Location.
									);
				break;
			case 'severity':
				$retval = array(
											10 => TTi18n::gettext('Low'), //Black
											20 => TTi18n::gettext('Medium'), //Blue
											25 => TTi18n::gettext('High'), //Orange
											30 => TTi18n::gettext('Critical') //Rename to Critical: Red, was "High"
								);
				break;
			case 'email_notification':
				$retval = array(
											//Flex returns an empty object if 0 => None, so we make it a string and add a space infront ' 0' => None as a work around.
											' 0' => TTi18n::gettext('None'),
											10 => TTi18n::gettext('Employee'),
											20 => TTi18n::gettext('Supervisor'),
											//20 => TTi18n::gettext('Immediate Supervisor'),
											//20 => TTi18n::gettext('All Supervisors'),
											100 => TTi18n::gettext('Both')
								);
				break;
			case 'columns':
				$retval = array(
										'-1010-active' => TTi18n::gettext('Active'),
										'-1010-severity' => TTi18n::gettext('Severity'),
										'-1010-grace' => TTi18n::gettext('Grace'),
										'-1010-watch_window' => TTi18n::gettext('Watch Window'),
										'-1010-email_notification' => TTi18n::gettext('Email Notification'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'exception_policy_control_id' => 'ExceptionPolicyControl',
										'name' => 'Name',
										'type_id' => 'Type',
										'severity_id' => 'Severity',
										'is_enabled_watch_window' => 'isEnabledWatchWindow',
										'watch_window' => 'WatchWindow',
										'is_enabled_grace' => 'isEnabledGrace',
										'grace' => 'Grace',
										'demerit' => 'Demerit',
										'email_notification_id' => 'EmailNotification',
										'active' => 'Active',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getExceptionPolicyControl() {
		if ( isset($this->data['exception_policy_control_id']) ) {
			return $this->data['exception_policy_control_id'];
		}

		return FALSE;
	}
	function setExceptionPolicyControl($id) {
		$id = trim($id);

		$epclf = TTnew( 'ExceptionPolicyControlListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'exception_policy_control',
													$epclf->getByID($id),
													TTi18n::gettext('Exception Policy Control is invalid')
													) ) {

			$this->data['exception_policy_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getExceptionTypeDefaultValues( $exclude_exceptions, $product_edition = 10 ) {
		if ( !is_array($exclude_exceptions) ) {
			$exclude_exceptions = array();
		}
		$type_options = $this->getTypeOptions( $product_edition );

		$retarr = array();

		foreach ( $type_options as $type_id => $exception_name ) {
			//Skip excluded exceptions
			if ( in_array( $type_id, $exclude_exceptions ) ) {
				continue;
			}

			switch ( $type_id ) {
				case 'S1': //UnSchedule Absence
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 100,
												'demerit' => 25,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S2': //Not Scheduled
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 100,
												'demerit' => 10,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S3': //In Early
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 10,
												'email_notification_id' => 20,
												'demerit' => 2,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 7200,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S4': //In Late
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 25,
												'email_notification_id' => 20,
												'demerit' => 10,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 7200,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S5': //Out Early
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 20,
												'email_notification_id' => 20,
												'demerit' => 10,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 7200,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S6': //Out Late
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 10,
												'email_notification_id' => 20,
												'demerit' => 2,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 7200,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S7': //Over daily scheduled time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 0,
												'demerit' => 2,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S8': //Under daily scheduled time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 2,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S9': //Over Weekly Scheduled Time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'O1': //Over Daily Time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 2,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => (3600*8),
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'O2': //Over Weekly Time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => (3600*40),
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'M1': //Missing In Punch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 30,
												'email_notification_id' => 100,
												'demerit' => 20,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'M2': //Missing Out Punch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 30,
												'email_notification_id' => 100,
												'demerit' => 20,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'M3': //Missing Lunch In/Out Punch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 30,
												'email_notification_id' => 100,
												'demerit' => 18,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'M4': //Missing Break In/Out Punch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 30,
												'email_notification_id' => 100,
												'demerit' => 17,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'C1': //Missed Check-in
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 25,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'D1': //No Job Or Task
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 0,
												'demerit' => 5,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'L1': //Long Lunch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 5,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'L2': //Short Lunch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 5,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'L3': //No Lunch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B1': //Long Break
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 5,
												'grace' => 300,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B2': //Short Break
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 5,
												'grace' => 300,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B3': //Too Many Breaks
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B4': //Too Few Breaks
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B5': //No Break
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'V1': //TimeSheet Not Verified
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 25,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => (48*3600), //48hrs grace period
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'J1': //Not allowed on job
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 20,
												'email_notification_id' => 20,
												'demerit' => 2,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'J2': //Not allowed on task
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 20,
												'email_notification_id' => 20,
												'demerit' => 2,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'J3': //Job completed
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 20,
												'email_notification_id' => 20,
												'demerit' => 2,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'J4': //No Job Or Task
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 0,
												'demerit' => 2,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				default:
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 0,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
			}
		}

		return $retarr;
	}

	function getName() {
		return Option::getByKey( $this->getType(), $this->getTypeOptions( getTTProductEdition() ) );
	}

	function getTypeOptions( $product_edition = 10 ) {
		$options = $this->getOptions('type');

		if ( getTTProductEdition() < TT_PRODUCT_CORPORATE OR $product_edition < 20 ) {
			$corporate_exceptions = array('J1','J2','J3','J4');
			foreach( $corporate_exceptions as $corporate_exception ) {
				unset($options[$corporate_exception]);
			}
		}

		return $options;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'type',
											$value,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getSeverity() {
		if ( isset($this->data['severity_id']) ) {
			return $this->data['severity_id'];
		}

		return FALSE;
	}
	function setSeverity($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('severity') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'severity',
											$value,
											TTi18n::gettext('Incorrect Severity'),
											$this->getOptions('severity')) ) {

			$this->data['severity_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getWatchWindow() {
		if ( isset($this->data['watch_window']) ) {
			return $this->data['watch_window'];
		}

		return FALSE;
	}
	function setWatchWindow($value) {
		$value = trim($value);

		if 	(	$value == 0
				OR $this->Validator->isNumeric(		'watch_window',
													$value,
													TTi18n::gettext('Incorrect Watch Window')) ) {

			$this->data['watch_window'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getGrace() {
		if ( isset($this->data['grace']) ) {
			return $this->data['grace'];
		}

		return FALSE;
	}
	function setGrace($value) {
		$value = trim($value);

		if 	(	$value == 0
				OR $this->Validator->isNumeric(		'grace',
													$value,
													TTi18n::gettext('Incorrect grace value')) ) {

			$this->data['grace'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDemerit() {
		if ( isset($this->data['demerit']) ) {
			return $this->data['demerit'];
		}

		return FALSE;
	}
	function setDemerit($value) {
		$value = trim($value);

		if 	(	$value == 0
				OR $this->Validator->isNumeric(		'demerit',
													$value,
													TTi18n::gettext('Incorrect demerit value')) ) {

			$this->data['demerit'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getEmailNotification() {
		if ( isset($this->data['email_notification_id']) ) {
			return $this->data['email_notification_id'];
		}

		return FALSE;
	}
	function setEmailNotification($value) {
		$value = (int)trim($value);

		if ( $this->Validator->inArrayKey(	'email_notification',
											$value,
											TTi18n::gettext('Incorrect Email Notification'),
											$this->getOptions('email_notification')) ) {

			$this->data['email_notification_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getActive() {
		return $this->fromBool( $this->data['active'] );
	}
	function setActive($bool) {
		$this->data['active'] = $this->toBool($bool);

		return TRUE;
	}

	function isEnabledGrace( $code = NULL ) {
		if ( $code == NULL ) {
			$code = $this->getType();
		}

		if ( in_array( $code, $this->enable_grace ) ) {
			return TRUE;
		}

		return FALSE;
	}

	function isEnabledWatchWindow( $code = NULL ) {
		if ( $code == NULL ) {
			$code = $this->getType();
		}

		if ( in_array( $code, $this->enable_watch_window ) ) {
			return TRUE;
		}

		return FALSE;
	}

	static function isPreMature( $code ) {
		if ( in_array( $code, self::$premature_exceptions ) ) {
			return TRUE;
		}

		return FALSE;
	}
	// static function,avoid PHP strict error
	static function calcExceptions( $user_date_id, $enable_premature_exceptions = FALSE, $enable_future_exceptions = TRUE ) {
		global $profiler;
		$profiler->startTimer( "ExceptionPolicy::calcExceptions()");

		if ( $user_date_id == '' ) {
			return FALSE;
		}
		Debug::text(' User Date ID: '. $user_date_id .' PreMature: '. (int)$enable_premature_exceptions , __FILE__, __LINE__, __METHOD__,10);

		$current_epoch = TTDate::getTime();

		//Get user date info
		$udlf = TTnew( 'UserDateListFactory' );
		$udlf->getById( $user_date_id );
		if ( $udlf->getRecordCount() > 0 ) {
			$user_date_obj = $udlf->getCurrent();

			if ( $enable_future_exceptions == FALSE
					AND $user_date_obj->getDateStamp() > TTDate::getEndDayEpoch( $current_epoch ) ) {
				return FALSE;
			}
		} else {
			return FALSE;
		}

		//16hrs... If punches are older then this time, its no longer premature.
		//This should actually be the PayPeriod Schedule maximum shift time.
		if ( is_object($user_date_obj->getPayPeriodObject())
				AND is_object($user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()) ) {
			self::$premature_delay = $user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()->getMaximumShiftTime();
			Debug::text(' Setting preMature Exception delay to maximum shift time: '. self::$premature_delay , __FILE__, __LINE__, __METHOD__,10);
		} else {
			self::$premature_delay = 57600;
		}

		//Get list of existing exceptions, so we can determine if we need to delete any. We can't delete them all blindly and re-create them
		//as this will send duplicate email notifications for every single punch.
		$existing_exceptions = array();
		$elf = TTnew( 'ExceptionListFactory' );
		$elf->getByUserDateID( $user_date_id );
		if ( $elf->getRecordCount() > 0 ) {
			foreach( $elf as $e_obj ) {
				$existing_exceptions[] = array(
												'id' => $e_obj->getId(),
												'user_date_id' => $e_obj->getUserDateID(),
												'exception_policy_id' => $e_obj->getExceptionPolicyID(),
												'type_id' => $e_obj->getType(),
												'punch_id' => $e_obj->getPunchID(),
												'punch_control_id' => $e_obj->getPunchControlID(),
											);
			}
		}
		unset($elf, $e_obj);

		//Get all Punches on this date for this user.
		$plf = TTnew( 'PunchListFactory' );
		$plf->getByUserDateId( $user_date_id );
		if ( $plf->getRecordCount() > 0 ) {
			Debug::text(' Found Punches: '.  $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		}

		$slf = TTnew( 'ScheduleListFactory' );
		$slf->getByUserDateIdAndStatusId( $user_date_id, 10 );
		if ( $slf->getRecordCount() > 0 ) {
			Debug::text(' Found Schedule: '.  $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		}

		$schedule_id_cache = NULL; //Cache schedule IDs so we don't need to do a lookup for every exception.

		$current_exceptions = array(); //Array holding current exception data.

		//Get all active exceptions.
		$eplf = TTnew( 'ExceptionPolicyListFactory' );
		$eplf->getByPolicyGroupUserIdAndActive( $user_date_obj->getUser(), TRUE );
		if ( $eplf->getRecordCount() > 0 ) {
			Debug::text(' Found Active Exceptions: '.  $eplf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

			foreach ( $eplf as $ep_obj )  {
				//Debug::text(' Found Exception Type: '. $ep_obj->getType() .' ID: '. $ep_obj->getID() .' Control ID: '. $ep_obj->getExceptionPolicyControl(), __FILE__, __LINE__, __METHOD__,10);

				if ( $enable_premature_exceptions == TRUE AND self::isPreMature( $ep_obj->getType() ) == TRUE ) {
					//Debug::text(' Premature Exception: '. $ep_obj->getType() , __FILE__, __LINE__, __METHOD__,10);
					$type_id = 5; //Pre-Mature
				} else {
					//Debug::text(' NOT Premature Exception: '. $ep_obj->getType() , __FILE__, __LINE__, __METHOD__,10);
					$type_id = 50; //Active
				}

				switch ( strtolower( $ep_obj->getType() ) ) {
					case 's1': 	//Unscheduled Absence... Anytime they are scheduled and have not punched in.
								//Ignore these exceptions if the schedule is after today (not including today),
								//so if a supervisors schedules an employee two days in advance they don't get a unscheduled
								//absence appearing right away.
								//Since we now trigger In Late/Out Late exceptions immediately after schedule time, only trigger this exception after
								//the schedule end time has passed.
								//**We also need to handle shifts that start at 11:00PM on one day, end at 8:00AM the next day, and they are assigned to the day where
								//the most time is worked (ie: the next day).
								//Handle split shifts too...
								//- This has a side affect that if the schedule policy start/stop time is set to 0, it will trigger both a UnScheduled Absence
								//  and a Not Scheduled exception for the same schedule/punch.

						//Loop through all schedules, then find punches to match.
						if ( $slf->getRecordCount() > 0 ) {
							foreach( $slf as $s_obj ) {
								if ( $s_obj->getStatus() == 10 AND ( $current_epoch >= $s_obj->getEndTime() ) ) {
									$add_exception = TRUE;
									//Debug::text(' Found Schedule: Start Time: '. TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ), __FILE__, __LINE__, __METHOD__,10);
									//Find punches that fall within this schedule time including start/stop window.
									if ( TTDate::doesRangeSpanMidnight( $s_obj->getStartTime(), $s_obj->getEndTime() )
											AND is_object($user_date_obj)
											AND is_object($user_date_obj->getPayPeriodObject())
											AND is_object($user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()) ) {
										//Get punches from both days.
										$plf_tmp = TTnew( 'PunchListFactory' );
										$plf_tmp->getShiftPunchesByUserIDAndEpoch( $user_date_obj->getUser(), $s_obj->getStartTime(), 0, $user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()->getMaximumShiftTime() );
										Debug::text(' Schedule spans midnight... Found rows from expanded search: '. $plf_tmp->getRecordCount() , __FILE__, __LINE__, __METHOD__,10);
										if ( $plf_tmp->getRecordCount() > 0 ) {
											foreach( $plf_tmp as $p_obj_tmp ) {
												if ( $s_obj->inSchedule( $p_obj_tmp->getTimeStamp() ) ) {
													Debug::text(' aFound punch for schedule...', __FILE__, __LINE__, __METHOD__,10);
													$add_exception = FALSE;
													break;
												}
											}
										}
										unset( $plf_tmp, $p_obj_tmp );
									} else {
										//Get punches from just this day.
										foreach( $plf as $p_obj ) {
											if ( $s_obj->inSchedule( $p_obj->getTimeStamp() ) ) {
												//Debug::text(' bFound punch for schedule...', __FILE__, __LINE__, __METHOD__,10);
												$add_exception = FALSE;
												break;
											}
										}
									}

									if ( $add_exception == TRUE ) {
										//Debug::text(' Adding S1 exception...', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																		'schedule_obj' => $s_obj,
																	);
									}
								}
							}
						}
						unset($s_obj, $add_exception);
						break;
					case 's2': //Not Scheduled
						//**We also need to handle shifts that start at 11:00PM on one day, end at 8:00AM the next day, and they are assigned to the day where
						//the most time is worked (ie: the next day).
						//Handle split shifts too...
						if ( $plf->getRecordCount() > 1 ) { //Make sure at least two punche exist.
							//Loop through each punch, find out if they are scheduled, and if they are in early
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf as $p_obj ) {
								//Ignore punches that have the exact same timestamp, as they are likely transfer punches.
								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
									}

									//Check if no schedule exists, or an absent schedule exists. If they work when not scheduled (no schedule) or schedule absent, both should trigger this.
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == FALSE
											OR ( is_object( $p_obj->getScheduleObject() ) AND $p_obj->getScheduleObject()->getStatus() == 20 ) ) {
										//Debug::text(' Worked when wasnt scheduled', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $p_obj->getID(),
																		'punch_control_id' => FALSE,
																	);

									} else {
										Debug::text('    Schedule Found', __FILE__, __LINE__, __METHOD__,10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}
						}
						unset($scheduled_id_cache, $prev_punch_time_stamp, $p_obj);
						break;
					case 's3': //In Early
						if ( $plf->getRecordCount() > 0 ) {
							//Loop through each punch, find out if they are scheduled, and if they are in early
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf as $p_obj ) {
								//Ignore punches that have the exact same timestamp, as they are likely transfer punches.
								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() < $p_obj->getScheduleObject()->getStartTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('    Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
											} elseif ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('    NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																				'punch_obj' => $p_obj,
																				'schedule_obj' => $p_obj->getScheduleObject(),
																			);
											}
										}
									} else {
										Debug::text('    NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}
						}
						break;
					case 's4': //In Late
						if ( $plf->getRecordCount() > 0 ) {
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf as $p_obj ) {
								Debug::text('    In Late. Punch: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__,10);
								//Ignore punches that have the exact same timestamp and/or punches with the transfer flag, as they are likely transfer punches.
								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getTransfer() == FALSE AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() > $p_obj->getScheduleObject()->getStartTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('    Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
											} elseif (  TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('    NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																				'punch_obj' => $p_obj,
																				'schedule_obj' => $p_obj->getScheduleObject(),
																			);
											}
										}
									} else {
										Debug::text('    NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}
						}
						unset($scheduled_id_cache);

						//Late Starting their shift, with no punch yet, trigger exception if:
						//  - Schedule is found
						//	- Current time is after schedule start time and before schedule end time.
						// 	- Current time is after exception grace time
						//Make sure we take into account split shifts.
						Debug::text('    Checking Late Starting Shift exception... Current time: '. TTDate::getDate('DATE+TIME', $current_epoch ), __FILE__, __LINE__, __METHOD__,10);
						if ( $slf->getRecordCount() > 0 ) {
							foreach ( $slf as $s_obj ) {
								if ( $s_obj->getStatus() == 10 AND ( $current_epoch >= $s_obj->getStartTime() AND $current_epoch <= $s_obj->getEndTime() ) ) {
									if ( TTDate::inWindow( $current_epoch, $s_obj->getStartTime(), $ep_obj->getGrace() ) == TRUE ) {
										Debug::text('    Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
									} else {
										//See if we can find a punch within the schedule time, if so assume we already created the exception above.
										//Make sure we take into account the schedule policy start/stop window.
										//However in the case where a single schedule shift and just one punch exists, if an employee comes in really
										//early (1AM) before the schedule start/stop window it will trigger an In Late exception.
										//This could still be correct though if they only come in for an hour, then come in late for their shift later.
										//Schedule start/stop time needs to be correct.
										//Also need to take into account shifts that span midnight, ie: 10:30PM to 6:00AM, as its important the schedules/punches match up properly.

										$add_exception = TRUE;
										Debug::text(' Found Schedule: Start Time: '. TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ), __FILE__, __LINE__, __METHOD__,10);
										//Find punches that fall within this schedule time including start/stop window.
										if ( TTDate::doesRangeSpanMidnight( $s_obj->getStartTime(), $s_obj->getEndTime() )
												AND is_object($user_date_obj)
												AND is_object($user_date_obj->getPayPeriodObject())
												AND is_object($user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()) ) {
											//Get punches from both days.
											$plf_tmp = TTnew( 'PunchListFactory' );
											$plf_tmp->getShiftPunchesByUserIDAndEpoch( $user_date_obj->getUser(), $s_obj->getStartTime(), 0, $user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()->getMaximumShiftTime() );
											Debug::text(' Schedule spans midnight... Found rows from expanded search: '. $plf_tmp->getRecordCount() , __FILE__, __LINE__, __METHOD__,10);
											if ( $plf_tmp->getRecordCount() > 0 ) {
												foreach( $plf_tmp as $p_obj_tmp ) {
													if ( $s_obj->inSchedule( $p_obj_tmp->getTimeStamp() ) ) {
														Debug::text('    Found punch for this schedule, skipping schedule...', __FILE__, __LINE__, __METHOD__,10);
														$add_exception = FALSE;
														continue 2; //Skip to next schedule without creating exception.
													}
												}
											}
											unset( $plf_tmp, $p_obj_tmp );
										} else {
											//Get punches from just this day.
											foreach( $plf as $p_obj ) {
												if ( $s_obj->inSchedule( $p_obj->getTimeStamp() ) ) {
													Debug::text(' bFound punch for schedule...', __FILE__, __LINE__, __METHOD__,10);
													$add_exception = FALSE;
													break;
												}
											}
										}

										if ( $add_exception == TRUE ) {
											Debug::text('    NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => FALSE,
																			'schedule_obj' => $s_obj,
																		);
										}
									}
								}
							}
						} else {
							Debug::text('    NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
						}
						break;
					case 's5': //Out Early
						if ( $plf->getRecordCount() > 0 ) {
							//Loop through each punch, find out if they are scheduled, and if they are in early
							$prev_punch_time_stamp = FALSE;
							$total_punches = $plf->getRecordCount();
							$x=1;
							foreach ( $plf as $p_obj ) {
								//Ignore punches that have the exact same timestamp and/or punches with the transfer flag, as they are likely transfer punches.
								//For Out Early, we have to wait until we are at the last punch, or there is a subsequent punch
								// to see if it matches the exact same time (transfer)
								//Therefore we need a two step confirmation before this exception can be triggered. Current punch, then next punch if it exists.
								if ( $p_obj->getTransfer() == FALSE AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 20 ) { //Normal Out
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() < $p_obj->getScheduleObject()->getEndTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('    Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
											} elseif ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('    NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);

												$tmp_exception = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																				'punch_obj' => $p_obj,
																				'schedule_obj' => $p_obj->getScheduleObject(),
																			);

												if ( $x	== $total_punches ) { //Trigger exception if we're the last punch.
													$current_exceptions[] = $tmp_exception;
												} else {
													//Save exception to be triggered if the next punch doesn't match the same time.
												}
											}
										}
									} else {
										Debug::text('    NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
									}
								} elseif ( $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									//This comes after an OUT punch, so we need to check if there are two punches
									//in a row with the same timestamp, if so ignore the exception.
									if ( isset($tmp_exception ) AND $p_obj->getTimeStamp() == $prev_punch_time_stamp ) {
										unset($tmp_exception);
									} elseif ( isset($tmp_exception) ) {
										$current_exceptions[] = $tmp_exception; //Set exception.
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();

								$x++;
							}
						}
						unset($tmp_exception, $x, $prev_punch_time_stamp);
						break;
					case 's6': //Out Late
						if ( $plf->getRecordCount() > 0  ) {
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf as $p_obj ) {
								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'time_stamp' => $p_obj->getTimeStamp() );

								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 20 ) { //Normal Out
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() > $p_obj->getScheduleObject()->getEndTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('    Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
											} elseif ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('    NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																				'punch_obj' => $p_obj,
																				'schedule_obj' => $p_obj->getScheduleObject(),
																			);
											}
										}
									} else {
										Debug::text('    NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}

							//Trigger exception if no out punch and we have passed schedule out time.
							//  - Schedule is found
							//	- Make sure the user is missing an OUT punch.
							//	- Current time is after schedule end time
							// 	- Current time is after exception grace time
							//  - Current time is before schedule end time + maximum shift time.
							if ( isset($punch_pairs) AND $slf->getRecordCount() > 0 ) {
								foreach($punch_pairs as $punch_control_id => $punch_pair) {
									if ( count($punch_pair) != 2 ) {
										Debug::text('aFound Missing Punch: ', __FILE__, __LINE__, __METHOD__,10);

										if ( $punch_pair[0]['status_id'] == 10 ) { //Missing Out Punch
											Debug::text('bFound Missing Out Punch: ', __FILE__, __LINE__, __METHOD__,10);

											foreach ( $slf as $s_obj ) {
												Debug::text('Punch: '. TTDate::getDate('DATE+TIME', $punch_pair[0]['time_stamp'] ) .' Schedule Start Time: '. TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ) .' End Time: '. TTDate::getDate('DATE+TIME', $s_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__,10);
												//Because this is just an IN punch, make sure the IN punch is before the schedule end time
												//So we can eliminate split shift schedules.
												if ( $punch_pair[0]['time_stamp'] <= $s_obj->getEndTime()
														AND $current_epoch >= $s_obj->getEndTime() AND $current_epoch <= ($s_obj->getEndTime()+self::$premature_delay) ) {
													if ( TTDate::inWindow( $current_epoch, $s_obj->getEndTime(), $ep_obj->getGrace() ) == TRUE ) {
														Debug::text('    Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
													} else {
														Debug::text('    NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
														$current_exceptions[] = array(
																						'user_date_id' => $user_date_id,
																						'exception_policy_id' => $ep_obj->getId(),
																						'type_id' => $type_id,
																						'punch_id' => FALSE,
																						'punch_control_id' => $punch_pair[0]['punch_control_id'],
																						'schedule_obj' => $s_obj,
																					);
													}
												}
											}
										}
									} else {
										Debug::text('No Missing Punches...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($punch_pairs, $punch_pair);
						}
						break;
					case 'm1': //Missing In Punch
						if ( $plf->getRecordCount() > 0 ) {
							foreach ( $plf as $p_obj ) {
								//Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Punch Control ID: '. $p_obj->getPunchControlID() .' Punch ID: '. $p_obj->getId() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);

								if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch-self::$premature_delay) ) {
									$type_id = 50;
								}

								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'punch_id' => $p_obj->getId() );
							}

							if ( isset($punch_pairs) ) {
								foreach($punch_pairs as $punch_control_id => $punch_pair) {
									//Debug::Arr($punch_pair, 'Punch Pair for Control ID:'. $punch_control_id, __FILE__, __LINE__, __METHOD__,10);

									if ( count($punch_pair) != 2 ) {
										Debug::text('a1Found Missing Punch: ', __FILE__, __LINE__, __METHOD__,10);

										if ( $punch_pair[0]['status_id'] == 20 ) { //Missing In Punch
											Debug::text('b1Found Missing In Punch: ', __FILE__, __LINE__, __METHOD__,10);
											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => $punch_pair[0]['punch_control_id'],
																		);
										}
									} else {
										Debug::text('No Missing Punches...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($punch_pairs, $punch_pair);
						}
						break;
					case 'm2': //Missing Out Punch
						if ( $plf->getRecordCount() > 0 ) {
							foreach ( $plf as $p_obj ) {
								Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Punch Control ID: '. $p_obj->getPunchControlID() .' Punch ID: '. $p_obj->getId() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);

								//This causes the exception to trigger if the first punch pair is more than the Maximum Shift time away from the current punch,
								//ie: In: 1:00AM, Out: 2:00AM, In 3:00PM (Maximum Shift Time less than 12hrs). The missing punch exception will be triggered immediately upon the 3:00PM punch.
								//if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch-self::$premature_delay) ) {
								//	$type_id = 50;
								//}

								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'time_stamp' => $p_obj->getTimeStamp() );
							}

							if ( isset($punch_pairs) ) {
								foreach($punch_pairs as $punch_control_id => $punch_pair) {
									if ( count($punch_pair) != 2 ) {
										Debug::text('a2Found Missing Punch: ', __FILE__, __LINE__, __METHOD__,10);

										if ( $punch_pair[0]['status_id'] == 10 ) { //Missing Out Punch
											Debug::text('b2Found Missing Out Punch: ', __FILE__, __LINE__, __METHOD__,10);

											//Make sure we are at least MaximumShift Time from the matching In punch before trigging this exception.
											//Even when an supervisor is entering punches for today, make missing out punch pre-mature if the maximum shift time isn't exceeded.
											//This will prevent timesheet recalculations from having missing punches for everyone today.
											//if ( $type_id == 5 AND $punch_pair[0]['time_stamp'] < ($current_epoch-self::$premature_delay) ) {
											if ( $punch_pair[0]['time_stamp'] < ($current_epoch-self::$premature_delay) ) {
												$type_id = 50;
											} else {
												$type_id = 5;
											}

											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => $punch_pair[0]['punch_control_id'],
																		);
										}
									} else {
										Debug::text('No Missing Punches...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($punch_pairs, $punch_pair);
						}

						break;
					case 'm3': //Missing Lunch In/Out punch
						if ( $plf->getRecordCount() > 0 ) {
							//We need to account for cases where they may punch IN from lunch first, then Out.
							//As well as just a Lunch In punch and nothing else.
							foreach ( $plf as $p_obj ) {
								if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch-self::$premature_delay) ) {
									$type_id = 50;
								}

								$punches[] = $p_obj;
							}

							if ( isset($punches) AND is_array($punches) ) {
								foreach( $punches as $key => $p_obj ) {
									if ( $p_obj->getType() == 20 ) { //Lunch
										Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
										if ( $p_obj->getStatus() == 10 ) {
											//Make sure previous punch is Lunch/Out
											if ( !isset($punches[$key-1])
													OR ( isset($punches[$key-1]) AND is_object($punches[$key-1])
															AND ( $punches[$key-1]->getType() != 20
																OR $punches[$key-1]->getStatus() != 20 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										} else {
											//Make sure next punch is Lunch/In
											if ( !isset($punches[$key+1]) OR ( isset($punches[$key+1]) AND is_object($punches[$key+1]) AND ( $punches[$key+1]->getType() != 20 OR $punches[$key+1]->getStatus() != 10 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										}
									}
								}
								unset($punches, $key, $p_obj);

								if ( isset($invalid_punches) AND count($invalid_punches) > 0 ) {
									foreach( $invalid_punches as $invalid_punch_arr ) {
										Debug::text('Found Missing Lunch In/Out Punch: ', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $invalid_punch_arr['punch_id'],
																		'punch_control_id' => FALSE,
																	);
									}
									unset($invalid_punch_arr);
								} else {
									Debug::text('Lunch Punches match up.', __FILE__, __LINE__, __METHOD__,10);
								}
								unset($invalid_punches);
							}
						}
						break;
					case 'm4': //Missing Break In/Out punch
						if ( $plf->getRecordCount() > 0 ) {
							//We need to account for cases where they may punch IN from break first, then Out.
							//As well as just a break In punch and nothing else.
							foreach ( $plf as $p_obj ) {
								if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch-self::$premature_delay) ) {
									$type_id = 50;
								}

								$punches[] = $p_obj;
							}

							if ( isset($punches) AND is_array($punches) ) {
								foreach( $punches as $key => $p_obj ) {
									if ( $p_obj->getType() == 30 ) { //Break
										Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Type: '. $p_obj->getType() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
										if ( $p_obj->getStatus() == 10 ) {
											//Make sure previous punch is Break/Out
											if ( !isset($punches[$key-1])
													OR ( isset($punches[$key-1]) AND is_object($punches[$key-1])
															AND ( $punches[$key-1]->getType() != 30
																OR $punches[$key-1]->getStatus() != 20 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										} else {
											//Make sure next punch is Break/In
											if ( !isset($punches[$key+1]) OR ( isset($punches[$key+1]) AND is_object($punches[$key+1]) AND ( $punches[$key+1]->getType() != 30 OR $punches[$key+1]->getStatus() != 10 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										}
									}
								}
								unset($punches, $key, $p_obj);

								if ( isset($invalid_punches) AND count($invalid_punches) > 0 ) {
									foreach( $invalid_punches as $invalid_punch_arr ) {
										Debug::text('Found Missing Break In/Out Punch: ', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $invalid_punch_arr['punch_id'],
																		'punch_control_id' => FALSE,
																	);
									}
									unset($invalid_punch_arr);
								} else {
									Debug::text('Lunch Punches match up.', __FILE__, __LINE__, __METHOD__,10);
								}
								unset($invalid_punches);
							}
						}
						break;
					case 'c1': //Missed Check-in
						//Use grace period and make sure the employee punches within that period of time (usually a transfer punch, but break/lunch should work too)
						if ( $plf->getRecordCount() > 0 AND $ep_obj->getGrace() > 0 ) {
							$prev_punch_time_stamp = FALSE;
							$prev_punch_obj = FALSE;

							$x=1;
							foreach ( $plf as $p_obj ) {
								Debug::text('   Missed Check-In Punch: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ) .' Delay: '. self::$premature_delay .' Current Epoch: '. $current_epoch, __FILE__, __LINE__, __METHOD__,10);

								//Handle punch pairs below. Only trigger on OUT punches.
								if ( is_object($prev_punch_obj) AND $prev_punch_obj->getStatus() == 10
									AND $p_obj->getStatus() == 20 AND ( $p_obj->getTimeStamp()-$prev_punch_time_stamp ) > $ep_obj->getGrace() ) { //Only check OUT punches when paired.
									Debug::text('   Triggering excepetion as employee missed check-in within: '. ( $p_obj->getTimeStamp()-$prev_punch_time_stamp ), __FILE__, __LINE__, __METHOD__,10);
									$current_exceptions[] = array(
																	'user_date_id' => $user_date_id,
																	'exception_policy_id' => $ep_obj->getId(),
																	'type_id' => $type_id,
																	'punch_id' => $p_obj->getID(), //When paired, only attach to the out punch.
																	'punch_control_id' => FALSE,
																	'punch_obj' => $p_obj,
																	'schedule_obj' => $p_obj->getScheduleObject(),
																);
								} elseif ( $prev_punch_time_stamp !== FALSE ) {
									Debug::text('   Employee Checked-In within: '. ( $p_obj->getTimeStamp()-$prev_punch_time_stamp ), __FILE__, __LINE__, __METHOD__,10);
								}

								//Handle cases where there is a IN punch but no OUT punch yet.
								//However ignore cases where there is a OUT punch but no IN punch.
								if ( $x == $plf->getRecordCount()
										AND $p_obj->getStatus() == 10
										AND ( $current_epoch-$p_obj->getTimeStamp() ) > $ep_obj->getGrace()
										AND $p_obj->getTimeStamp() > ($current_epoch-self::$premature_delay)
										) {
									Debug::text('   Triggering excepetion as employee hasnt checked in yet, within: '. ( $current_epoch-$prev_punch_time_stamp ), __FILE__, __LINE__, __METHOD__,10);
									$current_exceptions[] = array(
																	'user_date_id' => $user_date_id,
																	'exception_policy_id' => $ep_obj->getId(),
																	'type_id' => $type_id,
																	'punch_id' => FALSE,
																	'punch_control_id' => $p_obj->getPunchControlID(), //When not paired, attach to the punch control.
																	'punch_obj' => $p_obj,
																	'schedule_obj' => $p_obj->getScheduleObject(),
																);
								}

								$prev_punch_time_stamp = $p_obj->getTimeStamp();
								$prev_punch_obj = $p_obj;
								$x++;
							}
						}
						unset($prev_punch_obj, $prev_punch_time_stamp, $x);
						break;
					case 'd1': //No Branch or Department
						$add_exception = FALSE;
						foreach ( $plf as $p_obj ) {
							//In punches only
							if ( $p_obj->getStatus() == 10 AND is_object( $p_obj->getPunchControlObject() ) ) {
								//If no Tasks are setup, ignore checking them.
								if ( $p_obj->getPunchControlObject()->getBranch() == ''
										OR $p_obj->getPunchControlObject()->getBranch() == 0
										OR $p_obj->getPunchControlObject()->getBranch() == FALSE  ) {
									$add_exception = TRUE;
								}

								if ( $p_obj->getPunchControlObject()->getDepartment() == ''
										OR $p_obj->getPunchControlObject()->getDepartment() == 0
										OR $p_obj->getPunchControlObject()->getDepartment() == FALSE ) {

									//Make sure at least one task exists before triggering exception.
									$dlf = TTNew('DepartmentListFactory');
									$dlf->getByCompanyID( $user_date_obj->getUserObject()->getCompany(), 1 ); //Limit to just 1 record.
									if ( $dlf->getRecordCount() > 0 ) {
										$add_exception = TRUE;
									}
								}

								if ( $add_exception === TRUE ) {
									$current_exceptions[] = array(
																	'user_date_id' => $user_date_id,
																	'exception_policy_id' => $ep_obj->getId(),
																	'type_id' => $type_id,
																	'punch_id' => $p_obj->getId(),
																	'punch_control_id' => $p_obj->getPunchControlId(),
																);
								}
							}
						}
						break;
					case 's7': //Over Scheduled Hours
						if ( $plf->getRecordCount() > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//This ONLY takes in to account WORKED hours, not paid absence hours.
							//FIXME: Do we want to trigger this before their last out punch?
							$schedule_total_time = 0;

							if ( $slf->getRecordCount() > 0 ) {
								//Check for schedule policy
								foreach ( $slf as $s_obj ) {
									Debug::text(' Schedule Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);
									$schedule_total_time += $s_obj->getTotalTime();
								}

								$daily_total_time = 0;
								if ( $schedule_total_time > 0 ) {
									//Get daily total time.
									$udtlf = TTnew( 'UserDateTotalListFactory' );

									//Take into account auto-deduct/add meal policies, but not paid absences.
									//$udtlf->getByUserDateIdAndStatusAndType( $user_date_id, 10, 10 );
									$udtlf->getByUserDateId( $user_date_id );
									if ( $udtlf->getRecordCount() > 0 ) {
										foreach( $udtlf as $udt_obj ) {
											if ( $udt_obj->getTimeCategory() == 'worked_time' ) {
												$daily_total_time += $udt_obj->getTotalTime();
											}
										}
									}

									Debug::text(' Daily Total Time: '. $daily_total_time .' Schedule Total Time: '. $schedule_total_time, __FILE__, __LINE__, __METHOD__,10);

									if ( $daily_total_time > 0 AND $daily_total_time > ( $schedule_total_time + $ep_obj->getGrace() ) ) {
										Debug::text(' Worked Over Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);

										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																	);
									} else {
										Debug::text(' DID NOT Work Over Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							} else {
								Debug::text(' Not Scheduled', __FILE__, __LINE__, __METHOD__,10);
							}
						}
						break;
					case 's8': //Under Scheduled Hours
						if ( $plf->getRecordCount() > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//This ONLY takes in to account WORKED hours, not paid absence hours.
							$schedule_total_time = 0;

							if ( $slf->getRecordCount() > 0 ) {
								//Check for schedule policy
								foreach ( $slf as $s_obj ) {
									Debug::text(' Schedule Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);
									$schedule_total_time += $s_obj->getTotalTime();
								}

								$daily_total_time = 0;
								if ( $schedule_total_time > 0 ) {
									//Get daily total time.
									$udtlf = TTnew( 'UserDateTotalListFactory' );

									//Take into account auto-deduct/add meal policies
									//$udtlf->getByUserDateIdAndStatusAndType( $user_date_id, 10, 10 );
									$udtlf->getByUserDateId( $user_date_id );
									if ( $udtlf->getRecordCount() > 0 ) {
										foreach( $udtlf as $udt_obj ) {
											if ( $udt_obj->getTimeCategory() == 'worked_time' ) {
												$daily_total_time += $udt_obj->getTotalTime();
											}
										}
									}

									Debug::text(' Daily Total Time: '. $daily_total_time .' Schedule Total Time: '. $schedule_total_time, __FILE__, __LINE__, __METHOD__,10);

									if ( $daily_total_time < ( $schedule_total_time - $ep_obj->getGrace() ) ) {
										Debug::text(' Worked Under Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);

										if ( $type_id == 5 AND $user_date_obj->getDateStamp() < TTDate::getBeginDayEpoch( ($current_epoch-self::$premature_delay) ) ) {
											$type_id = 50;
										}

										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																	);
									} else {
										Debug::text(' DID NOT Work Under Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							} else {
								Debug::text(' Not Scheduled', __FILE__, __LINE__, __METHOD__,10);
							}
						}
						break;
					case 'o1': //Over Daily Time.
						if ( $plf->getRecordCount() > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//This ONLY takes in to account WORKED hours, not paid absence hours.
							//FIXME: Do we want to trigger this before their last out punch?
							$daily_total_time = 0;

							//Get daily total time.
							$udtlf = TTnew( 'UserDateTotalListFactory' );

							//Take into account auto-deduct/add meal policies
							//$udtlf->getByUserDateIdAndStatusAndType( $user_date_id, 10, 10 );
							$udtlf->getByUserDateId( $user_date_id );
							if ( $udtlf->getRecordCount() > 0 ) {
								foreach( $udtlf as $udt_obj ) {
									if ( $udt_obj->getTimeCategory() == 'worked_time' ) {
										$daily_total_time += $udt_obj->getTotalTime();
									}
								}
							}

							Debug::text(' Daily Total Time: '. $daily_total_time .' Watch Window: '. $ep_obj->getWatchWindow() .' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);

							if ( $daily_total_time > 0 AND $daily_total_time > $ep_obj->getWatchWindow() ) {
								Debug::text(' Worked Over Daily Hours', __FILE__, __LINE__, __METHOD__,10);

								$current_exceptions[] = array(
																'user_date_id' => $user_date_id,
																'exception_policy_id' => $ep_obj->getId(),
																'type_id' => $type_id,
																'punch_id' => FALSE,
																'punch_control_id' => FALSE,
															);
							} else {
								Debug::text(' DID NOT Work Over Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);
							}
						}
						break;
					case 'o2': //Over Weekly Time.
					case 's9': //Over Weekly Scheduled Time.
						if ( $plf->getRecordCount() > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//Get Pay Period Schedule info
							//FIXME: Do we want to trigger this before their last out punch?
							if ( is_object($user_date_obj->getPayPeriodObject())
									AND is_object($user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()) ) {
								$start_week_day_id = $user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
							} else {
								$start_week_day_id = 0;
							}
							Debug::text('Start Week Day ID: '. $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

							$weekly_scheduled_total_time = 0;

							//Currently we only consider committed scheduled shifts. We may need to change this to take into account
							//recurring scheduled shifts that haven't been committed yet as well.
							//In either case though we should take into account the entires week worth of scheduled time even if we are only partially through
							//the week, that way we won't be triggering s9 exceptions on a Wed and a Fri or something, it will only occur on the last days of the week.
							if ( strtolower( $ep_obj->getType() ) == 's9' ) {
								$tmp_slf = TTnew( 'ScheduleListFactory' );
								$tmp_slf->getByUserIdAndStartDateAndEndDate( $user_date_obj->getUser(), TTDate::getBeginWeekEpoch($user_date_obj->getDateStamp(), $start_week_day_id), TTDate::getEndWeekEpoch($user_date_obj->getDateStamp(), $start_week_day_id) );
								if ( $tmp_slf->getRecordCount() > 0 ) {
									foreach( $tmp_slf as $s_obj ) {
										if ( $s_obj->getStatus() == 10 ) { //Only working shifts.
											$weekly_scheduled_total_time += $s_obj->getTotalTime();
										}
									}
								}
								unset($tmp_slf, $s_obj);
							}

							//This ONLY takes in to account WORKED hours, not paid absence hours.
							$weekly_total_time = 0;

							//Get daily total time.
							$udtlf = TTnew( 'UserDateTotalListFactory' );
							$weekly_total_time = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $user_date_obj->getUser(), TTDate::getBeginWeekEpoch($user_date_obj->getDateStamp(), $start_week_day_id), $user_date_obj->getDateStamp() );

							Debug::text(' Weekly Total Time: '. $weekly_total_time .' Weekly Scheduled Total Time: '. $weekly_scheduled_total_time .' Watch Window: '. $ep_obj->getWatchWindow() .' Grace: '. $ep_obj->getGrace() .' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);
							//Don't trigger either of these exceptions unless both the worked and scheduled time is greater than 0. If they aren't scheduled at all
							//it should trigger a Unscheduled Absence exception instead of a over weekly scheduled time exception.
							if ( ( strtolower( $ep_obj->getType() ) == 'o2' AND $weekly_total_time > 0 AND $weekly_total_time > $ep_obj->getWatchWindow() )
									OR ( strtolower( $ep_obj->getType() ) == 's9' AND $weekly_scheduled_total_time > 0 AND $weekly_total_time > 0 AND $weekly_total_time > ( $weekly_scheduled_total_time + $ep_obj->getGrace() ) ) ) {
								Debug::text(' Worked Over Weekly Hours', __FILE__, __LINE__, __METHOD__,10);
								$current_exceptions[] = array(
																'user_date_id' => $user_date_id,
																'exception_policy_id' => $ep_obj->getId(),
																'type_id' => $type_id,
																'punch_id' => FALSE,
																'punch_control_id' => FALSE,
															);
							} else {
								Debug::text(' DID NOT Work Over Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);
							}
						}

						break;
					case 'l1': //Long Lunch
					case 'l2': //Short Lunch
						if ( $plf->getRecordCount() > 0 ) {
							//Get all lunch punches.
							$pair = 0;
							$x = 0;
							$out_for_lunch = FALSE;
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 20 ) {
									$lunch_out_timestamp = $p_obj->getTimeStamp();
									$lunch_punch_arr[$pair]['punch_id'] = $p_obj->getId();
									$out_for_lunch = TRUE;
								} elseif ( $out_for_lunch == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 20) {
									$lunch_punch_arr[$pair][20] = $lunch_out_timestamp;
									$lunch_punch_arr[$pair][10] = $p_obj->getTimeStamp();
									$out_for_lunch = FALSE;
									$pair++;
									unset($lunch_out_timestamp);
								} else {
									$out_for_lunch = FALSE;
								}
							}

							if ( isset($lunch_punch_arr) ) {
								//Debug::Arr($lunch_punch_arr, 'Lunch Punch Array: ', __FILE__, __LINE__, __METHOD__,10);

								foreach( $lunch_punch_arr as $pair => $time_stamp_arr ) {
									if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
										$lunch_total_time = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
										Debug::text(' Lunch Total Time: '. $lunch_total_time, __FILE__, __LINE__, __METHOD__, 10);

										if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
											$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
										}

										//Check to see if they have a schedule policy
										if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE
												AND is_object( $p_obj->getScheduleObject() ) == TRUE
												AND is_object( $p_obj->getScheduleObject()->getSchedulePolicyObject() ) == TRUE ) {
											$mp_obj = $p_obj->getScheduleObject()->getSchedulePolicyObject()->getMealPolicyObject();
										} else {
											$mplf = TTnew( 'MealPolicyListFactory' );
											$mplf->getByPolicyGroupUserId( $user_date_obj->getUserObject()->getId() );
											if ( $mplf->getRecordCount() > 0 ) {
												Debug::text('Found Meal Policy to apply.', __FILE__, __LINE__, __METHOD__, 10);
												$mp_obj = $mplf->getCurrent();
											}
										}

										if ( isset($mp_obj) AND is_object($mp_obj) ) {
											$meal_policy_lunch_time = $mp_obj->getAmount();
											Debug::text('Meal Policy Time: '. $meal_policy_lunch_time, __FILE__, __LINE__, __METHOD__, 10);

											$add_exception = FALSE;
											if ( strtolower( $ep_obj->getType() ) == 'l1'
													AND $meal_policy_lunch_time > 0
													AND $lunch_total_time > 0
													AND $lunch_total_time > ($meal_policy_lunch_time + $ep_obj->getGrace() ) ) {
												$add_exception = TRUE;
											} elseif ( strtolower( $ep_obj->getType() ) == 'l2'
													AND $meal_policy_lunch_time > 0
													AND $lunch_total_time > 0
													AND $lunch_total_time < ( $meal_policy_lunch_time - $ep_obj->getGrace() ) ) {
												$add_exception = TRUE;
											}

											if ( $add_exception == TRUE ) {
												Debug::text('Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);

												if ( isset($time_stamp_arr['punch_id']) ) {
													$punch_id = $time_stamp_arr['punch_id'];
												} else {
													$punch_id = FALSE;
												}

												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $punch_id,
																				'punch_control_id' => FALSE,
																			);
												unset($punch_id);
											} else {
												Debug::text('Not Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);
											}
										}

									} else {
										Debug::text(' Lunch Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							} else {
								Debug::text(' No Lunch Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 'l3': //No Lunch
						if ( $plf->getRecordCount() > 0 ) {
							//If they are scheduled or not, we can check for a meal policy and base our
							//decision off that. We don't want a No Lunch exception on a 3hr short shift though.
							//Also ignore this exception if the lunch is auto-deduct.
							//**Try to assign this exception to a specific punch control id, so we can do searches based on punch branch.

							//Find meal policy
							//Use scheduled meal policy first.
							$meal_policy_obj = NULL;
							if ( $slf->getRecordCount() > 0 ) {
								Debug::text('Schedule Found...', __FILE__, __LINE__, __METHOD__,10);
								foreach ( $slf as $s_obj ) {
									if ( $s_obj->getSchedulePolicyObject() !== FALSE
											AND $s_obj->getSchedulePolicyObject()->getMealPolicyObject() !== FALSE
											AND $s_obj->getSchedulePolicyObject()->getMealPolicyObject()->getType() != 10 ) {
										Debug::text('Found Schedule Meal Policy... Trigger Time: '. $s_obj->getSchedulePolicyObject()->getMealPolicyObject()->getTriggerTime(), __FILE__, __LINE__, __METHOD__,10);
										$meal_policy_obj = $s_obj->getSchedulePolicyObject()->getMealPolicyObject();
									} else {
										Debug::text('Schedule Meal Policy does not exist, or is auto-deduct?', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							} else {
								Debug::text('No Schedule Found...', __FILE__, __LINE__, __METHOD__,10);

								//Check if they have a meal policy, with no schedule.
								$mplf = TTnew( 'MealPolicyListFactory' );
								$mplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
								if ( $mplf->getRecordCount() > 0 ) {
									foreach( $mplf as $mp_obj ) {
										if ( $mp_obj->getType() != 10 ) {
											Debug::text('Found UnScheduled meal Policy... Trigger Time: '. $mp_obj->getTriggerTime(), __FILE__, __LINE__, __METHOD__,10);
											$meal_policy_obj = $mp_obj;
										}
									}
									unset($mplf, $mp_obj);
								} else {
									//There is no  meal policy or schedule policy with a meal policy assigned to it
									//With out this we could still apply No meal exceptions, but they will happen even on
									//a 2minute shift.
									Debug::text('No Lunch policy, applying No meal exception.', __FILE__, __LINE__, __METHOD__,10);
									$meal_policy_obj = TRUE;
								}
							}

							if ( is_object($meal_policy_obj) OR $meal_policy_obj === TRUE ) {
								$punch_control_id = FALSE;

								$daily_total_time = 0;
								$udtlf = TTnew( 'UserDateTotalListFactory' );
								$udtlf->getByUserDateIdAndStatus( $user_date_id, 20 );
								if ( $udtlf->getRecordCount() > 0 ) {
									foreach( $udtlf as $udt_obj ) {
										$daily_total_time += $udt_obj->getTotalTime();
										$punch_control_total_time[$udt_obj->getPunchControlID()] = $udt_obj->getTotalTime();
									}
								}
								Debug::text('Day Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__,10);
								//Debug::Arr($punch_control_total_time, 'Punch Control Total Time: ', __FILE__, __LINE__, __METHOD__,10);

								if ( $daily_total_time > 0 AND ( $meal_policy_obj === TRUE OR $daily_total_time > $meal_policy_obj->getTriggerTime() ) ) {
									//Check for meal punch.
									$meal_punch = FALSE;
									$tmp_punch_total_time = 0;
									$tmp_punch_control_ids = array();
									foreach ( $plf as $p_obj ) {
										if ( $p_obj->getType() == 20 ) { //20 = Lunch
											Debug::text('Found meal Punch: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
											$meal_punch = TRUE;
											break;
										}

										if ( isset($punch_control_total_time[$p_obj->getPunchControlID()]) AND !isset($tmp_punch_control_ids[$p_obj->getPunchControlID()]) ) {
											$tmp_punch_total_time += $punch_control_total_time[$p_obj->getPunchControlID()];
											if ( $punch_control_id === FALSE AND ( $meal_policy_obj === TRUE OR $tmp_punch_total_time > $meal_policy_obj->getTriggerTime() ) ) {
												Debug::text('Found punch control for exception: '. $p_obj->getPunchControlID() .' Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__,10);
												$punch_control_id = $p_obj->getPunchControlID();
												//Don't meal the loop here, as we have to continue on and check for other meals.
											}
										}
										$tmp_punch_control_ids[$p_obj->getPunchControlID()] = TRUE;
									}
									unset($tmp_punch_total_time, $tmp_punch_control_ids);

									if ( $meal_punch == FALSE ) {
										Debug::text('Triggering No Lunch exception!', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => $punch_control_id,
																	);
									}
								}
							}
						}
						break;
					case 'b1': //Long Break
					case 'b2': //Short Break
						if ( $plf->getRecordCount() > 0 ) {
							//Get all break punches.
							$pair = 0;
							$x = 0;
							$out_for_break = FALSE;
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 30 ) {
									$break_out_timestamp = $p_obj->getTimeStamp();
									$break_punch_arr[$pair]['punch_id'] = $p_obj->getId();
									$out_for_break = TRUE;
								} elseif ( $out_for_break == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 30) {
									$break_punch_arr[$pair][20] = $break_out_timestamp;
									$break_punch_arr[$pair][10] = $p_obj->getTimeStamp();
									$out_for_break = FALSE;
									$pair++;
									unset($break_out_timestamp);
								} else {
									$out_for_break = FALSE;
								}
							}
							unset($pair);

							if ( isset($break_punch_arr) ) {
								//Debug::Arr($break_punch_arr, 'Break Punch Array: ', __FILE__, __LINE__, __METHOD__,10);

								foreach( $break_punch_arr as $pair => $time_stamp_arr ) {
									if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
										$break_total_time = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
										Debug::text(' Break Total Time: '. $break_total_time, __FILE__, __LINE__, __METHOD__, 10);

										if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
											$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
										}

										//Check to see if they have a schedule policy
										$bplf = TTnew( 'BreakPolicyListFactory' );
										if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE
												AND is_object( $p_obj->getScheduleObject() ) == TRUE
												AND is_object( $p_obj->getScheduleObject()->getSchedulePolicyObject() ) == TRUE ) {
											$break_policy_ids = $p_obj->getScheduleObject()->getSchedulePolicyObject()->getBreakPolicy();
											$bplf->getByIdAndCompanyId( $break_policy_ids, $user_date_obj->getUserObject()->getCompany() );
										} else {
											$bplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
										}
										unset($break_policy_ids);

										if ( $bplf->getRecordCount() > 0 ) {
											Debug::text('Found Break Policy(ies) to apply: '. $bplf->getRecordCount() .' Pair: '. $pair, __FILE__, __LINE__, __METHOD__, 10);

											foreach( $bplf as $bp_obj ) {
												$bp_objs[] = $bp_obj;
											}
											unset($bplf, $bp_obj);

											if ( isset($bp_objs[$pair]) AND is_object($bp_objs[$pair]) ) {
												$bp_obj = $bp_objs[$pair];

												$break_policy_break_time = $bp_obj->getAmount();
												Debug::text('Break Policy Time: '. $break_policy_break_time .' ID: '. $bp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

												$add_exception = FALSE;
												if ( strtolower( $ep_obj->getType() ) == 'b1'
														AND $break_policy_break_time > 0
														AND $break_total_time > 0
														AND $break_total_time > ($break_policy_break_time + $ep_obj->getGrace() ) ) {
													$add_exception = TRUE;
												} elseif ( strtolower( $ep_obj->getType() ) == 'b2'
														AND $break_policy_break_time > 0
														AND $break_total_time > 0
														AND $break_total_time < ( $break_policy_break_time - $ep_obj->getGrace() ) ) {
													$add_exception = TRUE;
												}

												if ( $add_exception == TRUE ) {
													Debug::text('Adding Exception! '. $ep_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);

													if ( isset($time_stamp_arr['punch_id']) ) {
														$punch_id = $time_stamp_arr['punch_id'];
													} else {
														$punch_id = FALSE;
													}

													$current_exceptions[] = array(
																					'user_date_id' => $user_date_id,
																					'exception_policy_id' => $ep_obj->getId(),
																					'type_id' => $type_id,
																					'punch_id' => $punch_id,
																					'punch_control_id' => FALSE,
																				);
													unset($punch_id);
												} else {
													Debug::text('Not Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);
												}

												unset($bp_obj);
											}
											unset( $bp_objs );
										}
									} else {
										Debug::text(' Break Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							} else {
								Debug::text(' No Break Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 'b3': //Too Many Breaks
					case 'b4': //Too Few Breaks
						if ( $plf->getRecordCount() > 0 ) {
							//Get all break punches.
							$pair = 0;
							$x = 0;
							$out_for_break = FALSE;
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 30 ) {
									$break_out_timestamp = $p_obj->getTimeStamp();
									$break_punch_arr[$pair]['punch_id'] = $p_obj->getId();
									$out_for_break = TRUE;
								} elseif ( $out_for_break == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 30) {
									$break_punch_arr[$pair][20] = $break_out_timestamp;
									$break_punch_arr[$pair][10] = $p_obj->getTimeStamp();
									$out_for_break = FALSE;
									$pair++;
									unset($break_out_timestamp);
								} else {
									$out_for_break = FALSE;
								}
							}
							unset($pair);

							//Get daily total time.
							$daily_total_time = 0;
							$udtlf = TTnew( 'UserDateTotalListFactory' );
							//$udtlf->getByUserDateIdAndStatusAndType( $user_date_id, 10, 10 );
							$udtlf->getByUserDateId( $user_date_id );
							if ( $udtlf->getRecordCount() > 0 ) {
								foreach( $udtlf as $udt_obj ) {
									if ( $udt_obj->getTimeCategory() == 'worked_time' ) {
										$daily_total_time += $udt_obj->getTotalTime();
									}
								}
							}

							Debug::text(' Daily Total Time: '. $daily_total_time .' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);

							//Make sure we take into account how long they have currently worked, so we don't
							//say too few breaks for 3hr shift that they employee took one break on.
							//Trigger this exception if the employee doesn't take a break at all?
							if ( isset($break_punch_arr) ) {
								$total_breaks = count($break_punch_arr);

								//Debug::Arr($break_punch_arr, 'Break Punch Array: ', __FILE__, __LINE__, __METHOD__,10);

								foreach( $break_punch_arr as $pair => $time_stamp_arr ) {
									if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
										$break_total_time = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
										Debug::text(' Break Total Time: '. $break_total_time, __FILE__, __LINE__, __METHOD__, 10);

										if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
											$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
										}

										//Check to see if they have a schedule policy
										$bplf = TTnew( 'BreakPolicyListFactory' );
										if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE
												AND is_object( $p_obj->getScheduleObject() ) == TRUE
												AND is_object( $p_obj->getScheduleObject()->getSchedulePolicyObject() ) == TRUE ) {
											$break_policy_ids = $p_obj->getScheduleObject()->getSchedulePolicyObject()->getBreakPolicy();
											$bplf->getByIdAndCompanyId( $break_policy_ids, $user_date_obj->getUserObject()->getCompany() );
										} else {
											//$bplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
											$bplf->getByPolicyGroupUserIdAndDayTotalTime( $user_date_obj->getUser(), $daily_total_time );
										}
										unset($break_policy_ids);

										$allowed_breaks = $bplf->getRecordCount();

										$add_exception = FALSE;
										if ( strtolower( $ep_obj->getType() ) == 'b3' AND $total_breaks > $allowed_breaks ) {
											Debug::text(' Too many breaks taken...', __FILE__, __LINE__, __METHOD__, 10);
											$add_exception = TRUE;
										} elseif ( strtolower( $ep_obj->getType() ) == 'b4' AND $total_breaks < $allowed_breaks )  {
											Debug::text(' Too few breaks taken...', __FILE__, __LINE__, __METHOD__, 10);
											$add_exception = TRUE;
										} else {
											Debug::text(' Proper number of breaks taken...', __FILE__, __LINE__, __METHOD__, 10);
										}

										if ( $add_exception == TRUE
												AND ( strtolower( $ep_obj->getType() ) == 'b4'
													 OR ( strtolower( $ep_obj->getType() ) == 'b3' AND $pair > ($allowed_breaks-1) )  ) ) {
											Debug::text('Adding Exception! '. $ep_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);

											if ( isset($time_stamp_arr['punch_id']) AND strtolower( $ep_obj->getType() ) == 'b3' ) {
												$punch_id = $time_stamp_arr['punch_id'];
											} else {
												$punch_id = FALSE;
											}

											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => $punch_id,
																			'punch_control_id' => FALSE,
																		);
											unset($punch_id);
										} else {
											Debug::text('Not Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);
										}

									}
								}
							}
						}
						break;
					case 'b5': //No Break
						if ( $plf->getRecordCount() > 0 ) {
							//If they are scheduled or not, we can check for a break policy and base our
							//decision off that. We don't want a No Break exception on a 3hr short shift though.
							//Also ignore this exception if the break is auto-deduct.
							//**Try to assign this exception to a specific punch control id, so we can do searches based on punch branch.

							//Find break policy
							//Use scheduled break policy first.
							$break_policy_obj = NULL;
							if ( $slf->getRecordCount() > 0 ) {
								Debug::text('Schedule Found...', __FILE__, __LINE__, __METHOD__,10);
								foreach ( $slf as $s_obj ) {
									if ( $s_obj->getSchedulePolicyObject() !== FALSE ) {
										$break_policy_ids = $s_obj->getSchedulePolicyObject()->getBreakPolicy();
										if ( is_array($break_policy_ids) ) {
											$bplf = TTNew('BreakPolicyListFactory');
											$bplf->getByIdAndCompanyId($break_policy_ids, $user_date_obj->getUserObject()->getCompany() );
											if ( $bplf->getRecordCount() > 0 ) {
												foreach( $bplf as $bp_obj ) {
													if ( $bp_obj->getType() != 10 ) {
														$break_policy_obj = $bp_obj;
														break;
													}
												}
											}
										}
									}
									unset($s_obj, $break_policy_ids, $bplf, $bp_obj);
								}
							} else {
								Debug::text('No Schedule Found...', __FILE__, __LINE__, __METHOD__,10);

								//Check if they have a break policy, with no schedule.
								$bplf = TTnew( 'BreakPolicyListFactory' );
								$bplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
								if ( $bplf->getRecordCount() > 0 ) {
									Debug::text('Found UnScheduled Break Policy...', __FILE__, __LINE__, __METHOD__,10);
									foreach( $bplf as $bp_obj ) {
										if ( $bp_obj->getType() != 10 ) {
											$break_policy_obj = $bp_obj;
											break;
										}
									}
									unset($bplf, $bp_obj);
								} else {
									//There is no  break policy or schedule policy with a break policy assigned to it
									//With out this we could still apply No Break exceptions, but they will happen even on
									//a 2minute shift.
									Debug::text('No break policy, applying No break exception.', __FILE__, __LINE__, __METHOD__,10);
									$break_policy_obj = TRUE;
								}
							}

							if ( is_object($break_policy_obj) OR $break_policy_obj === TRUE ) {
								$punch_control_id = FALSE;

								$daily_total_time = 0;
								$udtlf = TTnew( 'UserDateTotalListFactory' );
								$udtlf->getByUserDateIdAndStatus( $user_date_id, 20 );
								if ( $udtlf->getRecordCount() > 0 ) {
									foreach( $udtlf as $udt_obj ) {
										$daily_total_time += $udt_obj->getTotalTime();
										$punch_control_total_time[$udt_obj->getPunchControlID()] = $udt_obj->getTotalTime();
									}
								}
								Debug::text('Day Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__,10);
								//Debug::Arr($punch_control_total_time, 'Punch Control Total Time: ', __FILE__, __LINE__, __METHOD__,10);

								if ( $daily_total_time > 0 AND ( $break_policy_obj === TRUE OR $daily_total_time > $break_policy_obj->getTriggerTime() ) ) {
									//Check for break punch.
									$break_punch = FALSE;
									$tmp_punch_total_time = 0;
									$tmp_punch_control_ids = array();
									foreach ( $plf as $p_obj ) {
										if ( $p_obj->getType() == 30 ) { //30 = Break
											Debug::text('Found break Punch: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
											$break_punch = TRUE;
											break;
										}

										if ( isset($punch_control_total_time[$p_obj->getPunchControlID()]) AND !isset($tmp_punch_control_ids[$p_obj->getPunchControlID()]) ) {
											$tmp_punch_total_time += $punch_control_total_time[$p_obj->getPunchControlID()];
											if ( $punch_control_id === FALSE AND ( $break_policy_obj === TRUE OR $tmp_punch_total_time > $break_policy_obj->getTriggerTime() ) ) {
												Debug::text('Found punch control for exception: '. $p_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__,10);
												$punch_control_id = $p_obj->getPunchControlID();
												//Don't break the loop here, as we have to continue on and check for other breaks.
											}
										}
										$tmp_punch_control_ids[$p_obj->getPunchControlID()] = TRUE;
									}
									unset($tmp_punch_total_time, $tmp_punch_control_ids);

									if ( $break_punch == FALSE ) {
										Debug::text('Triggering No Break exception!', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => $punch_control_id,
																	);
									}
								}
							}
						}
						break;
					case 'v1': //TimeSheet Not Verified
						//Get pay period schedule data, determine if timesheet verification is even enabled.
						if ( is_object($user_date_obj->getPayPeriodObject())
								AND is_object($user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject())
								AND $user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() > 10 ) {
							Debug::text('Verification enabled... Window Start: '. TTDate::getDate('DATE+TIME', $user_date_obj->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate() ) .' Grace Time: '. $ep_obj->getGrace() , __FILE__, __LINE__, __METHOD__,10);

							//*Only* trigger this exception on the last day of the pay period, because when the pay period is verified it has to force the last day to be recalculated.
							//Ignore timesheets without any time, (worked and absence). Or we could use the Watch Window to specify the minimum time required on
							//a timesheet to trigger this instead?
							//Make sure we are after the timesheet window start date + the grace period.
							if (	$user_date_obj->getPayPeriodObject()->getStatus() != 50
									AND $current_epoch >= ($user_date_obj->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate()+$ep_obj->getGrace())
									AND TTDate::getBeginDayEpoch( $user_date_obj->getDateStamp() ) == TTDate::getBeginDayEpoch( $user_date_obj->getPayPeriodObject()->getEndDate() )
									) {

									//Get pay period total time, include worked and paid absence time.
									$udtlf = TTnew( 'UserDateTotalListFactory' );
									$total_time = $udtlf->getTimeSumByUserIDAndPayPeriodId( $user_date_obj->getUser(), $user_date_obj->getPayPeriodObject()->getID() );
									if ( $total_time > 0 ) {
										//Check to see if pay period has been verified or not yet.
										$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
										$pptsvlf->getByPayPeriodIdAndUserId( $user_date_obj->getPayPeriodObject()->getId(), $user_date_obj->getUser() );

										$pay_period_verified = FALSE;
										if ( $pptsvlf->getRecordCount() > 0 ) {
											$pay_period_verified = $pptsvlf->getCurrent()->getAuthorized();
										}

										if ( $pay_period_verified == FALSE ) {
											//Always allow for emailing this exception because it can be triggered after a punch is modified and
											//any supervisor would need to be notified to verify the timesheet again.
											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => FALSE,
																			'enable_email_notification' => TRUE,
																		);
										} else {
											Debug::text('TimeSheet has already been authorized!', __FILE__, __LINE__, __METHOD__,10);
										}
									} else {
										Debug::text('Timesheet does not have any worked or paid absence time...', __FILE__, __LINE__, __METHOD__,10);
									}
									unset($udtlf, $total_time);
							} else {
								Debug::text('Not within timesheet verification window, or not after grace time.', __FILE__, __LINE__, __METHOD__,10);
							}
						} else {
							Debug::text('No Pay Period Schedule or TimeSheet Verificiation disabled...', __FILE__, __LINE__, __METHOD__,10);
						}
						break;
					case 'j1': //Not Allowed on Job
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND $plf->getRecordCount() > 0 ) {
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 10 ) { //In punches
									if ( is_object( $p_obj->getPunchControlObject() ) AND $p_obj->getPunchControlObject()->getJob() > 0 ) {
										//Found job punch, check job settings.
										$jlf = TTnew( 'JobListFactory' );
										$jlf->getById( $p_obj->getPunchControlObject()->getJob() );
										if ( $jlf->getRecordCount() > 0 ) {
											$j_obj = $jlf->getCurrent();

											if ( $j_obj->isAllowedUser( $user_date_obj->getUser() ) == FALSE ) {
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => $p_obj->getPunchControlId(),
																			);
											} else {
												Debug::text('    User allowed on Job!', __FILE__, __LINE__, __METHOD__,10);
											}
										} else {
											Debug::text('    Job not found!', __FILE__, __LINE__, __METHOD__,10);
										}
									} else {
										//Debug::text('    Not a Job Punch...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($j_obj);
						}
						break;
					case 'j2': //Not Allowed on Task
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND $plf->getRecordCount() > 0 ) {
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 10 ) { //In punches
									if ( is_object( $p_obj->getPunchControlObject() ) AND $p_obj->getPunchControlObject()->getJob() > 0 AND $p_obj->getPunchControlObject()->getJobItem() > 0 ) {
										//Found job punch, check job settings.
										$jlf = TTnew( 'JobListFactory' );
										$jlf->getById( $p_obj->getPunchControlObject()->getJob() );
										if ( $jlf->getRecordCount() > 0 ) {
											$j_obj = $jlf->getCurrent();

											if ( $j_obj->isAllowedItem( $p_obj->getPunchControlObject()->getJobItem() ) == FALSE ) {
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => $p_obj->getPunchControlId(),
																			);
											} else {
												Debug::text('    Job item allowed on job!', __FILE__, __LINE__, __METHOD__,10);
											}
										} else {
											Debug::text('    Job not found!', __FILE__, __LINE__, __METHOD__,10);
										}
									} else {
										//Debug::text('    Not a Job Punch...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}

							unset($j_obj);
						}
						break;
					case 'j3': //Job already completed
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND $plf->getRecordCount() > 0 ) {
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 10 ) { //In punches
									if ( is_object( $p_obj->getPunchControlObject() ) AND $p_obj->getPunchControlObject()->getJob() > 0 ) {
										//Found job punch, check job settings.
										$jlf = TTnew( 'JobListFactory' );
										$jlf->getById( $p_obj->getPunchControlObject()->getJob() );
										if ( $jlf->getRecordCount() > 0 ) {
											$j_obj = $jlf->getCurrent();

											//Status is completed and the User Date Stamp is greater then the job end date.
											//If no end date is set, ignore this.
											if ( $j_obj->getStatus() == 30 AND $j_obj->getEndDate() != FALSE AND $user_date_obj->getDateStamp() > $j_obj->getEndDate() ) {
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => $p_obj->getPunchControlId(),
																			);
											} else {
												Debug::text('    Job Not Completed!', __FILE__, __LINE__, __METHOD__,10);
											}
										} else {
											Debug::text('    Job not found!', __FILE__, __LINE__, __METHOD__,10);
										}
									} else {
										Debug::text('    Not a Job Punch...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($j_obj);
						}
						break;
					case 'j4': //No Job or Task
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND $plf->getRecordCount() > 0 ) {
							foreach ( $plf as $p_obj ) {
								$add_exception = FALSE;

								//In punches only
								if ( $p_obj->getStatus() == 10 AND is_object( $p_obj->getPunchControlObject() ) ) {
									//If no Tasks are setup, ignore checking them.
									if ( $p_obj->getPunchControlObject()->getJob() == ''
											OR $p_obj->getPunchControlObject()->getJob() == 0
											OR $p_obj->getPunchControlObject()->getJob() == FALSE  ) {
										$add_exception = TRUE;
									}

									if ( $p_obj->getPunchControlObject()->getJobItem() == ''
											OR $p_obj->getPunchControlObject()->getJobItem() == 0
											OR $p_obj->getPunchControlObject()->getJobItem() == FALSE ) {

										//Make sure at least one task exists before triggering exception.
										$jilf = TTNew('JobItemListFactory');
										$jilf->getByCompanyID( $user_date_obj->getUserObject()->getCompany(), 1 ); //Limit to just 1 record.
										if ( $jilf->getRecordCount() > 0 ) {
											$add_exception = TRUE;
										}
									}

									if ( $add_exception === TRUE ) {
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $p_obj->getId(),
																		'punch_control_id' => $p_obj->getPunchControlId(),
																	);
									}
								}
							}
						}
						break;
					default:
						Debug::text('BAD, should never get here: ', __FILE__, __LINE__, __METHOD__,10);
						break;
				}
			}
		}
		unset($ep_obj);

		$exceptions = self::diffExistingAndCurrentExceptions( $existing_exceptions, $current_exceptions );
		if ( is_array($exceptions) ) {
			if ( isset($exceptions['create_exceptions']) AND is_array($exceptions['create_exceptions']) AND count($exceptions['create_exceptions']) > 0 ) {
				Debug::text('Creating new exceptions... Total: '. count($exceptions['create_exceptions']), __FILE__, __LINE__, __METHOD__,10);
				foreach( $exceptions['create_exceptions'] as $tmp_exception ) {
					$ef = TTnew( 'ExceptionFactory' );
					$ef->setUserDateID( $tmp_exception['user_date_id'] );
					$ef->setExceptionPolicyID( $tmp_exception['exception_policy_id'] );
					$ef->setType( $tmp_exception['type_id'] );
					if ( isset($tmp_exception['punch_control_id']) AND $tmp_exception['punch_control_id'] != '' ) {
						$ef->setPunchControlId( $tmp_exception['punch_control_id'] );
					}
					if ( isset($tmp_exception['punch_id']) AND $tmp_exception['punch_id'] != '' ) {
						$ef->setPunchId( $tmp_exception['punch_id'] );
					}
					$ef->setEnableDemerits( TRUE );
					if ( $ef->isValid() ) {
						$ef->Save( FALSE ); //Save exception prior to emailing it, otherwise we can't save audit logs.
						if ( $enable_premature_exceptions == TRUE OR ( isset($tmp_exception['enable_email_notification']) AND $tmp_exception['enable_email_notification'] == TRUE ) ) {
							$eplf = TTnew( 'ExceptionPolicyListFactory' );
							$eplf->getById( $tmp_exception['exception_policy_id'] );
							if ( $eplf->getRecordCount() == 1 ) {
								$ep_obj = $eplf->getCurrent();
								$ef->emailException( $user_date_obj->getUserObject(), $user_date_obj, ( isset($tmp_exception['punch_obj']) ) ? $tmp_exception['punch_obj'] : NULL, ( isset($tmp_exception['schedule_obj']) ) ? $tmp_exception['schedule_obj'] : NULL, $ep_obj );
							}
						} else {
							Debug::text('Not emailing new exception: User Date ID: '. $tmp_exception['user_date_id'] .' Type ID: '. $tmp_exception['type_id'] .' Enable PreMature: '. (int)$enable_premature_exceptions, __FILE__, __LINE__, __METHOD__,10);
						}
					}
					unset($ef);
				}
			}

			if ( isset($exceptions['delete_exceptions']) AND is_array($exceptions['delete_exceptions']) AND count($exceptions['delete_exceptions']) > 0 ) {
				Debug::Text('Deleting no longer valid exceptions... Total: '. count($exceptions['delete_exceptions']), __FILE__, __LINE__, __METHOD__,10);
				$ef = TTnew( 'ExceptionFactory' );
				$ef->bulkDelete( $exceptions['delete_exceptions'] );
			}
		}
		$profiler->stopTimer( "ExceptionPolicy::calcExceptions()");

		return TRUE;
	}

	//This function needs to determine which new exceptions to create, and which old exceptions are no longer valid and to delete.
	static function diffExistingAndCurrentExceptions( $existing_exceptions, $current_exceptions ) {
		//Debug::Arr($existing_exceptions, 'Existing Exceptions: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($current_exceptions, 'Current Exceptions: ', __FILE__, __LINE__, __METHOD__,10);

		if ( is_array($existing_exceptions) AND count($existing_exceptions) == 0 ) {
			//No existing exceptions, nothing to delete or compare, just create new exceptions.
			return array( 'create_exceptions' => $current_exceptions, 'delete_exceptions' => array() );
		}

		if ( is_array($current_exceptions) AND count($current_exceptions) == 0 ) {
			//No current exceptions, delete all existing exceptions.
			foreach( $existing_exceptions as $existing_exception ) {
				$delete_exceptions[] = $existing_exception['id'];
			}
			return array( 'create_exceptions' => array(), 'delete_exceptions' => $delete_exceptions );
		}

		$new_exceptions = $current_exceptions; //Copy array so we can work from the copy.

		//Remove any current exceptions that already exist as existing exceptions.
		foreach( $current_exceptions as $current_key => $current_exception ) {
			foreach( $existing_exceptions as $existing_key => $existing_exception ) {
				//Need to match all elements except 'id'.
				if ( 	$current_exception['exception_policy_id'] == $existing_exception['exception_policy_id']
						AND
						$current_exception['type_id'] == $existing_exception['type_id']
						AND
						$current_exception['user_date_id'] == $existing_exception['user_date_id']
						AND
						$current_exception['punch_control_id'] == $existing_exception['punch_control_id']
						AND
						$current_exception['punch_id'] == $existing_exception['punch_id']
					) {
					//Debug::text('Removing current exception that matches existing exception: '. $current_key, __FILE__, __LINE__, __METHOD__,10);
					unset($new_exceptions[$current_key]);
				} else {
					//Debug::text('NOT Removing current exception that matches existing exception: Current: '. $current_key .' Existing: '. $existing_key, __FILE__, __LINE__, __METHOD__,10);
				}
			}
		}

		//Mark any existing exceptions that are not in the current exception list for deletion.
		$delete_exceptions = array();

		$delete_exception = FALSE;
		$total_current_exceptions = count($current_exceptions);
		foreach( $existing_exceptions as $existing_key => $existing_exception ) {
			$match_count = $total_current_exceptions;

			foreach( $current_exceptions as $current_key => $current_exception ) {
				if ( !( $current_exception['exception_policy_id'] == $existing_exception['exception_policy_id'] AND $current_exception['type_id'] == $existing_exception['type_id'] AND $current_exception['user_date_id'] == $existing_exception['user_date_id'] AND $current_exception['punch_control_id'] == $existing_exception['punch_control_id'] AND $current_exception['punch_id'] == $existing_exception['punch_id'] ) ) {
					$match_count--;
				}
				//Debug::text('aDetermining if we should delete this exception... Match Count: '. $match_count .' Total: '. $total_current_exceptions .' Existing Key: '. $existing_key, __FILE__, __LINE__, __METHOD__,10);
			}

			if ( $match_count == 0 ) {
				//Debug::text('bDetermining if we should delete this exception... Match Count: '. $match_count .' Total: '. $total_current_exceptions .' Existing Key: '. $existing_key, __FILE__, __LINE__, __METHOD__,10);
				$delete_exceptions[] = $existing_exception['id'];
			}
		}

		$retarr = array( 'create_exceptions' => $new_exceptions, 'delete_exceptions' => $delete_exceptions );
		//Debug::Arr($retarr, 'RetArr Exceptions: ', __FILE__, __LINE__, __METHOD__,10);
		return $retarr;
	}

	function isUnique($exception_policy_control_id, $type_id, $id ) {
		$ph = array(
					'exception_policy_control_id' => $exception_policy_control_id,
					'type_id' => trim(strtoupper($type_id)),
					'id' => (int)$id,
					);

		$query = 'select id from '. $this->getTable() .' where exception_policy_control_id = ? AND type_id = ? AND id != ? AND deleted = 0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id,'Unique Exception Control ID: '. $exception_policy_control_id .' Type ID: '. $type_id, __FILE__, __LINE__, __METHOD__,10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->isUnique( $this->getExceptionPolicyControl(), $this->getType(), $this->getID() ) == FALSE ) {
			$this->Validator->isTrue(		'type_id',
											FALSE,
											TTi18n::gettext('Duplicate exception already exists'));
		}

		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'is_enabled_watch_window':
						case 'is_enabled_grace':
							$function = str_replace('_', '', $variable);
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	//This is called for every record everytime, and doesn't help much because of that.
	//This has to be enabled to properly log modifications though.
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getExceptionPolicyControl(), $log_action, TTi18n::getText('Exception Policy') .' - '. TTi18n::getText('Type') .': '. Option::getByKey( $this->getType(), $this->getOptions('type') ), NULL, $this->getTable(), $this );
	}
}
?>
