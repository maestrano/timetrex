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
 * $Revision: 9521 $
 * $Id: import_punches.php 9521 2013-04-08 23:09:52Z ipso $
 * $Date: 2013-04-08 16:09:52 -0700 (Mon, 08 Apr 2013) $
 */
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

//Importing many punches can use a lot of memory if debugging is enabled and being buffered.
Debug::setEnable( FALSE );
Debug::setEnableDisplay( FALSE );
Debug::setBufferOutput( FALSE );
Debug::setEnableLog( FALSE );
Debug::setVerbosity( 0 );

//
//
// Custom functions to parse each individual column
//
//
function parse_status_id( $input, $default_value = NULL, $parse_hint = NULL ) {

	if ( strtolower( $input ) == 'i'
			OR strtolower( $input ) == 'in' ) {
		$retval = 10;
	} else {
		$retval = 20;
	}

	return $retval;
}

function parse_type_id( $input, $default_value = NULL, $parse_hint = NULL ) {

	if ( strtolower( $input ) == 'b'
			OR strtolower( $input ) == 'break' ) {
		$retval = 30;
	} elseif ( strtolower( $input ) == 'l'
			OR strtolower( $input ) == 'lunch' ) {
		$retval = 20;
	} else {
		//Normal
		$retval = 10;
	}

	return $retval;
}

function parse_branch_id( $input, $default_value = NULL, $parse_hint = NULL ) {
	global $branch_options;

	if ( !is_numeric( $input ) ) {
		return array_search( $input, $branch_options );
	}

	return $input;
}

function parse_department_id( $input, $default_value = NULL, $parse_hint = NULL ) {
	global $department_options;

	if ( !is_numeric( $input ) ) {
		return array_search( $input, $department_options );
	}

	return $input;
}

function parse_job_id( $input, $default_value = NULL, $parse_hint = NULL ) {
	global $job_options;

	if ( !is_numeric( $input ) ) {
		return array_search( $input, $job_options );
	}

	return $input;
}

function parse_task_id( $input, $default_value = NULL, $parse_hint = NULL ) {
	global $job_item_options;

	if ( !is_numeric( $input ) ) {
		return array_search( $input, $job_item_options );
	}

	return $input;
}

function parse_time_stamp( $input, $default_value = NULL, $parse_hint = NULL ) {
	//Use this to manually force a specific timezone.
	//TTDate::setTimeZone('GMT');

	if ( strpos( $parse_hint, '#') !== FALSE ) {
		$split_parse_hint = explode('#', $parse_hint);
	}

	if ( isset($split_parse_hint[0]) AND $split_parse_hint[0] != '' ) {
		TTDate::setDateFormat( $split_parse_hint[0] );
	}
	if ( isset($split_parse_hint[1]) AND $split_parse_hint[1] != '' ) {
		TTDate::setTimeFormat( $split_parse_hint[1] );
	} else {
		TTDate::setTimeFormat( 'g:i A' );
	}

	return TTDate::parseDateTime( $input );
}

//
//
// Main
//
//
if ( $argc < 3 OR in_array ($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: import_punches.php [OPTIONS] [Column MAP file] [CSV File]\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -n 			Dry-run, display the first two lines to confirm mapping is correct\n";
	echo $help_output;

} else {
	//Handle command line arguments
	$last_arg = count($argv)-1;

	if ( in_array('-n', $argv) ) {
		$dry_run = TRUE;
	} else {
		$dry_run = FALSE;
	}

	if ( isset($argv[$last_arg-1]) AND $argv[$last_arg-1] != '' ) {
		if ( !file_exists( $argv[$last_arg-1] ) OR !is_readable( $argv[$last_arg-1] ) ) {
			echo "Column MAP File: ". $argv[$last_arg-1] ." does not exists or is not readable!\n";
		} else {
			$column_map_file = $argv[$last_arg-1];
		}
	}

	if ( isset($argv[$last_arg]) AND $argv[$last_arg] != '' ) {
		if ( !file_exists( $argv[$last_arg] ) OR !is_readable( $argv[$last_arg] ) ) {
			echo "Import CSV File: ". $argv[$last_arg] ." does not exists or is not readable!\n";
		} else {
			$import_csv_file = $argv[$last_arg];
		}
	}


	//Import map file, confirm it is correct.
	$import_map_arr = Misc::parseCSV( $column_map_file, TRUE );
	if ( !is_array( $import_map_arr ) ) {
		echo "Parsing column map file failed!\n";
	} else {
		echo "Column Mappings...\n";

		foreach( $import_map_arr as $map_cols ) {
			//If no CSV column is set, assume the same name as timetrex column.
			if ( $map_cols['csv_column'] == '' ) {
				$map_cols['csv_column'] = $map_cols['timetrex_column'];
			}

			if ( ( isset( $map_cols['csv_column'] ) AND isset($map_cols['default_value'])  )
					AND ( $map_cols['csv_column'] != '' OR $map_cols['default_value'] != '' ) ) {
				echo "  TimeTrex Column: ". $map_cols['timetrex_column'] ." => ". $map_cols['csv_column'] ." Default: ". $map_cols['default_value'] ."\n";


				$filtered_import_map[$map_cols['timetrex_column']] = array(
												'timetrex_column' => $map_cols['timetrex_column'],
												'csv_column' => $map_cols['csv_column'],
												'default_value' => $map_cols['default_value'],
												'parse_hint' => $map_cols['parse_hint'],
												);
			} else {
				echo "  TimeTrex Column: ". $map_cols['timetrex_column'] ." => Skipping...\n";
			}
		}
		unset($import_map_arr, $map_cols);
		//var_dump($filtered_import_map);
	}


	if ( $dry_run == TRUE ) {
		//Import first two lines of CSV file to display for testing.
		$import_arr = Misc::parseCSV( $import_csv_file, TRUE, FALSE, ",", 9216, 2 );

		if ( !is_array( $import_arr ) ) {
			echo "Parsing CSV file failed!\n";
		} else {
			echo "Sample Punches...\n";

			$i=1;
			foreach( $import_arr as $tmp_import_arr ) {
				$mapped_row = Misc::importApplyColumnMap( $filtered_import_map, $tmp_import_arr );

				echo "  Sample Punch: $i\n";

				foreach( $mapped_row as $column => $value ) {
					echo "    $column: $value\n";
				}
				$i++;
			}
		}

		unset($import_arr, $mapped_row, $column, $value, $tmp_import_arr, $i);
	}


	//Import all data
	$import_arr = Misc::parseCSV( $import_csv_file, TRUE, FALSE, ",", 9216, 0 );

	if ( !is_array( $import_arr ) ) {
		echo "Parsing CSV file failed!\n";
	} else {
		echo "Importing Punches...\n";

		$uf = new UserFactory();
		$uf->StartTransaction();

		$commit_trans = TRUE;

		$i=1;
		$e=0;
		foreach( $import_arr as $tmp_import_arr ) {
			$mapped_row = Misc::importApplyColumnMap( $filtered_import_map, $tmp_import_arr );
			if ( isset($mapped_row['user_name']) ) {
				$user_identifer = $mapped_row['user_name'];
			} elseif ( $mapped_row['employee_number'] ) {
				$user_identifer = $mapped_row['employee_number'];
			} else {
				$user_identifer = $mapped_row['user_id'];
			}
			echo "  Importing Punch: $i. ". str_pad( $user_identifer , 30, '.', STR_PAD_RIGHT)."... ";

			$ulf = new UserListFactory();

			if ( isset($mapped_row['user_id']) AND $mapped_row['user_id'] != '' ) {
				$ulf->getById( $mapped_row['user_id'] );
			} elseif ( isset($mapped_row['employee_number']) AND $mapped_row['employee_number'] != ''
						AND isset($mapped_row['company_id']) AND $mapped_row['company_id'] != '' ) {
				$ulf->getByCompanyIDAndEmployeeNumber( $mapped_row['company_id'], $mapped_row['employee_number']);
			} elseif ( isset($mapped_row['user_name']) AND $mapped_row['user_name'] != '' ) {
				$ulf->getByUserName( $mapped_row['user_name'] );
			}

			if ( $ulf->getRecordCount() == 1 ) {
				$u_obj = $ulf->getCurrent();

				//Set user timezone before parsing.
				$u_obj->getUserPreferenceObject()->setDateTimePreferences();

				if ( !isset($branch_options) ) {
					//Get all branches
					$blf = new BranchListFactory();
					$blf->getByCompanyId( $u_obj->getCompany() );
					$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );
					unset($blf);
				}

				if ( !isset($department_options) ) {
					//Get departments
					$dlf = new DepartmentListFactory();
					$dlf->getByCompanyId( $u_obj->getCompany() );
					$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );
					unset($dlf);
				}

				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND !isset($job_options) ) {
					//Get jobs
					$jlf = new JobListFactory();
					$jlf->getByCompanyId( $u_obj->getCompany() );
					$job_options = $jlf->getArrayByListFactory( $jlf, FALSE, TRUE );
					unset($jlf);
				}

				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND !isset($job_item_options) ) {
					//Get tasks
					$jilf = new JobItemListFactory();
					$jilf->getByCompanyId( $u_obj->getCompany() );
					$job_item_options = $jilf->getArrayByListFactory( $jilf, FALSE, TRUE );
					unset($jilf);
				}

				if ( isset($mapped_row['time_stamp']) AND $mapped_row['time_stamp'] != '' ) {
					$time_stamp_epoch = Misc::importCallInputParseFunction( 'time_stamp', $mapped_row['time_stamp'], $filtered_import_map['time_stamp']['default_value'], $filtered_import_map['time_stamp']['parse_hint'] );
				} else {
					if ( isset($mapped_row['start_time_stamp']) AND $mapped_row['start_time_stamp'] != '' ) {
						$time_stamp_epoch = Misc::importCallInputParseFunction( 'time_stamp', $mapped_row['start_time_stamp'], $filtered_import_map['start_time_stamp']['default_value'], $filtered_import_map['start_time_stamp']['parse_hint'] );
					} else {
						$time_stamp_epoch = NULL;
					}
				}
				echo "  Time Stamp: $i. ". TTDate::getDate('DATE+TIME', $time_stamp_epoch) ." (".$time_stamp_epoch.")\n";

				$status_id = Misc::importCallInputParseFunction( 'status_id', $mapped_row['status_id'], $filtered_import_map['status_id']['default_value'], $filtered_import_map['status_id']['parse_hint'] );

				if ( !isset($mapped_row['time_stamp']) OR $mapped_row['time_stamp'] == '' ) {
					//Two punches per row
					if ( isset($mapped_row['start_time_stamp']) AND $mapped_row['start_time_stamp'] != '' ) {
						//
						// Start Punch
						//
						$pf = new PunchFactory();
						$pf->setUser( $u_obj->getId() );

						if ( isset($mapped_row['start_type_id']) AND $mapped_row['start_type_id'] != '' ) {
							$pf->setType( Misc::importCallInputParseFunction( 'start_type_id', $mapped_row['start_type_id'], $filtered_import_map['start_type_id']['default_value'], $filtered_import_map['start_type_id']['parse_hint'] ) );
						} else {
							$pf->setType(10); //Normal
						}

						$pf->setStatus(10); //In

						if ( isset($mapped_row['disable_rounding']) AND $mapped_row['disable_rounding'] != '' ) {
							$disable_rounding = (bool)$mapped_row['disable_rounding'];
						}
						//Reverse boolean.
						if ( isset($disable_rounding) AND $disable_rounding == TRUE ) {
							$enable_rounding = FALSE;
						} else {
							$enable_rounding = TRUE;
						}
						//echo "First Punch: ". TTDate::getDate('DATE+TIME', $time_stamp_epoch) ."\n";
						$pf->setTimeStamp( $time_stamp_epoch, $enable_rounding );

						$pf->setPunchControlID( $pf->findPunchControlID() );

						$pf->setActualTimeStamp( $time_stamp_epoch );
						$pf->setOriginalTimeStamp( $time_stamp_epoch );

						if ( $pf->isValid() ) {
							$pf->setEnableCalcTotalTime( TRUE );
							$pf->setEnableCalcSystemTotalTime( TRUE );
							$pf->setEnableCalcUserDateTotal( TRUE );
							$pf->setEnableCalcException( TRUE );

							if ( $pf->Save( FALSE ) == TRUE ) {
								$punch_control_id = $pf->getPunchControlID();
								echo " \t\t\t1. Success!";
							} else {
								echo " \t\t\t1. Failed!";
								$commit_trans = FALSE;
								$e++;

								$errors = $pf->Validator->getErrorsArray();
								if ( is_array($errors) ) {
									foreach( $errors as $error_arr ) {
										echo "      ERROR: ". $error_arr[0] ."\n";
									}
								}
							}
						} else {
							echo " \t\t\t1. Failed!\n";
							$commit_trans = FALSE;
							$e++;


							$errors = $pf->Validator->getErrorsArray();
							if ( is_array($errors) ) {
								foreach( $errors as $error_arr ) {
									echo "      ERROR: ". $error_arr[0] ."\n";
								}
							}
						}
					} else {
						echo " \t\t\t1. Failed!\n";
						//$commit_trans = FALSE;
						$e++;

						echo "      ERROR: No punch In timestamp...\n";
					}

					if ( isset($mapped_row['end_time_stamp']) AND $mapped_row['end_time_stamp'] != '' AND isset($pf) AND is_object($pf) ) {
						//
						// End Punch
						//
						$pf_b = new PunchFactory();
						$pf_b->setUser( $u_obj->getId() );

						if ( isset($mapped_row['end_type_id']) AND $mapped_row['end_type_id'] != '' ) {
							$pf_b->setType( Misc::importCallInputParseFunction( 'end_type_id', $mapped_row['end_type_id'], $filtered_import_map['end_type_id']['default_value'], $filtered_import_map['end_type_id']['parse_hint'] ) );
						} else {
							$pf_b->setType(10); //Normal
						}

						$pf_b->setStatus(20); //Out

						if ( isset($mapped_row['disable_rounding']) AND $mapped_row['disable_rounding'] != '' ) {
							$disable_rounding = (bool)$mapped_row['disable_rounding'];
						}
						//Reverse boolean.
						if ( isset($disable_rounding) AND $disable_rounding == TRUE ) {
							$enable_rounding = FALSE;
						} else {
							$enable_rounding = TRUE;
						}

						$end_time_stamp_epoch = Misc::importCallInputParseFunction( 'time_stamp', $mapped_row['end_time_stamp'], $filtered_import_map['end_time_stamp']['default_value'], $filtered_import_map['end_time_stamp']['parse_hint'] );

						//echo "Second Punch: ". TTDate::getDate('DATE+TIME', $end_time_stamp_epoch) ."\n";
						$pf_b->setTimeStamp( $end_time_stamp_epoch, $enable_rounding );

						$pf_b->setPunchControlID( $pf->findPunchControlID() );

						$pf_b->setActualTimeStamp( $end_time_stamp_epoch );
						$pf_b->setOriginalTimeStamp( $end_time_stamp_epoch );

						if ( $pf_b->isValid() ) {
							if ( $pf_b->Save() == TRUE ) {
								echo " 2. Success!\n";
							} else {
								echo " 2. Failed!\n";
								$commit_trans = FALSE;
								$e++;

								$errors = $pf_b->Validator->getErrorsArray();
								if ( is_array($errors) ) {
									foreach( $errors as $error_arr ) {
										echo "      ERROR: ". $error_arr[0] ."\n";
									}
								}
							}
						} else {
							echo " 2. Failed!\n";
							$commit_trans = FALSE;
							$e++;

							$errors = $pf_b->Validator->getErrorsArray();
							if ( is_array($errors) ) {
								foreach( $errors as $error_arr ) {
									echo "      ERROR: ". $error_arr[0] ."\n";
								}
							}
						}
					} else {
						echo " 2. Failed!\n";
						//$commit_trans = FALSE;
						$e++;

						echo "      ERROR: No punch Out timestamp...\n";
					}
				} else {
					//Single punch per row
					$pf = new PunchFactory();
					$pf->setUser( $u_obj->getId() );

					if ( isset($mapped_row['type_id']) AND $mapped_row['type_id'] != '' ) {
						$pf->setType( Misc::importCallInputParseFunction( 'type_id', $mapped_row['type_id'], $filtered_import_map['type_id']['default_value'], $filtered_import_map['type_id']['parse_hint'] ) );
					}

					if ( isset($mapped_row['status_id']) AND $mapped_row['status_id'] != '' ) {
						$pf->setStatus( $status_id );
					}

					if ( isset($mapped_row['disable_rounding']) AND $mapped_row['disable_rounding'] != '' ) {
						$disable_rounding = (bool)$mapped_row['disable_rounding'];
					}
					//Reverse boolean.
					if ( isset($disable_rounding) AND $disable_rounding == TRUE ) {
						$enable_rounding = FALSE;
					} else {
						$enable_rounding = TRUE;
					}

					$pf->setTimeStamp( $time_stamp_epoch, $enable_rounding );

					$pf->setPunchControlID( $pf->findPunchControlID() );

					$pf->setActualTimeStamp( $time_stamp_epoch );
					$pf->setOriginalTimeStamp( $time_stamp_epoch );

					if ( $pf->isValid() ) {
						if ( $pf->Save( FALSE ) == TRUE ) {
							echo " \t\t\tSuccess!\n";
						} else {
							echo " \t\t\tFailed!\n";
							$commit_trans = FALSE;
							$e++;

							$errors = $pf->Validator->getErrorsArray();
							if ( is_array($errors) ) {
								foreach( $errors as $error_arr ) {
									echo "      ERROR: ". $error_arr[0] ."\n";
								}
							}
						}
					} else {
						echo " \t\t\tFailed!\n";
						$commit_trans = FALSE;
						$e++;

						$errors = $pf->Validator->getErrorsArray();
						if ( is_array($errors) ) {
							foreach( $errors as $error_arr ) {
								echo "      ERROR: ". $error_arr[0] ."\n";
							}
						}
					}
				}

				$pcf = new PunchControlFactory();
				$pcf->setId( $pf->getPunchControlID() );
				$pcf->setPunchObject( $pf );

				if ( isset($mapped_row['branch_id']) AND $mapped_row['branch_id'] != '' ) {
					$pcf->setBranch( Misc::importCallInputParseFunction( 'branch_id', $mapped_row['branch_id'], $filtered_import_map['branch_id']['default_value'], $filtered_import_map['branch_id']['parse_hint'] ) );
				}

				if ( isset($mapped_row['department_id']) AND $mapped_row['department_id'] != '' ) {
					$pcf->setDepartment( Misc::importCallInputParseFunction( 'department_id', $mapped_row['department_id'], $filtered_import_map['department_id']['default_value'], $filtered_import_map['department_id']['parse_hint'] ) );
				}

				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					if ( isset($mapped_row['job_id']) AND $mapped_row['job_id'] != '' ) {
						$pcf->setJob( Misc::importCallInputParseFunction( 'job_id', $mapped_row['job_id'], $filtered_import_map['job_id']['default_value'], $filtered_import_map['job_id']['parse_hint'] ) );
					}

					if ( isset($mapped_row['task_id']) AND $mapped_row['task_id'] != '' ) {
						$pcf->setJobItem( Misc::importCallInputParseFunction( 'task_id', $mapped_row['task_id'], $filtered_import_map['task_id']['default_value'], $filtered_import_map['task_id']['parse_hint'] ) );
					}

					if ( isset($mapped_row['quantity']) AND is_numeric($mapped_row['quantity']) ) {
						$pcf->setQuantity( $mapped_row['quantity'] );
					}
					if ( isset($mapped_row['bad_quantity']) AND is_numeric($mapped_row['bad_quantity']) ) {
						$pcf->setBadQuantity( $mapped_row['bad_quantity'] );
					}
				}

				$pcf->setEnableStrictJobValidation( TRUE );
				$pcf->setEnableCalcUserDateID( TRUE );
				$pcf->setEnableCalcTotalTime( TRUE );
				$pcf->setEnableCalcSystemTotalTime( TRUE );
				$pcf->setEnableCalcWeeklySystemTotalTime( TRUE );
				$pcf->setEnableCalcUserDateTotal( TRUE );
				$pcf->setEnableCalcException( TRUE );

				if ( $pcf->isValid() ) {
					$punch_control_id = $pcf->Save( TRUE, TRUE );
				} else {
					echo " \t\t\tFailed!\n";
					$commit_trans = FALSE;
					$e++;

					$errors = $pcf->Validator->getErrorsArray();
					if ( is_array($errors) ) {
						foreach( $errors as $error_arr ) {
							echo "      ERROR: ". $error_arr[0] ."\n";
						}
					}
				}

			} else {
				echo " \t\t\tFailed!\n";
				$commit_trans = FALSE;
				$e++;

				echo "    ERROR: User not found!\n";
			}

			unset($tmp_punch_control_id, $status_id);
			ob_flush();
			flush();
			$i++;
		}

		if ( $e > 0 ) {
			echo "Total Errors: ". $e ."\n";
		}

		if ( $dry_run == TRUE OR $commit_trans !== TRUE ) {
			echo "Rolling back transaction!\n";
			$uf->FailTransaction();
		}
		//$uf->FailTransaction();
		$uf->CommitTransaction();
	}

}

echo "WARNING: Clear TimeTrex cache after running this.\n";
Debug::Display();
?>