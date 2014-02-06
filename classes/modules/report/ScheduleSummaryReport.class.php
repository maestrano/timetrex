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
class ScheduleSummaryReport extends Report {

	var $special_output_format = array(	'pdf_schedule_group_combined',
										'pdf_schedule_group_combined_print',
										'pdf_schedule_group',
										'pdf_schedule_group_print',
										'pdf_schedule_group_pagebreak', //Insert page breaks after each branch/department.
										'pdf_schedule_group_pagebreak_print',
										'pdf_schedule',
										'pdf_schedule_print'
									);
	
	function __construct() {
		$this->title = TTi18n::getText('Schedule Summary Report');
		$this->file_name = 'schedule_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_schedule_summary', $user_id, $company_id ) ) { //Piggyback on timesheet summary permissions.
			return TRUE;
		} else {
			if ( $this->getPermissionObject()->Check('schedule','view', $user_id, $company_id ) == FALSE
					AND $this->getPermissionObject()->Check('schedule','view_child', $user_id, $company_id ) == FALSE
					AND $this->getPermissionObject()->Check('schedule','view_own', $user_id, $company_id ) == TRUE ) {
				Debug::Text('Regular employee viewing their own timesheet...', __FILE__, __LINE__, __METHOD__,10);
				//Regular employee printing timesheet for themselves. Force specific config options.
				//Get current pay period from config, then overwrite it with
				$filter_config = $this->getFilterConfig();
				$this->setFilterConfig( array( 'include_user_id' => array($user_id), 'time_period' => $filter_config['time_period'], 'start_date' => $filter_config['start_date'], 'end_date' => $filter_config['end_date'] ) );
			} else {
				Debug::Text('Supervisor employee not restricting schedule...', __FILE__, __LINE__, __METHOD__,10);
			}

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
										'-2035-user_tag' => TTi18n::gettext('Employee Tags'),
										'-2040-include_user_id' => TTi18n::gettext('Employee Include'),
										'-2050-exclude_user_id' => TTi18n::gettext('Employee Exclude'),
										'-2060-default_branch_id' => TTi18n::gettext('Default Branch'),
										'-2070-default_department_id' => TTi18n::gettext('Default Department'),
										'-2080-schedule_branch_id' => TTi18n::gettext('Schedule Branch'),
										'-2090-schedule_department_id' => TTi18n::gettext('Schedule Department'),
                                        '-2100-custom_filter' => TTi18n::gettext('Custom Filter'),

										'-3000-status_id' => TTi18n::gettext('Schedule Status'),

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
				$retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 15, TRUE );
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$oflf = TTnew( 'OtherFieldListFactory' );
				//User and Punch fields conflict as they are merged together in a secondary process.
				$other_field_names = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), array(10), array( 10 => '' ) );
				if ( is_array($other_field_names) ) {
					$retval = Misc::addSortPrefix( $other_field_names, 9000 );
				}
				break;
            case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'ScheduleSummaryReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'ScheduleSummaryReport', 'custom_column' );
				}
                break;
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'ScheduleSummaryReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'ScheduleSummaryReport', 'custom_column' );
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
										'-1055-city' => TTi18n::gettext('City'),
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
										//'-1230-pay_period' => TTi18n::gettext('Pay Period'),

										'-1290-note' => TTi18n::gettext('Employee Note'),
										'-1295-tag' => TTi18n::gettext('Tags'),

										'-1600-branch' => TTi18n::gettext('Branch'),
										'-1610-department' => TTi18n::gettext('Department'),
										'-1620-schedule_policy' => TTi18n::gettext('Schedule Policy'),
										//'-1630-schedule_type' => TTi18n::gettext('Schedule Type'),
										'-1640-schedule_status' => TTi18n::gettext('Schedule Status'),
										'-1650-absence_policy' => TTi18n::gettext('Absence Policy'),
										//'-1660-date_stamp' => TTi18n::gettext('Date'),
										'-1670-start_time' => TTi18n::gettext('Start Time'),
										'-1680-end_time' => TTi18n::gettext('End Time'),

										'-5000-schedule_note' => TTi18n::gettext('Note'),
							   );

				$retval = array_merge( $retval, (array)$this->getOptions('date_columns'), (array)$this->getOptions('custom_columns'), (array)$this->getOptions('report_static_custom_column') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used

										//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
										'-2010-hourly_rate' => TTi18n::gettext('Hourly Rate'),

										'-2100-total_time' => TTi18n::gettext('Total Time'),
										'-2110-total_time_wage' => TTi18n::gettext('Total Time Wage'),
										'-2112-total_time_wage_burden' => TTi18n::gettext('Total Time Wage Burden'),
										'-2114-total_time_wage_with_burden' => TTi18n::gettext('Total Time Wage w/Burden'),

										'-4000-total_shift' => TTi18n::gettext('Total Shifts'), //Group counter...
							);

				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = $this->getOptions('columns');
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						if ( strpos($column, 'absence_policy') ) {
							//Ignore
						} elseif ( strpos($column, 'start_time') !== FALSE OR strpos($column, 'end_time') !== FALSE ) {
							$retval[$column] = 'time';
						} elseif ( strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE OR strpos($column, 'hourly_rate') !== FALSE ) {
							$retval[$column] = 'currency';
						} elseif ( strpos($column, '_time') OR strpos($column, '_policy') ) {
							$retval[$column] = 'time_unit';
						}
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
								if ( strpos($column, '_hourly_rate') !== FALSE OR strpos($column, 'hourly_rate') !== FALSE ) {
									$retval[$column] = 'avg';
								} else {
									$retval[$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'templates':
				$retval = array(
										'-1010-by_employee+work+total_time' => TTi18n::gettext('Work Time by Employee'),
										'-1020-by_employee+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Employee'),
										'-1030-by_title+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Title'),

										'-1110-by_date_by_full_name+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Date/Employee'),
										'-1120-by_full_name_by_date+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Employee/Date'),

										'-1210-by_branch+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Branch'),
										'-1220-by_department+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Department'),
										'-1230-by_branch_by_department+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Branch/Department'),

										'-1310-by_pay_period+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Pay Period'),
										'-1320-by_pay_period_by_employee+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Pay Period/Employee'),
										'-1330-by_pay_period_by_branch+work+total_time+total_time_wage' => TTi18n::gettext('Work  Time+Wage by Pay Period/Branch'),
										'-1340-by_pay_period_by_department+work+total_time+total_time_wage' => TTi18n::gettext('Work  Time+Wage by Pay Period/Department'),
										'-1350-by_pay_period_by_branch_by_department+work+total_time+total_time_wage' => TTi18n::gettext('Work  Time+Wage by Pay Period/Branch/Department'),

										'-1410-by_employee_by_pay_period+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Employee/Pay Period'),
										'-1420-by_branch_by_pay_period+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Branch/Pay Period'),
										'-1430-by_department_by_pay_period+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Department/Pay Period'),
										'-1440-by_branch_by_department_by_pay_period+work+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Branch/Department/Pay Period'),

										'-1510-by_title_by_start_time+work+total_time+total_time_wage+total_shift' => TTi18n::gettext('Work Time+Wage+Total Shifts by Title/Start Time'),
										'-1520-by_date_by_title+work+total_time+total_time_wage+total_shift' => TTi18n::gettext('Work Time+Wage+Total Shifts by Date/Title'),

										'-2010-by_employee+absence+total_time' => TTi18n::gettext('Absence Time by Employee'),
										'-2020-by_employee+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Employee'),
										'-2030-by_title+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Title'),

										'-2110-by_date_by_full_name+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Date/Employee'),
										'-2120-by_full_name_by_date+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Employee/Date'),

										'-2210-by_branch+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Branch'),
										'-2220-by_department+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Department'),
										'-2230-by_branch_by_department+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Branch/Department'),

										'-2310-by_pay_period+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period'),
										'-2320-by_pay_period_by_employee+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Employee'),
										'-2330-by_pay_period_by_branch+absence+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Pay Period/Branch'),
										'-2340-by_pay_period_by_department+absence+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Pay Period/Department'),
										'-2350-by_pay_period_by_branch_by_department+absence+total_time+total_time_wage' => TTi18n::gettext('Work Time+Wage by Pay Period/Branch/Department'),

										'-2410-by_employee_by_pay_period+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Employee/Pay Period'),
										'-2420-by_branch_by_pay_period+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Branch/Pay Period'),
										'-2430-by_department_by_pay_period+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Department/Pay Period'),
										'-2440-by_branch_by_department_by_pay_period+absence+total_time+total_time_wage' => TTi18n::gettext('Absence Time+Wage by Branch/Department/Pay Period'),


							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						//case 'by_employee+actual_time':
						//	break;
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
										case 'total_time':
											$retval['columns'][] = 'total_time';
											break;
										case 'total_time_wage':
											$retval['columns'][] = 'total_time_wage';
											break;
										case 'absence_policy':
											$retval['columns'][] = 'absence_policy';
											break;
										//Filter
										case 'work':
											$retval['filter']['status_id'] = array(10);
											break;
										case 'absence':
											$retval['filter']['status_id'] = array(20);
											break;
										case 'total_shift':
											$retval['columns'][] = 'total_shift';
											break;

										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'last_name';
											$retval['group'][] = 'first_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_title':
											$retval['columns'][] = 'title';

											$retval['group'][] = 'title';

											$retval['sort'][] = array('title' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'branch';

											$retval['group'][] = 'branch';

											$retval['sort'][] = array('branch' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'department';

											$retval['group'][] = 'department';

											$retval['sort'][] = array('department' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';

											$retval['group'][] = 'branch';
											$retval['group'][] = 'department';

											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
											break;
										case 'by_pay_period':
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_pay_period_by_employee':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_pay_period_by_branch':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'branch';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('branch' => 'asc');
											break;
										case 'by_pay_period_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'department';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'department';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
											break;
										case 'by_pay_period_by_branch_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch';
											$retval['group'][] = 'department';

											$retval['sub_total'][] = 'pay_period';
											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
											break;
										case 'by_employee_by_pay_period':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = array('full_name' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_branch_by_pay_period':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_department_by_pay_period':
											$retval['columns'][] = 'department';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'department';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'department';

											$retval['sort'][] = array('department' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_branch_by_department_by_pay_period':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch';
											$retval['group'][] = 'department';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch';
											$retval['sub_total'][] = 'department';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_date_by_full_name':
											$retval['columns'][] = 'date_stamp';
											$retval['columns'][] = 'full_name';

											$retval['group'][] = 'date_stamp';
											$retval['group'][] = 'full_name';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('full_name' => 'asc');
											break;
										case 'by_date_by_title':
											$retval['columns'][] = 'date_stamp';
											$retval['columns'][] = 'title';

											$retval['group'][] = 'date_stamp';
											$retval['group'][] = 'title';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('title' => 'asc');
											break;
										case 'by_full_name_by_date':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'date_stamp';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'date_stamp';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = array('full_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											break;
										case 'by_title_by_start_time':
											$retval['columns'][] = 'title';
											$retval['columns'][] = 'start_time';
											$retval['columns'][] = 'total_shift';

											$retval['group'][] = 'title';
											$retval['group'][] = 'start_time';

											$retval['sub_total'][] = 'title';

											$retval['sort'][] = array('title' => 'asc');
											$retval['sort'][] = array('start_time' => 'asc');
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

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array('schedule' => array(), 'user' => array(), 'total_shift' => array() );

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		if ( $this->getPermissionObject()->Check('schedule','view') == FALSE OR $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			$hlf = TTnew( 'HierarchyListFactory' );
			$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = array();
			$wage_permission_children_ids = array();
		}
		if ( $this->getPermissionObject()->Check('schedule','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('schedule','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('schedule','view_own') ) {
				$permission_children_ids[] = $this->getUserObject()->getID();
			}

			$filter_data['permission_children_ids'] = $permission_children_ids;
		}
		//Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
		if ( $this->getPermissionObject()->Check('wage','view') == TRUE ) {
			$wage_permission_children_ids = TRUE;
		} elseif ( $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('wage','view_child') == FALSE ) {
				$wage_permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('wage','view_own') ) {
				$wage_permission_children_ids[] = $this->getUserObject()->getID();
			}
		}
		//Debug::Text(' Permission Children: '. count($permission_children_ids) .' Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($permission_children_ids, 'Permission Children: '. count($permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($wage_permission_children_ids, 'Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);

		if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' );
			$job_status_options = $jlf->getOptions('status');
		} else {
			$job_status_options = array();
		}

		$pay_period_ids = array();

		if ( strpos( $format, 'schedule' ) === FALSE ) { //Avoid running these queries when printing out the schedule.
			$slf = TTnew( 'ScheduleListFactory' );
			$slf->getScheduleSummaryReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, NULL, NULL, NULL, array('last_name' => 'asc') ); //Sort by last name mainly for the PDF schedule for printing.
			Debug::Text(' Total Rows: '. $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $slf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
			if ( $slf->getRecordCount() > 0 ) {
				foreach ( $slf as $key => $s_obj ) {
					$hourly_rate = 0;
					if ( $wage_permission_children_ids === TRUE OR in_array( $s_obj->getColumn('user_id'), $wage_permission_children_ids) ) {
						$hourly_rate = $s_obj->getColumn( 'user_wage_hourly_rate' );
					}

					$date_stamp_epoch = TTDate::strtotime( $s_obj->getColumn('date_stamp') );

					$shift_arr = array(
						'user_id' => $s_obj->getColumn('user_id'),
						'status_id' => $s_obj->getColumn('status_id'),
						'group' => $s_obj->getColumn('group'),
						'default_branch' => $s_obj->getColumn('default_branch'),
						'default_department' => $s_obj->getColumn('default_department'),
						'branch' => $s_obj->getColumn('branch'),
						'department' => $s_obj->getColumn('department'),
						'job' => $s_obj->getColumn('job'),
						'job_status_id' => Option::getByKey($s_obj->getColumn('job_status_id'), $job_status_options, NULL ),
						'job_manual_id' => $s_obj->getColumn('job_manual_id'),
						'job_description' => $s_obj->getColumn('job_description'),
						'job_branch' => $s_obj->getColumn('job_branch'),
						'job_department' => $s_obj->getColumn('job_department'),
						'job_group' => $s_obj->getColumn('job_group'),
						'job_item' => $s_obj->getColumn('job_item'),
						'quantity' => $s_obj->getColumn('quantity'),
						'bad_quantity' => $s_obj->getColumn('bad_quantity'),

						'total_time' => $s_obj->getColumn('total_time'),
						'total_time_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $s_obj->getColumn('total_time') ), $hourly_rate ), FALSE ),
						'total_time_wage_burden' => Misc::MoneyFormat( bcmul( TTDate::getHours( $s_obj->getColumn('total_time') ), bcmul( $hourly_rate, bcdiv( $s_obj->getColumn('user_labor_burden_percent'), 100 ) ) ), FALSE ),
						'total_time_wage_with_burden' => Misc::MoneyFormat( bcmul( TTDate::getHours( $s_obj->getColumn('total_time') ), bcmul( $hourly_rate, bcadd( bcdiv( $s_obj->getColumn('user_labor_burden_percent'), 100 ), 1) ) ), FALSE ),

						'other_id1' => $s_obj->getColumn('other_id1'),
						'other_id2' => $s_obj->getColumn('other_id2'),
						'other_id3' => $s_obj->getColumn('other_id3'),
						'other_id4' => $s_obj->getColumn('other_id4'),
						'other_id5' => $s_obj->getColumn('other_id5'),

						'date_stamp' => $date_stamp_epoch,

						'schedule_policy' => $s_obj->getColumn('schedule_policy'),
						'absence_policy' => $s_obj->getColumn('absence_policy'),

						//'schedule_type' => Option::getByKey( $s_obj->getType(), $s_obj->getOptions('type'), NULL ), //Recurring/Scheduled?
						'schedule_status' => Option::getByKey( $s_obj->getStatus(), $s_obj->getOptions('status'), NULL ),

						'start_time' => TTDate::strtotime( $s_obj->getColumn('start_time') ),
						'end_time' => TTDate::strtotime( $s_obj->getColumn('end_time') ),

						'user_wage_id' => $s_obj->getColumn('user_wage_id'),
						'hourly_rate' => Misc::MoneyFormat( $hourly_rate, FALSE ),

						'pay_period_start_date' => strtotime( $s_obj->getColumn('pay_period_start_date') ),
						'pay_period_end_date' => strtotime( $s_obj->getColumn('pay_period_end_date') ),
						'pay_period_transaction_date' => strtotime( $s_obj->getColumn('pay_period_transaction_date') ),
						'pay_period' => strtotime( $s_obj->getColumn('pay_period_transaction_date') ),
						'pay_period_id' => $s_obj->getColumn('pay_period_id'),

						'schedule_note' => $s_obj->getColumn('note'),

						'total_shift' => 1,
						);
					unset($hourly_rate);

					$this->tmp_data['schedule'][$s_obj->getColumn('user_id')][] = $shift_arr;

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
				}
			}
			//Debug::Arr($this->tmp_data['schedule'], 'Schedule Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
			//Debug::Arr($this->form_data, 'Schedule Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
		}

		unset($filter_data['status_id']); //This is for schedule status, not user status.

		//If we're printing the schedule, make sure we include the required columns.
		if ( in_array( $format, $this->special_output_format ) ) {
			$this->config['columns_data']['first_name'] = TRUE;
			$this->config['columns_data']['last_name'] = TRUE;
		}

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = $this->form_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		//Add OPEN user to the list so it can printed on schedules.
		$this->tmp_data['user'][0] = $this->form_data['user'][0] = array(
										   'first_name' => TTi18n::getText('OPEN'),
										   'last_name' => '',
										   );

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['schedule']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['schedule']) ) {
			foreach( $this->tmp_data['schedule'] as $user_id => $level_1 ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					foreach( $level_1 as $key => $row ) {
						$date_columns = TTDate::getReportDates( NULL, $row['date_stamp'], FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
						$processed_data  = array(
												//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
												);

						$this->data[] = array_merge( $row, $this->tmp_data['user'][$user_id], $date_columns, $processed_data );

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
						$key++;
					}
				}
			}

			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function scheduleHeader( $branch = NULL, $department = NULL, $user = NULL, $new_page = TRUE ) {

		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(16) );
		if ( $new_page == TRUE ) {
			$this->pdf->Cell(0, $this->_pdf_scaleSize(5), TTi18n::getText('Schedule'), 0, 0, 'C');
			$this->pdf->Ln( $this->_pdf_fontSize(2) );
		}

		$config = $this->getFilterConfig();
		if ( $new_page == TRUE AND isset($config['start_date']) AND isset($config['end_date']) ) {
			$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(6) );
			$this->pdf->setY( $this->pdf->getY() + 4);
			$this->pdf->Cell(0, $this->_pdf_fontSize(3), TTDate::getDate('DATE', $config['start_date']) .' - '. TTDate::getDate('DATE', $config['end_date']), 0, 0, 'C');
			$this->pdf->Ln();
		}
		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(16) );

		$label = array();
		if ( $branch !== 0 AND $branch != '' ) {
			$label[] = TTi18n::getText('Branch').': '. $branch;
		}
		if ( $department !== 0 AND $department != '' ) {
			$label[] = TTi18n::getText('Department').': '. $department;
		} else {
			if ( $branch !== 0 AND $branch != '' ) {
				$label[] = TTi18n::getText('Department').': N/A';
			}
		}

		if ( $user !== 0 AND $user != '' ) {
			$label[] = TTi18n::getText('Employee').': '. $user;
		}

		//Debug::Arr($label, 'Label: Branch: '. $branch .' Department: '. $department, __FILE__, __LINE__, __METHOD__,10);

		if ( count($label) > 0 ) {
			if ( $new_page == FALSE ) {
				$this->pdf->setY( $this->pdf->getY() + 2);
			}
			$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(8) );
			$this->pdf->Cell(0, $this->_pdf_scaleSize(4), implode('                        ', $label ), 0, 0, 'C');
			$this->pdf->Ln();
		} else {
			//$this->pdf->Ln( $this->_pdf_fontSize(5) );
		}

		return TRUE;
	}

	function scheduleFooter() {
		global $config_vars;

		$margins = $this->pdf->getMargins();

		//Don't scale footer text lines as they aren't that important anyways.
		$this->pdf->SetFont($this->config['other']['default_font'], '', 8 );

		//Save x,y and restore after footer is set.
		$x = $this->pdf->getX();
		$y = $this->pdf->getY();

		//Jump to end of page.
		if ( ($this->pdf->getPageHeight()-$margins['bottom']-$margins['top']-15) > $y ) {
			$this->pdf->setY( $this->pdf->getPageHeight()-$margins['bottom']-$margins['top']-15 );
		}

		$company_obj = $this->getUserObject()->getCompanyObject();
		if ( is_object( $company_obj ) AND $company_obj->getProductEdition() > 10 ) {
			if ( $config_vars['other']['force_ssl'] == 1 ) {
				$protocol = 'https';
			} else {
				$protocol = 'http';
			}

			$url = $protocol .'://'.Misc::getHostName().Environment::getBaseURL() .'ical/ical.php';

			$this->pdf->Cell( ($this->pdf->getPageWidth()-$margins['right']-$margins['left']), 5, TTi18n::getText('Synchronize this schedule to your desktop/mobile phone calendar application').': '. $url, 1, 0, 'C', 0, NULL, 1 );
			$this->pdf->Ln();
		}

		$this->pdf->SetFont($this->config['other']['default_font'], '', 8 );
		$this->pdf->Cell( ($this->pdf->getPageWidth()-$margins['right']), 5, TTi18n::getText('Page').' '. $this->pdf->PageNo() .' of '. $this->pdf->getAliasNbPages(), 0, 0, 'C', 0 );
		$this->pdf->Ln();

		$this->pdf->SetFont($this->config['other']['default_font'], '', 6 );
		$this->pdf->Cell( ($this->pdf->getPageWidth()-$margins['right']), 5, TTi18n::gettext('Report Generated By').' '. APPLICATION_NAME .' v'. APPLICATION_VERSION .' @ '. TTDate::getDate('DATE+TIME', $this->start_time ), 0, 0, 'C', 0 );

		$this->pdf->setX( $x );
		$this->pdf->setY( $y );

		return TRUE;
	}

	function scheduleNoData() {
		$this->pdf->AddPage();
		$this->pdf->Ln(50);
		$this->pdf->setTextColor( 255, 0, 0 );
		$this->pdf->SetFont($this->config['other']['default_font'],'B', $this->_pdf_fontSize(18) );
		$this->pdf->Cell( $this->pdf->getPageWidth(), $this->_pdf_scaleSize(10), TTi18n::getText('NO DATA MATCHES CRITERIA'), 0, 0, 'C', 0, '', 1);
		$this->pdf->Ln(100);

		return TRUE;
	}

	function scheduleAddPage() {
		$this->scheduleFooterWeek();
		$this->scheduleFooter();
		$this->pdf->AddPage();
		//$this->pdf->setXY( 0, 0 );
		return TRUE;
	}

	function scheduleCheckPageBreak( $height, $add_page = TRUE ) {
		$margins = $this->pdf->getMargins();

		if ( ($this->pdf->getY()+$height) > ($this->pdf->getPageHeight()-$margins['bottom']-$margins['top']-10) ) {
			//Debug::Text('Detected Page Break needed...', __FILE__, __LINE__, __METHOD__,10);
			$this->scheduleAddPage();

			return TRUE;
		}
		return FALSE;
	}

	function scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format ) {
		if ( isset($column_widths['day']) ) {
			$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(8) );

			if ( $format != 'pdf_schedule' AND $format != 'pdf_schedule_print' ) {
				$this->pdf->Cell($column_widths['label'], 4, TTi18n::getText('Employee'), 1, 0, 'C', 0, NULL, 1);
			}

			$calendar_header = TTDate::getDayOfWeekArrayByStartWeekDay( $start_week_day );
			foreach( $calendar_header as $header_name ) {
				$this->pdf->Cell($column_widths['day'], $this->_pdf_scaleSize(4), $header_name, 1, 0, 'C', 0, NULL, 1);
			}

			$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

			$this->pdf->Ln();

			return TRUE;
		}

		return FALSE;
	}

	function scheduleWeekHeader( $calendar_array, $column_widths, $format, $new_page = FALSE ) {
		if ( is_array($calendar_array) AND isset($column_widths['day']) ) {
			$this->pdf->setFillColor(220,220,220);

			$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(8) );

			if ( $format != 'pdf_schedule' AND $format != 'pdf_schedule_print' ) {
				$this->pdf->Cell($column_widths['label'], $this->_pdf_scaleSize(4), '', 1, 0, 'C', 1);
			}
			$i=0;
			foreach( $calendar_array as $calendar_day ) {
				if ( ( $i == 0 AND $new_page == TRUE ) OR $calendar_day['isNewMonth'] == TRUE ) {
					$this->pdf->Cell($column_widths['day']*0.75, $this->_pdf_scaleSize(4), $calendar_day['month_name'], 'TBL', 0, 'L', 1);
				} else {
					$this->pdf->Cell($column_widths['day']*0.75, $this->_pdf_scaleSize(4), '', 'TBL', 0, 'L', 1);
				}
				$this->pdf->Cell($column_widths['day']*0.25, $this->_pdf_scaleSize(4), $calendar_day['day_of_month'], 'TBR', 0, 'R', 1);

				$i++;
			}

			$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

			$this->pdf->Ln();

			$this->pdf->setFillColor(255,255,255);
			return TRUE;
		}

		return FALSE;
	}

	function scheduleUserWeek( $schedule_data, $calendar_array, $start_week_day, $column_widths, $format, $row = 0 ) {
		if ( is_array($calendar_array) AND isset($column_widths['day']) AND is_array($schedule_data) AND count($schedule_data) > 0 ) {
			if ( $row % 2 == 0 ) {
				$row_bg_color_arr = array(255,255,255);
			} else {
				$row_bg_color_arr = array(240,240,240);
			}

			//Check to see if the employee is scheduled at all this week.
			switch ( $format ) {
				case 'pdf_schedule_group':
				case 'pdf_schedule_group_print':
				case 'pdf_schedule_group_pagebreak':
				case 'pdf_schedule_group_pagebreak_print':
					//
					// Group - Separate (branch/department on their own pages)
					//
					$s=0;
					$max_shifts_per_day = 0;
					foreach( $calendar_array as $calendar_day ) {
						if ( isset($schedule_data[$calendar_day['date_stamp']]) ) {
							$shifts_per_day = count($schedule_data[$calendar_day['date_stamp']]);
							if ( $shifts_per_day > $max_shifts_per_day ) {
								$max_shifts_per_day = $shifts_per_day;
							}
							$s++;
						}
					}

					if ( $s > 0 ) {
						$schedule_key = key($schedule_data);
						$user_id = $schedule_data[$schedule_key][0]['user_id'];
						if ( isset($this->form_data['user'][$user_id]) ) {
							$user_data = $this->form_data['user'][$user_id];

							$i=0;
							$row_height = $this->_pdf_fontSize(5);
							$max_row_height = $row_height*$max_shifts_per_day;

							foreach( $calendar_array as $calendar_day ) {
								$date_stamp = $calendar_day['date_stamp'];

								//Handle split shifts in the same day, count how many shifts exist.
								//Debug::Text('User ID: '. $user_id .' Date: '. $date_stamp .' Total Shifts: '. $max_shifts_per_day .' Height: '. $max_row_height .' Max Shifts: '. $max_shifts_per_day, __FILE__, __LINE__, __METHOD__,10);
								if ( $i == 0 ) {
									$row_top_y = $this->pdf->getY();
									$row_bottom_y = $this->pdf->getY()+$max_row_height;
									$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(8) );
									$this->pdf->Cell( $column_widths['label'], $max_row_height, $user_data['first_name'] .' '. $user_data['last_name'], 'LR', 0, 'C', TRUE, NULL, 1);
									$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );
								}

								if ( isset($schedule_data[$date_stamp]) ) {

									$shifts_per_day = 0;
									foreach( $schedule_data[$date_stamp] as $schedule_data_shift ) {
										if ( isset($schedule_data_shift['status_id']) AND $schedule_data_shift['status_id'] == 20 ) {
											$this->pdf->setTextColor(255,0,0);
											$this->pdf->Cell($column_widths['day'], $row_height, ( $schedule_data_shift['absence_policy'] != '' ) ? $schedule_data_shift['absence_policy'] : TTi18n::getText('N/A'), 'LR', 2, 'C', TRUE, NULL, 1);
											$this->pdf->setTextColor(0,0,0);
										} else {
											$this->pdf->Cell($column_widths['day'], $row_height, $schedule_data_shift['start_time'] .' - '. $schedule_data_shift['end_time'], 'LR', 2, 'C', TRUE, NULL, 1);
										}
										$shifts_per_day++;
									}

									if ( $shifts_per_day < $max_shifts_per_day ) {
										$this->pdf->Cell($column_widths['day'], ($max_shifts_per_day-$shifts_per_day)*$row_height, '', 'LR', 2, 'C', 1);
									}
								} else {
									//Debug::Text('   No Shifts: User ID: '. $user_id .' Date: '. $date_stamp .' Height: '. $max_row_height, __FILE__, __LINE__, __METHOD__,10);
									$this->pdf->Cell($column_widths['day'], $max_row_height, '', 'LR', 2, 'C', 1);
								}

								$this->pdf->setXY( $this->pdf->getX()+$column_widths['day'], $row_top_y );
								$i++;
							}

							$this->pdf->setY( $row_bottom_y ); //Last shift, set Y to end of cell so horizontal line can be drawn in the correct place.
						}

						return TRUE;
					}
					break;
				case 'pdf_schedule':
				case 'pdf_schedule_print':
				case 'pdf_schedule_group_combined':
				case 'pdf_schedule_group_combined_print':
					//
					// Group - Combined Branch/Departments
					//
					$user_id = FALSE;
					$s=0;
					$max_lines_per_day = 0;

					//Keep track of unique branch/departments, if it exceeds 1 then always display them
					$unique_branch = array();
					$unique_department = array();

					foreach( $calendar_array as $calendar_day ) {
						$date_stamp = $calendar_day['date_stamp'];
						$lines_per_day = 0;
						if ( isset($schedule_data[$date_stamp]) ) {
							$lines_per_day += count($schedule_data[$date_stamp]);
							foreach( $schedule_data[$date_stamp] as $branch => $level_2 ) {
								$unique_branch[$branch] = TRUE;

								$lines_per_day += count($level_2);
								foreach( $level_2 as $department => $level_3 ) {
									$unique_department[$department] = TRUE;

									$lines_per_day += count($level_3);
									if ( $user_id == FALSE AND isset($level_3[0]['user_id']) ) {
										$user_id = $level_3[0]['user_id'];
									}

									if ( ( count($unique_department) == 1 AND $department == $level_3[0]['default_department'] ) ) {
										$lines_per_day--;
									}

									//Debug::Text('aLines Per Day: '. $lines_per_day .' Max: '. $max_lines_per_day .' Branch: '. $branch .' Department: '. $department .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);
									$s++;
								}

								//Remove lines if they match default branch/department to save space.
								if ( ( count($unique_branch) == 1 AND isset($level_3) AND $branch == $level_3[0]['default_branch'] ) ) {
									$lines_per_day--;
								}
								//Debug::Text('bLines Per Day: '. $lines_per_day .' Max: '. $max_lines_per_day .' Branch: '. $branch .' Department: '. $department .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);

							}

							if ( $lines_per_day > $max_lines_per_day ) {
								$max_lines_per_day = $lines_per_day;
							}
						}
					}
					unset($date_stamp, $line_per_day );
					//Debug::Text('Max Lines Per Day: '. $max_lines_per_day .' User ID: '. $user_id .' Row: '. $row, __FILE__, __LINE__, __METHOD__,10);

					//Track if the user is assigned to multiple branches/departments, if we are going to display even one in the week, we may as well
					//display them all as it doesn't take up anymore space.
					$multiple_branches = FALSE;
					if ( count($unique_branch) > 1 ) {
						$multiple_branches = TRUE;
						unset($unique_branch);
					}

					$multiple_departments = FALSE;
					if ( count($unique_department) > 1 ) {
						$multiple_departments = TRUE;
						unset($unique_departments);
					}

					if ( $s > 0 ) {
						$schedule_key = key($schedule_data);

						if ( isset($this->form_data['user'][$user_id]) ) {
							$user_data = $this->form_data['user'][$user_id];

							$i=0;
							$row_height = 5;
							if ( $this->scheduleCheckPageBreak( ($row_height*$max_lines_per_day)+5, TRUE ) == TRUE ) {
								$this->scheduleHeader();
								$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );
								$this->scheduleWeekHeader( $calendar_array, $column_widths, $format, TRUE );
							}

							$top_y = $this->pdf->getY();

							foreach( $calendar_array as $calendar_day ) {
								$date_stamp = $calendar_day['date_stamp'];

								//Handle split shifts in the same day, count how many shifts exist.
								//Debug::Text('User ID: '. $user_data['first_name']  .'('.$user_id.') Date: '. $date_stamp .' Total Shifts: '. $max_lines_per_day .' Row: '. $row, __FILE__, __LINE__, __METHOD__,10);

								if ( $i == 0 AND ( $format != 'pdf_schedule' AND $format != 'pdf_schedule_print' ) ) {
									$this->pdf->setFillColorArray( $row_bg_color_arr );
									$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(8) );
									$this->pdf->Cell( $column_widths['label'], $row_height*$max_lines_per_day, $user_data['first_name'] .' '. $user_data['last_name'], 'BLR', 0, 'C', TRUE, NULL, 1);
									$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );
								}

								//$this->pdf->setLineWidth( 0.4 );
								$this->pdf->setFillColor(0,0,0);
								$this->pdf->Line( $this->pdf->getX(), $this->pdf->getY(), $this->pdf->getX()+$column_widths['day'], $this->pdf->getY() );
								$this->pdf->setFillColor(255,255,255);

								$x=0;
								if ( isset($schedule_data[$date_stamp]) ) {
									foreach( $schedule_data[$date_stamp] as $branch => $level_2 ) {
										if ( $branch !== 0 AND ( $multiple_branches == TRUE OR $branch != $level_3[0]['default_branch'] ) ) { //Don't display the employees default branch to save space.
											$this->pdf->setFillColor(215,215,215);
											$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(8) );
											$this->pdf->Cell($column_widths['day'], $row_height, $branch, 'LR', 2, 'C', TRUE, NULL, 1);
											$x++;
										}
										foreach( $level_2 as $department => $level_3 ) {
											if ( $department !== 0 AND ( $multiple_departments == TRUE OR $department != $level_3[0]['default_department'] ) ) { //Don't display the employees default branch to save space.
												$this->pdf->setFillColor(230,230,230);
												$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(8) );
												$this->pdf->Cell($column_widths['day'], $row_height, $department, 'LR', 2, 'C', TRUE, NULL, 1);
												$x++;
											}

											//$this->pdf->setFillColor(255,255,255);
											$this->pdf->setFillColorArray( $row_bg_color_arr );
											$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );
											$schedule_shift_label = array();
											foreach( $level_3 as $schedule_data_shift ) {
												if ( isset($schedule_data_shift['status_id']) AND $schedule_data_shift['status_id'] == 20 ) {
													$this->pdf->setTextColor(255,0,0);
													$this->pdf->Cell($column_widths['day'], $row_height, ( $schedule_data_shift['absence_policy'] != '' ) ? $schedule_data_shift['absence_policy'] : TTi18n::getText('N/A'), 'LR', 2, 'C', TRUE, NULL, 1);
													$this->pdf->setTextColor(0,0,0);
												} else {
													$this->pdf->Cell($column_widths['day'], $row_height, $schedule_data_shift['start_time'] .' - '. $schedule_data_shift['end_time'], 'LR', 2, 'C', TRUE, NULL, 1);
												}

												$x++;
											}
										}
									}
								}

								for ( $y = $x; $y < $max_lines_per_day; $y++) {
									$this->pdf->setFillColorArray( $row_bg_color_arr );
									$this->pdf->Cell($column_widths['day'], $row_height, '', 'LR', 2, 'C', 1);
								}

								$this->pdf->setXY( $this->pdf->getX()+$column_widths['day'], $top_y ); //Setting Y by itself resets X.

								$i++;
							}

							$this->pdf->Ln( $row_height*$max_lines_per_day );
						}

						return TRUE;
					}

					break;
			}
		}

		return FALSE;
	}

	function scheduleFooterWeek() {
		$week_width = $this->pdf->getPageWidth()-$this->config['other']['left_margin'];
		$this->pdf->Line( $this->pdf->getX(), $this->pdf->getY(), $week_width, $this->pdf->getY() );

		return TRUE;
	}

	function _outputPDFSchedule( $format ) {
		Debug::Text(' Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object($current_company) ) {
			Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		$filter_data = $this->getFilterConfig();
		$columns = Misc::trimSortPrefix( $this->getOptions('columns') );

		$adjust_x = 10;
		$adjust_y = 10;

		//Required fields
		// 'first_name', 'last_name', 'branch', 'department', 'start_time', 'end_time'
		$start_week_day = 0;
		if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getUserPreferenceObject() ) ) {
			$start_week_day = $this->getUserObject()->getUserPreferenceObject()->getStartWeekDay();
		}

		//Debug::Arr($this->form_data, 'Form Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->data, 'Data: ', __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), 2, NULL, TTi18n::getText('Querying Database...') ); //Iterations need to be 2, otherwise progress bar is not created.
		$this->getProgressBarObject()->set( $this->getAMFMessageID(), 2 );

		$sf = TTNew('ScheduleFactory');

		//getScheduleArray() doesn't accept pay_period_ids, so no data is returned if a time period of "last_pay_period" is selected.
		if ( isset($filter_data['pay_period_id']) ) {
			unset($filter_data['pay_period_id']);

			$filter_data['start_date'] = TTDate::getBeginDayEpoch( time()-(86400*14) ); //Default to the last 14days.
			$filter_data['end_date'] = TTDate::getEndDayEpoch( time()-86400 );
		}
		$raw_schedule_shifts = $sf->getScheduleArray( $filter_data );

		if ( is_array($raw_schedule_shifts) ) {
			//Debug::Arr($raw_schedule_shifts, 'Raw Schedule Shifts: ', __FILE__, __LINE__, __METHOD__,10);
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($raw_schedule_shifts, COUNT_RECURSIVE), NULL, TTi18n::getText('Retrieving Data...') );
			$key=0;
			foreach( $raw_schedule_shifts as $date_stamp => $day_schedule_shifts ) {
				foreach( $day_schedule_shifts as $shift_arr ) {
					$this->form_data['schedule_by_branch'][$shift_arr['branch']][$shift_arr['department']][$shift_arr['last_name'].$shift_arr['first_name']][$date_stamp][] = $shift_arr;

					//Need to be able to sort employees by last name first. Use names as keys instead of user_ids.
					//$this->form_data['schedule_by_user'][$shift_arr['user_id']][$date_stamp][$shift_arr['branch']][$shift_arr['department']][] = $shift_arr;
					$this->form_data['schedule_by_user'][$shift_arr['last_name'].'_'.$shift_arr['first_name']][$date_stamp][$shift_arr['branch']][$shift_arr['department']][] = $shift_arr;

					if ( !isset($this->form_data['dates']['start_date']) OR $this->form_data['dates']['start_date'] > $date_stamp ) {
						$this->form_data['dates']['start_date'] = $date_stamp;
					}
					if ( !isset($this->form_data['dates']['end_date']) OR $this->form_data['dates']['end_date'] < $date_stamp ) {
						$this->form_data['dates']['end_date'] = $date_stamp;
					}

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}
			}
			unset($date_stamp, $raw_schedule_shifts, $day_schedule_shifts);
		} else {
			Debug::Text('No schedule shifts returned...', __FILE__, __LINE__, __METHOD__,10);
		}

		//Initialize array element if it doesn't exist to prevent PHP warning.
		if ( !isset($this->form_data['schedule_by_user']) ) {
			$this->form_data['schedule_by_user'] = array();
		}
		//Debug::Arr($this->form_data['schedule_by_branch'], '2Raw Schedule Shifts: ', __FILE__, __LINE__, __METHOD__,10);

		//Debug::Arr($this->form_data['dates'], 'Dates: ', __FILE__, __LINE__, __METHOD__,10);
		//If pay periods are requested, we need to convert those to start/end dates.
		if ( isset($this->form_data['dates']['start_date']) AND isset($this->form_data['dates']['end_date']) AND ( !isset($filter_data['start_date']) OR !isset($filter_data['end_date']) ) ) {
			$filter_data['start_date'] = strtotime( $this->form_data['dates']['start_date']) ;
			$filter_data['end_date'] = strtotime( $this->form_data['dates']['end_date'] );
		}

		if ( isset($filter_data['start_date']) AND isset($filter_data['end_date']) ) {
			$pdf_created_date = time();

			$this->pdf = new TTPDF( $this->config['other']['page_orientation'], 'mm', $this->config['other']['page_format'], $this->getUserObject()->getCompanyObject()->getEncoding() );

			$this->pdf->SetAuthor( APPLICATION_NAME );
			$this->pdf->SetTitle( $this->title );
			$this->pdf->SetSubject( APPLICATION_NAME .' '. TTi18n::getText('Report') );

			$this->pdf->setMargins( $this->config['other']['left_margin'], $this->config['other']['top_margin'], $this->config['other']['right_margin'] );
			//Debug::Arr($this->config['other'], 'Margins: ', __FILE__, __LINE__, __METHOD__,10);

			$this->pdf->SetAutoPageBreak( FALSE, 0 );

			$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10) );

			//Debug::Arr($this->form_data, 'zabUser Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

			$calendar_array = TTDate::getCalendarArray($filter_data['start_date'], $filter_data['end_date'], $start_week_day );
			//Debug::Arr($calendar_array, 'Calendar Array: ', __FILE__, __LINE__, __METHOD__,10);

			switch ( $format ) {
				case 'pdf_schedule_group':
				case 'pdf_schedule_group_print':
				case 'pdf_schedule_group_pagebreak':
				case 'pdf_schedule_group_pagebreak_print':
					//
					// Group - Separate (branch/department on their own pages)
					//

					//Start displaying dates/times here. Start with header.
					$column_widths = array(
										'line' => 5,
										'label' => 30,
										'day' => ($this->pdf->getPageWidth()-$this->config['other']['left_margin']-$this->config['other']['right_margin']-30)/7,
										);

					if ( isset($this->form_data['schedule_by_branch']) ) {
						$this->pdf->AddPage( $this->config['other']['page_orientation'], 'Letter' );

						$n=0;
						ksort($this->form_data['schedule_by_branch']);
						foreach( $this->form_data['schedule_by_branch'] as $branch => $level_2 ) {
							ksort($level_2);
							foreach( $level_2 as $department => $level_3 ) {
								ksort($level_3);

								if ( $format == 'pdf_schedule_group_pagebreak' OR $format == 'pdf_schedule_group_pagebreak_print') {
									//Insert page breaks after each branch/department in this mode.
									if ( $n > 0 ) {
										$this->pdf->AddPage( $this->config['other']['page_orientation'], 'Letter' );
									}
									$page_break = TRUE;
								} else {
									$page_break = ( $n == 0 ) ? TRUE : $this->scheduleCheckPageBreak( 30, TRUE );
								}

								$this->scheduleHeader( $branch, $department, NULL, $page_break );
								$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );

								//FIXME: Find a better way to determine how many iterations there will be in this loop.
								$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($calendar_array), NULL, TTi18n::getText('Generating Schedules...') );
								$key=0;
								$i=0;
								foreach( $calendar_array as $calendar_day ) {
									if ( $i % 7 == 0 ) {
										$calendar_week_array = array_slice( $calendar_array, $i, 7 );
										if ( $i != 0 ) {
											$this->scheduleFooterWeek();
										}

										$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format );

										$s=0;
										foreach( $level_3 as $user_id => $user_schedule ) {
											if ( $this->_pdf_checkMaximumPageLimit() == FALSE ) {
												Debug::Text('Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__,10);
												//Exceeded maximum pages, stop processing.
												$this->_pdf_displayMaximumPageLimitError();
												break 4;
											}

											//Handle page break.
											$page_break_height = 5;
											if ( $this->scheduleCheckPageBreak( $page_break_height, TRUE ) == TRUE ) {
												$this->scheduleHeader( $branch, $department );
												$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );
												$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format, TRUE );
											}

											if ( $s % 2 == 0 ) {
												$this->pdf->setFillColor(255,255,255);
											} else {
												$this->pdf->setFillColor(245,245,245);
											}

											if ( $this->scheduleUserWeek( $user_schedule, $calendar_week_array, $start_week_day, $column_widths, $format, $key ) == TRUE ) {
												$s++;
											}
										}
									}

									$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
									if ( $key % 25 == 0 AND $this->isSystemLoadValid() == FALSE ) {
										return FALSE;
									}
									$key++;
									$i++;
								}

								$this->scheduleFooterWeek( $column_widths );
								$this->scheduleFooter();
							}

							$n++;
						}
					} else {
						$this->scheduleNoData();
					}

					break;
				case 'pdf_schedule_group_combined':
				case 'pdf_schedule_group_combined_print':
					ksort($this->form_data['schedule_by_user']);
					//Start displaying dates/times here. Start with header.
					$column_widths = array(
										'line' => 5,
										'label' => 30,
										'day' => ($this->pdf->getPageWidth()-$this->config['other']['left_margin']-$this->config['other']['right_margin']-30)/7,
										);


					$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->form_data['schedule_by_user'])*(count($calendar_array)/7), NULL, TTi18n::getText('Generating Schedules...') );

					$this->pdf->AddPage( $this->config['other']['page_orientation'], 'Letter' );

					$this->scheduleHeader();
					$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );

					$key=0;
					$i=0;
					foreach( $calendar_array as $calendar_day ) {
						if ( $i % 7 == 0 ) {
							$calendar_week_array = array_slice( $calendar_array, $i, 7 );
							if ( $i != 0 ) {
								$this->scheduleFooterWeek();
							}

							$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format );

							foreach( $this->form_data['schedule_by_user'] as $user_id => $user_schedule ) {
								if ( $this->_pdf_checkMaximumPageLimit() == FALSE ) {
									Debug::Text('Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__,10);
									//Exceeded maximum pages, stop processing.
									$this->_pdf_displayMaximumPageLimitError();
									break 2;
								}

								$s=0;
								//Handle page break.
								if ( $this->scheduleCheckPageBreak( 5, TRUE ) == TRUE ) {
									$this->scheduleFooterWeek( $column_widths );
									$this->scheduleHeader();
									$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );
									$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format, TRUE );
								}

								$this->pdf->setFillColor(255,255,255);
								if ( $this->scheduleUserWeek( $user_schedule, $calendar_week_array, $start_week_day, $column_widths, $format, $key ) == TRUE ) {
									$s++;
								}

								$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
								if ( $key % 25 == 0 AND $this->isSystemLoadValid() == FALSE ) {
									return FALSE;
								}
								$key++;
							}

							$this->scheduleFooterWeek( $column_widths );
						}

						$i++;
					}

					$this->scheduleFooter();

					break;
				case 'pdf_schedule':
				case 'pdf_schedule_print':
					ksort($this->form_data['schedule_by_user']);
					//Start displaying dates/times here. Start with header.
					$column_widths = array(
										'line' => 5,
										'label' => 0,
										'day' => ($this->pdf->getPageWidth()-$this->config['other']['left_margin']-$this->config['other']['right_margin']-0)/7,
										);

					if ( isset($this->form_data['schedule_by_user']) ) {
						$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->form_data['schedule_by_user'])*(count($calendar_array)/7), NULL, TTi18n::getText('Generating Schedules...') );
						$key=0;

						foreach( $this->form_data['schedule_by_user'] as $user_full_name => $user_schedule ) {
							$this->pdf->AddPage( $this->config['other']['page_orientation'], 'Letter' );

							$split_name = explode( '_',  $user_full_name );
							$this->scheduleHeader( NULL, NULL, $split_name[1] .' '. $split_name[0] );
							unset($split_name);

							$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );

							$i=0;
							foreach( $calendar_array as $calendar_day ) {
								if ( $i % 7 == 0 ) {
									if ( $this->_pdf_checkMaximumPageLimit() == FALSE ) {
										Debug::Text('Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__,10);
										//Exceeded maximum pages, stop processing.
										$this->_pdf_displayMaximumPageLimitError();
										break 2;
									}

									$calendar_week_array = array_slice( $calendar_array, $i, 7 );
									if ( $i != 0 ) {
										$this->scheduleFooterWeek();
									}

									$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format );

									//Handle page break.
									$page_break_height = 5;
									if ( $this->scheduleCheckPageBreak( $page_break_height, TRUE ) == TRUE ) {
										$this->scheduleHeader();
										$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );
										$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format, TRUE );
									}

									$this->pdf->setFillColor(255,255,255);
									$this->scheduleUserWeek( $user_schedule, $calendar_week_array, $start_week_day, $column_widths, $format, $key );
								}

								$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
								if ( $key % 25 == 0 AND $this->isSystemLoadValid() == FALSE ) {
									return FALSE;
								}
								$key++;
								$i++;
							}

							$this->scheduleFooterWeek( $column_widths );
							$this->scheduleFooter();
						}
					} else {
						$this->scheduleNoData();
					}
					break;
			}

			$output = $this->pdf->Output('','S');

			return $output;

		} else {
			Debug::Text('No start/end date specified...', __FILE__, __LINE__, __METHOD__,10);
		}

		Debug::Text('No data to return...', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function _output( $format = NULL ) {
		//Individual Schedules
		//Group - Combined (all branch/department combined together)
		//Group - Separated (branch/department all separated.)
		if ( in_array( $format, $this->special_output_format ) ) {
			return $this->_outputPDFSchedule( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
