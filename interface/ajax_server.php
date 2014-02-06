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
 * $Revision: 3224 $
 * $Id: ajax_server.php 3224 2009-12-26 18:10:21Z ipso $
 * $Date: 2009-12-26 10:10:21 -0800 (Sat, 26 Dec 2009) $
 */

require_once('../includes/global.inc.php');
if ( ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == 1)
		OR ( isset($_SERVER['HTTP_REFERER']) AND stristr( $_SERVER['HTTP_REFERER'], 'quick_punch') ) ) {
        Debug::text('AJAX Server - Installer enabled, or using quickpunch... NOT AUTHENTICATING...', __FILE__, __LINE__, __METHOD__, 10);
		$authenticate = FALSE;
}
$skip_message_check = TRUE;

require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once('HTML/AJAX/Server.php');

class AutoServer extends HTML_AJAX_Server {
        // this flag must be set for your init methods to be used
        var $initMethods = true;

        // init method for my ajax class
        function initAJAX_Server() {
			$ajax = new AJAX_Server();
			$this->registerClass($ajax);
        }
}

$server = new AutoServer();
$server->handleRequest();

Debug::text('AJAX Server called...', __FILE__, __LINE__, __METHOD__, 10);
Debug::writeToLog();
?>