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
class AJAX_Server {

	function getCurrentUserFullName() {
		global $current_user;

		if ( is_object( $current_user ) ) {
			return $current_user->getFullName();
		}

		return FALSE;
	}

	function getCurrentCompanyName() {
		global $current_company;

		if ( is_object( $current_company ) ) {
			return $current_company->getName();
		}

		return FALSE;
	}

	function getProvinceOptions( $country ) {
		Debug::Arr($country, 'aCountry: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !is_array($country) AND $country == '' ) {
			return FALSE;
		}

		if ( !is_array($country) ) {
			$country = array($country);
		}

		Debug::Arr($country, 'bCountry: ', __FILE__, __LINE__, __METHOD__, 10);

		$cf = TTnew( 'CompanyFactory' );

		$province_arr = $cf->getOptions('province');

		$retarr = array();

		foreach( $country as $tmp_country ) {
			if ( isset($province_arr[strtoupper($tmp_country)]) ) {
				//Debug::Arr($province_arr[strtoupper($tmp_country)], 'Provinces Array', __FILE__, __LINE__, __METHOD__, 10);

				$retarr = array_merge( $retarr, $province_arr[strtoupper($tmp_country)] );
				//$retarr = array_merge( $retarr, Misc::prependArray( array( -10 => '--' ), $province_arr[strtoupper($tmp_country)] ) );
			}
		}

		if ( count($retarr) == 0 ) {
			$retarr = array('00' => '--');
		}

		return $retarr;
	}

	function getJobItemOptions( $job_id, $user_id, $login_time, $key, $include_disabled = TRUE ) {
		//This must work when not fully authenticated.
		if ( $user_id != '' AND $user_id > 0 ) {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getById( (int)$user_id );
			if ( $ulf->getRecordCount() == 1 ) {
				Debug::Text('Found User, checking key!', __FILE__, __LINE__, __METHOD__, 10);
				$current_user = $ulf->getCurrent();

				//Only allow punches within 5 minutes of the original submit time.
				if ( $login_time >= ( time() - (60 * 5) ) AND trim($key) == md5($user_id.$login_time.$current_user->getPasswordSalt() ) ) {
					Debug::text('Job ID: '. $job_id .' Include Disabled: '. (int)$include_disabled, __FILE__, __LINE__, __METHOD__, 10);

					$jilf = TTnew( 'JobItemListFactory' );
					$jilf->getByCompanyIdAndJobId( $current_user->getCompany(), $job_id );
					//$jilf->getByJobId( $job_id );
					$job_item_options = $jilf->getArrayByListFactory( $jilf, TRUE, $include_disabled );
					if ( $job_item_options != FALSE AND is_array($job_item_options) ) {
							return $job_item_options;
					}
				}
			}
		}

		Debug::text('Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10);

		$retarr = array( '00' => '--');

		return $retarr;
	}

	function strtotime($str) {
		return TTDate::strtotime($str);
	}

	function parseDateTime($str) {
		return TTDate::parseDateTime( $str );
	}

	function getDate( $format, $epoch ) {
		return TTDate::getDate( $format, $epoch);
	}

	function getBeginMonthEpoch( $epoch ) {
		return TTDate::getBeginMonthEpoch( $epoch );
	}

	function getTimeZoneOffset( $time_zone ) {
		TTDate::setTimeZone( $time_zone );
		return TTDate::getTimeZoneOffset();
	}
}
?>
