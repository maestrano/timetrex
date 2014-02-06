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
 * $Revision: 11115 $
 * $Id: PermissionFactory.class.php 11115 2013-10-11 18:29:20Z ipso $
 * $Date: 2013-10-11 11:29:20 -0700 (Fri, 11 Oct 2013) $
 */

/**
 * @package Core
 */
class PermissionFactory extends Factory {
	protected $table = 'permission';
	protected $pk_sequence_name = 'permission_id_seq'; //PK Sequence name

	protected $permission_control_obj = NULL;
	protected $company_id = NULL;

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'preset':
				$retval = array(
										//-1 => TTi18n::gettext('--'),
										10 => TTi18n::gettext('Regular Employee (Punch In/Out)'),
										12 => TTi18n::gettext('Regular Employee (Manual Entry)'), //Can manually Add/Edit own punches/absences.
										18 => TTi18n::gettext('Supervisor (Subordinates Only)'),
										20 => TTi18n::gettext('Supervisor (All Employees)'),
										25 => TTi18n::gettext('HR Manager'),
										30 => TTi18n::gettext('Payroll Administrator'),
										40 => TTi18n::gettext('Administrator')
									);
				break;
			case 'common_permissions':
				$retval = array(
											'add' => TTi18n::gettext('Add'),
											'view' => TTi18n::gettext('View'),
											'view_own' => TTi18n::gettext('View Own'),
											'view_child' => TTi18n::gettext('View Subordinate'),
											'edit' => TTi18n::gettext('Edit'),
											'edit_own' => TTi18n::gettext('Edit Own'),
											'edit_child' => TTi18n::gettext('Edit Subordinate'),
											'delete' => TTi18n::gettext('Delete'),
											'delete_own' => TTi18n::gettext('Delete Own'),
											'delete_child' => TTi18n::gettext('Delete Subordinate'),
											'other' => TTi18n::gettext('Other'),
											);

				if ( defined('TIMETREX_API') == TRUE AND TIMETREX_API == TRUE ) {
					$retval = Misc::addSortPrefix( $retval, 1000 );
				}
				break;
			case 'preset_flags':
				if ( getTTProductEdition() >= TT_PRODUCT_COMMUNITY ) {
					$retval[10] = TTi18n::gettext('Scheduling');
					$retval[20] = TTi18n::gettext('Time & Attendance');
					$retval[30] = TTi18n::gettext('Payroll');
					$retval[70] = TTi18n::gettext('Human Resources');
				}

				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval[40] = TTi18n::gettext('Job Costing');
					$retval[50] = TTi18n::gettext('Document Management');
					$retval[60] = TTi18n::gettext('Invoicing');
				}

				if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
					$retval[75] = TTi18n::gettext('Recruitment');
					$retval[80] = TTi18n::gettext('Expense Tracking');
				}
				ksort($retval);
				break;
			case 'preset_level':
				$retval = array(
										10 => 1,
										12 => 2,
										18 => 10,
										20 => 15,
										25 => 18,
										30 => 20,
										40 => 25,
									);
				break;
			case 'section_group':
				$retval = array(
											0 => TTi18n::gettext('-- Please Choose --'),
											'all' => TTi18n::gettext('-- All --'),
											'company' => TTi18n::gettext('Company'),
											'user' => TTi18n::gettext('Employee'),
											'schedule' => TTi18n::gettext('Schedule'),
											'attendance' => TTi18n::gettext('Attendance'),
											'job' => TTi18n::gettext('Job Tracking'),
											'invoice' => TTi18n::gettext('Invoicing'),
											'payroll' => TTi18n::gettext('Payroll'),
											'policy' => TTi18n::gettext('Policies'),
											'report' => TTi18n::gettext('Reports'),
                                            'hr' => TTi18n::gettext('Human Resources (HR)'),
											'recruitment' => TTi18n::gettext('Recruitment'),
											);

				//Remove sections that don't apply to the current product edition.
				global $current_company;
				if ( is_object($current_company) ) {
					$product_edition = $current_company->getProductEdition();
				} else {
					$product_edition = getTTProductEdition();
				}
				
				if ( $product_edition == TT_PRODUCT_ENTERPRISE ) { //Enterprise
				} elseif ( $product_edition == TT_PRODUCT_CORPORATE ) { //Corporate
					unset( $retval['recruitment'] );
				} elseif ( $product_edition == TT_PRODUCT_COMMUNITY OR $product_edition == TT_PRODUCT_PROFESSIONAL ) { //Community or Professional
					unset( $retval['job'], $retval['invoice'], $retval['recruitment'] );
				}

				if ( defined('TIMETREX_API') == TRUE AND TIMETREX_API == TRUE ) {
					unset($retval[0]);
					$retval = Misc::addSortPrefix( $retval, 1000 );
					ksort($retval);
				}

				break;
			case 'section_group_map':
				$retval = array(
										'company' => array(
															'system',
															'company',
															'currency',
															'branch',
															'department',
															'station',
															'hierarchy',
															'authorization',
															'message',
															'other_field',
															'document',
															'help',
															'permission',
															'pay_period_schedule',
															),
										'user' 	=> array(
															'user',
															'user_preference',
															'user_tax_deduction',
                                                            'user_contact'
														),
										'schedule' 	=> array(
															'schedule',
															'recurring_schedule',
															'recurring_schedule_template',
														),
										'attendance' 	=> array(
															'punch',
															'absence',
															'accrual',
															'request',
														),
										'job' 	=> array(
															'job',
															'job_item',
															'job_report',
														),
										'invoice' 	=> array(
															'invoice_config',
															'client',
															'client_payment',
															'product',
															'tax_policy',
															'area_policy',
															'shipping_policy',
															'payment_gateway',
															'transaction',
															'invoice',
															'invoice_report'
														),
										'policy' 	=> array(
															'policy_group',
															'schedule_policy',
															'meal_policy',
															'break_policy',
															'over_time_policy',
															'premium_policy',
															'accrual_policy',
															'absence_policy',
															'round_policy',
															'exception_policy',
															'holiday_policy',
                                                            'expense_policy',
														),
										'payroll' 	=> array(
															'pay_stub_account',
															'pay_stub',
															'pay_stub_amendment',
															'wage',
															'roe',
															'company_tax_deduction',
                                                            'user_expense',
														),
										'report' 	=> array(
															'report',
                                                            'report_custom_column',
														),
                                        'hr' => array(
                                                        'qualification',
                                                        'user_education',
                                                        'user_license',
                                                        'user_skill',
                                                        'user_membership',
                                                        'user_language',
                                                        'kpi',
                                                        'user_review',
                                                        'job_vacancy',
                                                        'job_applicant',
                                                        'job_application',
                                                        'hr_report',
                                                        ),
                                        'recruitment' => array(
                                                        'job_vacancy',
                                                        'job_applicant',
                                                        'job_application',
                                                        'recruitment_report',
                                                        ),
										);

				//Remove sections that don't apply to the current product edition.
				global $current_company;
				if ( is_object($current_company) ) {
					$product_edition = $current_company->getProductEdition();
				} else {
					$product_edition = getTTProductEdition();
				}

				if ( $product_edition == TT_PRODUCT_ENTERPRISE ) { //Enterprise
				} elseif ( $product_edition == TT_PRODUCT_CORPORATE ) { //Corporate
					unset( $retval['recruitment'] );
					unset( $retval['payroll'][array_search( 'user_expense', $retval['payroll'])], $retval['policy'][array_search( 'expense_policy', $retval['policy'])] );
				} elseif ( $product_edition == TT_PRODUCT_COMMUNITY OR $product_edition == TT_PRODUCT_PROFESSIONAL ) { //Community or Professional
					unset( $retval['recruitment'], $retval['invoice'], $retval['job'] );
					unset( $retval['payroll'][array_search( 'user_expense', $retval['payroll'])], $retval['policy'][array_search( 'expense_policy', $retval['policy'])] );
				}

				break;
			case 'section':
				$retval = array(
										'system' => TTi18n::gettext('System'),
										'company' => TTi18n::gettext('Company'),
										'currency' => TTi18n::gettext('Currency'),
										'branch' => TTi18n::gettext('Branch'),
										'department' => TTi18n::gettext('Department'),
										'station' => TTi18n::gettext('Station'),
										'hierarchy' => TTi18n::gettext('Hierarchy'),
										'authorization' => TTi18n::gettext('Authorization'),
										'other_field' => TTi18n::gettext('Other Fields'),
										'document' => TTi18n::gettext('Documents'),
										'message' => TTi18n::gettext('Message'),
										'help' => TTi18n::gettext('Help'),
										'permission' => TTi18n::gettext('Permissions'),

										'user' => TTi18n::gettext('Employees'),
										'user_preference' => TTi18n::gettext('Employee Preferences'),
										'user_tax_deduction' => TTi18n::gettext('Employee Tax / Deductions'),
                                        'user_contact' => TTi18n::gettext('Employee Contact'),

										'schedule' => TTi18n::gettext('Schedule'),
										'recurring_schedule' => TTi18n::gettext('Recurring Schedule'),
										'recurring_schedule_template' => TTi18n::gettext('Recurring Schedule Template'),

										'request' => TTi18n::gettext('Requests'),
										'accrual' => TTi18n::gettext('Accruals'),
										'punch' => TTi18n::gettext('Punch'),
										'absence' => TTi18n::gettext('Absence'),

										'job' => TTi18n::gettext('Jobs'),
										'job_item' => TTi18n::gettext('Job Tasks'),
										'job_report' => TTi18n::gettext('Job Reports'),

										'invoice_config' => TTi18n::gettext('Invoice Settings'),
										'client' => TTi18n::gettext('Invoice Clients'),
										'client_payment' => TTi18n::gettext('Client Payment Methods'),
										'product' => TTi18n::gettext('Products'),
										'tax_policy' => TTi18n::gettext('Tax Policies'),
										'shipping_policy' => TTi18n::gettext('Shipping Policies'),
										'area_policy' => TTi18n::gettext('Area Policies'),
										'payment_gateway' => TTi18n::gettext('Payment Gateway'),
										'transaction' => TTi18n::gettext('Invoice Transactions'),
										'invoice' => TTi18n::gettext('Invoices'),
										'invoice_report' => TTi18n::gettext('Invoice Reports'),

										'policy_group' => TTi18n::gettext('Policy Group'),
										'schedule_policy' => TTi18n::gettext('Schedule Policies'),
										'meal_policy' => TTi18n::gettext('Meal Policies'),
										'break_policy' => TTi18n::gettext('Break Policies'),
										'over_time_policy' => TTi18n::gettext('Overtime Policies'),
										'premium_policy' => TTi18n::gettext('Premium Policies'),
										'accrual_policy' => TTi18n::gettext('Accrual Policies'),
										'absence_policy' => TTi18n::gettext('Absence Policies'),
										'round_policy' => TTi18n::gettext('Rounding Policies'),
										'exception_policy' => TTi18n::gettext('Exception Policies'),
										'holiday_policy' => TTi18n::gettext('Holiday Policies'),
                                        'expense_policy' => TTi18n::gettext('Expense Policies'),

										'pay_stub_account' => TTi18n::gettext('Pay Stub Accounts'),
										'pay_stub' => TTi18n::gettext('Employee Pay Stubs'),
										'pay_stub_amendment' => TTi18n::gettext('Employee Pay Stub Amendments'),
										'wage' => TTi18n::gettext('Wages'),
										'pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'roe' => TTi18n::gettext('Record of Employment'),
										'company_tax_deduction' => TTi18n::gettext('Company Tax / Deductions'),
                                        'user_expense' => TTi18n::gettext('Employee Expenses'),

										'report' => TTi18n::gettext('Reports'),
                                        'report_custom_column' => TTi18n::gettext('Report Custom Column'),

                                        'qualification' => TTi18n::gettext('Qualifications'),
                                        'user_education' => TTi18n::gettext('Employee Education'),
                                        'user_license' => TTi18n::gettext('Employee Licenses'),
                                        'user_skill' => TTi18n::gettext('Employee Skills'),
                                        'user_membership' => TTi18n::gettext('Employee Memberships'),
                                        'user_language' => TTi18n::gettext('Employee Language'),

                                        'kpi' => TTi18n::gettext('Key Performance Indicators'),
                                        'user_review' => TTi18n::gettext('Employee Review'),

                                        'job_vacancy' => TTi18n::gettext('Job Vacancy'),
                                        'job_applicant' => TTi18n::gettext('Job Applicant'),
                                        'job_application' => TTi18n::gettext('Job Application'),

                                        'hr_report' => TTi18n::gettext('HR Reports'),
										'recruitment_report' => TTi18n::gettext('Recruitment Reports'),
									);
				break;
			case 'name':
				$retval = array(
											'system' => array(
																'login' => TTi18n::gettext('Login Enabled'),
															),
											'company' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
																'edit_own_bank' => TTi18n::gettext('Edit Own Banking Information'),
																'login_other_user' => TTi18n::gettext('Login as Other Employee')
															),
											'user' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'edit_advanced' => TTi18n::gettext('Edit Advanced'),
																'edit_own_bank' => TTi18n::gettext('Edit Own Bank Info'),
																'edit_child_bank' => TTi18n::gettext('Edit Subordinate Bank Info'),
																'edit_bank' => TTi18n::gettext('Edit Bank Info'),
																'edit_permission_group' => TTi18n::gettext('Edit Permission Group'),
																'edit_pay_period_schedule' => TTi18n::gettext('Edit Pay Period Schedule'),
																'edit_policy_group' => TTi18n::gettext('Edit Policy Group'),
																'edit_hierarchy' => TTi18n::gettext('Edit Hierarchy'),
																'edit_own_password' => TTi18n::gettext('Edit Own Password'),
																'edit_own_phone_password' => TTi18n::gettext('Edit Own Quick Punch Password'),
																'enroll' => TTi18n::gettext('Enroll Employees'),
																'enroll_child' => TTi18n::gettext('Enroll Subordinate'),
																'timeclock_admin' => TTi18n::gettext('TimeClock Administrator'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																'view_sin' => TTi18n::gettext('View SIN/SSN'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
                                            'user_contact' => array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'view_sin' => TTi18n::gettext('View SIN/SSN'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'user_preference' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'user_tax_deduction' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'roe' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'company_tax_deduction' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
                                            'user_expense' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'authorize' => TTi18n::gettext('Authorize Expense')
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'pay_stub_account' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'pay_stub' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'pay_stub_amendment' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'wage' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'currency' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'branch' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'department' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
																'assign' => TTi18n::gettext('Assign Employees')

															),
											'station' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
																'assign' => TTi18n::gettext('Assign Employees')
															),
											'pay_period_schedule' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
																'assign' => TTi18n::gettext('Assign Employees')
															),
											'schedule' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
																'edit_branch' => TTi18n::gettext('Edit Branch Field'),
																'edit_department' => TTi18n::gettext('Edit Department Field'),
																'edit_job' => TTi18n::gettext('Edit Job Field'),
																'edit_job_item' => TTi18n::gettext('Edit Task Field'),
															),
											'other_field' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
															),
											'document' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'view_private' => TTi18n::gettext('View Private'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'edit_private' => TTi18n::gettext('Edit Private'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																'delete_private' => TTi18n::gettext('Delete Private'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
															),
											'accrual' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'policy_group' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'schedule_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'meal_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'break_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'absence_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'accrual_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'over_time_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'premium_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'round_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view' => TTi18n::gettext('View'),
																'view_own' => TTi18n::gettext('View Own'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'exception_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'holiday_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
                                            'expense_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),

											'recurring_schedule_template' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'recurring_schedule' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'request' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
																'authorize' => TTi18n::gettext('Authorize')
															),
											'punch' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
																'verify_time_sheet' => TTi18n::gettext('Verify TimeSheet'),
																'authorize' => TTi18n::gettext('Authorize TimeSheet'),
																'punch_in_out' => TTi18n::gettext('Punch In/Out'),
																'edit_transfer' => TTi18n::gettext('Edit Transfer Field'),
																'default_transfer' => TTi18n::gettext('Default Transfer On'),
																'edit_branch' => TTi18n::gettext('Edit Branch Field'),
																'edit_department' => TTi18n::gettext('Edit Department Field'),
																'edit_job' => TTi18n::gettext('Edit Job Field'),
																'edit_job_item' => TTi18n::gettext('Edit Task Field'),
																'edit_quantity' => TTi18n::gettext('Edit Quantity Field'),
																'edit_bad_quantity' => TTi18n::gettext('Edit Bad Quantity Field'),
																'edit_note' => TTi18n::gettext('Edit Note Field'),
																'edit_other_id1' => TTi18n::gettext('Edit Other ID1 Field'),
																'edit_other_id2' => TTi18n::gettext('Edit Other ID2 Field'),
																'edit_other_id3' => TTi18n::gettext('Edit Other ID3 Field'),
																'edit_other_id4' => TTi18n::gettext('Edit Other ID4 Field'),
																'edit_other_id5' => TTi18n::gettext('Edit Other ID5 Field'),
															),
											'absence' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete'),
																'edit_branch' => TTi18n::gettext('Edit Branch Field'),
																'edit_department' => TTi18n::gettext('Edit Department Field'),
																'edit_job' => TTi18n::gettext('Edit Job Field'),
																'edit_job_item' => TTi18n::gettext('Edit Task Field'),
															),
											'hierarchy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'authorization' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view' => TTi18n::gettext('View')
															),
											'message' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'add_advanced' => TTi18n::gettext('Add Advanced'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																'send_to_any' => TTi18n::gettext('Send to Any Employee'),
																'send_to_child' => TTi18n::gettext('Send to Subordinate')
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'help' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'report' => 		array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_active_shift' => TTi18n::gettext('Whos In Summary'),
																'view_user_information' => TTi18n::gettext('Employee Information'),
																'view_user_detail' => TTi18n::gettext('Employee Detail'),
																'view_pay_stub_summary' => TTi18n::gettext('Pay Stub Summary'),
																'view_payroll_export' => TTi18n::gettext('Payroll Export'),
																'view_wages_payable_summary' => TTi18n::gettext('Wages Payable Summary'),
																'view_system_log' => TTi18n::gettext('Audit Trail'),
																//'view_employee_pay_stub_summary' => TTi18n::gettext('Employee Pay Stub Summary'),
																//'view_shift_amendment_summary' => TTi18n::gettext('Shift Amendment Summary'),
																'view_timesheet_summary' => TTi18n::gettext('Timesheet Summary'),
																'view_exception_summary' => TTi18n::gettext('Exception Summary'),
																'view_accrual_balance_summary' => TTi18n::gettext('Accrual Balance Summary'),
																'view_schedule_summary' => TTi18n::gettext('Schedule Summary'),
																'view_punch_summary' => TTi18n::gettext('Punch Summary'),
																'view_remittance_summary' => TTi18n::gettext('Remittance Summary'),
																//'view_branch_summary' => TTi18n::gettext('Branch Summary'),
																'view_employee_summary' => TTi18n::gettext('Employee Summary'),
																'view_t4_summary' => TTi18n::gettext('T4 Summary'),
																'view_generic_tax_summary' => TTi18n::gettext('Generic Tax Summary'),
																'view_form941' => TTi18n::gettext('Form 941'),
																'view_form940' => TTi18n::gettext('Form 940'),
																'view_form940ez' => TTi18n::gettext('Form 940-EZ'),
																'view_form1099misc' => TTi18n::gettext('Form 1099-Misc'),
																'view_formW2' => TTi18n::gettext('Form W2 / W3'),
																'view_affordable_care' => TTi18n::gettext('Affordable Care'),
																'view_user_barcode' => TTi18n::gettext('Employee Barcodes'),
																'view_general_ledger_summary' => TTi18n::gettext('General Ledger Summary'),
                                                                'view_exception_summary' => TTi18n::gettext('Exception Summary'),
                                                                //'view_roe' => TTi18n::gettext('Record of employment'), //Disable for now as its not needed, use 'roe','view' instead.
                                                                'view_expense' => TTi18n::gettext('Expense Summary'),
															),
                                            'report_custom_column' => array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
											'job' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'job_item' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'job_report' => 		array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_job_summary' => TTi18n::gettext('Job Summary'),
																'view_job_analysis' => TTi18n::gettext('Job Analysis'),
																'view_job_payroll_analysis' => TTi18n::gettext('Job Payroll Analysis'),
																'view_job_barcode' => TTi18n::gettext('Job Barcode')
															),
											'invoice_config' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'add' => TTi18n::gettext('Add'),
																'edit' => TTi18n::gettext('Edit'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'client' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'client_payment' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																'view_credit_card' => TTi18n::gettext('View Credit Card #'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'product' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'tax_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'shipping_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'area_policy' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'payment_gateway' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'transaction' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'invoice' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
											'invoice_report' => 		array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_transaction_summary' => TTi18n::gettext('View Transaction Summary'),
															),
											'permission' => 	array(
																'enabled' => TTi18n::gettext('Enabled'),
																'view_own' => TTi18n::gettext('View Own'),
																'view_child' => TTi18n::gettext('View Subordinate'),
																'view' => TTi18n::gettext('View'),
																'add' => TTi18n::gettext('Add'),
																'edit_own' => TTi18n::gettext('Edit Own'),
																'edit_child' => TTi18n::gettext('Edit Subordinate'),
																'edit' => TTi18n::gettext('Edit'),
																'delete_own' => TTi18n::gettext('Delete Own'),
																'delete_child' => TTi18n::gettext('Delete Subordinate'),
																'delete' => TTi18n::gettext('Delete'),
																//'undelete' => TTi18n::gettext('Un-Delete')
															),
                                            'qualification' =>  array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
                                            'user_education'  =>  array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
                                            'user_license'   =>  array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
                                            'user_skill'  =>  array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
                                            'user_membership'  =>  array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
                                            'user_language'  =>  array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
                                            'kpi'  =>  array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
                                            'user_review'  =>  array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
                                            'job_vacancy' => array(
								                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')
                                                            ),
                                            'job_applicant' => array(
								                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')

                                                            ),
                                            'job_application' => array(
								                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'view_own' => TTi18n::gettext('View Own'),
                                                                'view_child' => TTi18n::gettext('View Subordinate'),
                                                                'view' => TTi18n::gettext('View'),
                                                                'add' => TTi18n::gettext('Add'),
                                                                'edit_own' => TTi18n::gettext('Edit Own'),
                                                                'edit_child' => TTi18n::gettext('Edit Subordinate'),
                                                                'edit' => TTi18n::gettext('Edit'),
                                                                'delete_own' => TTi18n::gettext('Delete Own'),
                                                                'delete_child' => TTi18n::gettext('Delete Subordinate'),
                                                                'delete' => TTi18n::gettext('Delete'),
                                                                //'undelete' => TTi18n::gettext('Un-Delete')

                                                            ),

                                            'hr_report' => array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'user_qualification' => TTi18n::gettext('Employee Qualifications'),
                                                                'user_review' => TTi18n::getText('Employee Review'),
                                                                'user_recruitment' => TTi18n::gettext('Employee Recruitment'),
                                                            ),
                                            'recruitment_report' => array(
                                                                'enabled' => TTi18n::gettext('Enabled'),
                                                                'user_recruitment' => TTi18n::gettext('Employee Recruitment'),
                                                            ),
									);
				break;

		}

		return $retval;
	}

	function setCompany( $id ) {
		$this->company_id = $id;
		return TRUE;
	}
	function getCompany() {
		if ( $this->company_id != '' ) {
			return $this->company_id;
		} else {
			$company_id = $this->getPermissionControlObject()->getCompany();

			return $company_id;
		}
	}

	function getPermissionControlObject() {
		if ( is_object($this->permission_control_obj) ) {
			return $this->permission_control_obj;
		} else {

			$pclf = TTnew( 'PermissionControlListFactory' );
			$pclf->getById( $this->getPermissionControl() );

			if ( $pclf->getRecordCount() == 1 ) {
				$this->permission_control_obj = $pclf->getCurrent();

				return $this->permission_control_obj;
			}

			return FALSE;
		}
	}

	function getPermissionControl() {
		if ( isset($this->data['permission_control_id']) ) {
			return $this->data['permission_control_id'];
		}

		return FALSE;
	}
	function setPermissionControl($id) {
		$id = trim($id);

		$pclf = TTnew( 'PermissionControlListFactory' );

		if ( $id != 0
				OR
				$this->Validator->isResultSetWithRows(	'permission_control',
													$pclf->getByID($id),
													TTi18n::gettext('Permission Group is invalid')
													) ) {

			$this->data['permission_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getSection() {
		if ( isset($this->data['section']) ) {
			return $this->data['section'];
		}

		return FALSE;
	}
	function setSection($section, $disable_error_check = FALSE ) {
		$section = trim($section);

		if ( $disable_error_check === TRUE
				OR
				$this->Validator->inArrayKey(	'section',
											$section,
											TTi18n::gettext('Incorrect section'),
											$this->getOptions('section')) ) {

			$this->data['section'] = $section;

			return TRUE;
		}

		return FALSE;
	}

	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name, $disable_error_check = FALSE ) {
		$name = trim($name);

		//Debug::Arr($this->getOptions('name', $this->getSection() ), 'Options: ', __FILE__, __LINE__, __METHOD__,10);
		if ( $disable_error_check === TRUE
				OR
				$this->Validator->inArrayKey(	'name',
											$name,
											TTi18n::gettext('Incorrect permission name'),
											$this->getOptions('name', $this->getSection() ) ) ) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getValue() {
		if ( isset($this->data['value']) AND $this->data['value'] == 1 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	function setValue($value) {
		$value = trim($value);

		//Debug::Arr($value, 'Value: ', __FILE__, __LINE__, __METHOD__,10);

		if 	(	$this->Validator->isLength(		'value',
												$value,
												TTi18n::gettext('Value is invalid'),
												1,
												255) ) {

			$this->data['value'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function filterPresetPermissions( $preset, $filter_sections = FALSE, $filter_permissions = FALSE ) {
		//Debug::Arr( array($filter_sections, $filter_permissions), 'Preset: '. $preset, __FILE__, __LINE__, __METHOD__,10);
		if ( $preset == 0 ) {
			$preset = 40; //Administrator.
		}

		$filter_sections = Misc::trimSortPrefix( $filter_sections, TRUE );
		if ( !is_array( $filter_sections ) ) {
			$filter_sections = FALSE;
		}

		//Always add enabled,system to the filter_permissions.
		$filter_permissions[] = 'enabled';
		$filter_permissions[] = 'login';
		$filter_permissions = Misc::trimSortPrefix( $filter_permissions, TRUE );
		if ( !is_array( $filter_permissions ) ) {
			$filter_permissions = FALSE;
		}

		//Get presets based on all flags.
		$preset_permissions = $this->getPresetPermissions( $preset, array_keys( $this->getOptions('preset_flags') ) );
		//Debug::Arr($preset_permissions, 'Preset Permissions: ', __FILE__, __LINE__, __METHOD__,10);

		if ( is_array($preset_permissions) ) {
			foreach($preset_permissions as $section => $permissions) {
				if ( $filter_sections === FALSE OR in_array( $section, $filter_sections ) ) {
					foreach($permissions as $name => $value) {
						//Other permission basically matches anything that is not in filter list. Things like edit_own_password, etc...
						if ( $filter_permissions === FALSE OR in_array( $name, $filter_permissions ) OR ( in_array( 'other', $filter_permissions ) AND !in_array( $name, $filter_permissions ) ) ) {
							//Debug::Text('aSetting Permission - Section: '. $section .' Name: '. $name .' Value: '. (int)$value, __FILE__, __LINE__, __METHOD__,10);
							$retarr[$section][$name] = $value;
						} else {
							//Debug::Text('bNOT Setting Permission - Section: '. $section .' Name: '. $name .' Value: '. (int)$value, __FILE__, __LINE__, __METHOD__,10);
						}
					}
				}
			}
		}

		if ( isset($retarr) ) {
			Debug::Arr($retarr, 'Filtered Permissions', __FILE__, __LINE__, __METHOD__,10);
			return $retarr;
		}

		return FALSE;
	}

	function getPresetPermissions( $preset, $preset_flags = array() ) {
		$key = Option::getByValue($preset, $this->getOptions('preset') );
		if ($key !== FALSE) {
			$preset = $key;
		}

		$preset_flags[] = 0; //Always add system presets.
		asort($preset_flags);

		Debug::Text('Preset: '. $preset, __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($preset_flags, 'Preset Flags... ', __FILE__, __LINE__, __METHOD__,10);

		if ( !isset($preset) OR $preset == '' OR $preset == -1 ) {
			Debug::Text('No Preset set... Skipping!', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		$preset_permissions = array(
									10 => //Role: Regular Employee
											array(
													0 => //Module: System
														array(
															'system' => array(
																				'login' => TRUE,
																			),
															'user' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																				'edit_own' => TRUE,
																				'edit_own_password' => TRUE,
																				'edit_own_phone_password' => TRUE,
																			),
															'user_preference' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																				'add' => TRUE,
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
																			),
															'request' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																				'add' => TRUE,
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
																			),
															'message' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																				'add' => TRUE,
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
																			),
															'help' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																			),

														),
													10 => //Module: Scheduling
														array(
															'schedule' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																			),
															'accrual' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE
																			),
															'absence' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																			),
														),
													20 => //Module: Time & Attendance
														array(
															'punch' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																				'add' => TRUE,
																				'verify_time_sheet' => TRUE,
																				'punch_in_out' => TRUE,
																				'edit_transfer' => TRUE,
																				'edit_branch' => TRUE,
																				'edit_department' => TRUE,
																				'edit_note' => TRUE,
																				'edit_other_id1' => TRUE,
																				'edit_other_id2' => TRUE,
																				'edit_other_id3' => TRUE,
																				'edit_other_id4' => TRUE,
																				'edit_other_id5' => TRUE,
																			),
															'accrual' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE
																			),
															'absence' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																			),

														),
													30 => //Module: Payroll
														array(
															'user' => 	array(
																				'enabled' => TRUE,
																				'edit_own_bank' => TRUE,
																			),
															'pay_stub' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																			),
														),
													40 => //Module: Job Costing
														array(
															'punch' =>	array(
																				'edit_job' => TRUE,
																				'edit_job_item' => TRUE,
																				'edit_quantity' => TRUE,
																				'edit_bad_quantity' => TRUE,
																			),
															'job' => 	array(
																				'enabled' => TRUE,
																			),
														),
													50 => //Module: Document Management
														array(
															'document' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																			),
														),
													60 => //Module: Invoicing
														array(
														),
													70 => //Module: Human Resources
														array(
														),
													75 => //Module: Recruitement
														array(
														),
													80 => //Module: Expenses
														array(
															'user_expense' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																				'add' => TRUE,
																				//'edit_own' => TRUE, //Don't allow editing expenses once they are submitted?
																				'delete_own' => TRUE,
																			),
														),
											),
									12 => //Role: Regular Employee (Manual Entry)
											array(
													20 => //Module: Time & Attendance
														array(
															'punch' => 	array(
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
																			),
															'absence' => 	array(
																				'add' => TRUE,
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
																			),
														),
												),
									18 => //Role: Supervisor (Subordinates Only)
											array(
													0 => //Module: System
														array(
															'user' => 	array(
																				'add' => TRUE, //Can only add user with permissions level equal or lower.
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'edit_advanced' => TRUE,
																				'enroll_child' => TRUE,
																				'delete_child' => TRUE,
																				'edit_pay_period_schedule' => TRUE,
																				'edit_permission_group' => TRUE,
																				'edit_policy_group' => TRUE,
																				'edit_hierarchy' => TRUE,
																			),
															'user_preference' => 	array(
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																			),
															'request' => 	array(
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																				'authorize' => TRUE
																			),
															'authorization' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE
																			),
															'message' => 	array(
																				'add_advanced' => TRUE,
																				'send_to_child' => TRUE,
																			),
															'report' => 		array(
																				'enabled' => TRUE,
																				'view_user_information' => TRUE,
																				'view_user_detail' => TRUE,
																				'view_user_barcode' => TRUE,
																			),
                                                            'report_custom_column' =>   array(
                                                                                'enabled'   => TRUE,
                                                                                'view_own' => TRUE,
																				'add' => TRUE,
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
                                                                            ),
														),
													10 => //Module: Scheduling
														array(
															'schedule' => 	array(
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																				'edit_branch' => TRUE,
																				'edit_department' => TRUE,
																			),
															'recurring_schedule_template' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																				'add' => TRUE,
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
																			),
															'recurring_schedule' => 	array(
																				'enabled' => TRUE,
																				'view_child' => TRUE,
																				'add' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'absence' => 	array(
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																				'edit_branch' => TRUE,
																				'edit_department' => TRUE,
																			),
															'accrual' => 	array(
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_own' => FALSE,
																				'edit_child' => TRUE,
																				'delete_own' => FALSE,
																				'delete_child' => TRUE,
																			),
															'report' => 		array(
																				'view_schedule_summary' => TRUE,
																				'view_accrual_balance_summary' => TRUE,
																			),

														),
													20 => //Module: Time & Attendance
														array(
															'punch' => 	array(
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																				'authorize' => TRUE
																			),
															'absence' => 	array(
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_own' => FALSE,
																				'edit_child' => TRUE,
																				'edit_branch' => TRUE,
																				'edit_department' => TRUE,
																				'delete_own' => FALSE,
																				'delete_child' => TRUE,
																			),
															'accrual' => 	array(
																				'view_child' => TRUE,
																				'add' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'report' => 		array(
																				'view_active_shift' => TRUE,
																				'view_timesheet_summary' => TRUE,
																				'view_punch_summary' => TRUE,
																				'view_exception_summary' => TRUE,
																				'view_accrual_balance_summary' => TRUE,
																			),

														),
													30 => //Module: Payroll
														array(
														),
													40 => //Module: Job Costing
														array(
															'schedule' => 	array(
																				'edit_job' => TRUE,
																				'edit_job_item' => TRUE,
																			),
															'absence' => 	array(
																				'edit_job' => TRUE,
																				'edit_job_item' => TRUE,
																			),
															'job' => 	array(
																				'add' => TRUE,
																				'view_own' => TRUE,
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
																			),
															'job_item' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
																			),
															'job_report' => array(
																				'enabled' => TRUE,
																				'view_job_summary' => TRUE,
																				'view_job_analysis' => TRUE,
																				'view_job_payroll_analysis' => TRUE,
																				'view_job_barcode' => TRUE
																			),
														),
													50 => //Module: Document Management
														array(
															'document' => 	array(
																				'add' => TRUE,
																				'view_private' => TRUE,
																				'edit' => TRUE,
																				'edit_private' => TRUE,
																				'delete' => TRUE,
																				'delete_private' => TRUE,
																			),

														),
													60 => //Module: Invoicing
														array(
															'client' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'client_payment' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'transaction' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'invoice' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
														),
													70 => //Module: Human Resources
														array(
															'user_contact' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE, //Can only add user with permissions level equal or lower.
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'qualification' => array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'user_education' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'user_license' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'user_skill' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'user_membership' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'user_language' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'kpi' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'user_review' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'hr_report' =>  array(
																				'enabled' => TRUE,
																				'user_qualification' => TRUE,
																				'user_review' => TRUE,
																			),

														),
													75 => //Module: Recruitement
														array(
															'job_vacancy' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'job_applicant' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
                                                            'job_application' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'view_child' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																			),
															'recruitment_report' =>  array(
																				'enabled' => TRUE,
																				'user_recruitment' => TRUE,
																			),

														),
													80 => //Module: Expenses
														array(
															'user_expense' => 	array(
																				'view_child' => TRUE,
																				'add' => TRUE,
																				'edit_child' => TRUE,
																				'delete_child' => TRUE,
																				'authorize' => TRUE,
																			),
														),
											),
									20 => //Role: Supervisor (All Employees)
											array(
													0 => //Module: System
														array(
															'user' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'enroll' => TRUE,
																				'delete' => TRUE
																			),
															'user_preference' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																			),
															'request' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'message' => 	array(
																				'send_to_any' => TRUE,
																			),
														),
													10 => //Module: Scheduling
														array(
															'schedule' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE
																			),
															'recurring_schedule_template' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'recurring_schedule' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'absence' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'accrual' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),

														),
													20 => //Module: Time & Attendance
														array(
															'punch' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'absence' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																				'edit_own' => TRUE,
																				'delete_own' => TRUE,
																			),
															'accrual' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
														),
													30 => //Module: Payroll
														array(
														),
													40 => //Module: Job Costing
														array(
															'job' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'job_item' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
														),
													50 => //Module: Document Management
														array(
														),
													60 => //Module: Invoicing
														array(
														),
													70 => //Module: Human Resources
														array(
															'user_contact' => array(
																				'add' => TRUE,
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'qualification' => array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE
																			),
															'user_education' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'user_license' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'user_skill' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'user_membership' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'user_language' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'kpi' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'user_review' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
														),
													75 => //Module: Recruitement
														array(
															'job_vacancy' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'job_applicant' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
                                                            'job_application' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
														),
													80 => //Module: Expenses
														array(
															'user_expense' => 	array(
																				'view' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
														),
											),
									25 => //Role: HR Manager
											array(
													0 => //Module: System
														array(
														),
													10 => //Module: Scheduling
														array(
														),
													20 => //Module: Time & Attendance
														array(
														),
													30 => //Module: Payroll
														array(
														),
													40 => //Module: Job Costing
														array(
														),
													50 => //Module: Document Management
														array(
														),
													60 => //Module: Invoicing
														array(
														),
													70 => //Module: Human Resources
														array(
															'qualification' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
														),
													75 => //Module: Recruitement
														array(
														),
													80 => //Module: Expenses
														array(
														),
											),
									30 => //Role: Payroll Administrator
											array(
													0 => //Module: System
														array(
															'company' => 	array(
																				'enabled' => TRUE,
																				'view_own' => TRUE,
																				'edit_own' => TRUE,
																				'edit_own_bank' => TRUE
																			),
															'user' => 	array(
																				'add' => TRUE,
																				'edit_bank' => TRUE,
																				'view_sin' => TRUE,
																			),
															'wage' => 		array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE
																			),
															'pay_period_schedule' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																				'assign' => TRUE
																			),
															'report' => 		array(
																				'view_system_log' => TRUE,
																				'view_employee_summary' => TRUE,
																			),
														),
													10 => //Module: Scheduling
														array(
														),
													20 => //Module: Time & Attendance
														array(
														),
													30 => //Module: Payroll
														array(
															'user_tax_deduction' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'roe' => 		array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'company_tax_deduction' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'pay_stub_account' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE
																			),
															'pay_stub' => 	array(
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE
																			),
															'pay_stub_amendment' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE
																			),
															'report' => 	array(
																				'view_pay_stub_summary' => TRUE,
																				'view_payroll_export' => TRUE,
																				//'view_employee_pay_stub_summary' => TRUE,
																				'view_remittance_summary' => TRUE,
																				'view_wages_payable_summary' => TRUE,
																				'view_t4_summary' => TRUE,
																				'view_generic_tax_summary' => TRUE,
																				'view_form941' => TRUE,
																				'view_form940' => TRUE,
																				'view_form940ez' => TRUE,
																				'view_form1099misc' => TRUE,
																				'view_formW2' => TRUE,
																				'view_affordable_care' => TRUE,
																				'view_general_ledger_summary' => TRUE,
																			),
														),
													40 => //Module: Job Costing
														array(
														),
													50 => //Module: Document Management
														array(
														),
													60 => //Module: Invoicing
														array(
															'product' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'tax_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'shipping_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'area_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'payment_gateway' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'invoice_report' => 	array(
																				'enabled' => TRUE,
																				'view_transaction_summary' => TRUE,
																			),
														),
													70 => //Module: Human Resources
														array(
														),
													75 => //Module: Recruitement
														array(
														),
													80 => //Module: Expenses
														array(
															'report' => 	array(
																				'view_expense' => TRUE
																			),
														),
											),
									40 => //Role: Administrator
											array(
													0 => //Module: System
														array(
															'user' => 	array(
																				'timeclock_admin' => TRUE,
																			),
															'policy_group' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'schedule_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'meal_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'break_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'over_time_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'premium_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'accrual_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'absence_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'round_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'exception_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'holiday_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'round_policy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'currency' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'branch' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'department' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																				'assign' => TRUE
																			),
															'station' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																				'assign' => TRUE
																			),
															'report' => 		array(
																				//'view_shift_actual_time' => TRUE,
																			),
															'hierarchy' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'other_field' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
															'permission' => 	array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
                                                            'report_custom_column' => array(
																				'view' => TRUE,
																				'edit' => TRUE,
                                                                                'delete' => TRUE,
                                                                            ),
														),
													10 => //Module: Scheduling
														array(
														),
													20 => //Module: Time & Attendance
														array(
														),
													30 => //Module: Payroll
														array(
														),
													40 => //Module: Job Costing
														array(
														),
													50 => //Module: Document Management
														array(
														),
													60 => //Module: Invoicing
														array(
															'invoice_config' => 	array(
																				'enabled' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),

														),
													70 => //Module: Human Resources
														array(
														),
													75 => //Module: Recruitement
														array(
														),
													80 => //Module: Expenses
														array(
															'expense_policy' => array(
																				'enabled' => TRUE,
																				'view' => TRUE,
																				'add' => TRUE,
																				'edit' => TRUE,
																				'delete' => TRUE,
																			),
														),
											),
									);

		$retarr = array();

		//Loop over each preset adding the permissions together for that preset and the role that is selected.
		$preset_options = array_keys( Misc::trimSortPrefix( $this->getOptions('preset') ) );
		if ( is_array($preset_options) ) {
			foreach( $preset_options as $preset_option ) {
				if ( isset($preset_permissions[$preset_option]) AND $preset_option <= $preset ) {
					foreach( $preset_flags as $preset_flag ) {
						if ( isset($preset_permissions[$preset_option][$preset_flag]) ) {
							Debug::Text('Applying Preset: '. $preset_option .' Preset Flag: '. $preset_flag, __FILE__, __LINE__, __METHOD__,10);
							$retarr = Misc::arrayMergeRecursive( $retarr, $preset_permissions[$preset_option][$preset_flag] );
						}
					}
				}
			}
		}

		return $retarr;
	}

	//This is used by CompanyFactory to create the initial permissions when creating a new company.
	//Also by the Quick Start wizard.
	function applyPreset($permission_control_id, $preset, $preset_flags) {
		$preset_permissions = $this->getPresetPermissions( $preset, $preset_flags );

		if ( !is_array($preset_permissions) ) {
			return FALSE;
		}

		$this->setPermissionControl( $permission_control_id );

		$product_edition = $this->getPermissionControlObject()->getCompanyObject()->getProductEdition();
		//Debug::Arr($preset_flags, 'Preset: '. $preset .' Product Edition: '. $product_edition, __FILE__, __LINE__, __METHOD__,10);

		$pf = TTnew( 'PermissionFactory' );
		$pf->StartTransaction();

		//Delete all previous permissions for this user.
		$this->deletePermissions( $this->getCompany(), $permission_control_id );
		
		foreach($preset_permissions as $section => $permissions) {
			foreach($permissions as $name => $value) {
				if ( $pf->isIgnore( $section, $name, $product_edition ) == FALSE ) {
					//Debug::Text('Setting Permission - Section: '. $section .' Name: '. $name .' Value: '. (int)$value, __FILE__, __LINE__, __METHOD__,10);
					$pf->setPermissionControl( $permission_control_id );
					$pf->setSection( $section );
					$pf->setName( $name );
					$pf->setValue( (int)$value );
					if ( $pf->isValid() ) {
						$pf->save();
					} else {
						Debug::Text('ERROR: Setting Permission - Section: '. $section .' Name: '. $name .' Value: '. (int)$value, __FILE__, __LINE__, __METHOD__,10);
					}
				}
			}
		}

		//Clear cache for all users assigned to this permission_control_id
		$pclf = TTnew( 'PermissionControlListFactory' );
		$pclf->getById( $permission_control_id );
		if ( $pclf->getRecordCount() > 0 ) {
			$pc_obj = $pclf->getCurrent();

			if ( is_array($pc_obj->getUser() ) ) {
				foreach( $pc_obj->getUser() as $user_id ) {
					$pf->clearCache( $user_id, $this->getCompany() );
				}
			}
		}
		unset($pclf, $pc_obj, $user_id);

		//$pf->FailTransaction();
		$pf->CommitTransaction();

		return TRUE;
	}

	function deletePermissions( $company_id, $permission_control_id ){
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $permission_control_id == '' ) {
			return FALSE;
		}

		$plf = TTnew( 'PermissionListFactory' );
		$plf->getByCompanyIDAndPermissionControlId( $company_id, $permission_control_id );
		foreach($plf as $permission_obj) {
			$permission_obj->delete(TRUE);
			$this->removeCache( $this->getCacheID() );
		}

		return TRUE;
	}

	static function isIgnore( $section, $name = NULL, $product_edition = 10 ) {
		global $current_company;

		//Ignore by default
		if ( $section == '' ) {
			return TRUE;
		}

		//Debug::Text(' Product Edition: '. $product_edition .' Primary Company ID: '. PRIMARY_COMPANY_ID, __FILE__, __LINE__, __METHOD__,10);
		if ( $product_edition == TT_PRODUCT_ENTERPRISE ) { //Enterprise
			//Company ignore permissions must be enabled always, and unset below if this is the primary company
			$ignore_permissions = array('help' => 'ALL',
										'company' => array('add','delete','delete_own','undelete','view','edit','login_other_user'),
										);
		} elseif ( $product_edition == TT_PRODUCT_CORPORATE ) { //Corporate
			//Company ignore permissions must be enabled always, and unset below if this is the primary company
			$ignore_permissions = array('help' => 'ALL',
										'company' => array('add','delete','delete_own','undelete','view','edit','login_other_user'),
                                        'job_vacancy' => 'ALL',
                                        'job_applicant' => 'ALL',
                                        'job_application' => 'ALL',
                                        'user_expense' => 'ALL',
                                        'expense_policy' => 'ALL',
                                        'report' => array('view_expense'),
                                        'recruitment_report' => 'ALL',
										);
		} elseif ( $product_edition == TT_PRODUCT_COMMUNITY OR $product_edition == TT_PRODUCT_PROFESSIONAL ) { //Community or Professional
			//Company ignore permissions must be enabled always, and unset below if this is the primary company
			$ignore_permissions = array('help' => 'ALL',
										'company' => array('add','delete','delete_own','undelete','view','edit','login_other_user'),
										'schedule' => array('edit_job','edit_job_item'),
										'punch' => array('edit_job','edit_job_item','edit_quantity','edit_bad_quantity'),
										'job_item' => 'ALL',
										'invoice_config' => 'ALL',
										'client' => 'ALL',
										'client_payment' => 'ALL',
										'product' => 'ALL',
										'tax_policy' => 'ALL',
										'area_policy' => 'ALL',
										'shipping_policy' => 'ALL',
										'payment_gateway' => 'ALL',
										'transaction' => 'ALL',
										'job_report' => 'ALL',
										'invoice_report' => 'ALL',
										'invoice' => 'ALL',
										'job' => 'ALL',
										'document' => 'ALL',
                                        'job_vacancy' => 'ALL',
                                        'job_applicant' => 'ALL',
                                        'job_application' => 'ALL',
                                        'user_expense' => 'ALL',
                                        'expense_policy' => 'ALL',
                                        'report' => array('view_expense'),
										'recruitment_report' => 'ALL',
										);
		}

		//If they are currently logged in as the primary company ID, allow multiple company permissions.
		if ( isset($current_company) AND $current_company->getProductEdition() > TT_PRODUCT_COMMUNITY AND $current_company->getId() == PRIMARY_COMPANY_ID ) {
			unset($ignore_permissions['company']);
		}

		if ( isset($ignore_permissions[$section])
				AND
					(
						(
							$name != ''
							AND
							($ignore_permissions[$section] == 'ALL'
							OR ( is_array($ignore_permissions[$section]) AND in_array($name, $ignore_permissions[$section]) ) )
						)
						OR
						(
							$name == ''
							AND
							$ignore_permissions[$section] == 'ALL'
						)
					)

					) {
			//Debug::Text(' IGNORING... Section: '. $section .' Name: '. $name, __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		} else {
			//Debug::Text(' NOT IGNORING... Section: '. $section .' Name: '. $name, __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}
	}

	function preSave() {
		//Just update any existing permissions. It would probably be faster to delete them all and re-insert though.
		$plf = TTnew( 'PermissionListFactory' );
		$obj = $plf->getByCompanyIdAndPermissionControlIdAndSectionAndName( $this->getCompany(), $this->getPermissionControl(), $this->getSection(), $this->getName() )->getCurrent();
		$this->setId( $obj->getId() );

		return TRUE;
	}

	function getCacheID() {
		$cache_id = 'permission_query_'.$this->getSection().$this->getName().$this->getPermissionControl().$this->getCompany();

		return $cache_id;
	}

	function clearCache( $user_id, $company_id ) {
		Debug::Text(' Clearing Cache for User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);

		$cache_id = 'permission_all'.$user_id.$company_id;
		return $this->removeCache( $cache_id );
	}

	function postSave() {
		//$cache_id = 'permission_query_'.$this->getSection().$this->getName().$this->getUser().$this->getCompany();
		//$this->removeCache( $this->getCacheID() );

		return TRUE;
	}

	function addLog( $log_action ) {
		if ( $this->getValue() == TRUE ) {
			$value_display =  TTi18n::getText( 'ALLOW' );
		} else {
			$value_display =  TTi18n::getText( 'DENY' );
		}

		return TTLog::addEntry( $this->getPermissionControl(), $log_action, TTi18n::getText('Section').': '. Option::getByKey($this->getSection(), $this->getOptions('section') ) .' Name: '. Option::getByKey( $this->getName(), $this->getOptions('name', $this->getSection() ) ) .' Value: '. $value_display , NULL, $this->getTable() );
	}
}
?>
