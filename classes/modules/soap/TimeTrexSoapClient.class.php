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
 * @package Modules\SOAP
 */
class TimeTrexSoapClient {
	var $soap_client_obj = NULL;

	function __construct() {
		$this->getSoapObject();

		return TRUE;
	}

	function getSoapObject() {
		if ( $this->soap_client_obj == NULL ) {
			$location = 'http://www.timetrex.com/ext_soap/server.php';
			//$location = 'http://www.trunk.dev1.office.timetrex.com/ext_soap/server.php';

			$this->soap_client_obj = new SoapClient(NULL, array(
											'location' => $location,
											'uri' => 'urn:test',
											'style' => SOAP_RPC,
											'use' => SOAP_ENCODED,
											'trace' => 1,
											'exceptions' => 0
											)
									);
		}

		return $this->soap_client_obj;
	}

	function printSoapDebug() {
		echo "<pre>\n";
		echo "Request :\n".htmlspecialchars($this->getSoapObject()->__getLastRequest()) ."\n";
		echo "Response :\n".htmlspecialchars($this->getSoapObject()->__getLastResponse()) ."\n";
		echo "</pre>\n";
	}

	function ping() {
		return $this->getSoapObject()->ping();
	}

	function isUpdateNotifyEnabled() {
		global $config_vars;
		if ( getTTProductEdition() > 10 AND DEPLOYMENT_ON_DEMAND == TRUE AND isset( $config_vars['other']['enable_update_notify'] ) AND $config_vars['other']['enable_update_notify'] == FALSE ) {
			return FALSE; //Disabled with On-Demand service.
		}

		if ( getTTProductEdition() == 10 ) {
			$sslf = TTnew( 'SystemSettingListFactory' );
			$sslf->getByName('update_notify');
			if ( $sslf->getRecordCount() == 1 ) {
				$value = $sslf->getCurrent()->getValue();

				if ( $value == 0 ) {
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	function getPrimaryCompanyData() {
		global $config_vars, $db;
		
		//Make sure a database connection has been established at least, otherwise this can cause FATAL error
		//which during installation (before any database exists) is bad.
		if ( isset($db) AND is_object($db) ) {
			if ( !isset( $config_vars['other']['primary_company_id'] ) ) {
				$config_vars['other']['primary_company_id'] = 1;
			}

			try {
				$clf = TTnew( 'CompanyListFactory' );
				$clf->getById( $config_vars['other']['primary_company_id'] );
				if ( $clf->getRecordCount() > 0 ) {
					foreach( $clf as $c_obj ) {
						$company_data = array(
												'system_version' => APPLICATION_VERSION,
												'application_version_date' => APPLICATION_VERSION_DATE,
												'registration_key' => $this->getLocalRegistrationKey(),
												'product_edition_id' => $c_obj->getProductEdition(),
												'product_edition_available' => getTTProductEdition(),
												'name' => $c_obj->getName(),
												'short_name' => $c_obj->getShortName(),
												'work_phone' => $c_obj->getWorkPhone(),
												'city' => $c_obj->getCity(),
												'country' => $c_obj->getCountry(),
												'province' => $c_obj->getProvince(),
												'postal_code' => $c_obj->getPostalCode(),
											);
					}

					return $company_data;
				} else {
					Debug::Text('ERROR: Primary company does not exist: '. $config_vars['other']['primary_company_id'], __FILE__, __LINE__, __METHOD__, 10);
				}
			} catch( Exception $e ) {
				Debug::Text('ERROR: Cant get company data for downloading upgrade file, database is likely down...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return FALSE;
	}

	function isLatestVersion( $company_id ) {
		$sslf = TTnew( 'SystemSettingListFactory' );
		$sslf->getByName('system_version');
		if ( $sslf->getRecordCount() == 1 ) {
			$version = $sslf->getCurrent()->getValue();

			$retval = $this->getSoapObject()->isLatestVersion( $this->getLocalRegistrationKey(), $company_id, $version);
			Debug::Text(' Current Version: '. $version .' Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}

		return FALSE;
	}

	function isLatestTaxEngineVersion( $company_id ) {
		$sslf = TTnew( 'SystemSettingListFactory' );
		$sslf->getByName('tax_engine_version');
		if ( $sslf->getRecordCount() == 1 ) {
			$version = $sslf->getCurrent()->getValue();

			$retval = $this->getSoapObject()->isLatestTaxEngineVersion( $this->getLocalRegistrationKey(), $company_id, $version);
			Debug::Text(' Current Version: '. $version .' Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}

		return FALSE;
	}

	function isLatestTaxDataVersion( $company_id ) {
		$sslf = TTnew( 'SystemSettingListFactory' );
		$sslf->getByName('tax_data_version');
		if ( $sslf->getRecordCount() == 1 ) {
			$version = $sslf->getCurrent()->getValue();

			$retval = $this->getSoapObject()->isLatestTaxDataVersion( $this->getLocalRegistrationKey(), $company_id, $version);
			Debug::Text(' Current Version: '. $version .' Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}

		return FALSE;
	}

	function isValidRegistrationKey( $key ) {
		$key = trim($key);
		if ( strlen( $key ) == 32 OR strlen( $key ) == 40 ) {
			return TRUE;
		}

		return FALSE;
	}

	function getLocalRegistrationKey() {
		$key = FALSE;

		$sslf = TTnew( 'SystemSettingListFactory' );
		$sslf->getByName('registration_key');
		if ( $sslf->getRecordCount() == 1 ) {
			$key = $sslf->getCurrent()->getValue();
		}

		//If key is invalid, attempt to obtain a new one.
		if ( $this->isValidRegistrationKey( $key ) == FALSE ) {
			$this->saveRegistrationKey();
			return FALSE;
		}

		return $key;
	}
	function getRegistrationKey() {
		return $this->getSoapObject()->generateRegistrationKey();
	}

	function saveRegistrationKey() {
		$sslf = TTnew( 'SystemSettingListFactory' );
		$sslf->getByName('registration_key');

		$get_new_key = FALSE;
		if ( $sslf->getRecordCount() > 1 ) {
			Debug::Text('Too many registration keys, removing them...', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $sslf as $ss_obj ) {
				$ss_obj->Delete();
			}
			$get_new_key = TRUE;
		} elseif ( $sslf->getRecordCount() == 1 ) {
			$key = $sslf->getCurrent()->getValue();
			if ( $this->isValidRegistrationKey( $key ) == FALSE ) {
				foreach( $sslf as $ss_obj ) {
					$ss_obj->Delete();
				}
				$get_new_key = TRUE;
			}
		}

		if ( $get_new_key == TRUE OR $sslf->getRecordCount() == 0 ) {
			//Get registration key from TimeTrex server.
			$key = trim( $this->getRegistrationKey() );
			Debug::Text('Registration Key from server: '. $key, __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->isValidRegistrationKey( $key ) == FALSE ) {
				$key = md5( uniqid() );
				Debug::Text('Failed getting registration key from server...', __FILE__, __LINE__, __METHOD__, 10);
			}

			$sslf->setName('registration_key');
			$sslf->setValue( $key );
			if ( $sslf->isValid() == TRUE ) {
				$sslf->Save();
			}

			return TRUE;
		} else {
			Debug::Text('Registration key is valid, skipping...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	function sendCompanyVersionData( $company_id ) {
		Debug::Text('Sending Company Version Data...', __FILE__, __LINE__, __METHOD__, 10);
		$cf = TTnew( 'CompanyFactory' );

		$tt_version_data['registration_key'] = $this->getLocalRegistrationKey();
		$tt_version_data['company_id'] = $company_id;

		$sslf = TTnew( 'SystemSettingListFactory' );
		$sslf->getByName('system_version');
		if ( $sslf->getRecordCount() == 1 ) {
			$tt_version_data['system_version'] = $sslf->getCurrent()->getValue();
		}

		$sslf->getByName('tax_engine_version');
		if ( $sslf->getRecordCount() == 1 ) {
			$tt_version_data['tax_engine_version'] = $sslf->getCurrent()->getValue();
		}

		$sslf->getByName('tax_data_version');
		if ( $sslf->getRecordCount() == 1 ) {
			$tt_version_data['tax_data_version'] = $sslf->getCurrent()->getValue();
		}

		$sslf->getByName('schema_version_group_A');
		if ( $sslf->getRecordCount() == 1 ) {
			$tt_version_data['schema_version']['A'] = $sslf->getCurrent()->getValue();
		}
		$sslf->getByName('schema_version_group_B');
		if ( $sslf->getRecordCount() == 1 ) {
			$tt_version_data['schema_version']['B'] = $sslf->getCurrent()->getValue();
		}
		$sslf->getByName('schema_version_group_T');
		if ( $sslf->getRecordCount() == 1 ) {
			$tt_version_data['schema_version']['T'] = $sslf->getCurrent()->getValue();
		}

		if ( isset($_SERVER['SERVER_SOFTWARE']) ) {
			$server_software = $_SERVER['SERVER_SOFTWARE'];
		} else {
			$server_software = 'N/A';
		}
		if ( isset($_SERVER['SERVER_NAME']) ) {
			$server_name = $_SERVER['SERVER_NAME'];
		} else {
			$server_name = Misc::getHostName();
		}

		$db_server_info = $cf->db->ServerInfo();
		$sys_version_data = array(
							'php_version' => phpversion(),
							'zend_version' => zend_version(),
							'web_server' => $server_software,
							'database_type' => $cf->db->databaseType,
							'database_version' => $db_server_info['version'],
							'database_description' => $db_server_info['description'],
							'server_name' => $server_name,
							'base_url' => Environment::getBaseURL(),
							'php_os' => PHP_OS,
							'system_information' => php_uname()
							);

		$version_data = array_merge( $tt_version_data, $sys_version_data);

		if ( isset($version_data) AND is_array( $version_data) ) {
			Debug::Text('Sent Company Version Data!', __FILE__, __LINE__, __METHOD__, 10);
			$retval = $this->getSoapObject()->saveCompanyVersionData( $version_data );

			if ( $retval == FALSE ) {
				Debug::Text('Server failed saving data!', __FILE__, __LINE__, __METHOD__, 10);
			}
			//$this->printSoapDebug();

			return $retval;
		}
		Debug::Text('NOT Sending Company Version Data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function sendCompanyUserCountData( $company_id ) {
		$cuclf = TTnew( 'CompanyUserCountListFactory' );
		$cuclf->getActiveUsers();
		if ( $cuclf->getRecordCount() > 0 ) {
			foreach( $cuclf as $cuc_obj ) {
				$user_counts[$cuc_obj->getColumn('company_id')]['active'] = $cuc_obj->getColumn('total');
			}
		}

		$cuclf->getInActiveUsers();
		if ( $cuclf->getRecordCount() > 0 ) {
			foreach( $cuclf as $cuc_obj ) {
				$user_counts[$cuc_obj->getColumn('company_id')]['inactive'] = $cuc_obj->getColumn('total');
			}
		}

		$cuclf->getDeletedUsers();
		if ( $cuclf->getRecordCount() > 0 ) {
			foreach( $cuclf as $cuc_obj ) {
				$user_counts[$cuc_obj->getColumn('company_id')]['deleted'] = $cuc_obj->getColumn('total');
			}
		}

		if ( isset($user_counts[$company_id]) ) {
			$user_counts[$company_id]['registration_key'] = $this->getLocalRegistrationKey();
			$user_counts[$company_id]['company_id'] = $company_id;

			return $this->getSoapObject()->saveCompanyUserCountData( $user_counts[$company_id] );
		}

		return FALSE;
	}

	function sendCompanyUserLocationData( $company_id ) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		$clf = TTnew( 'CompanyListFactory' );
		$clf->getById( $company_id );
		if ( $clf->getRecordCount() > 0 ) {

			$location_data['registration_key'] = $this->getLocalRegistrationKey();
			$location_data['company_id'] = $company_id;

			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByCompanyId( $company_id );
			if ( $ulf->getRecordCount() > 0 ) {
				foreach( $ulf as $u_obj ) {

					$key = str_replace(' ', '', strtolower( $u_obj->getCity().$u_obj->getCity().$u_obj->getCountry() ) );

					$location_data['location_data'][$key] = array(
														'city' => $u_obj->getCity(),
														'province' => $u_obj->getProvince(),
														'country' => $u_obj->getCountry()
															);
				}

				if ( isset($location_data['location_data']) ) {
					return $this->getSoapObject()->saveCompanyUserLocationData( $location_data );
				}
			}

		}

		return FALSE;
	}

	function sendCompanyData( $company_id, $force = FALSE ) {
		Debug::Text('Sending Company Data...', __FILE__, __LINE__, __METHOD__, 10);
		if ( $company_id == '' ) {
			return FALSE;
		}

		//Check for anonymous update notifications
		$anonymous_update_notify = 0;
		if ( $force == FALSE OR getTTProductEdition() == 10 ) {
			$sslf = TTnew( 'SystemSettingListFactory' );
			$sslf->getByName('anonymous_update_notify');
			if ( $sslf->getRecordCount() == 1 ) {
				$anonymous_update_notify = $sslf->getCurrent()->getValue();
			}
		}

		$obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; @$obj = new $obj_class; $hardware_id = $obj->getHardwareID(); unset($obj, $obj_class);

		$clf = TTnew( 'CompanyListFactory' );
		$clf->getById( $company_id );
		if ( $clf->getRecordCount() > 0 ) {
			foreach( $clf as $c_obj ) {
				$company_data['id'] = $c_obj->getId();
				$company_data['production'] = PRODUCTION;
				$company_data['registration_key'] = $this->getLocalRegistrationKey();
				$company_data['hardware_id'] = $hardware_id;
				$company_data['status_id'] = $c_obj->getStatus();
				$company_data['application_name'] = APPLICATION_NAME;
				$company_data['product_edition_id'] = $c_obj->getProductEdition();
				$company_data['is_professional_edition_available'] = getTTProductEdition();
				$company_data['product_edition_available'] = getTTProductEdition();
				$company_data['industry_id'] = $c_obj->getIndustry();

				if ( $anonymous_update_notify == 0 ) {
					$company_data['name'] = $c_obj->getName();
					$company_data['short_name'] = $c_obj->getShortName();
					$company_data['business_number'] = $c_obj->getBusinessNumber();
					$company_data['address1'] = $c_obj->getAddress1();
					$company_data['address2'] = $c_obj->getAddress2();
					$company_data['work_phone'] = $c_obj->getWorkPhone();
					$company_data['fax_phone'] = $c_obj->getFaxPhone();

					$ulf = TTnew( 'UserListFactory' );
					if ( $c_obj->getBillingContact() != '' ) {
						$ulf->getById( $c_obj->getBillingContact() );
						if ( $ulf->getRecordCount() == 1 ) {
							$u_obj = $ulf->getCurrent();
							if ( $u_obj->getWorkEmail() != '' ) {
								$email = $u_obj->getWorkEmail();
							} else {
								$email = $u_obj->getHomeEmail();
							}
							$company_data['billing_contact'] = '"'.$u_obj->getFullName().'" <'. $email .'>';
						}
					}
					if ( $c_obj->getAdminContact() != '' ) {
						$ulf->getById( $c_obj->getAdminContact() );
						if ( $ulf->getRecordCount() == 1 ) {
							$u_obj = $ulf->getCurrent();
							if ( $u_obj->getWorkEmail() != '' ) {
								$email = $u_obj->getWorkEmail();
							} else {
								$email = $u_obj->getHomeEmail();
							}
							$company_data['admin_contact'] = '"'.$u_obj->getFullName().'" <'. $email .'>';
						}
					}
					if ( $c_obj->getSupportContact() != '' ) {
						$ulf->getById( $c_obj->getSupportContact() );
						if ( $ulf->getRecordCount() == 1 ) {
							$u_obj = $ulf->getCurrent();
							if ( $u_obj->getWorkEmail() != '' ) {
								$email = $u_obj->getWorkEmail();
							} else {
								$email = $u_obj->getHomeEmail();
							}
							$company_data['support_contact'] = '"'.$u_obj->getFullName().'" <'. $email .'>';
						}
					}

					$logo_file = $c_obj->getLogoFileName( $c_obj->getId(), FALSE ); //Ignore default logo
					if ( $logo_file != '' AND file_exists( $logo_file ) ) {
						$company_data['logo'] = array('file_name' => $logo_file, 'data' => base64_encode( file_get_contents($logo_file) ) );
					}
				}

				$company_data['city'] = $c_obj->getCity();
				$company_data['country'] = $c_obj->getCountry();
				$company_data['province'] = $c_obj->getProvince();
				$company_data['postal_code'] = $c_obj->getPostalCode();

				//Get Last user login date.
				$ulf = TTnew('UserListFactory');
				$ulf->getByCompanyId( $company_id, 1, NULL, array( 'last_login_date' => 'is not null' ), array( 'last_login_date' => 'desc' ) );
				if ( $ulf->getRecordCount() == 1 ) {
					$company_data['last_login_date'] = $ulf->getCurrent()->getLastLoginDate();
				}
				//Get Last Punch Date (before today). Use PunchControl table only as its much faster.
				$plf = TTnew('PunchControlListFactory');
				$plf->getByCompanyId( $company_id, 1, NULL, array( array('date_stamp' => ">= '". $plf->db->BindTimeStamp( TTDate::getBeginDayEpoch( time() - (86400 * 30) ) )."'") , array( 'date_stamp' => "<= '". $plf->db->BindTimeStamp( TTDate::getEndDayEpoch( time() ) )."'" ) ), array( 'date_stamp' => 'desc' ) );
				if ( $plf->getRecordCount() == 1 ) {
					$company_data['last_punch_date'] = $plf->getCurrent()->getDateStamp();
				}
				//Get Last Schedule Date (before today)
				$slf = TTnew('ScheduleListFactory');
				$slf->getByCompanyId( $company_id, 1, NULL, array( array('date_stamp' => ">= '". $slf->db->BindTimeStamp( TTDate::getBeginDayEpoch( time() - (86400 * 30) ) )."'") , array( 'date_stamp' => "<= '". $slf->db->BindTimeStamp( TTDate::getEndDayEpoch( time() ) )."'" ) ), array( 'date_stamp' => 'desc' ) );
				if ( $slf->getRecordCount() == 1 ) {
					$company_data['last_schedule_date'] = $slf->getCurrent()->getStartTime();
				}
				//Get Last Pay Stub Date (before today)
				$pslf = TTnew('PayStubListFactory');
				$pslf->getByCompanyId( $company_id, 1, NULL, array( array('a.start_date' => ">= '". $pslf->db->BindTimeStamp( TTDate::getBeginDayEpoch( time() - (86400 * 30) ) )."'") , array( 'a.start_date' => "<= '". $pslf->db->BindTimeStamp( TTDate::getEndDayEpoch( time() ) )."'" ) ), array( 'a.start_date' => 'desc' ) );
				if ( $pslf->getRecordCount() == 1 ) {
					$company_data['last_pay_stub_date'] = $pslf->getCurrent()->getEndDate();
				}
				//Get Last Review Date (before today)
				$rclf = TTnew('UserReviewControlListFactory');
				$rclf->getByCompanyId( $company_id, 1, NULL, array( array('a.created_date' => ">= ". TTDate::getBeginDayEpoch( time() - (86400 * 30) ) ) , array( 'a.created_date' => "<= ". TTDate::getEndDayEpoch( time() )  ) ), array( 'a.created_date' => 'desc' ) );
				if ( $rclf->getRecordCount() == 1 ) {
					$company_data['last_user_review_date'] = $rclf->getCurrent()->getCreatedDate();
				}

				Debug::Text('Sent Company Data...', __FILE__, __LINE__, __METHOD__, 10);
				$retval = $this->getSoapObject()->saveCompanyData( $company_data );
				//$this->printSoapDebug();

				if ( is_array($retval) ) {
					foreach( $retval as $command => $command_data ) {
						Debug::Text('Running Command: '. $command, __FILE__, __LINE__, __METHOD__, 10);
						switch( strtolower($command) ) {
							case 'system_settings':
								if ( is_array($command_data) ) {
									foreach( $command_data as $name => $value ) {
										Debug::Text('Defining System Setting: '. $name, __FILE__, __LINE__, __METHOD__, 10);
										$sslf = new SystemSettingListFactory();
										$sslf->getByName( $name );
										if ( $sslf->getRecordCount() == 1 ) {
												$obj = $sslf->getCurrent();
										} else {
												$obj = new SystemSettingListFactory();
										}
										$obj->setName( $name );
										$obj->setValue( $value );
										if ( $obj->isValid() ) {
												$obj->Save();
										}
										unset($sslf, $obj);
									}
									unset($name, $value);
								}
								break;
							case 'config_file':
								if ( is_array($command_data) ) {
									Debug::Arr( $command_data, 'Defining Config File Settings: ', __FILE__, __LINE__, __METHOD__, 10);
									$install_obj = new Install();
									$install_obj->writeConfigFile( $command_data );
									unset($install_obj);
								}
								break;
							default:
								break;
						}
					}

					return TRUE;
				} else {
					return $retval;
				}
			}
		}

		return FALSE;
	}

	//
	// Currency Data Feed functions
	//
	function getCurrencyExchangeRates( $company_id, $currency_arr, $base_currency ) {
		/*

			Contact info@timetrex.com to request adding custom currency data feeds.

		*/
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( !is_array($currency_arr) ) {
			return FALSE;
		}

		if ( $base_currency == '' ) {
			return FALSE;
		}

		$currency_rates = $this->getSoapObject()->getCurrencyExchangeRates( $this->getLocalRegistrationKey(), $company_id, $currency_arr, $base_currency );

		if ( isset($currency_rates) AND is_array($currency_rates) AND count($currency_rates) > 0 ) {
			return $currency_rates;
		}

		return FALSE;
	}

	function getCurrencyExchangeRatesByDate( $company_id, $currency_arr, $base_currency, $start_date = NULL, $end_date = NULL ) {
		/*

			Contact info@timetrex.com to request adding custom currency data feeds.

		*/
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( !is_array($currency_arr) ) {
			return FALSE;
		}

		if ( $base_currency == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			$start_date = time();
		}

		if ( $end_date == '' ) {
			$end_date = time();
		}
		
		$currency_rates = $this->getSoapObject()->getCurrencyExchangeRatesByDate( $this->getLocalRegistrationKey(), $company_id, $currency_arr, $base_currency, $start_date, $end_date );

		if ( isset($currency_rates) AND is_array($currency_rates) AND count($currency_rates) > 0 ) {
			return $currency_rates;
		}

		return FALSE;
	}


	function isNewVersionReadyForUpgrade( $force = FALSE ) {
		global $config_vars;

		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) {
			$company_data['force'] = $force;

			$retval = $this->getSoapObject()->isNewVersionReadyForUpgrade( $company_data );
			Debug::Arr( array( $company_data, $retval ), 'Checking for new version based on this data: ', __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}

		return FALSE;
	}

	function getUpgradeFileURL( $force = FALSE ) {
		global $config_vars;

		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) {
			$company_data['force'] = $force;
			
			$retval = $this->getSoapObject()->getUpgradeFileURL( $company_data );
			return $retval;
		}

		return FALSE;
	}

	//
	// Email relay through SOAP
	//
	function validateEmail( $email ) {
		global $config_vars;

		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) AND $email != '' ) {
			return $this->getSoapObject()->validateEmail( $email, $company_data );
		}

		return FALSE;
	}

	function sendEmail( $to, $headers, $body ) {
		global $config_vars;

		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) AND $to != '' AND $body != '' ) {
			$retval = $this->getSoapObject()->sendEmail( $to, $headers, $body, $company_data );
			if ( $retval === 'unsubscribe' ) {
				UserFactory::UnsubscribeEmail( $to );
				$retval = FALSE;
			}
			return $retval;
		}

		return FALSE;
	}

	function getGeoCodeByAddress( $address1, $address2, $city, $province, $country, $postal_code ) {
		global $config_vars;

		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) AND $city != '' AND $country != '' ) {
			return $this->getSoapObject()->getGeoCodeByAddress( $address1, $address2, $city, $province, $country, $postal_code, $company_data );
		}

		return NULL; //Return NULL when no data available, and FALSE to try again later.
	}


}
?>
