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
 * @package Core
 */
class TTLDAP {
	var $data = NULL;
	private $password_attribute = 'userPassword';
	//private $password_attribute = NULL;

	function __construct() {
		global $LDAP_CONNECT_OPTIONS;

		if ( $this->checkLDAPExtension() == TRUE ) {
			$LDAP_CONNECT_OPTIONS = array(
					array('OPTION_NAME' => LDAP_OPT_DEREF, 'OPTION_VALUE' => 2 ),
					array('OPTION_NAME' => LDAP_OPT_SIZELIMIT, 'OPTION_VALUE' => 100 ),
					array('OPTION_NAME' => LDAP_OPT_TIMELIMIT, 'OPTION_VALUE' => 30 ),
					array('OPTION_NAME' => LDAP_OPT_PROTOCOL_VERSION, 'OPTION_VALUE' => 3 ),
					array('OPTION_NAME' => LDAP_OPT_ERROR_NUMBER, 'OPTION_VALUE' => 13 ),
					array('OPTION_NAME' => LDAP_OPT_REFERRALS, 'OPTION_VALUE' => FALSE ),
					array('OPTION_NAME' => LDAP_OPT_RESTART, 'OPTION_VALUE' => FALSE )
			);
		}

		return TRUE;
	}

	function getHost() {
		if ( isset($this->data['host']) ) {
			return $this->data['host'];
		}

		return FALSE;
	}
	function setHost($value) {
		$value = trim($value);

		$this->data['host'] = $value;
		return TRUE;
	}

	function getPort() {
		if ( isset($this->data['port']) ) {
			return $this->data['port'];
		}

		return 389; //Default port.
	}
	function setPort($value) {
		$this->data['port'] = $value;
		return TRUE;
	}

	function getBindUserName() {
		if ( isset($this->data['bind_user_name']) ) {
			return $this->data['bind_user_name'];
		}

		return FALSE;
	}
	function setBindUserName($value) {
		$value = trim($value);

		$this->data['bind_user_name'] = $value;
		return TRUE;
	}

	function getBindPassword() {
		if ( isset($this->data['bind_password']) ) {
			return $this->data['bind_password'];
		}

		return FALSE;
	}
	function setBindPassword($value) {
		$this->data['bind_password']  = trim($value);

		return TRUE;
	}

	function getBaseDN() {
		if ( isset($this->data['base_dn']) ) {
			return $this->data['base_dn'];
		}

		return FALSE;
	}
	function setBaseDN($value) {
		$value = trim($value);

		$this->data['base_dn'] = $value;
		return TRUE;
	}

	function getBindAttribute() {
		if ( isset($this->data['bind_attribute']) ) {
			return $this->data['bind_attribute'];
		}

		return FALSE;
	}
	function setBindAttribute($value) {
		$value = trim($value);

		$this->data['bind_attribute'] = $value;
		return TRUE;
	}

	function getUserFilter() {
		if ( isset($this->data['user_filter']) ) {
			return $this->data['user_filter'];
		}

		return FALSE;
	}
	function setUserFilter($value) {
		$value = trim($value);

		$this->data['user_filter'] = $value;
		return TRUE;
	}

	function getLoginAttribute() {
		if ( isset($this->data['login_attribute']) ) {
			return $this->data['login_attribute'];
		}

		return FALSE;
	}
	function setLoginAttribute($value) {
		$value = trim($value);

		$this->data['login_attribute'] = $value;
		return TRUE;
	}

	//This is not fully implemented, not sure if its even needed, as it appears
	//most active directory installs will work without needing to specify the domain.
	function getUserNameSuffix() {
		if ( isset($this->data['user_name_suffix']) ) {
			return $this->data['user_name_suffix'];
		}

		return FALSE;
	}
	function setUserNameSuffix($value) {
		$value = trim($value);

		$this->data['user_name_suffix'] = $value;
		return TRUE;
	}

	function checkLDAPExtension() {
		if ( function_exists('ldap_connect') ) {
			return TRUE;
		}

		return FALSE;
	}

	//Bind authentication is when a specific bind User/Password is *not* specified,
	//so we try to initially bind as the username trying to login instead.
	//However when bind username/password is specified, we can still attempt to bind as the username trying to login after a filter query is run.
	function isBindAuthentication() {
		if ( $this->getBindUserName() == '' ) { //Don't check password, as anonymous binding doesn't have one specified.
			return TRUE;
		}

		return FALSE;
	}

	function getFilterQuery( $user_name ) {
		$filter_query = '';
		$filter_count = 0;

		if ( $this->getLoginAttribute() != '' ) {
			$filter_query = '('.$this->getLoginAttribute().'='. $user_name.')';
			$filter_count++;
		}

		if ( $this->getUserFilter() != '' ) {
			$filter_query .= '('.$this->getUserFilter().')';
			$filter_count++;
		}

		if (  $filter_count > 1 ) {
			$filter_query = '(&'.$filter_query.')';
		}

		return $filter_query;
	}

	function getBindDN( $user_name ) {
		//return $this->getBindAttribute().'='. $user_name .', '.$this->getBaseDN();
		$retval = '';
		if ( $this->getBindAttribute() != '' ) {
			$retval .= $this->getBindAttribute().'=';
		}

		$retval .= $user_name .', '.$this->getBaseDN();

		return $retval;
	}

	function authenticate( $user_name, $password ) {
		$user_name = trim($user_name);
		$password = trim($password);

		$authentication_start_time = microtime(TRUE);
		$retval = FALSE;
		if ( $this->checkLDAPExtension() == TRUE ) {
			$connection_result = FALSE;
			$ldap_data = FALSE;

			$ldap = NewADOConnection( 'ldap' );
			$ldap->port = $this->getPort(); //If port is 636, use SSL instead.

			Debug::Text('LDAP: Host: '. $this->getHost() .' Port: '. $this->getPort() .' Bind User Name: '. $this->getBindUserName() .' Bind Password: '. $this->getBindPassword() .' Bind DN: '. $this->getBindDN( $user_name ) .' Base DN: '. $this->getBaseDN() .' Bind Authentication Mode: '. (int)$this->isBindAuthentication() .' Password: '. $password, __FILE__, __LINE__, __METHOD__, 10);
			try {
				//Poor mans timeout if we aren't using PHP v5.3 (which supports a built-in LDAP timeout setting)
				$fp = @fsockopen( str_ireplace('ldaps://', '', $this->getHost() ), $this->getPort(), $errno, $errstr, 3);
				if ( $fp == FALSE ) {
					Debug::Text('LDAP socket connection failed/timedout: '. $errstr .' ('.$errno.')', __FILE__, __LINE__, __METHOD__, 10);
				} else {
					fclose($fp);
					if ( $this->isBindAuthentication() == TRUE ) {
						try {
							Debug::Text('aLDAP Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
							//Attempt to connect with the raw user_name first, if that fails, try with a full BindDN
							$connection_result = $ldap->Connect( $this->getHost(), $user_name, $password, $this->getBaseDN() );
						} catch ( exception $e ) {
							Debug::Text('bLDAP Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
							$connection_result = $ldap->Connect( $this->getHost(), $this->getBindDN( $user_name ), $password, $this->getBaseDN() );
						}
					} else {
						if ( strtolower( $this->getBindUserName() ) == 'anonymous' ) {
							Debug::Text('LDAP Anonymous Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
							$connection_result = $ldap->Connect( $this->getHost(), '', '', $this->getBaseDN() );
						} else {
							Debug::Text('LDAP BindUser Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
							$connection_result = $ldap->Connect( $this->getHost(), $this->getBindUserName(), $this->getBindPassword(), $this->getBaseDN() );
						}
					}
					Debug::Text('LDAP Connection Result: '. (int)$connection_result, __FILE__, __LINE__, __METHOD__, 10);
				}
			} catch ( exception $e ) {
				Debug::Text('LDAP Connection Failed!: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
			}

			$filter = $this->getFilterQuery( $user_name );
			Debug::Text('LDAP Filter User: '. $filter, __FILE__, __LINE__, __METHOD__, 10);

			if ( $connection_result == TRUE AND $filter != '' ) {
				$ldap->SetFetchMode(ADODB_FETCH_ASSOC);

				try {
					$rs = $ldap->Execute( $filter );
					$ldap_data = $ldap->GetRow( $filter );

					if ( is_array($ldap_data) AND count($ldap_data) > 0 ) {

						if ( $this->getLoginAttribute() != '' AND isset($ldap_data[$this->getLoginAttribute()]) ) {

							//Usernames are passed here in lowercase already, so it should be case insensitive match.
							if ( strtolower($ldap_data[$this->getLoginAttribute()]) == strtolower($user_name) ) {
								//Try to compare plain text password if it exists
								if ( isset($ldap_data[$this->password_attribute]) ) {
									if ( $ldap_data[$this->password_attribute] == $password ) {
										Debug::Text('LDAP authentication success! (z)', __FILE__, __LINE__, __METHOD__, 10);
										$retval = TRUE;
									} else {
										Debug::Text('LDAP password comparison failed... LDAP Password attribute: '. $ldap_data[$this->password_attribute], __FILE__, __LINE__, __METHOD__, 10);
									}
								} else {
									//If no password attribute exists, and we're using bind authentication, still return true as we've matched the password and user filter
									Debug::Text('LDAP no password attribute exists...', __FILE__, __LINE__, __METHOD__, 10);
									if ( $this->isBindAuthentication() == TRUE ) {
										Debug::Text('LDAP matched filter, but no password attribute exists, returning TRUE...', __FILE__, __LINE__, __METHOD__, 10);
										$retval = TRUE;
									} else {
										//Bind authentication was not used, however we found the user with no password attribute, so attempt
										//to rebind with the discovered bindAttribute to test the password for cases where it may include a fully qualified domain.
										//This should avoid the need to suffix the user_name with '@mydomain.com'
										if ( isset($ldap_data[$this->getBindAttribute()]) AND $ldap_data[$this->getBindAttribute()] != '' ) {
											try {
												Debug::Text('aLDAP post-search Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
												//Attempt to connect with the raw post-search filter data first, if that fails, try with a full BindDN
												$retval = $ldap->Connect( $this->getHost(), $ldap_data[$this->getBindAttribute()], $password, $this->getBaseDN() );
											} catch ( exception $e ) {
												Debug::Text('bLDAP post-search Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
												$retval = $ldap->Connect( $this->getHost(), $this->getBindDN( $ldap_data[$this->getBindAttribute()] ), $password, $this->getBaseDN() );
											}
											Debug::Text('LDAP post-search bind connection result: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
										} else {
											Debug::Text('BindAttribute not found in users LDAP record...', __FILE__, __LINE__, __METHOD__, 10);
										}
									}
								}
							} else {
								Debug::Text('LDAP Login Attribute does not match user name...', __FILE__, __LINE__, __METHOD__, 10);
							}
						} else {
							Debug::Text('LDAP Login Attribute not found or not set...', __FILE__, __LINE__, __METHOD__, 10);
							if ( $this->isBindAuthentication() == TRUE ) {
								Debug::Text('LDAP matched filter, but no login attribute exists, returning TRUE...', __FILE__, __LINE__, __METHOD__, 10);
								$retval = TRUE;
							}
						}

					} else {
						Debug::Text('LDAP Filter data not found...', __FILE__, __LINE__, __METHOD__, 10);
					}

					if ( $retval == FALSE ) {
						Debug::Arr($ldap_data, 'LDAP Data: ', __FILE__, __LINE__, __METHOD__, 10);
					}
				} catch ( exception $e ) {
					Debug::Text('LDAP Filter Failed!: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
				}
			} elseif ( $this->isBindAuthentication() === TRUE AND $connection_result === TRUE ) {
				Debug::Text('LDAP bind authentication success! (a)', __FILE__, __LINE__, __METHOD__, 10);
				$retval = TRUE;
			}

			$ldap->Close();
		}

		Debug::Text('LDAP authentication result: '. (int)$retval .' Total Time: '. (microtime(TRUE) - $authentication_start_time) .'s', __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

}
?>
