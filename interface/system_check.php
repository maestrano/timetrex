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
$skip_db_error_exception = TRUE; //Skips DB error redirect
try {
	require_once('../includes/global.inc.php');
} catch(Exception $e) {
	echo 'FAIL (100) - '. $e->getMessage();
	exit;
}
//Debug::setVerbosity(11);

//Confirm database connection is up and maintenance jobs have run recently...
if ( PRODUCTION == TRUE ) {
	$cjlf = TTnew( 'CronJobListFactory' );
	$cjlf->getMostRecentlyRun();
	if ( $cjlf->getRecordCount() > 0 ) {
		$last_run_date_diff = time()-$cjlf->getCurrent()->getLastRunDate();
		if ( $last_run_date_diff > 1800 ) { //Must run in the last 30mins.
			echo 'FAIL! (200)';
			exit;
		}
	}
}

//If caching is enabled, make sure cache directory exists and is writeable.
if ( isset($config_vars['cache']['enable']) AND $config_vars['cache']['enable'] == TRUE ) {
	if ( isset($config_vars['cache']['redis_host']) AND $config_vars['cache']['redis_host'] != '' ) {
		$tmp_f = TTnew('SystemSettingFactory');
		$random_value = sha1( time() );
		$tmp_f->saveCache( $random_value, 'system_check' );
		$result = $tmp_f->getCache( 'system_check' );
		if ( $random_value != $result ) {
			echo 'FAIL! (320)';
			exit;
		}
		$tmp_f->removeCache('system_check');
	} elseif ( file_exists($config_vars['cache']['dir']) == FALSE ) {
		echo 'FAIL! (300)';
		exit;
	} else {
		if ( is_writeable( $config_vars['cache']['dir'] ) == FALSE ) {
			echo 'FAIL (310)';
			exit;
		}
	}
}

//Everything is good.
echo 'OK';
?>