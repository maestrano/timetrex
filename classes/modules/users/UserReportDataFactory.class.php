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
 * $Revision: 3061 $
 * $Id: UserGenericDataFactory.class.php 3061 2009-11-13 17:38:19Z ipso $
 * $Date: 2009-11-13 09:38:19 -0800 (Fri, 13 Nov 2009) $
 */

/**
 * @package Modules\Users
 */
class UserReportDataFactory extends Factory {
	protected $table = 'user_report_data';
	protected $pk_sequence_name = 'user_report_data_id_seq'; //PK Sequence name

	protected $user_obj = NULL;
	protected $obj_handler = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-name' => TTi18n::gettext('Name'),
										'-1020-description' => TTi18n::gettext('Description'),
										'-1030-script_name' => TTi18n::gettext('Report'),
										'-1040-is_default' => TTi18n::gettext('Default'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'script_name',
								'description',
								'is_default',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								'description',
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'user_id' => 'User',
										'script' => 'Script',
										'script_name' => FALSE,
										'name' => 'Name',
										'is_default' => 'Default',
										'description' => 'Description',
										'data' => 'Data',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getObjectHandler() {
		if ( is_object($this->obj_handler) ) {
			return $this->obj_handler;
		} else {
			$class = $this->getScript();
			if ( class_exists( $class, TRUE ) ) {
				$this->obj_handler = new $class();
				return $this->obj_handler;
			}

			return FALSE;
		}
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(			'company',
															$clf->getByID($id),
															TTi18n::gettext('Invalid Company')
															) ) {
			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getScript() {
		if ( isset($this->data['script']) ) {
			return $this->data['script'];
		}

		return FALSE;
	}
	function setScript($value) {
		//Strip out double slashes, as sometimes those occur and they cause the saved settings to not appear.
		$value = self::handleScriptName( trim($value) );
		if (	$this->Validator->isLength(	'script',
											$value,
											TTi18n::gettext('Invalid script'),
											1,250)
						) {

			$this->data['script'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		//Allow no user_id to be set yet, as that would be company generic data.

		if ( $this->getScript() == FALSE ) {
			return FALSE;
		}

		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $this->getCompany(),
					'script' => $this->getScript(),
					'name' => strtolower( $name ),
					);

		$query = 'select id from '. $this->getTable() .'
					where
						company_id = ?
						AND script = ?
						AND lower(name) = ? ';
		if (  $this->getUser() != '' ) {
			$query .= ' AND user_id = '. (int)$this->getUser();
		} else {
			$query .= ' AND user_id is NULL ';
		}

		$query .= ' AND deleted = 0';
		$name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($name_id,'Unique Name: '. $name , __FILE__, __LINE__, __METHOD__,10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name) {
		$name = trim($name);
		if (	$this->Validator->isLength(	'name',
											$name,
											TTi18n::gettext('Invalid name'),
											1,100)
				AND
				$this->Validator->isTrue(		'name',
												$this->isUniqueName($name),
												TTi18n::gettext('Name already exists'))

						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getDefault() {
		if ( isset($this->data['is_default']) ) {
			return $this->fromBool( $this->data['is_default'] );
		}

		return FALSE;
	}
	function setDefault($bool) {
		$this->data['is_default'] = $this->toBool($bool);

		return TRUE;
	}

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($description) {
		$description = trim($description);

		if (	$this->Validator->isLength(	'description',
											$description,
											TTi18n::gettext('Description is invalid'),
											0,1024) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function getData() {
		return unserialize( $this->data['data'] );
	}
	function setData($value) {
		$value = serialize($value);

		$this->data['data'] = $value;

		return TRUE;
	}

	function Validate() {
		if ( $this->getName() == '' ) {
			$this->Validator->isTRUE(	'name',
										FALSE,
										TTi18n::gettext('Invalid name'));
		}

		return TRUE;
	}
	function preSave() {
		if ( $this->getDefault() == TRUE ) {
			//Remove default flag from all other entries.
			$urdlf = TTnew( 'UserReportDataListFactory' );
			if ( $this->getUser() == FALSE ) {
				$urdlf->getByCompanyIdAndScriptAndDefault( $this->getUser(), $this->getScript(), TRUE );
			} else {
				$urdlf->getByUserIdAndScriptAndDefault( $this->getUser(), $this->getScript(), TRUE );
			}
			if ( $urdlf->getRecordCount() > 0 ) {
				foreach( $urdlf as $urd_obj ) {
					Debug::Text('Removing Default Flag From: '. $urd_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					$urd_obj->setDefault(FALSE);
					if ( $urd_obj->isValid() ) {
						$urd_obj->Save();
					}
				}
			}
		}

		return TRUE;
	}

	static function handleScriptName( $script_name ) {
		return str_replace('//', '/', $script_name);
	}

	//Support setting created_by,updated_by especially for importing data.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'script_name':
							$report_obj = $this->getObjectHandler();
							if ( is_object($report_obj ) ) {
								$data[$variable] = $report_obj->title;
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		if ( $this->getUser() == FALSE AND $this->getDefault() == TRUE ) {
			//Bypass logging on Company Default Save.
			return TRUE;
		}

		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Saved Report Data'), NULL, $this->getTable() );
	}
}
?>
