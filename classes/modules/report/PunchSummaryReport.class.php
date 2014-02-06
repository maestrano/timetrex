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
class PunchSummaryReport extends Report {

	function __construct() {
		$this->title = TTi18n::getText('Punch Summary Report');
		$this->file_name = 'punch_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_punch_summary', $user_id, $company_id ) ) { //Piggyback on timesheet summary permissions.
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
										'-2080-punch_branch_id' => TTi18n::gettext('Punch Branch'),
										'-2090-punch_department_id' => TTi18n::gettext('Punch Department'),
                                        '-2100-custom_filter' => TTi18n::gettext('Custom Filter'),

										'-5000-columns' => TTi18n::gettext('Display Columns'),
										'-5010-group' => TTi18n::gettext('Group By'),
										'-5020-sub_total' => TTi18n::gettext('SubTotal By'),
										'-5030-sort' => TTi18n::gettext('Sort By'),
							   );

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$corporate_edition_setup_fields = array(
										'-2510-job_status_id' => TTi18n::gettext('Job Status'),
										'-2520-job_group_id' => TTi18n::gettext('Job Group'),
										'-2530-include_job_id' => TTi18n::gettext('Include Job'),
										'-2540-exclude_job_id' => TTi18n::gettext('Exclude Job'),

										'-2610-job_item_group_id' => TTi18n::gettext('Task Group'),
										'-2620-include_job_item_id' => TTi18n::gettext('Include Task'),
										'-2630-exclude_job_item_id' => TTi18n::gettext('Exclude Task'),
									);
					$retval = array_merge( $retval, $corporate_edition_setup_fields );
				}

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
				$other_field_names = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), array(15,20,30), array( 15 => '', 20 => 'job_', 30 => 'job_item_') );
				if ( is_array($other_field_names) ) {
					$retval = Misc::addSortPrefix( $other_field_names, 9000 );
				}
				break;
            case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'PunchSummaryReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'PunchSummaryReport', 'custom_column' );
				}
                break;
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'PunchSummaryReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'PunchSummaryReport', 'custom_column' );
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

										//'-1490-note' => TTi18n::gettext('Employee Note'),
										'-1495-tag' => TTi18n::gettext('Tags'),

										//Handled in date_columns above.
										//'-1230-pay_period' => TTi18n::gettext('Pay Period'),

										'-1600-in_time_stamp' => TTi18n::gettext('In Punch'),
										'-1601-in_type' => TTi18n::gettext('In Type'),
										'-1610-out_time_stamp' => TTi18n::gettext('Out Punch'),
										'-1611-out_type' => TTi18n::gettext('Out Type'),
										'-1620-in_actual_time_stamp' => TTi18n::gettext('In (Actual)'),
										'-1630-out_actual_time_stamp' => TTi18n::gettext('Out (Actual)'),
										'-1660-branch' => TTi18n::gettext('Branch'),
										'-1670-department' => TTi18n::gettext('Department'),
										'-1671-in_station_type' => TTi18n::gettext('In Station Type'),
										'-1672-in_station_station_id' => TTi18n::gettext('In Station ID'),
										'-1673-in_station_source' => TTi18n::gettext('In Station Source'),
										'-1674-in_station_description' => TTi18n::gettext('In Station Description'),
										'-1675-out_station_type' => TTi18n::gettext('Out Station Type'),
										'-1676-out_station_station_id' => TTi18n::gettext('Out Station ID'),
										'-1677-out_station_source' => TTi18n::gettext('Out Station Source'),
										'-1678-out_station_description' => TTi18n::gettext('Out Station Description'),
										'-1720-note' => TTi18n::gettext('Note'),
										'-1900-in_created_date' => TTi18n::gettext('In Created Date'),
										'-1901-in_created_by' => TTi18n::gettext('In Created By'),
										'-1905-in_updated_date' => TTi18n::gettext('In Updated Date'),
										'-1906-in_updated_by' => TTi18n::gettext('In Updated By'),
										'-1910-out_created_date' => TTi18n::gettext('Out Created Date'),
										'-1911-out_created_by' => TTi18n::gettext('Out Created By'),
										'-1915-out_updated_date' => TTi18n::gettext('Out Updated Date'),
										'-1916-out_updated_by' => TTi18n::gettext('Out Updated By'),
										'-1920-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
										'-1925-verified_time_sheet_date' => TTi18n::gettext('Verified TimeSheet Date'),
										'-1930-verified_time_sheet_tainted' => TTi18n::gettext('TimeSheet Verification Tainted'),

										'-1950-tainted' => TTi18n::gettext('Tainted'),
										'-1951-tainted_status' => TTi18n::gettext('Tainted Status'),
							   );

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$corporate_edition_static_columns = array(
											//Static Columns - Aggregate functions can't be used on these.
											'-1810-job' => TTi18n::gettext('Job'),
											'-1820-job_manual_id' => TTi18n::gettext('Job Code'),
											'-1830-job_description' => TTi18n::gettext('Job Description'),
											'-1840-job_status' => TTi18n::gettext('Job Status'),
											'-1850-job_branch' => TTi18n::gettext('Job Branch'),
											'-1860-job_department' => TTi18n::gettext('Job Department'),
											'-1870-job_group' => TTi18n::gettext('Job Group'),
											'-1910-job_item' => TTi18n::gettext('Task'),
											'-1920-job_item_manual_id' => TTi18n::gettext('Task Code'),
											'-1930-job_item_description' => TTi18n::gettext('Task Description'),
											'-1940-job_item_group' => TTi18n::gettext('Task Group'),
								   );
					$retval = array_merge( $retval, $corporate_edition_static_columns );
				}

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

										'-2120-actual_total_time' => TTi18n::gettext('Actual Time'),
										'-2120-actual_total_time_wage' => TTi18n::gettext('Actual Time Wage'),
										'-2125-actual_total_time_diff' => TTi18n::gettext('Actual Time Difference'),
										'-2127-actual_total_time_diff_wage' => TTi18n::gettext('Actual Time Difference Wage'),

										'-3000-total_punch' => TTi18n::gettext('Total Punches'), //Group counter...
										'-3001-total_tainted_punch' => TTi18n::gettext('Total Tainted Punches'), //Group counter...
							);
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = array_merge($this->getOptions('dynamic_columns'), (array)$this->getOptions('report_custom_column'));
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						if ( strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE OR strpos($column, 'hourly_rate') !== FALSE ) {
							$retval[$column] = 'currency';
						} elseif ( strpos($column, '_time') OR strpos($column, '_policy') ) {
							$retval[$column] = 'time_unit';
						}
					}
				}

				$retval['in_time_stamp'] = $retval['out_time_stamp'] = $retval['in_actual_time_stamp'] = $retval['out_actual_time_stamp'] = $retval['in_created_date'] = $retval['in_updated_date'] = $retval['out_created_date'] = $retval['out_updated_date'] = $retval['verified_time_sheet_date'] = 'time_stamp';
				$retval['verified_time_sheet_tainted'] = $retval['tainted'] = 'boolean';
				$retval['verified_time_sheet_tainted'] = 'boolean';
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
				$retval['verified_time_sheet'] = 'first';
				$retval['verified_time_sheet_date'] = 'first';
				break;
			case 'templates':
				$retval = array(
										'-1010-by_employee+punch_summary+total_time' => TTi18n::gettext('Punch Summary By Employee'),
										'-1020-by_branch+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Branch'),
										'-1030-by_department+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Department'),
										'-1040-by_branch_by_department+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Branch/Department'),
										'-1050-by_pay_period+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Pay Period'),
										'-1060-by_date_stamp+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Date'),
										'-1070-by_station+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Station'),

										'-1080-by_employee+punch_summary+total_time+note' => TTi18n::gettext('Punch Summary+Notes by Employee'),
										'-1090-by_employee+punch_summary+total_time+actual_time' => TTi18n::gettext('Punch Summary+Actual Time by Employee'),
										'-1100-by_employee+punch_summary+station_summary+total_time' => TTi18n::gettext('Punch/Station Detail By Employee'),

										'-1110-by_employee+actual_time' => TTi18n::gettext('Actual Time by Employee'),

										'-1120-by_employee+tainted' => TTi18n::gettext('Tainted Punches By Employee'),


										//'-1010-by_job+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Job'),
										//'-1010-by_job_item+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Task'),
										'-1120-by_employee+verified_time_sheet' => TTi18n::gettext('TimeSheet Verification Tainted'),
							   );

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$professional_edition_templates = array(
										'-2010-by_job+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Job'),
										'-2020-by_job_item+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Task'),
										'-2030-by_job_by_job_item+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Job/Task'),
										'-2040-by_job_branch+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Job Branch'),
										'-2050-by_job_branch_by_job_department+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Job Branch/Department'),
										'-2060-by_job_group+punch_summary+total_time' => TTi18n::gettext('Punch Summary by Job Group'),
									);
					$retval = array_merge( $retval, $professional_edition_templates );
				}

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'by_employee+actual_time':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'total_time';
							$retval['columns'][] = 'actual_total_time';
							$retval['columns'][] = 'actual_total_time_diff';
							$retval['columns'][] = 'actual_total_time_diff_wage';

							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sort'][] = array('actual_total_time_diff' => 'desc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');

							break;
						case 'by_employee+verified_time_sheet':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'in_type';
							$retval['columns'][] = 'in_time_stamp';
							$retval['columns'][] = 'out_type';
							$retval['columns'][] = 'out_time_stamp';
							$retval['columns'][] = 'verified_time_sheet';
							$retval['columns'][] = 'verified_time_sheet_date';
							$retval['columns'][] = 'verified_time_sheet_tainted';

							$retval['sort'][] = array('verified_time_sheet_tainted' => 'desc');
							$retval['sort'][] = array('verified_time_sheet' => 'asc');
							$retval['sort'][] = array('verified_time_sheet_date' => 'desc');
							break;
						case 'by_employee+tainted':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'tainted';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'total_tainted_punch';
							$retval['columns'][] = 'total_punch';

							$retval['group'][] = 'tainted';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sub_total'][] = 'tainted';

							$retval['sort'][] = array('tainted' => 'desc');
							$retval['sort'][] = array('total_tainted_punch' => 'desc');
							$retval['sort'][] = array('total_punch' => 'desc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'desc');
							break;
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
										case 'actual_time':
											$retval['columns'][] = 'actual_total_time';
											$retval['columns'][] = 'actual_total_time_diff';
											break;
										case 'note':
											$retval['columns'][] = 'note';
											break;
										case 'punch_summary':
											$retval['columns'][] = 'in_type';
											$retval['columns'][] = 'in_time_stamp';
											$retval['columns'][] = 'out_type';
											$retval['columns'][] = 'out_time_stamp';
											break;
										case 'station_summary':
											$retval['columns'][] = 'in_station_type';
											$retval['columns'][] = 'out_station_type';
											break;
										//Filter

										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'last_name';
											$retval['sub_total'][] = 'first_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'branch';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'department';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'department';

											$retval['sort'][] = array('department' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'branch';
											$retval['sub_total'][] = 'department';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_pay_period':
											$retval['columns'][] = 'pay_period';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_station':
											$retval['columns'][] = 'in_station_type';
											$retval['columns'][] = 'in_station_description';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'in_station_type';
											$retval['sub_total'][] = 'in_station_description';

											$retval['sort'][] = array('in_station_type' => 'asc');
											$retval['sort'][] = array('in_station_description' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_date_stamp':
											$retval['columns'][] = 'date_stamp';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;

										//Professional Edition templates.
										case 'by_job':
											$retval['columns'][] = 'job';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job';

											$retval['sort'][] = array('job' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_item':
											$retval['columns'][] = 'job_item';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_item';

											$retval['sort'][] = array('job_item' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_by_job_item':
											$retval['columns'][] = 'job';
											$retval['columns'][] = 'job_item';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job';
											$retval['sub_total'][] = 'job_item';

											$retval['sort'][] = array('job' => 'asc');
											$retval['sort'][] = array('job_item' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_branch':
											$retval['columns'][] = 'job_branch';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_branch';

											$retval['sort'][] = array('job_branch' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_department':
											$retval['columns'][] = 'job_department';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_department';

											$retval['sort'][] = array('job_department' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_branch_by_job_department':
											$retval['columns'][] = 'job_branch';
											$retval['columns'][] = 'job_department';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_branch';
											$retval['sub_total'][] = 'job_department';

											$retval['sort'][] = array('job_branch' => 'asc');
											$retval['sort'][] = array('job_department' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_group':
											$retval['columns'][] = 'job_group';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_group';

											$retval['sort'][] = array('job_group' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
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
		$this->tmp_data = array('punch' => array(), 'user' => array(), 'verified_timesheet' => array() );

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		if ( $this->getPermissionObject()->Check('punch','view') == FALSE OR $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			$hlf = TTnew( 'HierarchyListFactory' );
			$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
			//Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = array();
			$wage_permission_children_ids = array();
		}
		if ( $this->getPermissionObject()->Check('punch','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('punch','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('punch','view_own') ) {
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

		$slf = TTnew( 'StationListFactory' );
		$station_type_options = $slf->getOptions('type');

		if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' );
			$job_status_options = $jlf->getOptions('status');
		} else {
			$job_status_options = array();
		}

		$pay_period_ids = array();

		$plf = TTnew( 'PunchListFactory' );
		$punch_type_options = $plf->getOptions('type');

		$plf->getPunchSummaryReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' Total Rows: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $plf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		if ( $plf->getRecordCount() > 0 ) {
			foreach ( $plf as $key => $p_obj ) {
				$pay_period_ids[$p_obj->getColumn('pay_period_id')] = TRUE;

				if ( !isset($this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]) ) {
					$hourly_rate = 0;
					if ( $wage_permission_children_ids === TRUE OR in_array( $p_obj->getColumn('user_id'), $wage_permission_children_ids) ) {
						$hourly_rate = $p_obj->getColumn( 'hourly_rate' );
					}

					$actual_time_diff = (int)$p_obj->getColumn('actual_total_time') - (int)$p_obj->getColumn('total_time');

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')] = array(
						'user_id' => $p_obj->getColumn('user_id'),
						'user_group' => $p_obj->getColumn('group'),
						'branch' => $p_obj->getColumn('branch'),
						'department' => $p_obj->getColumn('department'),
						'job' => $p_obj->getColumn('job'),
						'job_status_id' => $p_obj->getColumn('job_status_id'),
						'job_status' => Option::getByKey($p_obj->getColumn('job_status_id'), $job_status_options, NULL ),
						'job_manual_id' => $p_obj->getColumn('job_manual_id'),
						'job_description' => $p_obj->getColumn('job_description'),
						'job_branch' => $p_obj->getColumn('job_branch'),
						'job_department' => $p_obj->getColumn('job_department'),
						'job_group' => $p_obj->getColumn('job_group'),
						'job_item' => $p_obj->getColumn('job_item'),
						'job_other_id1' => $p_obj->getColumn('job_other_id1'),
						'job_other_id2' => $p_obj->getColumn('job_other_id2'),
						'job_other_id3' => $p_obj->getColumn('job_other_id3'),
						'job_other_id4' => $p_obj->getColumn('job_other_id4'),
						'job_other_id5' => $p_obj->getColumn('job_other_id5'),
						'quantity' => $p_obj->getColumn('quantity'),
						'bad_quantity' => $p_obj->getColumn('bad_quantity'),
						'note' => $p_obj->getColumn('note'),
						'total_time' => $p_obj->getColumn('total_time'),
						'total_time_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('total_time') ), $hourly_rate ), FALSE ),
						'total_time_wage_burden' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('total_time') ), bcmul( $hourly_rate, bcdiv( $p_obj->getColumn('labor_burden_percent'), 100 ) ) ), FALSE ),
						'total_time_wage_with_burden' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('total_time') ), bcmul( $hourly_rate, bcadd( bcdiv( $p_obj->getColumn('labor_burden_percent'), 100 ), 1) ) ), FALSE ),
						'actual_total_time' => $p_obj->getColumn('actual_total_time'),
						'actual_total_time_diff' => $actual_time_diff,
						'actual_total_time_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('actual_total_time') ), $hourly_rate ), FALSE ),
						'actual_total_time_diff_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $actual_time_diff ), $hourly_rate) ),
						'other_id1' => $p_obj->getColumn('other_id1'),
						'other_id2' => $p_obj->getColumn('other_id2'),
						'other_id3' => $p_obj->getColumn('other_id3'),
						'other_id4' => $p_obj->getColumn('other_id4'),
						'other_id5' => $p_obj->getColumn('other_id5'),
						'date_stamp' => TTDate::strtotime( $p_obj->getColumn('date_stamp') ),
						'in_time_stamp' => NULL,
						'in_actual_time_stamp' => NULL,
						'in_type' => NULL,
						'out_time_stamp' => NULL,
						'out_actual_time_stamp' => NULL,
						'out_type' => NULL,
						'user_wage_id' => $p_obj->getColumn('user_wage_id'),
						'hourly_rate' => Misc::MoneyFormat( $hourly_rate, FALSE ),
						'tainted' => 0,
						'tainted_status' => NULL,
						'in_station_type' => NULL,
						'in_station_station_id' => NULL,
						'in_station_source' => NULL,
						'in_station_description' => NULL,
						'out_station_type' => NULL,
						'out_station_station_id' => NULL,
						'out_station_source' => NULL,
						'out_station_description' => NULL,
						'pay_period_start_date' => strtotime( $p_obj->getColumn('pay_period_start_date') ),
						'pay_period_end_date' => strtotime( $p_obj->getColumn('pay_period_end_date') ),
						'pay_period_transaction_date' => strtotime( $p_obj->getColumn('pay_period_transaction_date') ),
						'pay_period' => strtotime( $p_obj->getColumn('pay_period_transaction_date') ),
						'pay_period_id' => $p_obj->getColumn('pay_period_id'),

						'total_punch' => 0,
						'total_tainted_punch' => 0,
						);
				}

				if ( $p_obj->getColumn('status_id') == 10 ) {
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('punch_time_stamp') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_type'] = Option::getByKey($p_obj->getColumn('type_id'), $punch_type_options, NULL );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_actual_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('punch_actual_time_stamp') );

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_type'] = Option::getByKey($p_obj->getColumn('station_type_id'), $station_type_options, '--' );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_station_id'] = $p_obj->getColumn('station_station_id');
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_source']  = $p_obj->getColumn('station_source');
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_description'] = $p_obj->getColumn('station_description');

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_created_date'] = TTDate::strtotime( $p_obj->getColumn('punch_created_date') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_created_by'] = Misc::getFullName( $p_obj->getColumn('punch_created_by_first_name'), $p_obj->getColumn('punch_created_by_middle_name'), $p_obj->getColumn('punch_created_by_last_name'), FALSE, FALSE );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_updated_date'] = TTDate::strtotime( $p_obj->getColumn('punch_updated_date') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_updated_by'] = Misc::getFullName( $p_obj->getColumn('punch_updated_by_first_name'), $p_obj->getColumn('punch_updated_by_middle_name'), $p_obj->getColumn('punch_updated_by_last_name'), FALSE, FALSE );

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['total_punch']++;
				} else {
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('punch_time_stamp') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_type'] = Option::getByKey($p_obj->getColumn('type_id'), $punch_type_options, NULL );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_actual_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('punch_actual_time_stamp') );

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_type'] = Option::getByKey($p_obj->getColumn('station_type_id'), $station_type_options, '--' );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_station_id'] = $p_obj->getColumn('station_station_id');
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_source']  = $p_obj->getColumn('station_source');
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_description'] = $p_obj->getColumn('station_description');

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_created_date'] = TTDate::strtotime( $p_obj->getColumn('punch_created_date') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_created_by'] = Misc::getFullName( $p_obj->getColumn('punch_created_by_first_name'), $p_obj->getColumn('punch_created_by_middle_name'), $p_obj->getColumn('punch_created_by_last_name'), FALSE, FALSE );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_updated_date'] = TTDate::strtotime( $p_obj->getColumn('punch_updated_date') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_updated_by'] = Misc::getFullName( $p_obj->getColumn('punch_updated_by_first_name'), $p_obj->getColumn('punch_updated_by_middle_name'), $p_obj->getColumn('punch_updated_by_last_name'), FALSE, FALSE );

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['total_punch']++;
				}

				if ( $p_obj->getTainted() == TRUE ) {
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['tainted'] = 1;
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['total_tainted_punch']++;

					if ( $this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['tainted_status'] !== NULL ) {
						$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['tainted_status'] = TTi18n::getText('Both (In&Out)');
					} else {
						if ( $p_obj->getColumn('status_id') == 10 ) {
							$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['tainted_status'] = TTi18n::getText('In');
						} else {
							$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['tainted_status'] = TTi18n::getText('Out');
						}
					}
				}

				unset($hourly_rate, $uw_obj, $actual_time_diff);

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}
		//Debug::Arr($this->tmp_data['punch'], 'Punch Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

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

		//Get verified timesheets for all pay periods considered in report.
		$pay_period_ids = array_keys( $pay_period_ids );
		if ( isset($pay_period_ids) AND count($pay_period_ids) > 0 ) {
			$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
			$pptsvlf->getByPayPeriodIdAndCompanyId( $pay_period_ids, $this->getUserObject()->getCompany() );
			if ( $pptsvlf->getRecordCount() > 0 ) {
				foreach( $pptsvlf as $pptsv_obj ) {
					$this->tmp_data['verified_timesheet'][$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = array(
																									'status' => $pptsv_obj->getVerificationStatusShortDisplay(),
																									'created_date' => $pptsv_obj->getCreatedDate(),
																									);
				}
			}
		}

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['punch']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['punch']) ) {
			foreach( $this->tmp_data['punch'] as $user_id => $level_1 ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					foreach( $level_1 as $punch_control_id => $row ) {
						$date_columns = TTDate::getReportDates( NULL, $row['date_stamp'], FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
						//$date_columns1 = TTDate::getReportDates( 'in_time_stamp', $row['in_time_stamp'], FALSE, $this->getUserObject() );
						//$date_columns2 = TTDate::getReportDates( 'out_time_stamp', $row['out_time_stamp'], FALSE, $this->getUserObject() );
						$processed_data  = array(
												//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
												);

						if ( isset( $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]) ) {
							$processed_data['verified_time_sheet'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['status'];
							$processed_data['verified_time_sheet_date'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['created_date'];
						} else {
							$processed_data['verified_time_sheet'] = TTi18n::getText('No');
							$processed_data['verified_time_sheet_date'] = FALSE;
						}

						if ( isset( $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']] )
								AND isset($row['in_updated_date']) AND isset($row['out_updated_date'])
								AND ( $processed_data['verified_time_sheet_date'] < $row['in_updated_date'] OR $processed_data['verified_time_sheet_date'] < $row['out_updated_date'] ) ) {
							$processed_data['verified_time_sheet_tainted'] = TRUE;
						} else {
							$processed_data['verified_time_sheet_tainted'] = FALSE;
						}

						$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

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
/*
	function _output( $format = NULL ) {
		return $this->_pdf();
	}
*/
}
?>
