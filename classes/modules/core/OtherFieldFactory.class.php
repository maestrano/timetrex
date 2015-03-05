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
class OtherFieldFactory extends Factory {
	protected $table = 'other_field';
	protected $pk_sequence_name = 'other_field_id_seq'; //PK Sequence name

	protected $company_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
											2  => TTi18n::gettext('Company'),
											4  => TTi18n::gettext('Branch'),
											5  => TTi18n::gettext('Department'),
											10	=> TTi18n::gettext('Employee'),
											12	=> TTi18n::gettext('Employee Title'),
											15	=> TTi18n::gettext('Punch'),
											20	=> TTi18n::gettext('Job'),
											30	=> TTi18n::gettext('Task'),
											50	=> TTi18n::gettext('Client'),
											55	=> TTi18n::gettext('Client Contact'),
											//57  => TTi18n::gettext('Client Payment'),
											60	=> TTi18n::gettext('Product'),
											70	=> TTi18n::gettext('Invoice'),
											80	=> TTi18n::gettext('Document'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => TTi18n::gettext('Type'),
										'-1021-other_id1' => TTi18n::gettext('Other ID1'),
										'-1022-other_id2' => TTi18n::gettext('Other ID2'),
										'-1023-other_id3' => TTi18n::gettext('Other ID3'),
										'-1024-other_id4' => TTi18n::gettext('Other ID4'),
										'-1025-other_id5' => TTi18n::gettext('Other ID5'),

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
								'type_id', //Required by Flex when a supervisor logs in to handle other fields properly.
								'type',
								'other_id1',
								'other_id2',
								'other_id3',
								'other_id4',
								'other_id5',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
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
										'type_id' => 'Type',
										'type' => FALSE,
										'other_id1' => 'OtherID1',
										'other_id2' => 'OtherID2',
										'other_id3' => 'OtherID3',
										'other_id4' => 'OtherID4',
										'other_id5' => 'OtherID5',
										'other_id6' => 'OtherID6',
										'other_id7' => 'OtherID7',
										'other_id8' => 'OtherID8',
										'other_id9' => 'OtherID9',
										'other_id10' => 'OtherID10',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = TTnew( 'CompanyListFactory' );
			$this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

			return $this->company_obj;
		}
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueType($type) {
		$ph = array(
					'company_id' => (int)$this->getCompany(),
					'type_id' => (int)$type,
					);

		$query = 'select id from '. $this->getTable() .'
					where company_id = ?
						AND type_id = ?
						AND deleted = 0';
		$type_id = $this->db->GetOne($query, $ph);
		Debug::Arr($type_id, 'Unique Type: '. $type, __FILE__, __LINE__, __METHOD__, 10);

		if ( $type_id === FALSE ) {
			return TRUE;
		} else {
			if ( $type_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return (int)$this->data['type_id'];
		}

		return FALSE;
	}
	function setType($type) {
		$type = trim($type);
		Debug::text('Attempting to set Type To: '. $type, __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->Validator->inArrayKey(	'type_id',
											$type,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type') )
					AND
						$this->Validator->isTrue(		'type_id',
														$this->isUniqueType($type),
														TTi18n::gettext('Type already exists'))

											) {

			$this->data['type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID1() {
		return $this->data['other_id1'];
	}
	function setOtherID1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id1',
											$value,
											TTi18n::gettext('Other ID1 is invalid'),
											1, 255) ) {

			$this->data['other_id1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID2() {
		return $this->data['other_id2'];
	}
	function setOtherID2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id2',
											$value,
											TTi18n::gettext('Other ID2 is invalid'),
											1, 255) ) {

			$this->data['other_id2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID3() {
		return $this->data['other_id3'];
	}
	function setOtherID3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id3',
											$value,
											TTi18n::gettext('Other ID3 is invalid'),
											1, 255) ) {

			$this->data['other_id3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID4() {
		return $this->data['other_id4'];
	}
	function setOtherID4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id4',
											$value,
											TTi18n::gettext('Other ID4 is invalid'),
											1, 255) ) {

			$this->data['other_id4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID5() {
		return $this->data['other_id5'];
	}
	function setOtherID5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id5',
											$value,
											TTi18n::gettext('Other ID5 is invalid'),
											1, 255) ) {

			$this->data['other_id5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID6() {
		return $this->data['other_id6'];
	}
	function setOtherID6($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id6',
											$value,
											TTi18n::gettext('Other ID6 is invalid'),
											1, 255) ) {

			$this->data['other_id6'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID7() {
		return $this->data['other_id7'];
	}
	function setOtherID7($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id7',
											$value,
											TTi18n::gettext('Other ID7 is invalid'),
											1, 255) ) {

			$this->data['other_id7'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID8() {
		return $this->data['other_id8'];
	}
	function setOtherID8($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id8',
											$value,
											TTi18n::gettext('Other ID8 is invalid'),
											1, 255) ) {

			$this->data['other_id8'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID9() {
		return $this->data['other_id9'];
	}
	function setOtherID9($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id9',
											$value,
											TTi18n::gettext('Other ID9 is invalid'),
											1, 255) ) {

			$this->data['other_id9'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID10() {
		return $this->data['other_id10'];
	}
	function setOtherID10($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id10',
											$value,
											TTi18n::gettext('Other ID10 is invalid'),
											1, 255) ) {

			$this->data['other_id10'] = $value;

			return TRUE;
		}

		return FALSE;
	}

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
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Other Fields'), NULL, $this->getTable(), $this );
	}
}
?>
