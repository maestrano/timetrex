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
 * $Revision: 2196 $
 * $Id: User.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\UnAuthenticated
 */
class APIAuthentication extends APIFactory {
	protected $main_class = 'Authentication';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	function Login($user_name, $password = NULL, $type = 'USER_NAME') {
		global $config_vars;
		$authentication = new Authentication();

		Debug::text('User Name: '. $user_name .' Password Length: '. strlen($password) .' Type: '. $type, __FILE__, __LINE__, __METHOD__, 10);

		//FIXME: When using Flex, I think it sets the cookie itself, so we need to pass this information on to it before it will actually work.
		//However this should work fine for JSON/SOAP.
		//FIXME: Store the type in the authentication table so we know how the user logged in. Then we can disable certain functionality if using the phone_id.
		if ( isset($config_vars['other']['web_session_expire']) AND $config_vars['other']['web_session_expire'] != '' ) {
			$authentication->setEnableExpireSession( (int)$config_vars['other']['web_session_expire'] );
		}

		if ( $authentication->Login($user_name, $password, $type) === TRUE ) {
			$retval = $authentication->getSessionId();
			Debug::text('Success, Session ID: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		} else {
			$validator_obj = new Validator();
			$validator_stats = array('total_records' => 1, 'valid_records' => 0 );

			$error_column = 'user_name';
			$error_message = TTi18n::gettext('User Name or Password is incorrect');

			//Get company status from user_name, so we can display messages for ONHOLD/Cancelled accounts.
			$clf = TTnew( 'CompanyListFactory' );
			$clf->getByUserName( $user_name );
			if ( $clf->getRecordCount() > 0 ) {
				$c_obj = $clf->getCurrent();
				if ( $c_obj->getStatus() == 20 ) {
					$error_message = TTi18n::gettext('Sorry, your company\'s account has been placed ON HOLD, please contact customer support immediately');
				} elseif ( $c_obj->getStatus() == 23 ) {
					$error_message = TTi18n::gettext('Sorry, your trial period has expired, please contact our sales department to reactivate your account');
				} elseif ( $c_obj->getStatus() == 28 ) {
					if ( $c_obj->getMigrateURL() != '' ) {
						$error_message = TTi18n::gettext('To better serve our customers your account has been migrated, please update your bookmarks to use the following URL from now on: ') . 'http://'. $c_obj->getMigrateURL();
					} else {
						$error_message = TTi18n::gettext('To better serve our customers your account has been migrated, please contact customer support immediately.');
					}
				} elseif ( $c_obj->getStatus() == 30 ) {
					$error_message = TTi18n::gettext('Sorry, your company\'s account has been CANCELLED, please contact customer support if you believe this is an error');
				} elseif ( $c_obj->getPasswordPolicyType() == 1 AND $c_obj->getProductEdition() > 10 ) {
					//Password policy is enabled, confirm users password has not exceeded maximum age.
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByUserName( $user_name );
					if ( $ulf->getRecordCount() > 0 ) {
						foreach( $ulf as $u_obj ) {
							//Make sure we confirm that the password is in fact correct, but just expired.
							if ( $u_obj->checkPassword($password, FALSE) == TRUE AND $u_obj->checkPasswordAge() == FALSE ) {
								$error_message = TTi18n::gettext('Sorry, your password has exceeded its maximum age specified by your company\'s password policy and must be changed immediately');
								$error_column = 'password';
							}
						}
					}
					unset($ulf, $u_obj);
				}
			}

			$validator_obj->isTrue( $error_column, FALSE, $error_message );

			$validator[0] = $validator_obj->getErrorsArray();

			return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
		}

		return $this->returnHandler( FALSE );
	}

	function newSession( $user_id, $client_id = NULL, $ip_address = NULL ) {
		global $authentication;

		if ( is_object($authentication) AND $authentication->getSessionID() != '' ) {
			Debug::text('Session ID: '. $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->getPermissionObject()->Check('company','view') AND $this->getPermissionObject()->Check('company','login_other_user') ) {
				if ( !is_numeric( $user_id ) ) { //If username is used, lookup user_id
					Debug::Text('Lookup User ID by UserName: '. $user_id, __FILE__, __LINE__, __METHOD__,10);
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByUserName( trim($user_id) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_id = $ulf->getCurrent()->getID();
					}
				}

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getByIdAndStatus( (int)$user_id, 10 );  //Can only switch to Active employees
				if ( $ulf->getRecordCount() == 1 ) {
					$new_session_user_obj = $ulf->getCurrent();

					Debug::Text('Login as different user: '. $user_id .' IP Address: '. $ip_address, __FILE__, __LINE__, __METHOD__,10);
					$new_session_id = $authentication->newSession( $user_id, $ip_address );

					$retarr = array(
									'session_id' => $new_session_id,
									'url' => Misc::getHostName(FALSE).Environment::getBaseURL(), //Don't include the port in the hostname, otherwise it can cause problems when forcing port 443 but not using 'https'.
									);

					//Add entry in source *AND* destination user log describing who logged in.
					//Source user log, showing that the source user logged in as someone else.
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 'Login',  TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSessionID() .' '.  TTi18n::getText('To Employee').': '. $new_session_user_obj->getFullName() .'('.$user_id.')', $this->getCurrentUserObject()->getId(), 'authentication');

					//Destination user log, showing the destination user was logged in *by* someone else.
					TTLog::addEntry( $user_id, 'Login',  TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSessionID() .' '.  TTi18n::getText('By Employee').': '. $this->getCurrentUserObject()->getFullName() .'('.$user_id.')', $user_id, 'authentication');

					return $this->returnHandler( $retarr );
				}
			}
		}

		return FALSE;
	}

	//Accepts user_id or user_name.
	function switchUser( $user_id ) {
		global $authentication;

		if ( is_object($authentication) AND $authentication->getSessionID() != '' ) {
			Debug::text('Session ID: '. $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->getPermissionObject()->Check('company','view') AND $this->getPermissionObject()->Check('company','login_other_user') ) {
				if ( !is_numeric( $user_id ) ) { //If username is used, lookup user_id
					Debug::Text('Lookup User ID by UserName: '. $user_id, __FILE__, __LINE__, __METHOD__,10);
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByUserName( trim($user_id) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_id = $ulf->getCurrent()->getID();
					}
				}

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getByIdAndStatus( (int)$user_id, 10 );  //Can only switch to Active employees
				if ( $ulf->getRecordCount() == 1 ) {
					Debug::Text('Login as different user: '. $user_id, __FILE__, __LINE__, __METHOD__,10);
					$authentication->changeObject( $user_id );

					//Add entry in source *AND* destination user log describing who logged in.
					//Source user log, showing that the source user logged in as someone else.
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 'Login',  TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSessionID() .' '.  TTi18n::getText('To Employee').': '. $authentication->getObject()->getFullName() .'('.$user_id.')', $this->getCurrentUserObject()->getId(), 'authentication');

					//Destination user log, showing the destination user was logged in *by* someone else.
					TTLog::addEntry( $user_id, 'Login',  TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSessionID() .' '.  TTi18n::getText('By Employee').': '. $this->getCurrentUserObject()->getFullName() .'('.$user_id.')', $user_id, 'authentication');

					return TRUE;
				} else {
					Debug::Text('User is likely not active: '. $user_id, __FILE__, __LINE__, __METHOD__,10);
				}
			}
		}

		return FALSE;
	}

	function Logout() {
		global $authentication;

		if ( is_object($authentication) AND $authentication->getSessionID() != '' ) {
			Debug::text('Logging out session ID: '. $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10);

			return $authentication->Logout();
		}

		return FALSE;
	}

	function isLoggedIn( $touch_updated_date = TRUE ) {
		global $authentication, $config_vars;

		$session_id = getSessionID();

		if ( $session_id != '' ) {
			$authentication = new Authentication();

			Debug::text('AMF Session ID: '. $session_id .' Source IP: '. $_SERVER['REMOTE_ADDR'], __FILE__, __LINE__, __METHOD__, 10);
			if ( isset($config_vars['other']['web_session_timeout']) AND $config_vars['other']['web_session_timeout'] != '' ) {
				$authentication->setIdle( (int)$config_vars['other']['web_session_timeout'] );
			}
			if ( $authentication->Check( $session_id, $touch_updated_date ) === TRUE ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getCurrentUserName() {
		if ( is_object( $this->getCurrentUserObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getUserName() );
		}

		return $this->returnHandler( FALSE );
	}
	function getCurrentUser() {
		if ( is_object( $this->getCurrentUserObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getObjectAsArray() );
		}

		return $this->returnHandler( FALSE );
	}

	function getCurrentCompany() {
		if ( is_object( $this->getCurrentCompanyObject() ) ) {
			return $this->returnHandler( $this->getCurrentCompanyObject()->getObjectAsArray() );
		}

		return $this->returnHandler( FALSE );
	}

	function getCurrentUserPreference() {
		if ( is_object( $this->getCurrentUserObject() ) AND is_object( $this->getCurrentUserObject()->getUserPreferenceObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getUserPreferenceObject()->getObjectAsArray() );
		}

		return $this->returnHandler( FALSE );
	}

	//Functions that can be called before the API client is logged in.
	//Mainly so the proper loading/login page can be displayed.
	function getApplicationName() {
		return APPLICATION_NAME;
	}
	function getApplicationVersion() {
		return APPLICATION_VERSION;
	}
	function getOrganizationName() {
		return ORGANIZATION_NAME;
	}
	function getOrganizationURL() {
		return ORGANIZATION_URL;
	}
	function isApplicationBranded() {
		global $config_vars;

		if ( isset($config_vars['branding']['application_name']) ) {
			return TRUE;
		}

		return FALSE;
	}
	function isPoweredByLogoEnabled() {
		global $config_vars;

		if ( isset($config_vars['branding']['disable_powered_by_logo']) AND $config_vars['branding']['disable_powered_by_logo'] == TRUE ) {
			return FALSE;
		}

		return TRUE;
	}

	function isAnalyticsEnabled() {
		global $config_vars;

		if ( isset($config_vars['other']['disable_google_analytics']) AND $config_vars['other']['disable_google_analytics'] == TRUE ) {
			return FALSE;
		}

		return TRUE;
	}

	function getTTProductEdition( $name = FALSE ) {
		if ( $name == TRUE ) {
			$edition = getTTProductEditionName();
		} else {
			$edition = getTTProductEdition();
		}

		Debug::text('Edition: '. $edition, __FILE__, __LINE__, __METHOD__, 10);
		return $edition;
	}

	function getDeploymentOnDemand() {
		return DEPLOYMENT_ON_DEMAND;
	}

	function getRegistrationKey() {
		$sslf = new SystemSettingListFactory();
		$sslf->getByName( 'registration_key' );
		if ( $sslf->getRecordCount() == 1 ) {
			$key = $sslf->getCurrent()->getValue();
			return $key;
		}

		return FALSE;
	}

	function getLocale( $language = NULL, $country = NULL ) {
		$language = Misc::trimSortPrefix( $language );
		if ( $language == '' AND is_object( $this->getCurrentUserObject() ) AND is_object($this->getCurrentUserObject()->getUserPreferenceObject()) ) {
			$language = $this->getCurrentUserObject()->getUserPreferenceObject()->getLanguage();
		}
		if ( $country == '' AND is_object( $this->getCurrentUserObject() ) ) {
			$country = $this->getCurrentUserObject()->getCountry();
		}

		if ( $language != '' ) {
			TTi18n::setLanguage( $language );
		}
		if ( $country != '' ) {
			TTi18n::setCountry( $country );
		}
		TTi18n::setLocale(); //Sets master locale

		$retval = str_replace('.UTF-8', '', TTi18n::getLocale() );
		Debug::text('Locale: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function getSystemLoad() {
		return Misc::getSystemLoad();
	}

	function getHTTPHost() {
		return $_SERVER['HTTP_HOST'];
	}

	function getCompanyName() {
		//Get primary company data needs to be used when user isn't logged in as well.
		$clf = TTnew( 'CompanyListFactory' );
		$clf->getByID( PRIMARY_COMPANY_ID );
		Debug::text('Primary Company ID: '. PRIMARY_COMPANY_ID, __FILE__, __LINE__, __METHOD__, 10);
		if ( $clf->getRecordCount() == 1 ) {
			return $clf->getCurrent()->getName();
		}

		return FALSE;
	}

	//Returns all login data required in a single call for optimization purposes.
	function getPreLoginData( $api = NULL ) {
		global $config_vars;

		return array(
				'primary_company_id' => PRIMARY_COMPANY_ID, //Needed for some branded checks.
				'base_url' => Environment::getBaseURL(),
				'api_url' => Environment::getAPIURL( $api ),
				'api_base_url' => Environment::getAPIBaseURL( $api ),
				'api_json_url' => Environment::getAPIURL( 'json' ),
				'images_url' => Environment::getImagesURL(),
				'powered_by_logo_enabled' => $this->isPoweredByLogoEnabled(),
				'product_edition' => $this->getTTProductEdition( FALSE ),
				'product_edition_name' => $this->getTTProductEdition( TRUE ),
				'deployment_on_demand' => $this->getDeploymentOnDemand(),
				'web_session_expire' => ( isset($config_vars['other']['web_session_expire']) AND $config_vars['other']['web_session_expire'] != '' ) ? (bool)$config_vars['other']['web_session_expire'] : FALSE, //If TRUE then session expires when browser closes.
				'analytics_enabled' => $this->isAnalyticsEnabled(),
				'registration_key' => $this->getRegistrationKey(),
				'http_host' => $this->getHTTPHost(),
				'application_version' => $this->getApplicationVersion(),
				'is_logged_in' => $this->isLoggedIn(),
				'language_options' => Misc::addSortPrefix( TTi18n::getLanguageArray() ),
				'language' => TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ),
			);
	}

	//Function that Flex can call when an irrecoverable error or uncaught exception is triggered.
	function sendErrorReport( $data, $screenshot = NULL ) {
		$attachments = NULL;
		if ( $screenshot != '' ) {
			$attachments[] = array( 'file_name' => 'screenshot.png', 'mime_type' => 'image/png', 'data' => base64_decode( $screenshot ) );
		}

		return Misc::sendSystemMail( TTi18n::gettext('Flex Error Report'), $data, $attachments );
	}

	/**
	 * Allows user who isn't logged in to change their password.
	 * @param string $user_name
	 * @param string $current_password
	 * @param string $new_password
	 * @param string $new_password2
	 * @param string $type
	 * @return bool
	 */
	function changePassword( $user_name, $current_password, $new_password, $new_password2 ) {
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByUserName( $user_name );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				if ( $u_obj->getCompanyObject()->getStatus() == 10 ) {
					Debug::text('Attempting to change password for: '. $user_name, __FILE__, __LINE__, __METHOD__, 10);

					if ( $current_password != '' ) {
						if ( $u_obj->checkPassword($current_password, FALSE) !== TRUE ) { //Disable password policy checking on current password.
							Debug::Text('Password check failed!', __FILE__, __LINE__, __METHOD__,10);
							$u_obj->Validator->isTrue(	'current_password',
													FALSE,
													TTi18n::gettext('Current password is incorrect') );
						}
					} else {
						Debug::Text('Current password not specified', __FILE__, __LINE__, __METHOD__,10);
						$u_obj->Validator->isTrue(	'current_password',
												FALSE,
												TTi18n::gettext('Current password is incorrect') );
					}

					if ( $new_password != '' OR $new_password2 != ''  ) {
						if ( $new_password == $new_password2 ) {
							$u_obj->setPassword($new_password);
						} else {
							$u_obj->Validator->isTrue(	'password',
													FALSE,
													TTi18n::gettext('Passwords don\'t match') );
						}
					} else {
						$u_obj->Validator->isTrue(	'password',
												FALSE,
												TTi18n::gettext('Passwords don\'t match') );
					}

					if ( $u_obj->isValid() ) {
						if ( DEMO_MODE == TRUE ) {
							//Return TRUE even in demo mode, but nothing happens.
							return $this->returnHandler( TRUE );
						} else {
							TTLog::addEntry( $u_obj->getID(), 20, TTi18n::getText('Password - Web (Exceeded Maximum Age)'), NULL, $u_obj->getTable() );
							return $this->returnHandler( $u_obj->Save() ); //Single valid record
						}
					} else {
						return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $u_obj->Validator->getErrorsArray(), array('total_records' => 1, 'valid_records' => 0) );
					}

				}
			}
		}

		return $this->returnHandler( FALSE );
	}

	//Ping function is also in APIMisc for when the session timesout is valid.
	//Ping no longer can tell if the session is timed-out, must use "isLoggedIn(FALSE)" instead.
	function Ping() {
		return TRUE;
	}
}
?>
