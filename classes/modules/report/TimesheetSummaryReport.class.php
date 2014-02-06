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
class TimesheetSummaryReport extends Report {

	function __construct() {
		$this->title = TTi18n::getText('TimeSheet Summary Report');
		$this->file_name = 'timesheet_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_timesheet_summary', $user_id, $company_id ) ) {
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
                                        '-2000-currency_id' => TTi18n::gettext('Currency'),
                                        '-2100-custom_filter' => TTi18n::gettext('Custom Filter'),

										'-4010-pay_period_time_sheet_verify_status_id' => TTi18n::gettext('TimeSheet Verification'),
										'-4020-include_no_data_rows' => TTi18n::gettext('Include Blank Records'),

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
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'TimesheetSummaryReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'TimesheetSummaryReport', 'custom_column' );
				}
                break;
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'TimesheetSummaryReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'TimesheetSummaryReport', 'custom_column' );
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
										'-1080-user_group' => TTi18n::gettext('Employee Group'),
										'-1090-default_branch' => TTi18n::gettext('Default Branch'),
										'-1100-default_department' => TTi18n::gettext('Default Department'),
										'-1110-currency' => TTi18n::gettext('Currency'),
										'-1111-current_currency' => TTi18n::gettext('Current Currency'),

										//'-1110-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
										//'-1120-pending_request' => TTi18n::gettext('Pending Requests'),

										'-1400-permission_control' => TTi18n::gettext('Permission Group'),
										'-1410-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1420-policy_group' => TTi18n::gettext('Policy Group'),

										'-1430-branch_name' => TTi18n::gettext('Branch'),
										'-1440-department_name' => TTi18n::gettext('Department'),

										//This can't be a secure SIN otherwise it doesn't make much sense putting it on here... But if its here then
										//supervisors can see it and it may be a security concern.
										//'-1480-sin' => TTi18n::gettext('SIN/SSN'),

										'-1490-note' => TTi18n::gettext('Note'),
										'-1495-tag' => TTi18n::gettext('Tags'),

										//Handled in date_columns above.
										//'-1450-pay_period' => TTi18n::gettext('Pay Period'),

										'-1510-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
										'-1515-verified_time_sheet_date' => TTi18n::gettext('Verified TimeSheet Date'),
							   );

				$retval = array_merge( $retval, (array)$this->getOptions('date_columns'), (array)$this->getOptions('custom_columns'), (array)$this->getOptions('report_static_custom_column')  );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used

										//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
										//'-2010-hourly_rate' => TTi18n::gettext('Hourly Rate'),

										'-2070-schedule_working' => TTi18n::gettext('Scheduled Time'),
										'-2072-schedule_working_diff' => TTi18n::gettext('Scheduled Time Diff.'),
										'-2080-schedule_absence' => TTi18n::gettext('Scheduled Absence'),

										'-2085-worked_days' => TTi18n::gettext('Worked Days'),
										'-2090-worked_time' => TTi18n::gettext('Worked Time'),
										//'-2100-actual_time' => TTi18n::gettext('Actual Time'),
										//'-2110-actual_time_diff' => TTi18n::gettext('Actual Time Difference'),
										//'-2130-paid_time' => TTi18n::gettext('Paid Time'),
										'-2290-regular_time' => TTi18n::gettext('Regular Time'),

										'-2500-gross_wage' => TTi18n::gettext('Gross Wage'),
										'-2501-gross_wage_with_burden' => TTi18n::gettext('Gross Wage w/Burden'),

										'-2530-regular_time_wage' => TTi18n::gettext('Regular Time - Wage'),
										'-2531-regular_time_wage_with_burden' => TTi18n::gettext('Regular Time - Wage w/Burden'),
										//'-2540-actual_time_wage' => TTi18n::gettext('Actual Time Wage'),
										//'-2550-actual_time_diff_wage' => TTi18n::gettext('Actual Time Difference Wage'),

										'-2690-regular_time_hourly_rate' => TTi18n::gettext('Regular Time - Hourly Rate'),
										'-2690-regular_time_hourly_rate_with_burden' => TTi18n::gettext('Regular Time - Hourly Rate w/Burden'),
							);

				$retval = array_merge( $retval, $this->getOptions('overtime_columns'), $this->getOptions('premium_columns'), $this->getOptions('absence_columns') );
				ksort($retval);
				break;
			case 'overtime_columns':
				//Get all Overtime policies.
				$retval = array();
				$otplf = TTnew( 'OverTimePolicyListFactory' );
				$otplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $otplf->getRecordCount() > 0 ) {
					foreach( $otplf as $otp_obj ) {
						$retval['-2291-over_time_policy-'.$otp_obj->getId()] = $otp_obj->getName();
						$retval['-2591-over_time_policy-'.$otp_obj->getId().'_wage'] = $otp_obj->getName() .' '. TTi18n::getText('- Wage');
						$retval['-2591-over_time_policy-'.$otp_obj->getId().'_wage_with_burden'] = $otp_obj->getName() .' '. TTi18n::getText('- Wage w/Burden');
						$retval['-2691-over_time_policy-'.$otp_obj->getId().'_hourly_rate'] = $otp_obj->getName() .' '. TTi18n::getText('- Hourly Rate');
						$retval['-2691-over_time_policy-'.$otp_obj->getId().'_hourly_rate_with_burden'] = $otp_obj->getName() .' '. TTi18n::getText('- Hourly Rate w/Burden');
					}
				}
				break;
			case 'premium_columns':
				$retval = array();
				//Get all Premium policies.
				$pplf = TTnew( 'PremiumPolicyListFactory' );
				$pplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach( $pplf as $pp_obj ) {
						$retval['-2291-premium_policy-'.$pp_obj->getId()] = $pp_obj->getName();
						$retval['-2591-premium_policy-'.$pp_obj->getId().'_wage'] = $pp_obj->getName() .' '. TTi18n::getText('- Wage');
						$retval['-2591-premium_policy-'.$pp_obj->getId().'_wage_with_burden'] = $pp_obj->getName() .' '. TTi18n::getText('- Wage w/Burden');
						$retval['-2691-premium_policy-'.$pp_obj->getId().'_hourly_rate'] = $pp_obj->getName() .' '. TTi18n::getText('- Hourly Rate');
						$retval['-2691-premium_policy-'.$pp_obj->getId().'_hourly_rate_with_burden'] = $pp_obj->getName() .' '. TTi18n::getText('- Hourly Rate w/Burden');
					}
				}
				break;
			case 'absence_columns':
				$retval = array();
				//Get all Absence Policies.
				$aplf = TTnew( 'AbsencePolicyListFactory' );
				$aplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach( $aplf as $ap_obj ) {
						$retval['-2291-absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
						if ( $ap_obj->getType() == 10 ) {
							$retval['-2591-absence_policy-'.$ap_obj->getId().'_wage'] = $ap_obj->getName() .' '. TTi18n::getText('- Wage');
							$retval['-2591-absence_policy-'.$ap_obj->getId().'_wage_with_burden'] = $ap_obj->getName() .' '. TTi18n::getText('- Wage w/Burden');
							$retval['-2691-absence_policy-'.$ap_obj->getId().'_hourly_rate'] = $ap_obj->getName() .' '. TTi18n::getText('- Hourly Rate');
							$retval['-2691-absence_policy-'.$ap_obj->getId().'_hourly_rate_with_burden'] = $ap_obj->getName() .' '. TTi18n::getText('- Hourly Rate w/Burden');
						}
					}
				}
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') );                
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = array_merge( $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_custom_column') );
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						if ( strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE ) {
							$retval[$column] = 'currency';
						} elseif ( strpos($column, '_time') OR strpos($column, 'schedule_') OR strpos($column, '_policy') ) {
							$retval[$column] = 'time_unit';
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
								if ( strpos($column, '_hourly_rate') !== FALSE ) {
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
										'-1010-by_employee+regular' => TTi18n::gettext('Regular Time by Employee'),
										'-1020-by_employee+overtime' => TTi18n::gettext('Overtime by Employee'),
										'-1030-by_employee+premium' => TTi18n::gettext('Premium Time by Employee'),
										'-1040-by_employee+absence' => TTi18n::gettext('Absence Time by Employee'),
										'-1050-by_employee+regular+overtime+premium+absence' => TTi18n::gettext('All Time by Employee'),

										'-1060-by_employee+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Employee'),
										'-1070-by_employee+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Employee'),
										'-1080-by_employee+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Employee'),
										'-1090-by_employee+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Employee'),
										'-1100-by_employee+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Employee'),

										'-1110-by_date_by_full_name+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Date/Employee'),
										'-1120-by_date_by_full_name+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Date/Employee'),
										'-1130-by_date_by_full_name+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Date/Employee'),
										'-1140-by_date_by_full_name+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Date/Employee'),
										'-1150-by_date_by_full_name+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Date/Employee'),

										'-1160-by_full_name_by_date+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Employee/Date'),
										'-1170-by_full_name_by_date+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Employee/Date'),
										'-1180-by_full_name_by_date+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Employee/Date'),
										'-1190-by_full_name_by_date+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Employee/Date'),
										'-1200-by_full_name_by_date+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Employee/Date'),

										'-1210-by_branch+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Branch'),
										'-1220-by_branch+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Branch'),
										'-1230-by_branch+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Branch'),
										'-1240-by_branch+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Branch'),
										'-1250-by_branch+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Branch'),

										'-1260-by_department+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Department'),
										'-1270-by_department+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Department'),
										'-1280-by_department+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Department'),
										'-1290-by_department+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Department'),
										'-1300-by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Department'),

										'-1310-by_branch_by_department+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Branch/Department'),
										'-1320-by_branch_by_department+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Branch/Department'),
										'-1330-by_branch_by_department+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Branch/Department'),
										'-1340-by_branch_by_department+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Branch/Department'),
										'-1350-by_branch_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Branch/Department'),

										'-1360-by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period'),
										'-1370-by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period'),
										'-1380-by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period'),
										'-1390-by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period'),
										'-1400-by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period'),

										'-1410-by_pay_period_by_employee+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Employee'),
										'-1420-by_pay_period_by_employee+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Employee'),
										'-1430-by_pay_period_by_employee+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Employee'),
										'-1440-by_pay_period_by_employee+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Employee'),
										'-1450-by_pay_period_by_employee+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Employee'),

										'-1460-by_pay_period_by_branch+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Branch'),
										'-1470-by_pay_period_by_branch+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Branch'),
										'-1480-by_pay_period_by_branch+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Branch'),
										'-1490-by_pay_period_by_branch+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Branch'),
										'-1500-by_pay_period_by_branch+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Branch'),

										'-1510-by_pay_period_by_department+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Department'),
										'-1520-by_pay_period_by_department+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Department'),
										'-1530-by_pay_period_by_department+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Department'),
										'-1540-by_pay_period_by_department+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Department'),
										'-1550-by_pay_period_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Department'),

										'-1560-by_pay_period_by_branch_by_department+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Branch/Department'),
										'-1570-by_pay_period_by_branch_by_department+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Branch/Department'),
										'-1580-by_pay_period_by_branch_by_department+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Branch/Department'),
										'-1590-by_pay_period_by_branch_by_department+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Branch/Department'),
										'-1600-by_pay_period_by_branch_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Branch/Department'),

										'-1610-by_employee_by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Employee/Pay Period'),
										'-1620-by_employee_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Employee/Pay Period'),
										'-1630-by_employee_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Employee/Pay Period'),
										'-1640-by_employee_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Employee/Pay Period'),
										'-1650-by_employee_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Employee/Pay Period'),

										'-1660-by_branch_by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Branch/Pay Period'),
										'-1670-by_branch_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Branch/Pay Period'),
										'-1680-by_branch_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Branch/Pay Period'),
										'-1690-by_branch_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Branch/Pay Period'),
										'-1700-by_branch_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Branch/Pay Period'),

										'-1810-by_department_by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Department/Pay Period'),
										'-1820-by_department_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Department/Pay Period'),
										'-1830-by_department_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Department/Pay Period'),
										'-1840-by_department_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Department/Pay Period'),
										'-1850-by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Department/Pay Period'),

										'-1860-by_branch_by_department_by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Branch/Department/Pay Period'),
										'-1870-by_branch_by_department_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Branch/Department/Pay Period'),
										'-1880-by_branch_by_department_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Branch/Department/Pay Period'),
										'-1890-by_branch_by_department_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Branch/Department/Pay Period'),
										'-1900-by_branch_by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Branch/Department/Pay Period'),

										'-3000-by_pay_period_by_employee+verified_time_sheet' => TTi18n::gettext('Timesheet Verification by Pay Period/Employee'),
										'-3010-by_verified_time_sheet_by_pay_period_by_employee+verified_time_sheet' => TTi18n::gettext('Timesheet Verification by Verification/Pay Period/Employee'),
							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'by_pay_period_by_employee+verified_time_sheet':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'pay_period';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'verified_time_sheet';
							$retval['columns'][] = 'verified_time_sheet_date';

							$retval['group'][] = 'pay_period';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sort'][] = array('pay_period' => 'asc');
							$retval['sort'][] = array('verified_time_sheet' => 'desc');
							$retval['sort'][] = array('verified_time_sheet_date' => 'asc');
							break;
						case 'by_verified_time_sheet_by_pay_period_by_employee+verified_time_sheet':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'verified_time_sheet';
							$retval['columns'][] = 'pay_period';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'verified_time_sheet_date';

							$retval['group'][] = 'verified_time_sheet';
							$retval['group'][] = 'pay_period';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sort'][] = array('verified_time_sheet' => 'desc');
							$retval['sort'][] = array('pay_period' => 'asc');
							$retval['sort'][] = array('verified_time_sheet_date' => 'asc');
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
										case 'regular':
											$retval['columns'][] = 'worked_time';
											$retval['columns'][] = 'regular_time';
											break;
										case 'overtime':
										case 'premium':
										case 'absence':
											$columns = Misc::trimSortPrefix( $this->getOptions( $template_keyword.'_columns') );
											if ( is_array($columns) ) {
												foreach( $columns as $column => $column_name ) {
													if ( strpos( $column, '_wage') === FALSE AND strpos( $column, '_hourly_rate') === FALSE ) {
														$retval['columns'][] = $column;
													}
												}
											}
											break;

										case 'regular_wage':
											$retval['columns'][] = 'regular_time_wage';
											break;
										case 'overtime_wage':
										case 'premium_wage':
										case 'absence_wage':
											$columns = Misc::trimSortPrefix( $this->getOptions( str_replace('_wage', '', $template_keyword).'_columns' ) );
											if ( is_array($columns) ) {
												foreach( $columns as $column => $column_name ) {
													if ( strpos( $column, '_wage') !== FALSE ) {
														$retval['columns'][] = $column;
													}
												}
											}
											break;
										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'branch_name';

											$retval['group'][] = 'branch_name';

											$retval['sort'][] = array('branch_name' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'department_name';

											$retval['sort'][] = array('department_name' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'department_name';

											$retval['sub_total'][] = 'branch_name';

											$retval['sort'][] = array('branch_name' => 'asc');
											$retval['sort'][] = array('department_name' => 'asc');
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
											$retval['columns'][] = 'branch_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('branch_name' => 'asc');
											break;
										case 'by_pay_period_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'department_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('department_name' => 'asc');
											break;
										case 'by_pay_period_by_branch_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'department_name';

											$retval['sub_total'][] = 'pay_period';
											$retval['sub_total'][] = 'branch_name';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('branch_name' => 'asc');
											$retval['sort'][] = array('department_name' => 'asc');
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
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch_name';

											$retval['sort'][] = array('branch_name' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_department_by_pay_period':
											$retval['columns'][] = 'department_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'department_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'department_name';

											$retval['sort'][] = array('department_name' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_branch_by_department_by_pay_period':
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'department_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'department_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch_name';
											$retval['sub_total'][] = 'department_name';

											$retval['sort'][] = array('branch_name' => 'asc');
											$retval['sort'][] = array('department_name' => 'asc');
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
										case 'by_full_name_by_date':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'date_stamp';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'date_stamp';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = array('full_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
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

	function getPolicyHourlyRates() {
		//Take into account wage groups!
		$policy_rates = array();

		//Get all Overtime policies.
		$otplf = TTnew( 'OverTimePolicyListFactory' );
		$otplf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $otplf->getRecordCount() > 0 ) {
			foreach( $otplf as $otp_obj ) {
				Debug::Text('Over Time Policy ID: '. $otp_obj->getId() .' Rate: '. $otp_obj->getRate() , __FILE__, __LINE__, __METHOD__,10);
				$policy_rates['over_time_policy-'.$otp_obj->getId()] = $otp_obj;
			}
		}

		//Get all Premium policies.
		$pplf = TTnew( 'PremiumPolicyListFactory' );
		$pplf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $pp_obj ) {
				$policy_rates['premium_policy-'.$pp_obj->getId()] = $pp_obj;
			}
		}

		//Get all Absence Policies.
		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$aplf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $aplf->getRecordCount() > 0 ) {
			foreach( $aplf as $ap_obj ) {
				$policy_rates['absence_policy-'.$ap_obj->getId()] = $ap_obj;
			}
		}

		return $policy_rates;
	}

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array('user_date_total' => array(), 'schedule' => array(), 'worked_days' => array(), 'user' => array(), 'default_branch' => array(), 'default_department' => array(), 'branch' => array(), 'department' => array(), 'verified_timesheet' => array() );
        
		$columns = $this->getColumnDataConfig();    
           
		$filter_data = $this->getFilterConfig();
		$policy_hourly_rates = $this->getPolicyHourlyRates();
        
        $currency_convert_to_base = $this->getCurrencyConvertToBase();
		$base_currency_obj = $this->getBaseCurrencyObject();
		$this->handleReportCurrency( $currency_convert_to_base, $base_currency_obj, $filter_data );
		$currency_options = $this->getOptions('currency');
        
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

		$pay_period_ids = array();

		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$udtlf->getTimesheetSummaryReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' Total Rows: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $udtlf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach ( $udtlf as $key => $udt_obj ) {
				$pay_period_ids[$udt_obj->getColumn('pay_period_id')] = TRUE;

				$user_id = $udt_obj->getColumn('user_id');

				$date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
				$branch_id = $udt_obj->getColumn('branch_id');
				$department_id = $udt_obj->getColumn('department_id');
				$status_id = $udt_obj->getColumn('status_id');
				$type_id = $udt_obj->getColumn('type_id');

				//Can we get rid of Worked and Paid time to simplify things? People have a hard time figuring out what these are anyways for reports.
				//Paid time doesn't belong to a branch/department, so if we try to group by branch/department there will
				//always be a blank line showing just the paid time. So if they don't want to display paid time, just exclude it completely.
				$column = $udt_obj->getTimeCategory();
				//Worked_time includes paid lunch/break time as well.
				if ( $column == 'paid_time' ) {
					$column = NULL;
				}

				//Debug::Text('Column: '. $column .' Total Time: '. $udt_obj->getColumn('total_time') .' Status: '. $status_id .' Type: '. $type_id .' Rate: '. $udt_obj->getColumn( 'hourly_rate' ), __FILE__, __LINE__, __METHOD__,10);
				if ( ( isset($filter_data['include_no_data_rows']) AND $filter_data['include_no_data_rows'] == 1 )
						OR ( $date_stamp != '' AND $column != '' AND $udt_obj->getColumn('total_time') != 0 )  ) {

					$hourly_rate = 0;
					$hourly_rate_with_burden = 0;
					if ( $wage_permission_children_ids === TRUE OR in_array( $user_id, $wage_permission_children_ids) ) {
						$hourly_rate = $udt_obj->getColumn( 'hourly_rate' );
						$hourly_rate_with_burden = bcmul( $hourly_rate, bcadd( bcdiv( $udt_obj->getColumn( 'labor_burden_percent' ), 100 ), 1) );
					}

					//This ises the hourly rate defined above.
					if ( isset($policy_hourly_rates[$column]) AND is_object($policy_hourly_rates[$column]) ) {
						$hourly_rate = $policy_hourly_rates[$column]->getHourlyRate( $hourly_rate );
						$hourly_rate_with_burden = $policy_hourly_rates[$column]->getHourlyRate( $hourly_rate_with_burden );
					}

					//Split time by user,date,branch,department as that is the lowest level we can split time.
					//We always need to split time as much as possible as it can always be combined together by grouping.
					//Unless we never add columns together that would ever span a single row is of course
					if ( !isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id] = array(
															'branch_id' => $udt_obj->getColumn('branch_id'),
															'department_id' => $udt_obj->getColumn('department_id'),
															//Use branch_id/department_id instead of names as we need to get additional branch/department information like manual_id/other_id fields below
															//'branch' => $udt_obj->getColumn('branch'),
															//'department' => $udt_obj->getColumn('department'),
															'pay_period_start_date' => strtotime( $udt_obj->getColumn('pay_period_start_date') ),
															'pay_period_end_date' => strtotime( $udt_obj->getColumn('pay_period_end_date') ),
															'pay_period_transaction_date' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
															'pay_period' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
															'pay_period_id' => $udt_obj->getColumn('pay_period_id'),
															);
					}

					//Add time/wage and calculate average hourly rate.
					$udt_total_time_wage = bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
					$udt_total_time_wage_with_burden = bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate_with_burden );

					if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column]) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column] += $udt_obj->getColumn('total_time');
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column] = $udt_obj->getColumn('total_time');
					}

					if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_wage']) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_wage'] += $udt_total_time_wage;
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_wage_with_burden'] += $udt_total_time_wage_with_burden;
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_wage'] = $udt_total_time_wage;
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_wage_with_burden'] = $udt_total_time_wage_with_burden;
					}

					if ( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column] != 0 ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_hourly_rate'] = bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_wage'], bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column], 3600) );
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_hourly_rate_with_burden'] = bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_wage_with_burden'], bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column], 3600) );
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_hourly_rate'] = $hourly_rate;
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column.'_hourly_rate_with_burden'] = $hourly_rate_with_burden;
					}

					//Gross wage calculation must go here otherwise it gets doubled up.
					//Worked Time is required for printable TimeSheets. Therefore this report is handled differently from TimeSheetSummary.
					if ( $column != 'worked_time' ) { //Exclude worked time from gross wage total.
						if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['gross_wage']) ) {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['gross_wage'] += $udt_total_time_wage;
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['gross_wage_with_burden'] += $udt_total_time_wage_with_burden;
						} else {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['gross_wage'] = $udt_total_time_wage;
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['gross_wage_with_burden'] = $udt_total_time_wage_with_burden;
						}
					}

					//Worked Days is tricky, since if they worked in multiple branches/departments in a single day, is that considered one worked day?
					//How do they find out how many days they worked in each branch/department though? It would add up to more days than they actually worked.
					//If we did some sort of partial day though, then due to rounding it could be thrown off, but either way it woulnd't be that helpful because
					//it would show they worked .33 of a day in one branch if they filtered by that branch.
					if ( $column == 'worked_time' AND $udt_obj->getColumn('total_time') > 0 AND !isset($this->tmp_data['worked_days'][$user_id.$date_stamp]) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['worked_days'] = 1;
						$this->tmp_data['worked_days'][$user_id.$date_stamp] = TRUE;
					}
					unset($hourly_rate);
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}
		//Debug::Arr($this->tmp_data['user_date_total'], 'User Date Total Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($columns['schedule_working']) OR isset($columns['schedule_working_diff']) OR isset($columns['schedule_absence']) ) {
			$slf = TTnew( 'ScheduleListFactory' );
			$slf->getDayReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			if ( $slf->getRecordCount() > 0 ) {
				foreach($slf as $s_obj) {
					$status = strtolower( Option::getByKey($s_obj->getColumn('status_id'), $s_obj->getOptions('status') ) );

					//Make sure we handle multiple schedules on the same day.
					if ( isset($this->tmp_data['schedule'][$s_obj->getColumn('user_id')][TTDate::strtotime( $s_obj->getColumn('date_stamp') )]['schedule_'.$status]) ) {
						$this->tmp_data['schedule'][$s_obj->getColumn('user_id')][TTDate::strtotime( $s_obj->getColumn('date_stamp') )]['schedule_'.$status] += $s_obj->getColumn('total_time');
					} else {
						$this->tmp_data['schedule'][$s_obj->getColumn('user_id')][TTDate::strtotime( $s_obj->getColumn('date_stamp') )]['schedule_'.$status] = $s_obj->getColumn('total_time');
					}
				}
			}
			//Debug::Arr($this->tmp_data['schedule'], 'Schedule Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
			unset($slf, $s_obj, $status);
		}

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( (array)$this->getColumnDataConfig(), array('other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, 'other_id5' => TRUE) ) );
			
            if ( $currency_convert_to_base == TRUE AND is_object( $base_currency_obj ) ) {
				$this->tmp_data['user'][$u_obj->getId()]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
				$this->tmp_data['user'][$u_obj->getId()]['currency_rate'] = $u_obj->getColumn('currency_rate');
			} else {
			    $this->tmp_data['user'][$u_obj->getId()]['current_currency'] =  $u_obj->getColumn('currency');
			}
            
            $this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
        
        //Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
		
		$blf = TTnew( 'BranchListFactory' );
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), array() ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text(' Branch Total Rows: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $blf as $key => $b_obj ) {
			$this->tmp_data['default_branch'][$b_obj->getId()] = Misc::addKeyPrefix( 'default_branch_', (array)$b_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'manual_id' => TRUE, 'other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, 'other_id5' => TRUE ) ) );
			$this->tmp_data['branch'][$b_obj->getId()] = Misc::addKeyPrefix( 'branch_', (array)$b_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'manual_id' => TRUE, 'other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, 'other_id5' => TRUE ) ) );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['default_branch'], 'Default Branch Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), array() ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text(' Department Total Rows: '. $dlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $dlf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $dlf as $key => $d_obj ) {
			$this->tmp_data['default_department'][$d_obj->getId()] = Misc::addKeyPrefix( 'default_department_', (array)$d_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'manual_id' => TRUE, 'other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, 'other_id5' => TRUE ) ) );
			$this->tmp_data['department'][$d_obj->getId()] = Misc::addKeyPrefix( 'department_', (array)$d_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'manual_id' => TRUE, 'other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, 'other_id5' => TRUE ) ) );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['default_department'], 'Default Department Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->tmp_data['department'], 'Department Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

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
		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__,10);          
        
		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['user_date_total']), NULL, TTi18n::getText('Pre-Processing Data...') );
        
		//Merge time data with user data
		$key=0;
        
		if ( isset($this->tmp_data['user_date_total']) ) {		  
			foreach( $this->tmp_data['user_date_total'] as $user_id => $level_1 ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					foreach( $level_1 as $date_stamp => $level_2 ) {
						foreach( $level_2 as $branch => $level_3 ) {
							foreach( $level_3 as $department => $row ) {

								$date_columns = TTDate::getReportDates( NULL, $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
								$processed_data  = array(
														//'branch_id' => $branch_id,
														//'department_id' => $department_id,
														//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
														);

								if ( isset( $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]) ) {
									$processed_data['verified_time_sheet'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['status'];
									$processed_data['verified_time_sheet_date'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['created_date'];
								} else {
									$processed_data['verified_time_sheet'] = TTi18n::getText('No');
									$processed_data['verified_time_sheet_date'] = FALSE;
								}

								if ( !isset($row['worked_time']) ) {
									$row['worked_time'] = 0;
								}
								if ( isset($this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_working']) ) {
									$processed_data['schedule_working'] = $this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_working'];
									$processed_data['schedule_working_diff'] = $row['worked_time'] - $this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_working'];
									unset($this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_working']);
								} else {
									$processed_data['schedule_working'] = 0;
									$processed_data['schedule_working_diff'] = $row['worked_time'];
								}
								if ( isset($this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_absent']) ) {
									$processed_data['schedule_absent'] = $this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_absent'];
									unset($this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_absent']);
								} else {
									$processed_data['schedule_absent'] = 0;
								}

								if ( isset($this->tmp_data['user'][$user_id]['default_branch_id']) AND isset($this->tmp_data['default_branch'][$this->tmp_data['user'][$user_id]['default_branch_id']]) ) {
									$tmp_default_branch = $this->tmp_data['default_branch'][$this->tmp_data['user'][$user_id]['default_branch_id']];
								} else {
									$tmp_default_branch = array();
								}
								if ( isset($this->tmp_data['user'][$user_id]['default_department_id']) AND isset($this->tmp_data['default_department'][$this->tmp_data['user'][$user_id]['default_department_id']]) ) {
									$tmp_default_department = $this->tmp_data['default_department'][$this->tmp_data['user'][$user_id]['default_department_id']];
								} else {
									$tmp_default_department = array();
								}

								if ( isset($this->tmp_data['branch'][$row['branch_id']]) ) {
									$tmp_branch = $this->tmp_data['branch'][$row['branch_id']];
								} else {
									$tmp_branch = array();
								}
								if ( isset($this->tmp_data['department'][$row['department_id']]) ) {
									$tmp_department = $this->tmp_data['department'][$row['department_id']];
								} else {
									$tmp_department = array();
								}
                                
                                $this->data[] = array_merge( $this->tmp_data['user'][$user_id], $tmp_default_branch, $tmp_default_department, $tmp_branch, $tmp_department, $row, $date_columns, $processed_data );                            
                                
                                
								$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
								$key++;
							}
						}
					}
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);
        return TRUE;
	}
}
?>