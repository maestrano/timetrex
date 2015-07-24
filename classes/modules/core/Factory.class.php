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
abstract class Factory {
	public $data = array();
	public $old_data = array(); //Used for detailed audit log.

	protected $enable_system_log_detail = TRUE;

	protected $next_insert_id = NULL;
	protected $progress_bar_obj = NULL;
	protected $AMF_message_id = NULL;

	public $validate_only = FALSE; //Used by the API to ignore certain validation checks if we are doing validation only.

	function __construct() {
		global $db, $cache;

		$this->db = $db;
		$this->cache = $cache;
		$this->Validator = new Validator();

		//Callback to the child constructor method.
		if ( method_exists($this, 'childConstruct') ) {
			$this->childConstruct();
		}

		return TRUE;
	}

	/*
	 * Used for updating progress bar for API calls.
	 */
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

	function setProgressBarObject( $obj ) {
		if	( is_object( $obj ) ) {
			$this->progress_bar_obj = $obj;
			return TRUE;
		}

		return FALSE;
	}
	function getProgressBarObject() {
		if	( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}

	//Generic function to return and cache class objects
	//ListFactory, ListFactoryMethod, Variable, ID, IDMethod
	function getGenericObject( $list_factory, $id, $variable, $list_factory_method = 'getById', $id_method = 'getID' ) {
		if ( isset($this->$variable) AND is_object( $this->$variable ) AND $id == $this->$variable->$id_method() ) { //Make sure we always compare that the object IDs match.
			return $this->$variable;
		} else {
			$lf = TTnew( $list_factory );
			$lf->$list_factory_method( $id );
			if ( $lf->getRecordCount() == 1 ) {
				$this->$variable = $lf->getCurrent();
				return $this->$variable;
			}

			return FALSE;
		}
	}

	//Generic function to return and cache CompanyGenericMap data, this greatly improves performance of CalculatePolicy when many policies exist.
	function getCompanyGenericMapData( $company_id, $object_type_id, $id, $variable ) {
		$tmp = &$this->$variable; //Works around a PHP issues where $this->$variable[$id] cause a fatal error on unknown string offset
		if ( $id > 0 AND isset($tmp[$id]) ) {
			return $tmp[$id];
		} else {
			$tmp[$id] = CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $company_id, $object_type_id, $id );
			return $tmp[$id];
		}
	}

	/*
	 * Cache functions
	 */
	function getCache($cache_id, $group_id = NULL ) {
		if ( is_object($this->cache) ) {
			if ( $group_id == NULL ) {
				$group_id = $this->getTable(TRUE);
			}

			$retval = $this->cache->get( $cache_id, $group_id );
			if ( is_object($retval) AND get_class( $retval ) == 'PEAR_Error' ) {
				Debug::Arr($retval, 'WARNING: Unable to read cache file, likely due to permissions or locking! Cache ID: '. $cache_id .' Table: '. $this->getTable(TRUE) .' File: '. $this->cache->_file, __FILE__, __LINE__, __METHOD__, 10);
			} elseif ( is_string( $retval ) AND strpos( $retval, '====' ) === 0 ) { //Detect ADODB serialized record set so it can be properly unserialized.
				return $this->unserializeRS( $retval );
			} else {
				return $retval;
			}
		}

		return FALSE;
	}
	function saveCache($data, $cache_id, $group_id = NULL ) {
		//Cache_ID can't have ':' in it, otherwise it fails on Windows.
		if ( is_object($this->cache) ) {
			if ( $group_id == NULL ) {
				$group_id = $this->getTable(TRUE);
			}

			//Check to ADODB record set, then serialize properly. We only need to do special serializing when there are more than one record.
			if ( is_object( $data ) AND strpos( get_class( $data ), 'ADORecordSet_' ) === 0 AND $data->RecordCount() > 1 ) {
				$data = $this->serializeRS( $data );
			}
			$retval = $this->cache->save( $data, $cache_id, $group_id );
			if ( $retval === FALSE ) {
				//Due to locking, its common that cache files may fail writing once in a while.
				Debug::text('WARNING: Unable to write cache file, likely due to permissions or locking! Cache ID: '. $cache_id .' Table: '. $this->getTable(TRUE) .' File: '. $this->cache->_file, __FILE__, __LINE__, __METHOD__, 10);
			}

			return $retval;
		}
		return FALSE;
	}
	function removeCache($cache_id = NULL, $group_id = NULL ) {
		//See ContributingPayCodePolicyFactory() ->getPayCode() for comments on a bug with caching...
		Debug::text('Attempting to remove cache: '. $cache_id, __FILE__, __LINE__, __METHOD__, 10);
		if ( is_object($this->cache) ) {
			if ( $group_id == '' ) {
				$group_id = $this->getTable(TRUE);
			}
			if ( $cache_id != '' ) {
				Debug::text('Removing cache: '. $cache_id .' Group Id: '. $group_id, __FILE__, __LINE__, __METHOD__, 10);
				return $this->cache->remove( $cache_id, $group_id );
			} elseif ( $group_id != '' ) {
				Debug::text('Removing cache group: '. $group_id, __FILE__, __LINE__, __METHOD__, 10);
				return $this->cache->clean( $group_id );
			}
		}

		return FALSE;
	}
	function setCacheLifeTime( $secs ) {
		if ( is_object($this->cache) ) {
			return $this->cache->setLifeTime( $secs );
		}

		return FALSE;
	}

	//Serialize ADODB recordset.
	function serializeRS( $rs ) {
		global $ADODB_INCLUDED_CSV;
		if ( empty($ADODB_INCLUDED_CSV) ) {
			include_once(ADODB_DIR.'/adodb-csvlib.inc.php');
		}

		return _rs2serialize( $rs, FALSE, $rs->sql );
	}

	//UnSerialize ADODB recordset.
	function unserializeRS( $rs ) {
		$rs = explode("\n", $rs);
		unset($rs[0]);
		$rs = join("\n", $rs);
		return unserialize( $rs );
	}

	function getTable($strip_quotes = FALSE) {
		if ( isset($this->table) ) {
			if ( $strip_quotes == TRUE ) {
				return str_replace('"', '', $this->table );
			} else {
				return $this->table;
			}
		}

		return FALSE;
	}

	//Generic function get any data from the data array.
	//Used mainly for the reports that return grouped queries and such.
	function getColumn( $column ) {

		if ( isset($this->data[$column]) ) {
			return $this->data[$column];
		}

		return FALSE;
	}

	//Print primary columns from object.
	function __toString() {
		if ( method_exists( $this, 'getObjectAsArray' ) ) {
			$columns = Misc::trimSortPrefix( $this->getOptions('columns') );
			$data = $this->getObjectAsArray( $columns );

			if ( is_array($columns) AND is_array($data) ) {
				$retarr = array();
				foreach( $columns as $column => $name ) {
					if ( isset($data[$column]) ) {
						$retarr[] = $name .': '. $data[$column];
					}
				}

				if ( count($retarr) > 0 ) {
					return implode( "\n", $retarr );
				}
			}
		}

		return FALSE;
	}

	function toBool($value) {
		$value = strtolower(trim($value));

		if ($value === TRUE OR $value == 1 OR $value == 't') {
			//return 't';
			return 1;
		} else {
			//return 'f';
			return 0;
		}
	}

	function fromBool($value) {
		if ( $value == 1 ) {
			return TRUE;
		} elseif ( $value == 0 ) {
			return FALSE;
		} elseif ( strtolower( trim( $value ) ) == 't' ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	//Determines if the data is new data, or updated data.
	function isNew( $force_lookup = FALSE ) {
		//Debug::Arr( $this->getId(), 'getId: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getId() === FALSE ) {
			//New Data
			return TRUE;
		} elseif ( $force_lookup == TRUE ) {
			//See if we can find the ID to determine if the record needs to be inserted or update.
			$ph = array( 'id' => $this->getID() );
			$query = 'select id from '. $this->getTable() .' where id = ?';
			$retval = $this->db->GetOne($query, $ph);
			if ( $retval === FALSE ) {
				return TRUE;
			}
		}

		//Not new data
		return FALSE;
	}

	//Determines if we were called by a save function or not.
	//This is useful for determining if we are just validating or actually saving data. Problem is its too late to throw any new validation errors.
	function isSave() {
		$stack = debug_backtrace();

		if ( is_array($stack) ) {
			//Loop through and if we find a Save function call return TRUE.
			//Not sure if this will work in some more complex cases though.
			foreach( $stack as $data ) {
				if ( $data['function'] == 'Save' ) {
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	//Returns the calling function name
	function getCallerFunction() {
		$stack = debug_backtrace();
		if ( isset($stack[1]) ) {
			return $statc[1]['function'];
		}

		return FALSE;
	}

	function getLabelId() {
		//Gets the ID used in validator labels. If no ID, uses "-1";
		if ( $this->getId() == FALSE ) {
			return '-1';
		}

		return $this->getId();
	}

	function getId() {
		if ( isset($this->data['id']) AND $this->data['id'] != NULL) {
			return (int)$this->data['id'];
		}

		return FALSE;
	}
	function setId($id) {
		/*
		if ($id != NULL) {
			//$this->data['id'] = (int)$id;
			$this->data['id'] = $id; //Allow ID to be set as FALSE. Essentially making a new entry.
		}
		*/
		if ( is_numeric( $id ) OR is_bool( $id ) ) {
			$this->data['id'] = $id; //Allow ID to be set as FALSE. Essentially making a new entry.
			return TRUE;
		}

		return FALSE;
	}

	function getEnableSystemLogDetail() {
		if ( isset($this->enable_system_log_detail) ) {
			return $this->enable_system_log_detail;
		}

		return FALSE;
	}
	function setEnableSystemLogDetail($bool) {
		$this->enable_system_log_detail = (bool)$bool;

		return TRUE;
	}

	function getDeleted() {
		if ( isset($this->data['deleted']) ) {
			return $this->fromBool( $this->data['deleted'] );
		}

		return FALSE;
	}
	function setDeleted($bool) {
		$value = (bool)$bool;

		//Handle Postgres's boolean values.
		if ($value === TRUE) {
			//Only set this one we're deleting
			$this->setDeletedDate();
			$this->setDeletedBy();
		}

		$this->data['deleted'] = $this->toBool($value);
		return TRUE;
	}

	function getCreatedDate() {
		if ( isset($this->data['created_date']) ) {
			return (int)$this->data['created_date'];
		}

		return FALSE;
	}
	function setCreatedDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ( $epoch == NULL OR $epoch == '' OR $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if	(	$this->Validator->isDate(		'created_date',
												$epoch,
												TTi18n::gettext('Incorrect Date')) ) {

			$this->data['created_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}
	function getCreatedBy() {
		if ( isset($this->data['created_by']) ) {
			return (int)$this->data['created_by'];
		}

		return FALSE;
	}
	function setCreatedBy($id = NULL) {
		$id = (int)trim($id);

		if ( empty($id) ) {
			global $current_user;

			if ( is_object($current_user) ) {
				$id = $current_user->getID();
			} else {
				return FALSE;
			}
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = TTnew( 'UserListFactory' );
		if ( $this->Validator->isResultSetWithRows(	'created_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {

			$this->data['created_by'] = $id;

			return TRUE;
		}

		return FALSE;
		*/

		$this->data['created_by'] = (int)$id;

		return TRUE;
	}

	function getUpdatedDate() {
		if ( isset($this->data['updated_date']) ) {
			return (int)$this->data['updated_date'];
		}

		return FALSE;
	}
	function setUpdatedDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ( $epoch == NULL OR $epoch == '' OR $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if	(	$this->Validator->isDate(		'updated_date',
												$epoch,
												TTi18n::gettext('Incorrect Date')) ) {

			$this->data['updated_date'] = $epoch;

			//return TRUE;
			//Return the value so we can use it in getUpdateSQL
			return $epoch;
		}

		return FALSE;

	}
	function getUpdatedBy() {
		if ( isset($this->data['updated_by']) ) {
			return (int)$this->data['updated_by'];
		}

		return FALSE;
	}
	function setUpdatedBy($id = NULL) {
		$id = (int)trim($id);

		if ( empty($id) ) {
			global $current_user;

			if ( is_object($current_user) ) {
				$id = $current_user->getID();
			} else {
				return FALSE;
			}
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = TTnew( 'UserListFactory' );
		if ( $this->Validator->isResultSetWithRows(	'updated_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {
			$this->data['updated_by'] = $id;

			//return TRUE;
			return $id;
		}

		return FALSE;
		*/

		$this->data['updated_by'] = $id;

		return $id;
	}


	function getDeletedDate() {
		if ( isset($this->data['deleted_date']) ) {
			return $this->data['deleted_date'];
		}

		return FALSE;
	}
	function setDeletedDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ( $epoch == NULL OR $epoch == '' OR $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if	(	$this->Validator->isDate(		'deleted_date',
												$epoch,
												TTi18n::gettext('Incorrect Date')) ) {

			$this->data['deleted_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}
	function getDeletedBy() {
		if ( isset($this->data['deleted_by']) ) {
			return $this->data['deleted_by'];
		}

		return FALSE;
	}
	function setDeletedBy($id = NULL) {
		$id = trim($id);

		if ( empty($id) ) {
			global $current_user;

			if ( is_object($current_user) ) {
				$id = $current_user->getID();
			} else {
				return FALSE;
			}
		}

		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'updated_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {

			$this->data['deleted_by'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function setCreatedAndUpdatedColumns( $data ) {
		Debug::text(' Set created/updated columns...', __FILE__, __LINE__, __METHOD__, 10);
		//Update array in-place.
		if ( isset($data['created_by']) AND is_numeric($data['created_by']) AND $data['created_by'] > 0 ) {
			$this->setCreatedBy( $data['created_by'] );
		}
		if ( isset($data['created_by_id']) AND is_numeric($data['created_by_id']) AND $data['created_by_id'] > 0 ) {
			$this->setCreatedBy( $data['created_by_id'] );
		}
		if ( isset($data['created_date']) AND $data['created_date'] != FALSE AND $data['created_date'] != '' ) {
			$this->setCreatedDate( TTDate::parseDateTime( $data['created_date'] ) );
		}

		if ( isset($data['updated_by']) AND is_numeric($data['updated_by']) AND $data['updated_by'] > 0 ) {
			$this->setUpdatedBy( $data['updated_by'] );
		}
		if ( isset($data['updated_by_id']) AND is_numeric($data['updated_by_id']) AND $data['updated_by_id'] > 0 ) {
			$this->setUpdatedBy( $data['updated_by_id'] );
		}
		if ( isset($data['updated_date']) AND $data['updated_date'] != FALSE AND $data['updated_date'] != '' ) {
			$this->setUpdatedDate( TTDate::parseDateTime( $data['updated_date'] ) );
		}

		return TRUE;
	}
	function getCreatedAndUpdatedColumns( &$data, $include_columns = NULL ) {
		//Update array in-place.
		if ( $include_columns == NULL OR ( isset($include_columns['created_by_id']) AND $include_columns['created_by_id'] == TRUE) ) {
			$data['created_by_id'] = $this->getCreatedBy();
		}
		if ( $include_columns == NULL OR ( isset($include_columns['created_by']) AND $include_columns['created_by'] == TRUE) ) {
			$data['created_by'] = Misc::getFullName( $this->getColumn('created_by_first_name'), $this->getColumn('created_by_middle_name'), $this->getColumn('created_by_last_name') );
		}
		if ( $include_columns == NULL OR ( isset($include_columns['created_date']) AND $include_columns['created_date'] == TRUE) ) {
			$data['created_date'] = TTDate::getAPIDate( 'DATE+TIME', $this->getCreatedDate() );
		}
		if ( $include_columns == NULL OR ( isset($include_columns['updated_by_id']) AND $include_columns['updated_by_id'] == TRUE) ) {
			$data['updated_by_id'] = $this->getUpdatedBy();
		}
		if ( $include_columns == NULL OR ( isset($include_columns['updated_by']) AND $include_columns['updated_by'] == TRUE) ) {
			$data['updated_by'] = Misc::getFullName( $this->getColumn('updated_by_first_name'), $this->getColumn('updated_by_middle_name'), $this->getColumn('updated_by_last_name') );
		}
		if ( $include_columns == NULL OR ( isset($include_columns['updated_date']) AND $include_columns['updated_date'] == TRUE) ) {
			$data['updated_date'] = TTDate::getAPIDate( 'DATE+TIME', $this->getUpdatedDate() );
		}

		return TRUE;
	}

	function getPermissionColumns( &$data, $object_user_id, $created_by_id, $permission_children_ids = NULL, $include_columns = NULL ) {
		$permission = new Permission();

		if( $include_columns == NULL OR ( isset($include_columns['is_owner']) AND $include_columns['is_owner'] == TRUE) ) {
			//If is_owner column is passed directly from SQL, use that instead of adding it here. Specifically the UserListFactory uses this.
			if ( $this->getColumn('is_owner') !== FALSE ) {
				$data['is_owner'] = (bool)$this->getColumn('is_owner');
			} else {
				$data['is_owner'] = $permission->isOwner( $created_by_id, $object_user_id );
			}
		}

		if ( $include_columns == NULL OR ( isset($include_columns['is_child']) AND $include_columns['is_child'] == TRUE) ) {
			//If is_child column is passed directly from SQL, use that instead of adding it here. Specifically the UserListFactory uses this.
			if ( $this->getColumn('is_child') !== FALSE ) {
				$data['is_child'] = (bool)$this->getColumn('is_child');
			} else {
				if ( is_array($permission_children_ids) ) {
					//ObjectID should always be a user_id.
					$data['is_child'] = $permission->isChild( $object_user_id, $permission_children_ids );
				} else {
					$data['is_child'] = FALSE;
				}
			}
		}

		return TRUE;
	}

	function getOptions($name, $parent = NULL) {
		if ( $parent == NULL OR $parent == '') {
			return $this->_getFactoryOptions( $name );
		} else {
			$retval = $this->_getFactoryOptions( $name, $parent );
			if ( isset($retval[$parent]) ) {
				return $retval[$parent];
			}
		}

		return FALSE;
	}
	protected function _getFactoryOptions( $name, $parent = NULL ) {
		return FALSE;
	}

	function getVariableToFunctionMap( $data = NULL ) {
		return $this->_getVariableToFunctionMap( $data );
	}
	protected function _getVariableToFunctionMap( $data ) {
		return FALSE;
	}

	function getRecordCount() {
		if ( isset($this->rs->_numOfRows) ) { //Check a deep variable to make sure it is in fact a valid ADODB record set, just in case some other object is passed in.
			return $this->rs->RecordCount();
		}

		return FALSE;
	}

	function getCurrentRow( $offset = 1 ) {
		if ( isset($this->rs) AND isset($this->rs->_currentRow) ) {
			return ( $this->rs->_currentRow + (int)$offset );
		}

		return FALSE;
	}

	function setQueryStatementTimeout( $milliseconds = NULL ) {
		global $db;

		if ( $milliseconds == '' ) {
			global $config_vars;
			$milliseconds = 0;
			if ( isset($this->config['other']['query_statement_timeout']) ) {
				$milliseconds = (int)$this->config['other']['query_statement_timeout'];
			}
		}

		Debug::Text('Setting DB query statement timeout to: '. $milliseconds, __FILE__, __LINE__, __METHOD__, 10);
		if ( strncmp($this->db->databaseType, 'postgres', 8) == 0 ) {
			$this->db->Execute('SET statement_timeout = '. (int)$milliseconds);
		}

		return TRUE;
	}

	private function getRecordSetColumnList($rs) {
		if (is_object($rs)) {
			for ($i = 0, $max = $rs->FieldCount(); $i < $max; $i++) {
				$field = $rs->FetchField($i);
				$fields[] = $field->name;
			}

			return $fields;
		}

		return FALSE;
	}

	protected function castInteger( $int, $type = 'int' ) {
		//smallint	2 bytes	small-range integer	-32768 to +32767
		//integer	4 bytes	typical choice for integer	-2147483648 to +2147483647
		//bigint	8 bytes	large-range integer	-9223372036854775808 to 9223372036854775807
		switch( $type ) {
			case 'smallint':
				if ( $int > 32767 OR $int < -32768 ) {
					$retval = FALSE;
				} else {
					$retval = (int)$int;
				}
				break;
			case 'numeric':
			case 'int':
				if ( $int > 2147483647 OR $int < -2147483648 ) {
					$retval = FALSE;
				} else {
					$retval = (int)$int;
				}
				break;
			case 'bigint':
				if ( $int > 9223372036854775807 OR $int < -9223372036854775808 ) {
					$retval = FALSE;
				} else {
					$retval = (int)$int;
				}
				break;
			default:
				return $int; //Make sure if the $type is not recognized we just return the raw value again.
				break;
		}

		if ( $retval === FALSE ) {
			Debug::Text(' Integer outside range: '. $int .' Type: '. $type, __FILE__, __LINE__, __METHOD__, 10);
		}
		return $retval;
	}

	protected function getListSQL($array, &$ph = NULL, $cast = FALSE ) {
		//Debug::Arr($array, 'List Values:', __FILE__, __LINE__, __METHOD__, 10);
		if ( $ph === NULL ) {
			if ( is_array( $array ) AND count($array) > 0) {
				return '\''.implode('\', \'', $array).'\'';
			} elseif ( is_array($array) ) {
				//Return NULL, because this is an empty array.
				return 'NULL';
			} elseif ( $array == '' ) {
				return 'NULL';
			}

			//Just a single ID, return it.
			return $array;
		} else {
			//Debug::Arr($ph, 'Place Holder BEFORE:', __FILE__, __LINE__, __METHOD__, 10);

			//Append $array values to end of $ph, return
			//one "?, " for each element in $array.

			$array_count = count($array);
			if ( is_array( $array ) AND $array_count > 0 ) {
				foreach( $array as $key => $val ) {
					$ph_arr[] = '?';

					//Make sure we filter out any FALSE or NULL values from going into a SQL list.
					//Replace them with "-1"'s so we keep the same number of place holders.
					//This should hopefully prevent SQL errors if a FALSE creeps into the SQL list array.
					if ( is_null($val) === FALSE AND $val !== '' AND strtolower($val) !== 'false' AND strtolower($val) !== 'true' AND ( is_numeric( $val ) OR is_string( $val ) ) ) {
						$val = $this->castInteger( $val, $cast );
						if ( $val === FALSE ) {
							$ph[] = -1;
						} else {
							$ph[] = $val;
						}
					} else {
						$ph[] = '-1';
					}
				}

				if ( isset($ph_arr) ) {
					$retval = implode(',', $ph_arr);
				}
			} elseif ( is_array($array) ) {
				//Return NULL, because this is an empty array.
				//This may have to return -1 instead of NULL
				//$ph[] = 'NULL';
				$ph[] = -1;
				$retval = '?';
			} elseif ( $array === FALSE OR $array === '' ) { //Make sure we don't catch int(0) here.
				//$ph[] = 'NULL';
				$ph[] = -1;
				$retval = '?';
			} else {
				$array = $this->castInteger( $array, $cast );
				if ( $array === FALSE ) {
					$ph[] = -1;
				} else {
					$ph[] = $array;
				}
				$retval = '?';
			}

			//Debug::Arr($ph, 'Place Holder AFTER: Cast: '. $cast, __FILE__, __LINE__, __METHOD__, 10);

			//Just a single ID, return it.
			return $retval;
		}
	}

	//This function takes plain input from the user and creates a SQL statement for filtering
	//based on a date range.
	// Supported Syntax:
	//					>=01-Jan-09
	//					<=01-Jan-09
	//					<01-Jan-09
	//					>01-Jan-09
	//					>01-Jan-09 & <10-Jan-09
	//
	function getDateRangeSQL( $str, $column, $format = 'epoch', $include_blank_dates = FALSE ) {
		if ( $str == '' ) {
			return FALSE;
		}

		if ( $column == '' ) {
			return FALSE;
		}

		//Debug::text(' Format: '. $format .' String: '. $str .' Column: '. $column, __FILE__, __LINE__, __METHOD__, 10);

		$operators = array(
							'>',
							'<',
							'>=',
							'<=',
							'=',
							);
		$operations = FALSE;
		//Parse input, separate any subqueries first.
		$split_str = explode( '&', $str, 2 ); //Limit sub-queries
		if ( is_array($split_str) ) {
			foreach( $split_str as $tmp_str ) {
				$tmp_str = trim($tmp_str);
				$date = (int)TTDate::parseDateTime( str_replace( $operators, '', $tmp_str ) );
				//Debug::text(' Parsed Date: '. $tmp_str .' To: '. TTDate::getDate('DATE+TIME', $date) .' ('. $date .')', __FILE__, __LINE__, __METHOD__, 10);

				if ( $date != 0 ) {
					preg_match('/^>=|>|<=|</i', $tmp_str, $operator );
					//Debug::Arr($operator, ' Operator: ', __FILE__, __LINE__, __METHOD__, 10);
					if ( isset($operator[0]) AND in_array( $operator[0], $operators) ) {
						if ( $operator[0] == '<=' ) {
							$date = TTDate::getEndDayEpoch( $date );
						} elseif ( $operator[0] == '>' ) {
							$date = TTDate::getEndDayEpoch( $date );
						}
						if ( $format == 'timestamp' )  {
							$date = '\''.$this->db->bindTimeStamp( $date ).'\'';
						} elseif ( $format == 'datestamp' ) {
							$date = '\''.$this->db->bindDateStamp( $date ).'\'';
						}

						if ( $include_blank_dates == TRUE ) {
							$operations[] = '('. $column .' '. $operator[0] .' '. $date .' OR ( '. $column .' is NULL OR '. $column .' = 0 ) )';
						} else {
							$operations[] = $column .' '. $operator[0] .' '. $date;
						}
					} else {
						//FIXME: Need to handle date filters without any operators better.
						//for example JobListFactory and JobSummaryReport and the time period is specified.
						$date1 = TTDate::getBeginDayEpoch( $date );
						$date2 = TTDate::getEndDayEpoch( $date );
						if ( $format == 'timestamp' )  {
							$date1 = '\''.$this->db->bindTimeStamp( $date1 ).'\'';
							$date2 = '\''.$this->db->bindTimeStamp( $date2 ).'\'';
						} elseif ( $format == 'datestamp' ) {
							$date1 = '\''.$this->db->bindDate( $date1 ).'\'';
							$date2 = '\''.$this->db->bindDate( $date2 ).'\'';
						}

						//Debug::text(' No operator specified... Using a 24hr period', __FILE__, __LINE__, __METHOD__, 10);
						if ( $include_blank_dates == TRUE ) {
							$operations[] = '('. $column .' >= '. $date1 .' OR ( '. $column .' is NULL OR '. $column .' = 0 ) )';
							$operations[] = '('. $column .' <= '. $date2 .' OR ( '. $column .' is NULL OR '. $column .' = 0 ) )';
						} else {
							$operations[] = $column .' >= '. $date1;
							$operations[] = $column .' <= '. $date2;
						}
					}
				}
			}
		}

		//Debug::Arr($operations, ' Operations: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($operations) ) {
			$retval = ' ( '. implode(' AND ', $operations ) .' )';
			//Debug::text(' Query parts: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}

		return FALSE;
	}

	///SQL where clause Syntax:
	//	* or % as wildcard.
	//	"<query>" as exact match, no default wildcard and no metaphone

	//Handles '*' and '%' as wildcards, defaults to wildcard on the end always.
	//If no wildcard is to be added, the last character should be |
	protected function handleSQLSyntax( $arg ) {
		$arg = str_replace('*', '%', trim($arg) );

		//Make sure we don't add '%' if $arg is blank.
		if ( $arg != '' AND strpos($arg, '%') === FALSE AND ( strpos( $arg, '|') === FALSE AND strpos( $arg, '"') === FALSE ) ) {
			$arg .= '%';
		}

		return addslashes( $this->stripSQLSyntax( $arg ) ); //Addaslashes to prevent SQL syntax error if %\ is at the end of the where clause.
	}

	protected function stripSQLSyntax( $arg ) {
		return str_replace( array('"'), '', $arg); //Strip syntax characters out.
	}

	protected function getWhereClauseSQL( $columns, $args, $type, &$ph, $query_stub = NULL, $and = TRUE ) {
		//Debug::Text('Type: '. $type .' Query Stub: '. $query_stub .' AND: '. (int)$and, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($columns, 'Columns: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($args, 'Args: ', __FILE__, __LINE__, __METHOD__, 10);
		switch( strtolower($type) ) {
			case 'text':
				if ( isset($args) AND !is_array($args) AND trim($args) != '' ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = 'lower('. $columns .') LIKE ?';
					}
					$ph[] = $this->handleSQLSyntax( strtolower($args) );
					$retval = $query_stub;
				}
				break;
			case 'text_metaphone':
				if ( isset($args) AND !is_array($args) AND trim($args) != '' ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = '( lower('. $columns .') LIKE ? OR '. $columns .'_metaphone LIKE ? )';
					}

					$ph[] = $this->handleSQLSyntax( strtolower($args) );
					if ( strpos($args, '"') !== FALSE ) { //ignores metaphone search.
						$ph[] = '';
					} else {
						$ph[] = $this->handleSQLSyntax( metaphone( Misc::stripThe( $args ) ) ); //Strip "The " from metaphones so its easier to find company names.
					}
					$retval = $query_stub;
				}
				break;
			case 'text_list':
			case 'lower_text_list':
			case 'upper_text_list':
				if ( !is_array($args) ) {
					$args = array( (string)$args );
				}

				$sql_text_case_function = NULL;
				if ( $type == 'upper_text_list' OR $type == 'lower_text_list' ) {
					if ( $type == 'upper_text_list' ) {
						$sql_text_case_function = 'UPPER';
						$text_case = CASE_UPPER;
					} else {
						$sql_text_case_function = 'LOWER';
						$text_case = CASE_LOWER;
					}
					$args = array_flip( array_change_key_case( array_flip( $args ), $text_case ) );
				}

				if ( isset($args) AND isset($args[0]) AND !in_array( -1, $args) AND !in_array( '00', $args) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $sql_text_case_function.'('. $columns .') IN (?)';
					}
					$retval = str_replace('?', $this->getListSQL($args, $ph, 'string' ), $query_stub );
				}

				break;
			case 'province':
				if ( !is_array($args) ) {
					$args = (array)$args;
				}

				if ( isset($args) AND isset($args[0]) AND !in_array( -1, $args) AND !in_array( '00', $args) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $columns .' IN (?)';
					}
					$retval = str_replace('?', $this->getListSQL($args, $ph), $query_stub );
				}
				break;
			case 'phone':
				if ( isset($args) AND !is_array($args) AND trim($args) != '' ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = "( replace( replace( replace( replace( replace( replace( ". $columns .", ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '') LIKE ? OR ". $columns ." LIKE ? )";
					}

					$ph[] = $ph[] = $this->handleSQLSyntax( preg_replace('/[^0-9\%\*\"]/', '', strtolower($args) ) ); //Need the same value twice for the query stub.
					$retval = $query_stub;
				}
				break;
			case 'smallint':
			case 'int':
			case 'bigint':
			case 'numeric':
				if ( !is_array($args) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' AND !is_array($columns) ) {
						if ( $args === NULL ) {
							$query_stub = $columns .' is NULL';
						} else {
							$args = $this->castInteger( $this->Validator->stripNonNumeric( $args ), $type );
							if ( is_numeric( $args ) ) {
								$ph[] = $args;
								$query_stub = $columns .' = ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'smallint_list':
			case 'int_list':
			case 'bigint_list':
			case 'numeric_list':
				if ( !is_array($args) ) {
					$args = (array)$args;
				}
				if ( isset($args) AND isset($args[0]) AND !in_array( -1, $args) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $columns .' IN (?)';
					}
					$retval = str_replace('?', $this->getListSQL($args, $ph, str_replace( '_list', '', $type ) ), $query_stub );
				}
				break;
			case 'numeric_list_with_all': //Doesn't check for -1 and ignore the filter completely. Used in KPIListFactory.
				if ( !is_array($args) ) {
					$args = (array)$args;
				}
				if ( isset($args) AND isset($args[0]) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $columns .' IN (?)';
					}
					$retval = str_replace('?', $this->getListSQL($args, $ph, str_replace( '_list', '', 'numeric_list' ) ), $query_stub );
				}
				break;
			case 'not_smallint_list':
			case 'not_int_list':
			case 'not_bigint_list':
			case 'not_numeric_list':
				if ( !is_array($args) ) {
					$args = (array)$args;
				}
				if ( isset($args) AND isset($args[0]) AND !in_array( -1, $args) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $columns .' NOT IN (?)';
					}
					$retval = str_replace('?', $this->getListSQL($args, $ph, str_replace( array('not_', '_list'), '', $type ) ), $query_stub );
				}
				break;
			case 'tag':
				//We need company_id and object_type_id passed in.
				if ( isset($args['company_id']) AND isset($args['object_type_id']) AND isset($args['tag']) ) {
					//Parse the tags search syntax to determine ANY, AND, OR searches.
					$parsed_tags = CompanyGenericTagFactory::parseTags($args['tag']);
					//Debug::Arr($parsed_tags, 'Parsed Tags: ', __FILE__, __LINE__, __METHOD__, 10);
					if ( is_array($parsed_tags) ) {
						$retval = '';
						if ( isset($parsed_tags['add']) AND count($parsed_tags['add']) > 0 ) {
							$query_stub = ' EXISTS	(
														select 1
														from company_generic_tag_map as cgtm
														INNER JOIN company_generic_tag as cgt ON (cgtm.tag_id = cgt.id)
														WHERE cgt.company_id = '. (int)$args['company_id'] .'
															AND cgtm.object_type_id = '. (int)$args['object_type_id'] .'
															AND '. $columns .' = cgtm.object_id
															AND ( lower(cgt.name) in (?) )
														group by cgtm.object_id
														HAVING COUNT(*) = '. count( $parsed_tags['add'] ) .'
													)';
							$retval .= str_replace('?', $this->getListSQL( Misc::arrayChangeValueCase($parsed_tags['add']), $ph), $query_stub );
							if ( isset($parsed_tags['delete']) AND count($parsed_tags['delete']) > 0 ) {
								$retval .= ' AND ';
							}
						}

						if ( isset($parsed_tags['delete']) AND count($parsed_tags['delete']) > 0 ) {
							$query_stub = ' NOT EXISTS	(
														select 1
														from company_generic_tag_map as cgtm
														INNER JOIN company_generic_tag as cgt ON (cgtm.tag_id = cgt.id)
														WHERE cgt.company_id = '. (int)$args['company_id'] .'
															AND cgtm.object_type_id = '. (int)$args['object_type_id'] .'
															AND '. $columns .' = cgtm.object_id
															AND ( lower(cgt.name) in (?) )
														group by cgtm.object_id
														HAVING COUNT(*) = '. count( $parsed_tags['delete'] ) .'
													)';
							$retval .= str_replace('?', $this->getListSQL( Misc::arrayChangeValueCase($parsed_tags['delete']), $ph), $query_stub );
						}
					}
				}
				if ( !isset($retval) ) {
					$retval = '';
				}
				break;
			case 'date_stamp': //Input epoch values, but convert bind to datestamp for datastamp datatypes.
				if ( !is_array($args) ) {
					$args = (array)$args;
				}
				if ( isset($args) AND isset($args[0]) AND !in_array( -1, $args) ) {
					foreach( $args as $tmp_arg ) {
						$converted_args[] = $this->db->bindDate( (int)$tmp_arg );
					}

					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $columns .' IN (?)';
					}
					$retval = str_replace('?', $this->getListSQL($converted_args, $ph ), $query_stub );
				}
				break;
			case 'start_datestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
			case 'end_datestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
				if ( !is_array($args) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' AND !is_array($columns) ) {
						$args = $this->castInteger( $this->Validator->stripNonNumeric( $args ), 'int' );
						if ( is_numeric( $args ) ) {
							$ph[] = $this->db->bindDate( $args );
							if ( strtolower($type) == 'start_datestamp' ) {
								$query_stub = $columns .' >= ?';
							} else {
								$query_stub = $columns .' <= ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'start_timestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
			case 'end_timestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
				if ( !is_array($args) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' AND !is_array($columns) ) {
						$args = $this->castInteger( $this->Validator->stripNonNumeric( $args ), 'int' );
						if ( is_numeric( $args ) ) {
							$ph[] = $this->db->bindTimeStamp( $args );
							if ( strtolower($type) == 'start_timestamp' ) {
								$query_stub = $columns .' >= ?';
							} else {
								$query_stub = $columns .' <= ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'start_date': //Uses EPOCH values only, used for integer datatype columns
			case 'end_date':
				if ( !is_array($args) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' AND !is_array($columns) ) {
						$args = $this->castInteger( $this->Validator->stripNonNumeric( $args ), 'int' );
						if ( is_numeric( $args ) ) {
							$ph[] = $args;
							if ( strtolower($type) == 'start_date' ) {
								$query_stub = $columns .' >= ?';
							} else {
								$query_stub = $columns .' <= ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'date_range': //Uses EPOCH values only, used for integer datatype columns
			case 'date_range_include_blank': //Include NULL/Blank dates.
			case 'date_range_datestamp': 
			case 'date_range_datestamp_include_blank': //Include NULL/Blank dates.
			case 'date_range_timestamp': //Uses text timestamp values, used for timestamp datatype columns
			case 'date_range_timestamp_include_blank': //Include NULL/Blank dates.
				if ( isset($args) AND !is_array($args) AND trim($args) != '' ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$include_blank_dates = ( strpos( $type, '_include_blank' ) !== FALSE ) ? TRUE : FALSE;
						switch( $type ) {
							case 'date_range_timestamp':
							case 'date_range_timestamp_and_blank':
								$query_stub = $this->getDateRangeSQL($args, $columns, 'timestamp', $include_blank_dates );
								break;
							case 'date_range_datestamp':
							case 'date_range_datestamp_and_blank':
								$query_stub = $this->getDateRangeSQL($args, $columns, 'datestamp', $include_blank_dates );
								break;
							default:
								$query_stub = $this->getDateRangeSQL($args, $columns, 'epoch', $include_blank_dates );
								break;
						}
					}
					//Debug::Text('Query Stub: '. $query_stub, __FILE__, __LINE__, __METHOD__, 10);
					$retval = $query_stub;
				}
				break;
			case 'user_id_or_name':
				if ( isset( $args ) AND is_array( $args ) ) {
					$retval = $this->getWhereClauseSQL( $columns[0], $args, 'numeric_list', $ph, '', FALSE );
				}
				if ( isset( $args ) AND !is_array( $args ) AND trim( $args ) != '' ) {
					$ph[] = $ph[] = $this->handleSQLSyntax(strtolower( trim( $args ) ));
					$retval = '(lower('.$columns[1].') LIKE ? OR lower('.$columns[2].') LIKE ? ) ';
				}
				break;
			case 'boolean':
				if ( isset($args) AND !is_array($args) AND trim($args) != '' AND !is_numeric($args) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						switch( strtolower( trim($args) ) ) {
							case 'yes':
							case 'y':
							case 'true':
							case 't':
							case 'on':
								$ph[] = 1;
								$query_stub = $columns .' = ?';
								break;
							case 'no':
							case 'n':
							case 'false':
							case 'f':
							case 'off':
								$ph[] = 0;
								$query_stub = $columns .' = ?';
								break;
						}
					}
					$retval = $query_stub;
				}
				break;
			default:
				Debug::Text('Invalid type: '. $type, __FILE__, __LINE__, __METHOD__, 10);
				break;
		}

		if ( isset($retval) ) {
			$and_sql = NULL;
			if ( $and == TRUE AND $retval != '' ) { //Don't prepend ' AND' if there is nothing to come after it.
				$and_sql = 'AND ';
			}

			//Debug::Arr($ph, 'Query Stub: '. $and_sql.$retval, __FILE__, __LINE__, __METHOD__, 10);
			return ' '.$and_sql.$retval.' '; //Wrap each query stub in spaces.
		}

		return NULL;
	}

	//Parses out the exact column name, without any aliases, or = signs in it.
	private function parseColumnName($column) {
		$column = trim($column);

		if ( strstr($column, '=') ) {
			$tmp_column = explode('=', $column);
			$retval = trim($tmp_column[0]);
			unset($tmp_column);
		} else {
			$retval = $column;
		}

		if ( strstr($retval, '.') ) {
			$tmp_column = explode('.', $retval);
			$retval = $tmp_column[1];
			unset($tmp_column);
		}
		//Debug::Text('Column: '. $column .' RetVal: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	protected function getWhereSQL($array, $append_where = FALSE) {
		//Make this a multi-dimensional array, the first entry
		//is the WHERE clauses with '?' for placeholders, the second is
		//the array to replace the placeholders with.
		if ( is_array($array) ) {
			$rs = $this->getEmptyRecordSet();
			$fields = $this->getRecordSetColumnList($rs);

			foreach ($array as $orig_column => $expression) {
				if ( is_array( $expression ) ) { //Handle nested arrays, so we the same column can be specified multiple times.
					foreach ($expression as $orig_column => $expression) {
						$orig_column = trim($orig_column);
						$column = $this->parseColumnName( $orig_column );
						$expression = trim($expression);

						if ( in_array($column, $fields) ) {
							$sql_chunks[] = $orig_column .' '. $expression;
						}
					}
				} else {
					$orig_column = trim($orig_column);
					$column = $this->parseColumnName( $orig_column );
					$expression = trim($expression);

					if ( in_array($column, $fields) ) {
						$sql_chunks[] = $orig_column .' '. $expression;
					}
				}
			}

			if ( isset($sql_chunks) ) {
				//Don't escape this, as prevents quotes from being used in cases where they are required link bindTimeStamp
				//$sql = $this->db->escape( implode(' AND ', $sql_chunks) );
				$sql = implode(' AND ', $sql_chunks);

				if ($append_where == TRUE) {
					return ' WHERE '.$sql;
				} else {
					return ' AND '.$sql;
				}
			}
		}

		return FALSE;
	}

	protected function getColumnsFromAliases( $columns, $aliases ) {
		// Columns is the original column array.
		//
		// Aliases is an array of search => replace key/value pairs.
		//
		// This is used so the frontend can sort by the column name (ie: type) and it can be converted to type_id for the SQL query.
		if ( is_array($columns) AND is_array( $aliases ) ) {
			$columns = $this->convertFlexArray( $columns );

			//Debug::Arr($columns, 'Columns before: ', __FILE__, __LINE__, __METHOD__, 10);

			foreach( $columns as $column => $sort_order ) {
				if ( isset($aliases[$column]) AND !isset($columns[$aliases[$column]]) ) {
					$retarr[$aliases[$column]] = $sort_order;
				} else {
					$retarr[$column] = $sort_order;
				}

			}
			//Debug::Arr($retarr, 'Columns after: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		return $columns;
	}

	function convertFlexArray( $array ) {
		//Flex doesn't appear to be consistent on the order the fields are placed into an assoc array, so
		//handle this type of array too:
		// array(
		//		0 => array('first_name' => 'asc')
		//		1 => array('last_name' => 'desc')
		//		)

		if ( isset($array[0]) AND is_array($array[0]) ) {
			Debug::text('Found Flex Sort Array, converting to proper format...', __FILE__, __LINE__, __METHOD__, 10);

			//Debug::Arr($array, 'Before conversion...', __FILE__, __LINE__, __METHOD__, 10);

			$new_arr = array();
			foreach( $array as $tmp_order => $tmp_arr ) {
				if ( is_array($tmp_arr) ) {
					foreach( $tmp_arr as $tmp_column => $tmp_order ) {
						$new_arr[$tmp_column] = $tmp_order;
					}
				}
			}
			$array = $new_arr;
			unset($tmp_key, $tmp_arr, $tmp_order, $tmp_column, $new_arr);
			//Debug::Arr($array, 'Converted format...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $array;
	}

	protected function getSortSQL($array, $strict = TRUE, $additional_fields = NULL) {
		if ( is_array($array) ) {
			$array = $this->convertFlexArray( $array );

			$alt_order_options = array( 1 => 'asc', -1 => 'desc');
			$order_options = array('asc', 'desc');

			$rs = $this->getEmptyRecordSet();
			$fields = $this->getRecordSetColumnList($rs);

			//Merge additional fields
			if ( is_array($additional_fields) ) {
				$fields = array_merge( $fields, $additional_fields);
			}
			//Debug::Arr($fields, 'Column List:', __FILE__, __LINE__, __METHOD__, 10);

			foreach ( $array as $orig_column => $order ) {
				$orig_column = trim($orig_column);

				$column = $this->parseColumnName( $orig_column );
				$order = trim($order);
				//Handle both order types.
				if ( is_numeric($order) ) {
					if ( isset($alt_order_options[$order]) ) {
						$order = $alt_order_options[$order];
					}
				}

				if ( $strict == FALSE
						OR (	(
									in_array($column, $fields)
									OR
									in_array($orig_column, $fields)
								)
								AND in_array( strtolower($order), $order_options)
							)
						) {
					//Make sure ';' does not appear in the resulting order string, to help prevent attacks in non-strict mode.
					if ( $strict == TRUE OR ( $strict == FALSE AND strpos( $orig_column, ';') === FALSE AND strpos( $order, ';') === FALSE ) ) {
						$sql_chunks[] = $orig_column.' '.$order;
					} else {
						Debug::text('ERROR: Found ";" in SQL order string: '. $orig_column .' Order: '. $order, __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::text('Invalid Sort Column/Order: '. $column .' Order: '. $order, __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			if ( isset($sql_chunks) ) {
				$sql = implode(',', $sql_chunks);

				return ' order by '. $this->db->escape( $sql );
			}
		}

		return FALSE;
	}

	public function getColumnList() {
		if ( is_array($this->data) AND count($this->data) > 0) {
			//Possible errors can happen if $this->data[<invalid_column>] is passed to save/update the database,
			//like what happens with APIPunch when attempting to delete a punch.

			//Remove all columns that are not directly part of the table itself, or those mapped not mapped to a function in the object.
			$variable_to_function_map = $this->getVariableToFunctionMap();
			if ( is_array( $variable_to_function_map ) ) {
				foreach( $variable_to_function_map as $variable => $function ) {
					if ( $function !== FALSE ) {
						$valid_column_list[] = $variable;
					}
				}
				$column_list = array_intersect( $valid_column_list, array_keys($this->data) );
			} else {
				$column_list = array_keys($this->data);
			}
			unset( $variable_to_function_map, $variable, $function );

			//Don't set updated_date when deleting records, we use deleted_date/deleted_by for that.
			if ( $this->getDeleted() == FALSE AND $this->setUpdatedDate() !== FALSE ) {
				$column_list[] = 'updated_date';
			}
			if ( $this->getDeleted() == FALSE AND $this->setUpdatedBy() !== FALSE ) {
				$column_list[] = 'updated_by';
			}
			//Make sure if the record is deleted we update the deleted columns.
			if ( $this->getDeleted() == TRUE AND $this->setDeletedDate() !== FALSE AND $this->setDeletedBy() !== FALSE ) {
				$column_list[] = 'deleted_date';
				$column_list[] = 'deleted_by';
			}

			$column_list = array_unique($column_list);

			//Debug::Arr($this->data, 'aColumn List', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($column_list, 'bColumn List', __FILE__, __LINE__, __METHOD__, 10);

			return $column_list;
		}

		return FALSE;
	}

	public function getEmptyRecordSet($id = NULL) {
		global $profiler, $config_vars;
		$profiler->startTimer( 'getEmptyRecordSet()' );

		if ($id == NULL) {
			$id = -1;
		}

		$id = (int)$id;

		//Possible errors can happen if $this->data[<invalid_column>] is passed, like what happens with APIPunch when attempting to delete a punch.
		//Why are we not using '*' for all empty record set queries? Will using * cause more fields to be updated then necessary?
		//Yes, it will, as well the updated_by/updated_date fields aren't controllable by getColumnList() then either.
		//Therefore any ListFactory queries used to potentially delete data should only include columns from its own table,
		//Or collect the IDs and use bulkDelete instead.
		//**getColumnList() now only returns valid table columns based on the variable to function map.
		$column_list = $this->getColumnList();

		//ignore_column_list can be set in InstallSchema files to prevent column names from being used which may cause SQL errors during upgrade process.
		if ( is_array($column_list) AND !isset($this->ignore_column_list) ) {
			//Implode columns.
			$column_str = implode(',', $column_list);
		} else {
			$column_str = '*'; //Get empty RS with all columns.
		}
		try {
			$query = 'select '. $column_str .' from '. $this->table .' where id = '. $id;
			if ( $id == -1 AND isset($config_vars['cache']['enable']) AND $config_vars['cache']['enable'] == TRUE ) {

				/*
				//Try to use Cache Lite instead of ADODB, to avoid cache write errors from causing a transaction rollback. It should be faster too.
				//However I think there is some issues with storing the record set, as ADODB goes to great lengths to avoid straight serialize/unserialize.
				$cache_id = 'empty_rs_'. $this->table .'_'. $id;
				$rs = $this->getCache($cache_id);
				if ( $rs === FALSE ) {
					$rs = $this->db->Execute($query);
					$this->saveCache($rs, $cache_id);
				}
				*/
				$save_error_handlers = $this->db->IgnoreErrors(); //Prevent a cache write error from causing a transaction rollback.
				try {
					$rs = $this->db->CacheExecute(604800, $query);
				} catch (Exception $e) {
					if ( $e->getCode() == -32000 OR $e->getCode() == -32001 ) { //Cache write error/cache file lock error.
						//Likely a cache write error occurred, fall back to non-cached query and log this error.
						Debug::Text('ERROR: Unable to write cache file, likely due to permissions or locking! Code: '. $e->getCode() .' Msg: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
					}

					//Execute non-cached query
					try {
						$rs = $this->db->Execute($query);
					} catch (Exception $e) {
						throw new DBError($e);
					}
				}
				$this->db->IgnoreErrors( $save_error_handlers ); //Prevent a cache write error from causing a transaction rollback.
			} else {
				$rs = $this->db->Execute($query);
			}
		} catch (Exception $e) {
			throw new DBError($e);
		}

		$profiler->stopTimer( 'getEmptyRecordSet()' );
		return $rs;
	}

	private function getUpdateQuery() {
		//Debug::text('Update', __FILE__, __LINE__, __METHOD__, 9);

		//
		// If the table has timestamp columns without timezone set
		// this function will think the data has changed, and update it.
		// PayStubFactory() had this issue.
		//

		//Debug::arr($this->data, 'Data Arr', __FILE__, __LINE__, __METHOD__, 10);

		//Add new columns to record set.
		//Check to make sure the columns exist in the table first though
		//Classes like station don't have updated_date, so we need to take that in to account.
		try {
			$rs = $this->getEmptyRecordSet( $this->getId() );
			$this->old_data = $rs->fields; //Store old data in memory for detailed audit log.
		} catch (Exception $e) {
			throw new DBError($e);
		}
		if (!$rs) {
			Debug::text('No Record Found! (ID: '. $this->getID() .' Insert instead?', __FILE__, __LINE__, __METHOD__, 9);
			//Throw exception?
		}

		//Debug::Arr($rs->fields, 'RecordSet: ', __FILE__, __LINE__, __METHOD__, 9);
		//Debug::Arr($this->data, 'Data Array: ', __FILE__, __LINE__, __METHOD__, 9);
		//Debug::Arr( array_diff_assoc($rs->fields, $this->data), 'Data Array: ', __FILE__, __LINE__, __METHOD__, 9);

		//If no columns changed, this will be FALSE.
		$query = $this->db->GetUpdateSQL($rs, $this->data);

		//No updates are fine. We still want to run postsave() etc...
		if ($query === FALSE) {
			$query = TRUE;
		} else {
			Debug::text('Data changed, set updated date: ', __FILE__, __LINE__, __METHOD__, 9);
		}

		//Debug::text('Update Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

		return $query;
	}

	private function getInsertQuery() {
		Debug::text('Insert', __FILE__, __LINE__, __METHOD__, 9);

		//Debug::arr($this->data, 'Data Arr', __FILE__, __LINE__, __METHOD__, 10);

		try {
			$rs = $this->getEmptyRecordSet();
		} catch (Exception $e) {
			throw new DBError($e);
		}

		$table = $this->getTable(); //STRICT warning from v5.4

		//Use table name instead of recordset, especially when using CacheLite for caching empty recordsets.
		//$query = $this->db->GetInsertSQL($rs, $this->data);
		$query = $this->db->GetInsertSQL($table, $this->data);

		//Debug::text('Insert Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

		return $query;
	}

	function StartTransaction() {
		Debug::text('StartTransaction(): Transaction Count: '. $this->db->transCnt .' Trans Off: '. $this->db->transOff, __FILE__, __LINE__, __METHOD__, 9);
		return $this->db->StartTrans();
	}

	function FailTransaction() {
		Debug::text('FailTransaction(): Transaction Count: '. $this->db->transCnt .' Trans Off: '. $this->db->transOff, __FILE__, __LINE__, __METHOD__, 9);
		return $this->db->FailTrans();
	}

	function CommitTransaction() {
		Debug::text('CommitTransaction(): Transaction Count: '. $this->db->transCnt .' Trans Off: '. $this->db->transOff, __FILE__, __LINE__, __METHOD__, 9);
		$retval = $this->db->CompleteTrans();

		if ( $retval == FALSE ) { //Check to see if the transaction has failed.
			//In PostgreSQL, when SESSION/LOCAL variables are set within a transaction that later rollsback, the session variables also rollback. This ensures the timezone still matches what we think it should.
			TTDate::setTimeZone( TTDate::getTimeZone(), TRUE );
		}

		return $retval;
	}

	//Call class specific validation function just before saving.
	function isValid() {
		if ( method_exists($this, 'Validate') ) {
			Debug::text('Calling Validate()', __FILE__, __LINE__, __METHOD__, 10);
			$this->Validate();
		}

		return $this->Validator->isValid();
	}

	function getSequenceName() {
		if ( isset($this->pk_sequence_name) ) {
			return $this->pk_sequence_name;
		}

		return FALSE;
	}
	function getNextInsertId() {
		if ( isset($this->pk_sequence_name) ) {
			return $this->db->GenID( $this->pk_sequence_name );
		}
		
		return FALSE;
	}

	//Execute SQL queries and handle paging properly for select statements.
	function ExecuteSQL( $query, $ph = NULL, $limit = NULL, $page = NULL ) {
		try {
			//$start_time = microtime(TRUE);
			if ($limit == NULL) {
				$this->rs = $this->db->Execute($query, $ph);
			} else {
				$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
			}
			//$total_time = (microtime(TRUE)-$start_time);
			//Debug::text('Slow Query Executed in: '. $total_time .'ms. Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	//Determines to insert or update, and does it.
	//Have this handle created, createdby, updated, updatedby.
	function Save($reset_data = TRUE, $force_lookup = FALSE) {
		$this->StartTransaction();

		//Run Pre-Save function
		//This is called before validate so it can do extra calculations, etc before validation.
		//Should this AND validate() NOT be called when delete flag is set?
		if ( method_exists($this, 'preSave') ) {
			Debug::text('Calling preSave()', __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->preSave() === FALSE ) {
				throw new GeneralError('preSave() failed.');
			}
		}

		//Don't validate when deleting, so we can delete records that may have some invalid options.
		//However we can still manually call this function to check if we need too.
		if ( $this->getDeleted() == FALSE AND $this->isValid() === FALSE ) {
			throw new GeneralError('Invalid Data, not saving.');
		}

		//Should we insert, or update?
		if ( $this->isNew( $force_lookup ) ) {
			//Insert
			$time = TTDate::getTime();

			if ( $this->getCreatedDate() == '' ) {
				$this->setCreatedDate($time);
			}
			if ( $this->getCreatedBy() == '' ) {
				$this->setCreatedBy();
			}

			//Set updated date at the same time, so we can easily select last
			//updated, or last created records.
			$this->setUpdatedDate($time);
			$this->setUpdatedBy();

			unset($time);

			$insert_id = $this->getID();
			if ( $insert_id == FALSE ) {
				//Append insert ID to data array.
				$insert_id = $this->getNextInsertId();
				if ( $insert_id === 0 ) { //Sometimes with MYSQL the _seq tables might not be initialized properly and cause insert_id=0.
					throw new DBError('ERROR: Insert ID returned as 0, sequence likely not setup correctly.');
				} else {
					Debug::text('Insert ID: '. $insert_id .' Table: '. $this->getTable(), __FILE__, __LINE__, __METHOD__, 9);
					$this->setId($insert_id);
				}
			}

			try {
				$query = $this->getInsertQuery();
			} catch (Exception $e) {
				throw new DBError($e);
			}
			$retval = (int)$insert_id;
			$log_action = 10; //'Add';
		} else {
			Debug::text(' Updating...', __FILE__, __LINE__, __METHOD__, 10);

			//Update
			$query = $this->getUpdateQuery(); //Don't pass data, too slow

			//Debug::Arr($this->data, 'Save(): Query: ', __FILE__, __LINE__, __METHOD__, 10);
			$retval = TRUE;

			if ( $this->getDeleted() === TRUE ) {
				$log_action = 30; //'Delete';
			} else {
				$log_action = 20; //'Edit';
			}
		}

		//Debug::text('Save(): Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($query, 'Save(): Query: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $query != '' OR $query === TRUE ) {

			if ( is_string($query) AND $query != '' ) {
				try {
					$this->db->Execute($query);
				} catch (Exception $e) {
					//Comment this out to see some errors on MySQL.
					throw new DBError($e);
				}
			}

			if ( method_exists($this, 'addLog') ) {
				//In some cases, like deleting users, this function will fail because the user is deleted before they are removed from other
				//tables like PayPeriodSchedule, so addLog() can't get the user information.
				global $config_vars;
				if ( !isset($config_vars['other']['disable_audit_log']) OR $config_vars['other']['disable_audit_log'] != TRUE ) {
					$this->addLog( $log_action );
				}
			}

			//Run postSave function.
			if ( method_exists($this, 'postSave') ) {
				Debug::text('Calling postSave()', __FILE__, __LINE__, __METHOD__, 10);
				if ( $this->postSave( array_diff_assoc( (array)$this->old_data, (array)$this->data ) ) === FALSE ) {
					throw new GeneralError('postSave() failed.');
				}
			}

			//Clear the data.
			if ( $reset_data == TRUE ) {
				$this->clearData();
			}
			//IF YOUR NOT RESETTING THE DATA, BE SURE TO CLEAR THE OBJECT MANUALLY
			//IF ITS IN A LOOP!! VERY IMPORTANT!

			$this->CommitTransaction();

			//Debug::Arr($retval, 'Save Retval: ', __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}

		Debug::text('Save(): returning FALSE! Very BAD!', __FILE__, __LINE__, __METHOD__, 10);

		throw new GeneralError('Save(): failed.');

		return FALSE; //This should return false here?
	}

	function Delete() {
		Debug::text('Delete: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 9);

		if ( $this->getId() !== FALSE ) {
			$ph = array(
						'id' => $this->getId(),
						);

			$query = 'DELETE FROM '. $this->getTable() .' WHERE id = ?';

			try {
				$this->db->Execute($query, $ph);

				if ( method_exists($this, 'addLog') ) {
					//In some cases, like deleting users, this function will fail because the user is deleted before they are removed from other
					//tables like PayPeriodSchedule, so addLog() can't get the user information.
					$this->addLog( 31 );
				}

			} catch (Exception $e) {
				throw new DBError($e);
			}

			return TRUE;
		}

		return FALSE;
	}

	function getIDSByListFactory( $lf ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		foreach( $lf as $lf_obj ) {
			$retarr[] = $lf_obj->getID();
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	function bulkDelete( $ids ) {
		//Debug::text('Delete: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 9);

		//Make SURE you get the right table when calling this.
		if ( is_array($ids) AND count($ids) > 0 ) {
			$ph = array();

			$query = 'DELETE FROM '. $this->getTable() .' WHERE id in ('. $this->getListSQL( $ids, $ph, 'int' ) .')';
			Debug::text('Bulk Delete Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

			try {
				$this->db->Execute($query, $ph);
			} catch (Exception $e) {
				throw new DBError($e);
			}

			return TRUE;
		}

		return FALSE;
	}

	function clearData() {
		$this->data = $this->tmp_data = $this->old_data = array();
		$this->next_insert_id = NULL;

		return TRUE;
	}

	final function getIterator() {
		return new FactoryListIterator($this);
	}

	//Grabs the current object
	final function getCurrent() {
		return $this->getIterator()->current();
	}
}
?>
