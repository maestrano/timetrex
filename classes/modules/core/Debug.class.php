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
class Debug {
	static protected $enable = FALSE;			//Enable/Disable debug printing.
	static protected $verbosity = 5;			//Display debug info with a verbosity level equal or lesser then this.
	static protected $buffer_output = TRUE;		//Enable/Disable output buffering.
	static protected $debug_buffer = NULL;		//Output buffer.
	static protected $enable_tidy = FALSE;		//Enable/Disable tidying of output
	static protected $enable_display = FALSE;	//Enable/Disable displaying of debug output
	static protected $enable_log = FALSE;		//Enable/Disable logging of debug output
	static protected $max_line_size = 200;		//Max line size in characters. This is used to break up long lines.
	static protected $max_buffer_size = 1000;	//Max buffer size in lines. **Syslog can't handle much more than 1000.
	static protected $buffer_id = NULL;			//Unique identifier for the debug buffer.
	static protected $php_errors = 0;			//Count number of PHP errors so we can automatically email the log.

	static protected $buffer_size = 0;			//Current buffer size in lines.

	static $tidy_obj = NULL;

	static function setEnable($bool) {
		self::setBufferID();
		self::$enable = $bool;
	}
	static function getEnable() {
		return self::$enable;
	}

	static function setBufferOutput($bool) {
		self::$buffer_output = $bool;
	}

	static function setVerbosity($level) {
		global $db;

		self::$verbosity = $level;

		if (is_object($db) AND $level == 11) {
			$db->debug = TRUE;
		}
	}
	static function getVerbosity() {
		return self::$verbosity;
	}

	static function setEnableTidy($bool) {
		self::$enable_tidy = $bool;
	}
	static function getEnableTidy() {
		return self::$enable_tidy;
	}

	static function setEnableDisplay($bool) {
		self::$enable_display = $bool;
	}
	static function getEnableDisplay() {
		return self::$enable_display;
	}

	static function setEnableLog($bool) {
		self::$enable_log = $bool;
	}
	static function getEnableLog() {
		return self::$enable_log;
	}

	static function setBufferID() {
		if ( self::$buffer_id == NULL ) {
			self::$buffer_id = uniqid();
		}
	}

	static function getSyslogIdent( $extra_ident = NULL, $company_name = NULL ) {
		global $config_vars, $current_company;

		$suffix = NULL;
		if ( $company_name != '' ) {
			$suffix = $company_name;
		} elseif ( isset($current_company) AND is_object( $current_company ) ) {
			$suffix = $current_company->getShortName();
		} else {
			$suffix = 'System';
		}

		if ( isset($config_vars['debug']['syslog_ident']) AND $config_vars['debug']['syslog_ident'] != '' ) {
			$retval = $config_vars['debug']['syslog_ident'].'-'.$suffix.$extra_ident;
		} else {
			$retval = APPLICATION_NAME.'-'.$suffix.$extra_ident;
		}

		return preg_replace('/[^a-zA-Z0-9-]/', '', escapeshellarg( $retval ) ); //This will remove spaces.
	}
	//	Three primary log types: $log_types = array( 0 => 'debug', 1 => 'client', 2 => 'timeclock' );
	static function getSyslogFacility( $log_type = 0 ) {
		global $config_vars;
		if ( isset($config_vars['debug']['syslog_facility']) AND $config_vars['debug']['syslog_facility'] != '' ) {
			$facility_arr = explode( ',', $config_vars['debug']['syslog_facility'] );
			if ( is_array($facility_arr) AND isset( $facility_arr[(int)$log_type] ) ) {
				return ( is_numeric( $facility_arr[(int)$log_type] ) ) ? $facility_arr[(int)$log_type] : constant( $facility_arr[(int)$log_type] );
			}
		}

		return LOG_LOCAL7; //Default
	}
	static function getSyslogPriority( $log_type = 0 ) {
		global $config_vars;

		if ( isset($config_vars['debug']['syslog_priority']) AND $config_vars['debug']['syslog_priority'] != '' ) {
			$priority_arr = explode( ',', $config_vars['debug']['syslog_priority'] );
			if ( is_array($priority_arr) AND isset( $priority_arr[(int)$log_type] ) ) {
				return ( is_numeric( $priority_arr[(int)$log_type] ) ) ? $priority_arr[(int)$log_type] : constant( $priority_arr[(int)$log_type] );
			}
		}

		return LOG_DEBUG; //Default
	}

	//Used to add timing to each debug call.
	static function getExecutionTime() {
		return ceil( ( (microtime( TRUE ) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000 ) );
	}

	//Splits long debug lines or array dumps to prevent syslog overflows.
	static function splitInput( $text, $prefix = NULL, $suffix = NULL ) {
		if ( strlen( $text ) > self::$max_line_size ) {
			$retarr = array();

			$lines = explode("\n", $text ); //Split on newlines first.
			foreach( $lines as $line ) {
				$split_lines = str_split( $line, self::$max_line_size ); //Split on long lines next.
				foreach( $split_lines as $split_line ) {
					$retarr[] = $prefix.$split_line.$suffix;
				}
			}
			unset($lines, $line, $split_lines, $split_line);
		} else {
			$retarr = array( $prefix.$text.$suffix ); //Always returns an array.
		}

		return $retarr;
	}

	static function Text($text = NULL, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9) {
		if ( $verbosity > self::getVerbosity() OR self::$enable == FALSE ) {
			return FALSE;
		}

		if ( empty($method) ) {
			$method = "[Function]";
		}

		//If text is too long, split it into an array.
		$text_arr = self::splitInput( $text, 'DEBUG [L'. str_pad( $line, 4, 0, STR_PAD_LEFT) .'] ['. self::getExecutionTime() .'ms]:'. "\t" .''. $method .'(): ', "\n" );

		if ( self::$buffer_output == TRUE ) {
			foreach( $text_arr as $text_line ) {
				self::$debug_buffer[] = array($verbosity, $text_line);
				self::$buffer_size++;
				self::handleBufferSize( $line, $method );
			}
		} else {
			if ( self::$enable_display == TRUE ) {
				foreach( $text_arr as $text_line ) {
					echo $text_line;
				}
			} elseif ( OPERATING_SYSTEM != 'WIN' AND self::$enable_log == TRUE ) {
				foreach( $text_arr as $text_line ) {
					syslog(LOG_DEBUG, $text_line );
				}
			}
		}

		return TRUE;
	}

	static function profileTimers( $profile_obj ) {
		if ( !is_object($profile_obj) ) {
			return FALSE;
		}

		ob_start();
		$profile_obj->printTimers();
		$ob_contents = ob_get_contents();
		ob_end_clean();

		return $ob_contents;
	}

	static function backTrace() {
		//ob_start();
		//debug_print_backtrace();
		//$ob_contents = ob_get_contents();
		//ob_end_clean();
		//return $ob_contents;

		$retval = '';
		$trace_arr = debug_backtrace();
		if ( is_array($trace_arr) ) {
			$i = 0;
			foreach( $trace_arr as $trace_line ) {
				if ( isset($trace_line['class']) AND isset($trace_line['type'])	 ) {
					$class = $trace_line['class'].$trace_line['type'];
				} else {
					$class = NULL;
				}

				if ( isset($trace_line['args']) AND is_array($trace_line['args']) ) {
					$args = array();
					foreach( $trace_line['args'] as $arg ) {
						if ( is_array($arg) ) {
							if ( self::getVerbosity() == 11 ) {
								$args[] = self::varDump( $arg );
							} else {
								//Don't display the entire array is it polutes the log and is too large for syslog anyways.
								$args[] = 'Array('. count($arg) .')';
							}
						} elseif ( is_object($arg) ) {
							if ( self::getVerbosity() == 11 ) {
								$args[] = self::varDump( $arg );
							} else {
								//Don't display the entire array is it polutes the log and is too large for syslog anyways.
								$args[] = 'Object('. get_class( $arg ) .')';
							}

						} else {
							$args[] = $arg;
						}
					}
				}
				$retval .= '#'.$i.'.'. $class.$trace_line['function'].'('. implode(', ', $args) .')' ."\n";
				$i++;
			}
		}
		unset($trace_arr, $trace_line, $args);

		return $retval;
	}

	static function varDump( $array ) {
		ob_start();
		var_dump($array); //Xdebug may interfere with this and cause it to not display all the data...
		//print_r($array);
		$ob_contents = ob_get_contents();
		ob_end_clean();

		return $ob_contents;
	}

	static function Arr($array, $text = NULL, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9) {
		if ( $verbosity > self::getVerbosity() OR self::$enable == FALSE ) {
			return FALSE;
		}

		if ( empty($method) ) {
			$method = "[Function]";
		}

		$text_arr[] = 'DEBUG [L'. str_pad( $line, 4, 0, STR_PAD_LEFT) .'] ['. self::getExecutionTime() .'ms] Array: '. $method .'(): '. $text ."\n";
		$text_arr = array_merge( $text_arr, self::splitInput( self::varDump($array), NULL, "\n" ) );
		$text_arr[] = "\n";

		if (self::$buffer_output == TRUE) {
			foreach( $text_arr as $text_line ) {
				self::$debug_buffer[] = array($verbosity, $text_line);
				self::$buffer_size++;
				self::handleBufferSize( $line, $method );
			}
		} else {
			if ( self::$enable_display == TRUE ) {
				foreach( $text_arr as $text_line ) {
					echo $text_line;
				}
			} elseif ( OPERATING_SYSTEM != 'WIN' AND self::$enable_log == TRUE ) {
				foreach( $text_arr as $text_line ) {
					syslog(LOG_DEBUG, $text_line );
				}
			}
		}

		return TRUE;
	}

	static function ErrorHandler( $error_number, $error_str, $error_file, $error_line ) {
		//Only handle errors included in the error_reporting()
		if ( ( error_reporting() & $error_number ) ) { //Bitwise operator.
			// This error code is not included in error_reporting
			switch ( $error_number ) {
				case E_USER_ERROR:
					$error_name = 'FATAL';
					break;
				case E_USER_WARNING:
				case E_WARNING:
					$error_name = 'WARNING';
					break;
				case E_USER_NOTICE:
				case E_NOTICE:
					$error_name = 'NOTICE';
					break;
				case E_STRICT:
					$error_name = 'STRICT';

					//Don't show STRICT errors when using the legacy HTML interface with PHP v5.4
					if ( defined( 'TIMETREX_AMF_API' ) == FALSE AND defined( 'TIMETREX_JSON_API' ) == FALSE AND defined( 'TIMETREX_SOAP_API' ) == FALSE ) {
						return TRUE;
					}
					break;
				case E_DEPRECATED:
					$error_name = 'DEPRECATED';
					break;
				default:
					$error_name = 'UNKNOWN';
			}

			$error_name .= '('. $error_number .')';

			$text = 'PHP ERROR - '. $error_name .': '. $error_str .' File: '. $error_file .' Line: '. $error_line;

			self::$php_errors++;
			self::Text( $text, $error_file, $error_line, __METHOD__, 1 );
			self::Text( self::backTrace(), $error_file, $error_line, __METHOD__, 1 );
		}

		return FALSE; //Let the standard PHP error handler work as well.
	}

	static function Shutdown() {
		$error = error_get_last();
		if ( $error !== NULL AND isset($error['type']) AND $error['type'] == 1 ) { //Only trigger fatal errors on shutdown.
			self::$php_errors++;
			self::Text('PHP ERROR - FATAL('. $error['type'] .'): '. $error['message'] .' File: '. $error['file'] .' Line: '. $error['line'], $error['file'], $error['line'], __METHOD__, 1 );

			if ( defined('TIMETREX_API') AND TIMETREX_API == TRUE ) { //Only when a fatal error occurs.
				global $amf_message_id;
				if ( $amf_message_id != '' ) {
					$progress_bar = new ProgressBar();
					$progress_bar->error( $amf_message_id, TTi18n::getText('ERROR: Operation cannot be completed.') );
					unset($progress_bar);
				}
			}
		}

		if ( self::$php_errors > 0 ) {
			self::Text('Detected PHP errors ('. self::$php_errors .'), emailing log...');
			self::Text('---------------[ '. Date('d-M-Y G:i:s O') .' (PID: '.getmypid().') ]---------------');

			self::emailLog();
			if ( $error !== NULL ) { //Fatal error, write to log once more as this won't be called automatically.
				self::writeToLog();
			}
		}

		return TRUE;
	}

	static function getOutput() {
		$output = NULL;
		if ( count(self::$debug_buffer) > 0 ) {
			foreach (self::$debug_buffer as $arr) {
				$verbosity = $arr[0];
				$text = $arr[1];

				if ($verbosity <= self::getVerbosity() ) {
					$output .= $text;
				}
			}

			return $output;
		}

		return FALSE;
	}

	static function emailLog() {
		if ( PRODUCTION === TRUE ) {
			$output = self::getOutput();

			if (strlen($output) > 0) {
				$server_domain = Misc::getHostName();
				Misc::sendSystemMail( APPLICATION_NAME. ' - Error!', $output );
			}
		}
		return TRUE;
	}

	static function writeToLog() {
		if (self::$enable_log == TRUE AND self::$buffer_output == TRUE) {
			global $config_vars;

			$file_name = $config_vars['path']['log'] . DIRECTORY_SEPARATOR .'timetrex.log';

			$eol = "\n";

			$output = $eol.'---------------[ '. @date('d-M-Y G:i:s O') .' (PID: '.getmypid().') ]---------------'.$eol;
			if ( is_array( self::$debug_buffer ) ) {
				foreach (self::$debug_buffer as $arr) {
					if ( $arr[0] <= self::getVerbosity() ) {
						$output .= $arr[1];
					}
				}
			}
			$output .= '---------------[ '. @date('d-M-Y G:i:s O') .' (PID: '.getmypid().') ]---------------'.$eol;

			if ( isset($config_vars['debug']['enable_syslog']) AND $config_vars['debug']['enable_syslog'] == TRUE AND OPERATING_SYSTEM != 'WIN' ) {
				//If using rsyslog, need to set:
				//$MaxMessageSize 256000 #Above ModuleLoad imtcp
				openlog( self::getSyslogIdent(), 11, self::getSyslogFacility( 0 ) ); //11 = LOG_PID | LOG_NDELAY | LOG_CONS
				syslog( self::getSyslogPriority( 0 ), $output ); //Used to strip_tags output, but that was likely causing problems with SQL queries with >= and <= in them.
				closelog();
			} elseif ( is_writable( $config_vars['path']['log'] ) ) {
				$fp = @fopen( $file_name, 'a' );
				@fwrite($fp, $output ); //Used to strip_tags output, but that was likely causing problems with SQL queries with >= and <= in them.
				@fclose($fp);
				unset($output);
			}

			return TRUE;
		}

		return FALSE;
	}

	static function Display() {
		if (self::$enable_display == TRUE AND self::$buffer_output == TRUE) {

			$output = self::getOutput();

			if ( function_exists('memory_get_usage') ) {
				$memory_usage = memory_get_usage();
			} else {
				$memory_usage = "N/A";
			}

			if (strlen($output) > 0) {
				echo "\nDebug Buffer\n";
				echo "============================================================================\n";
				echo "Memory Usage: ". $memory_usage ." Buffer Size: ". self::$buffer_size."\n";
				echo "----------------------------------------------------------------------------\n";
				echo $output;
				echo "============================================================================\n";
			}

			return TRUE;
		}

		return FALSE;
	}

	static function Tidy() {
		if (self::$enable_tidy == TRUE ) {

			$tidy_config = Environment::getBasePath() .'/includes/tidy.conf';

			self::$tidy_obj = tidy_parse_string( ob_get_contents(), $tidy_config );

			//erase the output buffer
			ob_clean();

			//tidy_clean_repair();
			self::$tidy_obj->cleanRepair();

			echo self::$tidy_obj;

		}
		return TRUE;
	}

	static function DisplayTidyErrors() {
		if ( self::$enable_tidy == TRUE
				AND ( tidy_error_count(self::$tidy_obj) > 0 OR tidy_warning_count(self::$tidy_obj) > 0 ) ) {
			echo "\nTidy Output<\n";
			echo "============================================================================\n";
			echo htmlentities( self::$tidy_obj->errorBuffer );
			echo "============================================================================\n";
		}
	}

	static function handleBufferSize( $line = NULL, $method = NULL) {
		//When buffer exceeds maximum size, write it to the log and clear it.
		//This will affect displaying large buffers though, but otherwise we may run out of memory.
		if ( self::$buffer_size >= self::$max_buffer_size ) {
			self::$debug_buffer[] = array(1, 'DEBUG [L'. $line .'] ['. self::getExecutionTime() .'ms]:'. "\t" .''. $method .'(): Maximum debug buffer size of: '. self::$max_buffer_size .' reached. Writing out buffer before continuing... Buffer ID: '. self::$buffer_id ."\n" );
			self::writeToLog();
			self::clearBuffer();
			self::$debug_buffer[] = array(1, 'DEBUG [L'. $line .'] ['. self::getExecutionTime() .'ms]:'. "\t" .''. $method .'(): Continuing debug output from Buffer ID: '. self::$buffer_id ."\n" );

			return TRUE;
		}

		return FALSE;
	}

	static function clearBuffer() {
		self::$debug_buffer = NULL;
		self::$buffer_size = 0;
		return TRUE;
	}
}
?>
