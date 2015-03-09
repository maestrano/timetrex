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
class CurrencyRateFactory extends Factory {
	protected $table = 'currency_rate';
	protected $pk_sequence_name = 'currency_rate_id_seq'; //PK Sequence name

	protected $currency_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										//'-1010-iso_code' => TTi18n::gettext('ISO Code'),
										'-1020-date_stamp' => TTi18n::gettext('Date'),
										'-1030-conversion_rate' => TTi18n::gettext('Conversion Rate'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('date_stamp', 'conversion_rate'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'date_stamp',
								'conversion_rate',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'date_stamp',
								);
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'currency_id' => 'Currency',
										//'status_id' => FALSE,
										//'status' => FALSE,
										//'name' => FALSE,
										//'symbol' => FALSE,
										//'iso_code' => FALSE,
										'date_stamp' => 'DateStamp',
										'conversion_rate' => 'ConversionRate',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
	}

	function getCurrency() {
		if ( isset($this->data['currency_id']) ) {
			return (int)$this->data['currency_id'];
		}

		return FALSE;
	}
	function setCurrency($id) {
		$id = trim($id);

		Debug::Text('Currency ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$culf = TTnew( 'CurrencyListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'currency_id',
														$culf->getByID($id),
														TTi18n::gettext('Invalid Currency')
													) ) {

			$this->data['currency_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDateStamp( $raw = FALSE ) {
		if ( isset($this->data['date_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['date_stamp'];
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $this->data['date_stamp'] );
			}
		}

		return FALSE;
	}
	function setDateStamp($epoch) {
		$epoch = trim($epoch);

		if	( $epoch != ''
				AND
				$this->Validator->isDate(		'date_stamp',
												$epoch,
												TTi18n::gettext('Incorrect date'))
			) {

			$this->data['date_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function isUnique() {
		$ph = array(
					'currency_id' => $this->getCurrency(),
					'date_stamp' => $this->db->BindDate( $this->getDateStamp() ),
					);

		$query = 'select id from '. $this->getTable() .' where currency_id = ? AND date_stamp = ?';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique Currency Rate: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getReverseConversionRate() {
		return bcdiv( 1, $this->getConversionRate() );
	}

	function getConversionRate() {
		if ( isset($this->data['conversion_rate']) ) {
			return $this->data['conversion_rate'];
		}

		return FALSE;
	}
	function setConversionRate( $value ) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);


		if ( 	$this->Validator->isTrue(		'conversion_rate',
												$value,
												TTi18n::gettext('Conversion rate not specified')
				) AND $this->Validator->isFloat(	'conversion_rate',
													$value,
													TTi18n::gettext('Incorrect Conversion Rate'))
			) {

			$this->data['conversion_rate'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->getDeleted() == FALSE ) {
			if ( $this->validate_only == FALSE AND $this->getDateStamp() == FALSE ) {
				$this->Validator->isTrue(	'date_stamp',
											FALSE,
											TTi18n::gettext('Date not specified') );
			} else if ( $this->validate_only == FALSE AND $this->isUnique() == FALSE ) {
				$this->Validator->isTrue(	'date_stamp',
											FALSE,
											TTi18n::gettext('Currency rate already exists for this date') );
			}

			if ( $this->getConversionRate() == FALSE AND $this->Validator->hasError('conversion_rate') == FALSE ) {
				$this->Validator->isTrue(		'conversion_rate',
												FALSE,
												TTi18n::gettext('Conversion rate not specified'));
			}
		}

		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

	//Support setting created_by, updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();

			foreach( $variable_function_map as $key => $function ) {

				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'date_stamp':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
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
		/*
		$include_columns = array(
								'id' => TRUE,
								'company_id' => TRUE,
								...
								)

		*/

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'date_stamp':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function( TRUE );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Currency Rate').': '. $this->getCurrencyObject()->getISOCode() .' '.  TTi18n::getText('Rate').': '. $this->getConversionRate(), NULL, $this->getTable(), $this );
	}

}
?>
