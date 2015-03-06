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

require_once('PHPUnit/Framework/TestCase.php');

/**
 * @group Browser
 */
class BrowserTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !class_exists('Browser') ) {
			require_once( Environment::getBasePath().'/classes/other/Browser.php');
		}

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function testBrowserIE() {
		$browser = new Browser( 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; BRI/2)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '11.0' ); //Use Trident Version

		$browser = new Browser( 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '11.0' ); //Use Trident Version

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; LEN2)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '9.0' );

		$browser = new Browser( 'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '11.0' ); //Use Trident Version

		$browser = new Browser( 'Mozilla/5.0 (Windows; U; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '6.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '6.0' );

		$browser = new Browser( 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '7.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; c .NET CLR 3.0.04506; .NET CLR 3.5.30707; InfoPath.1; el-GR)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '7.0' );

		$browser = new Browser( 'Mozilla/5.0 (MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB7.4; InfoPath.2; SV1; .NET CLR 3.3.69573; WOW64; en-US)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '8.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB7.4; InfoPath.2; SV1; .NET CLR 3.3.69573; WOW64; en-US)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '8.0' );

		$browser = new Browser( 'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '9.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 7.1; Trident/5.0)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '9.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '10.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '10.0' ); //Take MSIE over Trident here.

		return TRUE;
	}
}
?>