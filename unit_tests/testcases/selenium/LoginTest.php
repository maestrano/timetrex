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

require_once('PHPUnit/Extensions/SeleniumTestCase.php');

/**
 * @group UI
 */
class UILoginTest extends PHPUnit_Extensions_SeleniumTestCase {
	public function setUp() {
		global $selenium_config;
		$this->selenium_config = $selenium_config;

		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeZone('Etc/GMT+8', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$this->setHost( $selenium_config['host'] );
		$this->setBrowser( $selenium_config['browser'] );
		$this->setBrowserUrl( $selenium_config['default_url'] );

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function Login() {
		Debug::text('Login to: '. $this->selenium_config['default_url'], __FILE__, __LINE__, __METHOD__, 10);
		$this->open( $this->selenium_config['default_url'] );
		$this->waitForAttribute( 'css=div.login-view@init_complete' );

		$this->type('id=user_name', 'demoadmin1');
		$this->type('id=password', 'demo.de');
		$this->click('id=login_btn');
		$this->waitForAttribute( 'css=div.view@init_complete' );

		Debug::text('Login Complete...', __FILE__, __LINE__, __METHOD__, 10);
	}

	function Logout() {
		$view_name = $this->getText('xpath=//div[@id=\'ribbon\']/ul/li[11]/a');
		Debug::text('View: '. $view_name, __FILE__, __LINE__, __METHOD__, 10);
		$this->click('link=My Account');
		$this->click('id=Logout');
		$this->waitForAttribute( 'css=div.login-view@init_complete' );
		Debug::text('Logout...', __FILE__, __LINE__, __METHOD__, 10);
	}

	function waitForAttribute( $attribute_name, $value = 'true', $timeout = FALSE ) {
		if ( $timeout == '' ) {
			$timeout = $this->selenium_config['default_timeout'];
		}

		for ($second = 0; ; $second++) {
			if ( $second >= $timeout ) {
				Debug::text('TIMEOUT waitForAttribute failed: '. $attribute_name, __FILE__, __LINE__, __METHOD__, 10);
				$this->fail('timeout');
			}

			try {
				if ( $this->getAttribute( $attribute_name ) == $value ) {
					break;
				}
			} catch ( Exception $e ) {
				Debug::text('Exception! waitForAttribute failed: '. $attribute_name, __FILE__, __LINE__, __METHOD__, 10);
			}
			sleep(1);
		}

		return TRUE;
	}

	function waitForElementPresent( $attribute_name, $value = TRUE, $timeout = FALSE ) {
		if ( $timeout == '' ) {
			$timeout = $this->selenium_config['default_timeout'];
		}

		for ($second = 0; ; $second++) {
			if ( $second >= $timeout ) {
				Debug::text('TIMEOUT isElementPresent failed: '. $attribute_name, __FILE__, __LINE__, __METHOD__, 10);
				$this->fail('timeout');
			}

			try {
				if ( $this->isElementPresent( $attribute_name ) == $value ) {
					break;
				}
			} catch ( Exception $e ) {
				Debug::text('Exception! isElementPresent failed: '. $attribute_name, __FILE__, __LINE__, __METHOD__, 10);
			}
			sleep(1);
		}

		return TRUE;
	}

	function testUILoginLogout() {
		$this->Login();
		$this->Logout();
	}

	function testEditUser() {
		//TODO: Use input field names/ids rather then positions or xpath indexes.
		$this->Login();

		//Go to employee list
		$this->getText('xpath=//div[@id=\'ribbon\']/ul/li[11]/a');
		$this->click('link=Employee');
		$this->click('css=#Employee > img');
		$this->waitForAttribute( 'css=div.view@init_complete' );

		//Add new employee.
		$this->getText('xpath=//div[@id=\'ribbon\']/ul/li[11]/a');
		$this->click('css=#addIcon > img');
		$this->waitForAttribute( 'css=div.view@init_complete' );

		//Enter employee information.
		$this->type('xpath=id(\'tab0_content_div\')/div[1]/div[13]/div[2]/input', 'selenium.test');
		$this->fireEvent('xpath=id(\'tab0_content_div\')/div[1]/div[13]/div[2]/input', 'keyup');
		$this->waitForElementPresent('xpath=id(\'tab0_content_div\')/div[1]/div[13]/div[2]/input[contains(@class,\'error-tip\')]', FALSE );

		$this->type('xpath=id(\'tab0_content_div\')/div[1]/div[15]/div[2]/input', 'demo');
		$this->fireEvent('xpath=id(\'tab0_content_div\')/div[1]/div[15]/div[2]/input', 'keyup');
		$this->waitForElementPresent('xpath=id(\'tab0_content_div\')/div[1]/div[15]/div[2]/input[contains(@class,\'error-tip\')]', FALSE );

		$this->type('xpath=id(\'tab0_content_div\')/div[1]/div[17]/div[2]/input', 'demo');
		$this->fireEvent('xpath=id(\'tab0_content_div\')/div[1]/div[17]/div[2]/input', 'keyup');
		$this->waitForElementPresent('xpath=id(\'tab0_content_div\')/div[1]/div[17]/div[2]/input[contains(@class,\'error-tip\')]', FALSE );

		$this->type('xpath=(//input[@type=\'text\'])[12]', 'selenium');
		$this->fireEvent('xpath=(//input[@type=\'text\'])[12]', 'keyup');
		$this->type('xpath=(//input[@type=\'text\'])[13]', 'test');
		$this->fireEvent('xpath=(//input[@type=\'text\'])[13]', 'keyup');
		$this->waitForElementPresent('xpath=(//input[@type=\'text\'])[13][contains(@class,\'error-tip\')]', FALSE );

		$this->waitForElementPresent('xpath=id(\'EmployeeContextMenu\')/div/div/div[1]/ul/li[8][contains(@class,\'disable-image\')]', FALSE );
		$this->waitForAttribute( 'css=div.edit-view@validate_complete' );

		//Save employee
		$this->click('xpath=id(\'EmployeeContextMenu\')/div/div/div[1]/ul/li[8]');
		$this->waitForElementPresent('css=div.popup-loading' );
		$this->waitForElementPresent('css=div.edit-view', FALSE );
		$this->waitForAttribute( 'css=div.view@init_complete' );

		//Search for newly created user
		$this->click('link=BASIC SEARCH');
		$this->waitForElementPresent('div.ui-tabs-hide', FALSE );
		$this->type('css=input.t-text-input', 'selenium');
		$this->click('id=searchBtn');
		$this->waitForAttribute( 'css=div.search-panel@search_complete' );

		//Select employee
		$this->uncheck('xpath=//input[contains(@id,\'jqg_employee_view_container_\')]');
		$this->click('xpath=//input[contains(@id,\'jqg_employee_view_container_\')]');
		$this->waitForElementPresent('xpath=id(\'EmployeeContextMenu\')/div/div/div[1]/ul/li[5][contains(@class,\'disable-image\')]', FALSE );

		//Delete employee
		$this->click('id=deleteIcon');
		$this->isElementPresent('css=div.confirm-alert');

		//Confirm delete
		$this->click('id=yesBtn');
		$this->isElementPresent('css=div.no-result-div');


		$this->Logout();
	}
}
?>