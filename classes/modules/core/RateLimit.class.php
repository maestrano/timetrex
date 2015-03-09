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

class RateLimit {
	protected $sleep = FALSE; //When rate limit is reached, do we sleep or return FALSE?

	protected $id = 1;
	protected $group = 'rate_limit';

	protected $allowed_calls = 25;
	protected $time_frame = 60; //1 minute.

	protected $memory = NULL;

	function __construct() {
		$this->memory = new SharedMemory();

		return TRUE;
	}

	function getID() {
		return $this->id;
	}
	function setID($value) {
		if ( $value != '' ) {
			$this->id = $value;

			return TRUE;
		}

		return FALSE;
	}

	//Define the number of calls to check() allowed over a given time frame.
	function getAllowedCalls() {
		return $this->allowed_calls;
	}
	function setAllowedCalls($value) {
		if ( $value != '' ) {
			$this->allowed_calls = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeFrame() {
		return $this->time_frame;
	}
	function setTimeFrame($value) {
		if ( $value != '' ) {
			$this->time_frame = $value;

			return TRUE;
		}

		return FALSE;
	}

	function setRateData( $data ) {
		return $this->memory->set( $this->group.$this->getID(), $data );
	}
	function getRateData() {
		return $this->memory->get( $this->group.$this->getID() );
	}

	function getAttempts() {
		$rate_data = $this->getRateData();
		if ( isset($rate_data['attempts']) ) {
			return $rate_data['attempts'];
		}

		return FALSE;
	}

	function check() {
		if ( $this->getID() != '' ) {
			$rate_data = $this->getRateData();
			//Debug::Arr($rate_data, 'Failed Attempt Data: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( !isset($rate_data['attempts']) ) {
				$rate_data = array(
											'attempts' => 0,
											'first_date' => microtime(TRUE),
											);
			} elseif ( isset($rate_data['attempts']) ) {
				if ( $rate_data['attempts'] > $this->getAllowedCalls() AND $rate_data['first_date'] >= ( microtime(TRUE) - $this->getTimeFrame() ) ) {
					return FALSE;
				} elseif ( $rate_data['first_date'] < ( microtime(TRUE) - $this->getTimeFrame() ) ) {
					$rate_data['attempts'] = 0;
					$rate_data['first_date'] = microtime(TRUE);
				}
			}

			$rate_data['attempts']++;
			$this->setRateData( $rate_data );
			return TRUE; //Don't return result of setRateData() so if it can't write the data to shared memory it fails "OPEN".
		}

		return TRUE; //Return TRUE is no ID is specified, so it fails "OPEN".
	}

	function delete() {
		return $this->memory->delete( $this->group.$this->getID() );
	}
}
?>