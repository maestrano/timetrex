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
class TimesheetDetailReport extends Report {

	function __construct() {
		$this->title = TTi18n::getText('TimeSheet Detail Report');
		$this->file_name = 'timesheet_detail_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_timesheet_summary', $user_id, $company_id ) ) { //Piggyback on timesheet summary permissions.
			return TRUE;
		} else {
			//Debug::Text('Regular employee viewing their own timesheet...', __FILE__, __LINE__, __METHOD__,10);
			//Regular employee printing timesheet for themselves. Force specific config options.
			//Get current pay period from config, then overwrite it with
			$filter_config = $this->getFilterConfig();
			if ( isset($filter_config['time_period']['pay_period_id']) ) {
				$pay_period_id = $filter_config['time_period']['pay_period_id'];
			} else {
				$pay_period_id = 0;
			}
			$this->setFilterConfig( array( 'include_user_id' => array($user_id), 'time_period' => array( 'time_period' => 'custom_pay_period', 'pay_period_id' => $pay_period_id ) ) );

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
										'-1100-pdf_timesheet' => TTi18n::gettext('TimeSheet Summary'),
										'-1110-pdf_timesheet_detail' => TTi18n::gettext('TimeSheet Detail'),
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
										'-2035-user_tag' => TTi18n::gettext('Employee Tags'),
										'-2040-include_user_id' => TTi18n::gettext('Employee Include'),
										'-2050-exclude_user_id' => TTi18n::gettext('Employee Exclude'),
										'-2060-default_branch_id' => TTi18n::gettext('Default Branch'),
										'-2070-default_department_id' => TTi18n::gettext('Default Department'),
										'-2080-punch_branch_id' => TTi18n::gettext('Punch Branch'),
										'-2090-punch_department_id' => TTi18n::gettext('Punch Department'),
                                        '-2100-custom_filter' => TTi18n::gettext('Custom Filter'),
                                        '-2200-currency_id' => TTi18n::gettext('Currency'),

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
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'TimesheetDetailReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'TimesheetDetailReport', 'custom_column' );
				}
                break;
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'TimesheetDetailReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'TimesheetDetailReport', 'custom_column' );
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
										'-1111-current_currency' => TTi18n::gettext('Current Currency'),

										//'-1110-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
										//'-1120-pending_request' => TTi18n::gettext('Pending Requests'),

										'-1400-permission_control' => TTi18n::gettext('Permission Group'),
										'-1410-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1420-policy_group' => TTi18n::gettext('Policy Group'),

										//Handled in date_columns above.
										//'-1430-pay_period' => TTi18n::gettext('Pay Period'),

										'-1430-branch' => TTi18n::gettext('Branch'),
										'-1440-department' => TTi18n::gettext('Department'),

										'-1480-sin' => TTi18n::gettext('SIN/SSN'),

										'-1490-note' => TTi18n::gettext('Note'),
										'-1495-tag' => TTi18n::gettext('Tags'),

										'-1510-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
										'-1515-verified_time_sheet_date' => TTi18n::gettext('Verified TimeSheet Date'),
							   );

				$retval = array_merge( $retval, (array)$this->getOptions('date_columns'), (array)$this->getOptions('custom_columns'), (array)$this->getOptions('report_static_custom_column') );
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
										'-2110-min_punch_time_stamp' => TTi18n::gettext('First In Punch'),
										'-2115-max_punch_time_stamp' => TTi18n::gettext('Last Out Punch'),

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
				$retval['min_punch_time_stamp'] = 'time';
				$retval['max_punch_time_stamp'] = 'time';
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
								} elseif ( strpos($column, 'min_punch_time_stamp') !== FALSE ) {
									$retval[$column] = 'min';
								} elseif ( strpos($column, 'max_punch_time_stamp') !== FALSE ) {
									$retval[$column] = 'max';
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
										'-1420-by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Employee'),
										'-1430-by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Employee'),
										'-1440-by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Employee'),
										'-1450-by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Employee'),

										'-1451-by_pay_period_by_date_stamp_by_employee+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Date/Employee'),
										'-1452-by_pay_period_by_date_stamp_by_employee+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Date/Employee'),
										'-1453-by_pay_period_by_date_stamp_by_employee+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Date/Employee'),
										'-1454-by_pay_period_by_date_stamp_by_employee+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Date/Employee'),
										'-1455-by_pay_period_by_date_stamp_by_employee+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Date/Employee'),

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
										'-1820-by_department_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Department/Pay Period'),
										'-1830-by_department_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Department/Pay Period'),
										'-1840-by_department_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Department/Pay Period'),
										'-1850-by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Department/Pay Period'),

										'-1860-by_branch_by_department_by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Branch/Department/Pay Period'),
										'-1870-by_branch_by_department_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Branch/Department/Pay Period'),
										'-1880-by_branch_by_department_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Branch/Department/Pay Period'),
										'-1890-by_branch_by_department_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Branch/Department/Pay Period'),
										'-1900-by_branch_by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Branch/Department/Pay Period'),

										'-1910-by_full_name_by_dow+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Employee/Day of Week'),
										'-1920-by_full_name_by_dow+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Employee/Day of Week'),
										'-1930-by_full_name_by_dow+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Employee/Day of Week'),
										'-1940-by_full_name_by_dow+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Employee/Day of Week'),
										'-1950-by_full_name_by_dow+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Employee/Day of Week'),
							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'specific_template_name':
							//$retval['column'] = array();
							//$retval['filter'] = array();
							//$retval['group'] = array();
							//$retval['sub_total'] = array();
							//$retval['sort'] = array();
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
										case 'by_pay_period_by_date_stamp_by_employee':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'date_stamp';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'date_stamp';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'pay_period';
											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
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
										case 'by_full_name_by_date':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'date_stamp';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'date_stamp';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = array('full_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											break;
										case 'by_full_name_by_dow':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'date_dow';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'date_dow';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = array('full_name' => 'asc');
											$retval['sort'][] = array('date_dow' => 'asc');
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
				if ( $ap_obj->getType() == 10 ) {
					$policy_rates['absence_policy-'.$ap_obj->getId()] = $ap_obj;
				} else {
					$policy_rates['absence_policy-'.$ap_obj->getId()] = FALSE;
				}
			}
		}

		return $policy_rates;
	}

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array('user_date_total' => array(), 'schedule' => array(), 'worked_days' => array(), 'user' => array(), 'verified_timesheet' => array(), 'punch_rows' => array(), 'pay_period_schedule' => array(), 'pay_period' => array() );

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
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
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
		$udtlf->getTimesheetDetailReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' Total Rows: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $udtlf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach ( $udtlf as $key => $udt_obj ) {
				$pay_period_ids[$udt_obj->getColumn('pay_period_id')] = TRUE;

				$user_id = $udt_obj->getColumn('user_id');

				$date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
				$branch = $udt_obj->getColumn('branch');
				$department = $udt_obj->getColumn('department');
				$status_id = $udt_obj->getColumn('status_id');
				$type_id = $udt_obj->getColumn('type_id');

				//Can we get rid of Worked and Paid time to simplify things? People have a hard time figuring out what these are anyways for reports.
				//Paid time doesn't belong to a branch/department, so if we try to group by branch/department there will
				//always be a blank line showing just the paid time. So if they don't want to display paid time, just exclude it completely.
				$column = $udt_obj->getTimeCategory();
				//Include worked time for the PDF timesheet, however exclude it from Gross Wage totals.
				//Worked_time includes paid lunch/break time as well.
				if ( $column == 'paid_time' ) {
					$column = NULL;
				}

				//Debug::Text('Column: '. $column .' Total Time: '. $udt_obj->getColumn('total_time') .' Status: '. $status_id .' Type: '. $type_id .' Rate: '. $udt_obj->getColumn( 'hourly_rate' ), __FILE__, __LINE__, __METHOD__,10);
				if ( ( isset($filter_data['include_no_data_users']) AND $filter_data['include_no_data_users'] == 1 )
						OR ( !isset($filter_data['include_no_data_users']) AND $date_stamp != '' AND $column != '' AND $udt_obj->getColumn('total_time') != 0 )  ) {

					$hourly_rate = 0;
					$hourly_rate_with_burden = 0;
					if ( $wage_permission_children_ids === TRUE OR in_array( $user_id, $wage_permission_children_ids) ) {
						$hourly_rate = $udt_obj->getColumn( 'hourly_rate' );
						$hourly_rate_with_burden = bcmul( $hourly_rate, bcadd( bcdiv( $udt_obj->getColumn( 'labor_burden_percent' ), 100 ), 1) );
					}
					if ( isset($policy_hourly_rates[$column]) AND is_object($policy_hourly_rates[$column]) ) {
						$hourly_rate = $policy_hourly_rates[$column]->getHourlyRate( $hourly_rate );
						$hourly_rate_with_burden = $policy_hourly_rates[$column]->getHourlyRate( $hourly_rate_with_burden );
					}

					//Split time by user,date,branch,department as that is the lowest level we can split time.
					//We always need to split time as much as possible as it can always be combined together by grouping.
					if ( !isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department]) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department] = array(
															//'branch_id' => $udt_obj->getColumn('branch_id'),
															'branch' => $udt_obj->getColumn('branch'),
															//'department_id' => $udt_obj->getColumn('department_id'),
															'department' => $udt_obj->getColumn('department'),
															'pay_period_start_date' => strtotime( $udt_obj->getColumn('pay_period_start_date') ),
															'pay_period_end_date' => strtotime( $udt_obj->getColumn('pay_period_end_date') ),
															'pay_period_transaction_date' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
															'pay_period' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
															'pay_period_id' => $udt_obj->getColumn('pay_period_id'),
															'min_punch_time_stamp' => strtotime( $udt_obj->getColumn('min_punch_time_stamp') ),
															'max_punch_time_stamp' => strtotime( $udt_obj->getColumn('max_punch_time_stamp') ),
															);
					}

					$udt_total_time_wage = bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
					$udt_total_time_wage_with_burden = bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate_with_burden );

					if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column]) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column] += $udt_obj->getColumn('total_time');
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column] = $udt_obj->getColumn('total_time');
					}

					if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_wage']) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_wage'] += $udt_total_time_wage;
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_wage_with_burden'] += $udt_total_time_wage_with_burden;
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_wage'] = $udt_total_time_wage;
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_wage_with_burden'] = $udt_total_time_wage_with_burden;
					}

					if ( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column] > 0 ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_hourly_rate'] = bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_wage'], bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column], 3600) );
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_hourly_rate_with_burden'] = bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_wage_with_burden'], bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column], 3600) );
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_hourly_rate'] = $hourly_rate;
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department][$column.'_hourly_rate_with_burden'] = $hourly_rate_with_burden;
					}

					//Gross wage calculation must go here otherwise it gets doubled up.
					//Worked Time is required for printable TimeSheets. Therefore this report is handled differently from TimeSheetSummary.
					if ( $column != 'worked_time' ) { //Exclude worked time from gross wage total.
						if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department]['gross_wage']) ) {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department]['gross_wage'] += $udt_total_time_wage;
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department]['gross_wage_with_burden'] += $udt_total_time_wage_with_burden;
						} else {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department]['gross_wage'] = $udt_total_time_wage;
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department]['gross_wage_with_burden'] = $udt_total_time_wage_with_burden;
						}
					}

					//Worked Days is tricky, since if they worked in multiple branches/departments in a single day, is that considered one worked day?
					//How do they find out how many days they worked in each branch/department though? It would add up to more days than they actually worked.
					//If we did some sort of partial day though, then due to rounding it could be thrown off, but either way it woulnd't be that helpful because
					//it would show they worked .33 of a day in one branch if they filtered by that branch.
					if ( $column == 'worked_time' AND $udt_obj->getColumn('total_time') > 0 AND !isset($this->tmp_data['worked_days'][$user_id.$date_stamp]) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch][$department]['worked_days'] = 1;
						$this->tmp_data['worked_days'][$user_id.$date_stamp] = TRUE;
					}

					//
					//Handle data form PDF timesheet. Don't split it out by branch/department
					//  as that causes multiple rows per day to display.
					//
					if ( strpos($format, 'pdf_') !== FALSE ) {
						if ( !isset($this->form_data['user_date_total'][$user_id]['data'][$date_stamp]) ) {

							$this->form_data['user_date_total'][$user_id]['data'][$date_stamp] = array(
																//'branch_id' => $udt_obj->getColumn('branch_id'),
																'branch' => $udt_obj->getColumn('branch'),
																//'department_id' => $udt_obj->getColumn('department_id'),
																'department' => $udt_obj->getColumn('department'),
																//'pay_period_start_date' => strtotime( $udt_obj->getColumn('pay_period_start_date') ),
																//'pay_period_end_date' => strtotime( $udt_obj->getColumn('pay_period_end_date') ),
																//'pay_period_transaction_date' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
																'pay_period' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
																'pay_period_id' => $udt_obj->getColumn('pay_period_id'),
																'time_stamp' => $date_stamp,
																'min_punch_time_stamp' => strtotime( $udt_obj->getColumn('min_punch_time_stamp') ),
																'max_punch_time_stamp' => strtotime( $udt_obj->getColumn('max_punch_time_stamp') ),
																);
						} else {
							if ( strtotime( $udt_obj->getColumn('min_punch_time_stamp') ) < $this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['min_punch_time_stamp'] ) {
								$this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['min_punch_time_stamp'] = strtotime( $udt_obj->getColumn('min_punch_time_stamp') );
							}
							if ( strtotime( $udt_obj->getColumn('max_punch_time_stamp') ) > $this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['max_punch_time_stamp'] ) {
								$this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['max_punch_time_stamp'] = strtotime( $udt_obj->getColumn('max_punch_time_stamp') );
							}

						}
						if ( isset($this->form_data['user_date_total'][$user_id]['data'][$date_stamp][$column]) ) {
							$this->form_data['user_date_total'][$user_id]['data'][$date_stamp][$column] += $udt_obj->getColumn('total_time');
						} else {
							$this->form_data['user_date_total'][$user_id]['data'][$date_stamp][$column] = $udt_obj->getColumn('total_time');
						}
						//Total overtime/absence time, along with categorizing the time for easier timesheet generation later on.
						if ( strpos( $column, 'absence_policy' ) !== FALSE ) {
							if ( isset($this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['absence_time']) ) {
								$this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['absence_time'] += $udt_obj->getColumn('total_time');
							} else {
								$this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['absence_time'] = $udt_obj->getColumn('total_time');
							}
							$this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['categorized_time']['absence_policy'][$column] = TRUE;
						}
						if ( strpos( $column, 'over_time_policy' ) !== FALSE ) {
							if ( isset($this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['over_time']) ) {
								$this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['over_time'] += $udt_obj->getColumn('total_time');
							} else {
								$this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['over_time'] = $udt_obj->getColumn('total_time');
							}
							$this->form_data['user_date_total'][$user_id]['data'][$date_stamp]['categorized_time']['over_time_policy'][$column] = TRUE;
						}
					}
					unset($hourly_rate);
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}
		//Debug::Arr($this->tmp_data['user_date_total'], 'User Date Total Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
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
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );
            
            if ( $currency_convert_to_base == TRUE AND is_object( $base_currency_obj ) ) {
				$this->tmp_data['user'][$u_obj->getId()]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
				$this->tmp_data['user'][$u_obj->getId()]['currency_rate'] = $u_obj->getColumn('currency_rate');
			} else {
			    $this->tmp_data['user'][$u_obj->getId()]['current_currency'] = $u_obj->getColumn('currency');
			}
            
			if ( strpos($format, 'pdf_') !== FALSE ) {
				if ( !isset($this->form_data['user_date_total'][$u_obj->getId()]) ) {
					$this->form_data['user_date_total'][$u_obj->getId()] = array();
				}
				//Make sure we merge this array with existing data and include all required fields for generating timesheets. This prevents slow columns from being returned.
				$this->form_data['user_date_total'][$u_obj->getId()] += (array)$u_obj->getObjectAsArray( array('first_name' => TRUE, 'last_name' => TRUE, 'employee_number' => TRUE, 'title' => TRUE, 'group' => TRUE, 'default_branch' => TRUE, 'default_department' => TRUE ) );
			}

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->form_data, 'zUser Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get verified timesheets for all pay periods considered in report.
		$pay_period_ids = array_unique( array_keys( $pay_period_ids ) );
		if ( isset($pay_period_ids) AND count($pay_period_ids) > 0 ) {
			$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
			$pptsvlf->getByPayPeriodIdAndCompanyId( $pay_period_ids, $this->getUserObject()->getCompany() );
			if ( $pptsvlf->getRecordCount() > 0 ) {
				foreach( $pptsvlf as $pptsv_obj ) {
					$this->tmp_data['verified_timesheet'][$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = array(
																									'user_verified' => $pptsv_obj->getUserVerified(),
																									'user_verified_date' => $pptsv_obj->getUserVerifiedDate(),
																									'status_id' => $pptsv_obj->getStatus(),
																									'status' => $pptsv_obj->getVerificationStatusShortDisplay(),
																									'updated_date' => $pptsv_obj->getUpdatedDate(),
																									);
				}
			}

			$ppslf = TTnew('PayPeriodScheduleListFactory');
			$ppslf->getByPayPeriodIdAndCompanyId($pay_period_ids, $this->getUserObject()->getCompany() );
			if ( $ppslf->getRecordCount() > 0 ) {
				foreach( $ppslf as $pps_obj ) {
					$this->tmp_data['pay_period_schedule'][$pps_obj->getID()] = array( 'start_week_day' => $pps_obj->getStartWeekDay() );
				}
			}

			if ( strpos($format, 'pdf_') !== FALSE ) {
				$pplf = TTnew('PayPeriodListFactory');
				$pplf->getByIDList( $pay_period_ids );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach( $pplf as $pp_obj ) {
						if ( isset($this->tmp_data['pay_period_schedule'][$pp_obj->getPayPeriodSchedule()]) ) {
							$this->form_data['pay_period'][$pp_obj->getID()] = $this->tmp_data['pay_period_schedule'][$pp_obj->getPayPeriodSchedule()];
						}
					}
				}
			}
		}

		//Debug::Arr($this->form_data, 'zUser Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess( $format ) {
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
														//'branch' => $branch,
														//'department' => $department,
														//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
														//'min_punch_time_stamp' => TTDate::getDate('TIME', $row['min_punch_time_stamp']),
														//'max_punch_time_stamp' => TTDate::getDate('TIME', $row['max_punch_time_stamp'])
														);

								if ( isset( $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]) ) {
									$processed_data['verified_time_sheet_user_verified'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['user_verified'];
									$processed_data['verified_time_sheet_user_verified_date'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['user_verified_date'];
									$processed_data['verified_time_sheet_status_id'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['status_id'];
									$processed_data['verified_time_sheet'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['status'];
									$processed_data['verified_time_sheet_date'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['updated_date'];
								} else {
									$processed_data['verified_time_sheet_status_id'] = $processed_data['verified_time_sheet_user_verified'] = $processed_data['verified_time_sheet_user_verified_date'] = FALSE;
									$processed_data['verified_time_sheet'] = TTi18n::getText('No');
									$processed_data['verified_time_sheet_date'] = FALSE;
								}

								if (  isset( $this->tmp_data['pay_period'][$row['pay_period_id']] ) ) {
									$processed_data['start_week_day'] = $this->tmp_data['pay_period'][$row['pay_period_id']]['start_week_day'];
								}

								if ( !isset($row['worked_time']) ) {
									$row['worked_time'] = 0;
								}

								if ( isset($this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_working']) ) {
									$processed_data['schedule_working'] = $this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_working'];
									$processed_data['schedule_working_diff'] = $row['worked_time'] - $this->tmp_data['schedule'][$user_id][$date_stamp]['schedule_working'];
									//We can only include scheduled_time once per user/date combination. Otherwise its duplicates the amounts and makes it incorrect.
									//So once its used unset it so it can't be used again.
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

								if ( strpos($format, 'pdf_') === FALSE ) {
									$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );
								} else {
									$this->form_data['user_date_total'][$user_id]['data'][$date_stamp] = array_merge( $this->form_data['user_date_total'][$user_id]['data'][$date_stamp], $date_columns, $processed_data );
									//$this->form_data[$user_id]['data'][] = array_merge( $row, $date_columns, $processed_data );
								}
							}
						}
					}
				}
				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
				$key++;
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->form_data, 'Form Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}


	function timesheetHeader( $user_data ) {
		$margins = $this->pdf->getMargins();
		$current_company = $this->getUserObject()->getCompanyObject();

		$border = 0;

		$total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(24) );
		$this->pdf->Cell( $total_width, $this->_pdf_scaleSize(10), TTi18n::gettext('Employee TimeSheet') , $border, 0, 'C');
		$this->pdf->Ln( $this->_pdf_scaleSize(10) );
		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(12) );
		$this->pdf->Cell( $total_width, $this->_pdf_scaleSize(5), $current_company->getName() , $border, 0, 'C');
		$this->pdf->Ln( $this->_pdf_scaleSize(5)+$this->_pdf_scaleSize(2) );

		//Generated Date/User top right.
		$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(6) );
		$this->pdf->setY( ($this->pdf->getY()-$this->_pdf_fontSize(6)) );
		$this->pdf->setX( $this->pdf->getPageWidth()-$margins['right']-50 );
		$this->pdf->Cell(50, $this->_pdf_scaleSize(2), TTi18n::getText('Generated').': '. TTDate::getDate('DATE+TIME', time() ), $border, 0, 'R', 0, '', 1);
		$this->pdf->Ln( $this->_pdf_scaleSize(2) );
		$this->pdf->setX( $this->pdf->getPageWidth()-$margins['right']-50 );
		$this->pdf->Cell(50, $this->_pdf_scaleSize(2), TTi18n::getText('Generated For').': '. $this->getUserObject()->getFullName(), $border, 0, 'R', 0, '', 1);
		$this->pdf->Ln( $this->_pdf_scaleSize(5) );

		$this->pdf->Rect( $this->pdf->getX(), $this->pdf->getY()-$this->_pdf_scaleSize(2), $total_width, $this->_pdf_scaleSize(14) );

		$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(12) );
		$this->pdf->Cell(30, $this->_pdf_scaleSize(5), TTi18n::gettext('Employee').':' , $border, 0, 'R');
		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(12) );
		$this->pdf->Cell(70+(($total_width-200)/2), $this->_pdf_scaleSize(5), $user_data['first_name'] .' '. $user_data['last_name'] .' (#'. $user_data['employee_number'] .')', $border, 0, 'L');

		$this->pdf->SetFont('','', $this->_pdf_fontSize(12) );
		$this->pdf->Cell(40, $this->_pdf_scaleSize(5), TTi18n::gettext('Title').':', $border, 0, 'R');
		$this->pdf->SetFont('','B', $this->_pdf_fontSize(12) );
		$this->pdf->Cell(60+(($total_width-200)/2), $this->_pdf_scaleSize(5), $user_data['title'], $border, 0, 'L');
		$this->pdf->Ln( $this->_pdf_scaleSize(5) );

		$this->pdf->SetFont('','', $this->_pdf_fontSize(12) );
		$this->pdf->Cell(30, $this->_pdf_scaleSize(5), TTi18n::gettext('Branch').':' , $border, 0, 'R');
		$this->pdf->Cell(70+(($total_width-200)/2), $this->_pdf_scaleSize(5), $user_data['default_branch'], $border, 0, 'L');
		$this->pdf->Cell(40, $this->_pdf_scaleSize(5), TTi18n::gettext('Department').':' , $border, 0, 'R');
		$this->pdf->Cell(60+(($total_width-200)/2), $this->_pdf_scaleSize(5), $user_data['default_department'], $border, 0, 'L');
		$this->pdf->Ln( $this->_pdf_scaleSize(5) );

		$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10) );
		$this->pdf->Ln( $this->_pdf_scaleSize(5) );

		return TRUE;
	}

	function timesheetPayPeriodHeader( $user_data, $data ) {
		$line_h = $this->_pdf_scaleSize(5);

		$margins = $this->pdf->getMargins();
		$total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(10) );
		$this->pdf->setFillColor(220,220,220);
		if ( isset($data['verified_time_sheet']) AND $data['verified_time_sheet_user_verified'] == TRUE AND $data['verified_time_sheet_user_verified_date'] != '' ) {
			$this->pdf->Cell( 77.9, $line_h, TTi18n::gettext('Pay Period').': '. $data['pay_period']['display'], 1, 0, 'L', 1);
			$this->pdf->Cell( $total_width-77.9, $line_h, TTi18n::gettext('Electronically signed by') .' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '. TTi18n::gettext('on') .' '. TTDate::getDate('DATE+TIME', $data['verified_time_sheet_user_verified_date']  ), 1, 0, 'R', 1);
		} else {
			$this->pdf->Cell( $total_width, $line_h, TTi18n::gettext('Pay Period').': '. $data['pay_period']['display'], 1, 0, 'L', 1);
		}

		$this->pdf->Ln();

		unset($this->timesheet_week_totals);
		$this->timesheet_week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

		return TRUE;
	}

	function timesheetWeekHeader( $column_widths ) {
		$line_h = $this->_pdf_scaleSize(5);

		$margins = $this->pdf->getMargins();
		$total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

		$buffer = ($total_width-200)/10;

		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(10) );
		$this->pdf->setFillColor(220,220,220);

		$this->pdf->Cell( $column_widths['line']+$buffer, $line_h, '#', 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['date_stamp']+$buffer, $line_h, TTi18n::gettext('Date'), 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['dow']+$buffer, $line_h, TTi18n::gettext('DoW'), 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['in_punch_time_stamp']+$buffer, $line_h, TTi18n::gettext('In'), 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['out_punch_time_stamp']+$buffer, $line_h, TTi18n::gettext('Out'), 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['worked_time']+$buffer, $line_h, TTi18n::gettext('Worked Time'), 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['regular_time']+$buffer, $line_h, TTi18n::gettext('Regular Time'), 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['over_time']+$buffer, $line_h, TTi18n::gettext('Over Time'), 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['absence_time']+$buffer, $line_h, TTi18n::gettext('Absence Time'), 1, 0, 'C', 1, '', 1 );

		//$this->pdf->MultiCell( $column_widths['line']+$buffer, $line_h, '#' , 1, 'C', 1, 0);
		//$this->pdf->MultiCell( $column_widths['date_stamp']+$buffer, $line_h, TTi18n::gettext('Date') , 1, 'C', 1, 0);
		//$this->pdf->MultiCell( $column_widths['dow']+$buffer, $line_h, TTi18n::gettext('DoW') , 1, 'C', 1, 0);
		//$this->pdf->MultiCell( $column_widths['in_punch_time_stamp']+$buffer, $line_h, TTi18n::gettext('In') , 1, 'C', 1, 0);
		//$this->pdf->MultiCell( $column_widths['out_punch_time_stamp']+$buffer, $line_h, TTi18n::gettext('Out') , 1, 'C', 1, 0);
		//$this->pdf->MultiCell( $column_widths['worked_time']+$buffer, $line_h, TTi18n::gettext('Worked Time') , 1, 'C', 1, 0, '','', TRUE, 1 );
		//$this->pdf->MultiCell( $column_widths['regular_time']+$buffer, $line_h, TTi18n::gettext('Regular Time') , 1, 'C', 1, 0, '','', TRUE, 1 );
		//$this->pdf->MultiCell( $column_widths['over_time']+$buffer, $line_h, TTi18n::gettext('Over Time') , 1, 'C', 1, 0);
		//$this->pdf->MultiCell( $column_widths['absence_time']+$buffer, $line_h, TTi18n::gettext('Absence Time') , 1, 'C', 1, 0);
		$this->pdf->Ln();

		return TRUE;
	}

	function timesheetDayRow( $format, $columns, $column_widths, $user_data, $data, $prev_data ) {
		$margins = $this->pdf->getMargins();
		$total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

		$buffer = ($total_width-200)/10;

		//Handle page break.
		$page_break_height = 25;
		if ( $this->counter_i == 1 OR $this->counter_x == 1 ) {
			if ( $this->counter_i == 1 ) {
				$page_break_height += 5;
			}
			$page_break_height += 5;
		}

		$this->timesheetCheckPageBreak( $page_break_height, TRUE );

		//Debug::Text('Pay Period Changed: Current: '.  $data['pay_period_id'] .' Prev: '. $prev_data['pay_period_id'] .' Counter X: '. $this->counter_x .' Max I: '. $this->max_i .' PP Start: '. TTDate::getDate('DATE', $data['pay_period_start_date'] )  , __FILE__, __LINE__, __METHOD__,10);
		if ( $prev_data !== FALSE AND $data['pay_period_id'] != $prev_data['pay_period_id'] ) {
			//Only display week total if we are in the middle of a week when the pay period ends, not at the end of the week.
			if ( $this->counter_x != 1 ) {
				$this->timesheetWeekTotal( $column_widths, $this->timesheet_week_totals );
				$this->counter_x++;
			}
			$this->timesheetPayPeriodHeader( $user_data, $data );
		}

		//Show Header
		if ( $this->counter_i == 1 OR $this->counter_x == 1 ) {
			//Debug::Text('aFirst Row: Header', __FILE__, __LINE__, __METHOD__,10);
			if ( $this->counter_i == 1 ) {
				$this->timesheetPayPeriodHeader( $user_data, $data );
			}

			$this->timesheetWeekHeader( $column_widths );
		}

		if ( $this->counter_x % 2 == 0 ) {
			$this->pdf->setFillColor(220,220,220);
		} else {
			$this->pdf->setFillColor(255,255,255);
		}

		if ( $data['time_stamp'] !== '' ) {
			$default_line_h = $this->_pdf_scaleSize(4);
			$line_h = $default_line_h;

			$total_rows_arr = array();

			//Find out how many punches fall on this day, so we can change row height to fit.
			$total_punch_rows = 1;

			if ( isset($user_data['punch_rows'][$data['pay_period_id']][$data['time_stamp']]) ) {
				//Debug::Text('Punch Data Row: '. $this->counter_x, __FILE__, __LINE__, __METHOD__,10);

				$day_punch_data = $user_data['punch_rows'][$data['pay_period_id']][$data['time_stamp']];
				$total_punch_rows = count($day_punch_data);
			} else {
				//Debug::Text('NO Punch Data Row: '. $this->counter_x, __FILE__, __LINE__, __METHOD__,10);
			}

			$total_rows_arr[] = $total_punch_rows;

			$total_over_time_rows = 1;
			if ( $data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy']) ) {
				$total_over_time_rows = count($data['categorized_time']['over_time_policy']);
			}
			$total_rows_arr[] = $total_over_time_rows;

			$total_absence_rows = 1;
			if ( $data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy']) ) {
				$total_absence_rows = count($data['categorized_time']['absence_policy']);
			}
			$total_rows_arr[] = $total_absence_rows;

			rsort($total_rows_arr);
			$max_rows = $total_rows_arr[0];
			$line_h = ( $format == 'pdf_timesheet_detail' ) ? $default_line_h*$max_rows : $default_line_h;

			$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9) );
			$this->pdf->Cell( $column_widths['line']+$buffer, $line_h, $this->counter_x , 1, 0, 'C', 1);
			$this->pdf->Cell( $column_widths['date_stamp']+$buffer, $line_h, TTDate::getDate('DATE', $data['time_stamp'] ), 1, 0, 'C', 1);
			$this->pdf->Cell( $column_widths['dow']+$buffer, $line_h, date('D', $data['time_stamp']) , 1, 0, 'C', 1);

			$pre_punch_x = $this->pdf->getX();
			$pre_punch_y = $this->pdf->getY();

			//Print Punches
			if ( $format == 'pdf_timesheet_detail' AND isset($day_punch_data) ) {
				$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

				$n=0;
				foreach( $day_punch_data as $punch_control_id => $punch_data ) {
					if ( !isset($punch_data[10]['time_stamp']) ) {
						$punch_data[10]['time_stamp'] = NULL;
						$punch_data[10]['type_code'] = NULL;
					}
					if ( !isset($punch_data[20]['time_stamp']) ) {
						$punch_data[20]['time_stamp'] = NULL;
						$punch_data[20]['type_code'] = NULL;
					}

					if ( $n > 0 ) {
						$this->pdf->setXY( $pre_punch_x, $punch_y+$default_line_h);
					}

					$this->pdf->Cell( $column_widths['in_punch_time_stamp']+$buffer, $line_h/$total_punch_rows, TTDate::getDate('TIME', $punch_data[10]['time_stamp'] ) .' '. $punch_data[10]['type_code'], 1, 0, 'C', 1);
					$this->pdf->Cell( $column_widths['out_punch_time_stamp']+$buffer, $line_h/$total_punch_rows, TTDate::getDate('TIME', $punch_data[20]['time_stamp'] ) .' '. $punch_data[20]['type_code'], 1, 0, 'C', 1);

					$punch_x = $this->pdf->getX();
					$punch_y = $this->pdf->getY();

					$n++;
				}

				$this->pdf->setXY( $punch_x, $pre_punch_y);

				$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9) );
			} else {
				$this->pdf->Cell( $column_widths['in_punch_time_stamp']+$buffer, $line_h, TTDate::getDate('TIME', $data['min_punch_time_stamp'] ), 1, 0, 'C', 1);
				$this->pdf->Cell( $column_widths['out_punch_time_stamp']+$buffer, $line_h, TTDate::getDate('TIME', $data['max_punch_time_stamp'] ), 1, 0, 'C', 1);
			}

			$this->pdf->Cell( $column_widths['worked_time']+$buffer , $line_h, TTDate::getTimeUnit( $data['worked_time'] ) , 1, 0, 'C', 1);
			$this->pdf->Cell( $column_widths['regular_time']+$buffer, $line_h, TTDate::getTimeUnit( $data['regular_time'] ), 1, 0, 'C', 1);

			if ( $format == 'pdf_timesheet_detail' ) {
				if ( $data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy']) ) {
					$pre_over_time_x = $this->pdf->getX();
					$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

					//Count how many absence policy rows there are.
					$over_time_policy_total_rows = count($data['categorized_time']['over_time_policy']);
					foreach( $data['categorized_time']['over_time_policy'] as $policy_column => $value ) {
						$this->pdf->Cell( $column_widths['over_time']+$buffer, $line_h/$total_over_time_rows, $columns[$policy_column].': '.TTDate::getTimeUnit( $data[$policy_column] ), 1, 0, 'C', 1);
						$this->pdf->setXY( $pre_over_time_x, $this->pdf->getY()+($line_h/$total_over_time_rows) );

						$over_time_x = $this->pdf->getX();
					}
					$this->pdf->setXY( $over_time_x+$column_widths['over_time']+$buffer, $pre_punch_y);
					$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9) );
				} else {
					$this->pdf->Cell( $column_widths['over_time']+$buffer, $line_h, TTDate::getTimeUnit( $data['over_time'] ), 1, 0, 'C', 1);
				}

				if ( $data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy']) ) {
					$pre_absence_time_x = $this->pdf->getX();
					$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

					//Count how many absence policy rows there are.
					$absence_policy_total_rows = count($data['categorized_time']['absence_policy']);
					foreach( $data['categorized_time']['absence_policy'] as $policy_column => $value ) {
						$this->pdf->Cell( $column_widths['absence_time']+$buffer, $line_h/$total_absence_rows, $columns[$policy_column].': '.TTDate::getTimeUnit( $data[$policy_column] ), 1, 0, 'C', 1);
						$this->pdf->setXY( $pre_absence_time_x, $this->pdf->getY()+($line_h/$total_absence_rows));
					}

					$this->pdf->setY( $this->pdf->getY()-($line_h/$total_absence_rows));
					$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9) );
				} else {
					$this->pdf->Cell( $column_widths['absence_time']+$buffer, $line_h, TTDate::getTimeUnit( $data['absence_time'] ), 1, 0, 'C', 1);
				}
			} else {
				$this->pdf->Cell( $column_widths['over_time']+$buffer, $line_h, TTDate::getTimeUnit( $data['over_time'] ), 1, 0, 'C', 1);
				$this->pdf->Cell( $column_widths['absence_time']+$buffer, $line_h, TTDate::getTimeUnit( $data['absence_time'] ), 1, 0, 'C', 1);
			}
			$this->pdf->Ln( $line_h );

			unset($day_punch_data);
		}

		$this->timesheet_totals['worked_time'] += $data['worked_time'];
		$this->timesheet_totals['absence_time'] += $data['absence_time'];
		$this->timesheet_totals['regular_time'] += $data['regular_time'];
		$this->timesheet_totals['over_time'] += $data['over_time'];

		$this->timesheet_week_totals['worked_time'] += $data['worked_time'];
		$this->timesheet_week_totals['absence_time'] += $data['absence_time'];
		$this->timesheet_week_totals['regular_time'] += $data['regular_time'];
		$this->timesheet_week_totals['over_time'] += $data['over_time'];

		//Debug::Text('Row: '. $this->counter_x .' I: '. $this->counter_i .' Max I: '. $this->max_i, __FILE__, __LINE__, __METHOD__,10);
		//if ( $this->counter_x % 7 == 0 OR $this->counter_i == $max_i ) { //This used to change the week every 7 days starting from the first date in the timesheet.
		if ( TTDate::getDayOfWeek( TTDate::getMiddleDayEpoch($data['time_stamp'])+86400 ) == $data['start_week_day']
				OR ( isset($prev_data['start_week_day']) AND $data['start_week_day'] != $prev_data['start_week_day'] )
				OR $this->counter_i == $this->max_i ) {
			$this->timesheetWeekTotal( $column_widths, $this->timesheet_week_totals );

			unset($this->timesheet_week_totals);
			$this->timesheet_week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
		}

		$this->counter_i++;
		$this->counter_x++;

		return TRUE;
	}

	function timesheetWeekTotal( $column_widths, $week_totals ) {
		//Debug::Text('Week Total: Row: '. $this->counter_x, __FILE__, __LINE__, __METHOD__,10);

		$margins = $this->pdf->getMargins();
		$total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

		$buffer = ($total_width-200)/10;

		$line_h = $this->_pdf_scaleSize(6);

		//Show Week Total.
		$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['in_punch_time_stamp']+$column_widths['out_punch_time_stamp']+($buffer*5);
		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(9) );
		$this->pdf->Cell( $total_cell_width, $line_h, TTi18n::gettext('Week Total').': ', 0, 0, 'R', 0);
		$this->pdf->Cell( $column_widths['worked_time']+$buffer, $line_h, TTDate::getTimeUnit( $week_totals['worked_time'] ) , 0, 0, 'C', 0);
		$this->pdf->Cell( $column_widths['regular_time']+$buffer, $line_h, TTDate::getTimeUnit( $week_totals['regular_time'] ), 0, 0, 'C', 0);
		$this->pdf->Cell( $column_widths['over_time']+$buffer, $line_h, TTDate::getTimeUnit( $week_totals['over_time'] ), 0, 0, 'C', 0);
		$this->pdf->Cell( $column_widths['absence_time']+$buffer, $line_h, TTDate::getTimeUnit( $week_totals['absence_time'] ), 0, 0, 'C', 0);
		$this->pdf->Ln();

		$this->counter_x=0; //Reset to 0, as the counter increases to 1 immediately after.
		$this->counter_y++;

		return TRUE;
	}

	function timesheetTotal( $column_widths, $totals ) {
		$margins = $this->pdf->getMargins();
		$total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

		$buffer = ($total_width-200)/10;

		$line_h = $this->_pdf_scaleSize(6);

		$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['in_punch_time_stamp']+($buffer*4);
		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(9) );
		$this->pdf->Cell( $total_cell_width, $line_h, '' , 0, 0, 'R', 0);
		$this->pdf->Cell( $column_widths['out_punch_time_stamp']+$buffer, $line_h, TTi18n::gettext('Overall Total').': ', 'T', 0, 'R', 0);
		$this->pdf->Cell( $column_widths['worked_time']+$buffer, $line_h, TTDate::getTimeUnit( $totals['worked_time'] ) , 'T', 0, 'C', 0);
		$this->pdf->Cell( $column_widths['regular_time']+$buffer, $line_h, TTDate::getTimeUnit( $totals['regular_time'] ), 'T', 0, 'C', 0);
		$this->pdf->Cell( $column_widths['over_time']+$buffer, $line_h, TTDate::getTimeUnit( $totals['over_time'] ), 'T', 0, 'C', 0);
		$this->pdf->Cell( $column_widths['absence_time']+$buffer, $line_h, TTDate::getTimeUnit( $totals['absence_time'] ), 'T', 0, 'C', 0);
		$this->pdf->Ln();

		return TRUE;
	}

	function timesheetNoData() {
		$margins = $this->pdf->getMargins();
		$current_company = $this->getUserObject()->getCompanyObject();

		$border = 0;

		$total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

		$this->pdf->Ln(10);

		$this->pdf->Rect( $this->pdf->getX(), $this->pdf->getY()-2, $total_width, 10 );

		$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(12) );
		$this->pdf->Cell($total_width, 5, 'NO TIMESHEET DATA FOR THIS PERIOD', $border, 0, 'C');

		$this->pdf->Ln(10);

		$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10) );
		$this->pdf->Ln();

		return TRUE;
	}

	function timesheetSignature( $user_data, $data ) {
		$border = 0;

		$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10) );
		$this->pdf->setFillColor(255,255,255);
		$this->pdf->Ln(1);

		$margins = $this->pdf->getMargins();
		$total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

		$buffer = ($total_width-200)/4;

		$line_h = $this->_pdf_scaleSize(6);

		//Signature lines
		$this->pdf->MultiCell($total_width,5, TTi18n::gettext('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '.TTi18n::gettext('worked during the designated period.'), $border, 'L');
		$this->pdf->Ln( $line_h );

		$this->pdf->Cell(40+$buffer, $line_h, TTi18n::gettext('Employee Signature').':', $border, 0, 'L');
		$this->pdf->Cell(60+$buffer, $line_h, '_____________________________' , $border, 0, 'C');
		$this->pdf->Cell(40+$buffer, $line_h, TTi18n::gettext('Supervisor Signature').':', $border, 0, 'R');
		$this->pdf->Cell(60+$buffer, $line_h, '_____________________________' , $border, 0, 'C');

		$this->pdf->Ln(  $line_h );
		$this->pdf->Cell(40+$buffer, $line_h, '', $border, 0, 'R');
		$this->pdf->Cell(60+$buffer, $line_h, $user_data['first_name'] .' '. $user_data['last_name'] , $border, 0, 'C');

		$this->pdf->Ln(  $line_h );
		$this->pdf->Cell(140+($buffer*3), $line_h, '', $border, 0, 'R');
		$this->pdf->Cell(60+$buffer, $line_h, '_____________________________' , $border, 0, 'C');

		$this->pdf->Ln(  $line_h );
		$this->pdf->Cell(140+($buffer*3), $line_h, '', $border, 0, 'R');
		$this->pdf->Cell(60+$buffer, $line_h, TTi18n::gettext('(print name)'), $border, 0, 'C');

		if ( isset($data['verified_time_sheet_date']) AND $data['verified_time_sheet_date'] != FALSE ) {
			$this->pdf->Ln( $line_h );
			$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(10) );
			$this->pdf->Cell(200+$buffer, $line_h, TTi18n::gettext('TimeSheet electronically signed by').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '. TTi18n::gettext('on') .' '. TTDate::getDate('DATE+TIME', $data['verified_time_sheet_date'] ), $border, 0, 'C');
		}


		return TRUE;
	}

	//function timesheetFooter( $pdf_created_date, $adjust_x, $adjust_y ) {
	function timesheetFooter() {
		$margins = $this->pdf->getMargins();

		$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

		//Save x,y and restore after footer is set.
		$x = $this->pdf->getX();
		$y = $this->pdf->getY();

		//Jump to end of page.
		$this->pdf->setY( $this->pdf->getPageHeight()-$margins['bottom']-$margins['top']-10 );

		$this->pdf->Cell( ($this->pdf->getPageWidth()-$margins['right']), $this->_pdf_fontSize(5), TTi18n::getText('Page').' '. $this->pdf->PageNo() .' of '. $this->pdf->getAliasNbPages(), 0, 0, 'C', 0 );
		$this->pdf->Ln();

		$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(6) );
		$this->pdf->Cell( ($this->pdf->getPageWidth()-$margins['right']), $this->_pdf_fontSize(5), TTi18n::gettext('Report Generated By').' '. APPLICATION_NAME .' v'. APPLICATION_VERSION, 0, 0, 'C', 0 );

		$this->pdf->setX( $x );
		$this->pdf->setY( $y );
		return TRUE;
	}

	function timesheetCheckPageBreak( $height, $add_page = TRUE ) {
		$margins = $this->pdf->getMargins();

		if ( ($this->pdf->getY()+$height) > ($this->pdf->getPageHeight()-$margins['bottom']-$margins['top']-10) ) {
			//Debug::Text('Detected Page Break needed...', __FILE__, __LINE__, __METHOD__,10);
			$this->timesheetAddPage();

			return TRUE;
		}
		return FALSE;
	}

	function timesheetHandleDayGaps( $start_date, $end_date, $format, $columns, $column_widths, $user_data, $data, $prev_data ) {
		//Debug::Text('FOUND GAP IN DAYS!', __FILE__, __LINE__, __METHOD__,10);
		$blank_row_data = FALSE;
		for( $d=TTDate::getBeginDayEpoch($start_date); $d < $end_date; $d+=86400) {
			if ( $this->_pdf_checkMaximumPageLimit() == FALSE ) {
				Debug::Text('Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__,10);
				//Exceeded maximum pages, stop processing.
				$this->_pdf_displayMaximumPageLimitError();
				break;
			}

			//Need to handle pay periods switching in the middle of a string of blank rows.
			$blank_row_time_stamp = TTDate::getBeginDayEpoch($d);
			//Debug::Text('Blank row timestamp: '. TTDate::getDate('DATE+TIME', $blank_row_time_stamp ) .' Pay Period End Date: '. TTDate::getDate('DATE+TIME', $prev_data['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__,10);
			if ( $blank_row_time_stamp >= $prev_data['pay_period_end_date'] ) {
				//Debug::Text('aBlank row timestamp: '. TTDate::getDate('DATE+TIME', $blank_row_time_stamp ) .' Pay Period End Date: '. TTDate::getDate('DATE+TIME', $prev_data['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__,10);
				$pay_period_id = $data['pay_period_id'];
				$pay_period_start_date = $data['pay_period_start_date'];
				$pay_period_end_date = $data['pay_period_end_date'];
				$pay_period = $data['pay_period'];
			} else {
				//Debug::Text('bBlank row timestamp: '. TTDate::getDate('DATE+TIME', $blank_row_time_stamp ) .' Pay Period End Date: '. TTDate::getDate('DATE+TIME', $prev_data['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__,10);
				$pay_period_id = $prev_data['pay_period_id'];
				$pay_period_start_date = $prev_data['pay_period_start_date'];
				$pay_period_end_date = $prev_data['pay_period_end_date'];
				$pay_period = $prev_data['pay_period'];
			}

			$blank_row_data = array(
										'pay_period_id' => $pay_period_id,
										'pay_period_start_date' => $pay_period_start_date,
										'pay_period_end_date' => $pay_period_end_date,
										'pay_period' => $pay_period,
										'start_week_day' => $data['start_week_day'],
										'time_stamp' => $blank_row_time_stamp,
										'min_punch_time_stamp' => NULL,
										'max_punch_time_stamp' => NULL,
										'in_punch_time' => NULL,
										'out_punch_time' => NULL,
										'worked_time' => NULL,
										'regular_time' => NULL,
										'over_time' => NULL,
										'absence_time' => NULL
									);

			//Don't increase max_i if the last day is a gap. However if there are gaps in the middle of the pay period will cause a problem?
			if ( $d != TTDate::getBeginDayEpoch($end_date) ) {
				$this->max_i++;
			}
			$this->timesheetDayRow( $format, $columns, $column_widths, $user_data, $blank_row_data, $prev_data ); //Prev data is actually the current data for a blank row.

			//Make sure we set prev_data here as well so if a pay period changes in the middle of a blank row range its detected only once. not multiple times.
			$prev_data = $blank_row_data;
			unset( $blank_row_time_stamp, $pay_period_id, $pay_period_start_date, $pay_period_end_date, $pay_period);
		}

		return $blank_row_data; //Return the last rows data, so we can use this as the new prev_data in the main loop.
	}

	function timesheetAddPage() {
		$this->timesheetFooter();
		$this->pdf->AddPage();
		return TRUE;
	}


	function _outputPDFTimesheet( $format ) {
		Debug::Text(' Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

		$border = 0;

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object($current_company) ) {
			Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		$pdf_created_date = time();

		$adjust_x = 10;
		$adjust_y = 10;

		//Debug::Arr($this->form_data, 'Form Data: ', __FILE__, __LINE__, __METHOD__,10);
		if ( isset($this->form_data) AND count($this->form_data) > 0 ) {
			//Make sure we sort the form data for printable timesheets.
			$this->form_data['user_date_total'] = Sort::arrayMultiSort( $this->form_data['user_date_total'], $this->getSortConfig() );

			//Get pay period schedule data for each pay period.
			$this->pdf = new TTPDF( $this->config['other']['page_orientation'], 'mm', $this->config['other']['page_format'], $this->getUserObject()->getCompanyObject()->getEncoding() );

			$this->pdf->SetAuthor( APPLICATION_NAME );
			$this->pdf->SetTitle( $this->title );
			$this->pdf->SetSubject( APPLICATION_NAME .' '. TTi18n::getText('Report') );

			$this->pdf->setMargins( $this->config['other']['left_margin'], $this->config['other']['top_margin'], $this->config['other']['right_margin'] );
			//Debug::Arr($this->config['other'], 'Margins: ', __FILE__, __LINE__, __METHOD__,10);

			$this->pdf->SetAutoPageBreak(FALSE);

			$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10) );

			//Debug::Arr($this->form_data, 'zabUser Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

			$filter_data = $this->getFilterConfig();
			$columns = Misc::trimSortPrefix( $this->getOptions('columns') );

			$this->getProgressBarObject()->start( $this->getAMFMessageID(), 2, NULL, TTi18n::getText('Querying Database...') ); //Iterations need to be 2, otherwise progress bar is not created.
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), 2 );

			if ( $format == 'pdf_timesheet_detail' ) {
				$plf = TTnew( 'PunchListFactory' );
				$plf->getSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data);
				Debug::Text('Got punch data... Total Rows: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $plf->getRecordCount(), NULL, TTi18n::getText('Retrieving Punch Data...') );
				if ( $plf->getRecordCount() > 0 ) {
					foreach( $plf as $key => $p_obj ) {
						$this->form_data['user_date_total'][$p_obj->getColumn('user_id')]['punch_rows'][$p_obj->getColumn('pay_period_id')][TTDate::strtotime( $p_obj->getColumn('date_stamp'))][$p_obj->getPunchControlID()][$p_obj->getStatus()] = array( 'status_id' => $p_obj->getStatus(), 'type_id' => $p_obj->getType(), 'type_code' => $p_obj->getTypeCode(), 'time_stamp' => $p_obj->getTimeStamp() );
						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					}
				}
				unset($plf,$p_obj);
			}

			Debug::Text('Drawing timesheets...', __FILE__, __LINE__, __METHOD__,10);
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->form_data['user_date_total']), NULL, TTi18n::getText('Generating TimeSheets...') );
			$key=0;
			foreach( $this->form_data['user_date_total'] as $user_data ) {
				if ( $this->_pdf_checkMaximumPageLimit() == FALSE ) {
					Debug::Text('Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__,10);
					//Exceeded maximum pages, stop processing.
					$this->_pdf_displayMaximumPageLimitError();
					break;
				}

				if ( isset($user_data['first_name']) AND isset($user_data['last_name']) AND isset($user_data['employee_number']) ) {
					$this->pdf->AddPage( $this->config['other']['page_orientation'], 'Letter' );

					$this->timesheetHeader( $user_data );

					//Start displaying dates/times here. Start with header.
					$column_widths = array(
										'line' => 5,
										'date_stamp' => 20,
										'dow' => 10,
										'in_punch_time_stamp' => 20,
										'out_punch_time_stamp' => 20,
										'worked_time' => 20,
										'regular_time' => 20,
										'over_time' => 40.6,
										'absence_time' => 45,
										);

					if ( isset($user_data['data']) AND is_array($user_data['data']) ) {
						$user_data['data'] = Sort::arrayMultiSort( $user_data['data'],  array( 'time_stamp' => SORT_ASC ) );

						$this->timesheet_week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
						$this->timesheet_totals = array();
						$this->timesheet_totals = Misc::preSetArrayValues( $this->timesheet_totals, array( 'worked_time','absence_time', 'regular_time', 'over_time' ), 0 );

						$this->counter_i=1; //Overall row counter.
						$this->counter_x=1; //Row counter, starts over each week.
						$this->counter_y=1; //Week counter.
						$this->max_i = count($user_data['data']);
						$prev_data = FALSE;
						foreach( $user_data['data'] as $data ) {
							if ( $this->_pdf_checkMaximumPageLimit() == FALSE ) {
								Debug::Text('Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__,10);
								//Exceeded maximum pages, stop processing.
								$this->_pdf_displayMaximumPageLimitError();
								break 2;
							}

							if ( isset($this->form_data['pay_period'][$data['pay_period_id']]) ) {
								//Debug::Arr( $data, 'Data: i: '. $this->counter_i .' x: '. $this->counter_x .' Max I: '. $this->max_i, __FILE__, __LINE__, __METHOD__,10);
								$data = Misc::preSetArrayValues( $data, array('time_stamp', 'in_punch_time_stamp', 'out_punch_time_stamp', 'worked_time', 'absence_time', 'regular_time', 'over_time' ), '--' );

								$data['start_week_day'] = $this->form_data['pay_period'][$data['pay_period_id']]['start_week_day'];

								$row_date_gap = ($prev_data !== FALSE ) ? (TTDate::getMiddleDayEpoch($data['time_stamp'])-TTDate::getMiddleDayEpoch($prev_data['time_stamp'])) : 0; //Take into account DST by using mid-day epochs.
								//Debug::Text('Row Gap: '. $row_date_gap, __FILE__, __LINE__, __METHOD__,10);
								if ( $prev_data !== FALSE AND $row_date_gap > (86400) ) {
									//Handle gaps between individual days with hours.
									$prev_data = $this->timesheetHandleDayGaps( $prev_data['time_stamp']+86400, $data['time_stamp'], $format, $columns, $column_widths, $user_data, $data, $prev_data);
								} elseif ( $this->counter_i == 1 AND (TTDate::getMiddleDayEpoch($data['time_stamp'])-TTDate::getMiddleDayEpoch($data['pay_period_start_date'])) >= 86400 ) {
									//Always fill gaps between the pay period start date and the date with time, even if not filtering by pay period.
									//Handle gaps before the first date with hours is displayed, only when filtering by pay period though.
									$prev_data = $this->timesheetHandleDayGaps( $data['pay_period_start_date'], $data['time_stamp'], $format, $columns, $column_widths, $user_data, $data, $prev_data );
								}

								//Check for gaps at the end of the date range and before the end of the pay period.
								//If we find one we have to increase $max_i by one so the last timesheetDayRow doesn't display the week totals.
								if ( $this->counter_i == $this->max_i AND (TTDate::getMiddleDayEpoch($data['pay_period_end_date'])-TTDate::getMiddleDayEpoch($data['time_stamp'])) >= 86400 ) {
									$this->max_i++;
								}

								$this->timesheetDayRow( $format, $columns, $column_widths, $user_data, $data, $prev_data );

								$prev_data = $data;
							} else {
								Debug::Text('Pay Period does not exist, skipping... ID: '. $data['pay_period_id'], __FILE__, __LINE__, __METHOD__,10);
							}
						}

						//Check for gaps at the end of the date range and before the end of the pay period so we can fill them in. Only when filtering by pay period though.
						//as filtering by start/end date can result in a lot of data if they want show time for the last year but an employee was just hired.
						if ( isset($data['pay_period_end_date']) AND (TTDate::getMiddleDayEpoch($data['pay_period_end_date'])-TTDate::getMiddleDayEpoch($data['time_stamp'])) >= 86400 ) {
							//Handle gaps between the last day with hours and the end of the pay period.
							//Always fill gaps between the pay period end date and the current date with time, even if not filtering by pay period.
							$this->timesheetHandleDayGaps( $data['time_stamp']+86400, $data['pay_period_end_date'], $format, $columns, $column_widths, $user_data, $data, $prev_data );
						}

						if ( isset($this->timesheet_totals) AND is_array($this->timesheet_totals) ) {
							//Display overall totals.
							$this->timesheetTotal( $column_widths, $this->timesheet_totals );
							unset($totals);
						}

						$this->timesheetSignature( $user_data, $data );

						unset($data, $prev_data);
					} else {
						$this->timesheetNoData();
					}

					$this->timesheetFooter( $pdf_created_date, $adjust_x, $adjust_y );
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
				if ( $key % 25 == 0 AND $this->isSystemLoadValid() == FALSE ) {
					return FALSE;
				}
				$key++;
			}

			$output = $this->pdf->Output('','S');

			return $output;

		}

		Debug::Text('No data to return...', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function _output( $format = NULL ) {
		if ( $format == 'pdf_timesheet' OR $format == 'pdf_timesheet_print'
				OR $format == 'pdf_timesheet_detail' OR $format == 'pdf_timesheet_detail_print' ) {
			return $this->_outputPDFTimesheet( $format );
		} else {
			return parent::_output( $format );
		}
	}

}
?>