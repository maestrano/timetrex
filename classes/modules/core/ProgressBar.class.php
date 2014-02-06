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
 * $Revision: 2490 $
 * $Id: SharedMemory.class.php 2490 2009-04-24 22:13:40Z ipso $
 * $Date: 2009-04-24 15:13:40 -0700 (Fri, 24 Apr 2009) $
 */

/**
 * @package Core
 */

//
//http://danielmclaren.net/2008/08/13/tracking-progress-of-a-server-side-action-in-flashflex
//
class ProgressBar {
	protected $obj = NULL;

	var $default_key = NULL;

	var $update_iteration = 1; //This is how often we actually update the progress bar, even if the function is called more often.

	function __construct() {
		$this->obj = new SharedMemory();

		return TRUE;
	}

	//Allow setting a default key so we don't have to pass the key around outside of this object.
	function setDefaultKey( $key ) {
		$this->default_key = $key;
	}
	function getDefaultKey() {
		return $this->default_key;
	}

	function start( $key, $total_iterations = 100, $update_iteration = NULL, $msg = NULL )  {
		Debug::text('start: \''. $key .'\' Iterations: '. $total_iterations .' Update Iterations: '. $update_iteration .' Key: '. $key .'('.microtime(TRUE).') Message: '. $msg, __FILE__, __LINE__, __METHOD__,9);

		if ( $key == '' ) {
			$key = $this->getDefaultKey();
			if ( $key == '' ) {
				return FALSE;
			}
		}

		if ( $total_iterations <= 1 ) {
			return FALSE;
		}

		if ( $update_iteration == '' ) {
			$this->update_iteration = ceil($total_iterations / 20); //Update every 5%.
		} else {
			$this->update_iteration = $update_iteration;
		}

		if (  $msg == '' ) {
			$msg = TTi18n::getText('Processing...');
		}

		$epoch = microtime(TRUE);

		$progress_bar_arr = array(
					 'start_time' => $epoch,
					 'current_iteration' => 0,
					 'total_iterations' => $total_iterations,
					 'last_update_time' => $epoch,
					 'message' => $msg,
					 );

		$this->obj->set( $key, $progress_bar_arr );

		return TRUE;
	}

	function delete( $key ) {
		return $this->stop( $key );
	}
	function stop( $key ) {
		if ( $key == '' ) {
			$key = $this->getDefaultKey();
			if ( $key == '' ) {
				return FALSE;
			}
		}

		//Debug::text('stop: '. $key, __FILE__, __LINE__, __METHOD__,9);

		return $this->obj->delete( $key );
	}

	function set( $key, $current_iteration, $msg = NULL ) {
		if ( $key == '' ) {
			$key = $this->getDefaultKey();
			if ( $key == '' ) {
				return FALSE;
			}
		}

		//Add quick IF statement to short circuit any work unless we meet the update_iteration, ie: every X calls do we actually do anything.
		//When processing long batches though, we need to update every iteration for the first 10 iterations so we can get an accruate estimated time for completion.
		if ( $current_iteration <= 10 OR $current_iteration % $this->update_iteration == 0 ) {
			//Debug::text('set: '. $key .' Iteration: '. $current_iteration, __FILE__, __LINE__, __METHOD__,9);

			$progress_bar_arr = $this->obj->get( $key );

			if ( $progress_bar_arr != FALSE
					AND is_array( $progress_bar_arr )
					AND $current_iteration >= 0
					AND $current_iteration <= $progress_bar_arr['total_iterations']) {

				/*
				if ( PRODUCTION == FALSE AND isset($progress_bar_arr['total_iterations']) AND $progress_bar_arr['total_iterations'] >= 1 ) {
					//Add a delay based on the total iterations so we can test the progressbar more often
					$total_delay = 15000000; //10seconds
					usleep( ( ($total_delay / $progress_bar_arr['total_iterations']) * $this->update_iteration));
				}
				*/

				$progress_bar_arr['current_iteration'] = $current_iteration;
				$progress_bar_arr['last_update_time'] = microtime(TRUE);
			}

			if ( $msg != '' ) {
				$progress_bar_arr['message'] = $msg;
			}

			return $this->obj->set( $key, $progress_bar_arr );
		}

		return TRUE;
	}

	function get($key) {
		if ( $key == '' ) {
			$key = $this->getDefaultKey();
			if ( $key == '' ) {
				return FALSE;
			}
		}

		$retval = $this->obj->get( $key );
		//Debug::text('get: '. $key .'('.microtime(TRUE).')', __FILE__, __LINE__, __METHOD__,9);
		//Debug::Arr($retval, 'get: '. $key .'('.microtime(TRUE).')', __FILE__, __LINE__, __METHOD__,9);

		return $retval;
	}

	function test( $key, $total_iterations = 10 ) {
		Debug::text('testProgressBar: '. $key .' Iterations: '. $total_iterations, __FILE__, __LINE__, __METHOD__,9);

		$this->start( $key, $total_iterations );

		for($i=1; $i <= $total_iterations; $i++ ) {
			$this->set( $key, $i);
			sleep(rand(1,2));
		}

		$this->stop( $key );

		return TRUE;
	}
}
?>
