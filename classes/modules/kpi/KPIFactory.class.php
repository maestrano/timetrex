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
 * @package Modules\KPI
 */
class KPIFactory extends Factory
{
	protected $table = 'kpi';
	protected $pk_sequence_name = 'kpi_id_seq'; //PK Sequence name
	protected $tmp_data = NULL;
	protected $company_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch ( $name ) {
			case 'status':
				$retval = array( 10 => TTi18n::gettext( 'Enabled (Required)' ), 15 => TTi18n::gettext( 'Enabled (Optional)' ), 20 => TTi18n::gettext( 'Disabled' ), );
				break;
			case 'type':
				$retval = array( 10 => TTi18n::gettext( 'Scale Rating' ), 20 => TTi18n::gettext( 'Yes/No' ), 30 => TTi18n::gettext( 'Text' ), );
				break;
			case 'columns':
				$retval = array( '-1000-name' => TTi18n::gettext( 'Name' ), //'-2040-group' => TTi18n::gettext('Group'),
					'-1040-description' => TTi18n::gettext( 'Description' ), '-1050-type' => TTi18n::getText( 'Type' ), '-4050-minimum_rate' => TTi18n::gettext( 'Minimum Rating' ), '-4060-maximum_rate' => TTi18n::gettext( 'Maximum Rating' ), '-1010-status' => TTi18n::gettext( 'Status' ), '-1300-tag' => TTi18n::gettext( 'Tags' ), '-2000-created_by' => TTi18n::gettext( 'Created By' ), '-2010-created_date' => TTi18n::gettext( 'Created Date' ), '-2020-updated_by' => TTi18n::gettext( 'Updated By' ), '-2030-updated_date' => TTi18n::gettext( 'Updated Date' ), );
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array( 'name', //'group',
					'description', 'type', 'minimum_rate', 'maximum_rate', );
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array( 'name', );
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {

		$variable_function_map = array( 'id' => 'ID', 'company_id' => 'Company', 'name' => 'Name', 'group_id' => 'Group', //'group' => FALSE,
			'type_id' => 'Type', 'type' => FALSE, 'tag' => 'Tag', 'description' => 'Description', 'minimum_rate' => 'MinimumRate', 'maximum_rate' => 'MaximumRate', 'status_id' => 'Status', 'status' => FALSE, 'deleted' => 'Deleted', );

		return $variable_function_map;
	}

	function getCompanyObject() {

		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	function getCompany() {

		if ( isset( $this->data['company_id'] ) ) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
	}

	function setCompany( $id ) {

		$id = trim( $id );
		Debug::Text( 'Company ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		$clf = TTnew( 'CompanyListFactory' );
		if ( $this->Validator->isResultSetWithRows( 'company', $clf->getByID( $id ), TTi18n::gettext( 'Company is invalid' ) ) ) {
			$this->data['company_id'] = $id;
			Debug::Text( 'Setting company_id data...	   ' . $this->data['company_id'], __FILE__, __LINE__, __METHOD__, 10 );

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {

		if ( isset( $this->data['status_id'] ) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
	}

	function setStatus( $status ) {

		$status = trim( $status );
		$key = Option::getByValue( $status, $this->getOptions( 'status' ) );
		if ( $key !== FALSE ) {
			$status = $key;
		}
		if ( $this->Validator->inArrayKey( 'status', $status, TTi18n::gettext( 'Incorrect Status' ), $this->getOptions( 'status' ) ) ) {
			$this->data['status_id'] = $status;
			Debug::Text( 'Setting status_id data...	  ' . $this->data['status_id'], __FILE__, __LINE__, __METHOD__, 10 );

			return TRUE;
		}

		return FALSE;
	}

	function getType() {

		if ( isset( $this->data['type_id'] ) ) {
			return (int)$this->data['type_id'];
		}

		return FALSE;
	}

	function setType( $type_id ) {

		$type_id = trim( $type_id );
		if ( $this->Validator->inArrayKey( 'type_id', $type_id, TTi18n::gettext( 'Type is invalid' ), $this->getOptions( 'type' ) ) ) {
			$this->data['type_id'] = $type_id;

			return FALSE;
		}

		return FALSE;
	}

	function isUniqueName( $name ) {

		$ph = array( 'company_id' => $this->getCompany(), 'name' => trim( strtolower( $name ) ), );
		$query = 'select id from ' . $this->table . '
					where company_id = ?
						AND name = ?
						AND deleted = 0';
		$name_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $name_id === FALSE ) {
			return TRUE;
		}
		else {
			if ( $name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getName() {

		if ( isset( $this->data['name'] ) ) {
			return $this->data['name'];
		}

		return FALSE;
	}

	function setName( $name ) {

		$name = trim( $name );
		if ( $this->Validator->isLength( 'name', $name, TTi18n::gettext( 'Name is too long, consider using description instead' ), 3, 100 )
			AND
			$this->Validator->isTrue( 'name', $this->isUniqueName( $name ), TTi18n::gettext( 'Name is already taken' ) )
		) {
			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getGroup() {

		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 2020, $this->getID() );
	}

	function setGroup( $ids ) {

		Debug::text( 'Setting Groups IDs : ', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $ids, 'Setting Group data... ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 2020, $this->getID(), $ids );
	}


	function getDescription() {

		if ( isset( $this->data['description'] ) ) {
			return $this->data['description'];
		}

		return FALSE;
	}

	function setDescription( $description ) {

		$description = trim( $description );
		if ( $this->Validator->isLength( 'description', $description, TTi18n::gettext( 'Description is invalid' ), 0, 255 ) ) {
			$this->data['description'] = $description;
			Debug::Text( 'Setting description data...	' . $this->data['description'], __FILE__, __LINE__, __METHOD__, 10 );

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumRate() {

		if ( isset( $this->data['minimum_rate'] ) ) {
			return Misc::removeTrailingZeros( $this->data['minimum_rate'], 2 );
		}

		return FALSE;
	}

	function setMinimumRate( $value ) {

		$value = trim( $value );
		$value = $this->Validator->stripNonFloat( $value );
		if ( ( $this->getType() == 10 ) AND ( $this->Validator->isLength( 'minimum_rate', $value, TTi18n::gettext( 'Invalid  Minimum Rating' ), 1 )
				AND
				( $this->Validator->isNumeric( 'minimum_rate', $value, TTi18n::gettext( 'Minimum Rating must only be digits' ) )
					AND
					$this->Validator->isLengthAfterDecimal( 'minimum_rate', $value, TTi18n::gettext( 'Invalid	 Minimum Rating ' ), 0, 2 ) ) )
		) {
			$this->data['minimum_rate'] = $value;
			Debug::Text( 'Setting minimum_rate data...	 ' . $this->data['minimum_rate'], __FILE__, __LINE__, __METHOD__, 10 );

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumRate() {
		if ( isset( $this->data['maximum_rate'] ) ) {
			return Misc::removeTrailingZeros( $this->data['maximum_rate'], 2 );
		}

		return FALSE;
	}

	function setMaximumRate( $value ) {
		$value = trim( $value );
		$value = $this->Validator->stripNonFloat( $value );
		if ( ( $this->getType() == 10 ) AND ( $this->Validator->isLength( 'maximum_rate', $value, TTi18n::gettext( 'Invalid Maximum Rating' ), 1 )
				AND
				( $this->Validator->isNumeric( 'maximum_rate', $value, TTi18n::gettext( 'Maximum Rating must only be digits' ) )
					AND
					$this->Validator->isLengthAfterDecimal( 'maximum_rate', $value, TTi18n::gettext( 'Invalid Maximum Rating' ), 0, 2 ) ) )
		) {
			$this->data['maximum_rate'] = $value;
			Debug::Text( 'Setting maximum_rate data...'. $this->data['maximum_rate'], __FILE__, __LINE__, __METHOD__, 10 );

			return TRUE;
		}

		return FALSE;
	}

	function getTag() {

		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset( $this->tmp_data['tags'] ) ) {
			return $this->tmp_data['tags'];
		}
		elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 310, $this->getID() );
		}

		return FALSE;
	}

	function setTag( $tags ) {

		$tags = trim( $tags );
		//Save the tags in temporary memory to be committed in postSave()
		$this->tmp_data['tags'] = $tags;

		return TRUE;
	}


	function Validate() {

		if ( $this->getType() == 10 AND $this->getMinimumRate() != '' AND $this->getMaximumRate() != '' ) {
			if ( $this->getMinimumRate() >= $this->getMaximumRate() ) {
				$this->Validator->isTrue( 'minimum_rate', FALSE, TTi18n::gettext( 'Minimum Rating should be lesser than Maximum Rating' ) );
			}
		}
		if ( $this->getDeleted() == TRUE ) {
			$urlf = TTnew( 'UserReviewListFactory' );
			$urlf->getByKpiId( $this->getId() );
			if ( $urlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use', FALSE, TTi18n::gettext( 'KPI is in use' ) );

			}
		}

		return TRUE;
	}

	function preSave() {

		return TRUE;
	}

	function postSave() {

		$this->removeCache( $this->getId() );
		if ( $this->getDeleted() == FALSE ) {
			Debug::text( 'Setting Tags...', __FILE__, __LINE__, __METHOD__, 10 );
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 310, $this->getID(), $this->getTag() );
		}

		return TRUE;
	}

	//Support setting created_by, updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {

		Debug::Arr( $data, 'setObjectFromArray...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {
					$function = 'set' . $function;
					switch ( $key ) {
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset( $include_columns[$variable] ) AND $include_columns[$variable] == TRUE ) ) {
					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'type':
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						/*case 'group':
							if ( $this->getColumn( 'map_id' ) == -1 ) {
								$data[$variable] = 'All';
							} else {
								$data[$variable] = $this->getColumn( $variable );
							}
							break;*/
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getCreatedBy(), FALSE, $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {

		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'KPI' ), NULL, $this->getTable(), $this );
	}
}

?>
