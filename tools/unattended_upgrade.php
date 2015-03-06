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


//This must go above include for global.inc.php
if ( in_array('--config', $argv) ) {
	$_SERVER['TT_CONFIG_FILE'] = trim($argv[(array_search('--config', $argv) + 1)]);
}

//If requirements only check is enabled, do not connect to the database just in case the database isnt setup yet or setup incorrectly.
if ( in_array('--requirements_only', $argv) ) {
	$disable_database_connection = TRUE;
}
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

//Re-initialize install object with new config file.
$install_obj = new Install();

if ( isset( $config_vars['other']['primary_company_id'] ) ) {
	$company_id = $config_vars['other']['primary_company_id'];
} else {
	$company_id = 1;
}

//The installer already checks the cache directory to make sure its writable, so use that as the upgrade staging directory.
//The cache dir does get cleaned once per week though, but if an upgrade failed that may be helpful.
if ( !isset($config_vars['cache']['dir']) ) { //Just in case the cache directory is not set.
	$config_vars['cache']['dir'] = Environment::getBasePath();
}
$upgrade_staging_dir = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . 'upgrade_staging' . DIRECTORY_SEPARATOR;
$upgrade_staging_latest_dir = $upgrade_staging_dir . DIRECTORY_SEPARATOR . 'latest_version';
$upgrade_file_name = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . 'UPGRADE.ZIP';
$php_cli = $config_vars['path']['php_cli'];

function moveUpgradeFiles( $upgrade_staging_latest_dir ) {
	$latest_file_list = Misc::getFileList( $upgrade_staging_latest_dir, NULL, TRUE );
	if ( is_array($latest_file_list) ) {
		foreach( $latest_file_list as $latest_file ) {
			$new_file = str_replace( $upgrade_staging_latest_dir, Environment::getBasePath(), $latest_file  );

			//Check if directory exists.
			if ( !is_dir( dirname( $new_file ) ) ) {
				Debug::Text('Creating new directory: '. dirname( $new_file ), __FILE__, __LINE__, __METHOD__, 10);
				if ( @mkdir( dirname( $new_file ), 0755, TRUE ) == FALSE ) { //Read+Write+Execute for owner, Read/Execute for all others.
					Debug::Text('ERROR: FAILED TO CREATE DIRECTORY: '. $new_file, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
			Debug::Text('Moving: '. $latest_file .' To: '. $new_file, __FILE__, __LINE__, __METHOD__, 10);
			if ( @rename( $latest_file, $new_file ) == FALSE ) {
				Debug::Text('ERROR: FAILED TO MOVE: '. $latest_file .' To: '. $new_file, __FILE__, __LINE__, __METHOD__, 10);
			}
		}
	}

	return TRUE;
}

function setAutoUpgradeFailed( $value = 1 ) {
	$sslf = new SystemSettingListFactory();
	$sslf->getByName('auto_upgrade_failed');
	if ( $sslf->getRecordCount() == 1 ) {
		$obj = $sslf->getCurrent();
	} else {
		$obj = new SystemSettingListFactory();
	}
	$obj->setName( 'auto_upgrade_failed' );
	$obj->setValue( $value );
	if ( $obj->isValid() ) {
		$obj->Save();
	}

	if ( $value == 1 ) {
		Debug::Text('ERROR: AutoUpgrade Failed, setting failed flag...', __FILE__, __LINE__, __METHOD__, 10);
	} else {
		Debug::Text('AutoUpgrade Success, clearing failed flag...', __FILE__, __LINE__, __METHOD__, 10);
	}

	return TRUE;
}

function CLIExit( $code = 0 ) {
	Debug::Display();
	Debug::writeToLog();
	exit($code);
}

//Always enable debug logging during upgrade.
Debug::setEnable(TRUE);
Debug::setBufferOutput(TRUE);
Debug::setEnableLog(TRUE);
Debug::setVerbosity(10);

ignore_user_abort(TRUE);
ini_set( 'max_execution_time', 0 );
ini_set( 'memory_limit', '1024M' ); //Just in case.

//Force flush after each output line.
ob_implicit_flush( TRUE );
ob_end_flush();

if ( isset($argv[1]) AND in_array($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: unattended_upgrade.php\n";
	$help_output .= " [--config] = Config file to use.\n";
	$help_output .= " [--schema_only] = Run a schema upgrade only.\n";
	$help_output .= " [--requirements_only] = Run a system requirements check only.\n";
	$help_output .= " [-f] = Force upgrade even if INSTALL mode is disabled.\n";
	echo $help_output;
} else {
	$last_arg = ( count($argv) - 1 );

	if ( in_array('--requirements_only', $argv) ) {
		Debug::Text('Checking requirements only...', __FILE__, __LINE__, __METHOD__, 10);
		$exclude_requirements = array('php_cli_requirements', 'base_url', 'clean_cache');
		if ( in_array('--web_installer', $argv) ) {
			Debug::Text('  Launched from web installer...', __FILE__, __LINE__, __METHOD__, 10);
			//When run from the web_installer most requirements are already checked, so exclude the slow ones.
			$exclude_requirements[] = 'file_checksums';
			$exclude_requirements[] = 'file_permissions';
			$exclude_requirements[] = 'clean_cache';
		}

		$install_obj->cleanCacheDirectory();
		if ( $install_obj->checkAllRequirements( FALSE, $exclude_requirements ) == 0 ) {
			echo 'Requirements all pass successfully!'."\n";
			CLIExit(0);
			//CLIExit(1); //Test failed system requirement check...
		} else {
			echo 'Failed Requirements: '. implode(',', $install_obj->getFailedRequirements( FALSE, $exclude_requirements ) )."\n";
			CLIExit(1);
		}
		unset($exclude_requirements);
	}

	if ( in_array('-f', $argv) ) {
		$force = TRUE;
	} else {
		$force = FALSE;
	}

	//Full force mode, forces upgrade even if the file downloaded is the same version.
	//Primarily should be used when UPGRADE.ZIP already exists.
	if ( in_array('-ff', $argv) ) {
		$force = TRUE;
		$full_force = TRUE;
	} else {
		$full_force = FALSE;
	}

	if ( $force == TRUE ) {
		echo "Force Mode enabled...\n";
		//Force installer_enabled to TRUE so we don't have to manually modify the config file with scripts.
		$config_vars['other']['installer_enabled'] = TRUE;
	}
	$install_obj = new Install(); //Re-initialize install object with new config options set above. (force)

	
	if ( in_array('--schema_only', $argv) ) {
		if ( $install_obj->isInstallMode() == FALSE ) {
			echo "ERROR: Install mode is not enabled in the timetrex.ini.php file!\n";
			CLIExit(1);
		} else {
			$install_obj->cleanCacheDirectory();
			if ( $install_obj->checkAllRequirements( TRUE ) == 0 ) {
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

						echo "Upgrade successfull!\n";
						CLIExit(0);
					} else {
						Debug::Text('ERROR: Database does not exist.', __FILE__, __LINE__, __METHOD__, 10);
						echo "ERROR: Database does not exists!\n";
					}
				} else {
					echo "ERROR: No company exists for upgrading!\n";
				}
			} else {
				echo "ERROR: System requirements are not satisfied, or a new version exists!\n";
			}
		}
		CLIExit(1);
	}

	//Upgrade Stage2
	if ( in_array('--stage2', $argv) ) {
		/*
		 Steps to do full upgrade:
		- Check if new version is available, send FORCE flag to help update some clients sooner if required.
		- If new version exists:
			- Enable logging (in memory), don't modify config file.
			- Check existing system requirements/checksums to make sure no files have been changed and system requirements are still met.
				This should also check permissions to make sure the files are all writable by the user who is running the script.
			- Download new version .ZIP file, extract to 'upgrade_staging' directory.
			- Run system requirement check for new version in staging directory, to make sure we can upgrade to that version.
			- (?)Force a database backup if possible.
			- Copy main directory to 'upgrade_rollback' directory.
			- Move staging directory over top of main directory
			- Run schema upgrade.
			- Done.
		*/

		Debug::Text('AutoUpgrade Stage2... Version: '. APPLICATION_VERSION, __FILE__, __LINE__, __METHOD__, 10);
		if ( PRODUCTION == FALSE OR DEPLOYMENT_ON_DEMAND == TRUE ) {
			echo "ERROR: Not doing full upgrade when PRODUCTION or ONDEMAND mode is disabled...\n";
			CLIExit(1);
		}

		$config_vars['other']['installer_enabled'] = TRUE;

		echo "Performing any necessary corrections from previous version...\n";
		//From v7.3.1 to 7.3.2 some files weren't getting copied if they were new in this version and created a new directory.
		//So do the copy again in stage2 just in case.
		moveUpgradeFiles( $upgrade_staging_latest_dir );

		echo "Upgrading database schema...\n";
		//Don't check file_checksums, as the script is run from the old version and therefore the checksum version match will fail everytime.
		//They should have been checked above anyways, so in theory this shouldn't matter.

		$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_stage2_requirements' ), 'pre_install.php'), 'r');
		@fclose($handle);
		if ( $install_obj->checkAllRequirements( FALSE, array('file_checksums', 'php_cli_requirements', 'base_url', 'clean_cache' ) ) == 0  ) {
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
					Debug::Text('Upgrading schema now...', __FILE__, __LINE__, __METHOD__, 10);
					//Create SQL, always try to install every schema version, as
					//installSchema() will check if its already been installed or not.
					$install_obj->setDatabaseDriver( $config_vars['database']['type'] );
					$install_obj->createSchemaRange( NULL, NULL ); //All schema versions
					$install_obj->setVersions();

					Debug::Text('Upgrading database schema successful!', __FILE__, __LINE__, __METHOD__, 10);
					echo "Upgrading database schema successful!\n";
					$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_new_schema' ), 'pre_install.php'), 'r');
					@fclose($handle);

					Debug::Text('Cleaning up temporary files...', __FILE__, __LINE__, __METHOD__, 10);
					echo "Cleaning up temporary files...\n";
					//Send version data before and after upgrade.
					$ttsc = new TimeTrexSoapClient();
					$ttsc->sendCompanyData( $company_id, TRUE );
					$ttsc->sendCompanyVersionData( $company_id );

					//Attempt to update license file if necessary.
					$license = new TTLicense();
					$license->getLicenseFile( FALSE );

					//Clear all cache.
					$install_obj->cleanCacheDirectory();
					$cache->clean();

					Misc::cleanDir( $upgrade_staging_dir, TRUE, TRUE, TRUE );
					@unlink($upgrade_file_name);

					Debug::Text('Stage 2 Successfull!', __FILE__, __LINE__, __METHOD__, 10);
					echo "Stage 2 Successfull!\n";
					$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_done' ), 'pre_install.php'), 'r');
					@fclose($handle);

					//Make sure we disable the installer even if an error has occurred.
					//Since v7.3.0 had a bug where the installer never disabled, force it disabled here for at least one version just in case.
					//Even though we have switched to using the variable only, and this isn't needed anymore.
					$data['other']['installer_enabled'] = 'FALSE';
					$data['other']['default_interface'] = 'html5';
					$install_obj->writeConfigFile( $data );

					CLIExit(0);
				} else {
					Debug::Text('ERROR: Database does not exist.', __FILE__, __LINE__, __METHOD__, 10);
					echo "ERROR: Database does not exists!\n";
				}
			} else {
				Debug::Text('ERROR: No company exists for upgrading', __FILE__, __LINE__, __METHOD__, 10);
				echo "ERROR: No company exists for upgrading!\n";
			}
		} else {
			Debug::Text('ERROR: New system requirements are not satisfied!', __FILE__, __LINE__, __METHOD__, 10);
			echo "ERROR: New system requirements are not satisfied!\n";
		}

		CLIExit(1);
	}

	//Stage 1, Full upgrade, including downloading the file.
	if ( in_array('--schema_only', $argv) == FALSE AND in_array('--stage2', $argv) == FALSE ) {
		if ( PRODUCTION == FALSE OR DEPLOYMENT_ON_DEMAND == TRUE ) {
			echo "ERROR: Not doing full upgrade when PRODUCTION mode is disabled, or in ONDEMAND mode...\n";
			CLIExit(1);
		}

		Debug::Text('New version available, check current system requirements...', __FILE__, __LINE__, __METHOD__, 10);
		if ( disk_free_space( Environment::getBasePath() ) < (1000 * 1024000) ) {  //1000MB
			Debug::Text('Disk space available: '. disk_free_space( Environment::getBasePath() ), __FILE__, __LINE__, __METHOD__, 10);
			echo "Less than 1000MB of disk space available, unable to perform upgrade...\n";
			CLIExit(1);
		}

		//No need to write install file, as it just adds potential for problems if it doesn't get disabled again.
		$config_vars['other']['installer_enabled'] = TRUE;

		Debug::Text('Checking if new version is available, current version: '. APPLICATION_VERSION .' Force: '. (int)$full_force, __FILE__, __LINE__, __METHOD__, 10);

		$ttsc = new TimeTrexSoapClient();
		if ( $full_force === TRUE OR $ttsc->isNewVersionReadyForUpgrade( $force ) === TRUE ) {
			Debug::Text('New version available, or force used...', __FILE__, __LINE__, __METHOD__, 10);

			$handle = @fopen('http://www.timetrex.com/pre_install.php?v='. $install_obj->getFullApplicationVersion() .'&os='. PHP_OS .'&php_version='. PHP_VERSION .'&web_server='. urlencode( substr( $_SERVER['SERVER_SOFTWARE'], 0, 20 ) ) .'&page=unattended_upgrade', 'r');
			@fclose($handle);

			$install_obj->cleanCacheDirectory();
			if ( $install_obj->checkAllRequirements( FALSE, array('php_cli_requirements', 'base_url', 'clean_cache') ) == 0 ) {
				Debug::Text('New version available, attempting to download...', __FILE__, __LINE__, __METHOD__, 10);
				echo "New version available, attempting to download...\n";
				sleep(5); //Sleep for 5 seconds so it can be cancelled easy if needed.

				//Send version data before and after upgrade.
				$ttsc->sendCompanyData( $company_id, TRUE );
				$ttsc->sendCompanyUserLocationData( $company_id );
				$ttsc->sendCompanyUserCountData( $company_id );
				$ttsc->sendCompanyVersionData( $company_id );

				for( $i = 0; $i < 3; $i++ ) {
					$file_url = $ttsc->getUpgradeFileURL( $force );
					Debug::Arr($file_url, 'File Upgrade URL: ', __FILE__, __LINE__, __METHOD__, 10);

					if ( !is_soap_fault($file_url) AND $file_url !== FALSE AND $file_url != '' ) {
						Debug::Text('Got File Upgrade URL, breaking retry loop...'. $i, __FILE__, __LINE__, __METHOD__, 10);
						break;
					} else {
						echo "  Unable to obtain File Upgrade URL, retrying in 2 minutes: ". $i ."\n";
						Debug::Text('Unable to obtain File Upgrade URL, retrying: '. $i, __FILE__, __LINE__, __METHOD__, 10);
						sleep(120);
					}
				}

				if ( file_exists( $upgrade_file_name ) OR ( !is_soap_fault($file_url) AND $file_url !== FALSE AND $file_url != '' ) ) {
					$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_download' ), 'pre_install.php'), 'r');
					@fclose($handle);
					if ( file_exists( $upgrade_file_name ) == FALSE OR filesize( $upgrade_file_name ) <= 0 ) {
						$bytes_downloaded = @file_put_contents( $upgrade_file_name, fopen( $file_url, 'r') );
						Debug::Text('Downloaded file: '. $upgrade_file_name .' Size: '. @filesize( $upgrade_file_name ) .' Bytes downloaded: '. $bytes_downloaded, __FILE__, __LINE__, __METHOD__, 10);
						if ( $bytes_downloaded <= 0 OR @filesize( $upgrade_file_name ) <= 0 ) {
							Debug::Text('ERROR: File did not download correctly...', __FILE__, __LINE__, __METHOD__, 10);
							echo 'ERROR: File did not download correctly...'."\n";
						} else {
							echo 'Downloaded file: '. $upgrade_file_name .' Size: '. filesize( $upgrade_file_name ) ."\n";
						}
					} else {
						Debug::Text('Upgrade file already exists... Current Size: '. filesize( $upgrade_file_name ), __FILE__, __LINE__, __METHOD__, 10);
						echo "Upgrade file already exists...\n";
					}

					if ( file_exists( $upgrade_file_name ) AND filesize( $upgrade_file_name ) > 0 ) {
						Debug::Text('Cleaning staging directory: '. $upgrade_staging_dir, __FILE__, __LINE__, __METHOD__, 10);
						echo 'Cleaning staging directory: '. $upgrade_staging_dir ."\n";
						Misc::cleanDir( $upgrade_staging_dir, TRUE, TRUE, TRUE );

						Debug::Text('Unzipping UPGRADE.ZIP', __FILE__, __LINE__, __METHOD__, 10);
						echo "Unzipping UPGRADE.ZIP\n";
						$zip = new ZipArchive;
						$zip_result = $zip->open( $upgrade_file_name );
						if ( $zip_result === TRUE ) {
							$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_unzip' ), 'pre_install.php'), 'r');
							@fclose($handle);

							$zip->extractTo( $upgrade_staging_dir );
							$zip->close();
							Debug::Text('Unzipping UPGRADE.ZIP done...', __FILE__, __LINE__, __METHOD__, 10);
							echo "Unzipping UPGRADE.ZIP done...\n";
						} else {
							Debug::Text('ERROR: Unzipping UPGRADE.ZIP failed...', __FILE__, __LINE__, __METHOD__, 10);
							echo "ERROR: Unzipping UPGRADE.ZIP failed...\n";
						}
						unset($zip_result, $zip);

						//Rename whatever directory that is in the staging dir to
						if ( file_exists($upgrade_staging_dir) ) {
							if ($handle = opendir($upgrade_staging_dir) ) {
								while ( ( $entry = readdir($handle) ) !== FALSE ) {
									if ( $entry != '.' AND $entry != '..' ) {
										$upgrade_staging_extract_dir = $upgrade_staging_dir . DIRECTORY_SEPARATOR . $entry;
										break;
									}
								}
								closedir($handle);
							}

							if ( isset($upgrade_staging_extract_dir) ) {
								$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_rename_dir' ), 'pre_install.php'), 'r');
								@fclose($handle);

								Debug::Text('Upgrade Staging Extract Dir: '. $upgrade_staging_extract_dir .' Renaming to: '. $upgrade_staging_latest_dir, __FILE__, __LINE__, __METHOD__, 10);
								if ( @rename( $upgrade_staging_extract_dir, $upgrade_staging_latest_dir ) == FALSE ) {
									Debug::Text('ERROR: Unable to rename: '. $upgrade_staging_extract_dir .' to: '. $upgrade_staging_latest_dir, __FILE__, __LINE__, __METHOD__, 10);
									echo 'ERROR: Unable to rename: '. $upgrade_staging_extract_dir .' to: '. $upgrade_staging_latest_dir ."\n";
								}
							} else {
								Debug::Text('ERROR: UPGRADE.ZIP extract directory does not exist...', __FILE__, __LINE__, __METHOD__, 10);
							}
							unset($handle, $entry, $upgrade_staging_extract_dir );
						} else {
							Debug::Text('ERROR: Upgrade staging directory does not exist, cannot continue...', __FILE__, __LINE__, __METHOD__, 10);
							echo "ERROR: Upgrade staging directory does not exist, cannot continue...\n";
						}

						if ( isset($upgrade_staging_latest_dir) ) {
							//Check system requirements of new version.
							$latest_unattended_upgrade_tool = $upgrade_staging_latest_dir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'unattended_upgrade.php';
							if ( file_exists( $latest_unattended_upgrade_tool ) ) {
								if ( is_executable( $php_cli ) ) {
									$command = '"'.$php_cli .'" "'. $latest_unattended_upgrade_tool .'" --config "'. CONFIG_FILE .'" --requirements_only'; //Make each part is quoted in case there are spaces in the paths.
									system( $command, $exit_code );
									Debug::Text('Checking new version system requirements... Command: '. $command .' Exit Code: '. $exit_code, __FILE__, __LINE__, __METHOD__, 10);
									if ( $exit_code == 0 ) {
										Debug::Text('New version system requirements met...', __FILE__, __LINE__, __METHOD__, 10);
										$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_new_requirements' ), 'pre_install.php'), 'r');
										@fclose($handle);

										moveUpgradeFiles( $upgrade_staging_latest_dir );

										$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_launch_stage2' ), 'pre_install.php'), 'r');
										@fclose($handle);

										//Run separate process to finish stage2 of installer so it can be run with the new scripts.
										//This allows us more flexibility if an error occurs to finish the install or have the latest version correct problems.
										echo "Launching Stage 2...\n";
										sleep(5);
										$command = $php_cli .' '. __FILE__ .' --config '. CONFIG_FILE .' --stage2';
										system( $command, $exit_code );
										if ( $exit_code == 0 ) {
											Debug::Text('Stage2 success!', __FILE__, __LINE__, __METHOD__, 10);

											echo "Upgrade successfull!\n";
											
											setAutoUpgradeFailed( 0 ); //Clear auto_upgrade_failed setting if it isn't already.
											CLIExit(0);
										} else {
											Debug::Text('Stage2 failed... Exit Code: '. $exit_code, __FILE__, __LINE__, __METHOD__, 10);
											setAutoUpgradeFailed();	
										}
									} else {
										Debug::Text('ERROR: New version system requirements not met...', __FILE__, __LINE__, __METHOD__, 10);
										echo "ERROR: New version system requirements not met...\n";
										setAutoUpgradeFailed();
									}
								} else {
									Debug::text('ERROR: PHP CLI is not executable: '. $php_cli, __FILE__, __LINE__, __METHOD__, 10);
									echo "ERROR: PHP CLI is not executable: ". $php_cli ."\n";
									setAutoUpgradeFailed();
								}
							} else {
								Debug::Text('ERROR: UNATTENDED UPGRADE tool in new version does not exist: '. $latest_unattended_upgrade_tool, __FILE__, __LINE__, __METHOD__, 10);
								echo "ERROR: UNATTENDED UPGRADE tool in new version does not exist: ". $latest_unattended_upgrade_tool ."\n";
								setAutoUpgradeFailed();
							}
						} else {
							Debug::Text('ERROR: Upgrade staging latest directory does not exist, cannot continue...', __FILE__, __LINE__, __METHOD__, 10);
							echo "ERROR: Upgrade staging latest directory does not exist, cannot continue...\n";
							setAutoUpgradeFailed();
						}
					} else {
						Debug::Text('ERROR: UPGRADE.ZIP does not exist or is 0 bytes...', __FILE__, __LINE__, __METHOD__, 10);
						echo "ERROR: UPGRADE.ZIP does not exist or is 0 bytes...\n";
						setAutoUpgradeFailed();
					}
				} else {
					Debug::Text('Upgrade File URL not available...', __FILE__, __LINE__, __METHOD__, 10);
					echo "ERROR: Unable to download upgrade file at this time, please try again later...\n";
					setAutoUpgradeFailed();
				}
			} else {
				Debug::Text('ERROR: Current system requirements check failed...', __FILE__, __LINE__, __METHOD__, 10);
				echo "ERROR: Current system requirements check failed...\n";
				echo '  Failed Requirements: '. implode(',', $install_obj->getFailedRequirements( FALSE, array('php_cli_requirements', 'base_url', 'clean_cache') ) )."\n";
				setAutoUpgradeFailed();
			}
		} else {
			echo "Already running latest version: ". APPLICATION_VERSION ."\n";
			setAutoUpgradeFailed( 0 ); //Clear auto_upgrade_failed setting if it isn't already.
		}
		CLIExit(1);
	}
}
CLIExit(1);
?>
