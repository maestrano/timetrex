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
 * $Revision: 3725 $
 * $Id: CLI.inc.php 3725 2010-07-28 21:16:05Z ipso $
 * $Date: 2010-07-28 14:16:05 -0700 (Wed, 28 Jul 2010) $
 */
//Allow both CLI and CGI PHP binaries to call maint scripts.
if ( PHP_SAPI != 'cli' AND PHP_SAPI != 'cgi' AND PHP_SAPI != 'cgi-fcgi') {
	echo "This script can only be called from the Command Line.\n";
	exit;
}

if ( version_compare( PHP_VERSION, 5, '<') == 1 ) {
	echo "You are currently using PHP v". PHP_VERSION ." TimeTrex requires PHP v5 or greater!\n";
	exit;
}

//Allow CLI scripts to run much longer.
ini_set( 'max_execution_time', 7200 );

//Check post install requirements, because PHP CLI usually uses a different php.ini file.
$install_obj = new Install();
if ( $install_obj->checkAllRequirements( TRUE ) == 1 ) {
	$failed_requirements = $install_obj->getFailedRequirements( TRUE );
	unset($failed_requirements[0]);
	echo "----WARNING----WARNING----WARNING-----\n";
	echo "--------------------------------------\n";
	echo "Minimum PHP Requirements are NOT met!!\n";
	echo "--------------------------------------\n";
	echo "Failed Requirements: ".implode(',', (array)$failed_requirements )." \n";
	echo "--------------------------------------\n\n\n";
}

TTi18n::chooseBestLocale(); //Make sure a locale is set, specifically when generating PDFs.

//Uncomment the below block to force debug logging with maintenance jobs.
/*
Debug::setEnable( TRUE );
Debug::setBufferOutput( TRUE );
Debug::setEnableLog( TRUE );
if ( Debug::getVerbosity() <= 1 ) {
	Debug::setVerbosity( 1 );
}
*/
?>