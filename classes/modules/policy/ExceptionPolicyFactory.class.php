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


/**
 * @package Modules\Policy
 */
class ExceptionPolicyFactory extends Factory {
	protected $table = 'exception_policy';
	protected $pk_sequence_name = 'exception_policy_id_seq'; //PK Sequence name

	protected $enable_grace = array('S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'L1', 'L2', 'B1', 'B2', 'V1', 'C1' );
	protected $enable_watch_window = array('S3', 'S4', 'S5', 'S6', 'O1', 'O2');

	//M1 - Missing In Punch is not considered pre-mature, as the employee can't go back and punch in after the fact anyways.
	protected static $premature_exceptions = array('M2', 'M3', 'M4', 'L3', 'B4', 'B5', 'S8');

	protected static $premature_delay = 57600;

	protected $exception_policy_control_obj = NULL;

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										//Schedule Exceptions
										'S1' => TTi18n::gettext('Unscheduled Absence'),
										'S2' => TTi18n::gettext('Not Scheduled'),
										'S3' => TTi18n::gettext('In Early'),
										'S4' => TTi18n::gettext('In Late'),
										'S5' => TTi18n::gettext('Out Early'),
										'S6' => TTi18n::gettext('Out Late'),

										'S7' => TTi18n::gettext('Over Daily Scheduled Time'),
										'S8' => TTi18n::gettext('Under Daily Scheduled Time'),
										'S9' => TTi18n::gettext('Over Weekly Scheduled Time'),
										//'SA' => TTi18n::gettext('Under Weekly Scheduled Time'), //Is this needed?

										'SB' => TTi18n::gettext('Not Scheduled Branch or Department'),
										'SC' => TTi18n::gettext('Not Scheduled Job or Task'),

										//Add setting to set some sort of "Grace" period, or early warning system? Approaching overtime?
										//Have exception where they can set the cutoff in hours, and it triggers once the employee has exceeded the weekly hours.
										'O1' => TTi18n::gettext('Over Daily Time'),
										'O2' => TTi18n::gettext('Over Weekly Time'),

										//Punch Exceptions
										'M1' => TTi18n::gettext('Missing In Punch'),
										'M2' => TTi18n::gettext('Missing Out Punch'),
										'M3' => TTi18n::gettext('Missing Lunch In/Out Punch'),
										'M4' => TTi18n::gettext('Missing Break In/Out Punch'),

										'L1' => TTi18n::gettext('Long Lunch'),
										'L2' => TTi18n::gettext('Short Lunch'),
										'L3' => TTi18n::gettext('No Lunch'),

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
										'J1' => TTi18n::gettext('Not Allowed On Job'),
										'J2' => TTi18n::gettext('Not Allowed On Task'),
										'J3' => TTi18n::gettext('Job Completed'),
										'J4' => TTi18n::gettext('No Job or Task'),
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
											// Change it back to '0' as it causes problems with HTML5 interface, however tested it with Flex and seems to still work.
											'0' => TTi18n::gettext('None'),
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

	function getExceptionPolicyControlObject() {
		return $this->getGenericObject( 'ExceptionPolicyControlListFactory', $this->getExceptionPolicyControl(), 'exception_policy_control_obj' );
	}

	function getExceptionPolicyControl() {
		if ( isset($this->data['exception_policy_control_id']) ) {
			return (int)$this->data['exception_policy_control_id'];
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
				case 'SB': //Not Scheduled Branch/Department
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'SC': //Not Scheduled Job/Task
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 100,
												'demerit' => 5,
												'grace' => 0,
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
												'watch_window' => (3600 * 8),
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
												'watch_window' => (3600 * 40),
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
												'grace' => (48 * 3600), //48hrs grace period
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
			$corporate_exceptions = array('J1', 'J2', 'J3', 'J4', 'SC', 'C1');
			foreach( $corporate_exceptions as $corporate_exception ) {
				unset($options[$corporate_exception]);
			}
		}

		return $options;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return (string)$this->data['type_id']; //Should not be cast to int.
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
			return (int)$this->data['severity_id'];
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

		if	(	$value == 0
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

		if	(	$value == 0
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

		if	(	$value == 0
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
			return (int)$this->data['email_notification_id'];
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
							
	//This function needs to determine which new exceptions to create, and which old exceptions are no longer valid and to delete.
	static function diffExistingAndCurrentExceptions( $existing_exceptions, $current_exceptions ) {
		//Debug::Arr($existing_exceptions, 'Existing Exceptions: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($current_exceptions, 'Current Exceptions: ', __FILE__, __LINE__, __METHOD__, 10);

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
				if (	(int)$current_exception['exception_policy_id'] == (int)$existing_exception['exception_policy_id']
						AND
						(int)$current_exception['type_id'] == (int)$existing_exception['type_id']
						AND
						(int)$current_exception['user_id'] == (int)$existing_exception['user_id']
						AND
						(int)$current_exception['date_stamp'] == (int)$existing_exception['date_stamp']
						AND
						(int)$current_exception['punch_control_id'] == (int)$existing_exception['punch_control_id']
						AND
						(int)$current_exception['punch_id'] == (int)$existing_exception['punch_id']
					) {
					//Debug::text('Removing current exception that matches existing exception: '. $current_key, __FILE__, __LINE__, __METHOD__, 10);
					unset($new_exceptions[$current_key]);
				} //else { //Debug::text('NOT Removing current exception that matches existing exception: Current: '. $current_key .' Existing: '. $existing_key, __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		//Mark any existing exceptions that are not in the current exception list for deletion.
		$delete_exceptions = array();
		$matched_key = array();

		$delete_exception = FALSE;
		$total_current_exceptions = count($current_exceptions);
		foreach( $existing_exceptions as $existing_key => $existing_exception ) {
			$match_count = $total_current_exceptions;

			foreach( $current_exceptions as $current_key => $current_exception ) {
				if ( !isset($matched_key[$current_key]) AND ( $current_exception['exception_policy_id'] == $existing_exception['exception_policy_id'] AND $current_exception['type_id'] == $existing_exception['type_id'] AND $current_exception['user_id'] == $existing_exception['user_id'] AND $current_exception['date_stamp'] == $existing_exception['date_stamp'] AND $current_exception['punch_control_id'] == $existing_exception['punch_control_id'] AND $current_exception['punch_id'] == $existing_exception['punch_id'] ) ) {
					//Make sure we don't match the same exception twice and allow duplicate exceptions to persist.
					//This fixes the issue where two S1 exceptions may have been created, but only one should really exist.
					$matched_key[$current_key] = TRUE;
				} else {
					$match_count--;
				}

				/*
				if ( !( $current_exception['exception_policy_id'] == $existing_exception['exception_policy_id'] AND $current_exception['type_id'] == $existing_exception['type_id'] AND $current_exception['user_id'] == $existing_exception['user_id'] AND $current_exception['date_stamp'] == $existing_exception['date_stamp'] AND $current_exception['punch_control_id'] == $existing_exception['punch_control_id'] AND $current_exception['punch_id'] == $existing_exception['punch_id'] ) ) {
					Debug::text('a1Determining if we should delete this exception... Match Count: '. $match_count .' Total: '. $total_current_exceptions .' Existing Key: '. $existing_key, __FILE__, __LINE__, __METHOD__, 10);
					$match_count--;
				}
				*/
				
				//Debug::text('aDetermining if we should delete this exception... Match Count: '. $match_count .' Total: '. $total_current_exceptions .' Existing Key: '. $existing_key, __FILE__, __LINE__, __METHOD__, 10);
			}

			if ( $match_count == 0 ) {
				//Debug::text('bDetermining if we should delete this exception... Match Count: '. $match_count .' Total: '. $total_current_exceptions .' Existing Key: '. $existing_key, __FILE__, __LINE__, __METHOD__, 10);
				$delete_exceptions[] = $existing_exception['id'];
			}
		}

		$retarr = array( 'create_exceptions' => $new_exceptions, 'delete_exceptions' => $delete_exceptions );
		//Debug::Arr($retarr, 'RetArr Exceptions: ', __FILE__, __LINE__, __METHOD__, 10);
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
		Debug::Arr($id, 'Unique Exception Control ID: '. $exception_policy_control_id .' Type ID: '. $type_id, __FILE__, __LINE__, __METHOD__, 10);

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
