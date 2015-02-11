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

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'api'. DIRECTORY_SEPARATOR .'client'. DIRECTORY_SEPARATOR .'TimeTrexClientAPI.class.php');

//Example: php export_report.php -server "http://192.168.1.1/timetrex/api/soap/api.php" -username myusername -password mypass -report UserSummaryReport -template "by_employee+contact" /tmp/employee_list.csv csv
if ( $argc < 3 OR in_array ($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: export_report.php [OPTIONS] [output file] [file format]\n";
	$help_output .= "\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -server <URL>				URL to API server\n";
	$help_output .= "    -username <username>		API username\n";
	$help_output .= "    -password <password>		API password\n";
	$help_output .= "    -report <report>			Report to export (ie: TimesheetDetailReport,TimesheetSummaryReport,ScheduleSummaryReport,UserSummaryReport,PayStubSummaryReport)\n";
	$help_output .= "    -saved_report <name>		Name of saved report\n";
	$help_output .= "    -template <template>		Name of template\n";
	$help_output .= "    -time_period <name>		Time Period for report\n";

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

	if ( in_array('-report', $argv) ) {
		$report = trim($argv[array_search('-report', $argv)+1]);
	} else {
		$report = FALSE;
	}

	if ( in_array('-template', $argv) ) {
		$template = trim($argv[array_search('-template', $argv)+1]);
	} else {
		$template = FALSE;
	}

	if ( in_array('-saved_report', $argv) ) {
		$saved_report = trim($argv[array_search('-saved_report', $argv)+1]);
	} else {
		$saved_report = FALSE;
	}

	if ( in_array('-time_period', $argv) ) {
		$time_period = trim($argv[array_search('-time_period', $argv)+1]);
	} else {
		$time_period = FALSE;
	}

	$output_file = NULL;
	if ( isset($argv[$last_arg-1]) AND $argv[$last_arg-1] != '' ) {
		$output_file = $argv[$last_arg-1];
	}

	$file_format = 'csv';
	if ( isset($argv[$last_arg]) AND $argv[$last_arg] != '' ) {
		$file_format = $argv[$last_arg];
	}

	if ( !isset($output_file) ) {
		echo "Output File not set!\n";
		exit;
	}

	$TIMETREX_URL = $api_url;

	$api_session = new TimeTrexClientAPI();
	$api_session->Login( $username, $password );
	if ( $TIMETREX_SESSION_ID == FALSE ) {
		echo "API Username/Password is incorrect!\n";
		exit(1);
	}
	//echo "Session ID: $TIMETREX_SESSION_ID\n";

	if ( $report != '' ) {
		$report_obj = new TimeTrexClientAPI( $report );

		$config = array();
		if ( $saved_report != '' ) {
			$saved_report_obj = new TimeTrexClientAPI( 'UserReportData' );
			$saved_report_result = $saved_report_obj->getUserReportData( array('filter_data' => array('name' => trim($saved_report) ) ) );
			$saved_report_data = $saved_report_result->getResult();
			if ( is_array($saved_report_data) AND isset($saved_report_data[0]) AND isset($saved_report_data[0]['data']) ) {
				$config = $saved_report_data[0]['data']['config'];
			} else {
				echo "ERROR: Saved report not found...\n";
				exit(1);
			}
		} elseif ( $template != '' ) {
			$config_result = $report_obj->getTemplate( $template );
			$config = $config_result->getResult();
		}

		if ( $time_period != '' AND isset($config['-1010-time_period']) ) {
			$config['-1010-time_period']['time_period'] = $time_period;
		}
		//var_dump($config);

		$result = $report_obj->getReport( $config, strtolower($file_format) );
		$retval = $result->getResult();
		if ( is_array($retval) ) {
			if ( isset($retval['file_name']) AND $output_file == '' ) {
				$output_file = $retval['file_name'];
			}
			file_put_contents( $output_file, base64_decode($retval['data']) );
		} else {
			var_dump($retval);
			echo "ERROR: No report data...\n";
			exit(1);
		}
	} else {
		echo "ERROR: No report specified...\n";
		exit(1);
	}
}
?>
