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
 * $Revision: 5014 $
 * $Id: import.php 5014 2011-07-20 17:50:39Z ipso $
 * $Date: 2011-07-20 10:50:39 -0700 (Wed, 20 Jul 2011) $
 */
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'api'. DIRECTORY_SEPARATOR .'client'. DIRECTORY_SEPARATOR .'TimeTrexClientAPI.class.php');

if ( $argc < 3 OR in_array ($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: import.php [OPTIONS] [Column MAP file] [CSV File]\n";
	$help_output .= "\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -server <URL>				URL to API server\n";
	$help_output .= "    -username <username>		API username\n";
	$help_output .= "    -password <password>		API password\n";
	$help_output .= "    -object <object>			Object to import (ie: User,Branch,Punch)\n";
	$help_output .= "    -f <flag>				Custom flags\n";
	$help_output .= "    -n 					Dry-run, display the first two lines to confirm mapping is correct\n";

	echo $help_output;

} else {
	//Handle command line arguments
	$last_arg = count($argv)-1;

	if ( in_array('-n', $argv) ) {
		$dry_run = TRUE;
	} else {
		$dry_run = FALSE;
	}

	if ( in_array('-server', $argv) ) {
		$api_url = strtolower( trim($argv[array_search('-server', $argv)+1]) );
	} else {
		$api_url = FALSE;
	}

	if ( in_array('-username', $argv) ) {
		$username = strtolower( trim($argv[array_search('-username', $argv)+1]) );
	} else {
		$username = FALSE;
	}

	if ( in_array('-password', $argv) ) {
		$password = strtolower( trim($argv[array_search('-password', $argv)+1]) );
	} else {
		$password = FALSE;
	}

	if ( in_array('-object', $argv) ) {
		$object = trim($argv[array_search('-object', $argv)+1]);
	} else {
		$object = FALSE;
	}

	if ( isset($argv[$last_arg-1]) AND $argv[$last_arg-1] != '' ) {
		if ( !file_exists( $argv[$last_arg-1] ) OR !is_readable( $argv[$last_arg-1] ) ) {
			echo "Column MAP File: ". $argv[$last_arg-1] ." does not exist or is not readable!\n";
		} else {
			$column_map_file = $argv[$last_arg-1];
		}
	}

	if ( isset($argv[$last_arg]) AND $argv[$last_arg] != '' ) {
		if ( !file_exists( $argv[$last_arg] ) OR !is_readable( $argv[$last_arg] ) ) {
			echo "Import CSV File: ". $argv[$last_arg] ." does not exist or is not readable!\n";
		} else {
			$import_csv_file = $argv[$last_arg];
		}
	}

	if ( !isset($column_map_file) ) {
		echo "Column Map File not set!\n";
		exit;
	}

	function parseCSV($file, $head = FALSE, $first_column = FALSE, $delim="," , $len = 9216, $max_lines = NULL ) {
		if ( !file_exists($file) ) {
			Debug::text('Files does not exist: '. $file, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$return = false;
		$handle = fopen($file, "r");
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

		$i=1;
		while ( ($data = fgetcsv($handle, $len, $delim) ) !== FALSE) {
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

		fclose($handle);

		return $return;
	}

	$TIMETREX_URL = $api_url;

	$api_session = new TimeTrexClientAPI();
	$api_session->Login( $username, $password );
	if ( $TIMETREX_SESSION_ID == FALSE ) {
		echo "API Username/Password is incorrect!\n";
		exit;
	}
	echo "Session ID: $TIMETREX_SESSION_ID\n";

	if ( $object != '' ) {
		$column_map = parseCSV( $column_map_file, TRUE, FALSE, ',', 9216 );
		if ( is_array($column_map) ) {
			foreach( $column_map as $column_map_row ) {
				$column_map_arr[$column_map_row['timetrex_column']] = array( 'map_column_name' => $column_map_row['csv_column'], 'default_value' => $column_map_row['default_value'], 'parse_hint' => $column_map_row['parse_hint'] );
			}
		} else {
			echo "Column map is invalid!\n";
		}

		$obj = new TimeTrexClientAPI( 'Import'. $object );
		$obj->setRawData( file_get_contents( $import_csv_file ) );
		//var_dump( $obj->getOptions('columns') );

		$retval = $obj->Import( $column_map_arr, array('fuzzy_match' => TRUE), $dry_run );
		if ( $retval->getResult() == TRUE ) {
			echo "Import successful!\n";
		} else {
			echo $retval;
			exit(1);
		}
	}
}
?>
