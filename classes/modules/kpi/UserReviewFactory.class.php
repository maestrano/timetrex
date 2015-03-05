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
class UserReviewFactory extends Factory {
	protected $table = 'user_review';
	protected $pk_sequence_name = 'user_review_id_seq'; //PK Sequence name
	protected $kpi_obj = NULL;
	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-2050-rating' => TTi18n::gettext('Rating'),
										'-1200-note' => TTi18n::gettext('Note'),
										'-1300-tag' => TTi18n::gettext('Tags'),
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
								'rating',
								'note'
								);
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_review_control_id' => 'UserReviewControl',
										'kpi_id' => 'KPI',
										'name' => FALSE,
										'type_id' => FALSE,
										'status_id' => FALSE,
										'minimum_rate' => FALSE,
										'maximum_rate' => FALSE,
										'description' => FALSE,
										'rating' => 'Rating',
										'note' => 'Note',
										'tag' => 'Tag',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}
	function getKPIObject() {
		return $this->getGenericObject( 'KPIListFactory', $this->getKPI(), 'kpi_obj' );
	}

	function getKPI() {
		if ( isset($this->data['kpi_id']) ) {
			return (int)$this->data['kpi_id'];
		}
		return FALSE;
	}

	function setKPI($id) {
		$id = trim($id);
		$klf = TTnew( 'KPIListFactory' );
		if ( $this->Validator->isResultSetWithRows( 'kpi_id',
													$klf->getById($id),
													TTi18n::gettext('Invalid KPI')
														) ) {
						$this->data['kpi_id'] = $id;
						return TRUE;
		}
		return FALSE;
	}

	function getUserReviewControl() {
		if ( isset($this->data['user_review_control_id']) ) {
			return (int)$this->data['user_review_control_id'];
		}
		return FALSE;
	}

	function setUserReviewControl( $id ) {
		$id = trim($id);

		$urclf = TTnew('UserReviewControlListFactory');

		if ( $this->Validator->isResultSetWithRows( 'user_review_control_id',
													$urclf->getById($id),
													TTi18n::gettext('Invalid review control')
													) ) {
						$this->data['user_review_control_id'] = $id;
						return TRUE;
		}
		return FALSE;
	}

	function getRating() {
		if ( isset($this->data['rating']) ) {
			return $this->data['rating'];
		}
		return FALSE;
	}

	function setRating($value) {
		$value = trim($value);

		if ( $value == '' ) {
			$value = NULL;
		}
		if ( (
				$value == NULL
				OR
				( $this->Validator->isNumeric(	'rating',
													$value,
													TTi18n::gettext('Rating must only be digits')
										)
				AND
				$this->Validator->isLengthAfterDecimal( 'rating',
														$value,
														TTi18n::gettext('Invalid Rating'),
														0,
														2
										) ) )

			) {
				$this->data['rating'] = $value;
				return	TRUE;
		}

		//$this->data['rating'] = $value;
		//return  TRUE;
		return FALSE;
	}

	function getNote() {
		if ( isset($this->data['note']) ) {
			return $this->data['note'];
		}
		return FALSE;
	}
	function setNote($note) {
		$note = trim($note);

		if (	$note == ''
				OR
				$this->Validator->isLength( 'note',
											$note,
											TTi18n::gettext('Note is too long'),
											0, 4096 )  ) {
				$this->data['note'] = $note;
				return	TRUE;
		}

		return FALSE;
	}

	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['tags']) ) {
			return $this->tmp_data['tags'];
		} elseif ( is_object( $this->getKPIObject() ) AND $this->getKPIObject()->getCompany() > 0 AND $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getKPIObject()->getCompany(), 330, $this->getID() );
		}

		return FALSE;
	}
	function setTag( $tags ) {
		$tags = trim($tags);

		//Save the tags in temporary memory to be committed in postSave()
		$this->tmp_data['tags'] = $tags;

		return TRUE;
	}

	function Validate() {
		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.

		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getKPIObject()->getCompany(), 330, $this->getID(), $this->getTag() );
		}

		return TRUE;
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;

					switch( $variable ) {
						case 'name':
						case 'type_id':
						case 'status_id':
						case 'minimum_rate':
						case 'maximum_rate':
						case 'description':
							$data[$variable] = $this->getColumn( $variable );
							break;
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
		$kpi_obj = $this->getKPIObject();
		if ( is_object($kpi_obj) ) {
			return TTLog::addEntry( $this->getUserReviewControl(), $log_action, TTi18n::getText('Employee Review KPI') . ' - ' . TTi18n::getText('KPI') . ': ' . $kpi_obj->getName(), NULL, $this->getTable(), $this );
		}
		return FALSE;
	}

}
?>
