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
class LogDetailFactory extends Factory {
	protected $table = 'system_log_detail';
	protected $pk_sequence_name = 'system_log_detail_id_seq'; //PK Sequence name

	function getSystemLog() {
		return (int)$this->data['system_log_id'];
	}
	function setSystemLog($id) {
		$id = trim($id);

		//Allow NULL ids.
		if ( $id == '' OR $id == NULL ) {
			$id = 0;
		}

		$llf = TTnew( 'LogListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$llf->getByID($id),
															TTi18n::gettext('System log is invalid')
															) ) {
			$this->data['system_log_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getField() {
		if ( isset($this->data['field']) ) {
			return $this->data['field'];
		}

		return FALSE;
	}
	function setField($value) {
		$value = trim($value);

		if (	$this->Validator->isString(		'field',
												$value,
												TTi18n::gettext('Field is invalid'))
			) {
			$this->data['field'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getOldValue() {
		if ( isset($this->data['old_value']) ) {
			return $this->data['old_value'];
		}

		return FALSE;
	}
	function setOldValue($text) {
		$text = trim($text);

		if (
				$this->Validator->isLength(		'old_value',
												$text,
												TTi18n::gettext('Old value is invalid'),
												0,
												1024)

			) {
			$this->data['old_value'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getNewValue() {
		if ( isset($this->data['new_value']) ) {
			return $this->data['new_value'];
		}

		return FALSE;
	}
	function setNewValue($text) {
		$text = trim($text);

		if (
				$this->Validator->isLength(		'new_value',
												$text,
												TTi18n::gettext('New value is invalid'),
												0,
												1024)

			) {
			$this->data['new_value'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	//When comparing the two arrays, if there are sub-arrays, we need to *always* include those, as we can't actually
	//diff the two, because they are already saved by the time we get to this function, so there will never be any changes to them.
	//We don't want to include sub-arrays, as the sub-classes should handle the logging themselves.
	function diffData( $arr1, $arr2 ) {
		if ( !is_array($arr1) OR !is_array($arr2) ) {
			return FALSE;
		}

		$retarr = FALSE;
		foreach( $arr1 as $key => $val ) {
			if ( !isset($arr2[$key]) OR is_array($val) OR is_array($arr2[$key]) OR ( $arr2[$key] != $val ) ) {
				$retarr[$key] = $val;
			}
		}

		return $retarr;
	}

	function addLogDetail( $action_id, $system_log_id, $object ) {
		$start_time = microtime(TRUE);

		//Only log detail records on add, edit, delete, undelete
		//Logging data on Add/Delete/UnDelete, or anything but Edit will greatly bloat the database, on the order of tens of thousands of entries
		//per day. The issue though is its nice to know exactly what data was originally added, then what was edited, and what was finally deleted.
		//We may need to remove logging for added data, but leave it for edit/delete, so we know exactly what data was deleted.
		if ( !in_array($action_id, array(10, 20, 30, 31, 40) ) ) {
			Debug::text('Invalid Action ID: '. $action_id, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( $system_log_id > 0 AND is_object($object) ) {
			//Remove "Plugin" from the end of the class name incase plugins are enabled.
			$class = str_replace('Plugin', '', get_class( $object ) );
			Debug::text('System Log ID: '. $system_log_id .' Class: '. $class, __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($object->data, 'Object Data: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($object->old_data, 'Object Old Data: ', __FILE__, __LINE__, __METHOD__, 10);

			//Only store raw data changes, don't convert *_ID fields to full text names, it bloats the storage and slows down the logging process too much.
			//We can do the conversion when someone actually looks at the audit logs, which will obviously be quite rare in comparison. Even though this will
			//require quite a bit more code to handle.
			//There are also translation issues if we convert IDs to text at this point. However there could be continuity problems if ID values change in the future.
			$new_data = $object->data;
			//Debug::Arr($new_data, 'New Data Arr: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( $action_id == 20 ) { //Edit
				if ( method_exists( $object, 'setObjectFromArray' ) ) {
					//Run the old data back through the objects own setObjectFromArray(), so any necessary values can be parsed.
					$tmp_class = new $class;
					$tmp_class->setObjectFromArray( $object->old_data );
					$old_data = $tmp_class->data;
					unset($tmp_class);
				} else {
					$old_data = $object->old_data;
				}

				//We don't want to include any sub-arrays, as those classes should take care of their own logging, even though it may be slower in some cases.
				$diff_arr = array_diff_assoc( (array)$new_data, (array)$old_data );
			} elseif ( $action_id == 30 ) { //Delete
				$old_data = array();
				if ( method_exists( $object, 'setObjectFromArray' ) ) {
					//Run the old data back through the objects own setObjectFromArray(), so any necessary values can be parsed.
					$tmp_class = new $class;
					$tmp_class->setObjectFromArray( $object->data );
					$diff_arr = $tmp_class->data;
					unset($tmp_class);
				} else {
					$diff_arr = $object->data;
				}
			} else { //Add
				//Debug::text('Not editing, skipping the diff process...', __FILE__, __LINE__, __METHOD__, 10);
				//No need to store data that is added, as its already in the database, and if it gets changed or deleted we store it then.
				$old_data = array();
				$diff_arr = $object->data;
			}
			//Debug::Arr($old_data, 'Old Data Arr: ', __FILE__, __LINE__, __METHOD__, 10);

			//Handle class specific fields.
			switch ( $class ) {
				case 'UserFactory':
				case 'UserListFactory':
					unset(
							$diff_arr['labor_standard_industry'],
							$diff_arr['password'],
							$diff_arr['phone_password'],
							$diff_arr['password_reset_key'],
							$diff_arr['password_updated_date'],
							$diff_arr['last_login_date'],
							$diff_arr['full_name'],
							$diff_arr['first_name_metaphone'],
							$diff_arr['last_name_metaphone'],
							$diff_arr['ibutton_id'],
							$diff_arr['finger_print_1'],
							$diff_arr['finger_print_2'],
							$diff_arr['finger_print_3'],
							$diff_arr['finger_print_4'],
							$diff_arr['finger_print_1_updated_date'],
							$diff_arr['finger_print_2_updated_date'],
							$diff_arr['finger_print_3_updated_date'],
							$diff_arr['finger_print_4_updated_date']
							);
					break;
				case 'PayPeriodScheduleFactory':
				case 'PayPeriodScheduleListFactory':
					unset(
							$diff_arr['primary_date_ldom'],
							$diff_arr['primary_transaction_date_ldom'],
							$diff_arr['primary_transaction_date_bd'],
							$diff_arr['secondary_date_ldom'],
							$diff_arr['secondary_transaction_date_ldom'],
							$diff_arr['secondary_transaction_date_bd']
							);
					break;
				case 'PayPeriodFactory':
				case 'PayPeriodListFactory':
					unset(
							$diff_arr['is_primary']
							);
					break;
				case 'StationFactory':
				case 'StationListFactory':
					unset(
							$diff_arr['last_poll_date'],
							$diff_arr['last_push_date'],
							$diff_arr['last_punch_time_stamp'],
							$diff_arr['last_partial_push_date'],
							$diff_arr['mode_flag'], //This is changed often for some reason, would be nice to audit it though.
							$diff_arr['work_code_definition'],
							$diff_arr['allowed_date']
						);
					break;
				case 'ScheduleFactory':
				case 'ScheduleListFactory':
					unset(
							$diff_arr['recurring_schedule_template_control_id'],
							$diff_arr['replaced_id']
							);
					break;
				case 'PunchFactory':
				case 'PunchListFactory':
					unset(
							$diff_arr['actual_time_stamp'],
							$diff_arr['original_time_stamp'],
							$diff_arr['punch_control_id'],
							$diff_arr['station_id'],
							$diff_arr['latitude'],
							$diff_arr['longitude']
							);
					break;
				case 'PunchControlFactory':
				case 'PunchControlListFactory':
					unset(
							//$diff_arr['user_date_id'],
							$diff_arr['actual_total_time']
							);
					break;
				case 'PunchControlFactory':
				case 'PunchControlListFactory':
					unset(
							$diff_arr['overlap']
							);
					break;
				case 'AccrualFactory':
				case 'AccrualListFactory':
					unset(
							$diff_arr['user_date_total_id']
							);
					break;
				case 'JobItemFactory':
				case 'JobItemListFactory':
					unset(
							$diff_arr['type_id'],
							$diff_arr['department_id']
							);
					break;
				case 'ClientContactFactory':
				case 'ClientContactListFactory':
					unset(
							$diff_arr['password'],
							$diff_arr['password_reset_key'],
							$diff_arr['password_reset_date']
							);
					break;
				case 'UserReviewFactory':
				case 'UserReviewListFactory':
					unset(
							$diff_arr['user_review_control_id']
							);
					break;
				case 'ClientPaymentFactory':
				case 'ClientPaymentListFactory':
					if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
						//Only log secure values.
						if ( isset($diff_arr['cc_number']) ) {
							$old_data['cc_number'] = ( isset($old_data['cc_number']) ) ? $object->getSecureCreditCardNumber( Misc::decrypt( $old_data['cc_number'] ) ) : '';
							$new_data['cc_number'] = ( isset($new_data['cc_number']) ) ? $object->getSecureCreditCardNumber( Misc::decrypt( $new_data['cc_number'] ) ) : '';
						}

						if ( isset($diff_arr['bank_account']) ) {
							$old_data['bank_account'] = ( isset($old_data['bank_account']) ) ? $object->getSecureAccount( $old_data['bank_account'] ) : '';
							$new_data['bank_account'] = ( isset($old_data['bank_account']) ) ? $object->getSecureAccount( $new_data['bank_account'] ) : '';
						}

						if ( isset($diff_arr['cc_check']) ) {
							$old_data['cc_check'] = ( isset($old_data['cc_check']) ) ? $object->getSecureCreditCardCheck( $old_data['cc_check'] ) : '';
							$new_data['cc_check'] = ( isset($old_data['cc_check']) ) ? $object->getSecureCreditCardCheck( $new_data['cc_check'] ) : '';
						}
					}
					break;
				case 'JobApplicantFactory':
				case 'JobApplicantListFactory':
					unset(
							$diff_arr['password'],
							$diff_arr['password_reset_key'],
							$diff_arr['password_reset_date'],
							$diff_arr['first_name_metaphone'],
							$diff_arr['last_name_metaphone']
							//$diff_arr['longitude'],
							//$diff_arr['latitude']
							);
					break;
			}

			//Ignore specific columns here, like updated_date, updated_by, etc...
			unset(
					//These fields should never change, and therefore don't need to be recorded.
					$diff_arr['id'],
					$diff_arr['company_id'],

					//UserDateID controls which user things like schedules are assigned too, which is critical in the audit log.
					$diff_arr['user_date_id'], //UserDateTotal, Schedule, PunchControl, etc...

					$diff_arr['name_metaphone'],

					//General fields to skip
					$diff_arr['created_date'],
					$diff_arr['created_by'],
					$diff_arr['created_by_id'],
					$diff_arr['updated_date'],
					$diff_arr['updated_by'],
					$diff_arr['updated_by_id'],
					$diff_arr['deleted_date'],
					$diff_arr['deleted_by'],
					$diff_arr['deleted_by_id'],
					$diff_arr['deleted']
					);

			//Debug::Arr($diff_arr, 'Array Diff: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( is_array($diff_arr) AND count($diff_arr) > 0 ) {
				foreach( $diff_arr as $field => $value ) {

					$old_value = NULL;
					if ( isset($old_data[$field]) ) {
						$old_value = $old_data[$field];
						if ( is_bool($old_value) AND $old_value === FALSE ) {
							$old_value = NULL;
						} elseif ( is_array($old_value) ) {
							//$old_value = serialize($old_value);
							//If the old value is an array, replace it with NULL because it will always match the NEW value too.
							$old_value = NULL;
						}
					}

					$new_value = $new_data[$field];
					if ( is_bool($new_value) AND $new_value === FALSE ) {
						$new_value = NULL;
					} elseif ( is_array($new_value) ) {
						$new_value = serialize($new_value);
					}

					//Debug::Text('Old Value: '. $old_value .' New Value: '. $new_value, __FILE__, __LINE__, __METHOD__, 10);
					if ( !($old_value == '' AND $new_value == '') ) {
						$ph[] = (int)$system_log_id;
						$ph[] = $field;
						$ph[] = $new_value;
						$ph[] = $old_value;
						$data[] = '(?, ?, ?, ?)';
					}
				}
				if ( isset($data) ) {
					//Save data in a single SQL query.
					$query = 'INSERT INTO '. $this->getTable() .'(SYSTEM_LOG_ID, FIELD, NEW_VALUE, OLD_VALUE) VALUES'. implode(',', $data );
					//Debug::Text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
					$this->db->Execute($query, $ph);

					Debug::Text('Logged detail records in: '. (microtime(TRUE) - $start_time), __FILE__, __LINE__, __METHOD__, 10);

					return TRUE;
				}
			}
		}

		Debug::Text('Not logging detail records, likely no data changed in: '. (microtime(TRUE) - $start_time) .'s', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//This table doesn't have any of these columns, so overload the functions.
	function getDeleted() {
		return FALSE;
	}
	function setDeleted($bool) {
		return FALSE;
	}

	function getCreatedDate() {
		return FALSE;
	}
	function setCreatedDate($epoch = NULL) {
		return FALSE;
	}
	function getCreatedBy() {
		return FALSE;
	}
	function setCreatedBy($id = NULL) {
		return FALSE;
	}

	function getUpdatedDate() {
		return FALSE;
	}
	function setUpdatedDate($epoch = NULL) {
		return FALSE;
	}
	function getUpdatedBy() {
		return FALSE;
	}
	function setUpdatedBy($id = NULL) {
		return FALSE;
	}


	function getDeletedDate() {
		return FALSE;
	}
	function setDeletedDate($epoch = NULL) {
		return FALSE;
	}
	function getDeletedBy() {
		return FALSE;
	}
	function setDeletedBy($id = NULL) {
		return FALSE;
	}

	function preSave() {
		if ($this->getDate() === FALSE ) {
			$this->setDate();
		}

		return TRUE;
	}
}
?>
