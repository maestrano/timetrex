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
 * @package Modules\Install
 */
class Install {

	protected $temp_db = NULL;
	var $config_vars = NULL;
	protected $database_driver = NULL;
	protected $is_upgrade = FALSE;
	protected $extended_error_messages = NULL;
	protected $versions = array(
								'system_version' => APPLICATION_VERSION,
								);
	protected $progress_bar_obj = NULL;
	protected $AMF_message_id = NULL;


	function __construct() {
		global $config_vars, $cache;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'install'. DIRECTORY_SEPARATOR .'InstallSchema.class.php');

		$this->config_vars = $config_vars;

		//Disable caching so we don't exceed maximum memory settings.
		//$cache->_onlyMemoryCaching = TRUE; //This shouldn't be required anymore, as it also breaks invalidating cache files.

		ini_set('default_socket_timeout', 5);
		ini_set('allow_url_fopen', 1);

		//As of PHP v5.3 some SAPI's don't support dl(), however it appears that php.ini can still have it enabled.
		//Double check to make sure the dl() function exists prior to calling it.
		if ( version_compare(PHP_VERSION, '5.3.0', '<') AND function_exists('dl') == TRUE AND (bool)ini_get( 'enable_dl' ) == TRUE AND (bool)ini_get( 'safe_mode' ) == FALSE ) {
			$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';

			if ( extension_loaded('mysql') == FALSE ) {
				@dl($prefix . 'mysql.' . PHP_SHLIB_SUFFIX);
			}

			if ( extension_loaded('mysqli') == FALSE ) {
				@dl($prefix . 'mysqli.' . PHP_SHLIB_SUFFIX);
			}

			if ( extension_loaded('pgsql') == FALSE ) {
				@dl($prefix . 'pgsql.' . PHP_SHLIB_SUFFIX);
			}
		}

		return TRUE;
	}

	function getDatabaseDriver() {
		return $this->database_driver;
	}

	function setDatabaseDriver( $driver ) {
		if ( $this->getDatabaseType( $driver ) !== 1 ) {
			$this->database_driver = $this->getDatabaseType( $driver );

			return TRUE;
		}

		return FALSE;
	}

	function getProgressBarObject() {
		if	( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}
	//Returns the AMF messageID for each individual call.
	function getAMFMessageID() {
		if ( $this->AMF_message_id != NULL ) {
			return $this->AMF_message_id;
		}
		return FALSE;
	}
	function setAMFMessageID( $id ) {
		Debug::Text('AMF Message ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		if ( $id != '' ) {
			$this->AMF_message_id = $id;
			return TRUE;
		}

		return FALSE;
	}

	//Read .ini file.
	//Make sure setup_mode is enabled.
	function isInstallMode() {
		if ( isset($this->config_vars['other']['installer_enabled'])
				AND $this->config_vars['other']['installer_enabled'] == 1 ) {
			Debug::text('Install Mode is ON', __FILE__, __LINE__, __METHOD__, 9);
			return TRUE;
		}

		Debug::text('Install Mode is OFF', __FILE__, __LINE__, __METHOD__, 9);
		return FALSE;
	}

	function setExtendedErrorMessage( $key, $msg ) {
		if ( isset($this->extended_error_messages[$key]) AND in_array( $msg, $this->extended_error_messages[$key] ) ) {
			return TRUE;
		} else {
			$this->extended_error_messages[$key][] = $msg;
		}

		return TRUE;
	}

	function getExtendedErrorMessage( $key = NULL ) {
		if ( $key != '' ) {
			if ( isset($this->extended_error_messages[$key]) ) {
				return implode( ',', $this->extended_error_messages[$key] );
			}
		} else {
			return $this->extended_error_messages;
		}

		return FALSE;
	}

	//Checks if this is the professional version or not
	function getTTProductEdition() {
		return getTTProductEdition();
	}

	function getFullApplicationVersion() {
		$retval = APPLICATION_VERSION;

		if ( getTTProductEdition() == TT_PRODUCT_ENTERPRISE ) {
			$retval .= 'E';
		} elseif ( getTTProductEdition() == TT_PRODUCT_CORPORATE ) {
			$retval .= 'C';
		} elseif ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$retval .= 'P';
		} else {
			$retval .= 'S';
		}

		return $retval;
	}

	function getLicenseText() {
		$license_file = Environment::getBasePath(). DIRECTORY_SEPARATOR .'LICENSE';

		if ( is_readable($license_file) ) {
			$retval = file_get_contents( $license_file );

			if ( strlen($retval) > 10 ) {
				return $retval;
			}
		}

		return FALSE;
	}

	function setIsUpgrade( $val ) {
		$this->is_upgrade = (bool)$val;
	}
	function getIsUpgrade() {
		return $this->is_upgrade;
	}

	function setDatabaseConnection( $db ) {
		if ( is_object($db) AND ( is_resource($db->_connectionID) OR is_object($db->_connectionID) ) ) {
			$this->temp_db = $db;
			return TRUE;
		}

		return FALSE;
	}
	function getDatabaseConnection() {
		if ( isset($this->temp_db) AND ( is_resource($this->temp_db->_connectionID) OR is_object($this->temp_db->_connectionID)	 ) ) {
			return $this->temp_db;
		}

		return FALSE;
	}

	function setNewDatabaseConnection($type, $host, $user, $password, $database_name ) {
		if ( $this->getDatabaseConnection() !== FALSE ) {
			$this->getDatabaseConnection()->Close();
		}

		try {
			$db = ADONewConnection( $type );
			$db->SetFetchMode(ADODB_FETCH_ASSOC);
			$db->Connect( $host, $user, $password, $database_name);
			if (Debug::getVerbosity() == 11) {
				$db->debug = TRUE;
			}

			//MySQLi extension uses an object, not a resource.
			if ( is_resource($db->_connectionID) OR is_object($db->_connectionID) ) {
				$this->setDatabaseConnection( $db );

				//$this->temp_db = $db;

				return TRUE;
			}
		} catch (Exception $e) {
			return FALSE;
		}

		return FALSE;
	}

	function HumanBoolean($bool) {
		if ( $bool === TRUE OR strtolower(trim($bool)) == 'true' ) {
			return 'TRUE';
		} else {
			return 'FALSE';
		}
	}

	function writeConfigFile( $new_config_vars ) {
		if ( is_writeable( CONFIG_FILE ) ) {

			require_once('Config.php');
			$config = new Config();
			$data = $config->parseConfig( CONFIG_FILE, 'inicommented' );
			if ( is_object($data) AND get_class( $data ) == 'PEAR_Error' ) {
				Debug::Arr($data, 'ERROR modifying Config File!', __FILE__, __LINE__, __METHOD__, 9);
			} else {
				global $config_vars;

				//Debug::Arr($data, 'Current Config File!', __FILE__, __LINE__, __METHOD__, 9);
				if ( isset($new_config_vars['path']['base_url']) ) {
					$tmp_base_url = $new_config_vars['path']['base_url'];
				} elseif ( isset($config_vars['path']['base_url']) ) {
					$tmp_base_url = $config_vars['path']['base_url'];
				}
				if ( isset($tmp_base_url) ) {
					$new_config_vars['path']['base_url'] = preg_replace('@^(?:http://)?([^/]+)@i', '', $tmp_base_url );
					unset($tmp_base_url);
				}

				//Check for bug instroducd in v7.4.5 that removed all backslashes from paths, to attempt to put them back automatically.
				//This should be able to be removed by v8.0.
				if ( PRODUCTION == FALSE OR OPERATING_SYSTEM == 'WIN' ) {
					if ( !isset($new_config_vars['path']['php_cli']) AND isset($config_vars['path']['php_cli']) AND strpos( $config_vars['path']['php_cli'], '\\' ) === FALSE  ) {
						Debug::Text('Found php_cli path without backslash, trying to correct...', __FILE__, __LINE__, __METHOD__, 9);
						$new_config_vars['path']['php_cli'] = str_ireplace(':TimeTrexphpphp-win.exe', ':\TimeTrex\php\php-win.exe', $config_vars['path']['php_cli'] );
					}
					if ( !isset($new_config_vars['path']['storage']) AND isset($config_vars['path']['storage']) AND strpos( $config_vars['path']['storage'], '\\' ) === FALSE  ) {
						Debug::Text('Found storage path without backslash, trying to correct...', __FILE__, __LINE__, __METHOD__, 9);
						$new_config_vars['path']['storage'] = str_ireplace(':TimeTrexstorage', ':\TimeTrex\storage', $config_vars['path']['storage'] );
					}
					if ( !isset($new_config_vars['path']['log']) AND isset($config_vars['path']['log']) AND strpos( $config_vars['path']['log'], '\\' ) === FALSE  ) {
						Debug::Text('Found log path without backslash, trying to correct...', __FILE__, __LINE__, __METHOD__, 9);
						$new_config_vars['path']['log'] = str_ireplace(':TimeTrexlog', ':\TimeTrex\log', $config_vars['path']['log'] );
					}
					if ( !isset($new_config_vars['cache']['dir']) AND isset($config_vars['cache']['dir']) AND strpos( $config_vars['cache']['dir'], '\\' ) === FALSE  ) {
						Debug::Text('Found cache path without backslash, trying to correct...', __FILE__, __LINE__, __METHOD__, 9);
						$new_config_vars['cache']['dir'] = str_ireplace(':TimeTrexcache', ':\TimeTrex\cache', $config_vars['cache']['dir'] );
					}
				}
				//Clear erroneous INI sections due to same above bug.
				$new_config_vars['installer_enabled'] = 'TT_DELETE';
				$new_config_vars['default_interface'] = 'TT_DELETE';

				//Allow passing any empty array that will just rewrite the existing .INI file fixing any problems.
				if ( is_array($new_config_vars) ) {
					foreach( $new_config_vars as $section => $key_value_map ) {

						if ( !is_array( $key_value_map ) AND $key_value_map == 'TT_DELETE' ) {
							$item = $data->searchPath( array( $section ) );
							if ( is_object( $item ) ) {
								$item->removeItem();
							}
						} else {

							$key_value_map = (array)$key_value_map;
							foreach( $key_value_map as $key => $value ) {
								$item = $data->searchPath( array( $section, $key ) );
								if ( is_object( $item ) ) {
									$item->setContent( $value );
								} else {
									$item = $data->searchPath( array( $section ) );
									if ( is_object( $item ) ) {
										$item->createDirective( $key, $value, NULL, 'top' );
									} else {
										$item = $data->createSection( $section );
										$item->createDirective( $key, $value, NULL, 'top' );
									}

								}
							}

						}
					}

					Debug::text('Modified Config File!', __FILE__, __LINE__, __METHOD__, 9);
					//Debug::Arr($data, 'New Config File!', __FILE__, __LINE__, __METHOD__, 9);
					$retval = $config->writeConfig( CONFIG_FILE, 'inicommented' );

					//Make sure the first line in the file contains "die".
					$contents = file_get_contents( CONFIG_FILE );

					//Make sure we add back in the PHP code for security reasons.
					//BitRock seems to want to remove this and re-arrange the INI file as well for some odd reason.
					if ( stripos( $contents, ';<?php' ) === FALSE ) {
						Debug::text('Adding back in security feature...', __FILE__, __LINE__, __METHOD__, 9);
						$contents = ";<?php die('Unauthorized Access...'); //SECURITY MECHANISM, DO NOT REMOVE//?>\n".$contents;
					}
					file_put_contents( CONFIG_FILE, $contents );

					return $retval;
				}
			}
		} else {
			Debug::text('Config File Not Writable!', __FILE__, __LINE__, __METHOD__, 9);
		}

		return FALSE;
	}

	function setVersions() {
		if ( is_array($this->versions) ) {
			foreach( $this->versions as $name => $value ) {
				$sslf = TTnew( 'SystemSettingListFactory' );
				$sslf->getByName( $name );
				if ( $sslf->getRecordCount() == 1 ) {
					$obj = $sslf->getCurrent();
				} else {
					$obj = TTnew( 'SystemSettingListFactory' );
				}

				$obj->setName( $name );
				$obj->setValue( $value );
				if ( $obj->isValid() ) {
					if ( $obj->Save() === FALSE ) {
						return FALSE;
					}
				} else {
					return FALSE;
				}
			}

			//Set the date when the upgrade was performed, so we can tell when the version was installed.
			$sslf = TTnew( 'SystemSettingListFactory' );
			$sslf->getByName( 'system_version_install_date' );
			if ( $sslf->getRecordCount() == 1 ) {
				$obj = $sslf->getCurrent();
			} else {
				$obj = TTnew( 'SystemSettingListFactory' );
			}
			$obj->setName( 'system_version_install_date' );
			$obj->setValue( time() );
			if ( $obj->isValid() ) {
				if ( $obj->Save() === FALSE ) {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}

		return TRUE;
	}
	/*

		Database Schema functions

	*/
	function checkDatabaseExists( $database_name ) {
		Debug::text('Database Name: '. $database_name, __FILE__, __LINE__, __METHOD__, 9);
		$db_conn = $this->getDatabaseConnection();

		if ( $db_conn == FALSE ) {
			return FALSE;
		}

		$database_arr = $db_conn->MetaDatabases();

		if ( in_array($database_name, $database_arr ) ) {
			Debug::text('Exists - Database Name: '. $database_name, __FILE__, __LINE__, __METHOD__, 9);
			return TRUE;
		}

		Debug::text('Does not Exist - Database Name: '. $database_name, __FILE__, __LINE__, __METHOD__, 9);
		return FALSE;
	}

	function createDatabase( $database_name ) {
		Debug::text('Database Name: '. $database_name, __FILE__, __LINE__, __METHOD__, 9);

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb.inc.php');

		if ( $database_name == '' ) {
			Debug::text('Database Name invalid ', __FILE__, __LINE__, __METHOD__, 9);
			return FALSE;
		}

		$db_conn = $this->getDatabaseConnection();
		if ( $db_conn == FALSE ) {
			Debug::text('No Database Connection.', __FILE__, __LINE__, __METHOD__, 9);
			return FALSE;
		}
		Debug::text('Attempting to Create Database...', __FILE__, __LINE__, __METHOD__, 9);

		$dict = NewDataDictionary( $db_conn );

		$sqlarray = $dict->CreateDatabase( $database_name );
		return $dict->ExecuteSQLArray($sqlarray);
	}

	function checkTableExists( $table_name ) {
		Debug::text('Table Name: '. $table_name, __FILE__, __LINE__, __METHOD__, 9);
		$db_conn = $this->getDatabaseConnection();

		if ( $db_conn == FALSE ) {
			return FALSE;
		}

		$table_arr = $db_conn->MetaTables();

		if ( in_array($table_name, $table_arr ) ) {
			Debug::text('Exists - Table Name: '. $table_name, __FILE__, __LINE__, __METHOD__, 9);
			return TRUE;
		}

		Debug::text('Does not Exist - Table Name: '. $table_name, __FILE__, __LINE__, __METHOD__, 9);
		return FALSE;
	}

	//Get all schema versions
	//A=Community, B=Professional, C=Corporate, D=Enterprise, T=Tax
	function getAllSchemaVersions( $group = array('A', 'B', 'C', 'D', 'T') ) {
		if ( !is_array($group) ) {
			$group = array( $group );
		}

		$is_obj = new InstallSchema( $this->getDatabaseDriver(), '', NULL, $this->getIsUpgrade() );

		$schema_files = array();

		$dir = $is_obj->getSQLFileDirectory();
		if ( $handle = opendir($dir) ) {
			while ( FALSE !== ($file = readdir($handle))) {
				list($schema_base_name, $extension) = explode('.', $file);
				$schema_group = substr($schema_base_name, -1, 1 );
				Debug::text('Schema: '. $file .' Group: '. $schema_group, __FILE__, __LINE__, __METHOD__, 9);

				if ($file != '.' AND $file != '..'
						AND substr($file, 1, 0) != '.'
						AND in_array($schema_group, $group) ) {
					$schema_versions[] = basename($file, '.sql');
				}
			}
			closedir($handle);
		}

		sort($schema_versions);
		Debug::Arr($schema_versions, 'Schema Versions', __FILE__, __LINE__, __METHOD__, 9);

		return $schema_versions;
	}

	function handleSchemaGroupChange() {
		//Pre v7.0, if the database version is less than 7.0 we need to *copy* the schema version from group B to C so we don't try to upgrade the database with old schemas.
		if ( $this->getIsUpgrade() == TRUE ) {
			$sslf = TTnew( 'SystemSettingListFactory' );
			$sslf->getByName( 'system_version' );
			if ( $sslf->getRecordCount() > 0 ) {
				$ss_obj = $sslf->getCurrent();
				$system_version = $ss_obj->getValue();
				Debug::text('System Version: '. $system_version .' Application Version: '. APPLICATION_VERSION, __FILE__, __LINE__, __METHOD__, 9);

				//If the current version is greater than 7.0 and the system_version in the database is less than 7.0, we know we are upgrading from pre7.0 to post7.0.
				if ( version_compare( APPLICATION_VERSION, '7.0', '>=' ) AND version_compare( $system_version, '7.0', '<' ) ) {
					Debug::text('Upgrade schema groups...', __FILE__, __LINE__, __METHOD__, 9);

					$sslf->getByName( 'schema_version_group_B' );
					if ( $sslf->getRecordCount() > 0 ) {
						$ss_obj = $sslf->getCurrent();
						$schema_version_group_b = $ss_obj->getValue();
						Debug::text('Schema Version Group B: '. $schema_version_group_b, __FILE__, __LINE__, __METHOD__, 9);

						$tmp_name = 'schema_version_group_C';
						$tmp_sslf = TTnew( 'SystemSettingListFactory' );
						$tmp_sslf->getByName( $tmp_name );
						if ( $tmp_sslf->getRecordCount() == 1 ) {
							$tmp_obj = $tmp_sslf->getCurrent();
						} else {
							$tmp_obj = TTnew( 'SystemSettingListFactory' );
						}
						$tmp_obj->setName( $tmp_name );
						$tmp_obj->setValue( $schema_version_group_b );
						if ( $tmp_obj->isValid() ) {
							if ( $tmp_obj->Save() === FALSE ) {
								return FALSE;
							}
							return TRUE;
						} else {
							return FALSE;
						}
					}
				}
			}
		}
		
		return FALSE;
	}

	//Creates DB schema starting at and including start_version, and ending at, including end version.
	//Starting at NULL is first version, ending at NULL is last version.
	function createSchemaRange( $start_version = NULL, $end_version = NULL, $group = array('A', 'B', 'C', 'D', 'T') ) {
		global $cache, $progress_bar, $config_vars;

		if ( $this->checkDatabaseSchema() == 1 ) {
			return FALSE;
		}

		//Some schema changes can take a very long time to complete, make sure PHP doesn't cancel out on us.
		ignore_user_abort(TRUE);
		ini_set( 'max_execution_time', 0 );
		ini_set( 'memory_limit', '-1' );

		//Clear all cache before we do any upgrading, this is especially important during development processes
		//if we are switching between databases or reloading databases.
		$this->cleanCacheDirectory();
		$cache->clean(); //Clear all cache.

		//Disable detailed audit logging during schema upgrades, as it breaks upgrading from pre-audit log versions to post-audit log versions.
		//ie: v2.2.22 to v3.3.2.
		$config_vars['other']['disable_audit_log_detail'] = TRUE;

		$this->handleSchemaGroupChange(); //Copy schema group B to C during v7.0 upgrade.

		$schema_versions = $this->getAllSchemaVersions( $group );

		Debug::Arr($schema_versions, 'Schema Versions: ', __FILE__, __LINE__, __METHOD__, 9);

		$total_schema_versions = count($schema_versions);
		if ( is_array($schema_versions) AND $total_schema_versions > 0 ) {
			//$this->getDatabaseConnection()->StartTrans();
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_schema_versions );
			$x = 0;
			foreach( $schema_versions as $schema_version ) {
				if ( ( $start_version === NULL OR $schema_version >= $start_version )
					AND ( $end_version === NULL OR $schema_version <= $end_version )
						) {

					//Wrap each schema version in its own transaction (compared to all schema versions in one transaction), this reduces the length of time any one transaction
					//is open for and should allow vacuum to run more often on PostgreSQL speeding up subsequency schemas.
					//This may make it harder to test rollback schema upgrades during development though.
					$this->getDatabaseConnection()->StartTrans();
					
					$create_schema_result = $this->createSchema( $schema_version );

					if ( is_object($progress_bar) ) {
						$progress_bar->setValue( Misc::calculatePercent( $x, $total_schema_versions ) );
						$progress_bar->display();
					}

					if ( $this->is_upgrade ) {
						$msg = TTi18n::getText('Upgrading database');
					} else {
						$msg = TTi18n::getText('Initializing database');
					}

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $x, $msg.' - '.($x+1).' '.TTi18n::getText('of').' '.$total_schema_versions );

					if ( $create_schema_result === FALSE ) {
						Debug::text('CreateSchema Failed! On Version: '. $schema_version, __FILE__, __LINE__, __METHOD__, 9);
						$this->getDatabaseConnection()->FailTrans();
						return FALSE;
					}
					$this->getDatabaseConnection()->CompleteTrans();
				}

				//Fast way to clear memory caching only between schema upgrades to make sure it doesn't get too big.
				$cache->_memoryCachingArray = array();
				$cache->_memoryCachingCounter = 0; 

				$x++;
			}
			$this->initializeSequences();
			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			//$this->getDatabaseConnection()->FailTrans();
			//$this->getDatabaseConnection()->CompleteTrans();
		}

		//Clear all cache after the upgrade as well, as much of it is unlikely to be used again.
		$this->cleanCacheDirectory();
		$cache->clean();

		//Delete orphan files after schema upgrade is fully completed.
		$this->cleanOrphanFiles();

		return TRUE;
	}

	function createSchema( $version ) {
		if ( $version == '' ) {
			return FALSE;
		}

		$install = FALSE;

		$group = substr( $version, -1, 1);
		$version_number = substr( $version, 0, (strlen($version) - 1));

		Debug::text('Version: '. $version .' Version Number: '. $version_number .' Group: '. $group, __FILE__, __LINE__, __METHOD__, 9);

		//Only create schema if current system settings do not exist, or they are
		//older then this current schema version.
		if ( $this->checkTableExists( 'system_setting') == TRUE ) {
			Debug::text('System Setting Table DOES exist...', __FILE__, __LINE__, __METHOD__, 9);

			$sslf = TTnew( 'SystemSettingListFactory' );
			$sslf->getByName( 'schema_version_group_'. substr( $version, -1, 1) );
			if ( $sslf->getRecordCount() > 0 ) {
				$ss_obj = $sslf->getCurrent();
				Debug::text('Found System Setting Entry: '. $ss_obj->getValue(), __FILE__, __LINE__, __METHOD__, 9);

				if ( $ss_obj->getValue() < $version_number ) {
					Debug::text('Schema version is older, installing...', __FILE__, __LINE__, __METHOD__, 9);
					$install = TRUE;
				} else {
					Debug::text('Schema version is equal, or newer then what we are trying to install...', __FILE__, __LINE__, __METHOD__, 9);
					$install = FALSE;
				}
			} else {
				Debug::text('Did not find System Setting Entry...', __FILE__, __LINE__, __METHOD__, 9);
				$install = TRUE;
			}
		} else {
			Debug::text('System Setting Table does not exist...', __FILE__, __LINE__, __METHOD__, 9);
			$install = TRUE;
		}

		if ( $install == TRUE ) {
			$is_obj = new InstallSchema( $this->getDatabaseDriver(), $version, $this->getDatabaseConnection(), $this->getIsUpgrade() );
			return $is_obj->InstallSchema();
		}

		return TRUE;
	}

	function initializeSequence( $obj, $table, $class, $db_conn ) {
		$next_insert_id = $obj->getNextInsertId();
		Debug::Text('Table: '. $table .' Class: '. $class .' Sequence Name: '. $obj->getSequenceName() .' Next Insert ID: '. $next_insert_id, __FILE__, __LINE__, __METHOD__, 10);

		$query = 'select max(id) from '. $table;
		$max_id = (int)$db_conn->GetOne('select max(id) from '. $table);
		if ( $next_insert_id == 0 OR $next_insert_id < $max_id ) {
			Debug::Text('  Out-of-sync sequence table, fixing... Current Max ID: '. $max_id .' Next ID: '. $next_insert_id, __FILE__, __LINE__, __METHOD__, 10);
			if ( strncmp($db_conn->databaseType, 'mysql', 5) == 0 ) {
				if ( $next_insert_id == 0 ) {
					$query = 'insert into '. $obj->getSequenceName() .' VALUES('. ( $max_id + 1 ) .')';
				} else {
					$query = 'update '. $obj->getSequenceName() .' set ID = '. ( $max_id + 1 );
				}
			} elseif ( strncmp($db_conn->databaseType, 'postgres', 8) == 0 ) {
				//This can be helpful with PostgreSQL as well as sequences can get out of sync there too if the schema was created incorrectly.
				$query = 'select setval(\''. $obj->getSequenceName() .'\', '. ( $max_id + 1 ) .')';
			}
			//Debug::Text('  Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
			$db_conn->Execute($query);
		} else {
			Debug::Text('  Sequence is in sync, not updating...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	//Only required with MySQL, this can help prevent race conditions when creating new tables.
	//It will also correct any corrupt sequences that don't match their parent tables.
	function initializeSequences() {
		require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'includes'. DIRECTORY_SEPARATOR .'TableMap.inc.php');
		global $global_table_map;

		$db_conn = $this->getDatabaseConnection();

		if ( $db_conn == FALSE ) {
			return FALSE;
		}

		$table_arr = $db_conn->MetaTables();
		
		foreach( $global_table_map as $table => $class ) {
			if ( class_exists( $class ) AND in_array( $table, $table_arr ) ) {
				$obj = new $class;

				if ( $obj->getSequenceName() != '' ) {
					$this->initializeSequence( $obj, $table, $class, $db_conn );
				}
			}
		}

		return TRUE;
	}
	
	/*

		System Requirements

	*/

	function getPHPVersion() {
		return PHP_VERSION;
	}

	function checkPHPVersion($php_version = NULL) {
		// Return
		// 0 = OK
		// 1 = Invalid
		// 2 = UnSupported

		if ( $php_version == NULL ) {
			$php_version = $this->getPHPVersion();
		}
		Debug::text('Comparing with Version: '. $php_version, __FILE__, __LINE__, __METHOD__, 9);

		$min_version = '5.0.0';
		$max_version = '5.5.99'; //Change install.php as well, as some versions break backwards compatibility, so we need early checks as well.

		$unsupported_versions = array('');

		/*
			Invalid PHP Versions:
				v5.4.0+ - (Fixed as of 10-Apr-13) Fails due to deprecated call-time references (&$), disable for now.
				v5.3.0+ - Fails due to deprecated functions still in use. This is mostly fixed as of v3.1.0-rc1, leave enabled for now.
				v5.0.4 - Fails to assign object values by ref. In ViewTimeSheet.php $smarty->assign_by_ref( $pp_obj->getId() ) fails.
				v5.2.2 - Fails to populate $HTTP_RAW_POST_DATA http://bugs.php.net/bug.php?id=41293
					   - Implemented work around in global.inc.php
		*/
		$invalid_versions = array('5.0.4');


		if ( version_compare( $php_version, $min_version, '<') == 1 ) {
			//Version too low
			$retval = 1;
		} elseif ( version_compare( $php_version, $max_version, '>') == 1 ) {
			//UnSupported
			$retval = 2;
		} else {
			$retval = 0;
		}

		foreach( $unsupported_versions as $unsupported_version ) {
			if ( version_compare( $php_version, $unsupported_version, 'eq') == 1 ) {
				$retval = 2;
				break;
			}
		}

		foreach( $invalid_versions as $invalid_version ) {
			if ( version_compare( $php_version, $invalid_version, 'eq') == 1 ) {
				$retval = 1;
				break;
			}
		}

		//Debug::text('RetVal: '. $retval, __FILE__, __LINE__, __METHOD__, 9);
		return $retval;
	}

	function getDatabaseType( $type = NULL ) {
		if ( $type != '' ) {
			$db_type = $type;
		} else {
			//$db_type = $this->config_vars['database']['type'];
			$db_type = $this->getDatabaseDriver();
		}

		if ( stristr($db_type, 'postgres') ) {
			$retval = 'postgresql';
		} elseif ( stristr($db_type, 'mysql') ) {
			$retval = 'mysql';
		} else {
			$retval = 1;
		}

		return $retval;
	}

	function getMemoryLimit() {
		//
		// NULL = unlimited
		// INT = limited to that value

		$raw_limit = ini_get('memory_limit');
		//Debug::text('RAW Limit: '. $raw_limit, __FILE__, __LINE__, __METHOD__, 9);
		$limit = (int)rtrim($raw_limit, 'M');
		//Debug::text('Limit: '. $limit, __FILE__, __LINE__, __METHOD__, 9);

		if ( $raw_limit == '' OR $raw_limit <= 0 ) {
			return NULL;
		}

		return $limit;
	}

	function getPHPConfigFile() {
		return get_cfg_var("cfg_file_path");
	}

	function getConfigFile() {
		return CONFIG_FILE;
	}

	function getPHPIncludePath() {
		return get_cfg_var("include_path");
	}

	function getDatabaseVersion() {
		if ( $this->getDatabaseType() == 'postgresql' ) {
			$version = @pg_version();
			if ( $version == FALSE ) {
				//No connection
				return NULL;
			} else {
				return $version['server'];
			}
		} elseif ( $this->getDatabaseType() == 'mysqlt' OR $this->getDatabaseType() == 'mysqli' ) {
			$version = @get_server_info();
			return $version;
		}

		return FALSE;
	}

	function getDatabaseTypeArray() {
		$retval = array();

		if ( function_exists('pg_connect') ) {
			$retval['postgres8'] = 'PostgreSQL v8+';

			// set edb_redwood_date = 'off' must be set, otherwise enterpriseDB
			// changes all date columns to timestamp columns and breaks TimeTrex.
			$retval['enterprisedb'] = 'EnterpriseDB (DISABLE edb_redwood_date)';
		}
		if ( function_exists('mysqli_real_connect') ) {
			$retval['mysqli'] = 'MySQLi (v5.0.48+ w/InnoDB)';
		}
		//MySQLt driver is no longer supported, as it causes conflicts with ADODB and complex queries.
		if ( function_exists('mysql_connect') ) {
			$retval['mysqlt'] = 'MySQL (Legacy Driver - NOT SUPPORTED, use MYSQLi instead!)';
		}

		return $retval;
	}

	function checkFilePermissions() {
		// Return
		//
		// 0 = OK
		// 1 = Invalid
		// 2 = Unsupported
		if ( PRODUCTION == FALSE OR DEPLOYMENT_ON_DEMAND == TRUE ) {
			return 0; //Skip permission checks.
		}

		$dirs = array();

		//Make sure we check all files inside the log, storage, cache and templates_c directories, in case some files were created with the incorrect permissions and can't be overwritten.
		if ( isset($this->config_vars['cache']['dir']) ) {
			$dirs[] = $this->config_vars['cache']['dir'];
		}
		if ( isset($this->config_vars['path']['log']) ) {
			$dirs[] = $this->config_vars['path']['log'];
		}
		if ( isset($this->config_vars['path']['storage']) ) {
			$dirs[] = $this->config_vars['path']['storage'];
		}
		if ( Environment::getTemplateCompileDir() != '' ) {
			$dirs[] = Environment::getTemplateCompileDir();
		}

		$dirs[] = dirname( __FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR;

		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($dirs), NULL, TTi18n::getText('Check File Permission...') );

		foreach( $dirs as $k => $dir ) {
			Debug::Text('Checking directory readable/writable: '. $dir, __FILE__, __LINE__, __METHOD__, 10);
			if ( is_dir( $dir ) AND is_readable( $dir ) ) {
				try {
					$rdi = new RecursiveDirectoryIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );
					foreach ( new RecursiveIteratorIterator( $rdi ) as $file_name => $cur ) {
						if ( strcmp( basename($file_name), '.') == 0
								OR strcmp( basename($file_name), '..' ) == 0
								OR strcmp( basename($file_name), '.htaccess' ) == 0 ) { //.htaccess files often aren't writable by the webserver.
							continue;
						}

						//Debug::Text('Checking readable/writable: '. $file_name, __FILE__, __LINE__, __METHOD__, 10);
						if ( is_readable( $file_name ) == FALSE ) {
							Debug::Text('File or directory is not readable: '. $file_name, __FILE__, __LINE__, __METHOD__, 10);
							$this->setExtendedErrorMessage( 'checkFilePermissions', 'Not Readable: '. $file_name );
							return 1; //Invalid
						}

						if ( Misc::isWritable( $file_name ) == FALSE ) {
							Debug::Text('File or directory is not writable: '. $file_name, __FILE__, __LINE__, __METHOD__, 10);
							$this->setExtendedErrorMessage( 'checkFilePermissions', 'Not writable: '. $file_name );
							return 1; //Invalid
						}
					}
				} catch( Exception $e ) {
					Debug::Text('Failed opening/reading file or directory: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
					return 1;
				}
			}

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $k );
		}

		$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

		Debug::Text('All Files/Directories are readable/writable!', __FILE__, __LINE__, __METHOD__, 10);
		return 0;
	}

	function checkFileChecksums() {
		// Return
		//
		// 0 = OK
		// 1 = Invalid
		// 2 = Unsupported

		if ( PRODUCTION == FALSE OR DEPLOYMENT_ON_DEMAND == TRUE ) {
			return 0; //Skip checksums.
		}

		//Load checksum file.

		$checksum_file = dirname( __FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR . 'files.sha1';

		if ( file_exists( $checksum_file ) ) {
			$checksum_data = file_get_contents( $checksum_file );
			$checksums = explode("\n", $checksum_data );
			unset($checksum_data);
			if ( is_array($checksums) ) {

				$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($checksums), NULL, TTi18n::getText('Check File Checksums...') );

				$i = 0;
				foreach($checksums as $checksum_line ) {

					//1st line contains the TT version for the checksums, make sure it matches current version.
					if ( $i == 0 ) {
						if ( preg_match( '/\d+\.\d+\.\d+/', $checksum_line, $checksum_version ) ) {
							Debug::Text('Checksum version: '. $checksum_version[0], __FILE__, __LINE__, __METHOD__, 10);
							if ( version_compare( APPLICATION_VERSION, $checksum_version[0], '=') ) {
								Debug::Text('Checksum version matches!', __FILE__, __LINE__, __METHOD__, 10);
							} else {
								Debug::Text('Checksum version DOES NOT match! Version: '. APPLICATION_VERSION .' Checksum Version: '. $checksum_version[0], __FILE__, __LINE__, __METHOD__, 10);
								$this->setExtendedErrorMessage( 'checkFileChecksums', 'Application version does not match checksum version: '. $checksum_version[0] );
								return 1;
							}
						} else {
							Debug::Text('Checksum version not found in file: '. $checksum_line, __FILE__, __LINE__, __METHOD__, 10);
						}
					} elseif ( strlen( $checksum_line ) > 1 ) {
						$split_line = explode(' ', $checksum_line );
						if ( is_array($split_line) ) {
							$file_name = Environment::getBasePath() . str_replace( '/', DIRECTORY_SEPARATOR, str_replace('./', '', trim($split_line[2]) ) );
							$checksum = trim($split_line[0]);

							if ( file_exists( $file_name ) ) {
								$my_checksum = @sha1_file( $file_name );
								if ( $my_checksum == $checksum ) {
									//Debug::Text('File: '. $file_name .' Checksum: '. $checksum .' MATCHES', __FILE__, __LINE__, __METHOD__, 10);
									unset($my_checksum); //NoOp
								} else {
									Debug::Text('File: '. $file_name .' Checksum: '. $my_checksum .' DOES NOT match provided checksum of: '. $checksum, __FILE__, __LINE__, __METHOD__, 10);
									$this->setExtendedErrorMessage( 'checkFileChecksums', 'Checksum does not match: '. $file_name );
									return 1; //Invalid
								}
								unset($my_checksum);
							} else {
								Debug::Text('File does not exist: '. $file_name, __FILE__, __LINE__, __METHOD__, 10);
								$this->setExtendedErrorMessage( 'checkFileChecksums', 'File does not exist: '. $file_name );
								return 1; //Invalid
							}

						}
						unset($split_line, $file_name, $checksum);
					}

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $i );

					$i++;

				}
				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );
				return 0; //OK
			}
		} else {
			Debug::Text('Checksum file does not exist: '. $checksum_file, __FILE__, __LINE__, __METHOD__, 10);
			$this->setExtendedErrorMessage( 'checkFileChecksums', 'Checksum file does not exist: '. $checksum_file );
		}

		return 1; //Invalid
	}

	function checkDatabaseType() {
		// Return
		//
		// 0 = OK
		// 1 = Invalid
		// 2 = Unsupported

		$retval = 1;

		if ( function_exists('pg_connect') ) {
			$retval = 0;
		} elseif ( function_exists('mysqli_real_connect') ) {
			$retval = 0;
		} elseif ( function_exists('mysql_connect') ) {
			$retval = 2;
		}

		return $retval;
	}

	function checkDatabaseVersion() {
		$db_version = (string)$this->getDatabaseVersion();

		if ( $this->getDatabaseType() == 'postgresql' ) {
			if ( $db_version == NULL OR version_compare( $db_version, '8.0', '>') == 1 ) {
				return 0;
			}
		} elseif ( $this->getDatabaseType() == 'mysql' ) {
			//Require at least. 4.1.3 and use MySQLi extension?
			if ( version_compare( $db_version, '4.1.3', '>=') == 1 ) {
				return 0;
			}
		}

		return 1;
	}

	function checkDatabaseEngine() {
		//
		// For MySQL only, this checks to make sure InnoDB is enabled!
		//
		Debug::Text('Checking DatabaseEngine...', __FILE__, __LINE__, __METHOD__, 10);
		if ($this->getDatabaseType() != 'mysql' ) {
			return TRUE;
		}

		$db_conn = $this->getDatabaseConnection();
		if ( $db_conn == FALSE ) {
			Debug::text('No Database Connection.', __FILE__, __LINE__, __METHOD__, 9);
			return FALSE;
		}

		$query = 'show engines';
		$storage_engines = $db_conn->getAll($query);
		//Debug::Arr($storage_engines, 'Available Storage Engines:', __FILE__, __LINE__, __METHOD__, 9);
		if ( is_array($storage_engines) ) {
			foreach( $storage_engines as $key => $data ) {
				Debug::Text('Engine: '. $data['Engine'] .' Support: '. $data['Support'], __FILE__, __LINE__, __METHOD__, 10);
				if ( strtolower($data['Engine']) == 'innodb' AND ( strtolower($data['Support']) == 'yes' OR strtolower($data['Support']) == 'default' )	 ) {
					Debug::text('InnoDB is available!', __FILE__, __LINE__, __METHOD__, 9);
					return TRUE;
				}
			}
		}

		Debug::text('InnoDB is NOT available!', __FILE__, __LINE__, __METHOD__, 9);
		return FALSE;
	}

	function checkDatabaseSchema() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			$db_conn = $this->getDatabaseConnection();
			if ( $db_conn == FALSE ) {
				Debug::text('No Database Connection.', __FILE__, __LINE__, __METHOD__, 9);
				return FALSE;
			}

			if ( $this->checkTableExists( 'system_setting' ) == TRUE ) {
				$sslf = TTnew( 'SystemSettingListFactory' );
				$sslf->getByName( 'schema_version_group_B' );
				if ( $sslf->getRecordCount() == 1 ) {
					Debug::text('ERROR: Database schema out of sync with edition...', __FILE__, __LINE__, __METHOD__, 9);
					return 1;
				}
			}
		}

		return 0;
	}

	function isSUDOinstalled() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			exec( 'which sudo', $output, $exit_code );
			if ( $exit_code == 0 AND $output != '' ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	
	function getWebServerUser() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			if ( function_exists('posix_geteuid') AND function_exists('posix_getpwuid') ) {
				$user = posix_getpwuid( posix_geteuid() );
				Debug::text('Webserver running as User: '. $user['name'], __FILE__, __LINE__, __METHOD__, 9);

				return $user['name'];
			}
		}

		return FALSE;
	}

	function getScheduleMaintenanceJobsCommand() {
		$command = FALSE;
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			if ( $this->getWebServerUser() != '' ) {
				$command = Environment::getBasePath() . 'install_cron.sh ' . $this->getWebServerUser();
			}
		} elseif ( OPERATING_SYSTEM == 'WIN' ) {
			$system_root = getenv('SystemRoot');
			if ( $system_root != '' ) {
				$command = $system_root . '\system32\schtasks /create /SC minute /TN timetrex_maintenance /TR ""'. Environment::getBasePath() . '..\php\php-win.exe" "' . Environment::getBasePath() . 'maint\cron.php""';
			}
		}

		return $command;
	}

	function ScheduleMaintenanceJobs() {
		$command = $this->getScheduleMaintenanceJobsCommand();
		if ( $command != '' ) {
			exec( $command, $output, $exit_code );
			Debug::Arr($output, 'Schedule Maintenance Jobs Command: '. $command .' Exit Code: '. $exit_code .' Output: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( $exit_code == 0 ) {
				return 0;
			}
		}
		
		return 1; //Fail so we can display the command to the user instead.
	}

	function getBaseURL() {
		return Misc::getURLProtocol() .'://'. Misc::getHostName( TRUE ).Environment::getBaseURL().'install/install.php'; //Check for a specific file, so we can be sure its not incorrect.
	}
	function getRecommendedBaseURL() {
		return str_replace('install', '', dirname( $_SERVER['SCRIPT_NAME'] ) );
	}
	function checkBaseURL() {
		$url = $this->getBaseURL();
		$headers = @get_headers($url);
		Debug::Arr($headers, 'Checking Base URL: '. $url, __FILE__, __LINE__, __METHOD__, 9);
//		if ( isset($headers[0]) AND stripos($headers[0], '404') !== FALSE ) {
//			return 1; //Not found
//		} else {
//			return 0; //Found
//		}

		return 0; //Found
	}

	function getPHPOpenBaseDir() {
		return ini_get('open_basedir');
	}
	function getPHPCLIDirectory() {
		return dirname( $this->getPHPCLI() );
	}

	function checkPHPOpenBaseDir() {
		$open_basedir = $this->getPHPOpenBaseDir();
		Debug::Text('Open BaseDir: '. $open_basedir, __FILE__, __LINE__, __METHOD__, 9);
		if ( $open_basedir == '' ) {
			return 0;
		} else {
			if ( $this->getPHPCLI() != '' ) {
				//Check if PHPCLIDir is contained in open_basedir, or if open_basedir is contained in the PHPCLIDir.
				//For cases like: open_basedir=/var/www/vhosts/domain/ and php_cli=/var/www/vhosts/domain/usr/bin/
				// Or for cases: open_basedir=/usr/ and php_cli=/usr/bin/
				if ( strpos( $open_basedir, $this->getPHPCLIDirectory() ) !== FALSE OR strpos( $this->getPHPCLIDirectory(), $open_basedir ) !== FALSE ) {
					return 0;
				} else {
					Debug::Text('PHP CLI Binary ('. dirname( $this->getPHPCLIDirectory() ) .') NOT found in Open BaseDir: '. $open_basedir, __FILE__, __LINE__, __METHOD__, 9);
				}
			} else {
				return 0;
			}
		}

		return 1;
	}

	function getPHPCLI() {
		if ( isset($this->config_vars['path']['php_cli']) ) {
			return $this->config_vars['path']['php_cli'];
		}

		return FALSE;
	}
	function checkPHPCLIBinary() {
		if ( $this->getPHPCLI() != '' ) {
			if ( is_executable( $this->getPHPCLI() ) ) {
				return 0;
			}
		}

		return 1;
	}

	function getPHPCLIRequirementsCommand() {
		$command = '"'. $this->getPHPCLI() .'" "'. Environment::getBasePath() .'tools'. DIRECTORY_SEPARATOR .'unattended_upgrade.php" --config "'. CONFIG_FILE .'" --requirements_only --web_installer';
		return $command;
	}
	//Only check this if *not* being called from the CLI to prevent infinite loops.
	function checkPHPCLIRequirements() {
		if ( $this->checkPHPCLIBinary() === 0 ) {
			$command = $this->getPHPCLIRequirementsCommand();
			exec( $command, $output, $exit_code );
			Debug::Arr($output, 'PHP CLI Requirements Command: '. $command .' Output: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( $exit_code == 0 ) {
				return 0;
			} else {
				$this->setExtendedErrorMessage( 'checkPHPCLIRequirements', 'PHP CLI Requirements Output: '. '<br>'.implode('<br>', (array)$output ) );
			}
		}

		return 1;
	}

	function checkPEAR() {
		@include_once('PEAR.php');

		if ( class_exists('PEAR') ) {
			return 0;
		}

		return 1;
	}

	function checkPEARHTML_Progress() {
		include_once('HTML/Progress.php');

		if ( class_exists('HTML_Progress') ) {
			return 0;
		}

		return 1;
	}

	function checkPEARHTML_AJAX() {
		include_once('HTML/AJAX/Server.php');

		if ( class_exists('HTML_AJAX_Server') ) {
			return 0;
		}

		return 1;
	}

	function checkPEARHTTP_Download() {
		include_once('HTTP/Download.php');

		if ( class_exists('HTTP_Download') ) {
			return 0;
		}

		return 1;
	}

	function checkPEARValidate() {
		include_once('Validate.php');

		if ( class_exists('Validate') ) {
			return 0;
		}

		return 1;
	}

	function checkPEARValidate_Finance() {
		include_once('Validate/Finance.php');

		if ( class_exists('Validate_Finance') ) {
			return 0;
		}

		return 1;
	}

	function checkPEARValidate_Finance_CreditCard() {
		include_once('Validate/Finance/CreditCard.php');

		if ( class_exists('Validate_Finance_CreditCard') ) {
			return 0;
		}

		return 1;
	}

	function checkPEARNET_Curl() {
		include_once('Net/Curl.php');

		if ( class_exists('NET_Curl') ) {
			return 0;
		}

		return 1;
	}

	function checkPEARMail() {
		include_once('Mail.php');

		if ( class_exists('Mail') ) {
			return 0;
		}

		return 1;
	}

	function checkPEARMail_Mime() {
		include_once('Mail/mime.php');

		if ( class_exists('Mail_Mime') ) {
			return 0;
		}

		return 1;
	}

	function checkZIP() {
		if ( class_exists('ZipArchive') ) {
			return 0;
		}

		return 1;
	}

	function checkMAIL() {
		if ( function_exists('mail') ) {
			return 0;
		}

		return 1;
	}

	function checkGETTEXT() {
		if ( function_exists('gettext') ) {
			return 0;
		}

		return 2;
	}

	function checkBCMATH() {
		if ( function_exists('bcscale') ) {
			return 0;
		}

		return 1;
	}

	function checkMBSTRING() {
		if ( function_exists('mb_detect_encoding') ) {
			return 0;
		}

		return 1;
	}

	//No longer required, used pure PHP implemented TTDate::EasterDays() instead.
	function checkCALENDAR() {
		if ( function_exists('easter_date') ) {
			return 0;
		}

		return 1;
	}

	function checkSOAP() {
		if ( class_exists('SoapServer') ) {
			return 0;
		}

		return 1;
	}

	function checkMCRYPT() {
		if ( function_exists('mcrypt_module_open') ) {
			return 0;
		}

		return 1;
	}

	function checkGD() {
		if ( function_exists('imagefontheight') ) {
			return 0;
		}

		return 1;
	}

	function checkJSON() {
		if ( function_exists('json_decode') ) {
			return 0;
		}

		return 1;
	}

	//Not currently mandatory, but can be useful to provide better SOAP timeouts.
	function checkCURL() {
		if ( function_exists('curl_exec') ) {
			return 0;
		}

		return 1;
	}

	function checkSimpleXML() {
		if ( class_exists('SimpleXMLElement') ) {
			return 0;
		}

		return 1;
	}


	function checkWritableConfigFile() {
		if ( is_writable( CONFIG_FILE ) ) {
			return 0;
		}

		return 1;
	}

	function checkWritableCacheDirectory() {
		if ( isset($this->config_vars['cache']['dir']) AND is_writable($this->config_vars['cache']['dir']) ) {
			return 0;
		}

		return 1;
	}

	function cleanCacheDirectory( $exclude_regex_filter = '\.ZIP|upgrade_staging' ) {
		global $smarty;

		if ( isset($smarty) ) {
			$smarty->clear_all_cache();
		}

		return Misc::cleanDir( $this->config_vars['cache']['dir'], TRUE, TRUE, FALSE, $exclude_regex_filter ); //Don't clean UPGRADE.ZIP file and 'upgrade_staging' directory.
	}

	function cleanOrphanFiles() {
		if ( PRODUCTION == TRUE ) {
			//Load delete file list.
			$file_list = dirname( __FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR . 'files.delete';

			if ( file_exists( $file_list ) ) {
				$file_list_data = file_get_contents( $file_list );
				$files = explode("\n", $file_list_data );
				unset($file_list_data);
				if ( is_array($files) ) {
					$i = 0;
					foreach( $files as $file ) {
						if ( $file != '' ) {
							$file = Environment::getBasePath() . str_replace( array('/', '\\'), DIRECTORY_SEPARATOR, $file ); //Prefix base path to all files.
							if ( file_exists( $file ) ) {
								if ( @dir( $file ) ) {
									Debug::Text('Deleting Orphaned Dir: '. $file, __FILE__, __LINE__, __METHOD__, 9);
									Misc::cleanDir( $file, TRUE, TRUE, TRUE );
								} else {
									Debug::Text('Deleting Orphaned File: '. $file, __FILE__, __LINE__, __METHOD__, 9);
									@unlink( $file );
								}
							} else {
								Debug::Text('Orphaned File/Dir does not exist, not deleting: '. $file, __FILE__, __LINE__, __METHOD__, 9);
							}
						}
					}
				}
			}
		}

		return TRUE;
	}

	function checkCleanCacheDirectory() {
		if ( DEPLOYMENT_ON_DEMAND == FALSE ) {
			if ( is_dir( $this->config_vars['cache']['dir'] ) ) {
				$raw_cache_files = @scandir( $this->config_vars['cache']['dir'] );

				if ( is_array($raw_cache_files) AND count($raw_cache_files) > 0 ) {
					foreach( $raw_cache_files as $cache_file ) {
						if ( $cache_file != '.' AND $cache_file != '..' AND stristr( $cache_file, '.lock') === FALSE AND stristr( $cache_file, '.ZIP') === FALSE AND stristr( $cache_file, 'upgrade_staging') === FALSE) { //Ignore UPGRADE.ZIP files.
							return 1;
						}
					}
				}
			}
		}

		return 0;
	}

	function checkWritableStorageDirectory() {
		if ( isset($this->config_vars['path']['storage']) AND is_writable($this->config_vars['path']['storage']) ) {
			return 0;
		}

		return 1;
	}

	function checkWritableLogDirectory() {
		if ( isset($this->config_vars['path']['log']) AND is_writable($this->config_vars['path']['log']) ) {
			return 0;
		}

		return 1;
	}

	function checkPHPSafeMode() {
		if ( ini_get('safe_mode') != '1' ) {
			return 0;
		}

		return 1;
	}

	function checkPHPMemoryLimit() {
		if ( $this->getMemoryLimit() == NULL OR $this->getMemoryLimit() >= 128 ) {
			return 0;
		}

		return 1;
	}

	function checkPHPMagicQuotesGPC() {
		if ( get_magic_quotes_gpc() == 1 ) {
			return 1;
		}

		return 0;
	}

	function getCurrentTimeTrexVersion() {
		//return '1.2.1';
		return APPLICATION_VERSION;
	}

	function getLatestTimeTrexVersion() {
		if ( $this->checkSOAP() == 0 ) {
			$ttsc = new TimeTrexSoapClient();
			return $ttsc->getSoapObject()->getInstallerLatestVersion();
		}

		return FALSE;
	}

	function checkTimeTrexVersion() {
		$current_version = $this->getCurrentTimeTrexVersion();
		$latest_version = $this->getLatestTimeTrexVersion();

		if ( $latest_version == FALSE ) {
			return 1;
		} elseif ( version_compare( $current_version, $latest_version, '>=') == TRUE ) {
			return 0;
		}

		return 2;
	}

	function checkAllRequirements( $post_install_requirements_only = FALSE, $exclude_check = FALSE ) {
		// Return
		//
		// 0 = OK
		// 1 = Invalid
		// 2 = Unsupported

		//Total up each OK, Invalid, and Unsupported requirements
		$retarr = array(
						0 => 0,
						1 => 0,
						2 => 0
						);

		//$retarr[1]++; //Test failed requirements.

		$retarr[$this->checkPHPVersion()]++;
		$retarr[$this->checkDatabaseType()]++;
		$retarr[$this->checkSOAP()]++;
		$retarr[$this->checkBCMATH()]++;
		$retarr[$this->checkMBSTRING()]++;
		//$retarr[$this->checkCALENDAR()]++;
		$retarr[$this->checkGETTEXT()]++;
		$retarr[$this->checkGD()]++;
		$retarr[$this->checkJSON()]++;
		$retarr[$this->checkSimpleXML()]++;
		$retarr[$this->checkZIP()]++;
		$retarr[$this->checkMAIL()]++;

		$retarr[$this->checkPEAR()]++;

		//PEAR modules are bundled as of v1.2.0
		if ( $post_install_requirements_only == FALSE ) {
			if ( !is_array( $exclude_check ) OR ( is_array($exclude_check) AND in_array('base_url', $exclude_check) == FALSE ) ) {
				$retarr[$this->checkBaseURL()]++;
			}
			if ( !is_array( $exclude_check ) OR ( is_array($exclude_check) AND in_array('php_cli', $exclude_check) == FALSE ) ) {
				$retarr[$this->checkPHPCLIBinary()]++;
				$retarr[$this->checkPHPOpenBaseDir()]++;
			}
			if ( !is_array( $exclude_check ) OR ( is_array($exclude_check) AND in_array('php_cli_requirements', $exclude_check) == FALSE ) ) {
				$retarr[$this->checkPHPCLIRequirements()]++;
			}
			$retarr[$this->checkWritableConfigFile()]++;
			$retarr[$this->checkWritableCacheDirectory()]++;
			if ( !is_array( $exclude_check ) OR ( is_array($exclude_check) AND in_array('clean_cache', $exclude_check) == FALSE ) ) {
				$retarr[$this->checkCleanCacheDirectory()]++;
			}
			$retarr[$this->checkWritableStorageDirectory()]++;
			$retarr[$this->checkWritableLogDirectory()]++;
			if ( !is_array( $exclude_check ) OR ( is_array($exclude_check) AND in_array('file_permissions', $exclude_check) == FALSE ) ) {
				$retarr[$this->checkFilePermissions()]++;
			}
			if ( !is_array( $exclude_check ) OR ( is_array($exclude_check) AND in_array('file_checksums', $exclude_check) == FALSE ) ) {
				$retarr[$this->checkFileChecksums()]++;
			}
		}

		$retarr[$this->checkPHPSafeMode()]++;
		$retarr[$this->checkPHPMemoryLimit()]++;
		$retarr[$this->checkPHPMagicQuotesGPC()]++;

		if ( $this->getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$retarr[$this->checkPEARValidate()]++;
			$retarr[$this->checkMCRYPT()]++;
		}

		//Debug::Arr($retarr, 'RetArr: ', __FILE__, __LINE__, __METHOD__, 9);

		if ( $retarr[1] > 0 ) {
			return 1;
		} elseif ( $retarr[2] > 0 ) {
			return 2;
		} else {
			return 0;
		}
	}

	function getFailedRequirements( $post_install_requirements_only = FALSE, $exclude_check = FALSE ) {
		$fail_all = FALSE;

		$retarr[] = 'Require';

		if ( $fail_all == TRUE OR $this->checkPHPVersion() != 0 ) {
			$retarr[] = 'PHPVersion';
		}

		if ( $fail_all == TRUE OR $this->checkDatabaseType() != 0 ) {
			$retarr[] = 'DatabaseType';
		}

		if ( $fail_all == TRUE OR $this->checkSOAP() != 0 ) {
			$retarr[] = 'SOAP';
		}

		if ( $fail_all == TRUE OR $this->checkBCMATH() != 0 ) {
			$retarr[] = 'BCMATH';
		}

		if ( $fail_all == TRUE OR $this->checkMBSTRING() != 0 ) {
			$retarr[] = 'MBSTRING';
		}

		//if ( $fail_all == TRUE OR $this->checkCALENDAR() != 0 ) {
		//	$retarr[] = 'CALENDAR';
		//}

		if ( $fail_all == TRUE OR $this->checkGETTEXT() != 0 ) {
			$retarr[] = 'GETTEXT';
		}

		if ( $fail_all == TRUE OR $this->checkGD() != 0 ) {
			$retarr[] = 'GD';
		}

		if ( $fail_all == TRUE OR $this->checkJSON() != 0 ) {
			$retarr[] = 'JSON';
		}

		if ( $fail_all == TRUE OR $this->checkSimpleXML() != 0 ) {
			$retarr[] = 'SIMPLEXML';
		}

		if ( $fail_all == TRUE OR $this->checkMAIL() != 0 ) {
			$retarr[] = 'MAIL';
		}

		if ( $fail_all == TRUE OR $this->checkZIP() != 0 ) {
			$retarr[] = 'ZIP';
		}

		//Bundled PEAR modules require the base PEAR package at least
		if ( $fail_all == TRUE OR $this->checkPEAR() != 0 ) {
			$retarr[] = 'PEAR';
		}

		if ( $post_install_requirements_only == FALSE ) {
			if ( is_array($exclude_check) AND in_array('base_url', $exclude_check) == FALSE ) {
				if ( $fail_all == TRUE OR $this->checkBaseURL() != 0 ) {
					$retarr[] = 'BaseURL';
				}
			}
			if ( is_array($exclude_check) AND in_array('php_cli', $exclude_check) == FALSE ) {
				if ( $fail_all == TRUE OR $this->checkPHPCLIBinary() != 0 ) {
					$retarr[] = 'PHPCLI';
				}
				if ( $fail_all == TRUE OR $this->checkPHPOpenBaseDir() != 0 ) {
					$retarr[] = 'PHPOpenBaseDir';
				}
			}
			if ( is_array($exclude_check) AND in_array('php_cli_requirements', $exclude_check) == FALSE ) {
				if ( $fail_all == TRUE OR $this->checkPHPCLIRequirements() != 0 ) {
					$retarr[] = 'PHPCLIReq';
				}
			}

			if ( $fail_all == TRUE OR $this->checkWritableConfigFile() != 0 ) {
				$retarr[] = 'WConfigFile';
			}
			if ( $fail_all == TRUE OR $this->checkWritableCacheDirectory() != 0 ) {
				$retarr[] = 'WCacheDir';
			}
			if ( is_array($exclude_check) AND in_array('clean_cache', $exclude_check) == FALSE ) {
				if ( $fail_all == TRUE OR $this->checkCleanCacheDirectory() != 0 ) {
					$retarr[] = 'CleanCacheDir';
				}
			}
			if ( $fail_all == TRUE OR $this->checkWritableStorageDirectory() != 0 ) {
				$retarr[] = 'WStorageDir';
			}
			if ( $fail_all == TRUE OR $this->checkWritableLogDirectory() != 0 ) {
				$retarr[] = 'WLogDir';
			}
			if ( is_array($exclude_check) AND in_array('file_permissions', $exclude_check) == FALSE ) {
				if ( $fail_all == TRUE OR $this->checkFilePermissions() != 0 ) {
					$retarr[] = 'WFilePermissions';
				}
			}
			if ( is_array($exclude_check) AND in_array('file_checksums', $exclude_check) == FALSE  ) {
				if ( $fail_all == TRUE OR $this->checkFileChecksums() != 0 ) {
					$retarr[] = 'WFileChecksums';
				}
			}
		}

		if ( $fail_all == TRUE OR $this->checkPHPSafeMode() != 0 ) {
			$retarr[] = 'PHPSafeMode';
		}
		if ( $fail_all == TRUE OR $this->checkPHPMemoryLimit() != 0 ) {
			$retarr[] = 'PHPMemoryLimit';
		}
		if ( $fail_all == TRUE OR $this->checkPHPMagicQuotesGPC() != 0 ) {
			$retarr[] = 'PHPMagicQuotesGPC';
		}

		if ( $fail_all == TRUE OR $this->getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			if ( $fail_all == TRUE OR $this->checkPEARValidate() != 0 ) {
				$retarr[] = 'PEARVal';
			}

			if ( $fail_all == TRUE OR $this->checkMCRYPT() != 0 ) {
				$retarr[] = 'MCRYPT';
			}
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}
}
?>
