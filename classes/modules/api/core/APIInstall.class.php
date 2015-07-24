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
 * @package API\Core
 */
class APIInstall extends APIFactory {
	protected $main_class = 'Install';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	function getLicense() {
		$install_obj = new Install();

		if (  $install_obj->isInstallMode() == TRUE  ) {
			$retval['install_mode'] = TRUE;
			$license_text = $install_obj->getLicenseText();

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'license' ), 'pre_install.php'), "r");
			@fclose($handle);

			if ( $license_text != FALSE ) {
				$retval['license_text'] = $license_text;
			} else {
				$retval['error_message'] = TTi18n::getText( 'NO LICENSE FILE FOUND, Your installation appears to be corrupt!' );
			}

			return $this->returnHandler( $retval );
		}

		return $this->returnHandler( FALSE );

	}

	function getRequirements( $external_installer = 0 ) {
		$install_obj = new Install();
		$retval = array();

		if ( $install_obj->isInstallMode() == TRUE ) {

			if ( DEPLOYMENT_ON_DEMAND == FALSE ) {
				$install_obj->cleanCacheDirectory( '' ); //Don't exclude .ZIP files, so if there is a corrupt one it will be redownloaded after a manual installer is run.
			}

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array_merge( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'require'), $install_obj->getFailedRequirements( FALSE, array('clean_cache', 'file_permissions','file_checksums') ) ), 'pre_install.php'), "r");
			@fclose($handle);

			//Need to handle disabling any attempt to connect to the database, do this by using GET params on the URL like: db=0, then look for that in json/api.php

			$check_all_requirements = $install_obj->checkAllRequirements();
			if ( $external_installer == 1 AND $check_all_requirements == 0 AND $install_obj->checkTimeTrexVersion() == 0 ) {
				//Using external installer and there is no missing requirements, automatically send to next page.
//				Redirect::Page( URLBuilder::getURL( array('external_installer' => $external_installer, 'action:next' => 'next' ), $_SERVER['SCRIPT_NAME']) );
				return $this->returnHandler( array( 'action' => 'next' ) );
			} else {
				$install_obj->setAMFMessageID( $this->getAMFMessageID() );
//				Return array with the text for each requirement check.
				$retval['check_all_requirements'] = $check_all_requirements;
				$retval['tt_product_edition'] = $install_obj->getTTProductEdition();
				$retval['php_os'] = PHP_OS;
				$retval['application_name'] = APPLICATION_NAME;
				$retval['config_file_loc'] = $install_obj->getConfigFile();
				$retval['php_config_file'] = $install_obj->getPHPConfigFile();
				$retval['php_include_path'] = $install_obj->getPHPIncludePath();
				$retval['timetrex_version'] = array(
					'check_timetrex_version' => $install_obj->checkTimeTrexVersion(),
					'current_timetrex_version' => $install_obj->getCurrentTimeTrexVersion(),
					'latest_timetrex_version' => $install_obj->getLatestTimeTrexVersion()
				);
				$retval['php_version'] = array(
					'php_version' => $install_obj->getPHPVersion(),
					'check_php_version' => $install_obj->checkPHPVersion()
				);

				$retval['database_engine'] = $install_obj->checkDatabaseType();
				$retval['bcmath'] = $install_obj->checkBCMATH();
				$retval['mbstring'] = $install_obj->checkMBSTRING();
				$retval['gettext'] = $install_obj->checkGETTEXT();
				$retval['soap'] = $install_obj->checkSOAP();
				$retval['gd'] = $install_obj->checkGD();
				$retval['json'] = $install_obj->checkJSON();
				$retval['mcrypt'] = $install_obj->checkMCRYPT();
				$retval['simplexml'] = $install_obj->checkSimpleXML();
				$retval['zip'] = $install_obj->checkZIP();
				$retval['mail'] = $install_obj->checkMAIL();
				$retval['pear'] = $install_obj->checkPEAR();
				$retval['safe_mode'] = $install_obj->checkPHPSafeMode();
				$retval['magic_quotes'] = $install_obj->checkPHPMagicQuotesGPC();
				$retval['disk_space'] = $install_obj->checkDiskSpace();
				$retval['memory_limit'] = array(
					'check_php_memory_limit' => $install_obj->checkPHPMemoryLimit(),
					'memory_limit' => $install_obj->getMemoryLimit()
				);
				$retval['base_url'] = array(
					'check_base_url' => $install_obj->checkBaseURL(),
					'recommended_base_url' => $install_obj->getRecommendedBaseURL()
				);
				$retval['base_dir'] = array(
					'check_php_open_base_dir' => $install_obj->checkPHPOpenBaseDir(),
					'php_open_base_dir' => $install_obj->getPHPOpenBaseDir(),
					'php_cli_directory' => $install_obj->getPHPCLIDirectory()
				);
				$retval['cli_executable'] = array(
					'check_php_cli_binary' => $install_obj->checkPHPCLIBinary(),
					'php_cli' => $install_obj->getPHPCLI()
				);
				$retval['cli_requirements'] = array(
					'check_php_cli_requirements' => $install_obj->checkPHPCLIRequirements(),
					'php_cli_requirements_command' => $install_obj->getPHPCLIRequirementsCommand()
				);
				$retval['config_file'] = $install_obj->checkWritableConfigFile();
				$retval['cache_dir'] = array(
					'check_writable_cache_directory' => $install_obj->checkWritableCacheDirectory(),
					'cache_dir' => $install_obj->config_vars['cache']['dir']
				);
				$retval['storage_dir'] = array(
					'check_writable_storage_directory' => $install_obj->checkWritableStorageDirectory(),
					'storage_path' => $install_obj->config_vars['path']['storage']
				);
				$retval['log_dir'] = array(
					'check_writable_log_directory' => $install_obj->checkWritableLogDirectory(),
					'log_path' => $install_obj->config_vars['path']['log']
				);
				$retval['empty_cache_dir'] = array(
					'check_clean_cache_directory' => $install_obj->checkCleanCacheDirectory(),
					'cache_dir' => $install_obj->config_vars['cache']['dir']
				);
				$retval['file_permission'] = $install_obj->checkFilePermissions();
				$retval['file_checksums'] = $install_obj->checkFileChecksums();

				$extended_error_messages = $install_obj->getExtendedErrorMessage();

				if ( count( $extended_error_messages ) > 0 ) {
					$retval['extended_error_messages'] = $extended_error_messages;
				} else {
					$retval['extended_error_messages'] = array();
				}

				return $this->returnHandler( $retval );

			}


		}

		return $this->returnHandler( FALSE );
	}

	function testConnection( $data ) {
		$install_obj = new Install();

		if ( $install_obj->isInstallMode() == TRUE ) {
			//Convert enterprisedb type to postgresql8
			if ( isset($data['type']) AND $data['type'] == 'enterprisedb' ) {
				$data['final_type'] = 'postgres8';

				//Check to see if a port was specified or not, if not, default to: 5444
				if ( strpos($data['host'], ':') === FALSE ) {
					$data['final_host'] = $data['host'].':5444';
				} else {
					$data['final_host'] = $data['host'];
				}
			} else {
				if ( isset($data['type']) ) {
					$data['final_type'] = $data['type'];
				}
				if ( isset($data['host']) ) {
					$data['final_host'] = $data['host'];
				}
			}

			//In case load balancing is used, parse out just the first host.
			$host_arr = Misc::parseDatabaseHostString( $data['final_host'] );
			$host = $host_arr[0][0];

			$database_engine = TRUE;
			//Test regular user
			//This used to connect to the template1 database, but it seems newer versions of PostgreSQL
			//default to disallow connect privs.
			$test_connection = $install_obj->setNewDatabaseConnection($data['final_type'], $host, $data['user'], $data['password'], $data['database_name']);
			if ( $test_connection == TRUE ) {
				$database_exists = $install_obj->checkDatabaseExists($data['database_name']);
			}

			//Test priv user.
			if ( $data['priv_user'] != '' AND $data['priv_password'] != '' ) {
				Debug::Text('Testing connection as priv user', __FILE__, __LINE__, __METHOD__, 10);
				$test_priv_connection = $install_obj->setNewDatabaseConnection($data['final_type'], $host, $data['priv_user'], $data['priv_password'], '');
			} else {
				$test_priv_connection = TRUE;
			}

			$data['test_connection'] = $test_connection;
			$data['test_priv_connection'] = $test_priv_connection;
			$data['database_engine'] = $database_engine;

			$data['type_options'] = $install_obj->getDatabaseTypeArray();
			$data['application_name'] = APPLICATION_NAME;

			if ( !isset($data['priv_user']) ) {
				$data['priv_user'] = NULL;
			}

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'database_config', 'priv_user' => $data['priv_user']), 'pre_install.php'), "r");
			@fclose($handle);

			return $this->returnHandler( $data );
		}

		return $this->returnHandler( FALSE );
	}

	function getDatabaseConfig() {

		global $config_vars;

		$install_obj = new Install();

		if ( $install_obj->isInstallMode() == TRUE ) {

			$database_engine = TRUE;
			$test_connection = NULL;
			$test_priv_connection = NULL;

			$retval = array(
				'type' => $config_vars['database']['type'],
				'host' => $config_vars['database']['host'],
				'database_name' => $config_vars['database']['database_name'],
				'user' => $config_vars['database']['user'],
				'password' => $config_vars['database']['password'],
				'test_connection' => $test_connection,
				'test_priv_connection' => $test_priv_connection,
				'database_engine' => $database_engine,
			);

			$retval['type_options'] = $install_obj->getDatabaseTypeArray();
			$retval['application_name'] = APPLICATION_NAME;

			if ( !isset($retval['priv_user']) ) {
				$retval['priv_user'] = NULL;
			}
			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'database_config', 'priv_user' => $retval['priv_user']), 'pre_install.php'), "r");
			@fclose($handle);

			if ( $retval != FALSE ) {
				return $this->returnHandler( $retval );
			}
		}

		return $this->returnHandler( FALSE );
	}

	function createDatabase( $data ) {
		global $config_vars;
		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {
			//Convert enterprisedb type to postgresql8
			if ( isset($data['type']) AND $data['type'] == 'enterprisedb' ) {
				$data['final_type'] = 'postgres8';

				//Check to see if a port was specified or not, if not, default to: 5444
				if ( strpos($data['host'], ':') === FALSE ) {
					$data['final_host'] = $data['host'].':5444';
				} else {
					$data['final_host'] = $data['host'];
				}
			} else {
				if ( isset($data['type']) ) {
					$data['final_type'] = $data['type'];
				}
				if ( isset($data['host']) ) {
					$data['final_host'] = $data['host'];
				}
			}

			//In case load balancing is used, parse out just the first host.
			$host_arr = Misc::parseDatabaseHostString( $data['final_host'] );
			$host = $host_arr[0][0];

			$database_engine = TRUE;
			Debug::Text('Next', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($data) AND isset($data['priv_user']) AND isset($data['priv_password'])
				AND $data['priv_user'] != '' AND $data['priv_password'] != '' ) {
				$tmp_user_name = $data['priv_user'];
				$tmp_password = $data['priv_password'];
			} elseif ( isset($data) ) {
				$tmp_user_name = $data['user'];
				$tmp_password = $data['password'];
			}

			$test_db_connection = $install_obj->setNewDatabaseConnection($data['final_type'], $host, $tmp_user_name, $tmp_password, '');
			$install_obj->setDatabaseDriver( $data['final_type'] );

			if ( $install_obj->checkDatabaseExists($data['database_name']) == FALSE ) {
				Debug::Text('Creating Database', __FILE__, __LINE__, __METHOD__, 10);
				$install_obj->createDatabase( $data['database_name'] );
			}

			//Make sure InnoDB engine exists on MySQL
			if ( $install_obj->getDatabaseType() != 'mysql' OR ( $install_obj->getDatabaseType() == 'mysql' AND $install_obj->checkDatabaseEngine() == TRUE ) ) {
				//Check again to make sure database exists.
				$db_connection = $install_obj->setNewDatabaseConnection($data['final_type'], $host, $tmp_user_name, $tmp_password, $data['database_name']);
				if ( $install_obj->checkDatabaseExists($data['database_name']) == TRUE ) {
					//Create SQL
					Debug::Text('yDatabase does exist...', __FILE__, __LINE__, __METHOD__, 10);

					$tmp_config_data['database']['type'] = $data['final_type'];
					$tmp_config_data['database']['host'] = $data['final_host'];
					$tmp_config_data['database']['database_name'] = $data['database_name'];
					$tmp_config_data['database']['user'] = $data['user'];
					$tmp_config_data['database']['password'] = $data['password'];

					$install_obj->writeConfigFile( $tmp_config_data );

					return $this->returnHandler( array( 'next_page' => 'databaseSchema' ) );

				} else {
					Debug::Text('zDatabase does not exist.', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				$database_engine = FALSE;
				Debug::Text('MySQL does not support InnoDB storage engine!', __FILE__, __LINE__, __METHOD__, 10);
			}

			$test_connection = NULL;
			$test_priv_connection = NULL;

			$data['test_connection'] = $test_connection;
			$data['test_priv_connection'] = $test_priv_connection;
			$data['database_engine'] = $database_engine;

			//Get DB settings from INI file.
			$data = array(
				'type' => $config_vars['database']['type'],
				'host' => $config_vars['database']['host'],
				'database_name' => $config_vars['database']['database_name'],
				'user' => $config_vars['database']['user'],
				'password' => $config_vars['database']['password'],
				'test_connection' => $test_connection,
				'test_priv_connection' => $test_priv_connection,
				'database_engine' => $database_engine,
			);

			$data['type_options'] = $install_obj->getDatabaseTypeArray();
			$data['application_name'] = APPLICATION_NAME;
			if ( !isset($data['priv_user']) ) {
				$data['priv_user'] = NULL;
			}

			return $this->returnHandler( $data );

		}

		return $this->returnHandler( FALSE );
	}

	function getDatabaseSchema() {
		global $db, $config_vars;
		$install_obj = new Install();

		if ( $install_obj->isInstallMode() == TRUE ) {
			$database_engine = TRUE;
			$install_obj->setDatabaseConnection( $db ); //Default connection

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

				$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'database_schema'), 'pre_install.php'), "r");
				@fclose($handle);

				if ( $install_obj->getIsUpgrade() == TRUE ) {
					$retval = array('upgrade' => 1);
				} else {
					$retval = array('upgrade' => 0);
				}

				return $this->returnHandler( $retval );
			}

		}

		return $this->returnHandler( FALSE );
	}

	function setDatabaseSchema( $external_installer = 0 ) {
		ignore_user_abort(TRUE);
		ini_set( 'max_execution_time', 0 );
		ini_set( 'memory_limit', '-1' ); //Just in case.

		//Always enable debug logging during upgrade.
		Debug::setEnable(TRUE);
		Debug::setBufferOutput(TRUE);
		Debug::setEnableLog(TRUE);
		Debug::setVerbosity(10);

		global $db, $config_vars;
		$install_obj = new Install();

		if ( $install_obj->isInstallMode() == TRUE ) {

			$install_obj->setAMFMessageID( $this->getAMFMessageID() );

			$database_engine = TRUE;
			$install_obj->setDatabaseConnection( $db ); //Default connection

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

			if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == TRUE ) {
				//Create SQL, always try to install every schema version, as
				//installSchema() will check if its already been installed or not.
				$install_obj->setDatabaseDriver( $config_vars['database']['type'] );
				$install_obj->createSchemaRange( NULL, NULL ); //All schema versions

				//FIXME: Notify the user of any errors.
				$install_obj->setVersions();
			} else {
				Debug::Text('bDatabase does not exist.', __FILE__, __LINE__, __METHOD__, 10);
			}

			if ( $install_obj->getIsUpgrade() == TRUE ) {
				//Make sure when using external installer that update notifications are always enabled.
				$sslf = TTnew( 'SystemSettingListFactory' );
				$sslf->getByName('update_notify');
				if ( $sslf->getRecordCount() == 1 ) {
					$obj = $sslf->getCurrent();
				} else {
					$obj = TTnew( 'SystemSettingListFactory' );
				}

				$obj->setName( 'update_notify' );
				if ( $external_installer == 1 ) {
					$obj->setValue( 1 );
				}
				if ( $obj->isValid() ) {
					$obj->Save();
				}

				$retval = array('next_page' => 'postUpgrade');

			} else {
				if ( $external_installer == 1 ) {
					$retval = array('next_page' => 'systemSettings', 'action' => 'next');
				} else {
					$retval = array('next_page' => 'systemSettings');
				}
			}

			Debug::writeToLog();
			return $this->returnHandler( $retval );
		}

		return $this->returnHandler( FALSE );
	}

	function postUpgrade() {
		global $cache;
		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {
			$retval['application_name'] = APPLICATION_NAME;
			$retval['application_version'] = APPLICATION_VERSION;

			//Check for updated license file.
			$license = new TTLicense();
			$license->getLicenseFile( TRUE ); //Download updated license file if one exists.

			$cache->clean(); //Clear all cache.

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'postupgrade'), 'pre_install.php'), "r");
			@fclose($handle);

			return $this->returnHandler( $retval );

		}

		return $this->returnHandler( FALSE );
	}

	function installDone( $upgrade ) {
		global $authentication, $cache;
//		$authentication->Logout(); //Logout during the install process.

		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {
			//Disable installer now that we're done.
			$tmp_config_data['other']['installer_enabled'] = 'FALSE';
			$tmp_config_data['other']['default_interface'] = 'html5';
			$install_obj->writeConfigFile( $tmp_config_data );

			//Reset new_version flag.
			$sslf = TTnew( 'SystemSettingListFactory' );
			$sslf->getByName('new_version');
			if ( $sslf->getRecordCount() == 1 ) {
				$obj = $sslf->getCurrent();
			} else {
				$obj = TTnew( 'SystemSettingListFactory' );
			}
			$obj->setName( 'new_version' );
			$obj->setValue( 0 );
			if ( $obj->isValid() ) {
				$obj->Save();
			}

			//Reset system requirement flag, as all requirements should have passed.
			$sslf = new SystemSettingListFactory();
			$sslf->getByName('valid_install_requirements');
			if ( $sslf->getRecordCount() == 1 ) {
				$obj = $sslf->getCurrent();
			} else {
				$obj = new SystemSettingListFactory();
			}
			$obj->setName( 'valid_install_requirements' );
			$obj->setValue( 1 );
			if ( $obj->isValid() ) {
				$obj->Save();
			}

			//Reset auto_upgrade_failed flag, as they likely just upgraded to the latest version.
			$sslf = new SystemSettingListFactory();
			$sslf->getByName('auto_upgrade_failed');
			if ( $sslf->getRecordCount() == 1 ) {
				$obj = $sslf->getCurrent();
			} else {
				$obj = new SystemSettingListFactory();
			}
			$obj->setName( 'auto_upgrade_failed' );
			$obj->setValue( 0 );
			if ( $obj->isValid() ) {
				$obj->Save();
			}

			$cache->clean(); //Clear all cache.

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'done'), 'pre_install.php'), "r");
			@fclose($handle);

			$retval['application_name'] = APPLICATION_NAME;
//			$retval['base_url'] = Environment::getBaseURL();

			if ( isset( $upgrade ) ) {

				$retval['upgrade'] = $upgrade;
			}

			return $this->returnHandler( $retval );


		}

		return $this->returnHandler( FALSE );

	}

	function setSystemSettings( $data, $external_installer = 0 ) {
		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {

			//Set salt if it isn't already.
			$tmp_config_data['other']['salt'] = md5( uniqid() );

			if ( isset($data['base_url']) AND $data['base_url'] != '' ) {
				$tmp_config_data['path']['base_url'] = $data['base_url'];
			}
			if ( isset($data['log_dir']) AND $data['log_dir'] != '' ) {
				$tmp_config_data['path']['log_dir'] = $data['log_dir'];
			}
			if ( isset($data['storage_dir']) AND $data['storage_dir'] != '' ) {
				$tmp_config_data['path']['storage_dir'] = $data['storage_dir'];
			}
			if ( isset($data['cache_dir']) AND $data['cache_dir'] != '' ) {
				$tmp_config_data['cache']['dir'] = $data['cache_dir'];
			}

			$install_obj->writeConfigFile( $tmp_config_data );

			//Write auto_update feature to system settings.
			$sslf = TTnew( 'SystemSettingListFactory' );
			$sslf->getByName('update_notify');
			if ( $sslf->getRecordCount() == 1 ) {
				$obj = $sslf->getCurrent();
			} else {
				$obj = TTnew( 'SystemSettingListFactory' );
			}

			$obj->setName( 'update_notify' );
			if ( ( isset($data['update_notify']) AND $data['update_notify'] == 1 )
					OR getTTProductEdition() > 10
					OR $external_installer == 1 ) {
				$obj->setValue( 1 );
			} else {
				$obj->setValue( 0 );
			}
			if ( $obj->isValid() ) {
				$obj->Save();
			}

			//Write anonymous_auto_update feature to system settings.
			$sslf = TTnew( 'SystemSettingListFactory' );
			$sslf->getByName('anonymous_update_notify');
			if ( $sslf->getRecordCount() == 1 ) {
				$obj = $sslf->getCurrent();
			} else {
				$obj = TTnew( 'SystemSettingListFactory' );
			}

			$obj->setName( 'anonymous_update_notify' );
			if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY AND isset($data['anonymous_update_notify']) AND $data['anonymous_update_notify'] == 1 ) {
				$obj->setValue( 1 );
			} else {
				$obj->setValue( 0 );
			}
			if ( $obj->isValid() ) {
				$obj->Save();
			}

			$ttsc = new TimeTrexSoapClient();
			$ttsc->saveRegistrationKey();

//			$handle = fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'system_setting', 'update_notify' => (int)$data['update_notify'], 'anonymous_update_notify' => (int)$data['anonymous_update_notify']), 'pre_install.php'), "r");
//			fclose($handle);

			return $this->returnHandler( TRUE );
		}

		return $this->returnHandler( FALSE );
	}

	function getSystemSettings() {
		global $config_vars;
		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {
			$retval = array(
				'host_name' => $_SERVER['HTTP_HOST'],
				'base_url' => Environment::getBaseURL(),
				'log_dir' => $config_vars['path']['log'],
				'storage_dir' => $config_vars['path']['storage'],
				'cache_dir' => $config_vars['cache']['dir'],
			);

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'system_setting'), 'pre_install.php'), "r");
			@fclose($handle);

			return $this->returnHandler( $retval );
		}

		return $this->returnHandler( FALSE );
	}

	function getCompany() {
		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE  ) {
			$cf = TTnew( 'CompanyFactory' );
			//Select box options;
			$company_data['status_options'] = $cf->getOptions('status');
			$company_data['country_options'] = $cf->getOptions('country');
			$company_data['industry_options'] = $cf->getOptions('industry');

			if (!isset($id) AND isset($company_data['id']) ) {
				$id = $company_data['id'];
			} else {
				$id = '';
			}
			$company_data['user_list_options'] = UserListFactory::getByCompanyIdArray($id);


			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'company'), 'pre_install.php'), "r");
			@fclose($handle);

			return $this->returnHandler( $company_data );
		}

		return $this->returnHandler( FALSE );
	}

	function setCompany( $company_data ) {
		if ( !is_array( $company_data ) ) {
			return $this->returnHandler( FALSE );
		}

		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {
			$cf = TTnew( 'CompanyFactory' );
			//$cf->setParent($company_data['parent']);
			$cf->setStatus( 10 );
			$cf->setProductEdition( (int)getTTProductEdition() );
			$cf->setName($company_data['name'], TRUE); //Force change.
			$cf->setShortName($company_data['short_name']);
			$cf->setIndustry($company_data['industry_id']);
			$cf->setAddress1($company_data['address1']);
			$cf->setAddress2($company_data['address2']);
			$cf->setCity($company_data['city']);
			$cf->setCountry($company_data['country']);
			$cf->setProvince($company_data['province']);
			$cf->setPostalCode($company_data['postal_code']);
			$cf->setWorkPhone($company_data['work_phone']);

			$cf->setEnableAddCurrency( TRUE );
			$cf->setEnableAddPermissionGroupPreset( TRUE );
			$cf->setEnableAddUserDefaultPreset( TRUE );
			$cf->setEnableAddStation( TRUE );
			$cf->setEnableAddPayStubEntryAccountPreset( TRUE );
			$cf->setEnableAddCompanyDeductionPreset( TRUE );
			$cf->setEnableAddRecurringHolidayPreset( TRUE );

			if ( $cf->isValid() ) {
				$company_id = $cf->Save();

				$install_obj->writeConfigFile( array( 'other' => array( 'primary_company_id' => $company_id ) ) );

				return $this->returnHandler( $company_id );

			} else {

				$validator[] = $cf->Validator->getErrorsArray();
				$validator_stats = array('total_records' => 1, 'valid_records' => 1 );
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
			}
		}

		return $this->returnHandler( FALSE );
	}

	function getUser( $company_id ) {
		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {
			if ( isset($company_id) ) {
				$user_data['company_id'] = $company_id;
			}

			$user_data['application_name'] = APPLICATION_NAME;

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'user'), 'pre_install.php'), "r");
			@fclose($handle);

			return $this->returnHandler( $user_data );
		}

		return $this->returnHandler( FALSE );
	}

	function setUser( $user_data, $external_installer = 0 ) {
		$install_obj = new Install();
		if ( $install_obj->isInstallMode() == TRUE ) {
			$uf = TTnew( 'UserFactory' );
			$uf->StartTransaction();
			$uf->setId( $uf->getNextInsertId() ); //Because password encryption requires the user_id, we need to get it first when creating a new employee.
			$uf->setCompany( $user_data['company_id'] );
			$uf->setStatus( 10 );
			$uf->setUserName($user_data['user_name']);
			if ( !empty($user_data['password']) AND $user_data['password'] == $user_data['password2'] ) {
				$uf->setPassword($user_data['password']);
			} else {
				$uf->Validator->isTrue(	'password',
					FALSE,
					TTi18n::gettext('Passwords don\'t match') );
			}

			$uf->setEmployeeNumber(1);
			$uf->setFirstName($user_data['first_name']);
			$uf->setLastName($user_data['last_name']);
			$uf->setWorkEmail($user_data['work_email']);

			if ( is_object( $uf->getCompanyObject() ) ) {
				$uf->setCountry( $uf->getCompanyObject()->getCountry() );
				$uf->setProvince( $uf->getCompanyObject()->getProvince() );
				$uf->setAddress1( $uf->getCompanyObject()->getAddress1() );
				$uf->setAddress2( $uf->getCompanyObject()->getAddress2() );
				$uf->setCity( $uf->getCompanyObject()->getCity() );
				$uf->setPostalCode( $uf->getCompanyObject()->getPostalCode() );
				$uf->setWorkPhone( $uf->getCompanyObject()->getWorkPhone() );
				$uf->setHomePhone( $uf->getCompanyObject()->getWorkPhone() );

				if ( is_object( $uf->getCompanyObject()->getUserDefaultObject() ) ) {
					$uf->setCurrency( $uf->getCompanyObject()->getUserDefaultObject()->getCurrency() );
				}
			}

			//Get Permission Control with highest level, assume its for Administrators and use it.
			$pclf = TTnew( 'PermissionControlListFactory' );
			$pclf->getByCompanyId( $user_data['company_id'], NULL, NULL, NULL, array('level' => 'desc' ) );
			if ( $pclf->getRecordCount() > 0 ) {
				$pc_obj = $pclf->getCurrent();
				if ( is_object($pc_obj) ) {
					Debug::Text('Adding User to Permission Control: '. $pc_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
					$uf->setPermissionControl( $pc_obj->getId() );
				}
			}

			if ( $uf->isValid() ) {
				$user_id = $uf->Save( TRUE, TRUE );

				//Assign this user as admin/support/billing contact for now.
				$clf = TTnew( 'CompanyListFactory' );
				$clf->getById( $user_data['company_id'] );
				if ( $clf->getRecordCount() == 1 ) {
					$c_obj = $clf->getCurrent();
					$c_obj->setAdminContact( $user_id );
					$c_obj->setBillingContact( $user_id );
					$c_obj->setSupportContact( $user_id );
					if ( $c_obj->isValid() ) {
						$c_obj->Save();
					}
					unset($c_obj, $clf);
				}

				$uf->CommitTransaction();

				if ( $external_installer == 1 ) {
					return $this->returnHandler( array( 'next_page' => 'installDone' ) );
				} else {
					return $this->returnHandler( array( 'next_page' => 'maintenanceJobs' ) );
				}

			} else {

				$uf->FailTransaction();

				$validator[] = $uf->Validator->getErrorsArray();
				$validator_stats = array('total_records' => 1, 'valid_records' => 1 );
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
			}

		}

		return $this->returnHandler( FALSE );
	}

	function getProvinceOptions( $country ) {
		Debug::Arr($country, 'aCountry: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !is_array($country) AND $country == '' ) {
			return $this->returnHandler( FALSE );
		}

		if ( !is_array($country) ) {
			$country = array($country);
		}

		Debug::Arr($country, 'bCountry: ', __FILE__, __LINE__, __METHOD__, 10);

		$cf = TTnew( 'CompanyFactory' );

		$province_arr = $cf->getOptions('province');

		$retarr = array();

		foreach( $country as $tmp_country ) {
			if ( isset($province_arr[strtoupper($tmp_country)]) ) {
				//Debug::Arr($province_arr[strtoupper($tmp_country)], 'Provinces Array', __FILE__, __LINE__, __METHOD__, 10);

				$retarr = array_merge( $retarr, $province_arr[strtoupper($tmp_country)] );
				//$retarr = array_merge( $retarr, Misc::prependArray( array( -10 => '--' ), $province_arr[strtoupper($tmp_country)] ) );
			}
		}

		if ( count($retarr) == 0 ) {
			$retarr = array('00' => '--');
		}

		return $this->returnHandler( $retarr );
	}

	function getMaintenanceJobs( $data = NULL ) {
		$install_obj = new Install();

		if ( $install_obj->isInstallMode() == TRUE ) {

			$uf = TTnew( 'UserFactory' );

			if ( isset($data['company_id']) ) {
				$retval['company_id'] = $data['company_id'];
			}

			$retval['application_name'] = APPLICATION_NAME ? APPLICATION_NAME : '';
			$retval['php_os'] = PHP_OS;

			$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'maintenance'), 'pre_install.php'), "r");
			@fclose($handle);

			if ( $install_obj->ScheduleMaintenanceJobs() == 0 ) { //Add scheduled maintenance jobs to cron/schtask, if it succeeds move to next step automatically.
				return $this->returnHandler( TRUE );
			}

			if ( $install_obj->getWebServerUser() ) {
				$retval['web_server_user'] = $install_obj->getWebServerUser();
			} else {
				$retval['web_server_user'] = '';
			}

			$retval['schedule_maintenance_job_command'] = $install_obj->getScheduleMaintenanceJobsCommand();
			$retval['cron_file'] = Environment::getBasePath().'maint'. DIRECTORY_SEPARATOR .'cron.php';
			$retval['php_cli'] = $install_obj->getPHPCLI();
			$retval['is_sudo_installed'] = $install_obj->isSUDOInstalled();

			return $this->returnHandler( $retval );
		}

		return $this->returnHandler( FALSE );
	}
}
?>
