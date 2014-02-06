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
class TaxSummaryReport extends Report {

	function __construct() {
		$this->title = TTi18n::getText('Tax Summary Report');
		$this->file_name = 'tax_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_generic_tax_summary', $user_id, $company_id ) ) {
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
                                        '-2000-company_deduction_id' => TTi18n::gettext('Tax'),
										'-2010-user_status_id' => TTi18n::gettext('Employee Status'),
										'-2020-user_group_id' => TTi18n::gettext('Employee Group'),
										'-2030-user_title_id' => TTi18n::gettext('Employee Title'),
										'-2035-user_tag' => TTi18n::gettext('Employee Tags'),
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
				$retval = TTDate::getReportDateOptions( 'transaction', TTi18n::getText('Transaction Date'), 13, TRUE );
				break;
            case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'TaxSummaryReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'TaxSummaryReport', 'custom_column' );
				}
                break;
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'TaxSummaryReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'TaxSummaryReport', 'custom_column' );
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
										//'-1250-pay_period' => TTi18n::gettext('Pay Period'),

										'-1280-sin' => TTi18n::gettext('SIN/SSN'),
										'-1290-note' => TTi18n::gettext('Note'),
										'-1295-tag' => TTi18n::gettext('Tags'),

							   );

				$retval = array_merge( $retval, $this->getOptions('date_columns'), (array)$this->getOptions('report_static_custom_column') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used

										//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
										//'-2010-hourly_rate' => TTi18n::gettext('Hourly Rate'),
                                        '-1430-subject_wages' => TTi18n::gettext('Subject Wages'),
										'-1440-taxable_wages' => TTi18n::gettext('Taxable Wages'),
										'-1450-tax_withheld' => TTi18n::gettext('Tax Withheld'),

							);

				$retval = array_merge( $retval, $this->getOptions('pay_stub_account_amount_columns') );
				ksort($retval);

				break;
			case 'pay_stub_account_amount_columns':
				//Get all pay stub accounts
				$retval = array();

				$psealf = TTnew( 'PayStubEntryAccountListFactory' );
				$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(10,20,30,40,50,60,65) );
				if ( $psealf->getRecordCount() > 0 ) {
					$type_options  = $psealf->getOptions('type');
					foreach( $type_options as $key => $val ) {
						$type_options[$key] = str_replace( array('Employee', 'Employer', 'Deduction', 'Total'), array('EE', 'ER', 'Ded', ''), $val);
					}

					$i=0;
					foreach( $psealf as $psea_obj ) {
						//Need to make the PSEA_ID a string so we can array_merge it properly later.
						if ( $psea_obj->getType() == 40 ) { //Total accounts.
							$prefix = NULL;
						} else {
							$prefix = $type_options[$psea_obj->getType()] .' - ';
						}

						$retval['-3'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-PA'.$psea_obj->getID()] = $prefix.$psea_obj->getName();

						if ( $psea_obj->getType() == 10 ) { //Earnings only can see units.
							$retval['-4'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-PR'.$psea_obj->getID()] = $prefix.$psea_obj->getName() .' ['. TTi18n::getText('Rate') .']';
							$retval['-5'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-PU'.$psea_obj->getID()] = $prefix.$psea_obj->getName() .' ['. TTi18n::getText('Units') .']';
						}

						if ( $psea_obj->getType() == 50 ) { //Accruals, display balance/YTD amount.
							$retval['-6'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-PY'.$psea_obj->getID()] = $prefix.$psea_obj->getName() .' ['. TTi18n::getText('Balance') .']';
						}

						$i++;
					}
				}
				break;
			case 'pay_stub_account_unit_columns':
				//Units are only good for earnings?
				break;
			case 'pay_stub_account_ytd_columns':
				break;
            /*
            case 'company_deduction_options':
                //Get Company Tax Deductions
        		$cdlf = TTnew( 'CompanyDeductionListFactory' );
        		$cdlf->getByCompanyIdAndTypeId( $this->getUserObject()->getCompany(), 10 );
        		$retval = $cdlf->getArrayByListFactory( $cdlf, FALSE, TRUE );
                break;
            */
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = Misc::trimSortPrefix( array_merge($this->getOptions('dynamic_columns'), (array)$this->getOptions('report_custom_column')) );
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						if ( substr( $column, 0, 2 ) == 'PU' ) {
							$retval[$column] = 'numeric';
						} elseif ( strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE
							OR substr( $column, 0, 2 ) == 'PA' OR substr( $column, 0, 2 ) == 'PY' OR substr( $column, 0, 2 ) == 'PR' OR strpos($column, '_withheld') ) {
							$retval[$column] = 'currency';
						} elseif ( strpos($column, '_time') OR strpos($column, '_policy') ) {
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
										'-1010-by_employee+taxes' => TTi18n::gettext('Tax by Employee'),
							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
					$pseallf->getByCompanyId( $this->getUserObject()->getCompany() );
					if ( $pseallf->getRecordCount() > 0 ) {
						$pseal_obj = $pseallf->getCurrent();

						$default_linked_columns = array(
													$pseal_obj->getTotalGross(),
													$pseal_obj->getTotalNetPay(),
													$pseal_obj->getTotalEmployeeDeduction(),
													$pseal_obj->getTotalEmployerDeduction() );
					} else {
						$default_linked_columns = array();
					}
					unset($pseallf, $pseal_obj);

					switch( $template ) {
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
							$retval['-1010-time_period']['time_period'] = 'last_quarter';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode('+', $template );
							if ( is_array($template_keywords) ) {
								foreach( $template_keywords as $template_keyword ) {
									Debug::Text(' Keyword: '. $template_keyword, __FILE__, __LINE__, __METHOD__,10);

									switch( $template_keyword ) {
										//Columns
										case 'earnings':
											$retval['columns'][] = 'PA'.$default_linked_columns[0]; //Total Gross
											$retval['columns'][] = 'PA'.$default_linked_columns[1]; //Net Pay

											$psealf = TTnew( 'PayStubEntryAccountListFactory' );
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(10) );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach( $psealf as $psea_obj ) {
													$retval['columns'][] = 'PA'.$psea_obj->getID();
												}
											}
											break;
										case 'employee_deductions':
											$retval['columns'][] = 'PA'.$default_linked_columns[2]; //Employee Deductions

											$psealf = TTnew( 'PayStubEntryAccountListFactory' );
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(20) );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach( $psealf as $psea_obj ) {
													$retval['columns'][] = 'PA'.$psea_obj->getID();
												}
											}
											break;
										case 'employer_deductions':
											$retval['columns'][] = 'PA'.$default_linked_columns[3]; //Employor Deductions

											$psealf = TTnew( 'PayStubEntryAccountListFactory' );
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(30) );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach( $psealf as $psea_obj ) {
													$retval['columns'][] = 'PA'.$psea_obj->getID();
												}
											}
											break;
										case 'totals':
											$psealf = TTnew( 'PayStubEntryAccountListFactory' );
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(40) );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach( $psealf as $psea_obj ) {
													$retval['columns'][] = 'PA'.$psea_obj->getID();
												}
											}
											break;
										case 'taxes':
											//$retval['columns'][] = 'PA'.$default_linked_columns[0];
											$retval['columns'][] = 'subject_wages'; //Basically Total Gross.
											$retval['columns'][] = 'taxable_wages';
											$retval['columns'][] = 'tax_withheld';
											break;
										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'sin';

											$retval['-2000-company_deduction_id'][] = 0;

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'sin';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('sin' => 'asc');
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
		$this->tmp_data = array('pay_stub_entry' => array(), 'user' => array() );

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

        $ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
        if ( $ulf->getRecordCount() > 0 ) {
                //Get total gross pay stub account IDs
                $cdf = TTnew( 'CompanyDeductionFactory' );
                $cdf->setCompany( $this->getUserObject()->getCompany() );
                $total_gross_psea_ids = $cdf->getExpandedPayStubEntryAccountIDs( $cdf->getPayStubEntryAccountLinkObject()->getTotalGross() );

                if ( isset( $filter_data['company_deduction_id'] ) == FALSE ) {
                    $filter_data['company_deduction_id'] = '';
                }
                $cdlf = TTnew( 'CompanyDeductionListFactory' );
                $cdlf->getByCompanyIdAndId( $this->getUserObject()->getCompany(), $filter_data['company_deduction_id'] );
                if ( $cdlf->getRecordCount() > 0 ) {
        			$taxable_wages_psea_ids = array();
        			$tax_withheld_psea_ids = array();
        			Debug::Text('Found Company Deductions...', __FILE__, __LINE__, __METHOD__,10);
        			foreach( $cdlf as $cd_obj ) {

        				$taxable_wages_psea_ids = array_merge( $taxable_wages_psea_ids, (array)$cd_obj->getCombinedIncludeExcludePayStubEntryAccount( $cd_obj->getIncludePayStubEntryAccount(),  $cd_obj->getExcludePayStubEntryAccount() ) );
        				$tax_withheld_psea_ids[] = $cd_obj->getPayStubEntryAccount();

        			}
        			$taxable_wages_psea_ids = array_unique( $taxable_wages_psea_ids );
        			$tax_withheld_psea_ids = array_unique( $tax_withheld_psea_ids );
        		}

                foreach( $ulf as $u_obj ) {
    				$filter_data['user_ids'][] = $u_obj->getId();
    			}

                //Get all pay periods by transaction start/end date
    			if ( isset( $filter_data['start_date'] ) AND isset( $filter_data['start_date'] ) ) {
                    $pplf = TTnew( 'PayPeriodListFactory' );
        			$pplf->getByCompanyIdAndTransactionStartDateAndTransactionEndDate( $this->getUserObject()->getCompany(), $filter_data['start_date'], $filter_data['end_date']);

                    if ( $pplf->getRecordCount() > 0 ) {
        				foreach( $pplf as $pp_obj ) {
        					$pay_period_ids[] = $pp_obj->getId();
        				}
        			}
    			} elseif ( isset( $filter_data['pay_period_id'] ) ) {
    			     foreach( $filter_data['pay_period_id'] as $pay_period_id ) {
    			         $pay_period_ids[] = $pay_period_id;
    			     }
    			} else {
                     $pay_period_ids = '';
    			}

    			unset($pplf, $pp_obj);

                if ( isset($pay_period_ids) AND isset($filter_data['user_ids']) ) {

                        $pself = TTnew( 'PayStubEntryListFactory' );
                		//$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
                        $pself->getDateReportByCompanyIdAndUserIdAndPayPeriodId( $this->getUserObject()->getCompany(), $filter_data['user_ids'], $pay_period_ids, $filter_data['exclude_ytd_adjustment'] );

                		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pself->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
                		if ( $pself->getRecordCount() > 0 ) {

                			foreach( $pself as $key => $pse_obj ) {
                				$user_id = $pse_obj->getColumn('user_id');
                				$date_stamp = TTDate::strtotime( $pse_obj->getColumn('transaction_date') );
                				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

                                if ( isset($this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['PA'.$pay_stub_entry_name_id]) ) {
                                    $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['PA'.$pay_stub_entry_name_id] = bcadd( $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['PA'.$pay_stub_entry_name_id], $pse_obj->getColumn('amount') );
                                } else {
                					$this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['PA'.$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
                				}

                                if ( isset($this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['PY'.$pay_stub_entry_name_id]) ) {
                					$this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['PY'.$pay_stub_entry_name_id] = bcadd( $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['PY'.$pay_stub_entry_name_id], $pse_obj->getColumn('ytd_amount') );
                				} else {
                					$this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['PY'.$pay_stub_entry_name_id] = $pse_obj->getColumn('ytd_amount');
                				}

                                if ( isset($total_gross_psea_ids) AND is_array($total_gross_psea_ids) AND in_array($pay_stub_entry_name_id, $total_gross_psea_ids ) ) {
									if ( isset($this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['subject_wages']) ) {
									    $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['subject_wages'] = bcadd( $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['subject_wages'], $pse_obj->getColumn('amount') );
									} else {
				                        $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['subject_wages'] = $pse_obj->getColumn('amount');
									}

								}

								if ( isset($taxable_wages_psea_ids) AND is_array($taxable_wages_psea_ids) AND in_array($pay_stub_entry_name_id, $taxable_wages_psea_ids ) ) {
									if ( isset($this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['taxable_wages']) ) {
									    $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['taxable_wages'] = bcadd( $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['taxable_wages'], $pse_obj->getColumn('amount') );
									} else {
				                        $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['taxable_wages'] = $pse_obj->getColumn('amount');
									}
								}

								if ( isset($tax_withheld_psea_ids) AND is_array($tax_withheld_psea_ids) AND in_array($pay_stub_entry_name_id, $tax_withheld_psea_ids ) ) {
									if ( isset($this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['tax_withheld']) ) {
									    $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['tax_withheld'] = bcadd( $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['tax_withheld'], $pse_obj->getColumn('amount') );
									} else {
				                        $this->tmp_data['pay_stub_entry'][$date_stamp][$user_id]['tax_withheld'] = $pse_obj->getColumn('amount');
									}
								}

                				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
                			}
                		}
                }


        }

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
		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__,10);
		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {

            foreach( $this->tmp_data['pay_stub_entry'] as $date_stamp => $level_1 ) {
                foreach( $level_1 as $user_id => $row ) {

                        if ( isset($this->tmp_data['user'][$user_id]) ) {
                            $date_columns = TTDate::getReportDates( 'transaction', $date_stamp, FALSE, $this->getUserObject() );
                            $processed_data  = array(
												//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
												//'pay_stub' => array('sort' => $row['pay_stub_transaction_date'], 'display' => TTDate::getDate('DATE', $row['pay_stub_transaction_date'] ) ),
												);
                            //Need to make sure PSEA IDs are strings not numeric otherwise array_merge will re-key them.
    						$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

    						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
    						$key++;
                        }
                }
            }

			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1 );
		}

		Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}






}
?>
