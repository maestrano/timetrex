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
 * @package Modules\Report
 */
class FormW2Report extends Report {

	protected $user_ids = array();

	function __construct() {
		$this->title = TTi18n::getText('Form W2 Report');
		$this->file_name = 'form_w2';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report', 'enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report', 'view_formW2', $user_id, $company_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	protected function _validateConfig() {
		$config = $this->getConfig();

		//Make sure some time period is selected.
		if ( !isset($config['filter']['time_period']) AND !isset($config['filter']['pay_period_id']) ) {
			$this->validator->isTrue( 'time_period', FALSE, TTi18n::gettext('No time period defined for this report') );
		}

		return TRUE;
	}

	protected function _getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'output_format':
				$retval = array_merge( parent::getOptions('default_output_format'),
									array(
										'-1100-pdf_form' => TTi18n::gettext('Employee (One Employee/Page)'),
										'-1110-pdf_form_government' => TTi18n::gettext('Government (Multiple Employees/Page)'),
										'-1120-efile' => TTi18n::gettext('eFile'),
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
										'-2100-custom_filter' => TTi18n::gettext('Custom Filter'),

										'-4020-exclude_ytd_adjustment' => TTi18n::gettext('Exclude YTD Adjustments'),

										'-5000-columns' => TTi18n::gettext('Display Columns'),
										'-5010-group' => TTi18n::gettext('Group By'),
										'-5020-sub_total' => TTi18n::gettext('SubTotal By'),
										'-5030-sort' => TTi18n::gettext('Sort By'),
								);
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions( FALSE ); //Exclude Pay Period options.
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
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'FormW2Report', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'FormW2Report', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'FormW2Report', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'FormW2Report', 'custom_column' );
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

										'-1510-address1' => TTi18n::gettext('Address 1'),
										'-1512-address2' => TTi18n::gettext('Address 2'),
										'-1520-city' => TTi18n::gettext('City'),
										'-1522-province' => TTi18n::gettext('Province/State'),
										'-1524-country' => TTi18n::gettext('Country'),
										'-1526-postal_code' => TTi18n::gettext('Postal Code'),
										'-1530-work_phone' => TTi18n::gettext('Work Phone'),
										'-1540-work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
										'-1550-home_phone' => TTi18n::gettext('Home Phone'),
										'-1560-home_email' => TTi18n::gettext('Home Email'),
										'-1590-note' => TTi18n::gettext('Note'),
										'-1595-tag' => TTi18n::gettext('Tags'),
								);

				$retval = array_merge( $retval, $this->getOptions('date_columns'), (array)$this->getOptions('report_static_custom_column') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used
										'-2010-l1' => TTi18n::gettext('Wages (1)'),
										'-2020-l2' => TTi18n::gettext('Federal Income Tax (2)'),
										'-2030-l3' => TTi18n::gettext('Social Security Wages (3)'),
										'-2040-l4' => TTi18n::gettext('Social Security Tax (4)'),
										'-2040-l7' => TTi18n::gettext('Social Security Tips (7)'),
										'-2050-l5' => TTi18n::gettext('Medicare Wages (5)'),
										'-2060-l6' => TTi18n::gettext('Medicare Tax (6)'),
										'-2070-l8' => TTi18n::gettext('Allocated Tips (8)'),
										'-2080-l10' => TTi18n::gettext('Dependent Care Benefits (10)'),
										'-2090-l11' => TTi18n::gettext('Nonqualified Plans (11)'),
										'-2100-l12a' => TTi18n::gettext('Box 12a'),
										'-2110-l12b' => TTi18n::gettext('Box 12b'),
										'-2120-l12c' => TTi18n::gettext('Box 12c'),
										'-2130-l12d' => TTi18n::gettext('Box 12d'),

										'-2200-l14a' => TTi18n::gettext('Box 14a'),
										'-2210-l14b' => TTi18n::gettext('Box 14b'),
										'-2220-l14c' => TTi18n::gettext('Box 14c'),
										'-2230-l14d' => TTi18n::gettext('Box 14d'),
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
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__, 10);
							$retval['-1010-time_period']['time_period'] = 'last_year';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode('+', $template );
							if ( is_array($template_keywords) ) {
								foreach( $template_keywords as $template_keyword ) {
									Debug::Text(' Keyword: '. $template_keyword, __FILE__, __LINE__, __METHOD__, 10);

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
				Debug::Arr($retval, ' Template Config for: '. $template, __FILE__, __LINE__, __METHOD__, 10);

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

	function getFW2Object() {
		if ( !isset($this->form_obj['fw2']) OR !is_object($this->form_obj['fw2']) ) {
			$this->form_obj['fw2'] = $this->getFormObject()->getFormObject( 'w2', 'US' );
			return $this->form_obj['fw2'];
		}

		return $this->form_obj['fw2'];
	}
	function getFW3Object() {
		if ( !isset($this->form_obj['fw3']) OR !is_object($this->form_obj['fw3']) ) {
			$this->form_obj['fw3'] = $this->getFormObject()->getFormObject( 'w3', 'US' );
			return $this->form_obj['fw3'];
		}

		return $this->form_obj['fw3'];
	}
	function getRETURN1040Object() {
		if ( !isset($this->form_obj['return1040']) OR !is_object($this->form_obj['return1040']) ) {
			$this->form_obj['return1040'] = $this->getFormObject()->getFormObject( 'RETURN1040', 'US' );
			return $this->form_obj['return1040'];
		}

		return $this->form_obj['return1040'];
	}


	function formatFormConfig() {
		$default_include_exclude_arr = array( 'include_pay_stub_entry_account' => array(), 'exclude_pay_stub_entry_account' => array() );

		$default_arr = array(
				'l1' => $default_include_exclude_arr,
				'l2' => $default_include_exclude_arr,
				'l3' => $default_include_exclude_arr,
				'l4' => $default_include_exclude_arr,
				'l5' => $default_include_exclude_arr,
				'l6' => $default_include_exclude_arr,
				'l7' => $default_include_exclude_arr,
				'l8' => $default_include_exclude_arr,
				'l9' => $default_include_exclude_arr,
				'l10' => $default_include_exclude_arr,
				'l11' => $default_include_exclude_arr,
				'l12a' => $default_include_exclude_arr,
				'l12b' => $default_include_exclude_arr,
				'l12c' => $default_include_exclude_arr,
				'l12d' => $default_include_exclude_arr,
				'l13' => $default_include_exclude_arr,
				'l14' => $default_include_exclude_arr,
				'l14a' => $default_include_exclude_arr,
				'l14b' => $default_include_exclude_arr,
				'l14c' => $default_include_exclude_arr,
				'l14d' => $default_include_exclude_arr,
				'l15' => $default_include_exclude_arr,
				'l16' => $default_include_exclude_arr,
				'l17' => $default_include_exclude_arr,
				'l18' => $default_include_exclude_arr,
				'l19' => $default_include_exclude_arr,
				'l20' => $default_include_exclude_arr,
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

		//
		//Figure out state/locality wages/taxes.
		//
		$cdlf = TTnew( 'CompanyDeductionListFactory' );
		$cdlf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), array(10, 20), 10 );
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach( $cdlf as $cd_obj ) {
				if ( in_array( $cd_obj->getCalculation(), array(200, 300) ) ) { //Only consider State/District records.
					$tax_deductions[$cd_obj->getId()] = array(
												'id' => $cd_obj->getId(),
												'name' => $cd_obj->getName(),
												'calculation_id' => $cd_obj->getCalculation(),
												'province' => $cd_obj->getProvince(),
												'district' => $cd_obj->getDistrictName(),
												'pay_stub_entry_account_id' => $cd_obj->getPayStubEntryAccount(),
												'include' => $cd_obj->getIncludePayStubEntryAccount(),
												'exclude' => $cd_obj->getExcludePayStubEntryAccount(),
												'user_ids' => $cd_obj->getUser(),
												'company_value1' => $cd_obj->getCompanyValue1(),
												'user_value1' => $cd_obj->getUserValue1(),
												'user_value5' => $cd_obj->getUserValue5(), //District
												'user_ids' => $cd_obj->getUser()
											);
					$tax_deduction_pay_stub_account_id_map[$cd_obj->getPayStubEntryAccount()][] = $cd_obj->getId();
				}
			}
			//Debug::Arr($tax_deductions, 'Tax Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($tax_deduction_pay_stub_account_id_map, 'Tax Deduction Pay Stub Account Map: ', __FILE__, __LINE__, __METHOD__, 10);
		}

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
					$this->tmp_data['pay_stub_entry'][$user_id]['l1']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l1']['include_pay_stub_entry_account'], $form_data['l1']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l2']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l2']['include_pay_stub_entry_account'], $form_data['l2']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l3']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l3']['include_pay_stub_entry_account'], $form_data['l3']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l4']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l4']['include_pay_stub_entry_account'], $form_data['l4']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l5']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l5']['include_pay_stub_entry_account'], $form_data['l5']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l6']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l6']['include_pay_stub_entry_account'], $form_data['l6']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l7']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l7']['include_pay_stub_entry_account'], $form_data['l7']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l8']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l8']['include_pay_stub_entry_account'], $form_data['l8']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l10']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l10']['include_pay_stub_entry_account'], $form_data['l10']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l11']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l11']['include_pay_stub_entry_account'], $form_data['l11']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l12a']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12a']['include_pay_stub_entry_account'], $form_data['l12a']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l12b']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12b']['include_pay_stub_entry_account'], $form_data['l12b']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l12c']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12c']['include_pay_stub_entry_account'], $form_data['l12c']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l12d']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12d']['include_pay_stub_entry_account'], $form_data['l12d']['exclude_pay_stub_entry_account'] );

					$this->tmp_data['pay_stub_entry'][$user_id]['l14a']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14a']['include_pay_stub_entry_account'], $form_data['l14a']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l14b']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14b']['include_pay_stub_entry_account'], $form_data['l14b']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l14c']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14c']['include_pay_stub_entry_account'], $form_data['l14c']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['l14d']		= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14d']['include_pay_stub_entry_account'], $form_data['l14d']['exclude_pay_stub_entry_account'] );

					if ( is_array($data_b['psen_ids']) AND isset($tax_deductions) ) {
						//Support multiple tax/deductions that deposit to the same pay stub account.
						//Also make sure we handle tax/deductions that may not have anything deducted/withheld, but do have wages to be displayed.
						//  For example an employee not earning enough to have State income tax taken off yet.
						foreach( $tax_deductions as $tax_deduction_id => $tax_deduction_arr ) {
							//Found Tax/Deduction associated with this pay stub account.
							$tax_withheld_amount = Misc::calculateMultipleColumns( $data_b['psen_ids'], array($tax_deduction_arr['pay_stub_entry_account_id']) );
							if ( $tax_withheld_amount > 0 OR in_array( $user_id, (array)$tax_deduction_arr['user_ids']) ) {
								Debug::Text('Found User ID: '. $user_id .' in Tax Deduction Name: '. $tax_deduction_arr['name'] .'('.$tax_deduction_arr['id'].') Calculation ID: '. $tax_deduction_arr['calculation_id'] .' Withheld Amount: '. $tax_withheld_amount, __FILE__, __LINE__, __METHOD__, 10);
								if ( $tax_deduction_arr['calculation_id'] == 200 AND $tax_deduction_arr['province'] != '' ) {
									//determine how many district/states currently exist for this employee.
									foreach( range('a', 'z') as $z ) {
										//Make sure we are able to combine multiple state Tax/Deduction amounts together in the case
										//where they are using different Pay Stub Accounts for the State Income Tax and State Addl. Income Tax PSA's.
										if ( !( isset($this->tmp_data['pay_stub_entry'][$user_id]['l17'.$z]) AND isset($this->tmp_data['pay_stub_entry'][$user_id]['l15'. $z .'_state']) AND $this->tmp_data['pay_stub_entry'][$user_id]['l15'. $z .'_state'] != $tax_deduction_arr['province'] ) ) {
											$state_id = $z;
											break;
										}
									}
									//Debug::Text('State ID: '. $state_id .' Z: '. $z, __FILE__, __LINE__, __METHOD__, 10);

									//State Wages/Taxes
									$this->tmp_data['pay_stub_entry'][$user_id]['l15'. $state_id .'_state'] = $tax_deduction_arr['province'];
									if ( !isset($this->tmp_data['pay_stub_entry'][$user_id]['l16'. $state_id]) OR ( isset($this->tmp_data['pay_stub_entry'][$user_id]['l16'. $state_id]) AND $this->tmp_data['pay_stub_entry'][$user_id]['l16'. $state_id] == 0 ) ) {
										$this->tmp_data['pay_stub_entry'][$user_id]['l16'. $state_id] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $tax_deduction_arr['include'], $tax_deduction_arr['exclude'] );
									}
									if ( !isset($this->tmp_data['pay_stub_entry'][$user_id]['l17'. $state_id]) ) {
										$this->tmp_data['pay_stub_entry'][$user_id]['l17'. $state_id] = 0;
									}
									//Just combine the tax withheld part, not the wages/earnings, as we don't want to double up on that.
									$this->tmp_data['pay_stub_entry'][$user_id]['l17'. $state_id] += Misc::calculateMultipleColumns( $data_b['psen_ids'], array($tax_deduction_arr['pay_stub_entry_account_id']) );
								} elseif ( $tax_deduction_arr['calculation_id'] == 300 AND ( $tax_deduction_arr['district'] != '' OR $tax_deduction_arr['company_value1'] != '' ) )	 {
									if ( $tax_deduction_arr['district'] == '' AND $tax_deduction_arr['company_value1'] != '' ) {
										$district_name = $tax_deduction_arr['company_value1'];
									} else {
										$district_name = $tax_deduction_arr['district'];
									}

									foreach( range('a', 'z') as $z ) {
										//Make sure we are able to combine multiple district Tax/Deduction amounts together in the case
										//where they are using different Pay Stub Accounts for the District Income Tax and District Addl. Income Tax PSA's.
										if ( !( isset($this->tmp_data['pay_stub_entry'][$user_id]['l19'.$z]) AND isset($this->tmp_data['pay_stub_entry'][$user_id]['l20'. $z]) AND $this->tmp_data['pay_stub_entry'][$user_id]['l20'. $z] != $district_name ) ) {
											$district_id = $z;
											break;
										}
									}
									//Debug::Text('District Name ID: '. $district_name .' Z: '. $z, __FILE__, __LINE__, __METHOD__, 10);

									//District Wages/Taxes
									$this->tmp_data['pay_stub_entry'][$user_id]['l20'. $district_id] = $district_name;
									if ( !isset($this->tmp_data['pay_stub_entry'][$user_id]['l18'. $district_id]) OR ( isset($this->tmp_data['pay_stub_entry'][$user_id]['l18'. $district_id]) AND $this->tmp_data['pay_stub_entry'][$user_id]['l18'. $district_id] == 0 ) ) {
										$this->tmp_data['pay_stub_entry'][$user_id]['l18'. $district_id] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $tax_deduction_arr['include'], $tax_deduction_arr['exclude'] );
									}
									if ( !isset($this->tmp_data['pay_stub_entry'][$user_id]['l19'. $district_id]) ) {
										$this->tmp_data['pay_stub_entry'][$user_id]['l19'. $district_id] = 0;
									}
									//Just combine the tax withheld part, not the wages/earnings, as we don't want to double up on that.
									$this->tmp_data['pay_stub_entry'][$user_id]['l19'. $district_id] += Misc::calculateMultipleColumns( $data_b['psen_ids'], array($tax_deduction_arr['pay_stub_entry_account_id']) );
								} else {
									Debug::Text('Not State or Local income tax: '. $tax_deduction_arr['id'] .' Calculation: '. $tax_deduction_arr['calculation_id'] .' District: '. $tax_deduction_arr['district'] .' UserValue5: '.$tax_deduction_arr['user_value5'] .' CompanyValue1: '. $tax_deduction_arr['company_value1'], __FILE__, __LINE__, __METHOD__, 10);
								}
							} else {
								Debug::Text('User is either not assigned to Tax/Deduction, or they do not have any calculated amounts...', __FILE__, __LINE__, __METHOD__, 10);
							}
							unset($tax_withheld_amount);
						}
						unset($state_id, $district_id, $district_name, $tax_deduction_id, $tax_deduction_arr);
					}
				}
			}
		}

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->user_ids, 'User IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key = 0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {
			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $row ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					$date_columns = TTDate::getReportDates( NULL, $row['date_stamp'], FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
					$processed_data	 = array(
											'user_id' => $user_id,
											);

					$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$this->form_data = $this->data; //Copy data to Form Data so group/sort doesn't affect it.

		return TRUE;
	}

	function _outputPDFForm( $format = NULL ) {
	
		$show_background = TRUE;
		if ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' OR $format == 'efile' ) {
			$show_background = FALSE;
		}
		Debug::Text('Generating Form... Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);

		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object($current_company) ) {
			Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$current_user = $this->getUserObject();
		if ( !is_object($current_user) ) {
			Debug::Text('Invalid user object...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( $format == 'efile_xml' ) {
			$return1040 = $this->getRETURN1040Object();
			// Ceate the all needed data for Return1040.xsd at here.
			$return1040->return_created_timestamp = TTDate::getDBTimeStamp( TTDate::getTime(), FALSE );
			$return1040->year = TTDate::getYear( $filter_data['end_date'] );
			$return1040->tax_period_begin_date = TTDate::getDate('Y-m-d', TTDate::getBeginDayEpoch( $filter_data['start_date'] ));
			$return1040->tax_period_end__date = TTDate::getDate('Y-m-d', TTDate::getEndDayEpoch( $filter_data['end_date'] ));
			$return1040->software_id = '';
			$return1040->originator_efin = '';
			$return1040->originator_type_code = '';
			$return1040->pin_type_code = '';
			$return1040->jurat_disclosure_code = '';
			$return1040->pin_entered_by = '';
			$return1040->signature_date = TTDate::getDate('Y-m-d', TTDate::getTime());
			$return1040->return_type = '';
			$return1040->ssn = '';
			$return1040->name = ( isset($setup_data['company_name']) AND $setup_data['company_name'] != '' ) ? $setup_data['company_name'] : $current_company->getName();
			$return1040->name_control = '';
			$return1040->address1 = ( isset($setup_data['address1']) AND $setup_data['address1'] != '' ) ? $setup_data['address1'] : $current_company->getAddress1() .' '. $current_company->getAddress2();
			$return1040->city = ( isset($setup_data['city']) AND $setup_data['city'] != '' ) ? $setup_data['city'] : $current_company->getCity();
			$return1040->state = ( isset($setup_data['province']) AND ( $setup_data['province'] != '' AND $setup_data['province'] != 0 ) ) ? $setup_data['province'] : $current_company->getProvince();
			$return1040->zip_code = ( isset($setup_data['postal_code']) AND $setup_data['postal_code'] != '' ) ? $setup_data['postal_code'] : $current_company->getPostalCode();
			$return1040->ip_address = '';
			$return1040->ip_date = TTDate::getDate('Y-m-d', TTDate::getTime());
			$return1040->ip_time = TTDate::getDate('H:i:s', TTDate::getTime());
			$return1040->timezone = TTDate::getTimeZone();

			$this->getFormObject()->addForm( $return1040 );
		}

		$this->sortFormData(); //Make sure forms are sorted.

		$fw2 = $this->getFW2Object();

		$fw2->setDebug(FALSE);
		//if ( $format == 'efile' ) {
		//	$fw2->setDebug(TRUE);
		//}
		$fw2->setShowBackground( $show_background );

		if ( stristr( $format, 'government' ) ) {
			$form_type = 'government';
		} else {
			$form_type = 'employee';
		}
		Debug::Text('Form Type: '. $form_type, __FILE__, __LINE__, __METHOD__, 10);

		$fw2->setType( $form_type );
		$fw2->year = TTDate::getYear( $filter_data['end_date'] );
		//Add support for the user to manually set this data in the setup_data. That way they can use multiple tax IDs for different employees, all beit manually.
		$fw2->ein = ( isset($setup_data['ein']) AND $setup_data['ein'] != '' ) ? $setup_data['ein'] : $current_company->getBusinessNumber();
		$fw2->name = ( isset($setup_data['name']) AND $setup_data['name'] != '' ) ? $setup_data['name'] : $this->getUserObject()->getFullName();
		$fw2->trade_name = ( isset($setup_data['company_name']) AND $setup_data['company_name'] != '' ) ? $setup_data['company_name'] : $current_company->getName();
		$fw2->company_address1 = ( isset($setup_data['address1']) AND $setup_data['address1'] != '' ) ? $setup_data['address1'] : $current_company->getAddress1() .' '. $current_company->getAddress2();
		$fw2->company_city = ( isset($setup_data['city']) AND $setup_data['city'] != '' ) ? $setup_data['city'] : $current_company->getCity();
		$fw2->company_state = ( isset($setup_data['province']) AND ( $setup_data['province'] != '' AND $setup_data['province'] != 0 ) ) ? $setup_data['province'] : $current_company->getProvince();
		$fw2->company_zip_code = ( isset($setup_data['postal_code']) AND $setup_data['postal_code'] != '' ) ? $setup_data['postal_code'] : $current_company->getPostalCode();
		$fw2->efile_user_id = ( isset($setup_data['efile_user_id']) AND $setup_data['efile_user_id'] != '' ) ? $setup_data['efile_user_id'] : NULL;
		$fw2->efile_state = ( isset($setup_data['efile_state']) AND $setup_data['efile_state'] != '' ) ? $setup_data['efile_state'] : 0;

		$fw2->contact_name = $current_user->getFullName();
		$fw2->contact_phone = $current_user->getWorkPhone();
		$fw2->contact_phone_ext = $current_user->getWorkPhoneExt();
		$fw2->contact_email = $current_user->getWorkEmail();
		
		if ( isset($this->form_data) AND count($this->form_data) > 0 ) {
			$i = 0;
			$n = 1;
			foreach((array)$this->form_data as $row) {
				if ( !isset($row['user_id']) ) {
					Debug::Text('User ID not set!', __FILE__, __LINE__, __METHOD__, 10);
					continue;
				}

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getById( (int)$row['user_id'] );
				if ( $ulf->getRecordCount() == 1 ) {
					$user_obj = $ulf->getCurrent();

					$ee_data = array(
								'control_number' => $n,
								'first_name' => $user_obj->getFirstName(),
								'middle_name' => $user_obj->getMiddleName(),
								'last_name' => $user_obj->getLastName(),
								'address1' => $user_obj->getAddress1(),
								'address2' => $user_obj->getAddress2(),
								'city' => $user_obj->getCity(),
								'state' => $user_obj->getProvince(),
								'employment_province' => $user_obj->getProvince(),
								'zip_code' => $user_obj->getPostalCode(),
								'ssn' => $user_obj->getSIN(),
								'employee_number' => $user_obj->getEmployeeNumber(),
								'l1' => ( $row['l1'] != 0 ) ? $row['l1'] : NULL,
								'l2' => ( $row['l2'] != 0 ) ? $row['l2'] : NULL,
								'l3' => ( $row['l3'] != 0 ) ? $row['l3'] : NULL,
								'l4' => ( $row['l4'] != 0 ) ? $row['l4'] : NULL,
								'l5' => ( $row['l5'] != 0 ) ? $row['l5'] : NULL,
								'l6' => ( $row['l6'] != 0 ) ? $row['l6'] : NULL,
								'l7' => ( $row['l7'] != 0 ) ? $row['l7'] : NULL,
								'l8' => ( $row['l8'] != 0 ) ? $row['l8'] : NULL,
								'l10' => ( $row['l10'] != 0 ) ? $row['l10'] : NULL,
								'l11' => ( $row['l11'] != 0 ) ? $row['l11'] : NULL,
								'l12a_code' => NULL,
								'l12a'	=> NULL,
								'l12b_code' => NULL,
								'l12b'	=> NULL,
								'l12c_code' => NULL,
								'l12c'	=> NULL,
								'l12d_code' => NULL,
								'l12d'	=> NULL,
								'l14a_name' => NULL,
								'l14a'	=> NULL,
								'l14b_name' => NULL,
								'l14b'	=> NULL,
								'l14c_name' => NULL,
								'l14c'	=> NULL,
								'l14d_name' => NULL,
								'l14d'	=> NULL,
								);

					if ( $row['l12a'] > 0 AND isset($setup_data['l12a_code']) AND $setup_data['l12a_code'] != '') {
						$ee_data['l12a_code'] = $setup_data['l12a_code'];
						$ee_data['l12a'] = $row['l12a'];
					}
					if ( $row['l12b'] > 0 AND isset($setup_data['l12b_code']) AND $setup_data['l12b_code'] != '') {
						$ee_data['l12b_code'] = $setup_data['l12b_code'];
						$ee_data['l12b'] = $row['l12b'];
					}
					if ( $row['l12c'] > 0 AND isset($setup_data['l12c_code']) AND $setup_data['l12c_code'] != '') {
						$ee_data['l12c_code'] = $setup_data['l12c_code'];
						$ee_data['l12c'] = $row['l12c'];
					}
					if ( $row['l12d'] > 0 AND isset($setup_data['l12d_code']) AND $setup_data['l12d_code'] != '') {
						$ee_data['l12d_code'] = $setup_data['l12d_code'];
						$ee_data['l12d'] = $row['l12d'];
					}

					if ( $row['l14a'] > 0 AND isset($setup_data['l14a_name']) AND $setup_data['l14a_name'] != '') {
						$ee_data['l14a_name'] = $setup_data['l14a_name'];
						$ee_data['l14a'] = $row['l14a'];
					}
					if ( $row['l14b'] > 0 AND isset($setup_data['l14b_name']) AND $setup_data['l14b_name'] != '') {
						$ee_data['l14b_name'] = $setup_data['l14b_name'];
						$ee_data['l14b'] = $row['l14b'];
					}
					if ( $row['l14c'] > 0 AND isset($setup_data['l14c_name']) AND $setup_data['l14c_name'] != '') {
						$ee_data['l14c_name'] = $setup_data['l14c_name'];
						$ee_data['l14c'] = $row['l14c'];
					}
					if ( $row['l14d'] > 0 AND isset($setup_data['l14d_name']) AND $setup_data['l14d_name'] != '') {
						$ee_data['l14d_name'] = $setup_data['l14d_name'];
						$ee_data['l14d'] = $row['l14d'];
					}

					foreach( range('a', 'z') as $z ) {
						//State income tax
						if ( isset($row['l16'.$z]) ) {
							if ( isset($setup_data['state'][$row['l15'.$z.'_state']]) ) {
								$ee_data['l15'.$z.'_state_id'] = $setup_data['state'][$row['l15'.$z.'_state']]['state_id'];
							}
							$ee_data['l15'.$z.'_state'] = $row['l15'.$z.'_state'];
							$ee_data['l16'.$z] = $row['l16'.$z];
							$ee_data['l17'.$z] = $row['l17'.$z];
						} else {
							$ee_data['l15'.$z.'_state_id'] = NULL;
							$ee_data['l15'.$z.'_state'] = NULL;
							$ee_data['l16'.$z] = NULL;
							$ee_data['l17'.$z] = NULL;
						}

						//District income tax
						if ( isset($row['l18'.$z]) ) {
							$ee_data['l18'.$z] = $row['l18'.$z];
							$ee_data['l19'.$z] = $row['l19'.$z];
							$ee_data['l20'.$z] = $row['l20'.$z];
						} else {
							$ee_data['l18'.$z] = NULL;
							$ee_data['l19'.$z] = NULL;
							$ee_data['l20'.$z] = NULL;
						}
					}

					$fw2->addRecord( $ee_data );
					unset($ee_data);

					$i++;
					$n++;
				}
			}
		}
		$this->getFormObject()->addForm( $fw2 );

		if ( $form_type == 'government' ) {
			//Handle W3
			$fw3 = $this->getFW3Object();
			$fw3->setShowBackground( $show_background );
			$fw3->year = $fw2->year;
			$fw3->ein = $fw2->ein;
			$fw3->name = $fw2->name;
			$fw3->trade_name = $fw2->trade_name;
			$fw3->company_address1 = $fw2->company_address1;
			$fw3->company_address2 = $fw2->company_address2;
			$fw3->company_city = $fw2->company_city;
			$fw3->company_state = $fw2->company_state;
			$fw3->company_zip_code = $fw2->company_zip_code;

			$fw3->contact_name = $current_user->getFullName();
			$fw3->contact_phone = ( $current_user->getWorkPhoneExt() != '' ) ? $current_user->getWorkPhone().'x'.$current_user->getWorkPhoneExt() : $current_user->getWorkPhone();
			$fw3->contact_email = $current_user->getWorkEmail();

			$fw3->kind_of_payer = '941';
			$fw3->kind_of_employer = 'none';
			//$fw3->third_party_sick_pay = TRUE;

			if ( isset($setup_data['state'][$fw2->company_state]) AND isset($setup_data['state'][$fw2->company_state]['state_id']) AND $setup_data['state'][$fw2->company_state]['state_id'] != '' ) {
				$fw3->state_id1 = $setup_data['state'][$fw2->company_state]['state_id'];
			}

			$fw3->lc = count($this->form_data);
			$fw3->control_number = ($fw3->lc + 1 );
			//$fw3->ld = '1234568';

			//Use sumRecords()/getRecordsTotal() so all amounts are capped properly.
			$fw2->sumRecords();
			$total_row = $fw2->getRecordsTotal();
			//$total_row = Misc::ArrayAssocSum( $this->form_data );

			//Debug::Arr($total_row, 'Total Row Data: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( is_array($total_row) ) {
				$fw3->l1 = ( $total_row['l1'] != 0 ) ? $total_row['l1'] : NULL;
				$fw3->l2 = ( $total_row['l2'] != 0 ) ? $total_row['l2'] : NULL;
				$fw3->l3 = ( $total_row['l3'] != 0 ) ? $total_row['l3'] : NULL;
				$fw3->l4 = ( $total_row['l4'] != 0 ) ? $total_row['l4'] : NULL;
				$fw3->l5 = ( $total_row['l5'] != 0 ) ? $total_row['l5'] : NULL;
				$fw3->l6 = ( $total_row['l6'] != 0 ) ? $total_row['l6'] : NULL;
				$fw3->l7 = ( $total_row['l7'] != 0 ) ? $total_row['l7'] : NULL;
				$fw3->l8 = ( $total_row['l8'] != 0 ) ? $total_row['l8'] : NULL;
				$fw3->l10 = ( $total_row['l10'] != 0 ) ? $total_row['l10'] : NULL;
				$fw3->l11 = ( $total_row['l11'] != 0 ) ? $total_row['l11'] : NULL;

				$l12a_letters = array( 'd', 'e', 'f', 'g', 'h', 's', 'y', 'aa', 'bb', 'ee' );
				$fw3->l12a = NULL;
				if ( isset($total_row['l12a_code']) AND in_array( strtolower($total_row['l12a_code']), $l12a_letters ) ) {
					$fw3->l12a += $total_row['l12a'];
				}
				if ( isset($total_row['l12b_code']) AND in_array( strtolower($total_row['l12b_code']), $l12a_letters ) ) {
					$fw3->l12a += $total_row['l12b'];
				}
				if ( isset($total_row['l12c_code']) AND in_array( strtolower($total_row['l12c_code']), $l12a_letters ) ) {
					$fw3->l12a += $total_row['l12c'];
				}
				if ( isset($total_row['l12d_code']) AND in_array( strtolower($total_row['l12d_code']), $l12a_letters ) ) {
					$fw3->l12a += $total_row['l12d'];
				}

				foreach( range('a', 'z') as $z ) {
					//State income tax
					if ( isset($total_row['l16'.$z]) ) {
						$fw3->l16 += $total_row['l16'.$z];
						$fw3->l17 += $total_row['l17'.$z];
					}
					//District income tax
					if ( isset($total_row['l18'.$z]) ) {
						$fw3->l18 += $total_row['l18'.$z];
						$fw3->l19 += $total_row['l19'.$z];
					}
				}
			}

			$this->getFormObject()->addForm( $fw3 );
		}

		if ( $format == 'efile' ) {
			$output_format = 'EFILE';
			if ( $fw2->getDebug() == TRUE ) {
				$file_name = 'w2_efile_'.date('Y_m_d').'.csv';
			} else {
				$file_name = 'w2_efile_'.date('Y_m_d').'.txt';
			}
			$mime_type = 'applications/octet-stream'; //Force file to download.
		} elseif ( $format == 'efile_xml' ) {
			$output_format = 'XML';
			$file_name = 'w2_efile_'.date('Y_m_d').'.xml';
			$mime_type = 'applications/octet-stream'; //Force file to download.
		} else {
			$output_format = 'PDF';
			$file_name = $this->file_name.'.pdf';
			$mime_type = $this->file_mime_type;
		}

		$output = $this->getFormObject()->output( $output_format );

		return array( 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $output );
	}

	//Short circuit this function, as no postprocessing is required for exporting the data.
	function _postProcess( $format = NULL ) {
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile' OR $format == 'efile_xml' ) {
			Debug::Text('Skipping postProcess! Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		} else {
			return parent::_postProcess( $format );
		}
	}

	function _output( $format = NULL ) {
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile' OR $format == 'efile_xml' ) {
			//return $this->_outputPDFForm( 'efile' );
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
