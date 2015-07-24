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
class Misc {
	/*
		this method assumes that the form has one or more
		submit buttons and that they are named according
		to this scheme:

		<input type="submit" name="submit:command" value="some value">

		This is useful for identifying which submit button actually
		submitted the form.
	*/
	static function findSubmitButton( $prefix = 'action' ) {
		// search post vars, then get vars.
		$queries = array($_POST, $_GET);
		foreach($queries as $query) {
			foreach($query as $key => $value) {
				//Debug::Text('Key: '. $key .' Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				$newvar = explode(':', $key, 2);
				//Debug::Text('Explode 0: '. $newvar[0] .' 1: '. $newvar[1], __FILE__, __LINE__, __METHOD__, 10);
				if ( isset($newvar[0]) AND isset($newvar[1]) AND $newvar[0] === $prefix ) {
					$val = $newvar[1];

					// input type=image stupidly appends _x and _y.
					if ( substr($val, ( strlen($val) - 2 ) ) === '_x' ) {
						$val = substr($val, 0, ( strlen($val) - 2 ) );
					}

					//Debug::Text('Found Button: '. $val, __FILE__, __LINE__, __METHOD__, 10);
					return strtolower($val);
				}
			}
		}

		return NULL;
	}

	static function getSortDirectionArray( $text_keys = FALSE ) {
		if ( $text_keys === TRUE ) {
			return array('asc' => 'ASC', 'desc' => 'DESC');
		} else {
			return array(1 => 'ASC', -1 => 'DESC');
		}
	}

	//This function totals arrays where the data wanting to be totaled is deep in a multi-dimentional array.
	//Usually a row array just before its passed to smarty.
	static function ArrayAssocSum($array, $element = NULL, $decimals = NULL, $include_non_numeric = FALSE ) {
		if ( !is_array($array) ) {
			return FALSE;
		}

		$retarr = array();
		$totals = array();

		foreach($array as $key => $value) {
			if ( isset($element) AND isset($value[$element]) ) {
				foreach($value[$element] as $sum_key => $sum_value ) {
					if ( !isset($totals[$sum_key]) ) {
						$totals[$sum_key] = 0;
					}
					$totals[$sum_key] += $sum_value;
				}
			} else {
				//Debug::text(' Array Element not set: ', __FILE__, __LINE__, __METHOD__, 10);
				foreach($value as $sum_key => $sum_value ) {
					if ( !isset($totals[$sum_key]) ) {
						$totals[$sum_key] = 0;
					}
					if ( !is_numeric( $sum_value ) ) {
						if ( $include_non_numeric == TRUE AND $sum_value != '' ) {
							$totals[$sum_key] = $sum_value;
						}
					} else {
						$totals[$sum_key] += $sum_value;
					}
					//Debug::text(' Sum: '. $totals[$sum_key] .' Key: '. $sum_key .' This Value: '. $sum_value, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		//format totals
		if ( $decimals !== NULL ) {
			foreach($totals as $retarr_key => $retarr_value) {
				//Debug::text(' Number Formatting: '. $retarr_value, __FILE__, __LINE__, __METHOD__, 10);
				$retarr[$retarr_key] = number_format($retarr_value, $decimals, '.', '');
			}
		} else {
			return $totals;
		}
		unset($totals);

		return $retarr;
	}

	//This function is similar to a SQL group by clause, only its done on a AssocArray
	//Pass it a row array just before you send it to smarty.
	static function ArrayGroupBy($array, $group_by_elements, $ignore_elements = array() ) {

		if ( !is_array($group_by_elements) ) {
			$group_by_elements = array($group_by_elements);
		}

		if ( isset($ignore_elements) AND is_array($ignore_elements) ) {
			foreach($group_by_elements as $group_by_element) {
				//Remove the group by element from the ignore elements.
				unset($ignore_elements[$group_by_element]);
			}
		}

		$retarr = array();
		if ( is_array($array) ) {
			foreach( $array as $row) {
				$group_by_key_val = NULL;
				foreach($group_by_elements as $group_by_element) {
					if ( isset($row[$group_by_element]) ) {
						$group_by_key_val .= $row[$group_by_element];
					}
				}
				//Debug::Text('Group By Key Val: '. $group_by_key_val, __FILE__, __LINE__, __METHOD__, 10);

				if ( !isset($retarr[$group_by_key_val]) ) {
					$retarr[$group_by_key_val] = array();
				}

				foreach( $row as $key => $val) {
					//Debug::text(' Key: '. $key .' Value: '. $val, __FILE__, __LINE__, __METHOD__, 10);
					if ( in_array($key, $group_by_elements) ) {
						$retarr[$group_by_key_val][$key] = $val;
					} elseif( !in_array($key, $ignore_elements) ) {
						if ( isset($retarr[$group_by_key_val][$key]) ) {
							$retarr[$group_by_key_val][$key] = Misc::MoneyFormat( bcadd($retarr[$group_by_key_val][$key], $val), FALSE);
							//Debug::text(' Adding Value: '. $val .' For: '. $retarr[$group_by_key_val][$key], __FILE__, __LINE__, __METHOD__, 10);
						} else {
							//Debug::text(' Setting Value: '. $val, __FILE__, __LINE__, __METHOD__, 10);
							$retarr[$group_by_key_val][$key] = $val;
						}
					}
				}
			}
		}

		return $retarr;
	}

	static function ArrayAvg($arr) {

		if ((!is_array($arr)) OR (!count($arr) > 0)) {
			return FALSE;
		}

		return ( array_sum($arr) / count($arr) );
	}

	static function prependArray($prepend_arr, $arr) {
		if ( !is_array($prepend_arr) AND is_array($arr) ) {
			return $arr;
		} elseif ( is_array($prepend_arr) AND !is_array($arr) ) {
			return $prepend_arr;
		} elseif ( !is_array($prepend_arr) AND !is_array($arr) ) {
			return FALSE;
		}

		$retarr = $prepend_arr;

		foreach($arr as $key => $value) {
			//Don't overwrite entries from the prepend array.
			if ( !isset($retarr[$key]) ) {
				$retarr[$key] = $value;
			}
		}

		return $retarr;
	}

	static function arrayColumn( $input = NULL, $columnKey = NULL, $indexKey = NULL ) {
		if ( function_exists('array_column') ) {
			return array_column( (array)$input, $columnKey, $indexKey );
		} else {
			// Using func_get_args() in order to check for proper number of
			// parameters and trigger errors exactly as the built-in array_column()
			// does in PHP 5.5.
			$argc = func_num_args();
			$params = func_get_args();

			$params[0] = (array)$params[0];

			if ($argc < 2) {
				trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
				return NULL;
			}

			if (!is_array($params[0])) {
				trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
				return NULL;
			}

			if (!is_int($params[1])
				AND !is_float($params[1])
				AND !is_string($params[1])
				AND $params[1] !== NULL
				AND !(is_object($params[1]) AND method_exists($params[1], '__toString'))
			) {
				trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
				return FALSE;
			}

			if (isset($params[2])
				AND !is_int($params[2])
				AND !is_float($params[2])
				AND !is_string($params[2])
				AND !(is_object($params[2]) AND method_exists($params[2], '__toString'))
			) {
				trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
				return FALSE;
			}

			$paramsInput = $params[0];
			$paramsColumnKey = ($params[1] !== NULL) ? (string)$params[1] : NULL;

			$paramsIndexKey = NULL;
			if (isset($params[2])) {
				if (is_float($params[2]) OR is_int($params[2])) {
					$paramsIndexKey = (int)$params[2];
				} else {
					$paramsIndexKey = (string)$params[2];
				}
			}

			$resultArray = array();

			foreach ($paramsInput as $row) {

				$key = $value = NULL;
				$keySet = $valueSet = FALSE;

				if ($paramsIndexKey !== NULL AND array_key_exists($paramsIndexKey, $row)) {
					$keySet = TRUE;
					$key = (string)$row[$paramsIndexKey];
				}

				if ($paramsColumnKey === NULL ) {
					$valueSet = TRUE;
					$value = $row;
				} elseif (is_array($row) AND array_key_exists($paramsColumnKey, $row)) {
					$valueSet = TRUE;
					$value = $row[$paramsColumnKey];
				}

				if ($valueSet) {
					if ($keySet) {
						$resultArray[$key] = $value;
					} else {
						$resultArray[] = $value;
					}
				}

			}

			return $resultArray;
		}
	}

	static function flattenArray($array, $preserve = FALSE, $r = array() ) {
		foreach( $array as $key => $value ) {
			if ( is_array($value) ) {
				foreach( $value as $k => $v ) {
					if ( is_array($v) ) {
						$tmp = $v;
						unset($value[$k]);
					}
				}

				if ($preserve) {
					$r[$key] = $value;
				} else {
					$r[] = $value;
				}
			}

			$r = isset($tmp) ? self::flattenArray($tmp, $preserve, $r) : $r;
		}

		return $r;
	}

	/*
		When passed an array of input_keys, and an array of output_key => output_values,
		this function will return all the output_key => output_value pairs where
		input_key == output_key
	*/
	static function arrayIntersectByKey( $keys, $options ) {
		if ( is_array($keys) AND is_array($options) ) {
			foreach( $keys as $key ) {
				if ( isset($options[$key]) AND $key !== FALSE ) { //Ignore boolean FALSE, so the Root group isn't always selected.
					$retarr[$key] = $options[$key];
				}
			}

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		//Return NULL because if we return FALSE smarty will enter a
		//"blank" option into select boxes.
		return NULL;
	}

	/*
		When passed an associative array from a ListFactory, ie:
		array(	0 => array( <...Data ..> ),
				1 => array( <...Data ..> ),
				2 => array( <...Data ..> ),
				... )
		this function will return an associative array of only the key=>value
		pairs that intersect across all rows.

	*/
	static function arrayIntersectByRow( $rows ) {
		if ( !is_array($rows) ) {
			return FALSE;
		}

		if ( count($rows) < 2 ) {
			return FALSE;
		}

		//Debug::Arr($rows, 'Intersected/Common Data', __FILE__, __LINE__, __METHOD__, 10);
		$retval = FALSE;
		if ( isset($rows[0]) ) {
			$retval = @call_user_func_array( 'array_intersect_assoc', $rows );
			// The '@' cannot be removed, Some of the array_* functions that compare elements in
			// multiple arrays do so by (string)$elem1 === (string)$elem2 If $elem1 or $elem2 is an
			// array, then the array to string notice is thrown, $rows is an array and its every
			// element is also an array, but its element may have one element is still an array, if
			// so, the array to string notice will be produced. this case may be like this:
			//	array(
			//		array('a'), array(
			//			array('a'),
			//		),
			//	);
			// Put a "@" in front to prevent the error, otherwise, the Flex will not work properly.

			//Debug::Arr($retval, 'Intersected/Common Data', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retval;
	}
	/*
		Returns all the output_key => output_value pairs where
		the input_keys are not present in output array keys.

	*/
	static function arrayDiffByKey( $keys, $options ) {
		if ( is_array($keys) AND is_array($options) ) {
			foreach( $options as $key => $value ) {
				if ( !in_array($key, $keys, TRUE) ) { //Use strict we ignore boolean FALSE, so the Root group isn't always selected.
					$retarr[$key] = $options[$key];
				}
			}

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		//Return NULL because if we return FALSE smarty will enter a
		//"blank" option into select boxes.
		return NULL;
	}

	//This only merges arrays where the array keys must already exist.
	static function arrayMergeRecursiveDistinct( array $array1, array $array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) AND isset( $merged[$key] ) AND is_array( $merged[$key] ) ) {
				$merged[$key] = self::arrayMergeRecursiveDistinct( $merged[$key], $value );
			} else {
				$merged[$key] = $value;
			}
		}

		return $merged;
	}

	//Merges arrays with overwriting whereas PHP standard array_merge_recursive does not overwrites but combines.
	static function arrayMergeRecursive( array $array1, array $array2 ) {
		foreach( $array2 as $key => $value ) {
			if ( array_key_exists($key, $array1) AND is_array($value) ) {
				$array1[$key] = self::arrayMergeRecursive($array1[$key], $array2[$key]);
			} else {
				$array1[$key] = $value;
			}
		}

		return $array1;
	}

	static function arrayDiffAssocRecursive($array1, $array2) {
		if ( is_array($array1) ) {
			foreach($array1 as $key => $value) {
				if ( is_array($value) ) {
					if ( !isset($array2[$key]) ) {
						$difference[$key] = $value;
					} elseif( !is_array($array2[$key]) ) {
						$difference[$key] = $value;
					} else {
						$new_diff = self::arrayDiffAssocRecursive($value, $array2[$key]);
						if ( $new_diff !== FALSE ) {
							$difference[$key] = $new_diff;
						}
					}
				} elseif ( !isset($array2[$key]) OR $array2[$key] != $value ) {
					$difference[$key] = $value;
				}
			}
		}

		if ( !isset($difference) ) {
			return FALSE;
		}

		return $difference;
	}

	static function arrayCommonValue( $arr ) {
		$arr_count = array_count_values( $arr );
		arsort( $arr_count );
		return key( $arr_count );
	}

	//Adds prefix to all array keys, mainly for reportings and joining array data together to avoid conflicting keys.
	static function addKeyPrefix( $prefix, $arr, $ignore_elements = NULL ) {
		if ( is_array( $arr ) ) {
			foreach( $arr as $key => $value ) {
				if ( !is_array($ignore_elements) OR ( is_array( $ignore_elements ) AND !in_array( $key, $ignore_elements ) ) ) {
					$retarr[$prefix.$key] = $value;
				} else {
					$retarr[$key] = $value;
				}
			}

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		return FALSE;
	}
	//Removes prefix to all array keys, mainly for reportings and joining array data together to avoid conflicting keys.
	static function removeKeyPrefix( $prefix, $arr, $ignore_elements = NULL ) {
		if ( is_array( $arr ) ) {
			foreach( $arr as $key => $value ) {
				if ( !is_array($ignore_elements) OR ( is_array( $ignore_elements ) AND !in_array( $key, $ignore_elements ) ) ) {
					$retarr[self::strReplaceOnce($prefix, '', $key)] = $value;
				} else {
					$retarr[$key] = $value;
				}
			}

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		return FALSE;
	}

	//Adds sort prefixes to an array maintaining the original order. Primarily used because Flex likes to reorded arrays with string keys.
	static function addSortPrefix( $arr, $begin_counter = 1 ) {
		$i = $begin_counter;
		foreach( $arr as $key => $value ) {
			$sort_prefix = NULL;
			if ( substr($key, 0, 1 ) != '-' ) {
				$sort_prefix = '-'.str_pad($i, 4, 0, STR_PAD_LEFT).'-';
			}
			$retarr[$sort_prefix.$key] = $value;
			$i++;
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	//Removes sort prefixes from an array.
	static function trimSortPrefix( $value, $trim_arr_value = FALSE ) {
		if ( is_array($value) AND count($value) > 0 ) {
			foreach( $value as $key => $val ) {
				if ( $trim_arr_value == TRUE ) {
					$retval[$key] = preg_replace('/^-[0-9]{3,4}-/i', '', $val);
				} else {
					$retval[preg_replace('/^-[0-9]{3,4}-/i', '', $key)] = $val;
				}
			}
		} else {
			$retval = preg_replace('/^-[0-9]{3,4}-/i', '', $value );
		}

		if ( isset($retval) ) {
			return $retval;
		}

		return $value;
	}

	static function strReplaceOnce($str_pattern, $str_replacement, $string) {
		if ( strpos($string, $str_pattern) !== FALSE ) {
			$occurrence = strpos($string, $str_pattern);
			return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern));
		}

		return $string;
	}

	static function FileDownloadHeader($file_name, $type, $size) {
		if ( $file_name == '' OR $size == '') {
			return FALSE;
		}

		Header('Content-Type: '. $type);

		$agent = trim($_SERVER['HTTP_USER_AGENT']);
		if ((preg_match('|MSIE ([0-9.]+)|', $agent, $version)) OR
			(preg_match('|Internet Explorer/([0-9.]+)|', $agent, $version))) {
			//header('Content-Type: application/x-msdownload');
			if ($version == '5.5') {
				Header('Content-Disposition: filename="'.$file_name.'"');
			} else {
				Header('Content-Disposition: attachment; filename="'.$file_name.'"');
			}
		} else {
			//Header('Content-disposition: inline; filename='.$file_name); //Displays document inline (in browser window) if available
			Header('Content-Disposition: attachment; filename="'.$file_name.'"'); //Forces document to download
		}

		Header('Content-Length: '. $size);

		return TRUE;
	}

	//This function helps sending binary data to the client for saving/viewing as a file.
	static function APIFileDownload($file_name, $type, $data) {
		if ( $file_name == '' OR $data == '' ) {
			return FALSE;
		}

		if ( is_array($data) ) {
			return FALSE;
		}

		$size = strlen($data);

		self::FileDownloadHeader( $file_name, $type, $size );
		echo $data;
		//Don't return any TRUE/FALSE here as it could end up in the file.
	}

	static function removeTrailingZeros( $value, $minimum_decimals = 2 ) {
		//Remove trailing zeros after the decimal, leave a minimum of X though.
		if ( strpos( $value, '.') !== FALSE ) {
			$trimmed_value = rtrim( $value, 0);

			$tmp_minimum_decimals = strlen( (int)strrev($trimmed_value) );
			if ( $tmp_minimum_decimals > $minimum_decimals ) {
				$minimum_decimals = $tmp_minimum_decimals;
			}
			return number_format( $value, $minimum_decimals, '.', '' );
		}

		return $value;
	}

	static function MoneyFormat($value, $pretty = TRUE) {

		if ( $pretty == TRUE ) {
			$thousand_sep = ', ';
		} else {
			$thousand_sep = '';
		}

		return number_format( (float)$value, 2, '.', $thousand_sep);
	}

	//Removes vowels from the string always keeping the first and last letter.
	static function abbreviateString( $str ) {
		$vowels = array('a', 'e', 'i', 'o', 'u');

		$retarr = array();
		$words = explode( ' ', trim($str) );
		if ( is_array($words) ) {
			foreach( $words as $word ) {
				$first_letter_in_word = substr( $word, 0, 1);
				$last_letter_in_word = substr( $word, -1, 1);
				$word = str_ireplace( $vowels, '', trim($word) );
				if ( substr( $word, 0, 1) != $first_letter_in_word ) {
					$word = $first_letter_in_word.$word;
				}
				if ( substr( $word, -1, 1) != $last_letter_in_word ) {
					$word .= $last_letter_in_word;
				}
				$retarr[] = $word;
			}

			return implode(' ', $retarr);
		}

		return FALSE;
	}

	static function TruncateString( $str, $length, $start = 0, $abbreviate = FALSE ) {
		if ( strlen( $str ) > $length ) {
			if ( $abbreviate == TRUE ) {
				//Try abbreviating it first.
				$retval = trim( substr( self::abbreviateString( $str ), $start, $length ) );
				if ( strlen( $retval ) > $length ) {
					$retval .= '...';
				}
			} else {
				$retval = trim( substr( trim($str), $start, $length ) ).'...';
			}
		} else {
			$retval = $str;
		}

		return $retval;
	}

	static function HumanBoolean($bool) {
		if ( $bool == TRUE ) {
			return 'Yes';
		} else {
			return 'No';
		}
	}

	static function getBeforeDecimal($float) {
		$float = Misc::MoneyFormat( $float, FALSE );

		$float_array = preg_split('/\./', $float);

		if ( isset($float_array[0]) ) {
			return $float_array[0];
		}

		return FALSE;
	}

	static function getAfterDecimal($float, $format_number = TRUE ) {
		if ( $format_number == TRUE ) {
			$float = Misc::MoneyFormat( $float, FALSE );
		}

		$float_array = preg_split('/\./', $float);

		if ( isset($float_array[1]) ) {
			return str_pad($float_array[1], 2, '0');
		}

		return FALSE;
	}

	//Encode integer to a alphanumeric value that is reversible.
	static function encodeInteger( $int ) {
		if ( $int != '' ) {
			return strtoupper( base_convert( strrev( str_pad( $int, 11, 0, STR_PAD_LEFT ) ), 10, 36) );
		}

		return $int;
	}
	static function decodeInteger( $str, $max = 2147483646 ) {
		$retval = (int)str_pad( strrev( base_convert( $str, 36, 10) ), 11, 0, STR_PAD_RIGHT );
		if ( $retval > $max ) { //This helps prevent out of range errors in SQL queries.
			Debug::Text('Decoding string to int, exceeded max: '. $str .' Max: '. $max, __FILE__, __LINE__, __METHOD__, 10);
			$retval = 0;
		}

		return $retval;
	}

	static function calculatePercent( $current, $maximum, $precision = 0 ) {
		if ( $maximum == 0 ) {
			return 100;
		}

		$percent = round( ( ( $current / $maximum ) * 100 ), (int)$precision );

		if ( $precision == 0 ) {
			$percent = (int)$percent;
		}

		return $percent;
	}

	//Takes an array with columns, and a 2nd array with column names to sum.
	static function sumMultipleColumns($data, $sum_elements) {
		if (!is_array($data) ) {
			return FALSE;
		}

		if (!is_array($sum_elements) ) {
			return FALSE;
		}

		$retval = 0;

		foreach($sum_elements as $sum_element ) {
			if ( isset($data[$sum_element]) ) {
				$retval = bcadd( $retval, $data[$sum_element]);
				//Debug::Text('Found Element in Source Data: '. $sum_element .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return $retval;
	}

	static function calculateMultipleColumns($data, $include_elements = array(), $exclude_elements = array() ) {
		if ( !is_array($data) ) {
			return FALSE;
		}

		$retval = 0;

		if ( is_array( $include_elements ) ) {
			foreach($include_elements as $include_element ) {
				if ( isset($data[$include_element]) ) {
					$retval = bcadd( $retval, $data[$include_element]);
					//Debug::Text('Found Element in Source Data: '. $sum_element .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		if ( is_array( $exclude_elements ) ) {
			foreach($exclude_elements as $exclude_element ) {
				if ( isset($data[$exclude_element]) ) {
					$retval = bcsub( $retval, $data[$exclude_element]);
					//Debug::Text('Found Element in Source Data: '. $sum_element .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		return $retval;
	}

	static function getPointerFromArray( $array, $element, $start = 1 ) {
		//Debug::Arr($array, 'Source Array: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Text('Searching for Element: '. $element, __FILE__, __LINE__, __METHOD__, 10);
		$keys = array_keys( $array );
		//Debug::Arr($keys, 'Source Array Keys: ', __FILE__, __LINE__, __METHOD__, 10);

		//Debug::Text($keys, 'Source Array Keys: ', __FILE__, __LINE__, __METHOD__, 10);
		$key = array_search( $element, $keys );

		if ( $key !== FALSE ) {
			$key = ( $key + $start );
		}

		//Debug::Arr($key, 'Result: ', __FILE__, __LINE__, __METHOD__, 10);
		return $key;
	}

	static function AdjustXY( $coord, $adjust_coord) {
		return ( $coord + $adjust_coord );
	}

	// Static class, static function. avoid PHP strict error.
	static function writeBarCodeFile($file_name, $num, $print_text = TRUE, $height = 60 ) {
		if ( !class_exists('Image_Barcode') ) {
			require_once(Environment::getBasePath().'/classes/Image_Barcode/Barcode.php');
		}

		ob_start();
		$ib = new Image_Barcode();
		$ib->draw($num, 'code128', 'png', FALSE, $print_text, $height);
		$ob_contents = ob_get_contents();
		ob_end_clean();

		if ( file_put_contents($file_name, $ob_contents) > 0 ) {
			//echo "Writing file successfull<Br>\n";
			return TRUE;
		} else {
			//echo "Error writing file<Br>\n";
			return FALSE;
		}
	}

	static function hex2rgb( $hex, $asString = TRUE ) {
		// strip off any leading #
		if (0 === strpos($hex, '#')) {
			$hex = substr($hex, 1);
		} else if (0 === strpos($hex, '&H')) {
			$hex = substr($hex, 2);
		}

		// break into hex 3-tuple
		$cutpoint = ( ceil( ( strlen($hex) / 2 ) ) - 1 );
		$rgb = explode(':', wordwrap($hex, $cutpoint, ':', $cutpoint), 3);

		// convert each tuple to decimal
		$rgb[0] = (isset($rgb[0]) ? hexdec($rgb[0]) : 0);
		$rgb[1] = (isset($rgb[1]) ? hexdec($rgb[1]) : 0);
		$rgb[2] = (isset($rgb[2]) ? hexdec($rgb[2]) : 0);

		return ($asString ? "{$rgb[0]} {$rgb[1]} {$rgb[2]}" : $rgb);
	}

	static function Array2CSV( $data, $columns = NULL, $ignore_last_row = TRUE, $include_header = TRUE, $eol = "\n" ) {
		if ( is_array($data) AND count($data) > 0
				AND is_array($columns) AND count($columns) > 0 ) {

			if ( $ignore_last_row === TRUE ) {
				array_pop($data);
			}

			//Header
			if ( $include_header == TRUE ) {
				foreach( $columns as $column_name ) {
					$row_header[] = $column_name;
				}
				$out = '"'.implode('","', $row_header).'"'.$eol;
			} else {
				$out = NULL;
			}

			foreach( $data as $rows ) {
				foreach ($columns as $column_key => $column_name ) {
					if ( isset($rows[$column_key]) ) {
						$row_values[] = str_replace("\"", "\"\"", $rows[$column_key]);
					} else {
						//Make sure we insert blank columns to keep proper order of values.
						$row_values[] = NULL;
					}
				}

				$out .= '"'.implode('","', $row_values).'"'.$eol;
				unset($row_values);
			}

			return $out;
		}

		return FALSE;
	}

	static function Array2XML( $data, $columns = NULL, $column_format = NULL, $ignore_last_row = TRUE, $include_xml_header = FALSE, $root_element_name = 'data', $row_element_name = 'row') {
		if ( is_array($data) AND count($data) > 0
				AND is_array($columns) AND count($columns) > 0 ) {

			if ( $ignore_last_row === TRUE ) {
				array_pop($data);
			}

			//Debug::Arr($column_format, 'Column Format: ', __FILE__, __LINE__, __METHOD__, 10);

			$out = NULL;

			if ( $include_xml_header == TRUE ) {
				$out .= '<?xml version=\'1.0\' encoding=\'ISO-8859-1\'?>'."\n";
			}

			$out .= '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">'."\n";
			$out .= '	 <xsd:element name="'. $root_element_name .'">'."\n";
			$out .= '		 <xsd:complexType>'."\n";
			$out .= '			 <xsd:sequence>'."\n";
			$out .= '				 <xsd:element name="'. $row_element_name .'">'."\n";
			$out .= '					 <xsd:complexType>'."\n";
			$out .= '						 <xsd:sequence>'."\n";
			foreach ($columns as $column_key => $column_name ) {
				$data_type = 'string';
				if ( is_array($column_format) AND isset($column_format[$column_key]) ) {
					switch ( $column_format[$column_key] ) {
						case 'report_date':
							$data_type = 'string';
							break;
						case 'currency':
						case 'percent':
						case 'numeric':
							$data_type = 'decimal';
							break;
						case 'time_unit':
							$data_type = 'decimal';
							break;
						case 'date_stamp':
							$data_type = 'date';
							break;
						case 'time':
							$data_type = 'time';
							break;
						case 'time_stamp':
							$data_type = 'dateTime';
							break;
						case 'boolean':
							$data_type = 'string';
						default:
							$data_type = 'string';
							break;
					}
				}
				$out .= '							 <xsd:element name="'. $column_key .'" type="xsd:'. $data_type .'"/>'."\n";
			}
			$out .= '						 </xsd:sequence>'."\n";
			$out .= '					 </xsd:complexType>'."\n";
			$out .= '				 </xsd:element>'."\n";
			$out .= '			 </xsd:sequence>'."\n";
			$out .= '		 </xsd:complexType>'."\n";
			$out .= '	 </xsd:element>'."\n";
			$out .= '</xsd:schema>'."\n";

			if ( $root_element_name != '' ) {
				$out .= '<'. $root_element_name .'>'."\n";
			}

			foreach( $data as $rows ) {
				$out .= '<'. $row_element_name .'>'."\n";
				foreach ($columns as $column_key => $column_name ) {
					if ( isset($rows[$column_key]) ) {
						$out .= '	 <'. $column_key .'>'. $rows[$column_key] .'</'. $column_key .'>'."\n";
					}
				}
				$out .= '</'. $row_element_name .'>'."\n";
			}

			if ( $root_element_name != '' ) {
				$out .= '</'. $root_element_name .'>'."\n";
			}

			//Debug::Arr($out, 'XML: ', __FILE__, __LINE__, __METHOD__, 10);

			return $out;
		}

		return FALSE;
	}

	static function Export2XML( $factory_arr, $filter_data, $output_file ) {
		global $global_class_map;

		$global_exclude_arr = array(
									'Factory',
									'FactoryListIterator',
									'SystemSettingFactory',
									'CronJobFactory',
									'CompanyUserCountFactory',

									'HelpFactory',
									'HelpGroupControlFactory',
									'HelpGroupFactory',
									'HierarchyFactory',
									'HierarchyShareFactory',
									'JobUserAllowFactory',
									'JobItemAllowFactory',
									'PolicyGroupAccrualPolicyFactory',
									'PolicyGroupOverTimePolicyFactory',
									'PolicyGroupPremiumPolicyFactory',
									'PolicyGroupRoundIntervalPolicyFactory',
									'ProductTaxPolicyProductFactory',
									);
		
		$dependency_tree = new DependencyTree();
		$i = 0;
		foreach( $global_class_map as $class => $file ) {
			if ( stripos( $class, 'Factory' ) !== FALSE
					AND stripos( $class, 'API' ) === FALSE AND stripos( $class, 'ListFactory' ) === FALSE AND stripos( $class, 'Report' ) === FALSE
					AND !in_array( $class, $global_exclude_arr )
					) {
				if ( isset($global_class_dependancy_map[$class]) ) {
					$dependency_tree->addNode( $class, $global_class_dependancy_map[$class], $class, $i);
				} else {
					$dependency_tree->addNode( $class, array(), $class, $i);
				}
			}
			$i++;
		}
		$ordered_factory_arr = $dependency_tree->getAllNodesInOrder();
		//Debug::Arr($ordered_factory_arr, 'Ordered Factory List: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array($factory_arr) AND count($factory_arr) > 0 ) {
			Debug::Arr($factory_arr, 'Factory Filter: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $ordered_factory_arr as $factory ) {
				if ( in_array( $factory, $factory_arr) ) {
					$filtered_factory_arr[] = $factory;
				} // else { //Debug::Text('Removing factory: '. $factory .' due to filter...', __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			Debug::Text('Not filtering factory...', __FILE__, __LINE__, __METHOD__, 10);
			$filtered_factory_arr = $ordered_factory_arr;
		}
		unset($ordered_factory_arr);

		if ( isset($filtered_factory_arr) AND count($filtered_factory_arr) > 0 ) {
			@unlink( $output_file );
			$fp = bzopen( $output_file, 'w');
			
			Debug::Arr($filtered_factory_arr, 'Filtered/Ordered Factory List: ', __FILE__, __LINE__, __METHOD__, 10);
			
			Debug::Text('Exporting data...', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $filtered_factory_arr as $factory ) {
				$class = str_replace( 'Factory', 'ListFactory', $factory );
				$lf = new $class;
				Debug::Text('Exporting ListFactory: '. $factory .' Memory Usage: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(TRUE), __FILE__, __LINE__, __METHOD__, 10);
				self::ExportListFactory2XML( $lf, $filter_data, $fp );
				unset($lf);
			}
			bzclose($fp);

		} else {
			Debug::Text('No data to export...', __FILE__, __LINE__, __METHOD__, 10);
		}
	}

	static function ExportListFactory2XML( $lf, $filter_data, $file_pointer ) {
		require_once(Environment::getBasePath() .'classes/pear/XML/Serializer.php');
		
		$serializer = new XML_Serializer( array(
													XML_SERIALIZER_OPTION_INDENT		=> '  ',
													XML_SERIALIZER_OPTION_RETURN_RESULT => TRUE,
													'linebreak'			=> "\n",
													'typeHints'			=> TRUE,
													'encoding'			=> 'UTF-8',
													'rootName'			=> get_parent_class( $lf ),
												)
										);

		$lf->getByCompanyId( $filter_data['company_id'] );
		if ( $lf->getRecordCount() > 0 ) {
			Debug::Text('Exporting '. $lf->getRecordCount() .' rows...', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $lf as $obj ) {
				if ( isset($obj->data) ) {
					$result = $serializer->serialize( $obj->data );
					bzwrite($file_pointer, $result."\n" );
					//Debug::Arr($result, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);
				} else {
					Debug::Text('Object \'data\' variable does not exist, cant export...', __FILE__, __LINE__, __METHOD__, 10);
				}
			}
			unset($result, $obj, $serializer);
		} else {
			Debug::Text('No rows to export...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		return TRUE;
	}

	static function inArrayByKeyAndValue( $arr, $search_key, $search_value ) {
		if ( !is_array($arr) AND $search_key != '' AND $search_value != '') {
			return FALSE;
		}

		//Debug::Text('Search Key: '. $search_key .' Search Value: '. $search_value, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($arr, 'Hay Stack: ', __FILE__, __LINE__, __METHOD__, 10);

		foreach( $arr as $arr_key => $arr_value ) {
			if ( isset($arr_value[$search_key]) ) {
				if ( $arr_value[$search_key] == $search_value ) {
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	//This function is used to quickly preset array key => value pairs so we don't
	//have to have so many isset() checks throughout the code.
	static function preSetArrayValues( $arr, $keys, $preset_value = NULL ) {
		if ( is_array( $keys ) ) {
			foreach( $keys as $key ) {
				if ( is_object( $arr ) ) {
					if ( !isset($arr->$key) ) {
						$arr->$key = $preset_value;
					}
				} else {
					if ( !isset($arr[$key]) ) {
						$arr[$key] = $preset_value;
					}
				}
			}
		}

		return $arr;
	}
	
	static function getMimeType( $file_name, $buffer = FALSE, $keep_charset = FALSE, $unknown_type = 'application/octet-stream' ) {
		if ( function_exists('finfo_buffer') ) { //finfo extension in PHP v5.3+
			if ( $buffer == FALSE AND file_exists( $file_name ) ) {
				//Its a filename passed in.
				$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
				$retval = finfo_file($finfo, $file_name );
				finfo_close($finfo);
			} elseif ( $buffer == TRUE AND $file_name != '' ) {
				//Its a string buffer;
				$finfo = new finfo( FILEINFO_MIME );
				$retval = $finfo->buffer( $file_name );
			}

			if ( isset($retval) ) {
				if ( $keep_charset == FALSE ) {
					$split_retval = explode(';', $retval );
					if ( is_array($split_retval) AND isset($split_retval[0]) ) {
						$retval = $split_retval[0];
					}
				}
				Debug::text('MimeType: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
				return $retval;
			}
		} else {
			//Attempt to detect mime type with PEAR MIME class.
			if ( $buffer == FALSE AND file_exists( $file_name ) ) {
				require_once( Environment::getBasePath() .'/classes/pear/MIME/Type.php');
				$retval = MIME_Type::autoDetect( $file_name );
				if ( is_object($retval) ) { //MimeType failed.
					//Attempt to detect mime type manually when finfo extension and PEAR Mime Type is not installed (windows)
					$extension = strtolower( pathinfo($file_name, PATHINFO_EXTENSION) );
					switch( $extension ) {
						case 'jpg':
							$retval = 'image/jpeg';
							break;
						case 'png':
							$retval = 'image/png';
							break;
						case 'gif':
							$retval = 'image/gif';
							break;
						default:
							$retval = $unknown_type;
							break;
					}
				}

				return $retval;
			}
		}

		return FALSE;
	}

	static function countLinesInFile( $file ) {
		ini_set('auto_detect_line_endings', TRUE); //PHP can have problems detecting MAC line endings in some case, this should help solve that.

		$line_count = 0;
		$handle = fopen($file, 'r');
		while( !feof($handle) ) {
			$line = fgets($handle, 4096);
			$line_count = ( $line_count + substr_count( $line, "\n" ) );
		}

		fclose($handle);

		ini_set('auto_detect_line_endings', FALSE);

		return $line_count;
	}

	static function parseCSV($file, $head = FALSE, $first_column = FALSE, $delim=',', $len = 9216, $max_lines = NULL ) {
		if ( !file_exists($file) ) {
			Debug::text('Files does not exist: '. $file, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//mime_content_type is being deprecated in PHP, and it doesn't work properly on Windows. So if its not available just accept any file type.
		if ( function_exists('mime_content_type') ) {
			$mime_type = mime_content_type($file);
			if ( $mime_type !== FALSE AND !in_array( $mime_type, array('text/plain', 'plain/text', 'text/comma-separated-values', 'text/csv', 'application/csv', 'text/anytext', 'text/x-c') ) ) {
				Debug::text('Invalid MIME TYPE: '. $mime_type, __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
		}

		ini_set('auto_detect_line_endings', TRUE); //PHP can have problems detecting MAC line endings in some case, this should help solve that.

		$return = FALSE;
		$handle = fopen($file, 'r');
		if ( $head !== FALSE ) {
			if ( $first_column !== FALSE ) {
				while ( ($header = fgetcsv($handle, $len, $delim) ) !== FALSE) {
					if ( $header[0] == $first_column ) {
						//echo "FOUND HEADER!<br>\n";
						$found_header = TRUE;
						break;
					}
				}

				if ( $found_header !== TRUE ) {
					return FALSE;
				}
			} else {
				$header = fgetcsv($handle, $len, $delim);
			}
		}

		$i = 1;
		while ( ($data = fgetcsv($handle, $len, $delim) ) !== FALSE) {
			if ( $data !== array( NULL ) ) { // ignore blank lines
				if ( $head AND isset($header) ) {
					foreach ($header as $key => $heading) {
						$row[trim($heading)] = ( isset($data[$key]) ) ? $data[$key] : '';
					}
					$return[] = $row;
				} else {
					$return[] = $data;
				}

				if ( $max_lines !== NULL AND $max_lines != '' AND $i == $max_lines ) {
					break;
				}

				$i++;
			}
		}

		fclose($handle);

		ini_set('auto_detect_line_endings', FALSE);

		return $return;
	}

	static function importApplyColumnMap( $column_map, $csv_arr ) {
		if ( !is_array($column_map) ) {
			return FALSE;
		}

		if ( !is_array($csv_arr) ) {
			return FALSE;
		}

		foreach( $column_map as $map_arr ) {
			$timetrex_column = $map_arr['timetrex_column'];
			$csv_column = $map_arr['csv_column'];
			$default_value = $map_arr['default_value'];

			if ( isset($csv_arr[$csv_column]) AND $csv_arr[$csv_column] != '' ) {
				$retarr[$timetrex_column] = trim( $csv_arr[$csv_column] );
				//echo "NOT using default value: ". $default_value ."\n";
			} elseif ( $default_value != '' ) {
				//echo "using Default value! ". $default_value ."\n";
				$retarr[$timetrex_column] = trim( $default_value );
			}
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	static function importCallInputParseFunction( $function_name, $input, $default_value = NULL, $parse_hint = NULL ) {
		$full_function_name = 'parse_'.$function_name;

		if ( function_exists( $full_function_name ) ) {
			//echo "	  Calling Custom Parse Function for: $function_name\n";
			return call_user_func( $full_function_name, $input, $default_value, $parse_hint );
		}

		return $input;
	}

	static function encrypt( $str, $key = NULL ) {
		if ( $str == '' OR $str === FALSE OR empty($str) ) {
			return FALSE;
		}

		if ( $key == NULL OR $key == '' ) {
			global $config_vars;
			$key = $config_vars['other']['salt'];
		}

		$td = mcrypt_module_open( 'tripledes', '', 'ecb', '' );
		$iv = mcrypt_create_iv( mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND );
		$max_key_size = mcrypt_enc_get_key_size( $td );
		mcrypt_generic_init( $td, substr( $key, 0, $max_key_size ), $iv );

		$encrypted_data = base64_encode( mcrypt_generic( $td, trim($str) ) );

		mcrypt_generic_deinit( $td );
		mcrypt_module_close( $td );

		return $encrypted_data;
	}

	static function decrypt( $str, $key = NULL ) {
		if ( $key == NULL OR $key == '' ) {
			global $config_vars;
			$key = $config_vars['other']['salt'];
		}

		if ( $str == '' ) {
			return FALSE;
		}

		//Check to make sure str is actually base64_encoded.
		if ( base64_encode( base64_decode($str, TRUE) ) !== $str ) {
			Debug::Arr($str, 'ERROR: String is not base64_encoded...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$td = mcrypt_module_open( 'tripledes', '', 'ecb', '' );
		$iv = mcrypt_create_iv( mcrypt_enc_get_iv_size($td), MCRYPT_RAND );
		$max_key_size = mcrypt_enc_get_key_size( $td );
		mcrypt_generic_init( $td, substr($key, 0, $max_key_size ), $iv );

		$unencrypted_data = rtrim( mdecrypt_generic( $td, base64_decode( $str ) ) );

		mcrypt_generic_deinit( $td );
		mcrypt_module_close( $td );

		return $unencrypted_data;
	}

	static function getJSArray( $values, $name = NULL, $assoc = FALSE, $object = FALSE ) {
		if ( $name != '' AND (bool)$assoc == TRUE ) {
			$retval = 'new Array();';
			if ( is_array($values) AND count($values) > 0 ) {
				foreach( $values as $key => $value ) {
					$retval .= $name.'[\''. $key .'\']=\''. $value .'\';';
				}
			}
		} elseif ( $name != '' AND (bool)$object == TRUE ) { //For multidimensional objects.
			$retval = ' {';
			if ( is_array($values) AND count($values) > 0 ) {
				foreach( $values as $key => $value ) {
					$retval .= $key.': ';
					if ( is_array($value ) ) {
						$retval .= '{';
						foreach( $value as $key2 => $value2 ) {
							$retval .= $key2.': \''. $value2 .'\', ';
						}
						$retval .= '}, ';
					} else {
						$retval .= $key.': \''. $value .'\', ';
					}
				}
			}
			$retval .= '} ';
		} else {
			$retval = 'new Array("';
			if ( is_array($values) AND count($values) > 0 ) {
				$retval .= implode('","', $values);
			}
			$retval .= '");';
		}

		return $retval;
	}

	//Uses the internal array pointer to get array neighnors.
	static function getArrayNeighbors( $arr, $key, $neighbor = 'both' ) {
		$neighbor = strtolower($neighbor);
		//Neighor can be: Prev, Next, Both

		$retarr = array( 'prev' => FALSE, 'next' => FALSE );

		$keys = array_keys($arr);
		$key_indexes = array_flip($keys);

		if ( $neighbor == 'prev' OR $neighbor == 'both' ) {
			if ( isset($keys[($key_indexes[$key] - 1)]) ) {
				$retarr['prev'] = $keys[($key_indexes[$key] - 1)];
			}
		}

		if ( $neighbor == 'next' OR $neighbor == 'both' ) {
			if ( isset($keys[($key_indexes[$key] + 1)]) ) {
				$retarr['next'] = $keys[($key_indexes[$key] + 1)];
			}
		}
		//next($arr);

		return $retarr;
	}

	static function getURLProtocol() {
		$retval = 'http';
		if ( Misc::isSSL() == TRUE ) {
			$retval .= 's';
		}

		return $retval;
	}

	static function getEmailDomain() {
		global $config_vars;
		
		if ( isset($config_vars['other']['email_domain']) AND $config_vars['other']['email_domain'] != '' ) {
			$domain = $config_vars['other']['email_domain'];
		} else {
			Debug::Text( 'No From Email Domain set, falling back to regular hostname...', __FILE__, __LINE__, __METHOD__, 10);
			$domain = self::getHostName( FALSE );
		}

		return $domain;
	}

	//Checks if the domain the user is seeing in their browser matches the configured domain that should be used.
	//If not we can then do a redirect.
	static function checkValidDomain() {
		global $config_vars;

		if ( PRODUCTION == TRUE AND isset($config_vars['other']['enable_csrf_validation']) AND $config_vars['other']['enable_csrf_validation'] == TRUE ) {
			//Use HTTP_HOST rather than getHostName() as the same site can be referenced with multiple different host names
			//Especially considering on-site installs that default to 'localhost'
			//If deployment ondemand is set, then we assume SERVER_NAME is correct and revert to using that instead of HTTP_HOST which has potential to be forged.
			//Apache's UseCanonicalName On configuration directive can help ensure the SERVER_NAME is always correct and not masked.
			if ( DEPLOYMENT_ON_DEMAND == FALSE AND isset( $_SERVER['HTTP_HOST'] ) ) {
				$host_name = $_SERVER['HTTP_HOST'];
			} elseif ( isset( $_SERVER['SERVER_NAME'] ) ) {
				$host_name = $_SERVER['SERVER_NAME'];
			} elseif ( isset( $_SERVER['HOSTNAME'] ) ) {
				$host_name = $_SERVER['HOSTNAME'];
			} else {
				$host_name = '';
			}

			global $config_vars;
			if ( isset($config_vars['other']['hostname']) AND $config_vars['other']['hostname'] != '' ) {
				$search_result = strpos( $config_vars['other']['hostname'], $host_name );
				if ( $search_result === FALSE OR (int)$search_result >= 8 ) { //Check to see if .ini hostname is found within SERVER_NAME in less than the first 8 chars, so we ignore https://.
					$redirect_url = Misc::getURLProtocol() .'://'. Misc::getHostName() . Environment::getDefaultInterfaceBaseURL();
					Debug::Text( 'Web Server Hostname: '. $host_name .' does not match .ini specified hostname: '. $config_vars['other']['hostname'] .' Redirect: '. $redirect_url, __FILE__, __LINE__, __METHOD__, 10);

					$rl = TTNew('RateLimit');
					$rl->setID( 'authentication_'. Misc::getRemoteIPAddress() );
					$rl->setAllowedCalls( 5 );
					$rl->setTimeFrame( 60 ); //1 minute

					sleep(1); //Help prevent fast redirect loops.
					if ( $rl->check() == FALSE ) {
						Debug::Text('ERROR: Excessive redirects... sending to down for maintenance page to stop the loop: '. Misc::getRemoteIPAddress() .' for up to 1 minutes...', __FILE__, __LINE__, __METHOD__, 10);
						Redirect::Page( URLBuilder::getURL( array('exception' => 'domain_redirect_loop' ), Environment::getBaseURL().'DownForMaintenance.php') );
					} else {
						Redirect::Page( URLBuilder::getURL( NULL, $redirect_url ) );
					}
				}
				//else {
				//	Debug::Text( 'Domain matches!', __FILE__, __LINE__, __METHOD__, 10);
				//}

			}
		}

		return TRUE;
	}

	//Checks refer to help mitigate CSRF attacks.
	static function checkValidReferer( $referer = FALSE ) {
		global $config_vars;
		
		if ( PRODUCTION == TRUE AND isset($config_vars['other']['enable_csrf_validation']) AND $config_vars['other']['enable_csrf_validation'] == TRUE ) {
			if ( $referer == FALSE ) {
				if ( isset($_SERVER['HTTP_ORIGIN']) AND $_SERVER['HTTP_ORIGIN'] != '' ) {
					//IE9 doesn't send this, but if it exists use it instead as its likely more trustworthy.
					//Debug::Text( 'Using Referer from Origin header...', __FILE__, __LINE__, __METHOD__, 10);
					$referer = $_SERVER['HTTP_ORIGIN'];
					if ( $referer == 'file://' ) { //Mobile App and some browsers can send the origin as: file://
						return TRUE;
					}
				} elseif ( isset($_SERVER['HTTP_REFERER']) AND $_SERVER['HTTP_REFERER'] != '' ) {
					$referer = $_SERVER['HTTP_REFERER'];
				} else {
					$referer = '';
				}
			}

			//Debug::Text( 'Raw Referer: '. $referer, __FILE__, __LINE__, __METHOD__, 10);
			$referer = parse_url( $referer, PHP_URL_HOST );

			//Use HTTP_HOST rather than getHostName() as the same site can be referenced with multiple different host names
			//Especially considering on-site installs that default to 'localhost'
			//If deployment ondemand is set, then we assume SERVER_NAME is correct and revert to using that instead of HTTP_HOST which has potential to be forged.
			//Apache's UseCanonicalName On configuration directive can help ensure the SERVER_NAME is always correct and not masked.
			if ( DEPLOYMENT_ON_DEMAND == FALSE AND isset( $_SERVER['HTTP_HOST'] ) ) {
				$host_name = $_SERVER['HTTP_HOST'];
			} elseif ( isset( $_SERVER['SERVER_NAME'] ) ) {
				$host_name = $_SERVER['SERVER_NAME'];
			} elseif ( isset( $_SERVER['HOSTNAME'] ) ) {
				$host_name = $_SERVER['HOSTNAME'];
			} else {
				$host_name = '';
			}
			$host_name = ( $host_name != '' ) ? parse_url( 'http://'.$host_name, PHP_URL_HOST ) : ''; //Need to add 'http://' so parse_url() can strip it off again.
			//Debug::Text( 'Parsed Referer: '. $referer .' Hostname: '. $host_name, __FILE__, __LINE__, __METHOD__, 10);

			if ( $referer == $host_name OR $host_name == '' ) {
				return TRUE;
			}

			Debug::Text( 'CSRF check failed... Parsed Referer: '. $referer .' Hostname: '. $host_name, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		return TRUE;
	}
	
	static function getHostNameWithoutSubDomain( $host_name ) {
		$split_host_name = explode('.', $host_name );
		if ( count($split_host_name) > 2 ) {
			unset($split_host_name[0]);
			return implode('.', $split_host_name);
		}

		return $host_name;
	}
	
	static function getHostName( $include_port = TRUE ) {
		global $config_vars;

		$server_port = NULL;
		if ( isset( $_SERVER['SERVER_PORT'] ) ) {
			$server_port = ':'.$_SERVER['SERVER_PORT'];
		}

		if ( defined('DEPLOYMENT_ON_DEMAND') AND DEPLOYMENT_ON_DEMAND == TRUE AND isset($config_vars['other']['hostname']) AND $config_vars['other']['hostname'] != '' ) {
			$server_domain = $config_vars['other']['hostname'];
		} else {
			//Try server hostname/servername first, than fallback on .ini hostname setting.
			//If the admin sets the hostname in the .ini file, always use that, as the servers hostname from the CLI could be incorrect.
			if ( isset($config_vars['other']['hostname']) AND $config_vars['other']['hostname'] != '' ) {
				$server_domain = $config_vars['other']['hostname'];
				if ( strpos( $server_domain, ':') === FALSE ) {
					//Add port if its not already specified.
					$server_domain .= $server_port;
				}
			} elseif ( isset( $_SERVER['HTTP_HOST'] ) ) { //Use HTTP_HOST instead of SERVER_NAME first so it includes any custom ports.
				$server_domain = $_SERVER['HTTP_HOST'];
			} elseif ( isset( $_SERVER['SERVER_NAME'] ) ) {
				$server_domain = $_SERVER['SERVER_NAME'].$server_port;
			} elseif ( isset( $_SERVER['HOSTNAME'] ) ) {
				$server_domain = $_SERVER['HOSTNAME'].$server_port;
			} else {
				Debug::Text( 'Unable to determine hostname, falling back to localhost...', __FILE__, __LINE__, __METHOD__, 10);
				$server_domain = 'localhost'.$server_port;
			}
		}

		if ( $include_port == FALSE ) {
			//strip off port, important for sending emails.
			$server_domain = str_replace( $server_port, '', $server_domain );
		}

		return $server_domain;
	}

	static function parseDatabaseHostString( $database_host_string ) {
		$retarr = array();

		$db_hosts = explode(',', $database_host_string );
		if ( is_array($db_hosts) ) {
			$i = 0;
			foreach( $db_hosts as $db_host ) {
				$db_host_split = explode( '#', $db_host );

				$db_host = $db_host_split[0];
				$weight = ( isset($db_host_split[1]) ) ? $db_host_split[1] : 1;

				$retarr[] = array( $db_host, ( $i == 0 ) ? 'master' : 'slave', $weight );

				$i++;
			}
		}

		//Debug::Arr( $retarr,  'Parsed Database Connections: ', __FILE__, __LINE__, __METHOD__, 1);
		return $retarr;
	}

	static function isOpenPort( $address, $port = 80, $timeout = 3 ) {
		$checkport = @fsockopen($address, $port, $errnum, $errstr, $timeout); //The 2 is the time of ping in secs

		//Check if port is closed or open...
		if( $checkport == FALSE ) {
			return FALSE;
		}

		return TRUE;
	}

	static function array_isearch( $str, $array ) {
		foreach ( $array as $key => $value ) {
			if ( strtolower( $value ) == strtolower( $str ) ) {
				return $key;
			}
		}

		return FALSE;
	}

	//Accepts a search_str and key=>val array that it searches through, to return the array key of the closest fuzzy match.
	static function findClosestMatch( $search_str, $search_arr, $minimum_percent_match = 0, $return_all_matches = FALSE ) {
		if ( $search_str == '' ) {
			return FALSE;
		}

		if ( !is_array($search_arr) OR count($search_arr) == 0 ) {
			return FALSE;
		}

		foreach( $search_arr as $key => $search_val ) {
			similar_text( strtolower($search_str), strtolower($search_val), $percent);
			if ( $percent >= $minimum_percent_match ) {
				$matches[$key] = $percent;
			}
		}

		if ( isset($matches) AND count($matches) > 0 ) {
			arsort($matches);

			if ( $return_all_matches == TRUE ) {
				return $matches;
			}

			//Debug::Arr( $search_arr, 'Search Str: '. $search_str .' Search Array: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr( $matches, 'Matches: ', __FILE__, __LINE__, __METHOD__, 10);

			reset($matches);
			return key($matches);
		}

		//Debug::Text('No match found for: '. $search_str, __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	//Converts a number between 0 and 25 to the corresponding letter.
	static function NumberToLetter( $number ) {
		if ( $number > 25 ) {
			return FALSE;
		}

		return chr( ($number + 65) );
	}

	static function issetOr( &$var, $default = NULL ) {
		if ( isset($var) ) {
			return $var;
		}

		return $default;
	}

	static function getFullName($first_name, $middle_name, $last_name, $reverse = FALSE, $include_middle = TRUE) {
		if ( $first_name != '' AND $last_name != '' ) {
			if ( $reverse === TRUE ) {
				$retval = $last_name .', '. $first_name;
				if ( $include_middle == TRUE AND $middle_name != '' ) {
					$retval .= ' '.$middle_name[0].'.'; //Use just the middle initial.
				}
			} else {
				$retval = $first_name .' '. $last_name;
			}

			return $retval;
		}

		return FALSE;
	}

	static function getCityAndProvinceAndPostalCode( $city, $province, $postal_code ) {
		$retval = '';
		if ( $city != '' ) {
			$retval .= $city;
		}

		if ( $province != '' AND $province != '00' ) {
			if ( $retval != '' ) {
				$retval .= ',';
			}
			$retval .= ' '. $province;
		}

		if ( $postal_code != '' ) {
			$retval .= ' '. strtoupper( $postal_code );
		}

		return $retval;
	}

	//Caller ID numbers can come in in all sorts of forms:
	// 2505551234
	// 12505551234
	// +12505551234
	// (250) 555-1234
	//Parse out just the digits, and use only the last 10 digits.
	//Currently this will not support international numbers
	static function parseCallerID( $number ) {
		$validator = new Validator();

		$retval = substr( $validator->stripNonNumeric( $number ), -10, 10 );

		return $retval;
	}

	static function generateCopyName( $name, $strict = FALSE ) {
		$name = str_replace( TTi18n::getText('Copy of'), '', $name );

		if ( $strict === TRUE ) {
			$retval = TTi18n::getText('Copy of').' '. $name;
		} else {
			$retval = TTi18n::getText('Copy of').' '. $name .' ['. rand(1, 99) .']';
		}

		$retval = substr( $retval, 0, 99 ); //Make sure the name doesn't get too long.
		return $retval;
	}

	static function generateShareName( $from, $name, $strict = FALSE ) {
		if ( $strict === TRUE ) {
			$retval = $name .' ('. TTi18n::getText('Shared by').': '. $from .')';
		} else {
			$retval = $name .' ('. TTi18n::getText('Shared by').': '. $from .') ['. rand(1, 99) .']';
		}

		$retval = substr( $retval, 0, 99 ); //Make sure the name doesn't get too long.
		return $retval;
	}

	/** Delete all files in directory
	* @param $path directory to clean
	* @param $recursive delete files in subdirs
	* @param $delDirs delete subdirs
	* @param $delRoot delete root directory
	* @access public
	* @return success
	*/
	static function cleanDir( $path, $recursive = FALSE, $del_dirs = FALSE, $del_root = FALSE, $exclude_regex_filter = NULL ) {
		$result = TRUE;

		if( !$dir = @dir($path) ) {
			return FALSE;
		}

		Debug::Text('Cleaning: '. $path .' Exclude Regex: '. $exclude_regex_filter, __FILE__, __LINE__, __METHOD__, 10);
		while( $file = $dir->read() ) {
			if( $file === '.' OR $file === '..' ) {
				continue;
			}

			$full = $dir->path . DIRECTORY_SEPARATOR . $file;

			if ( $exclude_regex_filter != '' AND preg_match( '/'. $exclude_regex_filter .'/i', $full) == 1 ) {
				continue;
			}

			if ( is_dir($full) AND $recursive == TRUE ) {
				$result = self::cleanDir( $full, $recursive, $del_dirs, $del_dirs, $exclude_regex_filter );
			} elseif( is_file($full) ) {
				$result = @unlink($full);
				//Debug::Text('Deleting: '. $full, __FILE__, __LINE__, __METHOD__, 10);
			}

		}
		$dir->close();

		if ( $del_root == TRUE ) {
			//Debug::Text('Deleting Dir: '. $dir->path, __FILE__, __LINE__, __METHOD__, 10);
			$result = @rmdir($dir->path);
		}

		return $result;
	}

	static function getFileList( $start_dir, $regex_filter = NULL, $recurse = FALSE ) {
		$files = array();
		if ( is_dir($start_dir) AND is_readable( $start_dir ) ) {
			$fh = opendir($start_dir);
			while ( ($file = readdir($fh)) !== FALSE ) {
				# loop through the files, skipping . and .., and recursing if necessary
				if ( strcmp($file, '.') == 0 OR strcmp($file, '..' ) == 0 ) {
					continue;
				}

				$filepath = $start_dir . DIRECTORY_SEPARATOR . $file;
				if ( is_dir($filepath) AND $recurse == TRUE ) {
					Debug::Text(' Recursing into dir: '. $filepath, __FILE__, __LINE__, __METHOD__, 10);

					$tmp_files = self::getFileList($filepath, $regex_filter, TRUE );
					if ( $tmp_files != FALSE AND is_array($tmp_files) ) {
						$files = array_merge( $files, $tmp_files );
					}
					unset($tmp_files);
				} elseif ( !is_dir( $filepath ) ) {
					if ( $regex_filter == '*' OR preg_match( '/'.$regex_filter.'/i', $file) == 1 ) {
						//Debug::Text(' Match: Dir: '. $start_dir .' File: '. $filepath, __FILE__, __LINE__, __METHOD__, 10);
						if ( is_readable($filepath) ) {
							array_push($files, $filepath);
						} else {
							Debug::Text(' Matching file is not read/writable: '. $filepath, __FILE__, __LINE__, __METHOD__, 10);
						}
					} // else { //Debug::Text(' NO Match: Dir: '. $start_dir .' File: '. $filepath, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
			closedir($fh);
			sort($files);
		} else {
			# false if the function was called with an invalid non-directory argument
			$files = FALSE;
		}

		//Debug::Arr( $files, 'Matching files: ', __FILE__, __LINE__, __METHOD__, 10);
		return $files;
	}

	static function convertObjectToArray( $obj ) {
		if ( is_object($obj) ) {
			$obj = get_object_vars($obj);
		}

		if ( is_array($obj) ) {
			return array_map( array( 'Misc', __FUNCTION__), $obj );
		} else {
			return $obj;
		}
	}

	static function getBytesFromSize($val) {
		$val = trim($val);

		switch ( strtolower( substr($val, -1) ) ) {
			case 'm':
				$val = ( (int)substr($val, 0, -1) * 1048576 );
				break;
			case 'k':
				$val = ( (int)substr($val, 0, -1) * 1024 );
				break;
			case 'g':
				$val = ( (int)substr($val, 0, -1) * 1073741824 );
				break;
			case 'b':
				switch ( strtolower(substr($val, -2, 1)) ) {
					case 'm':
						$val = ( (int)substr($val, 0, -2) * 1048576 );
						break;
					case 'k':
						$val = ( (int)substr($val, 0, -2) * 1024 );
						break;
					case 'g':
						$val = ( (int)substr($val, 0, -2) * 1073741824 );
						break;
					default:
						break;
				}
				break;
			default:
				break;
		}

		return $val;
	}

	static function getSystemMemoryInfo() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			$memory_file = '/proc/meminfo';
			if ( @file_exists( $memory_file ) AND is_readable( $memory_file ) ) {
				$buffer = file_get_contents( $memory_file );

				preg_match('/MemFree:\s+([0-9]+) kB/im', $buffer, $mem_free_match);
				if ( isset($mem_free_match[1]) ) {
					$mem_free = Misc::getBytesFromSize( (int)$mem_free_match[1].'K' );
					unset($mem_free_match);
				}

				preg_match('/Cached:\s+([0-9]+) kB/im', $buffer, $mem_cached_match);
				if ( isset($mem_cached_match[1]) ) {
					$mem_cached = Misc::getBytesFromSize( (int)$mem_cached_match[1].'K' );
					unset($mem_cached_match);
				}

				Debug::Text(' Memory Info: Free: '. $mem_free .'b Cached: '. $mem_cached .'b', __FILE__, __LINE__, __METHOD__, 10);
				return ( $mem_free + ( $mem_cached * ( 3 / 4 ) ) ); //Only allow up to 3/4 of cached memory to be used.
			}
		}

		return 2147483647; //If not linux, return large number, this is in Bytes.
	}
	
	static function getSystemLoad() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			$loadavg_file = '/proc/loadavg';
			if ( file_exists( $loadavg_file ) AND is_readable( $loadavg_file ) ) {
				$buffer = '0 0 0';
				$buffer = file_get_contents( $loadavg_file );
				$load = explode(' ', $buffer);

				//$retval = max((float)$load[0], (float)$load[1], (float)$load[2]);
				$retval = max((float)$load[0], (float)$load[1] ); //Only consider 1 and 5 minute load averages, so we don't block cron/reports for more than 5 minutes.
				//Debug::text(' Load Average: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

				return $retval;
			}
		}

		return 0;
	}

	static function isSystemLoadValid() {
		global $config_vars;

		if ( !isset($config_vars['other']['max_cron_system_load']) ) {
			$config_vars['other']['max_cron_system_load'] = 9999;
		}

		$system_load = Misc::getSystemLoad();
		if ( isset($config_vars['other']['max_cron_system_load']) AND $system_load <= $config_vars['other']['max_cron_system_load'] ) {
			Debug::text(' Load average within valid limits: Current: '. $system_load .' Max: '. $config_vars['other']['max_cron_system_load'], __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		Debug::text(' Load average NOT within valid limits: Current: '. $system_load .' Max: '. $config_vars['other']['max_cron_system_load'], __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	static function sendSystemMail( $subject, $body, $attachments = NULL, $force = FALSE ) {
		if ( $subject == '' OR $body == '' ) {
			return FALSE;
		}

		$to = 'errors@timetrex.com';
		
		global $config_vars;
		if ( isset($config_vars['other']['system_admin_email']) ) {
			if ( $config_vars['other']['system_admin_email'] != '' ) {
				$to = $config_vars['other']['system_admin_email'];
			} else {
				return FALSE;
			}
		}

		$cc = NULL;
		$from = APPLICATION_NAME.'@'.Misc::getHostName( FALSE );

		$headers = array(
							'From'	  => $from,
							'Subject' => $subject,
							'cc'	  => $cc,
							'Reply-To' => $to,
							'Return-Path' => $to,
							'Errors-To' => $to,
						);

		$mail = new TTMail();
		$mail->setTo( $to );
		$mail->setHeaders( $headers );
		//$mail->setBody( $body );

		@$mail->getMIMEObject()->setTXTBody($body);

		if ( is_array($attachments) ) {
			foreach( $attachments as $attachment ) {
				if ( isset($attachment['data']) AND isset($attachment['mime_type']) AND isset($attachment['file_name']) ) {
					@$mail->getMIMEObject()->addAttachment( $attachment['data'], $attachment['mime_type'], $attachment['file_name'], FALSE );
				}
			}
		}

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );

		$retval = $mail->Send( $force );

		return $retval;
	}

	static function disableCaching( $email_notification = TRUE ) {
		//In case the cache directory does not exist, disabling caching can prevent errors from occurring or punches to be missed.
		//So this should be enabled even for ON-DEMAND services just in case.
		if ( PRODUCTION == TRUE ) {
			//Disable caching to prevent stale cache data from being read, and further cache errors.
			$install_obj = new Install();
			$tmp_config_vars['cache']['enable'] = 'FALSE';
			$write_config_result = $install_obj->writeConfigFile( $tmp_config_vars );
			unset($install_obj, $tmp_config_vars);

			if ( $email_notification == TRUE ) {
				if ( $write_config_result == TRUE ) {
					$subject = APPLICATION_NAME. ' - Error!';
					$body = 'ERROR writing cache file, likely due to incorrect operating system permissions, disabling caching to prevent data corruption. This may result in '. APPLICATION_NAME .' performing slowly.'."\n\n";
					$body .= Debug::getOutput();
				} else {
					$subject = APPLICATION_NAME. ' - Error!';
					$body = 'ERROR writing config file, likely due to incorrect operating system permissions conflicts. Please correction permissions so '. APPLICATION_NAME .' can operate correctly.'."\n\n";
					$body .= Debug::getOutput();
				}
				return self::sendSystemMail( $subject, $body );
			}

			return TRUE;
		}

		return FALSE;
	}

	static function getMapURL( $address1, $address2, $city, $province, $postal_code, $country, $service = 'google' ) { //$service_params = array()
		if ( $address1 == '' AND $address2 == '' ) {
			return FALSE;
		}

		$url = NULL;

		//Expand the country code to the full country name?
		if ( strlen($country) == 2 ) {
			$cf = TTnew('CompanyFactory');

			$long_country = Option::getByKey($country, $cf->getOptions('country') );
			if ( $long_country != '' ) {
				$country = $long_country;
			}
		}

		if ( $service == 'google' ) {
			$base_url = 'maps.google.com/?z=16&q=';
			$url = $base_url. urlencode($address1.' '. $city .' '. $province .' '. $postal_code .' '. $country);
		}

		if ( $url != '' ) {
			return 'http://'.$url;
		}

		return FALSE;
	}

	static function isEmail( $email, $check_dns = TRUE, $error_level = TRUE ) {
		if ( !function_exists('is_email') ) {
			require_once(Environment::getBasePath().'/classes/misc/is_email.php');
		}

		$result = is_email( $email, $check_dns, $error_level );
		if ( $result === ISEMAIL_VALID ) {
			return TRUE;
		} else {
			Debug::Text('Result Code: '. $result, __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	static function getPasswordStrength( $password ) {
		if ( strlen( $password ) == 0 ) {
			return 1;
		}

		$strength = 0;

		//get the length of the password
		$length = strlen($password);

		//check if password is not all lower case
		if ( strtolower($password) != $password ) {
			$strength++;
		}

		//check if password is not all upper case
		if ( strtoupper($password) != $password ) {
			$strength++;
		}

		//check string length is 8-15 chars
		if ( $length >= 6 && $length <= 9 ) {
			$strength++;
		}

		//check if length is 16-35 chars
		if ( $length >= 10 && $length <= 15 ) {
			$strength += 2;
		}

		//check if length greater than 35 chars
		if ( $length > 15 ) {
			$strength += 3;
		}

		//get the numbers in the password
		preg_match_all('/[0-9]/', $password, $numbers);
		$strength += ( count($numbers[0]) * 2 );

		//check for special chars
		preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^\\\]/', $password, $specialchars);
		$strength += ( count($specialchars[0]) * 3 );

		//get the number of unique chars
		$chars = str_split($password);
		$num_unique_chars = count( array_unique($chars) );
		$strength += ( $num_unique_chars * 2 );

		//strength is a number 1-10;
		$strength = $strength > 99 ? 99 : $strength;
		$strength = floor( ( ( $strength / 10 ) + 1 ) );

		return $strength;
	}

	static function getCurrentCompanyProductEdition() {
		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = getTTProductEdition();
		if ( $product_edition_id >= TT_PRODUCT_PROFESSIONAL ) {
			global $current_company;
			if ( isset($current_company) AND is_object($current_company) ) {
				$product_edition_id = $current_company->getProductEdition();
			}
		}

		return $product_edition_id;
	}

	static function redirectMobileBrowser() {
		extract( FormVariables::GetVariables( array('desktop') ) );
		if ( !isset($desktop) ) {
			$desktop = 0;
		}
		if ( getTTProductEdition() != TT_PRODUCT_COMMUNITY AND $desktop != 1 ) {
			$browser = self::detectMobileBrowser();
			if ( $browser == 'ios' OR $browser == 'html5' OR $browser == 'android' ) {
				Redirect::Page( URLBuilder::getURL( NULL, Environment::getBaseURL().'/quick_punch/QuickPunchLogin.php' ) );
			}
		} else {
			Debug::Text('Desktop browser override: '. (int)$desktop, __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	static function redirectUnSupportedBrowser() {
		if ( self::isUnSupportedBrowser() == TRUE ) {
			Redirect::Page( URLBuilder::getURL( array('tt_version' => APPLICATION_VERSION, 'tt_edition' => getTTProductEdition() ), 'http://www.timetrex.com/supported_web_browsers.php' ) );
		}

		return TRUE;
	}

	static function isUnSupportedBrowser( $useragent = NULL ) {
		if ( $useragent == '' ) {
			if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
				$useragent = $_SERVER['HTTP_USER_AGENT'];
			} else {
				return FALSE;
			}
		}

		$retval = FALSE;

		if ( !class_exists('Browser') ) {
			require_once( Environment::getBasePath().'/classes/other/Browser.php');
		}

		$browser = new Browser( $useragent );

		//This is for the full web interface
		//IE < 9
		//Firefox < 24
		//Chrome < 32
		//Safari < 5
		//Opera < 12
		if ( $browser->getBrowser() == Browser::BROWSER_IE AND version_compare( $browser->getVersion(), 9, '<' ) ) {
			$retval = TRUE;
		}

		if ( $browser->getBrowser() == Browser::BROWSER_FIREFOX AND version_compare( $browser->getVersion(), 24, '<' ) ) {
			$retval = TRUE;
		}

		if ( $browser->getBrowser() == Browser::BROWSER_CHROME AND version_compare( $browser->getVersion(), 30, '<' ) ) {
			$retval = TRUE;
		}

		if ( $browser->getBrowser() == Browser::BROWSER_SAFARI AND version_compare( $browser->getVersion(), 5, '<' ) ) {
			$retval = TRUE;
		}

		if ( $browser->getBrowser() == Browser::BROWSER_OPERA AND version_compare( $browser->getVersion(), 12, '<' ) ) {
			$retval = TRUE;
		}

		if ( $retval == TRUE ) {
			Debug::Text('Unsupported Browser: '. $browser->getBrowser() .' Version: '. $browser->getVersion(), __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retval;
	}

	static function detectMobileBrowser( $useragent = NULL ) {
		if ( $useragent == '' ) {
			if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
				$useragent = $_SERVER['HTTP_USER_AGENT'];
			} else {
				return FALSE;
			}
		}

		//Mobile Browsers: We just need to know if they are WAP or HTML5 for now.
		$retval = FALSE;

		if ( preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent)
				OR preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4) ) ) {
			$retval = 'html5';

			//Check to see if its an iPhone/iPod/iPad
			if ( preg_match('/ip(hone|od|ad)/i', $useragent) ) {
				$retval = 'ios';
			} elseif ( preg_match('/android.+mobile/i', $useragent) ) { //Check to see if its an android browser
				$retval = 'android';
			}

			//WAP is dying and HTTP_ACCEPT seems to cause more problems than it solves, specifically with older blackberry phones.
			/*
			//if (	( isset( $_SERVER['HTTP_ACCEPT'] ) AND strpos( strtolower( $_SERVER['HTTP_ACCEPT'] ), 'application/vnd.wap.xhtml+xml' ) > 0 )
					// This HTTP_X_WAP_PROFILE seem to be specified for some android phones (LG) that support HTML5 and not actually wap by the looks of it.
					//( ( isset( $_SERVER['HTTP_X_WAP_PROFILE'] ) OR isset( $_SERVER['HTTP_PROFILE'] ) ) )
					//( ( isset( $_SERVER['HTTP_X_WAP_PROFILE'] ) ) )
			//	) {
			//	Debug::Text('WAP profile accepted... HTTP_ACCEPT: '. $_SERVER['HTTP_ACCEPT'] .' HTTP_X_WAP_PROFILE: '. $_SERVER['HTTP_X_WAP_PROFILE'] .' HTTP_PROFILE: '. $_SERVER['HTTP_PROFILE'], __FILE__, __LINE__, __METHOD__, 10);
			//	$retval = 'wap';
			//} else
			if ( preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap)/i', strtolower($useragent) ) ) {
				Debug::Text('WAP browser...', __FILE__, __LINE__, __METHOD__, 10);
				$retval = 'wap';
			} else {
				$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
				$mobile_agents = array(
					'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
					'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
					'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
					'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
					'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
					'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
					'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
					'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
					'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-');

				if ( in_array($mobile_ua, $mobile_agents) ) {
					Debug::Text('WAP Agent found...', __FILE__, __LINE__, __METHOD__, 10);
					$retval = 'wap';
				}
			}
			*/
		}

		//$retval = 'android';
		Debug::Text('User Agent: '. $useragent .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	//Take an amount and a distribution array of key => value pairs, value being a decimal percent (ie: 0.50 for 50%)
	//return an array with the same keys and resulting distribution between them.
	//Adding any remainder to the last key is the fastest.
	static function PercentDistribution( $amount, $percent_arr, $remainder_operation = 'last', $precision = 2 ) {
		//$percent_arr = array(
		//					'key1' => 0.505,
		//					'key2' => 0.495,
		//);
		if ( is_array($percent_arr) AND count($percent_arr) > 0 ) {
			$total = 0;
			foreach( $percent_arr as $key => $distribution_percent ) {
				$distribution_amount = bcmul( $amount, $distribution_percent, $precision );
				$retarr[$key] = $distribution_amount;

				$total = bcadd( $total, $distribution_amount, $precision );
			}

			//Add any remainder to the last key.
			if ( $total != $amount ) {
				$remainder_amount = bcsub($amount, $total, $precision);
				//Debug::Text('Found remainder: '. $remainder_amount, __FILE__, __LINE__, __METHOD__, 10);

				if ( $remainder_operation == 'first' ) {
					reset($retarr);
					$key = key($retarr);
				}
				$retarr[$key] = bcadd($retarr[$key], $remainder_amount, $precision );
			}

			//Debug::Text('Amount: '. $amount .' Total (After Remainder): '. array_sum( $retarr ), __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}

		return FALSE;
	}

	//Change the case of all values in an array
	static function arrayChangeValueCase( $input, $case = CASE_LOWER) {
		switch ($case) {
			case CASE_LOWER:
				return array_map('strtolower', $input);
				break;
			case CASE_UPPER:
				return array_map('strtoupper', $input);
				break;
			default:
				trigger_error('Case is not valid, CASE_LOWER or CASE_UPPER only', E_USER_ERROR);
				return FALSE;
		}

		return FALSE;
	}

	static function isWritable($path) {
		if ( $path[(strlen($path) - 1)] == '/' ) {
			return self::isWritable( $path . uniqid( mt_rand() ).'.tmp' );
		}

		if ( file_exists($path) ) {
			if ( !( $f = @fopen($path, 'r+') ) ) {
				return FALSE;
			}
			fclose($f);
			return TRUE;
		}

		if ( !( $f = @fopen($path, 'w') ) ) {
			return FALSE;
		}

		fclose($f);
		unlink($path);

		return TRUE;
	}

	static function getRemoteIPAddress() {
		global $config_vars;

		if ( isset($config_vars['other']['proxy_ip_address_header_name']) AND $config_vars['other']['proxy_ip_address_header_name'] != '' ) {
			$header_name = $config_vars['other']['proxy_ip_address_header_name'];
		}

		if ( isset($header_name) AND isset($_SERVER[$header_name]) AND $_SERVER[$header_name] != ''  ) {
			//Debug::text('Remote IP: '. $_SERVER['REMOTE_ADDR'] .' Behind Proxy IP: '. $_SERVER[$header_name], __FILE__, __LINE__, __METHOD__, 10);
			return $_SERVER[$header_name];
		} elseif( isset($_SERVER['REMOTE_ADDR']) ) {
			//Debug::text('Remote IP: '. $_SERVER['REMOTE_ADDR'], __FILE__, __LINE__, __METHOD__, 10);
			return $_SERVER['REMOTE_ADDR'];
		}

		return FALSE;
	}

	static function isSSL( $ignore_force_ssl = FALSE ) {
		global $config_vars;

		if ( isset($config_vars['other']['proxy_protocol_header_name']) AND $config_vars['other']['proxy_protocol_header_name'] != '' ) {
			$header_name = $config_vars['other']['proxy_protocol_header_name']; //'HTTP_X_FORWARDED_PROTO'; //X-Forwarded-Proto;
		}
		
		//ignore_force_ssl is used for things like cookies where we need to determine if SSL is *currently* in use, vs. if we want it to be used or not.
		if ( $ignore_force_ssl == FALSE AND isset($config_vars['other']['force_ssl']) AND ( $config_vars['other']['force_ssl'] == TRUE ) ) {
			return TRUE;
		} elseif (
					( isset($_SERVER['HTTPS']) AND ( strtolower($_SERVER['HTTPS']) == 'on' OR $_SERVER['HTTPS'] == '1' ) )
					OR
					//Handle load balancer/proxy forwarding with SSL offloading.
					( isset($header_name) AND isset($_SERVER[$header_name]) AND strtolower($_SERVER[$header_name]) == 'https'  )
				) {
			return TRUE;
		} elseif ( isset($_SERVER['SERVER_PORT']) AND ( $_SERVER['SERVER_PORT'] == '443' ) ) {
			return TRUE;
		}

		return FALSE;
	}

	static function MajorVersionCompare( $version1, $version2, $operator ) {
		$tmp_version1 = explode('.', $version1 ); //Return first two dot versions.
		array_pop( $tmp_version1 );
		$version1 = implode('.', $tmp_version1 );

		$tmp_version2 = explode('.', $version2 ); //Return first two dot versions.
		array_pop( $tmp_version2 );
		$version2 = implode('.', $tmp_version2 );

		//Debug::Text('Comparing: Version1: '. $version1 .' Version2: '. $version2 .' Operator: '. $operator, __FILE__, __LINE__, __METHOD__, 10);

		return version_compare( $version1, $version2, $operator);
	}

	static function getInstanceIdentificationString($primary_company, $system_settings ) {
		$version_string[] = 'Company:';
		$version_string[] = ( is_object($primary_company) ) ? $primary_company->getName() : 'N/A';
		$version_string[] = 'Edition: '. getTTProductEditionName();
		$version_string[] = 'Key:';
		$version_string[] = ( isset($system_settings) AND isset($system_settings['registration_key']) ) ? $system_settings['registration_key'] : 'N/A';
		$version_string[] = 'Version: '. APPLICATION_VERSION;

		return implode(' ', $version_string );
	}

	//Removes the word "the" from the beginning of strings and optionally places it at the end.
	//Primarily for client/company names like: The XYZ Company -> XYZ Company, The
	//Should often be used to sanitize metaphones.
	static function stripThe( $str, $add_to_end = FALSE ) {
		if ( stripos( $str, 'The ' ) === 0 ) {
			$retval = substr( $str, 4 );
			if ( $add_to_end == TRUE ) {
				$retval .= ', The';
			}
			return $retval;
		}

		return $str;
	}

	//Remove any HTML special char (before its encoded) from the string
	//Useful for things like government forms submitted in XML.
	static function stripHTMLSpecialChars( $str ) {
		return str_replace( array('&', '"', '\'', '>', '<'), '', $str );
	}

	//Returns TRUE/FALSE if the identifier is within the staged rollout period. 
	static function getStagedRollout( $identifier, $original_release_date, $max_rollout_days = 10, $force = FALSE ) {
		//Divide the max_rollout_days into 5 brackets.
		//In the first 20% of the rollout period, update 1% of the customers.
		//In the next 20% of the rollout period, update 25% of the customers.
		//In the next 20% of the rollout period, update 50% of the customers.
		//In the next 20% of the rollout period, update 75% of the customers.
		//In the last 20% of the rollout period, update 100% of the customers.
		$version_updated_date = TTDate::getBeginDayEpoch( $original_release_date );
		$version_released_days = floor( TTDate::getDays( ( time() - $version_updated_date ) ) );
		$days_remaining = ( $max_rollout_days - $version_released_days );
		if ( $days_remaining < 0 ) {
			$days_remaining = 0;
		}

		if ( $days_remaining >= ( $max_rollout_days - ( $max_rollout_days * 0.20 ) ) ) {
			$percent_chance = 1;
		} elseif ( $days_remaining >= ( $max_rollout_days - ( $max_rollout_days * 0.40 ) ) ) {
			$percent_chance = 25;
		} elseif ( $days_remaining >= ( $max_rollout_days - ( $max_rollout_days * 0.60 ) ) ) {
			$percent_chance = 50;
		} elseif ( $days_remaining >= ( $max_rollout_days - ( $max_rollout_days * 0.80 ) ) ) {
			$percent_chance = 75;
		} elseif ( $days_remaining >= ( $max_rollout_days - ( $max_rollout_days * 1.00 ) ) ) {
			$percent_chance = 100;
		}

		srand( hexdec( substr( $identifier, 0, 10 ) ) );
		$random_trigger = rand( 1, 100 );
		if ( $force == TRUE OR $random_trigger <= $percent_chance ) {
			$retval = TRUE;
		} else {
			$retval = FALSE;
		}

		Debug::text('Identifier: '. $identifier .' Original Release Date: '. TTDate::getDate('DATE+TIME', $original_release_date ) .' Rollout Days: '. $max_rollout_days .' Days Remaining: '. $days_remaining .' Percent Chance: '. $percent_chance .' Force: '. (int)$force .' Result: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	static function checkValidImage( $file_data ) {
		$mime_type = Misc::getMimeType( $file_data, TRUE );
		if ( strpos( $mime_type, 'image' ) !== FALSE ) {
			$file_size = strlen( $file_data );
			
			//use getimagesize() to make sure image isn't too large and actually is an image.
			$image_size = getimagesizefromstring( $file_data );
			Debug::Arr($size, 'Mime Type: '. $mime_type .' Bytes: '. $file_size .' Size: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($size) AND isset($size[0]) AND isset($size[1]) ) {
				$bytes_to_image_size_ratio = ( $file_size / ( $size[0] * $size[1] ) );
				Debug::Text('Bytes to image ratio: '. $bytes_to_image_size_ratio, __FILE__, __LINE__, __METHOD__, 10);

				//UNFINISHED!
				
				return TRUE;
			}

			return FALSE;
		}

		Debug::Text('Not a image, unable to process: Mime Type: '. $mime_type, __FILE__, __LINE__, __METHOD__, 10);
		return TRUE; //Isnt an image, don't bother processing...
	}

	static function formatAddress( $name, $address1 = FALSE, $address2 = FALSE, $city = FALSE, $province = FALSE, $postal_code = FALSE, $country = FALSE ) {
		if ( $name != '' ) {
			$retarr[] = $name;
		}
		
		if ( $address1 != '' ) {
			$retarr[] = $address1;
		}
		if ( $address2 != '' ) {
			$retarr[] = $address2;
		}

		if ( $city != '' ) {
			if ( $province != '' ) {
				$city .= ',';
			}
			$city_arr[] = $city;
		}
		if ( $province != '' ) {
			$city_arr[] = $province;
		}
		if ( $postal_code != '' ) {
			$city_arr[] = $postal_code;
		}

		if ( is_array($city_arr) ) {
			$retarr[] = implode(' ', $city_arr);
		}

		if ( $country != '' ) {
			$retarr[] = $country;
		}
		
		return implode("\n", $retarr );
	}

	static function getUniqueID() {
		global $config_vars;
		if ( isset($config_vars['other']['salt']) AND $config_vars['other']['salt'] != '' ) {
			$salt = $config_vars['other']['salt'];
		} else {
			$salt = uniqid( dechex( mt_rand() ), TRUE );
		}

		if ( function_exists('mcrypt_create_iv') ) {
			$retval = $salt . bin2hex( mcrypt_create_iv( 128, MCRYPT_DEV_URANDOM ) ); //Use URANDOM as it wont block if there isn't enough entropy.
		} else {
			$retval = uniqid( $salt . dechex( mt_rand() ), TRUE );
		}
	
		return $retval;
	}
}
?>
