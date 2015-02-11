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
 * @package Modules\Policy
 */
class CompanyGenericMapFactory extends Factory {
	protected $table = 'company_generic_map';
	protected $pk_sequence_name = 'company_generic_map_id_seq'; //PK Sequence name

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$retval = array(
										//Policy Group mapping
										90 => 'contributing_pay_code_policy',

										100 => 'policy_group_regular_time_policy',
										105 => 'schedule_policy_include_regular_policy', //Mapping regular policies to schedule policies.
										106 => 'schedule_policy_exclude_regular_policy', //Mapping regular policies to schedule policies.
										110 => 'policy_group_over_time_policy',
										115 => 'schedule_policy_include_over_time_policy', //Mapping regular policies to schedule policies.
										116 => 'schedule_policy_exclude_over_time_policy', //Mapping regular policies to schedule policies.
										120 => 'policy_group_premium_policy',
										125 => 'schedule_policy_include_premium_policy', //Mapping premium policies to schedule policies.
										126 => 'schedule_policy_exclude_premium_policy', //Mapping premium policies to schedule policies.
										130 => 'policy_group_round_interval_policy',
										140 => 'policy_group_accrual_policy',
										150 => 'policy_group_meal_policy',
										155 => 'schedule_policy_meal_policy', //Mapping meal policies to schedule policies.
										160 => 'policy_group_break_policy',
										165 => 'schedule_policy_break_policy', //Mapping break policies to schedule policies.
										170 => 'policy_group_absence_policy',
										180 => 'policy_group_holiday_policy',
										190 => 'policy_group_exception_policy',
										200 => 'policy_group_expense_policy',

										300 => 'expense_policy_expense_policy',

/*
										//Station user mapping
										310 => 'station_branch',
										320 => 'station_department',
										330 => 'station_user_group',
										340 => 'station_include_user',
										350 => 'station_exclude_user',

										//Premium Policy mapping
										510 => 'premium_policy_branch',
										520 => 'premium_policy_department',
										530 => 'premium_policy_job',
										540 => 'premium_policy_job_group',
										550 => 'premium_policy_job_item',
										560 => 'premium_policy_job_item_group',
*/

										//Regular Policy mapping
										581 => 'regular_time_policy_branch',
										582 => 'regular_time_policy_department',
										583 => 'regular_time_policy_job',
										584 => 'regular_time_policy_job_group',
										585 => 'regular_time_policy_job_item',
										586 => 'regular_time_policy_job_item_group',

										//Overtime Policy mapping
										591 => 'over_time_policy_branch',
										592 => 'over_time_policy_department',
										593 => 'over_time_policy_job',
										594 => 'over_time_policy_job_group',
										595 => 'over_time_policy_job_item',
										596 => 'over_time_policy_job_item_group',

										//Contributing Shift Policy mapping
										610 => 'contributing_shift_policy_branch',
										620 => 'contributing_shift_policy_department',
										630 => 'contributing_shift_policy_job',
										640 => 'contributing_shift_policy_job_group',
										650 => 'contributing_shift_policy_job_item',
										660 => 'contributing_shift_policy_job_item_group',
										690 => 'contributing_shift_policy_holiday_policy',


										//Job user mapping
										1010 => 'job_user_branch',
										1020 => 'job_user_department',
										1030 => 'job_user_group',
										1040 => 'job_include_user',
										1050 => 'job_exclude_user',

										//Job task mapping
										1060 => 'job_job_item_group',
										1070 => 'job_include_job_item',
										1080 => 'job_exclude_job_item',


										1090 => 'qualification_group',

										2010 => 'kpi_group',
										2020 => 'kpi_kpi_group',

										//Invoice Payment Gateway mapping
										3010 => 'payment_gateway_credit_card_type',
										3020 => 'payment_gateway_bank_account_type',
									);
				break;
		}

		return $retval;
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$clf->getByID($id),
															TTi18n::gettext('Company is invalid')
															) ) {
			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getObjectType() {
		if ( isset($this->data['object_type_id']) ) {
			return (int)$this->data['object_type_id'];
		}

		return FALSE;
	}
	function setObjectType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'object_type',
											$type,
											TTi18n::gettext('Object Type is invalid'),
											$this->getOptions('object_type')) ) {

			$this->data['object_type_id'] = $type;

			return FALSE;
		}

		return FALSE;
	}

	function getObjectID() {
		if ( isset($this->data['object_id']) ) {
			return (int)$this->data['object_id'];
		}

		return FALSE;
	}
	function setObjectID($id) {
		$id = trim($id);

		if ( $this->Validator->isNumeric(	'object_id',
										$id,
										TTi18n::gettext('Object ID is invalid')
										) ) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getMapID() {
		if ( isset($this->data['map_id']) ) {
			return (int)$this->data['map_id'];
		}

		return FALSE;
	}
	function setMapID($id) {
		$id = trim($id);

		if ( $this->Validator->isNumeric(	'map_id',
										$id,
										TTi18n::gettext('Map ID is invalid')
										) ) {
			$this->data['map_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	static function setMapIDs( $company_id, $object_type_id, $object_id, $ids, $is_new = FALSE, $relaxed_range = FALSE ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $object_id == '') {
			return FALSE;
		}

		//If IDs is defined as a blank value, and not an array, assume its a blank array and remove all mapped IDs.
		if ( $ids == '' ) {
			$ids = array();
			//return FALSE;
		}

		if ( !is_array($ids) AND is_numeric( $ids ) ) {
			$ids = array($ids);
		}
		//Debug::Arr($ids, 'Object Type ID: '. $object_type_id .' Object ID: '. $object_id .' IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$ids = array_unique( $ids ); //Make sure the IDs are unique to help avoid duplicates.

			$tmp_ids = array();
			if ( $is_new == FALSE ) {
				//If needed, delete mappings first.
				$cgmlf = TTnew( 'CompanyGenericMapListFactory' );
				$cgmlf->getByCompanyIDAndObjectTypeAndObjectID( $company_id, $object_type_id, $object_id );
				foreach ($cgmlf as $obj) {
					$id = $obj->getMapID();
					//Debug::text('Object Type ID: '. $object_type_id .' Object ID: '. $obj->getObjectID() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete objects that are not selected
					//Also check for duplicate IDs and delete them too.
					if ( !in_array($id, $ids) OR in_array( $id, $tmp_ids ) ) {
						//Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						//Debug::text('NOT Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}
			foreach ($ids as $id) {
				if ( $id !== FALSE AND ( $relaxed_range == TRUE OR ( $relaxed_range == FALSE AND ( $id == -1 OR $id > 0 ) ) ) AND !in_array($id, $tmp_ids) ) {
					$cgmf = TTnew( 'CompanyGenericMapFactory' );
					$cgmf->setCompany( $company_id );
					$cgmf->setObjectType( $object_type_id );
					$cgmf->setObjectID( $object_id );
					$cgmf->setMapId( $id );
					$cgmf->Save();
				}
			}

			$cgmlf->removeCache( md5( $company_id . serialize( $object_type_id ) . serialize( $object_id ) ) );

			return TRUE;
		}

		Debug::text('No objects to map.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function postSave() {
		$this->removeCache( md5( $this->getCompany() . serialize( $this->getObjectType() ) . serialize( $this->getId() ) ) );

		return TRUE;
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

	function addLog( $log_action ) {
		$retval = FALSE;
		if ( $this->getObjectType() > 0 ) {
			$description = TTi18n::getText('Generic Object Mapping');
			switch( $this->getObjectType() ) {
				case 110:
				case 120:
				case 130:
				case 140:
				case 150:
				case 160:
				case 170:
				case 180:
				case 190:
				case 200:
					switch( $this->getObjectType() )  {
						case 110:
							$lf = TTnew( 'OverTimePolicyListFactory' );
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Overtime Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 120:
							$lf = TTnew( 'PremiumPolicyListFactory' );
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Premium Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 130:
							$lf = TTnew( 'RoundIntervalPolicyListFactory' );
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Rounding Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 140:
							$lf = TTnew( 'AccrualPolicyListFactory' );
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Accrual Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 150:
							$lf = TTnew( 'MealPolicyListFactory' );
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Meal Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 160:
							$lf = TTnew( 'BreakPolicyListFactory' );
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Break Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 170:
							$lf = TTnew( 'AbsencePolicyListFactory' );
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Absence Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 180:
							$lf = TTnew( 'HolidayPolicyListFactory' );
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Holiday Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 190: //Not handled with generic mapping currently.
							$lf = TTnew( 'ExceptionPolicyListFactory' );
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Exception Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 200:
							$lf = TTnew( 'ExpensePolicyListFactory' );
							$lf->getById( $this->getMapID() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = TTi18n::getText('Expense Policy').': '. $lf->getCurrent()->getName();
							}
							break;
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'policy_group' );
					break;
				case 300:
					$lf = TTnew( 'ExpensePolicyListFactory' );
					$lf->getById( $this->getMapID() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = TTi18n::getText('Expense Policy').': '. $lf->getCurrent()->getName();
					}
					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'expense_policy' );
					break;
				case 165:
					$lf = TTnew( 'BreakPolicyListFactory' );
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = TTi18n::getText('Break Policy').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'schedule_policy' );
					break;
				//Job user mapping
				case 1010: //'job_user_branch',
					$lf = TTnew( 'BranchListFactory' );
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = TTi18n::getText('Branch').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'job_user_branch' );
					break;
				case 1020: // => 'job_user_department',
					$lf = TTnew( 'DepartmentListFactory' );
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = TTi18n::getText('Department').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'job_user_department' );
					break;
				case 1030: // => 'job_user_group',
					$lf = TTnew( 'UserGroupListFactory' );
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = TTi18n::getText('Employee Group').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'job_user_group' );
					break;
				case 1090:
					$lf = TTnew( 'QualificationGroupListFactory' );
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = TTi18n::getText('Qualification Group').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'qualification_group' );
					break;
				case 2010:
				case 2020:
					switch( $this->getObjectType() ) {
						case 2010:
							$table_name = 'kpi_group';
							break;
						case 2020:
							$table_name = 'kpi';
							break;
					}
					$lf = TTnew( 'KPIGroupListFactory' );
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = TTi18n::getText('KPI Group').': '. $lf->getCurrent()->getName();
					}
					if ( $this->getMapID() == -1 ) {
						$description = TTi18n::getText('KPI Group').': All';
					}
					if ( $this->getMapID() == 0 ) {
						$description = TTi18n::getText('KPI Group').': Root';
					}
					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, $table_name );
					break;
				case 1040: // => 'job_include_user',
				case 1050: // => 'job_exclude_user',
					switch( $this->getObjectType() ) {
						case 1040:
							$table_name = 'job_include_user';
							$type = TTi18n::getText('Include');
							break;
						case 1050:
							$table_name = 'job_exclude_user';
							$type = TTi18n::getText('Exclude');
							break;
					}

					$lf = TTnew( 'UserListFactory' );
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = $type.' '. TTi18n::getText('Employee').': '. $lf->getCurrent()->getFullName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, $table_name );
					break;
				//Job task mapping
				case 1060: // => 'job_job_item_group',
					$lf = TTnew( 'JobItemGroupListFactory' );
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = TTi18n::getText('Task Group').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'job_job_item_group' );
					break;
				case 1070: // => 'job_include_job_item',
				case 1080: // => 'job_exclude_job_item',
					switch( $this->getObjectType() ) {
						case 1070:
							$table_name = 'job_include_job_item';
							$type = TTi18n::getText('Include');
							break;
						case 1080:
							$table_name = 'job_exclude_job_item';
							$type = TTi18n::getText('Exclude');
							break;
					}

					$lf = TTnew( 'JobItemListFactory' );
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = $type.' '. TTi18n::getText('Task').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, $table_name );
					break;
				case 3010: // => 'payment_gateway_credit_card_type',
					$table_name = 'payment_gateway_credit_card_type';

					$cpf = TTnew( 'ClientPaymentFactory' );
					$description = TTi18n::getText('Credit Card Type').': '. Option::getByKey( $this->getMapId(), $cpf->getOptions('credit_card_type') );

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, $table_name );

					break;
				case 3020: // => 'payment_gateway_bank_account_type',
					$table_name = 'payment_gateway_bank_account_type';

					$cpf = TTnew( 'ClientPaymentFactory' );
					$description = TTi18n::getText('Bank Account Type').': '. Option::getByKey( $this->getMapId(), $cpf->getOptions('bank_account_type') );

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, $table_name );

					break;
					break;
			}
		}

		return $retval;
	}

}
?>
