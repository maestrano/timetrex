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
 * $Revision: 8371 $
 * $Id: InstallSchema_1015A.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1015A extends InstallSchema_Base {

	protected $station_users = array();

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion() , __FILE__, __LINE__, __METHOD__,9);

		if ( strncmp($this->getDatabaseConnection()->databaseType,'postgres',8) == 0 ) {
			//Mark old stations that weren't explicitly made, or used since Jan 01/2007 as deleted.
			$query = 'update station set deleted = 1 where id in ( select a.id from station as a LEFT JOIN station_user as b ON a.id = b.station_id WHERE b.station_id IS NULL AND (a.allowed_date is NULL OR a.allowed_date < 1167609600) )';
			$this->getDatabaseConnection()->Execute( $query );
		}

		//Get all station_ids and users explicitly assigned to them.
		$query = 'select a.station_id, a.user_id from station_user as a LEFT JOIN station as b ON a.station_id = b.id where b.deleted = 0 order by station_id';
		$rs = $this->getDatabaseConnection()->Execute( $query );
		if ( $rs->RecordCount() > 0 ) {
			foreach( $rs as $row ) {
				$this->station_users[$row['station_id']][] = $row['user_id'];
			}
		}

		return TRUE;
	}

	function postInstall() {
		global $cache;

		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);

		Debug::text('l: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);

		if ( is_array($this->station_users) AND count($this->station_users) > 0 ) {
			foreach( $this->station_users as $station_id => $user_ids ) {
				//Get station object.
				$slf = TTnew( 'StationListFactory' );
				$slf->getById( $station_id );
				if ( $slf->getRecordCount() > 0 ) {
					$s_obj = $slf->getCurrent();

					if ( in_array( '-1', $user_ids) ) {
						//All users allowed
						$s_obj->setGroupSelectionType( 10 );
						$s_obj->setBranchSelectionType( 10 );
						$s_obj->setDepartmentSelectionType( 10 );
					} else {
						//Only specific users allowed
						$s_obj->setIncludeUser( $user_ids );
					}

					if ( $s_obj->isValid() ) {
						$s_obj->Save();
					}
				}
			}
		}
		unset($this->station_users);

		//Add currency updating to cron.
		$maint_base_path = Environment::getBasePath() . DIRECTORY_SEPARATOR .'maint'. DIRECTORY_SEPARATOR;
		if ( PHP_OS == 'WINNT' ) {
			$cron_job_base_command =  'php-win.exe '. $maint_base_path;
		} else {
			$cron_job_base_command =  'php '. $maint_base_path;
		}
		Debug::text('Cron Job Base Command: '. $cron_job_base_command, __FILE__, __LINE__, __METHOD__,9);

		$cjf = TTnew( 'CronJobFactory' );
		$cjf->setName('TimeClockSync');
		$cjf->setMinute('*');
		$cjf->setHour('*');
		$cjf->setDayOfMonth('*');
		$cjf->setMonth('*');
		$cjf->setDayOfWeek('*');
		$cjf->setCommand($cron_job_base_command.'TimeClockSync.php');
		$cjf->Save();

		return TRUE;
	}
}
?>
