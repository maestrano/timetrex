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
 * $Revision: 5166 $
 * $Id: Option.class.php 5166 2011-08-26 23:01:36Z ipso $
 * $Date: 2011-08-26 16:01:36 -0700 (Fri, 26 Aug 2011) $
 */

/**
 * @package Core
 */
class Option {
	static function getByKey($key, $options, $false = FALSE ) {
		if ( isset($options[$key]) ){
			//Debug::text('Returning Value: '. $options[$key] , __FILE__, __LINE__, __METHOD__, 9);

			return $options[$key];
		}

		return $false;
		//return FALSE;
	}

	static function getByValue($value, $options, $value_is_translated = TRUE ) {
		// I18n: Calling gettext on the value here enables a match with the translated value in the relevant factory.
		//       BUT... such string comparisons are messy and we really should be using getByKey for most everything.
		//		 Exceptions can be made by passing false for $value_is_translated.
		if ( $value_is_translated == TRUE ) {
			$value = TTi18n::gettext( $value );
		}
		if ( is_array( $value ) ) {
			return FALSE;
		}

		if ( !is_array( $options ) ) {
			return FALSE;
		}

		$flipped_options = array_flip($options);

		if ( isset($flipped_options[$value]) ){
			//Debug::text('Returning Key: '. $flipped_options[$value] , __FILE__, __LINE__, __METHOD__, 9);

			return $flipped_options[$value];
		}

		return FALSE;
	}

	static function getByFuzzyValue($value, $options, $value_is_translated = TRUE ) {
		// I18n: Calling gettext on the value here enables a match with the translated value in the relevant factory.
		//       BUT... such string comparisons are messy and we really should be using getByKey for most everything.
		//		 Exceptions can be made by passing false for $value_is_translated.
		if ( $value_is_translated == TRUE ) {
			$value = TTi18n::gettext( $value );
		}
		if ( is_array( $value ) ) {
			return FALSE;
		}

		if ( !is_array( $options ) ) {
			return FALSE;
		}

		$retarr = Misc::findClosestMatch( $value, $options, 10, FALSE );
		Debug::Arr($retarr, 'RetArr: ', __FILE__, __LINE__, __METHOD__,10);

		/*
		//Convert SQL search value ie: 'test%test%' to a regular expression.
		$value = str_replace('%', '.*', $value);

		foreach( $options as $key => $option_value ) {
			if ( preg_match('/^'.$value.'$/i', $option_value) ) {
				$retarr[] = $key;
			}
		}
		*/

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	//Takes $needles as an array, loops through them returning matching
	//keys => value pairs from haystack
	//Useful for filtering results to a select box, like status.
	static function getByArray($needles, $haystack) {

		if (!is_array($needles) ) {
			$needles = array($needles);
		}

		$needles = array_unique($needles);

		foreach($needles as $needle) {
			if ( isset($haystack[$needle]) ) {
				$retval[$needle] = $haystack[$needle];
			}
		}

		if ( isset($retval) ) {
			return $retval;
		}

		return FALSE;
	}

	static function getArrayByBitMask( $bitmask, $options ) {
		$bitmask = (int)$bitmask;

		if ( is_numeric($bitmask) AND is_array($options) ) {
			foreach( $options as $key => $value ) {
				//Debug::Text('Checking Bitmask: '. $bitmask .' mod '. $key .' != 0', __FILE__, __LINE__, __METHOD__,10);
				if ( ($bitmask & (int)$key) !== 0 ) {
					//Debug::Text('Found Bit: '. $key, __FILE__, __LINE__, __METHOD__,10);
					$retarr[] = $key;
				}
			}
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	static function getBitMaskByArray( $keys, $options ) {
		$retval = 0;
		if ( is_array($keys) AND is_array($options) ) {
			foreach( $keys as $key ) {
				if ( isset($options[$key]) ) {
					$retval |= $key;
				} else {
					Debug::Text('Key is not a valid bitmask int: '. $key, __FILE__, __LINE__, __METHOD__,10);
				}
			}
		}

		return $retval;
	}
}
?>
