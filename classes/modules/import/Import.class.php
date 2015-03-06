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
 * @package Modules\Import
 */
class Import {

	public $company_id = NULL;
	public $user_id = NULL;

	private $user_id_cache = NULL; //getUserIDByRowData cache.

	public $class_name = NULL;
	public $obj = NULL;
	public $data = array();

	protected $company_obj = NULL;
	protected $progress_bar_obj = NULL;
	protected $AMF_message_id = NULL;

	function getObject() {
		if ( !is_object($this->obj) ) {
			$this->obj = TTnew( $this->class_name );
			$this->obj->setAMFMessageID( $this->getAMFMessageID() ); //Need to transfer the same AMF message id so progress bars continue to work.
		}

		return $this->obj;
	}

	function getCompanyObject() {
		$cf = new CompanyFactory();
		return $cf->getGenericObject( 'CompanyListFactory', $this->company_id, 'company_obj' );
	}

	function getProgressBarObject() {
		if	( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}
	//Returns the AMF messageID for each individual call.
	function getAMFMessageID() {
		if ( $this->AMF_message_id != NULL ) {
			return $this->AMF_message_id;
		}
		return FALSE;
	}
	function setAMFMessageID( $id ) {
		if ( $id != '' ) {
			$this->AMF_message_id = $id;
			return TRUE;
		}

		return FALSE;
	}

	function getOptions($name, $parent = NULL) {
		if ( $parent == NULL OR $parent == '') {
			$retarr = $this->_getFactoryOptions( $name );
		} else {
			$retarr = $this->_getFactoryOptions( $name );
			if ( isset($retarr[$parent]) ) {
				$retarr = $retarr[$parent];
			}
		}

		if ( $name == 'columns' ) {
			//Remove columns that can never be imported.
			$retarr = Misc::trimSortPrefix( $retarr );
			unset($retarr['created_by'], $retarr['created_date'], $retarr['updated_by'], $retarr['updated_date'] );
			$retarr = Misc::addSortPrefix( $retarr );
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}
	protected function _getFactoryOptions( $name ) {
		return FALSE;
	}

	function getRawData( $limit = NULL ) {
		if ( isset($this->data['raw_data']) ) {
			Debug::Text('zRaw Data Size: '. count($this->data['raw_data']), __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($this->data['raw_data'], 'Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

			//FIXME: There appears to be a bug in Flex where if the file has a blank column header column, no data is parsed at all in the column map step of the wizard.
			if ( $limit > 0 ) {
				Debug::Text('azRaw Data Size: '. count($this->data['raw_data']), __FILE__, __LINE__, __METHOD__, 10);
				return array_slice( $this->data['raw_data'], 0, (int)$limit );
			} else {
				Debug::Text('bzRaw Data Size: '. count($this->data['raw_data']), __FILE__, __LINE__, __METHOD__, 10);
				return $this->data['raw_data'];
			}
		}

		return FALSE;
	}
	function setRawData($value) {
		if ( $value != '' ) {
			Debug::Text('Raw Data Size: '. count($value), __FILE__, __LINE__, __METHOD__, 10);
			$this->data['raw_data'] = $value;

			return TRUE;
		}

		return FALSE;
	}
	function getRawDataFromFile() {
		$file_name = $this->getStoragePath().$this->getLocalFileName();
		if ( file_exists( $file_name ) ) {
			Debug::Text('Loading data from file: '. $file_name .'...', __FILE__, __LINE__, __METHOD__, 10);
			return $this->setRawData( Misc::parseCSV( $file_name, TRUE, FALSE, ',', 9216, 0 ) );
		}

		Debug::Text('Loading data from file: '. $file_name .' Failed!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	function saveRawDataToFile( $data ) {
		Debug::Text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);
		$dir = $this->getStoragePath();
		Debug::Text('Storage Path: '. $dir, __FILE__, __LINE__, __METHOD__, 10);
		if ( isset($dir) ) {
			@mkdir($dir, 0700, TRUE);

			return file_put_contents( $dir.$this->getLocalFileName(), $data );
		}

		return FALSE;
	}
	function getRawDataColumns() {
		$raw_data = $this->getRawData();
		if ( is_array( $raw_data ) ) {
			foreach( $raw_data as $raw_data_row ) {
				foreach( $raw_data_row as $raw_data_column => $raw_data_column_data ) {
					$retarr[] = $raw_data_column;
				}
				break;
			}
			Debug::Arr($retarr, 'Raw Data Columns: ', __FILE__, __LINE__, __METHOD__, 10);

			return $retarr;
		}

		return FALSE;
	}

	function getParsedData() {
		if ( isset($this->data['parsed_data']) ) {
			return $this->data['parsed_data'];
		}
	}
	function setParsedData($value) {
		if ( $value != '' ) {
			$this->data['parsed_data'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//Generates a "best fit" column map array.
	function generateColumnMap() {
		$raw_data_columns = $this->getRawDataColumns();
		//Debug::Arr($raw_data_columns, 'Raw Data Columns:', __FILE__, __LINE__, __METHOD__, 10);

		$columns = Misc::trimSortPrefix( $this->getOptions('columns') );
		//Debug::Arr($columns, 'Object Columns:', __FILE__, __LINE__, __METHOD__, 10);

		//unset($columns['middle_name']); //This often conflicts with Last Name, so ignore mapping it by default. But then it won't work even for an exact match.

		if ( is_array( $raw_data_columns ) AND is_array($columns) ) {
			//Loop through all raw_data_columns finding best matches.
			$matched_columns = array();
			foreach( $raw_data_columns as $raw_data_key => $raw_data_column ) {
				$matched_column_key = Misc::findClosestMatch( $raw_data_column, $columns, 60 );
				if ( $matched_column_key !== FALSE AND isset($columns[$matched_column_key]) ) {
					Debug::Text('Close match for: '. $raw_data_column .' Match: '. $matched_column_key, __FILE__, __LINE__, __METHOD__, 10);
					$matched_columns[$raw_data_column] = $matched_column_key;
				} else {
					Debug::Text('No close match for: '. $raw_data_column, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
			unset($raw_data_column, $raw_data_key, $matched_column_key);
			$matched_columns = array_flip( $matched_columns );

			foreach( $columns as $column => $column_name ) {
				$retval[$column] = array(
										'import_column' => $column,
										'map_column_name' => ( isset($matched_columns[$column]) AND $matched_columns[$column] != '' ) ? $matched_columns[$column] : NULL,
										'default_value' => NULL,
										'parse_hint' => NULL,
										);
			}

			if ( isset($retval) ) {
				Debug::Arr($retval, 'Generate Column Map:', __FILE__, __LINE__, __METHOD__, 10);
				return $retval;
			}

		}

		return FALSE;
	}

	//Takes a saved column map and tries to merge it with existing column data from the file.
	//Needs to account for manually added columns that don't exist in the file already.
	//Needs to account for less/more columns added to the file itself.
	function mergeColumnMap( $saved_column_map ) {
		return $saved_column_map;
	}

	function getColumnMap() {
		if ( isset($this->data['column_map']) ) {
			return $this->data['column_map'];
		}
	}
	function setColumnMap($import_map_arr) {
		//
		// Array(
		//			$column_name => array( 'map_column_name' => 'user_name', 'default_value' => 'blah', 'parse_hint' => 'm/d/y' ),
		//			$column_name => array( 'map_column_name' => 'user_name', 'default_value' => 'blah', 'parse_hint' => 'm/d/y' ),
		//		)
		//
		// This must support columns that may not exist in the actual system, so they can be converted to ones that do.
		foreach( $import_map_arr as $import_column => $map_cols ) {
			if ( ( isset( $map_cols['map_column_name'] ) AND isset( $map_cols['default_value'] ) )
					AND ( $map_cols['map_column_name'] != '' OR $map_cols['default_value'] != '' ) ) {
				Debug::Text('Import Column: '. $import_column .' => '. $map_cols['map_column_name'] .' Default: '. $map_cols['default_value'], __FILE__, __LINE__, __METHOD__, 10);

				$filtered_import_map[$import_column] = array(
												'import_column' => $import_column,
												'map_column_name' => $map_cols['map_column_name'],
												'default_value' => $map_cols['default_value'],
												'parse_hint' => $map_cols['parse_hint'],
												);

			} else {
				Debug::Text('Import Column: '. $import_column .' Skipping...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		if ( isset($filtered_import_map) ) {
			//Debug::Arr($filtered_import_map, 'Filtered Import Map:', __FILE__, __LINE__, __METHOD__, 10);
			$this->data['column_map'] = $filtered_import_map;
			return TRUE;
		}

		return FALSE;
	}

	function getImportOptions( $key = NULL ) {
		if ( isset($this->data['import_options']) ) {
			if ( $key == '' ) {
				return $this->data['import_options'];
			} else {
				if ( isset($this->data['import_options'][$key]) ) {
					Debug::Text('Found specific import options key: '. $key .' returning: '. $this->data['import_options'][$key], __FILE__, __LINE__, __METHOD__, 10);
					return $this->data['import_options'][$key];
				} else {
					return NULL;
				}
			}
		}

		return FALSE;
	}
	function setImportOptions( $value ) {
		if ( is_array($value) ) {
			$this->data['import_options'] = Misc::trimSortPrefix( $value );
			Debug::Arr($this->data['import_options'], 'Import Options: ', __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		return FALSE;
	}

	function callInputParseFunction( $function_name, $map_data, $raw_row = NULL ) {
		$full_function_name = 'parse_'.$function_name;

		$input = '';
		if ( isset($map_data[$function_name]['map_column_name'])
				AND isset($raw_row[$map_data[$function_name]['map_column_name']]) AND $raw_row[$map_data[$function_name]['map_column_name']] != '' ) {

			//Make sure we check for proper UTF8 encoding and if its not remove the data so we don't cause a PGSQL invalid byte sequence error.
			if ( function_exists('mb_check_encoding') AND mb_check_encoding( $raw_row[$map_data[$function_name]['map_column_name']], 'UTF-8' ) === TRUE ) {
				$input = $raw_row[$map_data[$function_name]['map_column_name']];
			} else {
				Debug::Text('Bad UTF8 encoding!: '. $input, __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		$default_value = '';
		if ( isset($map_data[$function_name]['default_value']) ) {
			$default_value = $map_data[$function_name]['default_value'];
		}

		$parse_hint = '';
		if ( isset($map_data[$function_name]['parse_hint']) ) {
			$parse_hint = $map_data[$function_name]['parse_hint'];
		}

		if ( $input == '' AND $default_value != '' ) {
			$input = $default_value;
		}

		if ( method_exists( $this, $full_function_name ) ) {
			$retval = call_user_func( array( $this, $full_function_name ), $input, $default_value, $parse_hint, $map_data, $raw_row );
			//Debug::Arr( $retval, 'Input: '. $input .' Parse Hint: '. $parse_hint .' Default Value: '. $default_value .' Retval: ', __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		} else {
			if ( $input == '' AND $default_value != '' ) {
				return $default_value;
			}
		}
		
		return $input;
	}


	function preProcess() {
		if ( method_exists( $this, '_preProcess' ) ) {
			return $this->_preProcess();
		}

		return TRUE;
	}

	function preParseRow( $row_number, $raw_row ) {
		if ( method_exists( $this, '_preParseRow' ) ) {
			return $this->_preParseRow( $row_number, $raw_row );
		}

		return $raw_row;
	}
	function postParseRow( $row_number, $raw_row ) {

		if ( method_exists( $this, '_postParseRow' ) ) {
			$retval = $this->_postParseRow( $row_number, $raw_row );
		} else {
			$retval = $raw_row;
		}

		//Handle column aliases.
		$column_aliases = $this->getOptions('column_aliases');
		if ( is_array($column_aliases) ) {
			foreach( $column_aliases as $search => $replace ) {
				if ( isset($retval[$search]) ) {
					$retval[$replace] = $retval[$search];
					//unset($retval[$search]); //Don't unset old values, as the column might be used for validation or reporting back to the user.
				}
			}
		}

		return $retval;
	}

	//Parse data while applying any parse hints.
	//This converts the raw data into something that can be passed directly to the setObjectAsArray functions for this object.
	//Which may include converting one column into multiples and vice versa.
	function parseData() {
		$raw_data = $this->getRawData();
		$column_map = $this->getColumnMap();
		$parsed_data = array();

		//Debug::Arr($column_map, 'Column Map: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !is_array($raw_data) ) {
			Debug::Text('Invalid raw data...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Debug::Arr($raw_data, 'Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($raw_data), NULL, TTi18n::getText('Parsing import data...') );

		$x = 0;
		foreach( $raw_data as $raw_row ) {
			$parsed_data[$x] = $this->preParseRow( $x, $raw_row ); //This needs to run for each row so things like manual_ids can get updated automatically.
			//Debug::Arr($parsed_data[$x], 'Default Data: X: '. $x, __FILE__, __LINE__, __METHOD__, 10);

			foreach( $column_map as $import_column => $import_data ) {
				//Debug::Arr($import_data, 'Import Data X: '. $x .' Column: '. $import_column .' File Column Name: '. $import_data['map_column_name'], __FILE__, __LINE__, __METHOD__, 10);
				//Don't allow importing "id" columns.
				if ( strtolower($import_column) != 'id' AND $import_column !== 0 ) {
					$parsed_data[$x][$import_column] = $this->callInputParseFunction( $import_column, $column_map, $raw_row );
					//Debug::Arr($parsed_data[$x][$import_column], 'Import Column: '. $import_column .' Value: ', __FILE__, __LINE__, __METHOD__, 10);
				} else {
					//Don't allow importing "id" columns.
					unset($parsed_data[$x][$import_data['map_column_name']]);
				}
				
				if ( $import_column != $import_data['map_column_name'] ) {
					//Unset the original unmapped data so it doesn't conflict, especially if its an "id" column.
					//Only if the two columns don't match though, as there was a bug that if someone tried to import column names that matched the TimeTrex
					//names exactly, it would just unset them all.
					unset($parsed_data[$x][$import_data['map_column_name']]);
				}
			}

			$parsed_data[$x] = $this->postParseRow( $x, $parsed_data[$x] ); //This needs to run for each row so things like manual_ids can get updated automatically.

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $x );

			$x++;
		}

		//Don't stop the current progress bar, let it continue into the process/_import function.
		//$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

		Debug::Arr($parsed_data, 'Parsed Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return $this->setParsedData( $parsed_data );
	}

	//This function can't be named "import" as it will be called during __construct() then.
	function process( $validate_only = FALSE ) {
		//Because parse functions can create additional records (like groups, titles, branches)
		//we need to wrap those in a transaction so they can be rolled back on validate_only calls.
		//However non-validate_only calls can't be in any transaction, otherwise skipped records will get rolled back.
		$f = TTnew('UserFactory');

		if ( $validate_only == TRUE ) {
			$f->StartTransaction();
		}

		if ( $this->parseData() == TRUE ) {
			//Call sub-class import function to handle all the processing.
			//This function can call the API*()->set*(), or it can handle creating the objects on its own in advanced cases.
			//FIXME: Should this be wrapped in one big transaction, so its an all or nothing import, or allow the option for this?
			$this->preProcess(); //PreProcess data as a whole before importing.
			$retval = $this->_import( $validate_only );

			if ( $validate_only == FALSE ) {
				$lf = TTnew('LogFactory');
				$table_options = $lf->getOptions('table_name');

				$log_description = TTi18n::getText('Imported').' ';
				if ( isset($table_options[$this->getObject()->getMainClassObject()->getTable()]) ) {
					$log_description .= $table_options[$this->getObject()->getMainClassObject()->getTable()];
				} else {
					$log_description .= TTi18n::getText('Unknown');
				}
				$log_description .= ' '. TTi18n::getText('Records');

				if ( isset($retval['api_details']) AND isset($retval['api_details']['record_details']) ) {
					$log_description .= ' - '. TTi18n::getText('Total').': '. $retval['api_details']['record_details']['total'] .' '. TTi18n::getText('Valid').': '. $retval['api_details']['record_details']['valid'] .' '. TTi18n::getText('Invalid').': '. $retval['api_details']['record_details']['invalid'];
				}
				TTLog::addEntry( $this->user_id, 500, $log_description, $this->user_id, 'users' );
				$this->cleanStoragePath();
			}

			if ( $validate_only == TRUE ) {
				$f->FailTransaction();
				$f->CommitTransaction();
			}

			return $retval;
		}

		if ( $validate_only == TRUE ) {
			$f->FailTransaction();
			$f->CommitTransaction();
		}

		return FALSE;
	}

	//
	// File upload functions.
	//
	function getLocalFileData() {
		$file_name = $this->getStoragePath().$this->getLocalFileName();
		if ( file_exists($file_name) ) {
			return array(
							'size' => filesize( $file_name )
						);
		}

		return FALSE;
	}

	function getRemoteFileName() {
		if ( isset($this->data['remote_file_name']) ) {
			return $this->data['remote_file_name'];
		}
	}
	function setRemoteFileName($value) {
		if ( $value != '' ) {
			$this->data['remote_file_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLocalFileName() {
		$retval = md5( $this->company_id.$this->user_id );
		Debug::Text('Local File Name: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function cleanStoragePath($company_id = NULL) {
		if ( $company_id == '' ) {
			$company_id = $this->company_id;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$dir = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR;

		if ( $dir != '' ) {
			//Delete tmp files.
			foreach(glob($dir.'*') as $filename) {
				unlink($filename);
			}
		}

		return TRUE;
	}

	function getStoragePath( $company_id = NULL ) {
		if ( $company_id == '' ) {
			$company_id = $this->company_id;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		global $config_vars;
		return $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR .'import'. DIRECTORY_SEPARATOR . $company_id . DIRECTORY_SEPARATOR;
	}

	function renameLocalFile() {
		$src_file = $this->getStoragePath().$this->getRemoteFileName();
		$dst_file = $this->getStoragePath().$this->getLocalFileName();
		Debug::Text('Src File: '. $src_file .' Dst File: '. $dst_file, __FILE__, __LINE__, __METHOD__, 10);
		if ( file_exists( $src_file ) AND is_file($src_file) ) {
			$this->deleteLocalFile(); //Delete the dst_file before renaming, just in case.
			return rename( $src_file, $dst_file );
		}

		return FALSE;
	}

	function deleteLocalFile() {
		$file = $this->getStoragePath().$this->getLocalFileName();

		if ( file_exists($file) ) {
			Debug::Text('Deleting Local File: '. $file, __FILE__, __LINE__, __METHOD__, 10);
			@unlink($file);
		}

		return TRUE;
	}

	//
	// Generic parser functions.
	//
	function findClosestMatch( $input, $options, $match_percent = 50 ) {
		//We used to check for the option KEY, but that causes problems if the job code/job name are numeric values
		//that happen to match the record ID in the database. Use this as a fallback method instead perhaps?
		//Also consider things like COUNTRY/PROVINCE matches that are not numeric.
		//if ( isset($options[strtoupper($input)]) ) {
		//	return $input;
		//} else {

		if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
			$retval = Misc::findClosestMatch( $input, $options, $match_percent );
			if ( $retval !== FALSE ) {
				return $retval;
			}
		} else {
			$retval = array_search( strtolower($input), array_map('strtolower', $options) );
			if ( $retval !== FALSE ) {
				return $retval;
			}
		}

		return FALSE; //So we know if no match was made, rather than return $input
	}

	//Used by sub-classes to get general users while importing data.
	function getUserObject( $user_id ) {
		if ( $user_id > 0 ) {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByCompanyIdAndID( $this->company_id, $user_id );
			if ( $ulf->getRecordCount() == 1 ) {
				return $ulf->getCurrent();
			}
		}
		return FALSE;
	}

	function getUserIdentificationColumns() {
		$uf = TTNew('UserFactory');
		$retval = Misc::arrayIntersectByKey( array('user_name', 'employee_number', 'sin'), Misc::trimSortPrefix( $uf->getOptions('columns') ) );

		return $retval;
	}
	function getUserIDByRowData( $raw_row ) {
		//NOTE: Keep in mind that employee numbers can be duplicate based on status (ACTIVE vs TERMINATED), so
		//if there are ever duplicate employee numbers, the import process won't be able to differentiate between them, and the
		//update process will not work.
		//Actually, the above is no longer the case.
		if ( isset($raw_row['user_name']) AND $raw_row['user_name'] != '' ) {
			$filter_data = array( 'user_name' => $raw_row['user_name'] );
			Debug::Text('Searching for existing record based on User Name: '. $raw_row['user_name'], __FILE__, __LINE__, __METHOD__, 10);
		} elseif ( isset($raw_row['sin']) AND $raw_row['sin'] != '' ) {
			//Search for SIN before employee_number, as employee numbers are more likely to change.
			$filter_data = array( 'sin' => $raw_row['sin'] );
			Debug::Text('Searching for existing record based on SIN: '. (int)$raw_row['sin'], __FILE__, __LINE__, __METHOD__, 10);
		} elseif ( isset($raw_row['employee_number']) AND $raw_row['employee_number'] != '' ) {
			$filter_data = array( 'employee_number' => (int)$raw_row['employee_number'] );
			Debug::Text('Searching for existing record based on Employee Number: '. (int)$raw_row['employee_number'], __FILE__, __LINE__, __METHOD__, 10);
		} else {
			Debug::Text('No suitable columns for identifying the employee were specified... ', __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( isset($filter_data) ) {
			//Cache this lookup to help speed up importing of mass data. This is about a 1000x speedup for large imports.
			$cache_id = md5( $this->company_id.serialize( $filter_data ) );
			if ( isset($this->user_id_cache[$cache_id]) ) {
				Debug::Text('Found existing cached record ID: '. $this->user_id_cache[$cache_id], __FILE__, __LINE__, __METHOD__, 10);
				return $this->user_id_cache[$cache_id];
			} else {
				$ulf = TTnew( 'UserListFactory' );
				$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data );
				if ( $ulf->getRecordCount() == 1 ) {
					$tmp_user_obj = $ulf->getCurrent();
					Debug::Text('Found existing record ID: '. $tmp_user_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

					//return $tmp_user_obj->getID();
					$this->user_id_cache[$cache_id] = $tmp_user_obj->getID();
					return $this->user_id_cache[$cache_id];
				}

			}
		}

		Debug::Text('NO employee found!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function _parse_name( $column, $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		if ( $parse_hint == '' ) {
			$parse_hint = 'first_name';
		}

		$retval = $input;
		switch ( $parse_hint ) {
			case 'first_name':
			case 'last_name':
			case 'middle_name':
				$retval = $input;
				break;
			case 'first_last_name':
				if ( $column == 'first_name' ) {
					$offset = 0;
				} else {
					$offset = 1;
				}
				$split_full_name = explode( ' ', $input );
				if ( isset($split_full_name[$offset]) ) {
					$retval = $split_full_name[$offset];
				}
				break;
			case 'last_first_name':
				if ( $column == 'first_name' ) {
					$offset = 1;
				} else {
					$offset = 0;
				}
				$split_full_name = explode( ',', $input );
				if ( isset($split_full_name[$offset]) ) {
					$retval = $split_full_name[$offset];
				}
				break;
			case 'last_first_middle_name':
				if ( $column == 'first_name' ) {
					$offset = 1;
				} else {
					$offset = 0;
				}
				$split_full_name = explode( ',', $input);
				if ( isset($split_full_name[$offset]) ) {
					$retval = $split_full_name[$offset];
					if ( $column == 'first_name' OR $column == 'middle_name' ) {
						if ( $column == 'first_name' ) {
							$offset = 0;
						} else {
							$offset = 1;
						}

						$split_retval = explode( ' ', $retval);
						$retval = $split_retval[$offset];
					}
				}
				break;
			default:
				$retval = $input;
				break;
		}

		Debug::Text('Column: '. $column .' Parse Hint: '. $parse_hint .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function parse_first_name( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->_parse_name( 'first_name', $input, $default_value, $parse_hint, $raw_row );
	}
	function parse_middle_name( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->_parse_name( 'middle_name', $input, $default_value, $parse_hint, $raw_row );
	}
	function parse_last_name( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->_parse_name( 'last_name', $input, $default_value, $parse_hint, $raw_row );
	}

	function parse_postal_code( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		//Excel likes to strip leading zeros from fields, so take 4 digit US zip codes and prepend the zero.
		if ( is_numeric( $input) AND strlen( $input ) <= 4 AND strlen( $input ) >= 1 ) {
			return str_pad( $input, 5, 0, STR_PAD_LEFT );
		}

		return $input;
	}


	function parse_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		$input = str_replace( array('/'), '-', $input);

		return $input;
	}

	function parse_work_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->parse_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL );
	}
	function parse_home_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->parse_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL );
	}
	function parse_fax_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->parse_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL );
	}

	function parse_date( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( $input != '' ) { //Don't try to parse a blank date, this helps in cases where hire/termination dates are imported blank.
			if ( isset($parse_hint) AND $parse_hint != '' ) {
				TTDate::setDateFormat( $parse_hint );
				return TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $input ) );
			} else {
				return TTDate::getMiddleDayEpoch( TTDate::strtotime( $input ) );
			}
		}

		return $input;
	}

	function parse_sex( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		if ( strtolower( $input ) == 'f'
				OR strtolower( $input ) == 'female' ) {
			$retval = 20;
		} elseif ( strtolower( $input ) == 'm'
				OR strtolower( $input ) == 'male' ) {
			$retval = 10;
		} else {
			$retval = 5; //Unspecified
		}

		return $retval;
	}

	function parse_country( $input, $default_value = NULL, $parse_hint = NULL ) {
		$cf = TTnew('CompanyFactory');
		$options = $cf->getOptions( 'country' );

		if ( isset($options[strtoupper($input)]) ) {
			return $input;
		} else {
			if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower($input), array_map('strtolower', $options) );
			}
		}
	}

	function parse_province( $input, $default_value = NULL, $parse_hint = NULL, $map_data = NULL, $raw_row = NULL ) {
		$country = $this->callInputParseFunction( 'country', $map_data, $raw_row );
		Debug::Text('Input: '. $input .' Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);

		$options = array();
		if ( $country != '' ) {
			$cf = TTnew('CompanyFactory');
			$options = $cf->getOptions( 'province', $country );
		}

		if ( !isset($options[strtoupper($input)]) ) {
			if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
				$retval = Misc::findClosestMatch( $input, $options );
				if ( $retval !== FALSE ) {
					return $retval;
				} else {
					$input = '00';
				}
			} else {
				$retval = array_search( strtolower($input), array_map('strtolower', $options) );
				if ( $retval !== FALSE ) {
					return $retval;
				} else {
					$input = '00';
				}
			}
		}

		return $input;
	}
}
?>
