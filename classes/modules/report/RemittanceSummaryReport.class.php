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
class RemittanceSummaryReport extends Report {

	protected $user_ids = array();

	function __construct() {
		$this->title = TTi18n::getText('Remittance Summary Report');
		$this->file_name = 'remittance_summary';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_remittance_summary', $user_id, $company_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	protected function _getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'output_format':
				$retval = parent::getOptions('default_output_format');
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

										'-4020-exclude_ytd_adjustment' => TTi18n::gettext('Exclude YTD Adjustments'),

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
				$retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 13, TRUE );
				break;
            case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'RemittanceSummaryReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'RemittanceSummaryReport', 'custom_column' );
				}
                break;
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'RemittanceSummaryReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'RemittanceSummaryReport', 'custom_column' );
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
										'-2060-total' => TTi18n::gettext('Total Deductions'),
										'-2070-ei_total' => TTi18n::gettext('EI'),
										'-2080-cpp_total' => TTi18n::gettext('CPP'),
										'-2090-tax_total' => TTi18n::gettext('Tax'),
										'-2100-gross_payroll' => TTi18n::gettext('Gross Pay')
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
			case 'schedule_deposit':
				$retval = array(
									10 => TTi18n::gettext('Monthly'),
									20 => TTi18n::gettext('Semi-Weekly')
								);
				break;
			case 'templates':
				$retval = array(
										'-1005-by_pay_period_by_employee' => TTi18n::gettext('by Pay Period/Employee'),

										'-1010-by_employee' => TTi18n::gettext('by Employee'),
										'-1020-by_pay_period' => TTi18n::gettext('by Pay Period'),
										'-1030-by_month' => TTi18n::gettext('by Month'),
										'-1040-by_branch' => TTi18n::gettext('by Branch'),
										'-1050-by_department' => TTi18n::gettext('by Department'),
										'-1060-by_branch_by_department' => TTi18n::gettext('by Branch/Department'),

										'-1110-by_month_by_employee' => TTi18n::gettext('by Month/Employee'),
										'-1120-by_month_by_branch' => TTi18n::gettext('by Month/Branch'),
										'-1130-by_month_by_department' => TTi18n::gettext('by Month/Department'),
										'-1140-by_month_by_branch_by_department' => TTi18n::gettext('by Month/Branch/Department'),

							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'default':
							//Proper settings to generate the form.
							$retval['-1010-time_period']['time_period'] = 'last_month';

							$retval['columns'] = $this->getOptions('columns');

							$retval['group'][] = 'date_month';

							$retval['sort'][] = array('date_month' => 'asc');

							$retval['other']['grand_total'] = TRUE;

							break;
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
							$retval['-1010-time_period']['time_period'] = 'last_month';

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
										case 'by_pay_period':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'pay_period_transaction_date';

											$retval['group'][] = 'pay_period_transaction_date';

											$retval['sort'][] = array('pay_period_transaction_date' => 'asc');
											break;

										case 'by_pay_period_by_employee':
											$retval['columns'][] = 'pay_period_transaction_date';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'pay_period_transaction_date';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'pay_period_transaction_date';

											$retval['sort'][] = array('pay_period_transaction_date' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_month':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'date_month';

											$retval['group'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
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
											$retval['-1010-time_period']['time_period'] = 'this_year';

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
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_branch' => 'asc');
											break;
										case 'by_month_by_department':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_month_by_branch_by_department':
											$retval['-1010-time_period']['time_period'] = 'this_year';

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

	function formatFormConfig() {
		$default_include_exclude_arr = array( 'include_pay_stub_entry_account' => array(), 'exclude_pay_stub_entry_account' => array() );

		$default_arr = array(
                'ei' => $default_include_exclude_arr,
                'cpp' => $default_include_exclude_arr,
                'tax' => $default_include_exclude_arr,
			);

		$retarr = array_merge( $default_arr, (array)$this->getFormConfig() );
		return $retarr;
	}

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array( 'user' => array(), 'pay_stub_entry' => array(), 'pay_period' => array() );

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();

		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
		$pseallf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pseal_obj = $pseallf->getCurrent();
		}

		$pself = TTnew( 'PayStubEntryListFactory' );
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $pse_obj ) {

				$user_id = $this->user_ids[] = $pse_obj->getColumn('user_id');
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn('pay_stub_transaction_date') );
				$branch = $pse_obj->getColumn('default_branch');
				$department = $pse_obj->getColumn('default_department');
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = array(
																'pay_period_start_date' => strtotime( $pse_obj->getColumn('pay_stub_start_date') ),
																'pay_period_end_date' => strtotime( $pse_obj->getColumn('pay_stub_end_date') ),
																//Some transaction dates could be throughout the day for terminated employees being paid early, so always forward them to the middle of the day to keep group_by working correctly.
																'pay_period_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ) ),
																'pay_period' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
															);

					$this->form_data['pay_period'][] = strtotime( $pse_obj->getColumn('pay_stub_transaction_date') );
				}

				if ( isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id], $pse_obj->getColumn('amount') );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}
			}

			if ( isset($this->tmp_data['pay_stub_entry']) AND is_array($this->tmp_data['pay_stub_entry']) ) {
				foreach($this->tmp_data['pay_stub_entry'] as $user_id => $data_a) {
					foreach($data_a as $date_stamp => $data_b) {

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total'] 				= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['ei']['include_pay_stub_entry_account'], 				$form_data['ei']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total'] 				= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['cpp']['include_pay_stub_entry_account'], 				$form_data['cpp']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['tax_total'] 				= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['tax']['include_pay_stub_entry_account'], 				$form_data['tax']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['total'] 					= $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total'] + $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total'] + $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['tax_total'];
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['gross_payroll'] 			= Misc::calculateMultipleColumns( $data_b['psen_ids'], (array)$pseal_obj->getTotalGross(), array() );
					}
				}
			}
		}

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

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
			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				foreach( $level_1 as $date_stamp => $row ) {
					$date_columns = TTDate::getReportDates( NULL, $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
					$processed_data  = array(
											//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
											);

					$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function _pdf_Header() {
		if ( $this->pdf->getPage() == 1 ) {
			//Draw separate table at the top showing the summarized data specifically for the form.
			$column_options = array(
									'cpp_total' => TTi18n::getText('CPP Contributions'),
									'ei_total' => TTi18n::getText('EI Premiums'),
									'tax_total' => TTi18n::getText('Tax Deductions'),
									'total' => TTi18n::getText('This Payment'),
									'gross_payroll' => TTi18n::getText('Gross Payroll'),
									'employees' => TTi18n::getText('Total Employees'),
									'end_remitting_period' => TTi18n::getText('End of Period'),
									'due_date' => TTi18n::getText('Due Date'),
									);
			$columns = array(
									'cpp_total' => TRUE,
									'ei_total' => TRUE,
									'tax_total' => TRUE,
									'total' => TRUE,
									'gross_payroll' => TRUE,
									'employees' => TRUE,
									'end_remitting_period' => TRUE,
									'due_date' => TRUE,
							 );

			$header_layout = $this->config['other']['layout']['header'];

			$margins = $this->pdf->getMargins();

			//Draw report information
			if ( $this->pdf->getPage() > 1 ) {
				$this->_pdf_drawLine(0.75); //Slightly smaller than first/last lines.
			}

			if ( is_array($columns) AND count($columns) > 0 ) {
				$this->pdf->SetFont($this->config['other']['default_font'],'B', $this->_pdf_fontSize( $this->config['other']['table_header_font_size'] ) );
				$this->pdf->setTextColor(0);
				$this->pdf->setDrawColor(0);
				$this->pdf->setFillColor(240); //Grayscale only.

				$column_widths = $this->_pdf_getTableColumnWidths( $this->getLargestColumnData( array_intersect_key($column_options, (array)$columns) ), $this->config['other']['layout']['header'] ); //Table largest column data;
				$cell_height = $this->_pdf_getMaximumHeightFromArray( $columns, $column_options, $column_widths, $this->config['other']['table_header_word_wrap'], $this->_pdf_fontSize( $header_layout['height'] ) );
				foreach( $columns as $column => $tmp ) {
					if ( isset($column_options[$column]) AND isset($column_widths[$column]) ) {
						$cell_width = $column_widths[$column];
						if ( ($this->pdf->getX()+$cell_width) > $this->pdf->getPageWidth() ) {
							Debug::Text(' Page not wide enough, it should be at least: '. ($this->pdf->getX()+$cell_width) .' Page Width: '. $this->pdf->getPageWidth(), __FILE__, __LINE__, __METHOD__,10);
							$this->pdf->Ln();
						}
						$this->pdf->Cell( $cell_width, $this->_pdf_fontSize( $header_layout['height'] ), $column_options[$column], $header_layout['border'], 0, $header_layout['align'], $header_layout['fill'], '', $header_layout['stretch'] );
						//Wrapping shouldn't be needed as the cell widths should expand to at least fit the header. Wrapping may be needed on regular rows though.
						//$this->pdf->MultiCell( $cell_width, $cell_height, $column_options[$column], 0, $header_layout['align'], $header_layout['fill'], 0 );
					} else {
						Debug::Text(' Invalid Column: '. $column, __FILE__, __LINE__, __METHOD__,10);
					}
				}
				$this->pdf->Ln();

				$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.


				//Reset all styles/fills after page break.
				$this->pdf->SetFont($this->config['other']['default_font'],'', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );
				$this->pdf->SetTextColor(0);
				$this->pdf->SetDrawColor(0);
				$this->pdf->setFillColor(255);

				//Draw data
				$border = 0;

				$row_layout = array(
										'max_width' => 30,
										'cell_padding' => 2,
										'height' => 5,
										'align' => 'R',
										'border' => 0,
										'fill' => 1,
										'stretch' => 1 );

				//Get the earliest transaction date of all pay periods.
				$this->form_data['pay_period'] = array_unique( (array)$this->form_data['pay_period'] );
				ksort( $this->form_data['pay_period'] );
				$transaction_date = current( (array)$this->form_data['pay_period']);
				Debug::Text('Transaction Date: '. TTDate::getDate('DATE', $transaction_date) .'('.  $transaction_date .')', __FILE__, __LINE__, __METHOD__,10);

				$summary_table_data = $this->total_row;
				$summary_table_data['cpp_total'] = TTi18n::formatCurrency( ( isset($summary_table_data['cpp_total']) ) ? $summary_table_data['cpp_total'] : 0 );
				$summary_table_data['ei_total'] = TTi18n::formatCurrency( ( isset($summary_table_data['ei_total'] ) ) ? $summary_table_data['ei_total'] : 0 );
				$summary_table_data['tax_total'] = TTi18n::formatCurrency( ( isset($summary_table_data['tax_total'] ) ) ? $summary_table_data['tax_total'] : 0 );
				$summary_table_data['total'] = TTi18n::formatCurrency( ( isset($summary_table_data['total'] ) ) ? $summary_table_data['total'] : 0 );
				$summary_table_data['gross_payroll'] = TTi18n::formatCurrency( ( isset($summary_table_data['gross_payroll'] ) ) ? $summary_table_data['gross_payroll'] : 0 );
				$summary_table_data['employees'] = count($this->user_ids);
				$remittance_due_date = Wage::getRemittanceDueDate($transaction_date, ( isset($summary_table_data['total'] ) ) ? $summary_table_data['total'] : 0 );
				$summary_table_data['due_date'] = ( $remittance_due_date > 0 ) ? TTDate::getDate('DATE', $remittance_due_date ) : TTi18n::getText("N/A");
				$summary_table_data['end_remitting_period'] = ( $transaction_date > 0 ) ? date('Y-m', $transaction_date) : TTi18n::getText("N/A");

				foreach( $columns as $column => $tmp ) {
					$value = $summary_table_data[$column];
					$cell_width = ( isset($column_widths[$column]) ) ? $column_widths[$column] : 30;

					$this->pdf->Cell( $cell_width, $this->_pdf_fontSize( $row_layout['height'] ), $value, $border, 0, $row_layout['align'], $row_layout['fill'], '', $row_layout['stretch'] );
				}
				$this->pdf->Ln();
				$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.

				$this->pdf->Ln();
				$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.
			}
		}

		parent::_pdf_Header();
		return TRUE;
	}
}
?>
