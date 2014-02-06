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
 * $Revision: 10068 $
 * $Id: InstallSchema_Base.class.php 10068 2013-05-31 00:36:18Z ipso $
 * $Date: 2013-05-30 17:36:18 -0700 (Thu, 30 May 2013) $
 */

/**
 * @package Modules\Install
 */
class InstallSchema_Base {

	protected $schema_sql_file_name = NULL;
	protected $version = NULL;
	protected $db = NULL;
	protected $is_upgrade = FALSE;

	function setDatabaseConnection( $db ) {
		$this->db = $db;
	}

	function getDatabaseConnection() {
		return $this->db;
	}

	function setIsUpgrade( $val ) {
		$this->is_upgrade = (bool)$val;
	}
	function getIsUpgrade() {
		return $this->is_upgrade;
	}

	function setVersion($value) {
		$this->version = $value;
	}
	function getVersion() {
		return $this->version;
	}

	function setSchemaSQLFilename($file_name) {
		$this->schema_sql_file_name = $file_name;
	}
	function getSchemaSQLFilename() {
		return $this->schema_sql_file_name;
	}

	function getSchemaGroup() {
		$schema_group = substr( $this->getVersion(), -1,1 );
		Debug::text('Schema: '. $this->getVersion() .' Group: '. $schema_group, __FILE__, __LINE__, __METHOD__,9);

		return strtoupper($schema_group);
	}

	//Copied from Install class.
	function checkTableExists( $table_name ) {
		Debug::text('Table Name: '. $table_name, __FILE__, __LINE__, __METHOD__,9);
		$db_conn = $this->getDatabaseConnection();

		if ( $db_conn == FALSE ) {
			return FALSE;
		}

		$table_arr = $db_conn->MetaTables();

		if ( in_array($table_name, $table_arr ) ) {
			Debug::text('Exists - Table Name: '. $table_name, __FILE__, __LINE__, __METHOD__,9);
			return TRUE;
		}

		Debug::text('Does not Exist - Table Name: '. $table_name, __FILE__, __LINE__, __METHOD__,9);
		return FALSE;
	}

	//load Schema file data
	function getSchemaSQLFileData() {
		//Read SQL data into memory
		if ( is_readable( $this->getSchemaSQLFilename() ) ) {
			Debug::text('Schema SQL File is readable: '. $this->getSchemaSQLFilename(), __FILE__, __LINE__, __METHOD__,9);
			$contents = file_get_contents( $this->getSchemaSQLFilename() );

			Debug::Arr($contents, 'SQL File Data: ', __FILE__, __LINE__, __METHOD__,9);
			return $contents;
		}

		Debug::text('Schema SQL File is NOT readable, or is empty!', __FILE__, __LINE__, __METHOD__,9);

		return FALSE;
	}

	private function _InstallSchema() {
		//Run the actual SQL queries here

		$sql = $this->getSchemaSQLFileData();
		if ( $sql == FALSE ) {
			return FALSE;
		}

		if ( $sql !== FALSE AND strlen($sql) > 0 ) {
			Debug::text('Schema SQL has data, executing commands!', __FILE__, __LINE__, __METHOD__,9);

			//Split into individual SQL queries, as MySQL apparently doesn't like more then one query
			//in a single query() call.
			$split_sql = explode(';', $sql);
			if ( is_array($split_sql) ) {

				foreach( $split_sql as $sql_line ) {
				
					if ( trim($sql_line) != '' ) {

						$retval = $this->getDatabaseConnection()->Execute( $sql_line );

						if ( $retval == FALSE ) {
							return FALSE;
						}
					}
				}
			}
		}

		Debug::text('Schema SQL does not have data, not executing commands, continuing...', __FILE__, __LINE__, __METHOD__,9);
		return TRUE;
	}

	private function _postPostInstall() {
		Debug::text('Modify Schema version in system settings table!', __FILE__, __LINE__, __METHOD__,9);
		//Modify schema version in system_settings table.

		$sslf = TTnew( 'SystemSettingListFactory' );
		$sslf->getByName('schema_version_group_'. $this->getSchemaGroup() );
		if ( $sslf->getRecordCount() == 1 ) {
			$obj = $sslf->getCurrent();
		} else {
			$obj = TTnew( 'SystemSettingListFactory' );
		}

		$obj->setName( 'schema_version_group_'. $this->getSchemaGroup() );
		$obj->setValue( $this->getVersion() );
		if ( $obj->isValid() ) {
			Debug::text('Setting Schema Version to: '. $this->getVersion() .' Group: '. $this->getSchemaGroup() , __FILE__, __LINE__, __METHOD__,9);
			$obj->Save();

			return TRUE;
		}

		return FALSE;
	}

	function InstallSchema() {

		$this->getDatabaseConnection()->StartTrans();

		Debug::text('Installing Schema Version: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);
		if ( $this->preInstall() == TRUE ) {
			if ( $this->_InstallSchema() == TRUE ) {
				if ( $this->postInstall() == TRUE ) {
					$retval = $this->_postPostInstall();
					if ( $retval == TRUE ) {

						$this->getDatabaseConnection()->CompleteTrans();

						return $retval;
					}

				}
			}
		}

		$this->getDatabaseConnection()->FailTrans();

		return FALSE;
	}
}
?>
