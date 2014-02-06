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
 * $Revision: 2095 $
 * $Id: Sort.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Modules\Report
 */
class T4ASummaryReport extends Report {

	protected $user_ids = array();

	function __construct() {
		$this->title = TTi18n::getText('T4A Summary Report');
		$this->file_name = 't4a_summary';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_t4_summary', $user_id, $company_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	protected function _getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'output_format':
				$retval = array_merge( parent::getOptions('default_output_format'),
									array(
										'-1100-pdf_form' => TTi18n::gettext('Employee (One Employee/Page)'),
										'-1110-pdf_form_government' => TTi18n::gettext('Government (Multiple Employees/Page)'),
										'-1120-efile_xml' => TTi18n::gettext('eFile'),
										)
									);
				break;
			case 'default_setup_fields':
				$retval = array(
										'template',
										'time_period',
										'columns',
							   );

				break;
			case 'setup_fields':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-template' => TTi18n::gettext('Template'),
										'-1010-time_period' => TTi18n::gettext('Time Period'),

										'-2010-user_status_id' => TTi18n::gettext('Employee Status'),
										'-2020-user_group_id' => TTi18n::gettext('Employee Group'),
										'-2030-user_title_id' => TTi18n::gettext('Employee Title'),
										'-2040-include_user_id' => TTi18n::gettext('Employee Include'),
										'-2050-exclude_user_id' => TTi18n::gettext('Employee Exclude'),
										'-2060-default_branch_id' => TTi18n::gettext('Default Branch'),
										'-2070-default_department_id' => TTi18n::gettext('Default Department'),
                                        '-3000-custom_filter' => TTi18n::gettext('Custom Filter'),

										'-5000-columns' => TTi18n::gettext('Display Columns'),
										'-5010-group' => TTi18n::gettext('Group By'),
										'-5020-sub_total' => TTi18n::gettext('SubTotal By'),
										'-5030-sort' => TTi18n::gettext('Sort By'),
							   );
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				//$retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 13, TRUE );
				$retval = array();
				break;
            case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'T4ASummaryReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'T4ASummaryReport', 'custom_column' );
				}
                break;
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'T4ASummaryReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'T4ASummaryReport', 'custom_column' );
					if ( is_array($report_static_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_static_custom_column_labels, 9700 );
					}
				}
                break;
            case 'formula_columns':
                $retval = TTMath::formatFormulaColumns( array_merge( array_diff( $this->getOptions('static_columns'), (array)$this->getOptions('report_static_custom_column') ), $this->getOptions('dynamic_columns') ) );
                break; 
            case 'filter_columns':
                $retval = TTMath::formatFormulaColumns( array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') ) );
                break;
			case 'static_columns':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1001-middle_name' => TTi18n::gettext('Middle Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-full_name' => TTi18n::gettext('Full Name'),
										'-1030-employee_number' => TTi18n::gettext('Employee #'),
										'-1035-sin' => TTi18n::gettext('SIN/SSN'),
										'-1040-status' => TTi18n::gettext('Status'),
										'-1050-title' => TTi18n::gettext('Title'),
										'-1060-province' => TTi18n::gettext('Province/State'),
										'-1070-country' => TTi18n::gettext('Country'),
										'-1080-group' => TTi18n::gettext('Group'),
										'-1090-default_branch' => TTi18n::gettext('Default Branch'),
										'-1100-default_department' => TTi18n::gettext('Default Department'),
										'-1110-currency' => TTi18n::gettext('Currency'),
										//'-1111-current_currency' => TTi18n::gettext('Current Currency'),

										//'-1110-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
										//'-1120-pending_request' => TTi18n::gettext('Pending Requests'),

										//Handled in date_columns above.
										//'-1450-pay_period' => TTi18n::gettext('Pay Period'),

										'-1400-permission_control' => TTi18n::gettext('Permission Group'),
										'-1410-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1420-policy_group' => TTi18n::gettext('Policy Group'),
							   );

				$retval = array_merge( $retval, $this->getOptions('date_columns'), (array)$this->getOptions('report_static_custom_column') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used
										'-2100-income' => TTi18n::gettext('Income (14)'),
										'-2110-tax' => TTi18n::gettext('Income Tax (22)'),
										'-2120-employee_cpp' => TTi18n::gettext('Employee CPP (16)'),
										'-2125-ei_earnings' => TTi18n::gettext('EI Insurable Earnings (24)'),
										'-2126-cpp_earnings' => TTi18n::gettext('CPP Pensionable Earnings (26)'),
										'-2130-employee_ei' => TTi18n::gettext('Employee EI (18)'),
										'-2140-union_dues' => TTi18n::gettext('Union Dues (44)'),
										'-2150-employer_cpp' => TTi18n::gettext('Employer CPP'),
										'-2160-employer_ei' => TTi18n::gettext('Employer EI'),
										'-2170-rpp' => TTi18n::gettext('RPP Contributions (20)'),
										'-2180-charity' => TTi18n::gettext('Charity Donations (46)'),
										'-2190-pension_adjustment' => TTi18n::gettext('Pension Adjustment (52)'),
										'-2200-other_box_0' => TTi18n::gettext('Other Box 1'),
										'-2210-other_box_1' => TTi18n::gettext('Other Box 2'),
										'-2220-other_box_2' => TTi18n::gettext('Other Box 3'),
										'-2220-other_box_3' => TTi18n::gettext('Other Box 4'),
										'-2220-other_box_4' => TTi18n::gettext('Other Box 5'),
										'-2220-other_box_5' => TTi18n::gettext('Other Box 6'),
							);
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = array_merge( $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_custom_column') );
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						$retval[$column] = 'currency';
					}
				}
				break;
			case 'aggregates':
				$retval = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') ) ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								$retval[$column] = 'sum';
						}
					}
				}
				break;
			case 'type':
				$retval = array(
											'-1010-O' => TTi18n::getText('Original'),
											'-1020-A' => TTi18n::getText('Amended'),
											'-1030-C' => TTi18n::getText('Cancel'),
										);
				break;
			case 'templates':
				$retval = array(
										//'-1010-by_month' => TTi18n::gettext('by Month'),
										'-1020-by_employee' => TTi18n::gettext('by Employee'),
										'-1030-by_branch' => TTi18n::gettext('by Branch'),
										'-1040-by_department' => TTi18n::gettext('by Department'),
										'-1050-by_branch_by_department' => TTi18n::gettext('by Branch/Department'),

										//'-1060-by_month_by_employee' => TTi18n::gettext('by Month/Employee'),
										//'-1070-by_month_by_branch' => TTi18n::gettext('by Month/Branch'),
										//'-1080-by_month_by_department' => TTi18n::gettext('by Month/Department'),
										//'-1090-by_month_by_branch_by_department' => TTi18n::gettext('by Month/Branch/Department'),
							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'default':
							//Proper settings to generate the form.
							//$retval['-1010-time_period']['time_period'] = 'last_quarter';

							$retval['columns'] = $this->getOptions('columns');

							$retval['group'][] = 'date_quarter_month';

							$retval['sort'][] = array('date_quarter_month' => 'asc');

							$retval['other']['grand_total'] = TRUE;

							break;
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
							$retval['-1010-time_period']['time_period'] = 'last_year';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode('+', $template );
							if ( is_array($template_keywords) ) {
								foreach( $template_keywords as $template_keyword ) {
									Debug::Text(' Keyword: '. $template_keyword, __FILE__, __LINE__, __METHOD__,10);

									switch( $template_keyword ) {
										//Columns

										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_month':
											$retval['columns'][] = 'date_month';

											$retval['group'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											break;
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'default_branch';

											$retval['sort'][] = array('default_branch' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_department';

											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_month_by_employee':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_month_by_branch':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_branch' => 'asc');
											break;
										case 'by_month_by_department':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_month_by_branch_by_department':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';
											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;

									}
								}
							}

							$retval['columns'] = array_merge( $retval['columns'], array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) ) );

							break;
					}
				}

				//Set the template dropdown as well.
				$retval['-1000-template'] = $template;

				//Add sort prefixes so Flex can maintain order.
				if ( isset($retval['filter']) ) {
					$retval['-5000-filter'] = $retval['filter'];
					unset($retval['filter']);
				}
				if ( isset($retval['columns']) ) {
					$retval['-5010-columns'] = $retval['columns'];
					unset($retval['columns']);
				}
				if ( isset($retval['group']) ) {
					$retval['-5020-group'] = $retval['group'];
					unset($retval['group']);
				}
				if ( isset($retval['sub_total']) ) {
					$retval['-5030-sub_total'] = $retval['sub_total'];
					unset($retval['sub_total']);
				}
				if ( isset($retval['sort']) ) {
					$retval['-5040-sort'] = $retval['sort'];
					unset($retval['sort']);
				}
				Debug::Arr($retval, ' Template Config for: '. $template, __FILE__, __LINE__, __METHOD__,10);

				break;
			default:
				//Call report parent class options function for options valid for all reports.
				$retval = $this->__getOptions( $name );
				break;
		}

		return $retval;
	}

	function getFormObject() {
		if ( !isset($this->form_obj['gf']) OR !is_object($this->form_obj['gf']) ) {
			//
			//Get all data for the form.
			//
			require_once( Environment::getBasePath() .'/classes/GovernmentForms/GovernmentForms.class.php');

			$gf = new GovernmentForms();

			$this->form_obj['gf'] = $gf;
			return $this->form_obj['gf'];
		}

		return $this->form_obj['gf'];
	}

	function getT4AObject() {
		if ( !isset($this->form_obj['t4a']) OR !is_object($this->form_obj['t4a']) ) {
			$this->form_obj['t4a'] = $this->getFormObject()->getFormObject( 'T4A', 'CA' );
			return $this->form_obj['t4a'];
		}

		return $this->form_obj['t4'];
	}

	function getT4ASumObject() {
		if ( !isset($this->form_obj['t4asum']) OR !is_object($this->form_obj['t4asum']) ) {
			$this->form_obj['t4asum'] = $this->getFormObject()->getFormObject( 'T4ASum', 'CA' );
			return $this->form_obj['t4asum'];
		}

		return $this->form_obj['t4asum'];
	}

	function getT619Object() {
		if ( !isset($this->form_obj['t619']) OR !is_object($this->form_obj['t619']) ) {
			$this->form_obj['t619'] = $this->getFormObject()->getFormObject( 'T619', 'CA' );
			return $this->form_obj['t619'];
		}

		return $this->form_obj['t619'];
	}

	function formatFormConfig() {
		$default_include_exclude_arr = array( 'include_pay_stub_entry_account' => array(), 'exclude_pay_stub_entry_account' => array() );

		$default_arr = array(
				'pension' => $default_include_exclude_arr,
                'lump_sum_payment' => $default_include_exclude_arr,
                'income_tax' => $default_include_exclude_arr,
                'annuities' => $default_include_exclude_arr,
                'self_employed_commission' => $default_include_exclude_arr,
                'service_fees' =>$default_include_exclude_arr,
				'other_box' => array(
									0 => $default_include_exclude_arr,
									1 => $default_include_exclude_arr,
									2 => $default_include_exclude_arr,
									3 => $default_include_exclude_arr,
									4 => $default_include_exclude_arr,
									5 => $default_include_exclude_arr,
									6 => $default_include_exclude_arr,
									),
			);

		$retarr = array_merge( $default_arr, (array)$this->getFormConfig() );
		return $retarr;
	}

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array( 'pay_stub_entry' => array() );

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();

		$pself = TTnew( 'PayStubEntryListFactory' );
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $pse_obj ) {

				$user_id = $this->user_ids[] = $pse_obj->getColumn('user_id');
				//$date_stamp = TTDate::strtotime( $pse_obj->getColumn('pay_stub_transaction_date') );
				$branch = $pse_obj->getColumn('default_branch');
				$department = $pse_obj->getColumn('default_department');
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id] = array(
																'date_stamp' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
																'pay_period_start_date' => strtotime( $pse_obj->getColumn('pay_stub_start_date') ),
																'pay_period_end_date' => strtotime( $pse_obj->getColumn('pay_stub_end_date') ),
																'pay_period_transaction_date' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
																'pay_period' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
															);
				}


				if ( isset($this->tmp_data['pay_stub_entry'][$user_id]['psen_ids'][$pay_stub_entry_name_id]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id]['psen_ids'][$pay_stub_entry_name_id] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id]['psen_ids'][$pay_stub_entry_name_id], $pse_obj->getColumn('amount') );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id]['psen_ids'][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}
			}

			if ( isset($this->tmp_data['pay_stub_entry']) AND is_array($this->tmp_data['pay_stub_entry']) ) {
				foreach($this->tmp_data['pay_stub_entry'] as $user_id => $data_b) {

					$this->tmp_data['pay_stub_entry'][$user_id]['pension'] 							= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['pension']['include_pay_stub_entry_account'], 					$form_data['pension']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['lump_sum_payment'] 				= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['lump_sum_payment']['include_pay_stub_entry_account'], 			$form_data['lump_sum_payment']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['income_tax'] 						= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['income_tax']['include_pay_stub_entry_account'], 					$form_data['income_tax']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['annuities'] 						= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['annuities']['include_pay_stub_entry_account'], 					$form_data['annuities']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['self_employed_commission'] 		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['self_employed_commission']['include_pay_stub_entry_account'],	$form_data['self_employed_commission']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['service_fees'] 					= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['service_fees']['include_pay_stub_entry_account'], 				$form_data['service_fees']['exclude_pay_stub_entry_account'] );

					for( $n=0; $n <= 4; $n++) {
						$this->tmp_data['pay_stub_entry'][$user_id]['other_box_'.$n]				= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['other_box'][$n]['include_pay_stub_entry_account'],			$form_data['other_box'][$n]['exclude_pay_stub_entry_account'] );
					}
				}
			}
		}

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

		//Debug::Arr($this->user_ids, 'User IDs: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->form_data, 'Form Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {
			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $row ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					$date_columns = TTDate::getReportDates( NULL, $row['date_stamp'], FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
					$processed_data  = array(
											'user_id' => $user_id,
											);

					$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		$this->form_data = $this->data; //Copy data to Form Data so group/sort doesn't affect it.

		return TRUE;
	}

	function _outputPDFForm( $format = NULL ) {
		$show_background = TRUE;
		if ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) {
			$show_background = FALSE;
		}
		Debug::Text('Generating Form... Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($setup_data, 'Setup Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->data, 'Data: ', __FILE__, __LINE__, __METHOD__,10);

		//$last_row = count($this->form_data)-1;
		//$total_row = $last_row+1;

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object($current_company) ) {
			Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		$this->sortFormData(); //Make sure forms are sorted.

		if ( $format == 'efile_xml' ) {
			$t619 = $this->getT619Object();
			$t619->setStatus( $setup_data['status_id'] );
			$t619->transmitter_number = ( isset($setup_data['transmitter_number']) ) ? $setup_data['transmitter_number'] : NULL;
			$t619->transmitter_name = ( isset($setup_data['company_name']) AND $setup_data['company_name'] != '' ) ? $setup_data['company_name'] : $current_company->getName();
			$t619->transmitter_address1 = ( isset($setup_data['address1']) AND $setup_data['address1'] != '' ) ? $setup_data['address1'] : $current_company->getAddress1();
			$t619->transmitter_address2 = ( isset($setup_data['address2']) AND $setup_data['address2'] != '' ) ? $setup_data['address2'] : $current_company->getAddress2();
			$t619->transmitter_city = ( isset($setup_data['city']) AND $setup_data['city'] != '' ) ? $setup_data['city'] : $current_company->getCity();
			$t619->transmitter_province = ( isset($setup_data['province']) AND ( $setup_data['province'] != '' AND $setup_data['province'] != 0 ) ) ? $setup_data['province'] : $current_company->getProvince();
			$t619->transmitter_postal_code = ( isset($setup_data['postal_code']) AND $setup_data['postal_code'] != '' ) ? $setup_data['postal_code'] : $current_company->getPostalCode();
			$t619->contact_name = $this->getUserObject()->getFullName();
			$t619->contact_phone = $current_company->getWorkPhone();
			$t619->contact_email = $this->getUserObject()->getWorkEmail();
			$this->getFormObject()->addForm( $t619 );
		}

		$t4a = $this->getT4AObject();
		if ( isset($setup_data['include_t4a_back']) AND $setup_data['include_t4a_back'] == 1 ) {
			$t4a->setShowInstructionPage(TRUE);
		}

		if ( stristr( $format, 'government' ) ) {
			$form_type = 'government';
		} else {
			$form_type = 'employee';
		}
		Debug::Text('Form Type: '. $form_type, __FILE__, __LINE__, __METHOD__,10);

		$t4a->setType( $form_type );
		$t4a->setStatus( $setup_data['status_id'] );
		$t4a->year = TTDate::getYear( $filter_data['start_date'] );
		$t4a->payroll_account_number = ( isset($setup_data['payroll_account_number']) AND $setup_data['payroll_account_number'] != '' ) ? $setup_data['payroll_account_number'] : $current_company->getBusinessNumber();
		$t4a->company_name = ( isset($setup_data['company_name']) AND $setup_data['company_name'] != '' ) ? $setup_data['company_name'] : $current_company->getName();

		$i=0;
		foreach($this->form_data as $row) {
			//if ( $i == $last_row ) {
			//	continue;
			//}

			if ( !isset($row['user_id']) ) {
				Debug::Text('User ID not set!', __FILE__, __LINE__, __METHOD__,10);
				continue;
			}

			$ulf = TTnew( 'UserListFactory' );
			$ulf->getById( (int)$row['user_id'] );
			if ( $ulf->getRecordCount() == 1 ) {
				$user_obj = $ulf->getCurrent();

				$ee_data = array(
							'first_name' => $user_obj->getFirstName(),
							'middle_name' => $user_obj->getMiddleName(),
							'last_name' => $user_obj->getLastName(),
							'address1' => $user_obj->getAddress1(),
							'address2' => $user_obj->getAddress2(),
							'city' => $user_obj->getCity(),
							'province' => $user_obj->getProvince(),
							'employment_province' => $user_obj->getProvince(),
							'postal_code' => $user_obj->getPostalCode(),
							'sin' => $user_obj->getSIN(),
							'employee_number' => $user_obj->getEmployeeNumber(),
							'l16' => $row['pension'],
							'l22' => $row['income_tax'],
							'l18' => $row['lump_sum_payment'],
							'l20' => $row['self_employed_commission'],
							'l24' => $row['annuities'],
							'l48' => $row['service_fees'],
							'other_box_0_code' => NULL,
							'other_box_0' => NULL,
							'other_box_1_code' => NULL,
							'other_box_1' => NULL,
							'other_box_2_code' => NULL,
							'other_box_2' => NULL,
							'other_box_3_code' => NULL,
							'other_box_3' => NULL,
							'other_box_4_code' => NULL,
							'other_box_4' => NULL,
							'other_box_5_code' => NULL,
							'other_box_5' => NULL,
							);

				if ( $row['other_box_0'] > 0 AND isset($setup_data['other_box'][0]['box']) AND $setup_data['other_box'][0]['box'] !='') {
					$ee_data['other_box_0_code'] = $setup_data['other_box'][0]['box'];
					$ee_data['other_box_0'] = $row['other_box_0'];
				}

				if ( $row['other_box_1'] > 0 AND isset($setup_data['other_box'][1]['box']) AND $setup_data['other_box'][1]['box'] !='') {
					$ee_data['other_box_1_code'] = $setup_data['other_box'][1]['box'];
					$ee_data['other_box_1'] = $row['other_box_1'];
				}

				if ( $row['other_box_2'] > 0 AND isset($setup_data['other_box'][2]['box']) AND $setup_data['other_box'][2]['box'] !='') {
					$ee_data['other_box_2_code'] = $setup_data['other_box'][2]['box'];
					$ee_data['other_box_2'] = $row['other_box_2'];
				}

				if ( $row['other_box_3'] > 0 AND isset($setup_data['other_box'][3]['box']) AND $setup_data['other_box'][3]['box'] !='') {
					$ee_data['other_box_3_code'] = $setup_data['other_box'][3]['box'];
					$ee_data['other_box_3'] = $row['other_box_3'];
				}

				if ( $row['other_box_4'] > 0 AND isset($setup_data['other_box'][4]['box']) AND $setup_data['other_box'][4]['box'] !='') {
					$ee_data['other_box_4_code'] = $setup_data['other_box'][4]['box'];
					$ee_data['other_box_4'] = $row['other_box_4'];
				}
				$t4a->addRecord( $ee_data );
				unset($ee_data);

				$i++;
			}
		}
		$this->getFormObject()->addForm( $t4a );

		//Handle T4ASummary
		$t4as = $this->getT4ASumObject();
		$t4as->setStatus( $setup_data['status_id'] );
		$t4as->year = $t4a->year;
		$t4as->payroll_account_number = $t4a->payroll_account_number;
		$t4as->company_name = $t4a->company_name;
		$t4as->company_address1 = ( isset($setup_data['address1']) AND $setup_data['address1'] != '' ) ? $setup_data['address1'] : $current_company->getAddress1();
		$t4as->company_address2 = ( isset($setup_data['address2']) AND $setup_data['address2'] != '' ) ? $setup_data['address2'] : $current_company->getAddress2();
		$t4as->company_city = ( isset($setup_data['city']) AND $setup_data['city'] != '' ) ? $setup_data['city'] : $current_company->getCity();
		$t4as->company_province = ( isset($setup_data['province']) AND ( $setup_data['province'] != '' AND $setup_data['province'] != 0 ) ) ? $setup_data['province'] : $current_company->getProvince();
		$t4as->company_postal_code = ( isset($setup_data['postal_code']) AND $setup_data['postal_code'] != '' ) ? $setup_data['postal_code'] : $current_company->getPostalCode();

		$t4as->l76 = $this->getUserObject()->getFullName(); //Contact name.
		$t4as->l78 = $current_company->getWorkPhone();

		$total_row = Misc::ArrayAssocSum( $this->form_data );
		$t4as->l88 = count($this->form_data);
		$t4as->l16 = $total_row['pension'];
		$t4as->l22 = $total_row['income_tax'];
		$t4as->l18 = $total_row['lump_sum_payment'];
		$t4as->l20 = $total_row['self_employed_commission'];
		$t4as->l24 = $total_row['annuities'];
		$t4as->l48 = $total_row['service_fees'];

		if ( isset($setup_data['other_box']) ) {
			foreach( $setup_data['other_box'] as $key => $other_box_data ) {
				//Debug::Text('zFound other box total for T4A Sum: '. $key .' Code: '. $other_box_data['box'], __FILE__, __LINE__, __METHOD__,10);
				if ( in_array( (int)$other_box_data['box'], array(28,30,32,34,40,42) ) ) {
					//Debug::Text('Found other box total for T4A Sum: '. $key .' Code: '. $other_box_data['box'], __FILE__, __LINE__, __METHOD__,10);
					$object_var = 'l'.(int)$other_box_data['box'];
					$t4as->$object_var = $total_row['other_box_'.$key];
					unset($object_var);
				}
			}
		}
		unset($other_box_data, $key);

		$total_other_deductions = Misc::MoneyFormat( Misc::sumMultipleColumns( $total_row, array('other_box_0','other_box_1','other_box_2','other_box_3','other_box_4') ), FALSE );
		$t4as->l101 = $total_other_deductions;

		//$total_deductions = Misc::MoneyFormat( Misc::sumMultipleColumns( $total_row, array('pension','income_tax','lump_sum_payment', 'self_employed_commission', 'annuities','service_fees') ), FALSE );
		//$t4as->l82 = $total_deductions;
		$t4as->l82 = $total_row['income_tax'];
		$this->getFormObject()->addForm( $t4as );

		if ( $format == 'efile_xml' ) {
			$output_format = 'XML';
			$file_name = 't4a_efile_'.date('Y_m_d').'.xml';
			$mime_type = 'applications/octet-stream'; //Force file to download.
		} else {
			$output_format = 'PDF';
			$file_name = $this->file_name;
			$mime_type = $this->file_mime_type;
		}

		$output = $this->getFormObject()->output( $output_format );

		return array( 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $output );
	}

	//Short circuit this function, as no postprocessing is required for exporting the data.
	function _postProcess( $format = NULL ) {
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile_xml' ) {
			Debug::Text('Skipping postProcess! Format: '. $format, __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		} else {
			return parent::_postProcess( $format );
		}
	}

	function _output( $format = NULL ) {
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile_xml' ) {
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
