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

require_once('../includes/global.inc.php');

//Debug::setVerbosity( 11 );

$authenticate = FALSE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext('Password Reset'));

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'email',
												'key',
												'email_sent',
												'password',
												'password2',
												) ) );

$rl = TTNew('RateLimit');
$rl->setID( 'password_reset_'. Misc::getRemoteIPAddress() );
$rl->setAllowedCalls( 10 );
$rl->setTimeFrame( 900 ); //15 minutes

$validator = new Validator();

$action = Misc::findSubmitButton();
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__, 10);
switch ($action) {
	case 'change_password':
		Debug::Text('Change Password: '. $key, __FILE__, __LINE__, __METHOD__, 10);
		if ( $rl->check() == FALSE ) {
			Debug::Text('Excessive change password attempts... Preventing resets from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
			sleep(5); //Excessive password attempts, sleep longer.
			$action = 'reset_password';
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByPasswordResetKey( $key );
			if ( $ulf->getRecordCount() == 1 ) {
				Debug::Text('FOUND Password reset key! ', __FILE__, __LINE__, __METHOD__, 10);

				$user_obj = $ulf->getCurrent();
				if ( $user_obj->checkPasswordResetKey( $key ) == TRUE ) {
					$user_name = $user_obj->getUserName();

					//Make sure passwords match
					if ( $password != '' AND trim($password) === trim($password2) ) {
						//Change password
						$user_obj->setPassword( $password ); //Password reset key is cleared when password is changed.
						if ( $user_obj->isValid() ) {
							$user_obj->Save(FALSE);
							Debug::Text('Password Change succesful!', __FILE__, __LINE__, __METHOD__, 10);

							//Logout all sessions for this user when password is successfully reset.
							$authentication = TTNew('Authentication');
							$authentication->logoutUser( $user_obj->getId() );

							unset($user_obj);

							Redirect::Page( URLBuilder::getURL( array('password_reset' => 1 ), Environment::getDefaultInterfaceBaseURL() ) );
						}
					} else {
						$validator->isTrue('password', FALSE, TTi18n::getText('Passwords do not match') );
					}

					//Do this once a successful key is found, so the user can get as many password change attempts as needed.
					$rl->delete(); //Clear password reset rate limit upon successful reset.
				} else {
					Debug::Text('DID NOT FIND Valid Password reset key!', __FILE__, __LINE__, __METHOD__, 10);
					$action = 'reset_password';
				}
			} else {
				Debug::Text('DID NOT FIND Password reset key!', __FILE__, __LINE__, __METHOD__, 10);
				$action = 'reset_password';
			}

			Debug::text('Change Password Failed! Attempt: '. $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
			sleep( ($rl->getAttempts() * 0.5) ); //If email is incorrect, sleep for some time to slow down brute force attacks.							
		}
		break;
	case 'password_reset':
		//Debug::setVerbosity( 11 );
		Debug::Text('Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
		if ( $rl->check() == FALSE ) {
			Debug::Text('Excessive password reset attempts... Preventing resets from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
			sleep(5); //Excessive password attempts, sleep longer.
			$action = 'reset_password';
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByPasswordResetKey( $key );
			if ( $ulf->getRecordCount() == 1 ) {
				Debug::Text('FOUND Password reset key!', __FILE__, __LINE__, __METHOD__, 10);				
				$user_obj = $ulf->getCurrent();
				if ( $user_obj->checkPasswordResetKey( $key ) == TRUE ) {
					$user_name = $user_obj->getUserName();					
					$rl->delete(); //Clear password reset rate limit upon successful reset.									
				} else {
					Debug::Text('DID NOT FIND Valid Password reset key!', __FILE__, __LINE__, __METHOD__, 10);
					$action = 'reset_password';					
				}
			} else {
				Debug::Text('DID NOT FIND Password reset key!', __FILE__, __LINE__, __METHOD__, 10);
				$action = 'reset_password';
			}
			
			Debug::text('Password Reset Failed! Attempt: '. $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
			sleep( ($rl->getAttempts() * 0.5) ); //If email is incorrect, sleep for some time to slow down brute force attacks.			
		}
		break;
	case 'reset_password':
		//Debug::setVerbosity( 11 );
		Debug::Text('Email: '. $email, __FILE__, __LINE__, __METHOD__, 10);
		if ( $rl->check() == FALSE ) {
			Debug::Text('Excessive reset password attempts... Preventing resets from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
			sleep(5); //Excessive password attempts, sleep longer.
			$validator->isTrue('email', FALSE, TTi18n::getText('Email address was not found in our database (z)') );
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByHomeEmailOrWorkEmail( $email );
			if ( $ulf->getRecordCount() == 1 ) {
				$user_obj = $ulf->getCurrent();

				if ( $user_obj->getStatus() == 10 ) { //Only allow password resets on active employees.
					//Check if company is using LDAP authentication, if so deny password reset.
					if ( $user_obj->getCompanyObject()->getLDAPAuthenticationType() == 0 ) {
						$user_obj->sendPasswordResetEmail();
						Debug::Text('Found USER! ', __FILE__, __LINE__, __METHOD__, 10);

						$rl->delete(); //Clear password reset rate limit upon successful login.

						Redirect::Page( URLBuilder::getURL( array('email_sent' => 1, 'email' => $email ), 'ForgotPassword.php' ) );
					} else {
						Debug::Text('LDAP Authentication is enabled, password reset is disabled! ', __FILE__, __LINE__, __METHOD__, 10);
						$validator->isTrue('email', FALSE, TTi18n::getText('Please contact your administrator for instructions on changing your password.'). ' (LDAP)' );
					}
				} else {
					$validator->isTrue('email', FALSE, TTi18n::getText('Email address was not found in our database (b)') );
				}
			} else {
				//Error
				Debug::Text('DID NOT FIND USER! Returned: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				$validator->isTrue('email', FALSE, TTi18n::getText('Email address was not found in our database (a)') );
			}

			Debug::text('Reset Password Failed! Attempt: '. $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
			sleep( ($rl->getAttempts() * 0.5) ); //If email is incorrect, sleep for some time to slow down brute force attacks.
		}
		break;
	default:
		if ( $email_sent == TRUE ) {
			//Make sure we don't allow malicious users to use some long email address like:
			//"This is the FBI, you have been fired if you don't..."
			if ( $validator->isEmail( 'email', $email, TTi18n::getText('Invalid Email address') ) == FALSE ) {
				$email = NULL;
				$email_sent = FALSE;
			}
		}

		break;
}

$smarty->assign_by_ref('email', $email);
$smarty->assign_by_ref('email_sent', $email_sent);
$smarty->assign_by_ref('key', $key);
$smarty->assign_by_ref('user_name', $user_name);
$smarty->assign_by_ref('action', $action);

$smarty->assign_by_ref('validator', $validator);

$smarty->display('ForgotPassword.tpl');
?>