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

/*
 - Example config array:

 $log_rotate_config[] = array(
							'directory' => '/var/log/timetrex',
							'recurse' => TRUE,
							'file' => '*',
							'frequency' => 'DAILY',
							'history' =>  5 );
*/
class LogRotate {

	private $config_arr = array();

	function __construct( $config_arr = NULL ) {
		$this->config_arr = $config_arr;
		return TRUE;
	}

	function addConfig( $arr ) {
		$this->config_arr[] = $arr;
		return TRUE;
	}

	function getFileList( $start_dir, $regex_filter = NULL, $recurse = FALSE ) {
		return Misc::getFileList( $start_dir, $regex_filter, $recurse );
	}

	function isFileReadyToRotate( $file, $frequency = 'daily' ) {
		$retval = FALSE;

		if ( file_exists( $file ) ) {
			//Linux doesnt store when the file was created, so we have to base this on when we last ran.
			//$file_created_time = filectime( $file );
			//Debug::Text(' File: '. $file .' was created: '. date('r', $file_created_time), __FILE__, __LINE__, __METHOD__, 10);

			switch( strtolower($frequency) ) {
				case 'always':
					$retval = TRUE;
					break;
				case 'daily':
					$retval = TRUE;
					break;
			}
		}

		return $retval;
	}

	//Checks to see if the file has a numeric extension signifying that it is not a primary file and has already been rotated.
	function isFileRotatable( $file ) {
		$extension = pathinfo( $file, PATHINFO_EXTENSION );
		//Debug::Text(' File:  '. $file .' Extension: '. $extension, __FILE__, __LINE__, __METHOD__, 10);

		//Only rotate if the file size is greater then 0 bytes.
		if ( !is_numeric( $extension ) AND file_exists($file) AND filesize( $file ) > 0 ) {
			return TRUE;
		}

		return FALSE;
	}

	function getRotatedHistoryFiles( $files, $primary_file ) {
		if ( is_array($files) ) {
			foreach( $files as $key => $filename ) {
				$pattern = '/'. str_replace( array('/', '\\'), array('\\', '\\\\'), $primary_file) .'\.[0-9]{1,2}/i';
				//Debug::Text(' Pattern: '. $pattern, __FILE__, __LINE__, __METHOD__, 10);
				if ( preg_match( $pattern, $filename) == 1 ) {
					$retarr[] = $filename;
				}
			}
		}

		if ( isset($retarr) AND is_array($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	function padExtension( $extension, $history ) {
		if ( strlen($history) < 2 ) {
			$pad_length = 2;
		} else {
			$pad_length = strlen($history);
		}
		return str_pad( $extension, $pad_length, '0', STR_PAD_LEFT );
	}

	function handleHistoryFiles( $files, $history = 0 ) {
		if ( is_array($files) ) {
			rsort($files);
			foreach( $files as $key => $filename ) {
				$path_info = pathinfo( $filename );

				$new_extension = $this->padExtension( ((int)$path_info['extension'] + 1), $history );
				$new_file = $path_info['dirname'] . DIRECTORY_SEPARATOR . $path_info['filename'] . '.' . $new_extension;

				if ( $new_extension > $history AND is_writable( $filename ) ) {
					Debug::Text(' Found last file in history, delete rather then rename: '. $filename, __FILE__, __LINE__, __METHOD__, 10);
					unlink( $filename );
				} else {
					if ( file_exists( $filename ) AND !file_exists( $new_file ) ) {
						Debug::Text(' Renaming: '. $filename .' To: '. $new_file, __FILE__, __LINE__, __METHOD__, 10);

						rename( $filename, $new_file );
					} else {
						Debug::Text(' Unable to rename file, file does not exist or new name does exist or we do not have permission: '. $new_file, __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	function _rotate( $files, $rotate_config ) {
		if ( is_array($files) ) {
			foreach( $files as $key => $filename ) {
				if ( $this->isFileRotatable( $filename ) == TRUE ) {
					Debug::Text(' File IS a primary log file: '. $filename, __FILE__, __LINE__, __METHOD__, 10);

					if ( $this->isFileReadyToRotate( $filename, $rotate_config['frequency']) == TRUE ) {
						Debug::Text(' File is old enough to be rotated: '. $filename, __FILE__, __LINE__, __METHOD__, 10);

						$this->handleHistoryFiles( $this->getRotatedHistoryFiles( $files, $filename ), $rotate_config['history'] );

						//Rename primary log file
						$new_file = $filename . '.' . $this->padExtension( 1, $rotate_config['history'] );
						if ( file_exists( $filename ) AND !file_exists( $new_file ) ) {
							Debug::Text(' Renaming primary log file: '. $filename .' To: '. $new_file, __FILE__, __LINE__, __METHOD__, 10);
							rename( $filename, $new_file);
						} else {
							Debug::Text(' NOT Renaming primary log file: '. $filename, __FILE__, __LINE__, __METHOD__, 10);
						}
						unset($new_file);

					} else {
						Debug::Text(' File does not need to be rotated yet: '. $filename, __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::Text(' File is not a primary log file: '. $filename, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		return TRUE;
	}

	function Rotate() {
		//Loop through config entries
		if ( is_array($this->config_arr) AND isset($this->config_arr[0]) ) {
			foreach( $this->config_arr as $rotate_config ) {
				//Debug::Arr($rotate_config, ' Log Rotate Config: ', __FILE__, __LINE__, __METHOD__, 10);
				if ( isset($rotate_config['directory']) AND $rotate_config['directory'] != '' ) {
					Debug::Text(' Rotating Logs: Dir: '. $rotate_config['directory'] .' File: '. $rotate_config['file'], __FILE__, __LINE__, __METHOD__, 10);

					$files = $this->getFileList( $rotate_config['directory'], $rotate_config['file'], $rotate_config['recurse']);
					//Debug::Arr( $files, 'Matching files: ', __FILE__, __LINE__, __METHOD__, 10);

					if ( is_array( $files ) AND count($files) > 0 ) {
						$this->_rotate( $files, $rotate_config );
					} else {
						Debug::Text(' No files to rotate...', __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}
		} else {
			Debug::Text(' No config loaded!', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}
}
?>