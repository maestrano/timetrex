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
 * $Revision: 3387 $
 * $Id: ImportBranch.class.php 3387 2010-03-04 17:42:17Z ipso $
 * $Date: 2010-03-04 09:42:17 -0800 (Thu, 04 Mar 2010) $
 */


/**
 * @package Modules\Import
 */
class ImportPunch extends Import {

	public $class_name = 'APIPunch';

	public $branch_options = FALSE;
	public $branch_manual_id_options = FALSE;
	public $department_options = FALSE;
	public $department_manual_id_options = FALSE;

	public $job_options = FALSE;
	public $job_manual_id_options = FALSE;
	public $job_item_options = FALSE;
	public $job_item_manual_id_options = FALSE;

	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
								//'-1010-user_name' => TTi18n::gettext('User Name'),
								//'-1020-employee_number' => TTi18n::gettext('Employee #'),
								'-1100-type' => TTi18n::gettext('Type'),
								'-1110-status' => TTi18n::gettext('Status'),

								'-1210-time_stamp' => TTi18n::gettext('Date/Time'),
								'-1220-date' => TTi18n::gettext('Date'),
								'-1230-time' => TTi18n::gettext('Time'),

								'-1239-in_type' => TTi18n::gettext('In Type'),
								'-1240-in_time_stamp' => TTi18n::gettext('In Date/Time'),
								'-1250-in_punch_date' => TTi18n::gettext('In Date'),
								'-1260-in_punch_time' => TTi18n::gettext('In Time'),
								'-1269-out_type' => TTi18n::gettext('Out Type'),
								'-1270-out_time_stamp' => TTi18n::gettext('Out Date/Time'),
								'-1280-out_punch_date' => TTi18n::gettext('Out Date'),
								'-1290-out_punch_time' => TTi18n::gettext('Out Time'),

								'-1310-branch' => TTi18n::gettext('Branch'),
								'-1320-department' => TTi18n::gettext('Department'),
								'-1410-station_id' => TTi18n::gettext('Station ID'),

								'-1420-longitude' => TTi18n::gettext('Longitude'),
								'-1430-latitude' => TTi18n::gettext('Latitude'),

								'-1500-note' => TTi18n::gettext('Note'),
								);

				//Since getOptions() can be called without first setting a company, we don't always know the product edition for the currently
				//logged in employee.
				if ( ( is_object($this->getCompanyObject()) AND $this->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE )
						OR ( !is_object($this->getCompanyObject()) AND getTTProductEdition() >= TT_PRODUCT_CORPORATE ) ) {
					$retval += array(
									'-1330-job' => TTi18n::gettext('Job'),
									'-1340-job_item' => TTi18n::gettext('Task'),
									'-1350-quantity' => TTi18n::gettext('Quantity'),
									'-1360-bad_quantity' => TTi18n::gettext('Bad Quantity'),
								);
				}

				$retval = Misc::addSortPrefix( Misc::prependArray( $this->getUserIdentificationColumns(), Misc::trimSortPrefix($retval) ) );
				ksort($retval);

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = array(
								'type' => 'type_id',
								'status' => 'status_id',
								'branch' => 'branch_id',
								'department' => 'department_id',
								'job' => 'job_id',
								'job_item' => 'job_item_id',
								);
				break;
			case 'import_options':
				$retval = array(
								'-1010-fuzzy_match' => TTi18n::getText('Enable smart matching.'),
								//'-1015-update' => TTi18n::getText('Delete existing punches that may conflict with imported punches.'), //Need an array to pick the unique column to use as the identifier, or we can just detect this on our own?
								//Allow these to be imported separately instead.
								'-1020-disable_rounding' => TTi18n::getText('Disable rounding.'),
								);
				break;
			case 'parse_hint':
				$upf = TTnew('UserPreferenceFactory');

				$retval = array(
								'branch' => array(
												    '-1010-name' => TTi18n::gettext('Name'),
													'-1010-manual_id' => TTi18n::gettext('Code'),
												  ),
								'department' => array(
												    '-1010-name' => TTi18n::gettext('Name'),
													'-1010-manual_id' => TTi18n::gettext('Code'),
												  ),
								'job' => array(
												    '-1010-name' => TTi18n::gettext('Name'),
													'-1010-manual_id' => TTi18n::gettext('Code'),
												  ),
								'job_item' => array(
												    '-1010-name' => TTi18n::gettext('Name'),
													'-1010-manual_id' => TTi18n::gettext('Code'),
												  ),
								//Not sure how we can define the format to parse the date/time in one field.
								'time_stamp' => $upf->getOptions('date_time_format'),
								'in_time_stamp' => $upf->getOptions('date_time_format'),
								'out_time_stamp' => $upf->getOptions('date_time_format'),

								'date' => $upf->getOptions('date_format'),
								'in_punch_date' => $upf->getOptions('date_format'),
								'out_punch_date' => $upf->getOptions('date_format'),

								'time' => $upf->getOptions('time_format'),
								'in_punch_time' => $upf->getOptions('time_format'),
								'out_punch_time' => $upf->getOptions('time_format'),

								);
				break;
		}

		return $retval;
	}

	function _preParseRow( $row_number, $raw_row ) {
		//Debug::Arr($raw_row, 'preParse Row: ', __FILE__, __LINE__, __METHOD__,10);
		return $raw_row;
	}

	function _postParseRow( $row_number, $raw_row ) {
		$raw_row['user_id'] = $this->getUserIdByRowData( $raw_row );
		if ( $raw_row['user_id'] == FALSE ) {
			unset($raw_row['user_id']);
		}

		//Combine date/time columns together and convert all time_stamp columns into epochs.
		$column_map = $this->getColumnMap(); //Include columns that should always be there.

		//Handle one punch per row.
		if ( isset($column_map['time_stamp']) ) {
			Debug::Text('Parsing time_stamp column...', __FILE__, __LINE__, __METHOD__,10);
			$date_time_format = $column_map['time_stamp']['parse_hint'].'_'.$column_map['time_stamp']['parse_hint'];
		} elseif ( !isset($column_map['time_stamp']) AND isset($column_map['date']) AND isset($column_map['time']) ) {
			Debug::Text('Parsing date/time column...', __FILE__, __LINE__, __METHOD__,10);
			//$raw_row['time_stamp'] = $raw_row[$column_map['date']['map_column_name']].' '. $raw_row[$column_map['time']['map_column_name']];
			$raw_row['time_stamp'] = $raw_row['date'].' '. $raw_row['time'];
			$date_time_format = $column_map['date']['parse_hint'].'_'.$column_map['time']['parse_hint'];
			unset($raw_row['date'],$raw_row['time']);
		} else {
			$date_time_format = 'd-M-y_g:i A T';
		}
		if ( isset($raw_row['time_stamp']) ) {
			$split_date_time_format = explode('_', $date_time_format );
			//Debug::Arr($split_date_time_format, 'Date/Time Format: '. $date_time_format, __FILE__, __LINE__, __METHOD__,10);
			TTDate::setDateFormat( $split_date_time_format[0] );
			TTDate::setTimeFormat( $split_date_time_format[1] );
			$raw_row['time_stamp'] = TTDate::parseDateTime( $raw_row['time_stamp'] );
		}

		//Debug::Arr($column_map, 'Column Map', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($raw_row, 'Raw Row', __FILE__, __LINE__, __METHOD__,10);

		//Handle two punches per row.
		if ( isset($column_map['in_time_stamp']) AND isset($column_map['out_time_stamp']) ) {
			Debug::Text('Parsing Two punches per row...', __FILE__, __LINE__, __METHOD__,10);
			$in_date_time_format = $column_map['in_time_stamp']['parse_hint'].'_'.$column_map['in_time_stamp']['parse_hint'];
			$out_date_time_format = $column_map['out_time_stamp']['parse_hint'].'_'.$column_map['out_time_stamp']['parse_hint'];
			unset($raw_row['status'],$raw_row['type']);
		} elseif ( !isset($column_map['in_time_stamp']) AND !isset($column_map['out_time_stamp'])
					AND isset($column_map['in_punch_date']) AND isset($column_map['in_punch_time'])
					AND isset($column_map['out_punch_date']) AND isset($column_map['out_punch_time']) ) {
			Debug::Text('Parsing Two punches per row with separte date/time columns...', __FILE__, __LINE__, __METHOD__,10);
			$raw_row['in_time_stamp'] = $raw_row['in_punch_date'].' '. $raw_row['in_punch_time'];
			$in_date_time_format = $column_map['in_punch_date']['parse_hint'].'_'.$column_map['in_punch_time']['parse_hint'];
			unset($raw_row['in_punch_date'],$raw_row['in_punch_time']);

			$raw_row['out_time_stamp'] = $raw_row['out_punch_date'].' '. $raw_row['out_punch_time'];
			$out_date_time_format = $column_map['out_punch_date']['parse_hint'].'_'.$column_map['out_punch_time']['parse_hint'];
			unset($raw_row['out_punch_date'],$raw_row['out_punch_time']);
			unset($raw_row['status'],$raw_row['type']);
		} else {
			$in_date_time_format = $out_date_time_format = $date_time_format = 'd-M-y_g:i A T';
		}
		if ( isset($raw_row['in_time_stamp']) AND isset($raw_row['out_time_stamp']) ) {
			Debug::Text('bParsing Two punches per row...', __FILE__, __LINE__, __METHOD__,10);
			$split_in_date_time_format = explode('_', $in_date_time_format );
			TTDate::setDateFormat( $split_in_date_time_format[0] );
			TTDate::setTimeFormat( $split_in_date_time_format[1] );
			$raw_row['in_time_stamp'] = TTDate::parseDateTime( $raw_row['in_time_stamp'] );

			$split_out_date_time_format = explode('_', $out_date_time_format );
			TTDate::setDateFormat( $split_out_date_time_format[0] );
			TTDate::setTimeFormat( $split_out_date_time_format[1] );
			$raw_row['out_time_stamp'] = TTDate::parseDateTime( $raw_row['out_time_stamp'] );
		}

		if ( !isset($raw_row['in_type']) AND !isset($raw_row['out_type']) AND !isset($raw_row['type']) AND ( !isset($raw_row['type_id']) OR (isset($raw_row['type_id']) AND $raw_row['type_id'] == '') ) ) {
			Debug::Text('Defaulting to normal punch type...', __FILE__, __LINE__, __METHOD__,10);
			$raw_row['type_id'] = 10; //Normal
		}

		if ( !isset($raw_row['in_status']) AND !isset($raw_row['out_status']) AND !isset($raw_row['status']) AND ( !isset($raw_row['status_id']) OR (isset($raw_row['status_id']) AND $raw_row['status_id'] == '') ) ) {
			Debug::Text('Defaulting to IN punch status...', __FILE__, __LINE__, __METHOD__,10);
			$raw_row['status_id'] = 10; //IN
		}

		if ( $this->getImportOptions('disable_rounding') == TRUE ) {
			$raw_row['disable_rounding'] = TRUE;
		}
		unset($raw_row['date'], $raw_row['time'] );

		Debug::Arr($raw_row, 'postParse Row: ', __FILE__, __LINE__, __METHOD__,10);
		return $raw_row;
	}

	function _preProcess() {
		//If two timestamps are defined on a single row, split them into individual punches.
		$parsed_data = $this->getParsedData();

		if ( isset($parsed_data[0]['in_time_stamp']) AND isset($parsed_data[0]['out_time_stamp']) ) {
			Debug::Arr($parsed_data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);
			if ( is_array( $parsed_data ) ) {
				foreach( $parsed_data as $key => $data ) {
					foreach( $data as $column_key => $column_value ) {
						if ( $column_key == 'in_time_stamp' OR $column_key == 'out_time_stamp' ) {
							if ( $column_key == 'in_time_stamp' ) {
								$in_punch['time_stamp'] = $column_value;
							}
							if ( $column_key == 'out_time_stamp' ) {
								$out_punch['time_stamp'] = $column_value;
							}
						} elseif ( $column_key == 'in_type' OR $column_key == 'out_type' ) {
							if ( $column_key == 'in_type' ) {
								$in_punch['type_id'] = $column_value;
							}
							if ( $column_key == 'out_type' ) {
								$out_punch['type_id'] = $column_value;
							}
						} elseif ( $column_key == 'status_id' ) {
							$in_punch['status_id'] = 10;
							$out_punch['status_id'] = 20;
						} else {
							//Copy all other columns to each punch.
							$in_punch[$column_key] = $column_value;
							$out_punch[$column_key] = $column_value;
						}
					}

					$retarr[] = $in_punch;
					$retarr[] = $out_punch;
				}
			}

			Debug::Arr($retarr, 'preProcess Data Results ', __FILE__, __LINE__, __METHOD__,10);
			$this->setParsedData( $retarr );
		} else {
			Debug::Text('Not two punches per row... Skipping preProcess step...', __FILE__, __LINE__, __METHOD__,10);
		}

		return TRUE;
	}
	function _import( $validate_only ) {
		//Set timezone for employee
		return $this->getObject()->setPunch( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//
	function parse_status( $input, $default_value = NULL, $parse_hint = NULL ) {

		if ( strtolower( $input ) == 'i'
				OR strtolower( $input ) == 'in' ) {
			$retval = 10;
		} elseif ( strtolower( $input ) == 'o'
				OR strtolower( $input ) == 'out' ) {
			$retval = 20;
		} else {
			$retval = (int)$input;
		}

		return $retval;
	}

	function parse_in_type( $input, $default_value = NULL, $parse_hint = NULL ) {
		return $this->parse_type( $input, $default_value = NULL, $parse_hint = NULL );
	}
	function parse_out_type( $input, $default_value = NULL, $parse_hint = NULL ) {
		return $this->parse_type( $input, $default_value = NULL, $parse_hint = NULL );
	}

	function parse_type( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( strtolower( $input ) == 'n'
				OR strtolower( $input ) == 'normal' ) {
			$retval = 10;
		} elseif ( strtolower( $input ) == 'l'
				OR strtolower( $input ) == 'lunch' ) {
			$retval = 20;
		} elseif ( strtolower( $input ) == 'b'
				OR strtolower( $input ) == 'break' ) {
			$retval = 30;
		} else {
			$retval = (int)$input;
		}

		return $retval;
	}

	function getBranchOptions() {
		$this->branch_options = $this->branch_manual_id_options = array();
		$blf = TTNew('BranchListFactory');
		$blf->getByCompanyId( $this->company_id );
		if ( $blf->getRecordCount() > 0 ) {
			foreach( $blf as $b_obj ) {
				$this->branch_options[$b_obj->getId()] = $b_obj->getName();
				$this->branch_manual_id_options[$b_obj->getId()] = $b_obj->getManualId();
			}
		}
		unset($blf, $b_obj);

		return TRUE;
	}

	function parse_branch( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0; //No branch
		}

		if ( !is_array( $this->branch_options ) ) {
			$this->getBranchOptions();
		}

		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->branch_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->branch_options );
		}
		
		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	function getDepartmentOptions() {
		//Get departments
		$this->department_options = $this->department_manual_id_options = array();
		$dlf = TTNew('DepartmentListFactory');
		$dlf->getByCompanyId( $this->company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$this->department_options[$d_obj->getId()] = $d_obj->getName();
				$this->department_manual_id_options[$d_obj->getId()] = $d_obj->getManualId();
			}
		}
		unset($dlf, $d_obj);

		return TRUE;
	}
	function parse_department( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0; //No department
		}

		if ( !is_array( $this->department_options ) ) {
			$this->getDepartmentOptions();
		}

		//Always fall back to searching by name unless we know for sure its by manual_id
		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->department_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->department_options );
		}

		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	function getJobOptions() {
		//Get jobs
		$this->job_options = $this->job_manual_id_options = array();
		$dlf = TTNew('JobListFactory');
		$dlf->getByCompanyId( $this->company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$this->job_options[$d_obj->getId()] = $d_obj->getName();
				$this->job_manual_id_options[$d_obj->getId()] = $d_obj->getManualId();
			}
		}
		unset($dlf, $d_obj);

		return TRUE;
	}
	function parse_job( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0; //No job
		}

		if ( !is_array( $this->job_options ) ) {
			$this->getJobOptions();
		}

		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->job_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->job_options );
		}

		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}


	function getJobItemOptions() {
		//Get job_items
		$this->job_item_options = $this->job_item_manual_id_options = array();
		$dlf = TTNew('JobItemListFactory');
		$dlf->getByCompanyId( $this->company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$this->job_item_options[$d_obj->getId()] = $d_obj->getName();
				$this->job_item_manual_id_options[$d_obj->getId()] = $d_obj->getManualId();
			}
		}
		unset($dlf, $d_obj);

		return TRUE;
	}
	function parse_job_item( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0; //No job_item
		}

		if ( !is_array( $this->job_item_options ) ) {
			$this->getJobItemOptions();
		}

		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->job_item_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->job_item_options );
		}

		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	//Disable the regular parse_date as we need to handle it separately for punches.
	function parse_date( $input, $default_value = NULL, $parse_hint = NULL ) {
		return $input;
	}
}
?>
