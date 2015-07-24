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
class Form941Report extends Report {

	protected $user_ids = array();

	function __construct() {
		$this->title = TTi18n::getText('Form 941 Report');
		$this->file_name = 'form_941';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report', 'enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report', 'view_form941', $user_id, $company_id ) ) {
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
										'-1100-pdf_form' => TTi18n::gettext('Form'),
										//'-1120-efile' => TTi18n::gettext('eFile'),
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
				$retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 13, TRUE );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'Form941Report', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'Form941Report', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'Form941Report', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'Form941Report', 'custom_column' );
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
										'-2010-wages' => TTi18n::gettext('Wages'), //Line 2
										'-2020-income_tax' => TTi18n::gettext('Income Tax'), //Line 3
										'-2030-social_security_wages' => TTi18n::gettext('Taxable Social Security Wages'), //Line 5a
										'-2040-social_security_tips' => TTi18n::gettext('Taxable Social Security Tips'), //Line 5b
										'-2050-medicare_wages' => TTi18n::gettext('Taxable Medicare Wages'), //Line 5c
										'-2060-sick_wages' => TTi18n::gettext('Sick Pay'), //Line 7b
										'-2070-eic' => TTi18n::gettext('Earned Income Credit (EIC)'), //Line 9
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
										'-1010-by_month' => TTi18n::gettext('by Month'),
										'-1020-by_employee' => TTi18n::gettext('by Employee'),
										'-1030-by_branch' => TTi18n::gettext('by Branch'),
										'-1040-by_department' => TTi18n::gettext('by Department'),
										'-1050-by_branch_by_department' => TTi18n::gettext('by Branch/Department'),

										'-1060-by_month_by_employee' => TTi18n::gettext('by Month/Employee'),
										'-1070-by_month_by_branch' => TTi18n::gettext('by Month/Branch'),
										'-1080-by_month_by_department' => TTi18n::gettext('by Month/Department'),
										'-1090-by_month_by_branch_by_department' => TTi18n::gettext('by Month/Branch/Department'),
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
							$retval['-1010-time_period']['time_period'] = 'last_quarter';

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

	function getF941Object() {
		if ( !isset($this->form_obj['f941']) OR !is_object($this->form_obj['f941']) ) {
			$this->form_obj['f941'] = $this->getFormObject()->getFormObject( '941', 'US' );
			return $this->form_obj['f941'];
		}

		return $this->form_obj['f941'];
	}

	function getRETURN941Object() {
		if ( !isset($this->form_obj['return941']) OR !is_object($this->form_obj['return941']) ) {
			$this->form_obj['return941'] = $this->getFormObject()->getFormObject( 'RETURN941', 'US' );
			return $this->form_obj['return941'];
		}

		return $this->form_obj['return941'];
	}

	function formatFormConfig() {
		$default_include_exclude_arr = array( 'include_pay_stub_entry_account' => array(), 'exclude_pay_stub_entry_account' => array() );

		$default_arr = array(
				'wages' => $default_include_exclude_arr,
				'income_tax' => $default_include_exclude_arr,
				'social_security_wages' => $default_include_exclude_arr,
				'social_security_tips' => $default_include_exclude_arr,
				'medicare_wages' => $default_include_exclude_arr,
				'sick_wages' => $default_include_exclude_arr,
				'eic' => $default_include_exclude_arr,
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

		require_once( Environment::getBasePath().'/classes/payroll_deduction/PayrollDeduction.class.php');
		$pd_obj = new PayrollDeduction('US', 'WA'); //State doesn't matter.
		$pd_obj->setDate($filter_data['end_date']);

		$social_security_wage_limit = $pd_obj->getSocialSecurityMaximumEarnings();
		$medicare_additional_threshold_limit = $pd_obj->getMedicareAdditionalEmployerThreshold();
		Debug::Text('Social Security Wage Limit: '. $social_security_wage_limit .' Medicare Threshold: '. $medicare_additional_threshold_limit .' Date: '. TTDate::getDate('DATE', $filter_data['end_date'] ), __FILE__, __LINE__, __METHOD__, 10);

		//Need to get totals up to the beginning of this quarter so we can determine if any employees have exceeded the social security limit.
		$pself = TTnew( 'PayStubEntryListFactory' );
		$ytd_filter_data = $filter_data;
		$ytd_filter_data['end_date'] = ( $ytd_filter_data['start_date'] - 1 );
		$ytd_filter_data['start_date'] = TTDate::getBeginYearEpoch( $ytd_filter_data['start_date'] );
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $ytd_filter_data );
		//Debug::Arr($ytd_filter_data, 'YTD Filter Data: Row Count: '.	$pself->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $pse_obj ) {
				$user_id = $pse_obj->getColumn('user_id'); //Make sure we don't add this to the unique user_id list.
				//Always use middle day epoch, otherwise multiple entries could exist for the same day.
				$date_stamp = TTDate::getMiddleDayEpoch( TTDate::strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ) );
				$branch = $pse_obj->getColumn('default_branch');
				$department = $pse_obj->getColumn('default_department');
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = array(
																'pay_period_start_date' => strtotime( $pse_obj->getColumn('pay_stub_start_date') ),
																'pay_period_end_date' => strtotime( $pse_obj->getColumn('pay_stub_end_date') ),
																'pay_period_transaction_date' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
																'pay_period' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
															);
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
						if ( !isset($this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages']) ) {
							$this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages'] = 0;
						}
						$this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages']	+= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['social_security_wages']['include_pay_stub_entry_account'], $form_data['social_security_wages']['exclude_pay_stub_entry_account'] );
						//Include tips in this amount as well.
						$this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages']	+= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['social_security_tips']['include_pay_stub_entry_account'], $form_data['social_security_tips']['exclude_pay_stub_entry_account'] );

						//Handle additional medicare wages in excess of 200, 000
						if ( !isset($this->tmp_data['ytd_pay_stub_entry'][$user_id]['medicare_wages']) ) {
							$this->tmp_data['ytd_pay_stub_entry'][$user_id]['medicare_wages'] = 0;
						}
						$this->tmp_data['ytd_pay_stub_entry'][$user_id]['medicare_wages']	+= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['medicare_wages']['include_pay_stub_entry_account'], $form_data['medicare_wages']['exclude_pay_stub_entry_account'] );
					}
				}
			}

			//Debug::Arr($this->tmp_data['ytd_pay_stub_entry'], 'YTD Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		}
		unset($pse_obj, $user_id, $date_stamp, $branch, $department, $pay_stub_entry_name_id, $this->tmp_data['pay_stub_entry']);



		//Get just the data for the quarter now.
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $pse_obj ) {
				$user_id = $this->user_ids[] = $pse_obj->getColumn('user_id');
				//Always use middle day epoch, otherwise multiple entries could exist for the same day.
				$date_stamp = TTDate::getMiddleDayEpoch( TTDate::strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ) );
				$branch = $pse_obj->getColumn('default_branch');
				$department = $pse_obj->getColumn('default_department');
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = array(
																'pay_period_start_date' => strtotime( $pse_obj->getColumn('pay_stub_start_date') ),
																'pay_period_end_date' => strtotime( $pse_obj->getColumn('pay_stub_end_date') ),
																'pay_period_transaction_date' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
																'pay_period' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
															);
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
						$quarter_month = TTDate::getYearQuarterMonthNumber( $date_stamp );
						//Debug::Text('Quarter Month: '. $quarter_month .' Epoch: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['wages']					= ( isset($form_data['wages']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['wages']['include_pay_stub_entry_account'], $form_data['wages']['exclude_pay_stub_entry_account'] ) : 0;
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['income_tax']				= ( isset($form_data['income_tax']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['income_tax']['include_pay_stub_entry_account'], $form_data['income_tax']['exclude_pay_stub_entry_account'] ) : 0;

						//Because employees can be excluded from Social Security/Medicare, only include wage amounts if the SS tax is not 0.
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tax']	= ( isset($form_data['social_security_tax']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['social_security_tax']['include_pay_stub_entry_account'], $form_data['social_security_tax']['exclude_pay_stub_entry_account'] ) : 0;
						if ( ( isset($form_data['social_security_tax']['include_pay_stub_entry_account']) AND !is_array($form_data['social_security_tax']['include_pay_stub_entry_account']) ) OR $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tax'] != 0 ) {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'] = ( isset($form_data['social_security_wages']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['social_security_wages']['include_pay_stub_entry_account'], $form_data['social_security_wages']['exclude_pay_stub_entry_account'] ) : 0;
						} else {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'] = 0;
						}

						//Handle social security wage limit.
						if ( !isset($this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages']) ) {
							$this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages'] = 0;
						}
						if ( $this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages'] < $social_security_wage_limit ) {
							if ( ( isset($form_data['social_security_tax']['include_pay_stub_entry_account']) AND !is_array($form_data['social_security_tax']['include_pay_stub_entry_account']) ) OR $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tax'] != 0 ) {
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages']	= ( isset($form_data['social_security_wages']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['social_security_wages']['include_pay_stub_entry_account'], $form_data['social_security_wages']['exclude_pay_stub_entry_account'] ) : 0;
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips']	= ( isset($form_data['social_security_tips']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['social_security_tips']['include_pay_stub_entry_account'], $form_data['social_security_tips']['exclude_pay_stub_entry_account'] ) : 0;
							} else {
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips'] = 0;
							}
							if ( ($this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages'] + $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'] + $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips']) > $social_security_wage_limit ) {
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'] = ( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'] - (($this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages'] + $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'] + $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips']) - $social_security_wage_limit) );
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips'] = 0;
								$this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages'] = $social_security_wage_limit;
							} else {
								$this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'];
								$this->tmp_data['ytd_pay_stub_entry'][$user_id]['social_security_wages'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips'];
							}
						} else {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'] = 0;
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips'] = 0;
						}

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_tax'] = ( isset($form_data['medicare_tax']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['medicare_tax']['include_pay_stub_entry_account'], $form_data['medicare_tax']['exclude_pay_stub_entry_account'] ) : 0;
						if ( ( isset($form_data['medicare_tax']['include_pay_stub_entry_account']) AND !is_array($form_data['medicare_tax']['include_pay_stub_entry_account']) ) OR $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_tax'] != 0 ) {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_wages'] = ( isset($form_data['medicare_wages']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['medicare_wages']['include_pay_stub_entry_account'], $form_data['medicare_wages']['exclude_pay_stub_entry_account'] ) : 0;
						} else {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_wages'] = 0;
						}
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_additional_wages'] = 0;

						//Handle medicare additional wage limit, only consider wages earned above the threshold to be "medicare_additional_wages"
						if ( !isset($this->tmp_data['ytd_pay_stub_entry'][$user_id]['medicare_wages']) ) {
							$this->tmp_data['ytd_pay_stub_entry'][$user_id]['medicare_wages'] = 0;
						}
						if ( $this->tmp_data['ytd_pay_stub_entry'][$user_id]['medicare_wages'] > $medicare_additional_threshold_limit ) {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_additional_wages'] = $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_wages'];
						} else {
							if ( ( ( isset($form_data['medicare_tax']['include_pay_stub_entry_account']) AND !is_array($form_data['medicare_tax']['include_pay_stub_entry_account']) ) OR $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_tax'] != 0 )
									AND ($this->tmp_data['ytd_pay_stub_entry'][$user_id]['medicare_wages'] + $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_wages']) > $medicare_additional_threshold_limit	) {
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_additional_wages'] = ( ($this->tmp_data['ytd_pay_stub_entry'][$user_id]['medicare_wages'] + $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_wages']) - $medicare_additional_threshold_limit );
							} else {
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_additional_wages'] = 0;
							}
						}
						//Debug::Text('User ID: '. $user_id .' DateStamp: '. $date_stamp .' YTD Medicare Additional Wages: '. $this->tmp_data['ytd_pay_stub_entry'][$user_id]['medicare_wages'] .' This Pay Stub: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_additional_wages'], __FILE__, __LINE__, __METHOD__, 10);

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['sick_wages']				= ( isset($form_data['sick_wages']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['sick_wages']['include_pay_stub_entry_account'], $form_data['sick_wages']['exclude_pay_stub_entry_account'] ) : 0;
						//$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['eic']						= ( isset($form_data['eic']) ) ? Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['eic']['include_pay_stub_entry_account'], $form_data['eic']['exclude_pay_stub_entry_account'] ) : 0;

						//Separate data used for reporting, grouping, sorting, from data specific used for the Form.
						if ( !isset($this->form_data['pay_period'][$quarter_month][$date_stamp]) ) {
							$this->form_data['pay_period'][$quarter_month][$date_stamp] = Misc::preSetArrayValues( array(), array('l2', 'l3', 'l5a', 'l5b', 'l5c', 'l5d', 'l7', 'l9', 'l5a2', 'l5b2', 'l5c2', 'l5d', 'l8', 'l10' ), 0 );
						}
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l2'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['wages'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l3'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['income_tax'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5a'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5b'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5c'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_wages'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5d'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_additional_wages'];
						//$this->form_data['pay_period'][$quarter_month][$date_stamp]['l9'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['eic'];

						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5f'] = 0; //Not implemented currently.

						//Calculated fields, make sure we don't use += on these.
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5a2'] = bcmul( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5a'], $this->getF941Object()->social_security_rate );
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5b2'] = bcmul( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5b'], $this->getF941Object()->social_security_rate );
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5c2'] = bcmul( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5c'], $this->getF941Object()->medicare_rate );
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5d2'] = bcmul( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5d'], $this->getF941Object()->medicare_additional_rate );

						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5e'] = bcadd( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5a2'], bcadd( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5b2'], bcadd( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5c2'], $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5d2']) ) );

						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l6'] = bcadd( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l3'], bcadd( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5e'], $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5f'] ) );

						//Calculate amounts for Schedule B.
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l10'] = $this->form_data['pay_period'][$quarter_month][$date_stamp]['l6']; //Add L6 -> L9 if they are implemented later.
					}
				}

				//Total all pay periods by month_id
				if ( isset($this->form_data['pay_period']) ) {
					foreach( $this->form_data['pay_period'] as $month_id => $pp_data ) {
						$this->form_data['quarter'][$month_id] = Misc::ArrayAssocSum($pp_data, NULL, 8);
					}

					//Total all quarters.
					if ( isset($this->form_data['quarter']) ) {
						$this->form_data['total'] = Misc::ArrayAssocSum( $this->form_data['quarter'], NULL, 6);
					}
				}

			}
		}

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

		//Debug::Arr($this->user_ids, 'User IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($this->form_data, 'Form Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
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
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		if ( isset( $this->tmp_data['pay_stub_entry'] ) == FALSE ) {
			return TRUE;
		}
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key = 0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {
			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				foreach( $level_1 as $date_stamp => $row ) {
					$date_columns = TTDate::getReportDates( NULL, $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
					$processed_data	 = array(
											//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
											);

					$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function _outputPDFForm( $format = NULL ) {
		$show_background = TRUE;
		if ( $format == 'pdf_form_print' ) {
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

		if ( $format == 'efile_xml' ) {
			$return941 = $this->getRETURN941Object();

			$return941->TaxPeriodEndDate = TTDate::getDate('Y-m-d', TTDate::getEndDayEpoch( $filter_data['end_date'] ));
			$return941->ReturnType = '';
			$return941->ein = ( isset($setup_data['ein']) AND $setup_data['ein'] != '' ) ? $setup_data['ein'] : $current_company->getBusinessNumber();
			$return941->BusinessName1 = '';
			$return941->BusinessNameControl = '';

			$return941->AddressLine = ( isset($setup_data['address1']) AND $setup_data['address1'] != '' ) ? $setup_data['address1'] : $current_company->getAddress1() .' '. $current_company->getAddress2();
			$return941->City = ( isset($setup_data['city']) AND $setup_data['city'] != '' ) ? $setup_data['city'] : $current_company->getCity();
			$return941->State = ( isset($setup_data['province']) AND ( $setup_data['province'] != '' AND $setup_data['province'] != 0 ) ) ? $setup_data['province'] : $current_company->getProvince();
			$return941->ZIPCode = ( isset($setup_data['postal_code']) AND $setup_data['postal_code'] != '' ) ? $setup_data['postal_code'] : $current_company->getPostalCode();

			$this->getFormObject()->addForm( $return941 );
		}

		$f941 = $this->getF941Object();
		$f941->setDebug(FALSE);
		$f941->setShowBackground( $show_background );

		$f941->year = TTDate::getYear( $filter_data['end_date'] );

		//Add support for the user to manually set this data in the setup_data. That way they can use multiple tax IDs for different employees, all beit manually.
		$f941->ein = ( isset($setup_data['ein']) AND $setup_data['ein'] != '' ) ? $setup_data['ein'] : $current_company->getBusinessNumber();
		$f941->name = ( isset($setup_data['name']) AND $setup_data['name'] != '' ) ? $setup_data['name'] : $this->getUserObject()->getFullName();
		$f941->trade_name = ( isset($setup_data['company_name']) AND $setup_data['company_name'] != '' ) ? $setup_data['company_name'] : $current_company->getName();
		$f941->address = ( isset($setup_data['address1']) AND $setup_data['address1'] != '' ) ? $setup_data['address1'] : $current_company->getAddress1() .' '. $current_company->getAddress2();
		$f941->city = ( isset($setup_data['city']) AND $setup_data['city'] != '' ) ? $setup_data['city'] : $current_company->getCity();
		$f941->state = ( isset($setup_data['province']) AND ( $setup_data['province'] != '' AND $setup_data['province'] != 0 ) ) ? $setup_data['province'] : $current_company->getProvince();
		$f941->zip_code = ( isset($setup_data['postal_code']) AND $setup_data['postal_code'] != '' ) ? $setup_data['postal_code'] : $current_company->getPostalCode();

		$f941->quarter = TTDate::getYearQuarter( $filter_data['end_date'] );

		//Debug::Arr($this->form_data, 'Final Data for Form: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( isset($this->form_data) AND count($this->form_data) == 3 ) {

			$f941->l1 = count($this->user_ids);
			$f941->l2 = $this->form_data['total']['l2'];
			$f941->l3 = $this->form_data['total']['l3'];

			$f941->l5a = $this->form_data['total']['l5a'];
			$f941->l5b = $this->form_data['total']['l5b'];
			$f941->l5c = $this->form_data['total']['l5c'];
			$f941->l5d = $this->form_data['total']['l5d'];

			if ( isset($setup_data['quarter_deposit']) AND $setup_data['quarter_deposit'] != ''	 ) {
				$f941->l11 = Misc::MoneyFormat($setup_data['quarter_deposit'], FALSE);
			}
			//Debug::Text('L11: '. $f941->l11 .' L6: '. $f941->calcL6() .' - '. $this->form_data['total']['l10'], __FILE__, __LINE__, __METHOD__, 10);

			$f941->l13b = TRUE;

			if ( isset($setup_data['deposit_schedule']) AND $setup_data['deposit_schedule'] == 10 ) {
				if ( isset($this->form_data['quarter'][1]['l10']) ) {
					$f941->l16_month1 = $this->form_data['quarter'][1]['l10'];
				}
				if ( isset($this->form_data['quarter'][2]['l10']) ) {
					$f941->l16_month2 = $this->form_data['quarter'][2]['l10'];
				}
				if ( isset($this->form_data['quarter'][3]['l10']) ) {
					$f941->l16_month3 = $this->form_data['quarter'][3]['l10'];
				}
			} elseif ( isset($setup_data['deposit_schedule']) AND $setup_data['deposit_schedule'] == 20 ) {
				$f941sb = $this->getFormObject()->getFormObject( '941sb', 'US' );
				$f941sb->setShowBackground( $show_background );

				$f941sb->year = $f941->year;
				$f941sb->ein = $f941->ein;
				$f941sb->name = $f941->name;
				$f941sb->quarter = $f941->quarter;

				for( $i = 1; $i <= 3; $i++ ) {
					if ( isset($this->form_data['pay_period'][$i]) ) {
						foreach( $this->form_data['pay_period'][$i] as $pay_period_epoch => $data ) {
							//Debug::Text('SB: Month: '. $i .' Pay Period Date: '. TTDate::getDate('DATE', $pay_period_epoch) .' DOM: '. TTDate::getDayOfMonth($pay_period_epoch) .' Amount: '. $data['l10'], __FILE__, __LINE__, __METHOD__, 10);
							$f941sb_data[$i][TTDate::getDayOfMonth($pay_period_epoch)] = $data['l10']; //Don't round this as it can cause mismatches in the totals.
						}
					}
				}

				if ( isset($f941sb_data[1]) ) {
					$f941sb->month1 = $f941sb_data[1];
				}
				if ( isset($f941sb_data[2]) ) {
					$f941sb->month2 = $f941sb_data[2];
				}
				if ( isset($f941sb_data[3]) ) {
					$f941sb->month3 = $f941sb_data[3];
				}
				unset($i, $d, $f941sb_data);
			}
		} else {
			Debug::Arr($this->data, 'Invalid Form Data: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		$this->getFormObject()->addForm( $f941 );

		if ( isset($f941sb) AND is_object( $f941sb ) ) {
			$this->getFormObject()->addForm( $f941sb );
		}

		if ( $format == 'efile_xml' ) {
			$output_format = 'XML';
			$file_name = '941_efile_'.date('Y_m_d').'.xml';
			$mime_type = 'applications/octet-stream'; //Force file to download.
		} else {
			$output_format = 'PDF';
			$file_name = $this->file_name.'.pdf';
			$mime_type = $this->file_mime_type;
		}

		$output = $this->getFormObject()->output( $output_format );

		return $output;
	}

	function _output( $format = NULL ) {
		if ( $format == 'pdf_form' OR $format == 'pdf_form_print' OR $format == 'efile_xml' ) {
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
