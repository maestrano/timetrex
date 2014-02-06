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
 * $Revision: 1246 $
 * $Id: fix_client_balance.php 1246 2007-09-14 23:47:42Z ipso $
 * $Date: 2007-09-14 16:47:42 -0700 (Fri, 14 Sep 2007) $
 */
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

ignore_user_abort(TRUE);
ini_set( 'max_execution_time', 0 );
ini_set( 'memory_limit', '1024M' ); //Just in case.

if ( $argc < 1 OR in_array ($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: unattended_upgrade.php\n";
	$help_output .= " [-f] = Force upgrade even if INSTALL mode is disabled.\n";
	echo $help_output;
} else {
	$last_arg = count($argv)-1;

	if ( in_array('-f', $argv) ) {
		$force = TRUE;
	} else {
		$force = FALSE;
	}

	if ( $force == TRUE ) {
		echo "Force Mode enabled...\n";
		//Force installer_enabled to TRUE so we don't have to manually modify the config file with scripts.
		$config_vars['other']['installer_enabled'] = TRUE;
	}

	//Re-initialize install object with new config file.
	$install_obj = new Install();
	$install_obj->cleanCacheDirectory();
	if ( $install_obj->isInstallMode() == FALSE ) {
		echo "ERROR: Install mode is not enabled in the timetrex.ini.php file!\n";
		exit(1);
	} else {
		$check_all_requirements = $install_obj->checkAllRequirements( TRUE );
		if ( $check_all_requirements == 0 AND $install_obj->checkTimeTrexVersion() == 0 ) {

			$install_obj->setDatabaseConnection( $db ); //Default connection

			//Make sure at least one company exists in the database, this only works for upgrades, not initial installs.
			if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == TRUE ) {
				if ( $install_obj->checkTableExists( 'company' ) == TRUE ) {
					//Table could be created, but check to make sure a company actually exists too.
					$clf = TTnew( 'CompanyListFactory' );
					$clf->getAll();
					if ( $clf->getRecordCount() >= 1 ) {
						$install_obj->setIsUpgrade( TRUE );
					} else {
						//No company exists, send them to the create company page.
						$install_obj->setIsUpgrade( FALSE );
					}
				} else {
					$install_obj->setIsUpgrade( FALSE );
				}
			}

			if ( $install_obj->getIsUpgrade() == TRUE ) {
				if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == TRUE ) {
					//Create SQL, always try to install every schema version, as
					//installSchema() will check if its already been installed or not.
					$install_obj->setDatabaseDriver( $config_vars['database']['type'] );
					$install_obj->createSchemaRange( NULL, NULL ); //All schema versions
					$install_obj->setVersions();

					//Clear all cache.
					$install_obj->cleanCacheDirectory();
					$cache->clean();

					$data['installer_enabled'] = 'FALSE';
					$install_obj->writeConfigFile( $data );

					echo "Upgrade successfull!\n";
					//Debug::Display();
					exit(0);
				} else {
					Debug::Text('ERROR: Database does not exist.', __FILE__, __LINE__, __METHOD__,10);
					echo "ERROR: Database does not exists!\n";
				}
			} else {
				echo "ERROR: No company exists for upgrading!\n";
			}
		} else {
			echo "ERROR: System requirements are not satisfied, or a new version exists!\n";
		}
	}
}
Debug::Display();
exit(1);
?>
