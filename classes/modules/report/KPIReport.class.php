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
 * @package Core
 */
class KPIReport extends Report {

	function __construct() {
		$this->title = TTi18n::getText('Review Summary Report');
		$this->file_name = 'review_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('hr_report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('hr_report','user_review', $user_id, $company_id ) ) {
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
                                        '-2060-include_reviewer_user_id' => TTi18n::gettext('Reviewer Include'),
										'-2070-exclude_reviewer_user_id' => TTi18n::gettext('Reviewer Exclude'),
										'-2080-default_branch_id' => TTi18n::gettext('Default Branch'),
										'-2090-default_department_id' => TTi18n::gettext('Default Department'),
										//'-2080-punch_branch_id' => TTi18n::gettext('Punch Branch'),
										//'-2090-punch_department_id' => TTi18n::gettext('Punch Department'),
                                        '-2100-kpi_id' => TTi18n::gettext('Key Performance Indicators'),
                                        //'-2090-user_id' => TTi18n::gettext('Employee'),
                                        //'-2110-reviewer_user_id' => TTi18n::gettext('Reviewer'),
                                        '-2120-kpi_group_id' => TTi18n::gettext('KPI Group'),
                                        '-2130-kpi_status_id' => TTi18n::gettext('KPI Status'),
                                        '-2140-kpi_type_id' => TTi18n::gettext('KPI Type'),
                                        '-2150-user_review_control_status_id' => TTi18n::gettext('Review Status'),
                                        '-2160-user_review_control_type_id' => TTi18n::gettext('Review Type'),

                                        '-2170-term_id' => TTi18n::gettext('Review Terms'),
                                        '-2180-severity_id' => TTi18n::gettext('Review Severity/Importance'),

                                        '-2188-review_tag' => TTi18n::gettext('Review Tags'),

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
				$retval = array_merge(
									TTDate::getReportDateOptions( 'user_review_control.start', TTi18n::getText('Start Date'), 16, FALSE ),
									TTDate::getReportDateOptions( 'user_review_control.end', TTi18n::getText('End Date'), 17, FALSE ),
									TTDate::getReportDateOptions( 'user_review_control.due', TTi18n::getText('Due Date'), 18, FALSE )
								);
				break;
            case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'),NULL, 'KPIReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'KPIReport', 'custom_column' );
				}
                break;
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
                    $report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'KPIReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
                    $report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'KPIReport', 'custom_column' );
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
										'-1000-kpi.name' => TTi18n::gettext('Key Performance Indicators'),

                                        '-1005-kpi.type' => TTi18n::getText('KPI Type'),
                                        '-1000-kpi.status' => TTi18n::gettext('KPI Status'),

                                        '-1010-kpi.description' => TTi18n::gettext('Description'),

                                        '-1020-user.first_name' => TTi18n::gettext('First Name'),
										'-1030-user.middle_name' => TTi18n::gettext('Middle Name'),
										'-1040-user.last_name' => TTi18n::gettext('Last Name'),
										'-1050-user.full_name' => TTi18n::gettext('Full Name'),
                                        '-1060-user.status' => TTi18n::gettext('Employee Status'),
                                        '-1070-user.sex' => TTi18n::gettext('Gender'),
                                        '-1080-user.user_group' => TTi18n::gettext('Employee Group'),
                                        '-1090-user.title' => TTi18n::gettext('Employee Title'),
                                        '-1100-user.default_branch' => TTi18n::gettext('Branch'), //abbreviate for space
										'-1110-user.default_department' => TTi18n::gettext('Department'), //abbreviate for space
										'-1120-user.city' => TTi18n::gettext('City'),
										'-1130-user.province' => TTi18n::gettext('Province/State'),
										'-1140-user.country' => TTi18n::gettext('Country'),
                                        '-1150-user.postal_code' => TTi18n::gettext('Postal Code'),
										'-1160-user.work_phone' => TTi18n::gettext('Work Phone'),
										'-1170-user.work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
										'-1180-user.home_phone' => TTi18n::gettext('Home Phone'),
										'-1190-user.mobile_phone' => TTi18n::gettext('Mobile Phone'),
										'-1200-user.fax_phone' => TTi18n::gettext('Fax Phone'),
										'-1210-user.home_email' => TTi18n::gettext('Home Email'),
										'-1220-user.work_email' => TTi18n::gettext('Work Email'),
                                        '-1230-user.address1' => TTi18n::gettext('Address 1'),
										'-1240-user.address2' => TTi18n::gettext('Address 2'),
                                        '-1244-user.tag' => TTi18n::getText('Employee Tags'),

                                        '-1250-user_review_control.reviewer_user' => TTi18n::gettext('Reviewer Name'),
                                        '-1260-user_review_control.status' => TTi18n::gettext('Review Status'),
                                        '-1270-user_review_control.type' => TTi18n::gettext('Review Type'),
                                        '-1280-user_review_control.term' => TTi18n::gettext('Review Terms'),
                                        '-1290-user_review_control.severity' => TTi18n::gettext('Review Severity/Importance'),
                                        '-1292-user_review_control.tag' => TTi18n::gettext('Review Tags'),

                                        '-1300-user_review.note' => TTi18n::gettext('KPI Notes'),
                                        '-1350-user.note' => TTi18n::gettext('Employee Notes'),
                                        '-1400-user_review_control.note' => TTi18n::gettext('Review Notes'),


							   );

				$retval = array_merge( $retval, $this->getOptions('date_columns'), (array)$this->getOptions('report_static_custom_column') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used
										'-2000-kpi.minimum_rate' => TTi18n::gettext('Minimum Rating'),
                                        '-2010-kpi.maximum_rate' => TTi18n::gettext('Maximum Rating'),
                                        '-2020-user_review.rating' => TTi18n::gettext('Rating'),

										'-2000-total_review' => TTi18n::gettext('Total Reviews'), //Group counter...
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
						if ( strpos($column, 'rate') !== FALSE) {
							$retval[$column] = 'numeric';
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
							case 'kpi.minimum_rate':
								$retval[$column] = 'min';
								break;
							case 'kpi.maximum_rate':
								$retval[$column] = 'max';
								break;
							case 'user_review.rating':
							case 'total_review':
								$retval[$column] = 'sum';
								break;
						}
					}
				}
				break;
			case 'templates':
				$retval = array(
										'-1010-by_employee_by_kpi+kpi+rating' => TTi18n::gettext('Review Information By Employee/KPI'),
										'-1020-by_kpi_by_employee+kpi+rating' => TTi18n::gettext('Review Information By KPI/Employee'),

										'-1040-by_type_by_terms_by_severity+kpi+rating' => TTi18n::gettext('Review Summary By Type/Terms/Severity'),
										'-1050-by_employee_by_type_by_terms_by_severity+kpi+rating' => TTi18n::gettext('Review Summary By Employee/Type/Terms/Severity'),
										'-1060-by_type_by_terms_by_severity_by_employee+kpi+rating' => TTi18n::gettext('Review Summary By Type/Terms/Severity/Employee'),

										'-1070-by_employee+due_date' => TTi18n::gettext('Pending Reviews By Employee'),
 							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
                        case 'by_employee_by_kpi+kpi+rating':
                            $retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.due-date_stamp';
                            $retval['columns'][] = 'kpi.name';
                            $retval['columns'][] = 'user_review.rating';
							$retval['columns'][] = 'user_review.note';

							$retval['-2150-user_review_control_status_id'] = array(30);
                            $retval['-2160-user_review_control_type_id'] = array(); //Allow use to easily filter based on review type

							$retval['sub_total'][] = 'user.full_name';
							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'user_review_control.due-date_stamp';

                            $retval['sort'][] = array('user.full_name' => 'asc');
							$retval['sort'][] = array('user_review_control.type' => 'asc');
							$retval['sort'][] = array('user_review_control.due-date_stamp' => 'desc');
                            $retval['sort'][] = array('user_review.rating' => 'desc');
                            $retval['sort'][] = array('kpi.name' => 'asc');
                            break;
                        case 'by_kpi_by_employee+kpi+rating':
							$retval['columns'][] = 'user_review_control.type';
                            $retval['columns'][] = 'kpi.name';
                            $retval['columns'][] = 'user.full_name';
                            $retval['columns'][] = 'user_review.rating';
							$retval['columns'][] = 'user_review.note';

							$retval['-2150-user_review_control_status_id'] = array(30);
                            $retval['-2160-user_review_control_type_id'] = array(); //Allow use to easily filter based on review type

							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'kpi.name';

							$retval['sort'][] = array('user_review_control.type' => 'asc');
							$retval['sort'][] = array('kpi.name' => 'asc');
							$retval['sort'][] = array('user_review.rating' => 'desc');
							$retval['sort'][] = array('user.full_name' => 'asc');
                            break;
                        case 'by_type_by_terms_by_severity+kpi+rating':
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.term';
							$retval['columns'][] = 'user_review_control.severity';
                            $retval['columns'][] = 'total_review';

							$retval['-2150-user_review_control_status_id'] = array(30);

							$retval['group'][] = 'user_review_control.type';
							$retval['group'][] = 'user_review_control.term';
							$retval['group'][] = 'user_review_control.severity';

							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'user_review_control.term';
							$retval['sub_total'][] = 'user_review_control.severity';

							$retval['sort'][] = array('user_review_control.type' => 'asc');
							$retval['sort'][] = array('user_review_control.term' => 'asc');
							$retval['sort'][] = array('user_review_control.severity' => 'desc');
                            break;
                        case 'by_employee_by_type_by_terms_by_severity+kpi+rating':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.term';
							$retval['columns'][] = 'user_review_control.severity';
                            $retval['columns'][] = 'total_review';
							$retval['columns'][] = 'user_review.rating';

							$retval['-2150-user_review_control_status_id'] = array(30);

							$retval['group'][] = 'user.full_name';
							$retval['group'][] = 'user_review_control.type';
							$retval['group'][] = 'user_review_control.term';
							$retval['group'][] = 'user_review_control.severity';

							$retval['sub_total'][] = 'user.full_name';
							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'user_review_control.term';
							$retval['sub_total'][] = 'user_review_control.severity';

							$retval['sort'][] = array('user.full_name' => 'asc');
							$retval['sort'][] = array('user_review_control.type' => 'asc');
							$retval['sort'][] = array('user_review_control.term' => 'asc');
							$retval['sort'][] = array('user_review_control.severity' => 'desc');
                            break;
                        case 'by_type_by_terms_by_severity_by_employee+kpi+rating':
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.term';
							$retval['columns'][] = 'user_review_control.severity';
							$retval['columns'][] = 'user.full_name';
                            $retval['columns'][] = 'total_review';
							$retval['columns'][] = 'user_review.rating';

							$retval['-2150-user_review_control_status_id'] = array(30);

							$retval['group'][] = 'user_review_control.type';
							$retval['group'][] = 'user_review_control.term';
							$retval['group'][] = 'user_review_control.severity';
							$retval['group'][] = 'user.full_name';

							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'user_review_control.term';
							$retval['sub_total'][] = 'user_review_control.severity';

							$retval['sort'][] = array('user_review_control.type' => 'asc');
							$retval['sort'][] = array('user_review_control.term' => 'asc');
							$retval['sort'][] = array('user_review_control.severity' => 'desc');
							$retval['sort'][] = array('total_review' => 'desc');
							$retval['sort'][] = array('user_review.rating' => 'desc');
                            break;
						case 'by_employee+due_date':
                            $retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.reviewer_user';
							$retval['columns'][] = 'user_review_control.due-date_stamp';

							$retval['-1010-time_period']['time_period'] = 'this_month';
							$retval['-2150-user_review_control_status_id'] = array(10);
                            $retval['-2160-user_review_control_type_id'] = array(); //Allow use to easily filter based on review type

							$retval['group'][] = 'user.full_name';
							$retval['group'][] = 'user_review_control.type';
							$retval['group'][] = 'user_review_control.reviewer_user';
							$retval['group'][] = 'user_review_control.due-date_stamp';

                            $retval['sort'][] = array('user_review_control.due-date_stamp' => 'asc');
							$retval['sort'][] = array('user_review_control.type_id' => 'asc');
							$retval['sort'][] = array('user.full_name' => 'asc');
							break;
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
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
		$this->tmp_data = array('user' => array(), 'kpi' => array(), 'user_review_control' => array(), 'user_review' => array() );

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();
        $columns['start_date'] = $columns['end_date'] = $columns['due_date'] = TRUE;
        //$columns['status'] = $columns['type'] = $columns['note'] = $columns['rating'] = TRUE;
        //Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
            $this->tmp_data['user'][$u_obj->getId()] = Misc::addKeyPrefix('user.', (array)$u_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user.', $columns ) ) );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		//Get KPI data for joining.
		$klf = TTnew( 'KPIListFactory' );
		$klf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' KPI Rows: '. $klf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $klf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $klf as $key => $k_obj ) {

			$this->tmp_data['kpi'][$k_obj->getId()] = Misc::addKeyPrefix('kpi.', (array)$k_obj->getObjectAsArray( Misc::removeKeyPrefix( 'kpi.', $columns ) ) );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		//Get user review control data for joining.
		$urclf = TTnew( 'UserReviewControlListFactory' );
		$urclf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Review Control Rows: '. $urclf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $urclf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $urclf as $key => $urc_obj ) {
            $this->tmp_data['user_review_control'][$urc_obj->getId()][$urc_obj->getUser()] = Misc::addKeyPrefix('user_review_control.', (array)$urc_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_review_control.', $columns ) ) );
			$this->tmp_data['user_review_control'][$urc_obj->getId()][$urc_obj->getUser()]['total_review'] = 1;

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		$urlf = TTnew( 'UserReviewListFactory' );
		$urlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Review Rows: '. $urlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $urlf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $urlf as $key => $ur_obj ) {
			$this->tmp_data['user_review'][$ur_obj->getUserReviewControl()][$ur_obj->getKPI()] = Misc::addKeyPrefix('user_review.', (array)$ur_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_review.', $columns ) ));
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		//Debug::Arr($this->tmp_data, ' TMP Rows: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {

		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['user_review_control']), NULL, TTi18n::getText('Pre-Processing Data...') );

		$key=0;
		if ( isset($this->tmp_data['user_review_control']) ) {
			foreach( $this->tmp_data['user_review_control'] as $user_review_control_id => $level_1 ) {

			     foreach( $level_1 as $user_id => $user_review_control  ) {
			        $processed_data = array();
                    if ( isset($user_review_control['user_review_control.start_date']) ) {
    					$start_date_columns = TTDate::getReportDates( 'user_review_control.start', TTDate::parseDateTime( $user_review_control['user_review_control.start_date'] ), FALSE, $this->getUserObject() );
    				} else {
    				    $start_date_columns = array();
    				}
    				if ( isset($user_review_control['user_review_control.end_date']) ) {
    					$end_date_columns = TTDate::getReportDates( 'user_review_control.end', TTDate::parseDateTime( $user_review_control['user_review_control.end_date'] ), FALSE, $this->getUserObject() );
    				} else {
    				    $end_date_columns = array();
    				}
    				if ( isset($user_review_control['user_review_control.due_date']) ) {
    					$due_date_columns = TTDate::getReportDates( 'user_review_control.due', TTDate::parseDateTime( $user_review_control['user_review_control.due_date'] ), FALSE, $this->getUserObject() );
    				} else {
    				    $due_date_columns = array();
    				}
                    if ( isset($this->tmp_data['user'][$user_id]) AND is_array($this->tmp_data['user'][$user_id]) ) {
                        $processed_data = array_merge( $processed_data, $this->tmp_data['user'][$user_id]);
                    }
                    if ( isset( $this->tmp_data['user_review'][$user_review_control_id] ) ) {
                        foreach( $this->tmp_data['user_review'][$user_review_control_id] as $kpi_id => $kpi ) {
                                if ( is_array( $kpi ) ) {
                                    $processed_data = array_merge( $processed_data, $kpi );
                                }
                                if ( is_array( $user_review_control ) ) {
                                    $processed_data = array_merge( $processed_data, $user_review_control );
                                }
                                if ( isset( $this->tmp_data['kpi'][$kpi_id] ) AND is_array( $this->tmp_data['kpi'][$kpi_id] ) ) {
                                    $processed_data = array_merge( $processed_data, $this->tmp_data['kpi'][$kpi_id] );
                                }

                                $this->data[] = array_merge( $start_date_columns, $end_date_columns, $due_date_columns, $processed_data );

                        }
                    }

                 }
				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
				$key++;
			}
			unset($this->tmp_data, $kpi,  $user_review_control, $start_date_columns, $end_date_columns, $due_date_columns, $processed_data );
		}
		Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);
		return TRUE;
	}
}
?>
