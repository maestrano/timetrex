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
 * $Revision: 1246 $
 * $Id: Misc.class.php 1246 2007-09-14 23:47:42Z ipso $
 * $Date: 2007-09-14 16:47:42 -0700 (Fri, 14 Sep 2007) $
 */

/**
 * @package Core
 */
class UnitConvert {
	/*
		This class is used to convert units, ie:
		pounds (lbs) to grams (g)
		inches (in) to meters (m)
		miles (mi) to kiliometers (km)
	*/

	//Convert weight units to grams, first.
	//Convert dimension units to mm, first.
	//Handle square and cubic (exponent) calculations as well.
	static $units = array(
						// 1 Unit = X G
						'oz' => 28.349523125,
						'lb' => 453.59237,
						'lbs' => 453.59237,
						'g'  => 1,
						'kg' => 1000,

						//1 Unit = X MM
						'mm' => 1,
						'in' => 25.4,
						'cm' => 10,
						'ft' => 304.8,
						'm' => 1000,
					);

	//Only units in the same array can be converted to one another.
	static $valid_unit_groups = array(
									'g' => array('g','oz','lb','lbs','kg'),
									'mm' => array('mm','in','cm','ft','m')
									);

	static function convert( $src_unit, $dst_unit, $measurement, $exponent = 1 ) {
		$src_unit = strtolower($src_unit);
		$dst_unit = strtolower($dst_unit);

		if ( !isset(self::$units[$src_unit]) ) {
			return FALSE;
		}
		if ( !isset(self::$units[$dst_unit]) ) {
			return FALSE;
		}

		if (  $src_unit == $dst_unit ) {
			return $measurement;
		}

		//Make sure we can convert from one unit to another.
		$valid_conversion = FALSE;
		foreach( self::$valid_unit_groups as $base_unit => $valid_units ) {
			if ( in_array($src_unit, $valid_units) AND in_array($dst_unit, $valid_units) ) {
				//Valid conversion
				$valid_conversion = TRUE;
			}
		}

		if ( $valid_conversion == FALSE ) {
			return FALSE;
		}

		$base_measurement = pow( self::$units[$src_unit], $exponent) * $measurement;
		//Debug::Text(' Base Measurement: '. $base_measurement, __FILE__, __LINE__, __METHOD__,10);
		if ( $base_measurement != 0 ) {
			$retval = (1 / pow(self::$units[$dst_unit], $exponent) ) * $base_measurement;

			return $retval;
		}

		return FALSE;
	}
}
?>