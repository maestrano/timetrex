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
class ROEReport extends Report {

	protected $user_ids = array();

	function __construct() {
		$this->title = TTi18n::getText('ROE Report');
		$this->file_name = 'roe';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {

		if ( $this->getPermissionObject()->Check('report', 'enabled', $user_id, $company_id )
				//AND $this->getPermissionObject()->Check('report', 'view_roe', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('roe', 'view', $user_id, $company_id )
			) {
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
										'-1100-pdf_form' => TTi18n::gettext('Form'),
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

										'-2080-code_id' => TTi18n::gettext('Reason'),
										'-2090-pay_period_type_id' => TTi18n::gettext('Pay Period Type'),

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
										TTDate::getReportDateOptions( 'first', TTi18n::gettext('First Day Worked(Or first day since last ROE)'), 16, FALSE ),
										TTDate::getReportDateOptions( 'last', TTi18n::gettext('Last Day For Which Paid'), 16, FALSE ),
										TTDate::getReportDateOptions( 'pay_period_end', TTi18n::gettext('Final Pay Period Ending Date'), 17, FALSE ),
										TTDate::getReportDateOptions( 'recall', TTi18n::gettext('Expected Date of Recall'), 17, FALSE )
				);
				$retval = array();
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'ROEReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'ROEReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'ROEReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'ROEReport', 'custom_column' );
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

										'-1120-code' => TTi18n::gettext('Reason'),
										'-1130-pay_period_type' => TTi18n::gettext('Pay Period Type'),
										//'-1140-first_date' => TTi18n::gettext('First Day Worked(Or first day since last ROE)'),
										//'-1150-last_date' => TTi18n::gettext('Last Day For Which Paid'),
										//'-1160-pay_period_end_date' => TTi18n::gettext('Final Pay Period Ending Date'),
										//'-1170-recall_date' => TTi18n::gettext('Expected Date of Recall'),
										'-1180-serial' => TTi18n::gettext('Serial No'),
										'-1190-comments' => TTi18n::gettext('Comments'),

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
										'-2100-insurable_earnings' => TTi18n::gettext('Insurable Earnings (Box 15B)'),
										'-2110-vacation_pay' => TTi18n::gettext('Vacation Pay (Box 17A)'),

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
						$retval[$column] = 'numeric';
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

	function getROEObject() {
		if ( !isset($this->form_obj['roe']) OR !is_object($this->form_obj['roe']) ) {
			$this->form_obj['roe'] = $this->getFormObject()->getFormObject( 'ROE', 'CA' );
			return $this->form_obj['roe'];
		}

		return $this->form_obj['roe'];
	}

	function formatFormConfig() {
		$retarr = (array)$this->getFormConfig();
		return $retarr;
	}

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array( 'user' => array(), 'roe' => array() );

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

		//Debug::Arr($this->user_ids, 'User IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->form_data, 'Form Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray(); //Don't pass $this->getColumnDataConfig() here as no columns are sent from Flex so it breaks the report.
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get ROE data for joining
		$rlf = TTnew( 'ROEListFactory' );
		$rlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' ROE Total Rows: '. $rlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $rlf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach( $rlf as $key => $r_obj ) {
			$this->tmp_data['roe'][$r_obj->getUser()] = (array)$r_obj->getObjectAsArray(); //Don't pass $this->getColumnDataConfig() here as no columns are sent from Flex so it breaks the report.
			if ( $r_obj->isPayPeriodWithNoEarnings() == TRUE ) {
				$this->tmp_data['roe'][$r_obj->getUser()]['pay_period_earnings'] = $r_obj->getInsurableEarningsByPayPeriod( '15c' );
			}
			//Box 17A, Vacation pay in last pay period
			$vacation_pay = $r_obj->getLastPayPeriodVacationEarnings();
			if ( $vacation_pay > 0 ) {
				$this->tmp_data['roe'][$r_obj->getUser()]['vacation_pay'] = $vacation_pay;
			}

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['roe'], 'ROE Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['roe']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key = 0;
		if ( isset($this->tmp_data['roe']) ) {
			foreach( $this->tmp_data['roe'] as $user_id => $row ) {

				$process_data = array();
				if ( isset($row['first_date']) ) {
					$first_date_columns = TTDate::getReportDates( 'first', TTDate::parseDateTime( $row['first_date'] ), FALSE, $this->getUserObject() );
				} else {
					$first_date_columns = array();
				}

				if ( isset($row['last_date']) ) {
					$last_date_columns = TTDate::getReportDates( 'last', TTDate::parseDateTime( $row['last_date'] ), FALSE, $this->getUserObject() );
				} else {
					$last_date_columns = array();
				}

				if ( isset($row['pay_period_end_date']) ) {
					$pay_period_end_date_columns = TTDate::getReportDates( 'pay_period_end', TTDate::parseDateTime( $row['pay_period_end_date'] ), FALSE, $this->getUserObject() );
				} else {
					$pay_period_end_date_columns = array();
				}

				if ( isset($row['recall_date']) ) {
					$recall_date_columns = TTDate::getReportDates( 'recall', TTDate::parseDateTime( $row['recall_date'] ), FALSE, $this->getUserObject() );
				} else {
					$recall_date_columns = array();
				}


				if ( isset($this->tmp_data['user'][$user_id]) ) {

					if ( is_array( $this->tmp_data['user'][$user_id] ) ) {
						$process_data = array_merge( $process_data, $this->tmp_data['user'][$user_id] );
					}
					if ( is_array( $row ) ) {
						$process_data = array_merge( $process_data, $row );
					}
					$this->data[] = array_merge( $process_data, $first_date_columns, $last_date_columns, $pay_period_end_date_columns, $recall_date_columns );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}
			}
			unset($this->tmp_data, $row, $first_date_columns, $last_date_columns, $pay_period_end_date_columns, $recall_date_columns, $process_data);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$this->form_data = $this->data; //Copy data to Form Data so group/sort doesn't affect it.

		return TRUE;
	}

	function _outputPDFForm( $format = NULL ) {
		// Always display the background.
		$show_background = TRUE;
		Debug::Text('Generating Form... Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);

		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($setup_data, 'Setup Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//$last_row = count($this->form_data)-1;
		//$total_row = $last_row+1;

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object($current_company) ) {
			Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$roe = $this->getROEObject();
		$roe->setShowBackground( $show_background );
		//$roe->setDebug( TRUE );
		//$roe->setType( $form_type );
		$roe->business_number = $current_company->getBusinessNumber();
		$roe->company_name = $current_company->getName();
		$roe->company_address1 = $current_company->getAddress1();
		$roe->company_address2 = $current_company->getAddress2();
		$roe->company_city = $current_company->getCity();
		$roe->company_province = $current_company->getProvince();
		$roe->company_postal_code = $current_company->getPostalCode();
		$roe->company_work_phone = $current_company->getWorkPhone();
		$roe->english = TRUE;

		$i = 0;
		foreach($this->form_data as $row) {
			if ( !isset($row['user_id']) ) {
				Debug::Text('User ID not set!', __FILE__, __LINE__, __METHOD__, 10);
				continue;
			}

			$ulf = TTnew( 'UserListFactory' );
			$ulf->getById( (int)$row['user_id'] );
			if ( $ulf->getRecordCount() == 1 ) {
				$user_obj = $ulf->getCurrent();

				$title_obj = $user_obj->getTitleObject();

				$ee_data = array(
							'first_name' => $user_obj->getFirstName(),
							'middle_name' => $user_obj->getMiddleName(),
							'last_name' => $user_obj->getLastName(),
							'employee_full_name' => $user_obj->getFullName(FALSE),
							'employee_address1' => $user_obj->getAddress1(),
							'employee_address2' => $user_obj->getAddress2(),
							'employee_city' => $user_obj->getCity(),
							'employee_province' => $user_obj->getProvince(),
							'employee_postal_code' => $user_obj->getPostalCode(),
							'title' => ( is_object( $title_obj ) ) ?  $title_obj->getName() : NULL,
							'sin' => $user_obj->getSIN(),

							'pay_period_type' => $row['pay_period_type'],
							'pay_period_type_id' => $row['pay_period_type_id'],
							'code_id' => $row['code_id'],
							'first_date' => TTDate::parseDateTime( $row['first_date'] ),
							'last_date' => TTDate::parseDateTime( $row['last_date'] ),
							'pay_period_end_date' => TTDate::parseDateTime( $row['pay_period_end_date'] ),
							'recall_date' => TTDate::parseDateTime( $row['recall_date'] ),
							'insurable_hours' => $row['insurable_hours'],
							'insurable_earnings' => $row['insurable_earnings'],
							'vacation_pay' => $row['vacation_pay'],
							'serial' => $row['serial'],
							'comments' => $row['comments'],
							'created_date' => TTDate::parseDateTime( $row['created_date'] ),
							);

			}

			$ulf->getById( (int)$row['created_by_id'] );
			if ( $ulf->getRecordCount() == 1 ) {
				$user_obj = $ulf->getCurrent();

				$ee_data['created_user_first_name'] = $user_obj->getFirstName();
				$ee_data['created_user_middle_name'] = $user_obj->getMiddleName();
				$ee_data['created_user_last_name'] = $user_obj->getLastName();
				$ee_data['created_user_full_name'] = $user_obj->getFullName(FALSE);
				$ee_data['created_user_work_phone'] = $user_obj->getWorkPhone();
			}

			if ( isset( $row['pay_period_earnings'] ) AND is_array( $row['pay_period_earnings'] ) ) {
				foreach( $row['pay_period_earnings'] as $pay_period_earning ) {
					$ee_data['pay_period_earnings'][] = Misc::MoneyFormat( $pay_period_earning['amount'], FALSE );
				}
			}

			$roe->addRecord( $ee_data );
			unset($ee_data);

			$i++;

		}

		$this->getFormObject()->addForm( $roe );

		if ( $format == 'efile_xml' ) {
			$output_format = 'XML';
			$file_name = 'roe_efile_'.date('Y_m_d').'.blk'; //The filename should actually end in ".blk" instead of ".xml"
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
			Debug::Text('Skipping postProcess! Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);
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
