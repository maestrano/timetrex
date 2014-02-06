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
class GeneralLedgerSummaryReport extends Report {

	function __construct() {
		$this->title = TTi18n::getText('General Ledger Summary Report');
		$this->file_name = 'generalledger_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_general_ledger_summary', $user_id, $company_id ) ) {
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
                                        '-2100-custom_filter' => TTi18n::gettext('Custom Filter'),

										'-4020-exclude_ytd_adjustment' => TTi18n::gettext('Exclude YTD Adjustments'),

										'-5000-columns' => TTi18n::gettext('Display Columns'), //No Columns for this report.
										'-5010-group' => TTi18n::gettext('Group By'),
										'-5020-sub_total' => TTi18n::gettext('SubTotal By'),
										'-5030-sort' => TTi18n::gettext('Sort By'),
							   );
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = TTDate::getReportDateOptions( 'transaction', TTi18n::getText('Transaction Date'), 13, TRUE );
				break;
            case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'GeneralLedgerSummaryReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'GeneralLedgerSummaryReport', 'custom_column' );
				}
                break;
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'GeneralLedgerSummaryReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'GeneralLedgerSummaryReport', 'custom_column' );
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
										'-1040-status' => TTi18n::gettext('Status'),
										'-1050-title' => TTi18n::gettext('Title'),
										'-1060-province' => TTi18n::gettext('Province/State'),
										'-1070-country' => TTi18n::gettext('Country'),
										'-1080-user_group' => TTi18n::gettext('Group'),
										'-1090-default_branch' => TTi18n::gettext('Default Branch'),
										'-1100-default_department' => TTi18n::gettext('Default Department'),
										'-1110-currency' => TTi18n::gettext('Currency'),
										'-1200-permission_control' => TTi18n::gettext('Permission Group'),
										'-1210-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1220-policy_group' => TTi18n::gettext('Policy Group'),
										//Handled in date_columns above.
										//'-1250-pay_period' => TTi18n::gettext('Pay Period'),

										'-2010-account' => TTi18n::gettext('Account'),
							   );

				$retval = array_merge( $retval, $this->getOptions('date_columns'), (array)$this->getOptions('report_static_custom_column') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used
										'-2100-debit_amount' => TTi18n::gettext('Debit'),
										'-2110-credit_amount' => TTi18n::gettext('Credit'),
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
						if ( strpos($column, '_amount') !== FALSE ) {
							$retval[$column] = 'currency';
						}
					}
				}
				$retval['verified_time_sheet_date'] = 'time_stamp';
				break;
			case 'aggregates':
				$retval = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') ) ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos($column, '_hourly_rate') !== FALSE OR substr( $column, 0, 2 ) == 'PR') {
									$retval[$column] = 'avg';
								} else {
									$retval[$column] = 'sum';
								}
						}
					}
				}
				$retval['verified_time_sheet'] = 'first';
				$retval['verified_time_sheet_date'] = 'first';
				break;
			case 'templates':
				$retval = array(

										'-1010-by_employee' => TTi18n::gettext('by Employee'),

										'-1110-by_title' => TTi18n::gettext('by Title'),
										'-1120-by_group' => TTi18n::gettext('by Group'),
										'-1130-by_branch' => TTi18n::gettext('by Branch'),
										'-1140-by_department' => TTi18n::gettext('by Department'),
										'-1150-by_branch_by_department' => TTi18n::gettext('by Branch/Department'),
										'-1160-by_pay_period' => TTi18n::gettext('By Pay Period'),
							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

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
										case 'by_employee':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'account';
											$retval['columns'][] = 'debit_amount';
											$retval['columns'][] = 'credit_amount';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'account';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = array('full_name' => 'asc');
											$retval['sort'][] = array('account' => 'asc');
											break;

										case 'by_title':
											$retval['columns'][] = 'title';
											$retval['columns'][] = 'account';
											$retval['columns'][] = 'debit_amount';
											$retval['columns'][] = 'credit_amount';

											$retval['group'][] = 'title';
											$retval['group'][] = 'account';

											$retval['sub_total'][] = 'title';

											$retval['sort'][] = array('title' => 'asc');
											$retval['sort'][] = array('account' => 'asc');
											break;
										case 'by_group':
											$retval['columns'][] = 'user_group';
											$retval['columns'][] = 'account';
											$retval['columns'][] = 'debit_amount';
											$retval['columns'][] = 'credit_amount';

											$retval['group'][] = 'user_group';
											$retval['group'][] = 'account';

											$retval['sub_total'][] = 'user_group';

											$retval['sort'][] = array('user_group' => 'asc');
											$retval['sort'][] = array('account' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'account';
											$retval['columns'][] = 'debit_amount';
											$retval['columns'][] = 'credit_amount';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'account';

											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('account' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'default_department';
											$retval['columns'][] = 'account';
											$retval['columns'][] = 'debit_amount';
											$retval['columns'][] = 'credit_amount';

											$retval['group'][] = 'default_department';
											$retval['group'][] = 'account';

											$retval['sub_total'][] = 'default_department';

											$retval['sort'][] = array('default_department' => 'asc');
											$retval['sort'][] = array('account' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';
											$retval['columns'][] = 'account';
											$retval['columns'][] = 'debit_amount';
											$retval['columns'][] = 'credit_amount';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';
											$retval['group'][] = 'account';

											$retval['sub_total'][] = 'default_branch';
											$retval['sub_total'][] = 'default_department';

											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											$retval['sort'][] = array('account' => 'asc');
											break;
										case 'by_pay_period':
											$retval['columns'][] = 'transaction-pay_period';
											$retval['columns'][] = 'account';
											$retval['columns'][] = 'debit_amount';
											$retval['columns'][] = 'credit_amount';

											$retval['group'][] = 'transaction-pay_period';
											$retval['group'][] = 'account';

											$retval['sub_total'][] = 'transaction-pay_period';

											$retval['sort'][] = array('transaction-pay_period' => 'asc');
											$retval['sort'][] = array('account' => 'asc');
											break;
									}
								}
							}
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

	function calculatePercentDistribution( $user_date_total_arr, $pay_period_total_arr ) {
		//Debug::Arr($user_date_total_arr, 'User Date Total Arr: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($pay_period_total_arr , 'Total Time Arr: ', __FILE__, __LINE__, __METHOD__,10);

		//Flatten array to one dimension and calculate percents.
		if ( is_array($pay_period_total_arr) ) {
			foreach($pay_period_total_arr as $user_id => $level_1 ) {
				foreach( $level_1 as $pay_period_id => $pay_period_total_time ) {

					if ( isset($user_date_total_arr[$user_id][$pay_period_id]) ) {
						foreach( $user_date_total_arr[$user_id][$pay_period_id] as $branch_id => $level_10 ) {
							foreach( $level_10 as $department_id => $level_11 ) {
								foreach( $level_11 as $job_id => $level_12 ) {
									foreach( $level_12 as $job_item_id => $total_time ) {
										//Debug::Text('Pay Period Total Time: '. $pay_period_total_time .' Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__,10);
										$key = $branch_id.'-'.$department_id.'-'.$job_id .'-'. $job_item_id;
										$retarr[$user_id][$pay_period_id][$key] = $total_time / $pay_period_total_time;
									}
								}
							}
						}
					}

				}
			}

			//Debug::Arr($retarr , 'RetArr: ', __FILE__, __LINE__, __METHOD__,10);

			return $retarr;
		}

		return FALSE;
	}
	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array('pay_stub_entry' => array(), 'user_date_total' => array(), 'pay_period_total' => array(), 'pay_period_distribution' => array(), 'user' => array() );

		//Don't need to process data unless we're preparing the report.
		$psf = TTnew( 'PayStubFactory' );
		$export_type_options = Misc::trimSortPrefix( $psf->getOptions('export_type') );
		if ( isset($export_type_options[$format]) ) {
			Debug::Text('Skipping data retrieval for format: '. $format, __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		}

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		if ( $this->getPermissionObject()->Check('pay_stub','view') == FALSE OR $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			$hlf = TTnew( 'HierarchyListFactory' );
			$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = array();
			$wage_permission_children_ids = array();
		}
		if ( $this->getPermissionObject()->Check('pay_stub','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('pay_stub','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('pay_stub','view_own') ) {
				$permission_children_ids[] = $this->getUserObject()->getID();
			}

			$filter_data['permission_children_ids'] = $permission_children_ids;
		}

		$this->enable_time_based_distribution = FALSE;
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$psealf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $psealf->getRecordCount() > 0 ) {
			foreach($psealf as $psea_obj) {
				if ( $this->enable_time_based_distribution == FALSE AND ( strpos( $psea_obj->getDebitAccount(), 'punch' ) !== FALSE OR strpos( $psea_obj->getCreditAccount(), 'punch' ) !== FALSE ) ) {
					$this->enable_time_based_distribution = TRUE;
				}
				$psea_arr[$psea_obj->getId()] = array(
															'name' => $psea_obj->getName(),
															'debit_account' => $psea_obj->getDebitAccount(),
															'credit_account' => $psea_obj->getCreditAccount(),
															);
			}
		}
		Debug::Text(' Time Based Distribution: '. (int)$this->enable_time_based_distribution, __FILE__, __LINE__, __METHOD__,10);

		$crlf = TTnew( 'CurrencyListFactory' );
		$crlf->getByCompanyId( $this->getUserObject()->getCompany() );
		$currency_options = $crlf->getArrayByListFactory( $crlf, FALSE, TRUE );

		//Get Base Currency
		$crlf->getByCompanyIdAndBase( $this->getUserObject()->getCompany(), TRUE );
		if ( $crlf->getRecordCount() > 0 ) {
			$base_currency_obj = $crlf->getCurrent();
		}
		$currency_convert_to_base = TRUE;

		//Debug::Text(' Permission Children: '. count($permission_children_ids) .' Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($permission_children_ids, 'Permission Children: '. count($permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($wage_permission_children_ids, 'Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);

		//Get total time for each filtered employee in each filtered pay period. DO NOT filter by anything else, as we need the overall total time worked always.
		if ( $this->enable_time_based_distribution == TRUE ) {
			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getGeneralLedgerReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			Debug::Text(' User Date Total Rows: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $udtlf->getRecordCount(), NULL, TTi18n::getText('Retrieving Punch Data...') );
			if ( $udtlf->getRecordCount() > 0 ) {
				foreach ( $udtlf as $key => $udt_obj ) {
					$pay_period_ids[$udt_obj->getColumn('pay_period_id')] = TRUE;

					$user_id = $udt_obj->getColumn('user_id');
					$date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
					$pay_period_id = $udt_obj->getColumn('pay_period_id');
					$branch_id = $udt_obj->getColumn('branch_id');
					$department_id = $udt_obj->getColumn('department_id');
					$job_id = $udt_obj->getColumn('job_id');
					$job_item_id = $udt_obj->getColumn('job_item');

					$status_id = $udt_obj->getColumn('status_id');
					$type_id = $udt_obj->getColumn('type_id');

					$column = $udt_obj->getTimeCategory();

					//Or just worked and paid absence time.
					//Worked time include auto-deduct lunches/breaks though, so we have to exclude those as they can throw off the percentages.
					if ( ( $type_id == 100 OR $type_id == 110 ) OR ( $column != 'worked_time' AND strpos( $column, 'absence_policy' ) === FALSE ) ) {
						$column = NULL;
					}

					//Debug::Text('Column: '. $column .' DateStamp: '. $date_stamp .' Total Time: '. $udt_obj->getColumn('total_time') .' Status: '. $status_id .' Type: '. $type_id .' Branch: '. $branch_id .' Department: '. $department_id, __FILE__, __LINE__, __METHOD__,10);
					if ( ( $date_stamp != '' AND $column != '' AND $udt_obj->getColumn('total_time') != 0 )  ) {
						//Add time/wage and calculate average hourly rate.
						if ( isset($this->tmp_data['user_date_total'][$user_id][$pay_period_id][$branch_id][$department_id][$job_id][$job_item_id]) ) {
							$this->tmp_data['user_date_total'][$user_id][$pay_period_id][$branch_id][$department_id][$job_id][$job_item_id] += $udt_obj->getColumn('total_time');
						} else {
							$this->tmp_data['user_date_total'][$user_id][$pay_period_id][$branch_id][$department_id][$job_id][$job_item_id] = $udt_obj->getColumn('total_time');
						}

						if ( isset($this->tmp_data['pay_period_total'][$user_id][$pay_period_id]) ) {
							$this->tmp_data['pay_period_total'][$user_id][$pay_period_id] += $udt_obj->getColumn('total_time');
						} else {
							$this->tmp_data['pay_period_total'][$user_id][$pay_period_id] = $udt_obj->getColumn('total_time');
						}

					}

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
				}
			}
			$this->tmp_data['pay_period_distribution'] = $this->calculatePercentDistribution( $this->tmp_data['user_date_total'], $this->tmp_data['pay_period_total'] );
		}

		$pself = TTnew( 'PayStubEntryListFactory' );
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pself->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		Debug::Text(' PSE Total Rows: '. $pself->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $key => $pse_obj ) {
				$user_id = $pse_obj->getColumn('user_id');
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn('pay_period_transaction_date') );
				$branch = $pse_obj->getColumn('default_branch');
				$department = $pse_obj->getColumn('default_department');
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = array(
																'pay_period_start_date' => strtotime( $pse_obj->getColumn('pay_period_start_date') ),
																'pay_period_end_date' => strtotime( $pse_obj->getColumn('pay_period_end_date') ),
																'pay_period_transaction_date' => strtotime( $pse_obj->getColumn('pay_period_transaction_date') ),
																'pay_period' => strtotime( $pse_obj->getColumn('pay_period_transaction_date') ),
																'pay_period_id' => $pse_obj->getColumn('pay_period_id'),

																'pay_stub_start_date' => strtotime( $pse_obj->getColumn('pay_stub_start_date') ),
																'pay_stub_end_date' => strtotime( $pse_obj->getColumn('pay_stub_end_date') ),
																'pay_stub_transaction_date' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
															);
				}

				//If the account name has punch_branch, punch_department, punch_job, punch_job_item variables specified, loop through
				//those duplicating the row using only a time based distribution percentage for the amount.

				if ( isset($psea_arr[$pse_obj->getPayStubEntryNameId()]) ) {
					if ( isset($psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'])
							AND $psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'] != '' ) {

						$debit_accounts = explode(',', $psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'] );
						foreach( $debit_accounts as $debit_account ) {
							//$debit_account = replaceGLAccountVariables( $debit_account, $replace_arr);
							//Debug::Text('Debit Entry: Account: '. $debit_account .' Amount: '. $pse_obj->getAmount() , __FILE__, __LINE__, __METHOD__,10);
							//Allow negative amounts, but skip any $0 entries
							if ( $pse_obj->getAmount() != 0 ) {
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][] = array(
													'account' => $debit_account,
													'debit_amount' => Misc::MoneyFormat( $base_currency_obj->getBaseCurrencyAmount( $pse_obj->getAmount(), $pse_obj->getColumn('currency_rate'), $currency_convert_to_base ), FALSE ),
													'credit_amount' => NULL,
													);
							}
						}
						unset($debit_accounts, $debit_account);
					}

					if ( isset($psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'])
							AND $psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'] != '' ) {

						//Debug::Text('Combined Credit Accounts: '. count($credit_accounts) , __FILE__, __LINE__, __METHOD__,10);
						$credit_accounts = explode(',', $psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'] );
						foreach( $credit_accounts as $credit_account) {
							//$credit_account = replaceGLAccountVariables( $credit_account, $replace_arr);
							//Allow negative amounts, but skip any $0 entries
							if ( $pse_obj->getAmount() != 0 ) {
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][] = array(
													'account' => $credit_account,
													'debit_amount' => NULL,
													'credit_amount' => Misc::MoneyFormat( $base_currency_obj->getBaseCurrencyAmount( $pse_obj->getAmount(), $pse_obj->getColumn('currency_rate'), $currency_convert_to_base ), FALSE ),
													);
							}
						}
						unset($credit_accounts, $credit_account);

					}

				} else {
					Debug::Text('No Pay Stub Entry Account Matches!', __FILE__, __LINE__, __METHOD__,10);
				}
				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( array(	'default_branch_id' => TRUE,
																											'default_department_id' => TRUE,
																											'employee_number' => TRUE,
																											'other_id1' => TRUE,
																											'other_id2' => TRUE,
																											'other_id3' => TRUE,
																											'other_id4' => TRUE,
																											'other_id5' => TRUE),
																									(array)$this->getColumnDataConfig() ) );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__,10);
		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

		$blf = TTnew( 'BranchListFactory' );
		$branch_options = $blf->getByCompanyIdArray( $this->getUserObject()->getCompany() );
		//Get Branch ID to Branch Code mapping
		$branch_code_map = array( 0 => 0 );
		$blf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $blf->getRecordCount() > 0 ) {
			foreach( $blf as $b_obj ) {
				$branch_code_map[$b_obj->getId()] = $b_obj;
			}
		}

		$dlf = TTnew( 'DepartmentListFactory' );
		$department_options = $dlf->getByCompanyIdArray( $this->getUserObject()->getCompany() );
		//Get Department ID to Branch Code mapping
		$department_code_map = array( 0 => 0 );
		$dlf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$department_code_map[$d_obj->getId()] = $d_obj;
			}
		}

		$utlf = TTnew( 'UserTitleListFactory' );
		$title_options = $utlf->getByCompanyIdArray( $this->getUserObject()->getCompany() );

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' );
			$job_options = $jlf->getByCompanyIdArray( $this->getUserObject()->getCompany() );
			//Get Job ID to Job Code mapping
			$job_code_map = array( 0 => 0 );
			$jlf->getByCompanyId( $this->getUserObject()->getCompany() );
			if ( $jlf->getRecordCount() > 0 ) {
				foreach( $jlf as $j_obj ) {
					$job_code_map[$j_obj->getId()] = $j_obj;
				}
			}

			$jilf = TTnew( 'JobItemListFactory' );
			$job_item_options = $jlf->getByCompanyIdArray( $this->getUserObject()->getCompany() );
			//Get Job ID to Job Code mapping
			$job_item_code_map = array( 0 => 0 );
			$jilf->getByCompanyId( $this->getUserObject()->getCompany() );
			if ( $jilf->getRecordCount() > 0 ) {
				foreach( $jilf as $ji_obj ) {
					$job_item_code_map[$ji_obj->getId()] = $ji_obj;
				}
			}

		} else {
			$job_code_map = array();
			$job_item_code_map = array();
		}

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {
			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					//foreach( $level_1 as $date_stamp => $row ) {
					foreach( $level_1 as $date_stamp => $row ) {
						$replace_arr = array(
												( is_object($branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]) ) ? $branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]->getManualID() : NULL,
												( is_object($branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]) ) ? $branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID1() : NULL,
												( is_object($branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]) ) ? $branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID2() : NULL,
												( is_object($branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]) ) ? $branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID3() : NULL,
												( is_object($branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]) ) ? $branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID4() : NULL,
												( is_object($branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]) ) ? $branch_code_map[(int)$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID5() : NULL,
												( is_object($department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]) ) ? $department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]->getManualID() : NULL,
												( is_object($department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]) ) ? $department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID1() : NULL,
												( is_object($department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]) ) ? $department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID2() : NULL,
												( is_object($department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]) ) ? $department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID3() : NULL,
												( is_object($department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]) ) ? $department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID4() : NULL,
												( is_object($department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]) ) ? $department_code_map[(int)$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID5() : NULL,
												NULL, //'#punch_branch#', //12
												NULL, //'#punch_branch_other_id1#',
												NULL, //'#punch_branch_other_id2#',
												NULL, //'#punch_branch_other_id3#',
												NULL, //'#punch_branch_other_id4#',
												NULL, //'#punch_branch_other_id5#',
												NULL, //'#punch_department#', //18
												NULL, //'#punch_department_other_id1#',
												NULL, //'#punch_department_other_id2#',
												NULL, //'#punch_department_other_id3#',
												NULL, //'#punch_department_other_id4#',
												NULL, //'#punch_department_other_id5#',
												NULL, //'#punch_job#',
												NULL, //'#punch_job_other_id1#',
												NULL, //'#punch_job_other_id2#',
												NULL, //'#punch_job_other_id3#',
												NULL, //'#punch_job_other_id4#',
												NULL, //'#punch_job_other_id5#',
												NULL, //'#punch_job_item#',
												NULL, //'#punch_job_item_other_id1#',
												NULL, //'#punch_job_item_other_id2#',
												NULL, //'#punch_job_item_other_id3#',
												NULL, //'#punch_job_item_other_id4#',
												NULL, //'#punch_job_item_other_id5#',
												( isset($this->tmp_data['user'][$user_id]['employee_number']) ) ? $this->tmp_data['user'][$user_id]['employee_number'] : NULL,
												( isset($this->tmp_data['user'][$user_id]['other_id1']) ) ? $this->tmp_data['user'][$user_id]['other_id1'] : NULL,
												( isset($this->tmp_data['user'][$user_id]['other_id2']) ) ? $this->tmp_data['user'][$user_id]['other_id2'] : NULL,
												( isset($this->tmp_data['user'][$user_id]['other_id3']) ) ? $this->tmp_data['user'][$user_id]['other_id3'] : NULL,
												( isset($this->tmp_data['user'][$user_id]['other_id4']) ) ? $this->tmp_data['user'][$user_id]['other_id4'] : NULL,
												( isset($this->tmp_data['user'][$user_id]['other_id5']) ) ? $this->tmp_data['user'][$user_id]['other_id5'] : NULL,
											);

						$date_columns = TTDate::getReportDates( 'transaction', $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
						$processed_data  = array(
												//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
												//'pay_stub' => array('sort' => $row['pay_stub_transaction_date'], 'display' => TTDate::getDate('DATE', $row['pay_stub_transaction_date'] ) ),
												);

						if ( isset($row['psen_ids']) AND is_array($row['psen_ids']) ) {
							$psen_ids = $row['psen_ids'];
							unset($row['psen_ids']);
							foreach($psen_ids as $psen_data ) {
								if ( $this->enable_time_based_distribution == TRUE ) {
									$expanded_gl_rows = $this->expandGLAccountRows( $psen_data, $this->tmp_data['pay_period_distribution'][$user_id][$row['pay_period_id']], $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $replace_arr );
									foreach( $expanded_gl_rows as $gl_row ) {
										$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data, $gl_row );
									}
									unset($expanded_gl_rows, $psen_data);
								} else {
									$psen_data['account'] = $this->replaceGLAccountVariables( $psen_data['account'], $replace_arr );
									//Need to make sure PSEA IDs are strings not numeric otherwise array_merge will re-key them.
									$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data, $psen_data );
								}

							}
							unset($psen_ids, $psen_id, $psen_amount);
						}

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
						$key++;
					}
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1 );
		}

		$this->form_data = $this->data; //Used for exporting.

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function calcPercentDistribution( $type, $psen_data, $distribution_arr, $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $replace_arr  ) {

		$amount_distribution_arr = Misc::PercentDistribution( $psen_data[$type.'_amount'], $distribution_arr );
		//Debug::Arr($amount_distribution_arr, $type .' PSEN Distribution Arr: ', __FILE__, __LINE__, __METHOD__,10);
		if ( is_array($amount_distribution_arr) ) {
			$retarr = array();

			foreach( $amount_distribution_arr as $key => $amount ) {
				if ( $amount == 0 ) {
					continue;
				}

				$account_arr = explode('-', $key );
				if ( isset($account_arr[0]) AND $account_arr[0] != 0 ) { //Branch
					$replace_arr[12] = ( is_object($branch_code_map[(int)$account_arr[0]]) ) ? $branch_code_map[(int)$account_arr[0]]->getManualID() : NULL;
					$replace_arr[13] = ( is_object($branch_code_map[(int)$account_arr[0]]) ) ? $branch_code_map[(int)$account_arr[0]]->getOtherID1() : NULL;
					$replace_arr[14] = ( is_object($branch_code_map[(int)$account_arr[0]]) ) ? $branch_code_map[(int)$account_arr[0]]->getOtherID2() : NULL;
					$replace_arr[15] = ( is_object($branch_code_map[(int)$account_arr[0]]) ) ? $branch_code_map[(int)$account_arr[0]]->getOtherID3() : NULL;
					$replace_arr[16] = ( is_object($branch_code_map[(int)$account_arr[0]]) ) ? $branch_code_map[(int)$account_arr[0]]->getOtherID4() : NULL;
					$replace_arr[17] = ( is_object($branch_code_map[(int)$account_arr[0]]) ) ? $branch_code_map[(int)$account_arr[0]]->getOtherID5() : NULL;
				}

				if ( isset($account_arr[1]) AND $account_arr[1] != 0 ) { //Department
					$replace_arr[18] = ( is_object($department_code_map[(int)$account_arr[1]]) ) ? $department_code_map[(int)$account_arr[1]]->getManualID() : NULL;
					$replace_arr[19] = ( is_object($department_code_map[(int)$account_arr[1]]) ) ? $department_code_map[(int)$account_arr[1]]->getOtherID1() : NULL;
					$replace_arr[20] = ( is_object($department_code_map[(int)$account_arr[1]]) ) ? $department_code_map[(int)$account_arr[1]]->getOtherID2() : NULL;
					$replace_arr[21] = ( is_object($department_code_map[(int)$account_arr[1]]) ) ? $department_code_map[(int)$account_arr[1]]->getOtherID3() : NULL;
					$replace_arr[22] = ( is_object($department_code_map[(int)$account_arr[1]]) ) ? $department_code_map[(int)$account_arr[1]]->getOtherID4() : NULL;
					$replace_arr[23] = ( is_object($department_code_map[(int)$account_arr[1]]) ) ? $department_code_map[(int)$account_arr[1]]->getOtherID5() : NULL;
				}

				if ( isset($account_arr[2]) AND $account_arr[2] != 0 ) { //Job
					$replace_arr[24] = ( is_object($job_code_map[(int)$account_arr[2]]) ) ? $job_code_map[(int)$account_arr[2]]->getManualID() : NULL;
					$replace_arr[25] = ( is_object($job_code_map[(int)$account_arr[2]]) ) ? $job_code_map[(int)$account_arr[2]]->getOtherID1() : NULL;
					$replace_arr[26] = ( is_object($job_code_map[(int)$account_arr[2]]) ) ? $job_code_map[(int)$account_arr[2]]->getOtherID2() : NULL;
					$replace_arr[27] = ( is_object($job_code_map[(int)$account_arr[2]]) ) ? $job_code_map[(int)$account_arr[2]]->getOtherID3() : NULL;
					$replace_arr[28] = ( is_object($job_code_map[(int)$account_arr[2]]) ) ? $job_code_map[(int)$account_arr[2]]->getOtherID4() : NULL;
					$replace_arr[29] = ( is_object($job_code_map[(int)$account_arr[2]]) ) ? $job_code_map[(int)$account_arr[2]]->getOtherID5() : NULL;
				}

				if ( isset($account_arr[3]) AND $account_arr[3] != 0 ) { //Job Item
					$replace_arr[30] = ( is_object($job_item_code_map[(int)$account_arr[3]]) ) ? $job_item_code_map[(int)$account_arr[3]]->getManualID() : NULL;
					$replace_arr[31] = ( is_object($job_item_code_map[(int)$account_arr[3]]) ) ? $job_item_code_map[(int)$account_arr[3]]->getOtherID1() : NULL;
					$replace_arr[32] = ( is_object($job_item_code_map[(int)$account_arr[3]]) ) ? $job_item_code_map[(int)$account_arr[3]]->getOtherID2() : NULL;
					$replace_arr[33] = ( is_object($job_item_code_map[(int)$account_arr[3]]) ) ? $job_item_code_map[(int)$account_arr[3]]->getOtherID3() : NULL;
					$replace_arr[34] = ( is_object($job_item_code_map[(int)$account_arr[3]]) ) ? $job_item_code_map[(int)$account_arr[3]]->getOtherID4() : NULL;
					$replace_arr[35] = ( is_object($job_item_code_map[(int)$account_arr[3]]) ) ? $job_item_code_map[(int)$account_arr[3]]->getOtherID5() : NULL;
				}

				$retarr[] = array(
								  'account' => $this->replaceGLAccountVariables( $psen_data['account'], $replace_arr),
								  'debit_amount' => ( $type == 'debit' ) ? $amount : NULL,
								  'credit_amount' => ( $type == 'credit' ) ? $amount : NULL,
								  );

			}

			//Debug::Arr($retarr, $type .' PSEN Distribution Retarr: ', __FILE__, __LINE__, __METHOD__,10);
			return $retarr;
		}

		return FALSE;

	}
	//Checks to see if a GL Account contains any "Punch" variables, and expands based on them.
	function expandGLAccountRows( $psen_data, $distribution_arr, $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $replace_arr ) {
		//Debug::Arr($psen_data, 'PSEN Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($distribution_arr, 'Distribution Arr ', __FILE__, __LINE__, __METHOD__,10);

		if ( strpos( $psen_data['account'], 'punch' ) !== FALSE ) {
			//Expand account based on percent distribution.
			Debug::Text('Found punch distribution variables...', __FILE__, __LINE__, __METHOD__,10);
			$retarr = array();
			if ( is_array($distribution_arr) ) {
				$retarr = array_merge(
									  $this->calcPercentDistribution( 'credit', $psen_data, $distribution_arr, $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $replace_arr ),
									  $this->calcPercentDistribution( 'debit', $psen_data, $distribution_arr, $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $replace_arr )
									);

			}

			//Debug::Arr($retarr, 'Expanded GL Rows RetArr: ', __FILE__, __LINE__, __METHOD__,10);
			return $retarr;
		} else {
			//Still nede to replace the variables.
			$psen_data['account'] = $this->replaceGLAccountVariables( $psen_data['account'], $replace_arr );
		}

		return array( 0 => $psen_data );
	}

	function replaceGLAccountVariables( $subject, $replace_arr = NULL) {
		$search_arr = array(
							'#default_branch#',
							'#default_branch_other_id1#',
							'#default_branch_other_id2#',
							'#default_branch_other_id3#',
							'#default_branch_other_id4#',
							'#default_branch_other_id5#',
							'#default_department#',
							'#default_department_other_id1#',
							'#default_department_other_id2#',
							'#default_department_other_id3#',
							'#default_department_other_id4#',
							'#default_department_other_id5#',

							'#punch_branch#',
							'#punch_branch_other_id1#',
							'#punch_branch_other_id2#',
							'#punch_branch_other_id3#',
							'#punch_branch_other_id4#',
							'#punch_branch_other_id5#',
							'#punch_department#',
							'#punch_department_other_id1#',
							'#punch_department_other_id2#',
							'#punch_department_other_id3#',
							'#punch_department_other_id4#',
							'#punch_department_other_id5#',

							'#punch_job#',
							'#punch_job_other_id1#',
							'#punch_job_other_id2#',
							'#punch_job_other_id3#',
							'#punch_job_other_id4#',
							'#punch_job_other_id5#',
							'#punch_job_item#',
							'#punch_job_item_other_id1#',
							'#punch_job_item_other_id2#',
							'#punch_job_item_other_id3#',
							'#punch_job_item_other_id4#',
							'#punch_job_item_other_id5#',

							'#employee_number#',
							'#employee_other_id1#',
							'#employee_other_id2#',
							'#employee_other_id3#',
							'#employee_other_id4#',
							'#employee_other_id5#',
							);

		if ( $subject != '' AND is_array($replace_arr) ) {
			$subject = str_replace( $search_arr, $replace_arr, $subject );
		}

		//Handle cases where variables are replaced with nothing or invalid values.
		//5010--99
		$subject = str_replace('--', '-', $subject );

		//-5010-99
		//5010-99-
		//-5010-99-
		if ( substr( $subject, 0, 1) == '-' ) {
			$subject = substr( $subject, 1 );
		}
		if ( substr( $subject, -1) == '-' ) {
			$subject = substr( $subject, 0, -1 );
		}

		return $subject;
	}

	function _outputExportGeneralLedger( $format ) {
		Debug::Text('Generating GL export for Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

		//Calculate sub-total so we know where the journal entries start/stop.

		$enable_grouping = FALSE;
		if ( is_array( $this->formatGroupConfig() ) AND count( $this->formatGroupConfig() ) > 0 ) {
			Debug::Arr($this->formatGroupConfig(), 'Group Config: ', __FILE__, __LINE__, __METHOD__,10);
			$enable_grouping = TRUE;
		}

		$file_name = 'no_data.txt';
		$data = NULL;


		if ( is_array($this->form_data) ) {
			//Need to group the exported data so the number of journal entries can be reduced.
			$this->form_data = Group::GroupBy( $this->form_data, $this->formatGroupConfig() );

			$gle = new GeneralLedgerExport();
			$gle->setFileFormat( $format );

			$prev_group_key = NULL;
			$i=0;
			foreach( $this->form_data as $row ) {
				$group_key = 0;
				if ( $enable_grouping == TRUE ) {
					$comment = array();
					foreach( $this->formatGroupConfig() as $group_column => $group_agg ) {
						if ( is_int( $group_agg ) AND isset($row[$group_column]) AND $group_column != 'account' ) {

							if ( is_array($row[$group_column]) AND isset($row[$group_column]['display']) ) {
								$comment[] = $row[$group_column]['display'];
								$group_key .= crc32( $row[$group_column]['display'] );
							} elseif ( $row[$group_column] != '' )  {
								$comment[] = $row[$group_column];
								$group_key .= $row[$group_column];
							}
						} else {
							$group_key .= 0;
						}
					}
					unset($group_column, $group_agg);
				}
				//Debug::Arr($row, 'GL Export Row: Group Key: '. $group_key , __FILE__, __LINE__, __METHOD__,10);

				if ( $prev_group_key === NULL OR $prev_group_key != $group_key ) {
					if ( $i > 0 ) {
						Debug::Text('Ending previous JE: Group Key: '. $group_key , __FILE__, __LINE__, __METHOD__,10);
						$gle->setJournalEntry($je); //Add previous JE before starting a new one.
					}

					Debug::Text('Starting new JE: Group Key: '. $group_key , __FILE__, __LINE__, __METHOD__,10);
					$je = new GeneralLedgerExport_JournalEntry();
					if ( isset($row['pay_stub_transaction_date']) ) {
						$je->setDate( $row['pay_stub_transaction_date'] );
					} elseif ( isset($row['transaction-date_stamp']) ) {
						$je->setDate( TTDate::parseDateTime($row['transaction-date_stamp']) );
					} else {
						$je->setDate( time() );
					}

					$je->setSource( APPLICATION_NAME );

					if ( isset($comment) AND is_array($comment) AND count($comment) > 0 ) {
 						$je->setComment( implode(' ', $comment ) );
					} else {
						$je->setComment( TTi18n::getText('Payroll') );
					}
				}

				if ( isset($row['debit_amount']) AND $row['debit_amount'] > 0 ) {
					Debug::Text('Adding Debit Record for: '. $row['debit_amount'] , __FILE__, __LINE__, __METHOD__,10);
					$record = new GeneralLedgerExport_Record();
					$record->setAccount( $row['account'] );
					$record->setType( 'debit' );
					$record->setAmount( $row['debit_amount'] );
					$je->setRecord($record);
				}
				if ( isset($row['credit_amount']) AND $row['credit_amount'] > 0 ) {
					Debug::Text('Adding Credit Record for: '. $row['credit_amount'] , __FILE__, __LINE__, __METHOD__,10);
					$record = new GeneralLedgerExport_Record();
					$record->setAccount( $row['account'] );
					$record->setType( 'credit' );
					$record->setAmount( $row['credit_amount'] );
					$je->setRecord($record);
				}
				unset($record);

				$prev_group_key = $group_key;
				$i++;
			}
			$gle->setJournalEntry($je); //Handle last JE here

			if ( $gle->compile() == TRUE ) {
				$data = $gle->getCompiledData();
				Debug::Text('Exporting as: '. $format, __FILE__, __LINE__, __METHOD__,10);

				if ( $format == 'simply' ) {
					$file_name = 'general_ledger_'. str_replace( array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.txt';
				} elseif ( $format == 'quickbooks' ) {
					$file_name = 'general_ledger_'. str_replace( array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.iif';
				} else {
					$file_name = 'general_ledger_'. str_replace( array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.csv';
				}
			}
		}

		return array( 'file_name' => $file_name, 'mime_type' => 'application/text', 'data' => $data );
	}

	function _output( $format = NULL ) {
		$psf = TTnew( 'PayStubFactory' );
		$export_type_options = Misc::trimSortPrefix( $psf->getOptions('export_general_ledger') );
		Debug::Arr($export_type_options, 'Format: '. $format, __FILE__, __LINE__, __METHOD__,10);
		if ( isset($export_type_options[$format]) ) {
			return $this->_outputExportGeneralLedger( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
