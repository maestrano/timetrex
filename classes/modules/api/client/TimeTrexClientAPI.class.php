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
 * $Revision: 8201 $
 * $Id: TimeTrexClientAPI.php 8201 2006-07-07 23:40:12Z root $
 * $Date: 2006-07-07 16:40:12 -0700 (Fri, 07 Jul 2006) $
 */

/**
 * @package API\TimeTrexClientAPI
 */
class TimeTrexClientAPI {
	protected $url = 'http://demo.timetrex.com/api/soap/api.php';
	protected $session_id = NULL;
	protected $session_hash = NULL; //Used to determine if we need to login again because the URL or Session changed.
	protected $class_factory = NULL;
	protected $namespace = 'urn:api';
	protected $protocol_version = 1;

	function __construct($class = NULL, $url = NULL, $session_id = NULL) {
		global $TIMETREX_URL, $TIMETREX_SESSION_ID;

		ini_set('default_socket_timeout', 3600);

		if ( $url == '' ) {
			$url = $TIMETREX_URL;
		}

		if ( $session_id == '' ) {
			$session_id = $TIMETREX_SESSION_ID;
		}

		$this->setURL( $url );
		$this->setSessionId( $session_id );
		$this->setClass( $class );

		return TRUE;
	}

	function getSoapClientObject() {
		global $TIMETREX_BASIC_AUTH_USER, $TIMETREX_BASIC_AUTH_PASSWORD;

		if ( $this->session_id != '' ) {
			$url_pieces[] = 'SessionID='. $this->session_id;
		}

		$url_pieces[] = 'Class='. $this->class_factory;

		if ( strpos( $this->url, '?' ) === FALSE ) {
			$url_separator = '?';
		} else {
			$url_separator = '&';
		}

		$url = $this->url.$url_separator.'v='. $this->protocol_version .'&'. implode('&', $url_pieces);

		$retval = new SoapClient(NULL, array(
								'location' => $url,
								'uri' => $this->namespace,
								'style' => SOAP_RPC,
								'use' => SOAP_ENCODED,
								'login' => $TIMETREX_BASIC_AUTH_USER,
								'password' => $TIMETREX_BASIC_AUTH_PASSWORD,
								//'connection_timeout' => 120,
								//'request_timeout' => 3600,
								'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
								'trace' => 1,
								'exceptions' => 0
								)
						);

		return $retval;
	}

	function setURL( $url ) {
		if ( $url != '' ) {
			$this->url = $url;
			return TRUE;
		}

		return FALSE;
	}

	function setSessionID( $value ) {
		if ( $value != '' ) {
			global $TIMETREX_SESSION_ID;
			$this->session_id = $TIMETREX_SESSION_ID = $value;
			return TRUE;
		}

		return FALSE;
	}

	function setClass( $value ) {
		$this->class_factory = trim($value);

		return TRUE;
	}

	//Use the SessionHash to ensure the URL for the session doesn't get changed out from under us without re-logging in.
	function calcSessionHash( $url, $session_id ) {
		return md5( trim($url) . trim($session_id) );
	}
	function getSessionHash() {
		return $this->session_hash;
	}
	private function setSessionHash() {
		global $TIMETREX_SESSION_HASH;
		$this->session_hash = $TIMETREX_SESSION_HASH = $this->calcSessionHash( $this->url, $this->session_id );
		return TRUE;
	}

	function isFault( $result ) {
		return $this->getSoapClientObject()->is_soap_fault( $result );
	}

	function Login( $user_name, $password = NULL, $type = 'USER_NAME' ) {
		//Check to see if we are currently logged in as the same user already?
		global $TIMETREX_SESSION_ID, $TIMETREX_SESSION_HASH;
		if ( $TIMETREX_SESSION_ID != '' AND $TIMETREX_SESSION_HASH == $this->calcSessionHash( $this->url, $TIMETREX_SESSION_ID ) ) { //AND $this->isLoggedIn() == TRUE
			//Already logged in, skipping unnecessary new login procedure.
			return TRUE;
		}

		$this->session_id = $this->session_hash = NULL; //Don't set old session ID on URL.
		$retval = $this->getSoapClientObject()->Login( $user_name, $password, $type );
		if ( is_soap_fault( $retval ) ) {
			trigger_error('SOAP Fault: (Code: '. $retval->faultcode .', String: '. $retval->faultstring .') - Request: '. $this->getSoapClientObject()->__getLastRequest() .' Response: '. $this->getSoapClientObject()->__getLastResponse(), E_USER_NOTICE);
			return FALSE;
		}

		if ( !is_array($retval) AND $retval != FALSE ) {
			$this->setSessionID( $retval );
			$this->setSessionHash();
			return TRUE;
		}

		return FALSE;
	}

	function isLoggedIn() {
		$old_class = $this->class_factory;
		$this->setClass('Authentication');
		$retval = $this->getSoapClientObject()->isLoggedIn();
		$this->setClass( $old_class );
		unset($old_class);

		return $retval;
	}

	function Logout() {
		$this->setClass('Authentication');
		$retval = $this->getSoapClientObject()->Logout();
		if ( $retval == TRUE ) {
			global $TIMETREX_SESSION_ID, $TIMETREX_SESSION_HASH;
			$TIMETREX_SESSION_ID = $TIMETREX_SESSION_HASH = FALSE;
			$this->session_id = $this->session_hash = NULL;
		}

		return $retval;
	}

	function __call($function_name, $args = array() ) {
		if ( is_object( $this->getSoapClientObject() ) ) {
			$retval = call_user_func_array(array($this->getSoapClientObject(), $function_name), $args);

			if ( is_soap_fault( $retval ) ) {
				trigger_error('SOAP Fault: (Code: '. $retval->faultcode .', String: '. $retval->faultstring .') - Request: '. $this->getSoapClientObject()->__getLastRequest() .' Response: '. $this->getSoapClientObject()->__getLastResponse(), E_USER_NOTICE);

				return FALSE;
			}

			return new TimeTrexClientAPIReturnHandler( $function_name, $args, $retval );
		}

		return FALSE;
	}
}

/**
 * @package API\TimeTrexClientAPI
 */
class TimeTrexClientAPIReturnHandler {
	/*
	'api_retval' => $retval,
	'api_details' => array(
					'code' => $code,
					'description' => $description,
					'record_details' => array(
											'total' => $validator_stats['total_records'],
											'valid' => $validator_stats['valid_records'],
											'invalid' => ($validator_stats['total_records']-$validator_stats['valid_records'])
											),
					'details' =>  $details,
					)
	*/
	protected $function_name = NULL;
	protected $args = NULL;
	protected $result_data = FALSE;

	function __construct( $function_name, $args, $result_data ) {
		$this->function_name = $function_name;
		$this->args = $args;
		$this->result_data = $result_data;

		return TRUE;
	}

	function getResultData() {
		return $this->result_data;
	}

	function getFunction() {
		return $this->function_name;
	}
	function getArgs() {
		return $this->args;
	}

	function __toString() {
		$eol = "<br>\n";

		$output = array();
		$output[] = '=====================================';
		$output[] = 'Function: '. $this->getFunction() .'()';
		$output[] = 'Args: '. $this->getArgs();
		$output[] = '-------------------------------------';
		$output[] = 'Returned:';
		$output[] = ( $this->isValid() === TRUE ) ? 'IsValid: YES' : 'IsValid: NO';
		if ( $this->isValid() === TRUE ) {
			$output[] = 'Return Value: '. $this->getResult();
		} else {
			$output[] = 'Code: '. $this->getCode();
			$output[] = 'Description: '. $this->getDescription();
			$output[] = 'Details: ';

			$details = $this->getDetails();
			if ( is_array($details) ) {
				foreach( $details as $row => $detail ) {
					$output[] = 'Row: '. $row;
					foreach( $detail as $field => $msgs ) {
						$output[] = '--Field: '. $field;
						foreach( $msgs as $key => $msg ) {
							$output[] = '----Message: '. $msg;
						}
					}
				}
			}
		}
		$output[] = '=====================================';
		$output[] = '';

		return implode( $eol, $output );
	}

	function isValid() {
		if ( isset($this->result_data['api_retval']) ) {
			return (bool)$this->result_data['api_retval'];
		}

		return TRUE;
	}
	function isError() { //Opposite of isValid()
		if ( isset($this->result_data['api_retval']) ) {
			if ( $this->result_data['api_retval'] === FALSE ) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	function getCode() {
		if ( isset($this->result_data['api_details']) AND isset($this->result_data['api_details']['code']) ) {
			return $this->result_data['api_details']['code'];
		}

		return FALSE;
	}
	function getDescription() {
		if ( isset($this->result_data['api_details']) AND isset($this->result_data['api_details']['description']) ) {
			return $this->result_data['api_details']['description'];
		}

		return FALSE;
	}
	function getDetails() {
		if ( isset($this->result_data['api_details']) AND isset($this->result_data['api_details']['details']) ) {
			return $this->result_data['api_details']['details'];
		}

		return FALSE;
	}

	function getRecordDetails() {
		if ( isset($this->result_data['api_details']) AND isset($this->result_data['api_details']['record_details']) ) {
			return $this->result_data['api_details']['record_details'];
		}

		return FALSE;
	}
	function getTotalRecords() {
		if ( isset($this->result_data['api_details']) AND isset($this->result_data['api_details']['record_details'])  AND isset($this->result_data['api_details']['record_details']['total_records']) ) {
			return $this->result_data['api_details']['record_details']['total_records'];
		}

		return FALSE;
	}
	function getValidRecords() {
		if ( isset($this->result_data['api_details']) AND isset($this->result_data['api_details']['record_details'])  AND isset($this->result_data['api_details']['record_details']['valid_records']) ) {
			return $this->result_data['api_details']['record_details']['valid_records'];
		}

		return FALSE;
	}
	function getInValidRecords() {
		if ( isset($this->result_data['api_details']) AND isset($this->result_data['api_details']['record_details'])  AND isset($this->result_data['api_details']['record_details']['invalid_records']) ) {
			return $this->result_data['api_details']['record_details']['invalid_records'];
		}

		return FALSE;
	}

	function getResult() {
		if ( isset($this->result_data['api_retval']) ) {
			return $this->result_data['api_retval'];
		} else {
			return $this->result_data;
		}
	}
}
?>