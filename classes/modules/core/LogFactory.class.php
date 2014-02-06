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
 * $Revision: 9889 $
 * $Id: LogFactory.class.php 9889 2013-05-14 22:49:16Z ipso $
 * $Date: 2013-05-14 15:49:16 -0700 (Tue, 14 May 2013) $
 */

/**
 * @package Core
 */
class LogFactory extends Factory {
	protected $table = 'system_log';
	protected $pk_sequence_name = 'system_log_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'action':
				$retval = array(
											10 => TTi18n::gettext('Add'),
											20 => TTi18n::gettext('Edit'),
											30 => TTi18n::gettext('Delete'),
											31 => TTi18n::gettext('Delete (F)'), //Full Delete
											40 => TTi18n::gettext('UnDelete'),
											100 => TTi18n::gettext('Login'),
											110 => TTi18n::gettext('Logout'),
											200 => TTi18n::gettext('Allow'),
											210 => TTi18n::gettext('Deny'),
											500 => TTi18n::gettext('Notice'),
											510 => TTi18n::gettext('Warning'),
											900 => TTi18n::gettext('Other')
									);
				break;
			case 'table_name':
				$retval = array(
											'authentication'					=> TTi18n::getText('Authentication'),
											'company'							=> TTi18n::getText('Company'),
											'branch'							=> TTi18n::getText('Branch'),
											'department'						=> TTi18n::getText('Department'),
											'currency'							=> TTi18n::getText('Currency'),
											'accrual'							=> TTi18n::getText('Accrual'),
											'authorizations'					=> TTi18n::getText('Authorizations'),
											'request'							=> TTi18n::getText('Request'),
											'message'							=> TTi18n::getText('Messages'), //Old version
											'message_control'					=> TTi18n::getText('Messages'),
											'holidays'							=> TTi18n::getText('Holidays'),
											'bank_account'						=> TTi18n::getText('Bank Account'),
											'roe'								=> TTi18n::getText('Record of Employment'),
											'station'							=> TTi18n::getText('Station'),
											'station_user_group'				=> TTi18n::getText('Station Employee Group'),
											'station_branch'					=> TTi18n::getText('Station Branch'),
											'station_department'				=> TTi18n::getText('Station Department'),
											'station_include_user'				=> TTi18n::getText('Station Include Employee'),
											'station_exclude_user'				=> TTi18n::getText('Station Exclude Employee'),
											'station'							=> TTi18n::getText('Station'),
											'punch'								=> TTi18n::getText('Punch'),
											'punch_control'						=> TTi18n::getText('Punch Control'),
											'exception'							=> TTi18n::getText('Exceptions'),
											'schedule'							=> TTi18n::getText('Schedule'),
											'other_field'						=> TTi18n::getText('Other Field'),
											'system_setting'					=> TTi18n::getText('System Setting'),
											'cron'								=> TTi18n::getText('Maintenance Jobs'),
											'permission_control'				=> TTi18n::getText('Permission Groups'),
											'permission_user'					=> TTi18n::getText('Permission Employees'),
											'permission'						=> TTi18n::getText('Permissions'),

											'policy_group'						=> TTi18n::getText('Policy Group'),
											'policy_group_user'					=> TTi18n::getText('Policy Group Employees'),
											'schedule_policy'					=> TTi18n::getText('Schedule Policy'),
											'round_interval_policy'				=> TTi18n::getText('Rounding Policy'),
											'meal_policy'						=> TTi18n::getText('Meal Policy'),
											'break_policy'						=> TTi18n::getText('Break Policy'),
											'accrual_policy'					=> TTi18n::getText('Accrual Policy'),
											'accrual_policy_milestone'			=> TTi18n::getText('Accrual Policy Milestone'),
											'over_time_policy'					=> TTi18n::getText('Overtime Policy'),
											'premium_policy'					=> TTi18n::getText('Premium Policy'),
											'premium_policy_branch'				=> TTi18n::getText('Premium Policy Branch'),
											'premium_policy_department'			=> TTi18n::getText('Premium Policy Department'),
											'premium_policy_job_group'			=> TTi18n::getText('Premium Policy Job Group'),
											'premium_policy_job'				=> TTi18n::getText('Premium Policy Job'),
											'premium_policy_job_item_group'		=> TTi18n::getText('Premium Policy Task Group'),
											'premium_policy_job_item'			=> TTi18n::getText('Premium Policy Task'),
											'absence_policy'					=> TTi18n::getText('Absense Policy'),
											'exception_policy_control'			=> TTi18n::getText('Exception Policy (Control)'),
											'exception_policy'					=> TTi18n::getText('Exception Policy'),
											'holiday_policy'					=> TTi18n::getText('Holiday Policy'),
											'holiday_policy_recurring_holiday'	=> TTi18n::getText('Holiday Policy'),

											'pay_period'						=> TTi18n::getText('Pay Period'),
											'pay_period_schedule'				=> TTi18n::getText('Pay Period Schedule'),
											'pay_period_schedule_user'			=> TTi18n::getText('Pay Period Schedule Employees'),
											'pay_period_time_sheet_verify'		=> TTi18n::getText('TimeSheet Verify'),

											'pay_stub'							=> TTi18n::getText('Pay Stub'),
											'pay_stub_amendment'				=> TTi18n::getText('Pay Stub Amendment'),
											'pay_stub_entry_account'			=> TTi18n::getText('Pay Stub Account'),
											'pay_stub_entry_account_link'		=> TTi18n::getText('Pay Stub Account Linking'),

											'recurring_holiday'					=> TTi18n::getText('Recurring Holiday'),
											'recurring_ps_amendment'			=> TTi18n::getText('Recurring PS Amendment'),
											'recurring_ps_amendment_user'		=> TTi18n::getText('Recurring PS Amendment Employees'),
											'recurring_schedule_control'		=> TTi18n::getText('Recurring Schedule'),
											'recurring_schedule_user'			=> TTi18n::getText('Recurring Schedule Employees'),
											'recurring_schedule_template_control' => TTi18n::getText('Recurring Schedule Template'),
											'recurring_schedule_template'		=> TTi18n::getText('Recurring Schedule Week'),

											'user_date_total'					=> TTi18n::getText('Employee Hours'),
											'user_default'						=> TTi18n::getText('New Hire Defaults'),
											'user_generic_data'					=> TTi18n::getText('Employee Generic Data'),
											'user_preference'					=> TTi18n::getText('Employee Preference'),
											'users'								=> TTi18n::getText('Employee'),
											'user_identification'				=> TTi18n::getText('Employee Identification'),
											'company_deduction'					=> TTi18n::getText('Tax / Deduction'),
											'company_deduction_pay_stub_entry_account' => TTi18n::getText('Tax / Deduction PS Accounts'),
											'user_deduction'					=> TTi18n::getText('Employee Deduction'),
											'user_title'						=> TTi18n::getText('Employee Title'),
											'user_wage'							=> TTi18n::getText('Employee Wage'),

											'hierarchy_control'					=> TTi18n::getText('Hierarchy'),
											'hierarchy_object_type'				=> TTi18n::getText('Hierarchy Object Type'),
											'hierarchy_user'					=> TTi18n::getText('Hierarchy Subordinate'),
											'hierarchy_level'					=> TTi18n::getText('Hierarchy Superior'),
											'hierarchy'							=> TTi18n::getText('Hierarchy Tree'),

											'user_report_data'					=> TTi18n::getText('Reports'),
											'report_schedule'					=> TTi18n::getText('Report Schedule'),
                                            'report_custom_column'              => TTi18n::getText('Report Custom Column'),

											'job'								=> TTi18n::getText('Job'),
											'job_user_branch'					=> TTi18n::getText('Job Branch'),
											'job_user_department'				=> TTi18n::getText('Job Department'),
											'job_user_group'					=> TTi18n::getText('Job Group'),
											'job_include_user'					=> TTi18n::getText('Job Include Employee'),
											'job_exclude_user'					=> TTi18n::getText('Job Exclude Employee'),
											'job_job_item_group'				=> TTi18n::getText('Job Task Group'),
											'job_include_job_item'				=> TTi18n::getText('Job Include Task'),
											'job_exclude_job_item'				=> TTi18n::getText('Job Exclude Task'),
											'job_item'							=> TTi18n::getText('Job Task'),
											'job_item_amendment'				=> TTi18n::getText('Job Task Amendment'),
											'document'							=> TTi18n::getText('Document'),
											'document_revision'					=> TTi18n::getText('Document Revision'),
											'client'							=> TTi18n::getText('Client'),
											'client_contact'					=> TTi18n::getText('Client Contact'),
											'client_payment'					=> TTi18n::getText('Client Payment'),
											'invoice'							=> TTi18n::getText('Invoice'),
											'invoice_config'					=> TTi18n::getText('Invoice Settings'),
											'invoice_transaction'				=> TTi18n::getText('Invoice Transaction'),
											'product'							=> TTi18n::getText('Product'),
											'product_price'						=> TTi18n::getText('Product Price Bracket'),
											'product_tax_policy'				=> TTi18n::getText('Product Tax Policy'),
											'tax_area_policy'					=> TTi18n::getText('Invoice Tax Area Policy'),
											'tax_policy'						=> TTi18n::getText('Invoice Tax Policy'),
											'transaction'						=> TTi18n::getText('Invoice Transaction'),
                                            'user_contact'                      => TTi18n::getText('Employee Contact'),
                                            'user_expense'                      => TTi18n::getText('Expense'),
                                            'expense_policy'                    => TTi18n::getText('Expense Policy'),
                                            'user_review'                       => TTi18n::getText('Review'),
                                            'user_review_control'               => TTi18n::getText('Review (Control)'),
                                            'kpi'                               => TTi18n::getText('Key Performance Indicator'),
                                            'qualification'                     => TTi18n::getText('Qualification'),
                                            'user_skill'                        => TTi18n::getText('Skill'),
                                            'user_education'                    => TTi18n::getText('Education'),
                                            'user_membership'                   => TTi18n::getText('Memberships'),
                                            'user_license'                      => TTi18n::getText('Licenses'),
                                            'user_language'                     => TTi18n::getText('Languages'),
                                            'job_vacancy'                       => TTi18n::getText('Job Vacancy'),
                                            'job_applicant'                     => TTi18n::getText('Job Applicant'),
                                            'job_application'                   => TTi18n::getText('Job Application'),
                                            'job_applicant_location'            => TTi18n::getText('Job Applicant Location'),
                                            'job_applicant_employment'          => TTi18n::getText('Job Applicant Employment'),
                                            'job_applicant_reference'           => TTi18n::getText('Job Applicant Reference'),
                                            'job_applicant_skill'               => TTi18n::getText('Job Applicant Skill'),
                                            'job_applicant_education'           => TTi18n::getText('Job Applicant Education'),
                                            'job_applicant_license'             => TTi18n::getText('Job Applicant Licenses'),
                                            'job_applicant_language'            => TTi18n::getText('Job Applicant Languages'),
                                            'job_applicant_membership'          => TTi18n::getText('Job Applicant Memberships'),
                                            'ethnic_group'                      => TTi18n::getText('Ethnic Group'),

									);
				break;
			case 'columns':
				$retval = array(
										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),
										'-1100-date' => TTi18n::gettext('Date'),
										'-1110-object' => TTi18n::gettext('Object'),
										'-1120-action' => TTi18n::gettext('Action'),
										'-1130-description' => TTi18n::gettext('Description'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'date',
								'object',
								'action',
								'description',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array();
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array();
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_id' => 'User',
										'first_name' => FALSE,
										'last_name' => FALSE,
										'object_id' => 'Object',
										'table_name' => 'TableName',
										'object' => FALSE, //Actually the display table name.

										'action_id' => 'Action',
										'action' => FALSE,
										'description' => 'Description',
										'date' => 'Date',

										'details' => 'Details',

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
		}
	}

	function getLink() {

		$link = FALSE;

		//Only show links on add/edit/allow actions.
		if ( !in_array( $this->getAction(), array(10,20,200) ) ) {
			return $link;
		}

		switch ( $this->getTableName() ) {
			case 'authentication':
				break;
			case 'company':
				$link = 'company/EditCompany.php?id='. $this->getObject();
				break;
			case 'branch':
				$link = 'branch/EditBranch.php?id='. $this->getObject();
				break;
			case 'department':
				$link = 'department/EditDepartment.php?id='. $this->getObject();
				break;
			case 'currency':
				$link = 'currency/EditCurrency.php?id='. $this->getObject();
				break;
			case 'accrual':
				//$link = 'currency/EditCurrency.php?id='. $this->getObject();
				break;
			case 'authorizations':
				break;
			case 'request':
				$link = 'request/ViewRequest.php?id='. $this->getObject();
				break;
			case 'permission_control':
				$link = 'permission/EditPermissionControl.php?id='. $this->getObject();
				break;
			case 'holidays':
				break;
			case 'bank_account':
				break;
			case 'roe':
				break;
			case 'station':
				$link = 'station/EditStation.php?id='. $this->getObject();
				break;
			case 'punch':
				break;
			case 'other_field':
				break;
			case 'system_setting':
				break;
			case 'cron':
				break;
			case 'policy_group':
				$link = 'policy/EditPolicyGroup.php?id='. $this->getObject();
				break;
			case 'schedule_policy':
				$link = 'policy/EditSchedulePolicy.php?id='. $this->getObject();
				break;
			case 'round_interval_policy':
				$link = 'policy/EditRoundIntervalPolicy.php?id='. $this->getObject();
				break;
			case 'meal_policy':
				$link = 'policy/EditMealPolicy.php?id='. $this->getObject();
				break;
			case 'accrual_policy':
				$link = 'policy/EditAccrualPolicy.php?id='. $this->getObject();
				break;
			case 'over_time_policy':
				$link = 'policy/EditOverTimePolicy.php?id='. $this->getObject();
				break;
			case 'premium_policy':
				$link = 'policy/EditPremiumTimePolicy.php?id='. $this->getObject();
				break;
			case 'absence_policy':
				$link = 'policy/EditAbsencePolicy.php?id='. $this->getObject();
				break;
			case 'exception_policy_control':
				$link = 'policy/EditExceptionControlPolicy.php?id='. $this->getObject();
				break;
			case 'holiday_policy':
				$link = 'policy/EditHolidayPolicy.php?id='. $this->getObject();
				break;
			case 'pay_period':
				$link = 'payperiod/ViewPayPeriod.php?pay_period_id='. $this->getObject();
				break;
			case 'pay_period_schedule':
				$link = 'payperiod/EditPayPeriodSchedule.php?id='. $this->getObject();
				break;
			case 'pay_period_time_sheet_verify':
				break;
			case 'pay_stub':
				break;
			case 'pay_stub_amendment':
				$link = 'pay_stub_amendment/EditPayStubAmendment.php?id='. $this->getObject();
				break;
			case 'pay_stub_entry_account':
				$link = 'pay_stub/EditPayStubEntryAccount.php?id='. $this->getObject();
				break;
			case 'pay_stub_entry_account_link':
				break;
			case 'recurring_holiday':
				$link = 'policy/EditRecurringHoliday.php?id='. $this->getObject();
				break;
			case 'recurring_ps_amendment':
				$link = 'pay_stub_amendment/EditRecurringPayStubAmendment.php?id='. $this->getObject();
				break;
			case 'recurring_schedule_control':
				$link = 'schedule/EditRecurringSchedule.php?id='. $this->getObject();
				break;
			case 'recurring_schedule_template_control':
				$link = 'schedule/EditRecurringScheduleTemplate.php?id='. $this->getObject();
				break;
			case 'user_date_total':
				break;
			case 'user_default':
				$link = 'users/EditUserDefault.php?id='. $this->getObject();
				break;
			case 'user_generic_data':
				break;
			case 'user_preference':
				$link = 'users/EditUserPreference.php?user_id='. $this->getObject();
				break;
			case 'users':
				$link = 'users/EditUser.php?id='. $this->getObject();
				break;
			case 'company_deduction':
				$link = 'company/EditCompanyDeduction.php?id='. $this->getObject();
				break;
			case 'user_deduction':
				$link = 'users/EditUserDeduction.php?id='. $this->getObject();
				break;
			case 'user_title':
				$link = 'users/EditUserTitle.php?id='. $this->getObject();
				break;
			case 'user_wage':
				$link = 'users/EditUserWage.php?id='. $this->getObject();
				break;
			case 'job':
				$link = 'job/EditJob.php?id='. $this->getObject();
				break;
			case 'job_item':
				$link = 'job_item/EditJobItem.php?id='. $this->getObject();
				break;
			case 'job_item_amendment':
				$link = 'job_item/EditJobItemAmendment.php?id='. $this->getObject();
				break;
			case 'document':
				$link = 'document/EditDocument.php?document_id='. $this->getObject();
				break;
			case 'document_revision':
				break;
			case 'client':
				$link = 'client/EditClient.php?client_id='. $this->getObject();
				break;
			case 'client_contact':
				$link = 'client/EditClientContact.php?id='. $this->getObject();
				break;
			case 'client_payment':
				$link = 'client/EditClientPayment.php?id='. $this->getObject();
				break;
			case 'invoice':
				$link = 'invoice/EditInvoice.php?id='. $this->getObject();
				break;
			case 'invoice_config':
				$link = 'invoice/EditInvoiceConfig.php';
				break;
			case 'invoice_transaction':
				$link = 'invoice/EditTransaction.php?id='. $this->getObject();
				break;
			case 'product':
				$link = 'product/EditProduct.php?id='. $this->getObject();
				break;
			case 'tax_area_policy':
				$link = 'invoice_policy/EditTaxAreaPolicy.php?id='. $this->getObject();
				break;
			case 'tax_policy':
				$link = 'invoice_policy/EditTaxPolicy.php?id='. $this->getObject();
				break;
		}

		if ( $link !== FALSE ) {
			$link = Environment::getBaseURL().$link;
		}

		return $link;
	}

	function getUser() {
		return $this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		//Allow NULL ids.
		if ( $id == '' OR $id == NULL ) {
			$id = 0;
		}

		$ulf = TTnew( 'UserListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('User is invalid')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getObject() {
		if ( isset($this->data['object_id']) ) {
			return $this->data['object_id'];
		}

		return FALSE;
	}
	function setObject($id) {
		$id = trim($id);

		if (	$this->Validator->isNumeric(	'object',
												$id,
												TTi18n::gettext('Object is invalid'))
			) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTableName() {
		if ( isset($this->data['table_name']) ) {
			return $this->data['table_name'];
		}

		return FALSE;
	}
	function setTableName($text) {
		$text = trim($text);

		if (
				$this->Validator->isLength(		'table',
												$text,
												TTi18n::gettext('Table is invalid'),
												2,
												250)

			) {
			$this->data['table_name'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getAction() {
		return $this->data['action_id'];
	}
	function setAction($action) {
		$action = trim($action);

		//Use integer ID values instead.
		$key = Option::getByValue($action, $this->getOptions('action') );
		if ($key !== FALSE) {
			Debug::text('Text Action: '. $action .' Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
			$action = $key;
		}
		if ( $this->Validator->inArrayKey(	'action',
											$action,
											TTi18n::gettext('Incorrect Action'),
											$this->getOptions('action')) ) {

			$this->data['action_id'] = $action;

			return FALSE;
		}

		return FALSE;
	}

	function getDescription() {
		return $this->data['description'];
	}
	function setDescription($text) {
		$text = trim($text);

		if (
				$this->Validator->isLength(		'description',
												$text,
												TTi18n::gettext('Description is invalid'),
												2,
												2000)

			) {
			$this->data['description'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getDate() {
		if ( isset($this->data['date']) ) {
			return $this->data['date'];
		}

		return FALSE;
	}

	function setDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == '') {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'date',
												$epoch,
												TTi18n::gettext('Date is invalid')) ) {

			$this->data['date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function addEntry( $object_id, $action_id, $description = NULL, $user_id = NULL, $table = NULL) {
		if ($object_id == '' ) {
			return FALSE;
		}

		if ($action_id == '') {
			return FALSE;
		}

		if ( $user_id == '' ) {
			global $current_user;
			if ( is_object($current_user) ) {
				$user_id = $current_user->getId();
			} else {
				$user_id = 0;
			}
		}

		if ( $table == '' ) {
			$table = $this->getTable();
		}

		$this->setObject( $object_id );
		$this->setAction( $action_id );
		$this->setTable( $table );
		$this->setUser( (int)$user_id );
		$this->setDescription( $description );

		if ( $this->isValid() === TRUE ) {
			$this->Save();

			return TRUE;
		}

		return FALSE;
	}

	function getDetails() {
		if ( getTTProductEdition() > 10 AND $this->isNew() == FALSE AND is_object( $this->getUserObject() ) ) {
			//Get class for this table
			Debug::Text( 'Table: '. $this->getTableName(), __FILE__, __LINE__, __METHOD__,10);
			require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'includes'. DIRECTORY_SEPARATOR .'TableMap.inc.php');
			if ( isset($global_table_map[$this->getTableName()]) ) {
				$table_class = $global_table_map[$this->getTableName()];
				$class = new $table_class;
				Debug::Text( 'Table Class: '. $table_class, __FILE__, __LINE__, __METHOD__,10);

				$ldlf = TTnew( 'LogDetailListFactory' );
				$ldlf->getBySystemLogIdAndCompanyId( $this->getID(), $this->getUserObject()->getCompany() );
				if ( $ldlf->getRecordCount() > 0 ) {
					foreach( $ldlf as $ld_obj ) {
						if ( $this->getObject() > 0 ) {
							$class->setID( $this->getObject() ); //Set the object id of the class so we can reference it later if needed.
						}
						$detail_row[] = array(
											'field' => $ld_obj->getField(),
											'display_field' => LogDetailDisplay::getDisplayField( $class, $ld_obj->getField() ),
											'old_value' => LogDetailDisplay::getDisplayOldValue( $class, $ld_obj->getField(), $ld_obj->getOldValue() ),
											'new_value' => LogDetailDisplay::getDisplayNewValue( $class, $ld_obj->getField(), $ld_obj->getNewValue() ),
											);
					}

					$detail_row = Sort::multiSort( $detail_row, 'display_field' ) ;
					//Debug::Arr( $detail_row, 'Detail Row: ', __FILE__, __LINE__, __METHOD__,10);

					return $detail_row;
				}
			}
		}

		Debug::Text('No Log Details... ID: '. $this->getID(), __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	//Don't allow remote API calls to set audit trail records.
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

			//$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
        $data = array();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'date':
							$data[$variable] = TTDate::getAPIDate('DATE+TIME', $this->getDate() );
							break;
						case 'object':
							$data[$variable] = Option::getByKey( $this->getTableName(), $this->getOptions( 'table_name' ) );
							break;
						case 'action':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'details':
							if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->getDetails();
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
			//$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
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
