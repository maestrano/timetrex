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
require_once( Environment::getBasePath() .'/classes/pear/System/SharedMemory.php');
class SharedMemory {
	protected $obj = NULL;

	function __construct() {
		global $config_vars;

		$shared_memory = new System_SharedMemory();
		if ( isset($config_vars['cache']['redis_host']) AND $config_vars['cache']['redis_host'] != '' ) {
			$this->obj = $shared_memory->Factory( 'Redis', array('host' => $config_vars['cache']['redis_host'], 'db' => ( isset($config_vars['cache']['redis_db']) ) ? $config_vars['cache']['redis_db'] : '', 'timeout' => 1 ) );
		} else {
			if ( OPERATING_SYSTEM == 'WIN' ) {
				$this->obj = $shared_memory->Factory( 'File', array('tmp' => $config_vars['cache']['dir'] ) );
			} else {
				$this->obj = $shared_memory->Factory( 'File', array('tmp' => $config_vars['cache']['dir'] ) );
				////$this->obj = &System_SharedMemory::Factory( 'Systemv', array( 'size' => $size ) ); //Run into size issues all the time.
			}
		}

		return TRUE;
	}

	function set( $key, $value ) {
		if ( is_string( $key ) ) {
			return $this->obj->set( $key, $value );
		}
		return FALSE;
	}

	function get( $key ) {
		if ( is_string( $key ) ) {
			return $this->obj->get( $key );
		}
		return FALSE;
	}

	function delete( $key ) {
		if ( is_string( $key ) ) {
			return $this->obj->rm( $key );
		}
		return FALSE;
	}
}
?>
