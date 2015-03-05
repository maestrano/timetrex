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
 * @package Modules\Cron
 */
class CronJobFactory extends Factory {
	protected $table = 'cron';
	protected $pk_sequence_name = 'cron_id_seq'; //PK Sequence name

	protected $temp_time = NULL;
	protected $execute_flag = FALSE;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'limit':
				$retval = array(
							'minute' => array('min' => 0, 'max' => 59 ),
							'hour' => array('min' => 0, 'max' => 23 ),
							'day_of_month' => array('min' => 1, 'max' => 31 ),
							'month' => array('min' => 1, 'max' => 12 ),
							'day_of_week' => array('min' => 0, 'max' => 7 ),
							);
				break;
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('READY'),
										20 => TTi18n::gettext('RUNNING'),
									);
				break;

		}

		return $retval;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return TRUE;
		}

		return FALSE;
	}

	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name) {
		$name = trim($name);

		if (	$this->Validator->isLength(	'name',
											$name,
											TTi18n::gettext('Name is invalid'),
											1, 250)
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function isValidLimit( $value_arr, $limit_arr ) {
		if ( is_array($value_arr) AND is_array($limit_arr) ) {
			foreach($value_arr as $value ) {
				if ( $value == '*' ) {
					$retval = TRUE;
				}

				if ( $value >= $limit_arr['min'] AND $value <= $limit_arr['max'] ) {
					$retval = TRUE;
				} else {
					return FALSE;
				}
			}

			return $retval;
		}

		return FALSE;
	}

	function getMinute() {
		if ( isset($this->data['minute']) ) {
			return $this->data['minute'];
		}

		return FALSE;
	}
	function setMinute($value) {
		$value = trim($value);

		if (	$this->Validator->isLength(	'minute',
											$value,
											TTi18n::gettext('Minute is invalid'),
											1, 250)
						) {

			$this->data['minute'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getHour() {
		if ( isset($this->data['hour']) ) {
			return $this->data['hour'];
		}

		return FALSE;
	}
	function setHour($value) {
		$value = trim($value);

		if (	$this->Validator->isLength(	'hour',
											$value,
											TTi18n::gettext('Hour is invalid'),
											1, 250)
						) {

			$this->data['hour'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDayOfMonth() {
		if ( isset($this->data['day_of_month']) ) {
			return $this->data['day_of_month'];
		}

		return FALSE;
	}
	function setDayOfMonth($value) {
		$value = trim($value);

		if (	$this->Validator->isLength(	'day_of_month',
											$value,
											TTi18n::gettext('Day of Month is invalid'),
											1, 250)
						) {

			$this->data['day_of_month'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getMonth() {
		if ( isset($this->data['month']) ) {
			return $this->data['month'];
		}

		return FALSE;
	}
	function setMonth($value) {
		$value = trim($value);

		if (	$this->Validator->isLength(	'month',
											$value,
											TTi18n::gettext('Month is invalid'),
											1, 250)
						) {

			$this->data['month'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDayOfWeek() {
		if ( isset($this->data['day_of_week']) ) {
			return $this->data['day_of_week'];
		}

		return FALSE;
	}
	function setDayOfWeek($value) {
		$value = trim($value);

		if (	$this->Validator->isLength(	'day_of_week',
											$value,
											TTi18n::gettext('Day of Week is invalid'),
											1, 250)
						) {

			$this->data['day_of_week'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getCommand() {
		if ( isset($this->data['command']) ) {
			return $this->data['command'];
		}

		return FALSE;
	}
	function setCommand($value) {
		$value = trim($value);

		if (	$this->Validator->isLength(	'command',
											$value,
											TTi18n::gettext('Command is invalid'),
											1, 250)
						) {

			$this->data['command'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLastRunDate( $raw = FALSE ) {
		if ( isset($this->data['last_run_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['last_run_date'];
			} else {
				return TTDate::strtotime( $this->data['last_run_date'] );
			}
		}

		return FALSE;
	}
	function setLastRunDate($epoch) {
		$epoch = trim($epoch);

		if	(	$this->Validator->isDate(		'last_run',
												$epoch,
												TTi18n::gettext('Incorrect last run'))
			) {

			$this->data['last_run_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	private function setTempTime( $epoch ) {
		$this->temp_time = $epoch;
	}

	private function getTempTime() {
		return $this->temp_time;
	}

	private function setExecuteFlag( $bool ) {
		$this->execute_flag = (bool)$bool;
	}

	private function getExecuteFlag() {
		return $this->execute_flag;
	}

	function isSystemLoadValid() {
		return Misc::isSystemLoadValid();
	}

	//Check if job is scheduled to run right NOW.
	//If the job has missed a run, it will run immediately.
	function isScheduledToRun( $epoch = NULL, $last_run_date = NULL ) {
		//Debug::text('Checking if Cron Job is scheduled to run: '. $this->getName(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $epoch == '' ) {
			$epoch = time();
		}

		//Debug::text('Checking if Cron Job is scheduled to run: '. $this->getName(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $last_run_date == '' ) {
			$last_run_date = (int)$this->getLastRunDate();
		}

		Debug::text(' Name: '. $this->getName() .' Current Epoch: '. TTDate::getDate('DATE+TIME', $epoch) .' Last Run Date: '. TTDate::getDate('DATE+TIME', $last_run_date), __FILE__, __LINE__, __METHOD__, 10);
		return Cron::isScheduledToRun( $this->getMinute(), $this->getHour(), $this->getDayOfMonth(), $this->getMonth(), $this->getDayOfWeek(), $epoch, $last_run_date );
	}

	//Executes the CronJob
	function Execute( $php_cli = NULL, $dir = NULL ) {
		global $config_vars;
		$lock_file = new LockFile( $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . $this->getName().'.lock' );

		//Check job last updated date, if its more then 12hrs and its still in the "running" status,
		//chances are its an orphan. Change status.
		//if ( $this->getStatus() != 10 AND $this->getLastRunDate() < time()-(12*3600) ) {
		if ( $this->getStatus() != 10 AND $this->getUpdatedDate() > 0 AND $this->getUpdatedDate() < (time() - ( 6 * 3600 )) ) {
			Debug::text('ERROR: Job has been running for more then 6 hours! Asssuming its an orphan, marking as ready for next run.', __FILE__, __LINE__, __METHOD__, 10);
			$this->setStatus(10);
			$this->Save(FALSE);

			$lock_file->delete();
		}

		if ( !is_executable( $php_cli ) ) {
			Debug::text('ERROR: PHP CLI is not executable: '. $php_cli, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( $this->isSystemLoadValid() == FALSE ) {
			Debug::text('System load is too high, skipping...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Cron script to execute
		$script = $dir . DIRECTORY_SEPARATOR . $this->getCommand();

		if ( $this->getStatus() == 10 AND $lock_file->exists() == FALSE ) {
			$lock_file->create();

			$this->setExecuteFlag(TRUE);

			Debug::text('Job is NOT currently running, running now...', __FILE__, __LINE__, __METHOD__, 10);
			//Mark job as running
			$this->setStatus(20); //Running
			$this->Save(FALSE);

			//Even if the file does not exist, we still need to "pretend" the cron job ran (set last ran date) so we don't
			//display the big red error message saying that NO jobs have run in the last 24hrs.
			if ( file_exists( $script ) ) {
				$command = '"'. $php_cli .'" "'. $script .'"';
				//if ( OPERATING_SYSTEM == 'WIN' ) {
					//Windows requires quotes around the entire command, and each individual section with that might have spaces.
					//23-May-13: This seems to cause the command to fail now. Perhaps its related to newer versions of PHP?
					//$command = '"'. $command .'"';
				//}
				Debug::text('Command: '. $command, __FILE__, __LINE__, __METHOD__, 10);

				$start_time = microtime(TRUE);
				exec($command, $output, $retcode);
				Debug::Arr($output, 'Time: '. (microtime(TRUE) - $start_time) .'s - Command RetCode: '. $retcode .' Output: ', __FILE__, __LINE__, __METHOD__, 10);

				TTLog::addEntry( $this->getId(), 500, TTi18n::getText('Executing Cron Job').': '. $this->getID() .' '.	TTi18n::getText('Command').': '. $command .' '.	 TTi18n::getText('Return Code').': '. $retcode, NULL, $this->getTable() );
			} else {
				Debug::text('WARNING: File does not exist, skipping: '. $script, __FILE__, __LINE__, __METHOD__, 10);
			}

			$this->setStatus(10); //Ready
			$this->setLastRunDate( TTDate::roundTime( time(), 60, 30) );
			$this->Save(FALSE);

			$this->setExecuteFlag(FALSE);

			$lock_file->delete();
			return TRUE;
		} else {
			Debug::text('Job is currently running, skipping...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	function preSave() {
		if ( $this->getStatus() == '' ) {
			$this->setStatus(10); //Ready
		}

		if ( $this->getMinute() == '' ) {
			$this->setMinute('*');
		}

		if ( $this->getHour() == '' ) {
			$this->setHour('*');
		}

		if ( $this->getDayOfMonth() == '' ) {
			$this->setDayOfMonth('*');
		}

		if ( $this->getMonth() == '' ) {
			$this->setMonth('*');
		}

		if ( $this->getDayOfWeek() == '' ) {
			$this->setDayOfWeek('*');
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

	function addLog( $log_action ) {
		if ( $this->getExecuteFlag() == FALSE ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Cron Job'), NULL, $this->getTable() );
		}

		return TRUE;
	}
}
?>
