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

$smarty->assign('title', TTi18n::gettext('Confirm Email'));

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'email',
												'email_confirmed',
												'key',
												) ) );

$validator = new Validator();

$action = Misc::findSubmitButton();
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__, 10);
switch ($action) {
	case 'confirm_email':
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByEmailIsValidKey( $key );
		if ( $ulf->getRecordCount() == 1 ) {
			Debug::Text('FOUND Email Validation key! Email: '. $email, __FILE__, __LINE__, __METHOD__, 10);

			$valid_key = TRUE;
			
			$ttsc = new TimeTrexSoapClient();

			$user_obj = $ulf->getCurrent();
			if ( $user_obj->getWorkEmailIsValidKey() == $key AND $user_obj->getWorkEmail() == $email ) {
				$user_obj->setWorkEmailIsValidKey( '' );
				//$user_obj->setWorkEmailIsValidDate( '' ); //Keep date so we know when the address was validated last.
				$user_obj->setWorkEmailIsValid( TRUE );

				$remote_validation_result = $ttsc->validateEmail( $user_obj->getWorkEmail() );
			} elseif( $user_obj->getHomeEmailIsValidKey() == $key AND $user_obj->getHomeEmail() == $email ) {
				$user_obj->setHomeEmailIsValidKey( '' );
				//$user_obj->setHomeEmailIsValidDate( '' ); //Keep date so we know when the address was validated last.
				$user_obj->setHomeEmailIsValid( TRUE );

				$remote_validation_result = $ttsc->validateEmail( $user_obj->getHomeEmail() );
			} else {
				$valid_key = FALSE;
			}

			if ( $valid_key == TRUE AND $user_obj->isValid() ) {
				$user_obj->Save(FALSE);
				Debug::Text('Email validation is succesful!', __FILE__, __LINE__, __METHOD__, 10);

				TTLog::addEntry( $user_obj->getId(), 500, TTi18n::gettext('Validated email address').': '. $email, $user_obj->getId(), 'users' );

				Redirect::Page( URLBuilder::getURL( array('email_confirmed' => 1, 'email' => $email ), 'ConfirmEmail.php' ) );
				break;
			} else {
				Debug::Text('aDID NOT FIND email validation key!', __FILE__, __LINE__, __METHOD__, 10);
				$email_confirmed = FALSE;
			}
		} else {
			Debug::Text('bDID NOT FIND email validation key!', __FILE__, __LINE__, __METHOD__, 10);
			$email_confirmed = FALSE;
		}
	default:
		if ( $email_confirmed == FALSE ) {
			//Make sure we don't allow malicious users to use some long email address like:
			//"This is the FBI, you have been fired if you don't..."
			if ( $validator->isEmail( 'email', $email, TTi18n::getText('Invalid confirmation key') ) == FALSE ) {
				$email = NULL;
				//$email_sent = FALSE;
			}
		}

		break;
}

$smarty->assign_by_ref('email', $email);
$smarty->assign_by_ref('email_confirmed', $email_confirmed);
$smarty->assign_by_ref('key', $key);
$smarty->assign_by_ref('action', $action);

$smarty->assign_by_ref('validator', $validator);

$smarty->display('ConfirmEmail.tpl');
?>