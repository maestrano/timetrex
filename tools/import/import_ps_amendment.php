<?php

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');
//
//
// Custom functions to parse each individual column
//
//
function parse_pay_stub_account( $input, $default_value = NULL, $parse_hint = NULL )
{
	return $input;
}
function parse_user_name( $input, $default_value = NULL, $parse_hint = NULL ) {
	if ( strpos($input, ',') === FALSE ) {
		return $input;
	}

	$split_user_name = explode(',', $input);
	$first_user = $split_user_name[1];
	if ( strpos( $first_user, ' ') !== FALSE ) {
		$user_name = substr( $user_name, 0, strlen( $user_name )-2);
	}
	return $user_name;
}

function parse_units( $input, $default_value = NULL, $parse_hint = NULL ) {
	$val = new Validator();
	$retval_units = $val->stripNonFloat($input);
	return $retval_units;
}

function parse_rate( $input, $default_value = NULL, $parse_hint = NULL ) {
	$val = new Validator();
	$retval_rate = $val->stripNonFloat($input);
	return $retval_rate;
}

function parse_amount( $input, $default_value = NULL, $parse_hint = NULL ) {
	$val = new Validator();
	$retval_amount = $val->stripNonFloat($input);
	return $retval_amount;
}

function parse_effective_date( $input, $default_value = NULL, $parse_hint = NULL ) {
	if ( isset($parse_hint) AND $parse_hint != '' ) {
		TTDate::setDateFormat( $parse_hint );
	}
	return TTDate::parseDateTime( $input );
}

//
//
// Main
//
//
if ( $argc < 3 OR in_array ($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: import_ps_amendment.php [OPTIONS] [Column MAP file] [CSV File]\n";
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
	if ( !is_array( $import_map_arr )) {
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
	}

	if ( $dry_run == TRUE ) {
		//Import first two lines of CSV file to display for testing.
		$import_arr = Misc::parseCSV( $import_csv_file, TRUE, FALSE, ",", 9216, 2 );

		if ( !is_array( $import_arr ) ) {
			echo "Parsing CSV file failed!\n";
		} else {
			echo "Sample Pay Stub...\n";

			$i=1;
			foreach( $import_arr as $tmp_import_arr ) {
				$mapped_row = Misc::importApplyColumnMap( $filtered_import_map, $tmp_import_arr );

				echo "  Sample Pay Stub: $i\n";

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
		echo "Importing Pay Stub...\n";

		$psaf = new PayStubAmendmentFactory();
		$psaf->StartTransaction();

		$commit_trans = TRUE;

		$i=1;
		$e=0;
		foreach( $import_arr as $tmp_import_arr ) {
			$mapped_row = Misc::importApplyColumnMap( $filtered_import_map, $tmp_import_arr );
			if ( isset($mapped_row['user_name']) ) {
				$user_identifer = $mapped_row['user_name'];
			} else {
				$user_identifer = $mapped_row['user_id'];
			}

			echo "  Importing Pay Stub for: ". str_pad( $user_identifer , 30, '.', STR_PAD_RIGHT)."...";
			$ulf = new UserListFactory();

			if ( isset($mapped_row['user_id']) AND $mapped_row['user_id'] != '' ) {
				$ulf->getById( $mapped_row['user_id'] );
			} elseif ( isset($mapped_row['user_name']) AND $mapped_row['user_name'] != '' ) {
				$ulf->getByUserName( $mapped_row['user_name'] );
			}

			if ( $ulf->getRecordCount() == 1 ) {
				$u_obj = $ulf->getCurrent();

				$psaf->setUser($u_obj->getId());

				if ( isset($mapped_row['status_id']) AND $mapped_row['status_id'] != '' ) {
					$psaf->setStatus( Misc::importCallInputParseFunction( 'status_id', $mapped_row['status_id'], $filtered_import_map['status_id']['default_value'], $filtered_import_map['status_id']['parse_hint'] ) );
				}

    			if ( isset($mapped_row['type_id']) AND $mapped_row['type_id'] != '' ) {
					$psaf->setType( Misc::importCallInputParseFunction( 'type_id', $mapped_row['type_id'], $filtered_import_map['type_id']['default_value'], $filtered_import_map['type_id']['parse_hint'] ) );
				}

    			if ( isset($mapped_row['pay_stub_account']) AND $mapped_row['pay_stub_account'] != '' ) {
      				$psealf = new PayStubEntryAccountListFactory();
      				$psealf->getByCompanyIdAndTypeAndFuzzyName($ulf->getCurrent()->getCompany(), array(10,20,30,50,60,65),$mapped_row['pay_stub_account']);

					if ( $psealf->getRecordCount() > 0 ) {
						$psaf->setPayStubEntryNameId( Misc::importCallInputParseFunction( 'pay_stub_account', $psealf->getCurrent()->getId(), $filtered_import_map['pay_stub_account']['default_value'], $filtered_import_map['pay_stub_account']['parse_hint'] ) );
					} else {
                    	$psaf->setPayStubEntryNameId( Misc::importCallInputParseFunction( 'pay_stub_account', "Invalid Pay Stub Account ", $filtered_import_map['pay_stub_account']['default_value'], $filtered_import_map['pay_stub_account']['parse_hint'] ) );
                    }
    			}

				if ( isset($mapped_row['units']) AND $mapped_row['units'] != '' ) {
					$psaf->setUnits( Misc::importCallInputParseFunction( 'units', $mapped_row['units'], $filtered_import_map['units']['default_value'], $filtered_import_map['units']['parse_hint'] ) );
				}

				if ( isset($mapped_row['rate']) AND $mapped_row['rate'] != '' ) {
					$psaf->setRate( Misc::importCallInputParseFunction( 'rate', $mapped_row['rate'], $filtered_import_map['rate']['default_value'], $filtered_import_map['rate']['parse_hint'] ) );
				}

				if ( isset($mapped_row['amount']) AND $mapped_row['amount'] != '' ) {
					$psaf->setAmount( Misc::importCallInputParseFunction( 'amount', $mapped_row['amount'], $filtered_import_map['amount']['default_value'], $filtered_import_map['amount']['parse_hint'] ) );
				}

				if ( isset($mapped_row['effective_date']) AND $mapped_row['effective_date'] != '' ) {
					$psaf->setEffectiveDate( Misc::importCallInputParseFunction( 'effective_date', $mapped_row['effective_date'], $filtered_import_map['effective_date']['default_value'], $filtered_import_map['effective_date']['parse_hint'] ) );
				}

				if ( $psaf->isValid() ) {
                 	echo " \t\t\tSuccess!\n";
					$pay_stub_id = $psaf->Save();
				} else {
					echo " \t\t\tFailed!\n";
					$commit_trans = FALSE;
					$e++;
					$errors = $psaf->Validator->getErrorsArray();

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
			unset( $status_id);
			ob_flush();
			flush();
			$i++;
		}
		if ( $e > 0 ) {
			echo "Total Errors: ". $e ."\n";
		}

		if ( $dry_run == TRUE OR $commit_trans !== TRUE ) {
			echo "Rolling back transaction!\n";
			$psaf->FailTransaction();
		}

		$psaf->CommitTransaction();
	}
}
echo "WARNING: Clear TimeTrex cache after running this.\n";
//Debug::Display();
?>
