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
 * $Revision: 8371 $
 * $Id: InstallSchema.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package Modules\Install
 */
class InstallSchema extends Install {

	protected $schema_version = NULL;
	protected $obj = NULL;

	function __construct( $database_type, $version, $db_conn, $is_upgrade = FALSE ) {
		Debug::text('Database Type: '. $database_type .' Version: '. $version, __FILE__, __LINE__, __METHOD__, 10);
		$this->database_type = $database_type;
		$this->schema_version = $version;

		if ( $database_type == '' ) {
			return FALSE;
		}

		if ( $version == '' ) {
			return FALSE;
		}

		$schema_class_file_name = Environment::getBasePath() . DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'install'. DIRECTORY_SEPARATOR .'InstallSchema_'. $version .'.class.php';
		$schema_sql_file_name = $this->getSchemaSQLFilename( $version );
		if ( file_exists($schema_class_file_name)
				AND file_exists($schema_sql_file_name ) ) {

			include_once( $schema_class_file_name );

			$class_name = 'InstallSchema_'. $version;

			$this->obj = new $class_name;
			$this->obj->setDatabaseConnection( $db_conn );
			$this->obj->setIsUpgrade( $is_upgrade );
			$this->obj->setVersion( $version );
			$this->obj->setSchemaSQLFilename( $this->getSchemaSQLFilename() );

			return TRUE;
		} else {
			Debug::text('Schema Install Class File DOES NOT Exists - File Name: '. $schema_class_file_name .' Schema SQL File: '. $schema_sql_file_name, __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	function getSQLFileDirectory() {
		return Environment::getBasePath() . DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'install'. DIRECTORY_SEPARATOR .'sql'. DIRECTORY_SEPARATOR . $this->database_type . DIRECTORY_SEPARATOR;
	}

	function getSchemaSQLFilename() {
		return $this->getSQLFileDirectory() . $this->schema_version .'.sql';
	}

	//load Schema file data
	function getSchemaSQLFileData() {

	}

	private function getObject() {
		if ( is_object($this->obj) ) {
			return $this->obj;
		}

		return FALSE;
	}

	function __call($function_name, $args = array() ) {
		if ( $this->getObject() !== FALSE ) {
			//Debug::text('Calling Sub-Class Function: '. $function_name, __FILE__, __LINE__, __METHOD__, 10);
			if ( is_callable( array($this->getObject(), $function_name) ) ) {
				$return = call_user_func_array(array($this->getObject(), $function_name), $args);

				return $return;
			}
		}

		Debug::text('Sub-Class Function Call FAILED!:'. $function_name, __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

}
?>
