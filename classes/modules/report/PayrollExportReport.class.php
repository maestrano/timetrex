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
class PayrollExportReport extends TimesheetSummaryReport {

	function __construct() {
		$this->title = TTi18n::getText('Payroll Export Report');
		$this->file_name = 'payroll_export';

		//Don't call TimesheetSummaryReport __construct(), skip one level lower to the Report class instead.
		Report::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_payroll_export', $user_id, $company_id ) ) {
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
			case 'export_columns': //Must pass export_type.
				if ( $params == 'csv_advanced' ) {

					if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCompanyObject() ) AND $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
						$jar = TTNew('JobDetailReport');
					} else {
						$jar = TTNew('TimesheetDetailReport');
					}
					$jar->setUserObject( $this->getUserObject() );

					$retval = $jar->getOptions('static_columns');
				} else {
					$retval = parent::getOptios('static_columns');
				}
				break;
			case 'output_format':
				$retval = parent::getOptions('default_output_format');
				break;
			case 'export_type':
				$retval = array(
								0 => TTi18n::gettext('-- Please Choose --'),
								'adp' 				=> TTi18n::gettext('ADP'),
								'paychex_preview' 	=> TTi18n::gettext('Paychex Preview'),
								'paychex_preview_advanced_job' => TTi18n::gettext('Paychex Preview (by Day/Job)'),
								'paychex_online' 	=> TTi18n::gettext('Paychex Online Payroll'),
								'ceridian_insync' 	=> TTi18n::gettext('Ceridian Insync'),
								'millenium' 		=> TTi18n::gettext('Millenium'),
								'quickbooks' 		=> TTi18n::gettext('QuickBooks Pro'),
								//'quickbooks_advanced' => TTi18n::gettext('QuickBooks Pro (Advanced)'), //Break time out by day?
								'surepayroll' 		=> TTi18n::gettext('SurePayroll'),
								'chris21' 			=> TTi18n::gettext('Chris21'),
								'csv' 				=> TTi18n::gettext('Generic Excel/CSV'),
								'csv_advanced' 		=> TTi18n::gettext('Generic Excel/CSV (Advanced)'),
								//'other' 			=> TTi18n::gettext('-- Other --'),
								);
				break;
			case 'export_policy':
				$static_columns = array();

				$columns = array(					'-0010-regular_time' => TTi18n::gettext('Regular Time'),
													);

				$columns = Misc::prependArray( $static_columns, $columns);

				//Get all Overtime policies.
				$otplf = TTnew( 'OverTimePolicyListFactory' );
				$otplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $otplf->getRecordCount() > 0 ) {
					foreach ($otplf as $otp_obj ) {
						$otp_columns['-0020-over_time_policy-'.$otp_obj->getId()] = TTi18n::gettext('Overtime').': '.$otp_obj->getName();
					}

					$columns = array_merge( $columns, $otp_columns);
				}

				//Get all Premium policies.
				$pplf = TTnew( 'PremiumPolicyListFactory' );
				$pplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach ($pplf as $pp_obj ) {
						$pp_columns['-0030-premium_policy-'.$pp_obj->getId()] = TTi18n::gettext('Premium').': '.$pp_obj->getName();
					}

					$columns = array_merge( $columns, $pp_columns);
				}


				//Get all Absence Policies.
				$aplf = TTnew( 'AbsencePolicyListFactory' );
				$aplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach ($aplf as $ap_obj ) {
						$ap_columns['-0040-absence_policy-'.$ap_obj->getId()] = TTi18n::gettext('Absence').': '.$ap_obj->getName();
					}

					$columns = array_merge( $columns, $ap_columns);
				}

				$retval = $columns;
				break;
			case 'default_hour_codes':
				$export_type = $this->getOptions('export_type');
				$export_policy = Misc::trimSortPrefix( $this->getOptions('export_policy') );

				foreach( $export_type as $type => $name ) {
					switch( strtolower($type) ) {
						case 'paychex_online':
							foreach( $export_policy as $id => $name ) {
								if ( strpos( $id, 'regular') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'Regular';
								} elseif ( strpos( $id, 'over_time') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'Overtime';
								} elseif ( strpos( $id, 'absence') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'Absence';
								}
							}

							break;
						default:
							if ( $type === 0 ) {
								continue;
							}

							foreach( $export_policy as $id => $name ) {
								if ( strpos( $id, 'regular') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'REG';
								} elseif ( strpos( $id, 'over_time') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'OT';
								} elseif ( strpos( $id, 'absence') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'ABS';
								}
							}
						break;
					}
				}
				break;
			case 'hour_column_name':
				$hour_column_name_map = array(
								'adp' 				=> TTi18n::gettext('ADP Hours Code'),
								'paychex_preview' 	=> TTi18n::gettext('Paychex Hours Code'),
								'paychex_preview_advanced_job' => TTi18n::gettext('Paychex Hours Code'),
								'paychex_online' 	=> TTi18n::gettext('Paychex Hours Code'),
								'ceridian_insync' 	=> TTi18n::gettext('Ceridian Hours Code'),
								'millenium' 		=> TTi18n::gettext('Millenium Hours Code'),
								'quickbooks' 		=> TTi18n::gettext('Quickbooks Payroll Item Name'),
								'quickbooks_advanced' => TTi18n::gettext('Quickbooks Payroll Item Name'),
								'surepayroll' 		=> TTi18n::gettext('Payroll Code'),
								'csv' 				=> TTi18n::gettext('Hours Code'),
								'csv_advanced' 				=> TTi18n::gettext('Hours Code'),
								);

				if (  isset($params['export_type']) AND isset($hour_column_name_map[$params['export_type']]) ) {
					$retval = $hour_column_name_map[$params['export_type']];
				} else {
					$retval = $hour_column_name_map['csv'];
				}
				break;
			case 'adp_hour_column_options':
				$retval['adp_hour_column_options'][0] = TTi18n::gettext('-- DO NOT EXPORT --');
				$retval['adp_hour_column_options']['-0010-regular_time'] = TTi18n::gettext('Regular Time');
				$retval['adp_hour_column_options']['-0020-overtime'] = TTi18n::gettext('Overtime');
				for ( $i=3; $i <= 4; $i++ ) {
					$retval['adp_hour_column_options']['-003'.$i.'-'.$i] = TTi18n::gettext('Hours') .' '. $i;
				}
				break;
			case 'adp_company_code_options':
			case 'adp_batch_options':
			case 'adp_temp_dept_options':
				$retval = array(
								0 => TTi18n::gettext('-- Custom --'),
								'-0010-default_branch_manual_id' => TTi18n::gettext('Default Branch: Code'),
								'-0020-default_department_manual_id' => TTi18n::gettext('Default Department: Code'),
								'-0030-branch_manual_id' => TTi18n::gettext('Branch: Code'),
								'-0040-department_manual_id' => TTi18n::gettext('Department: Code'),
								);

				$oflf = TTnew( 'OtherFieldListFactory' );

				//Put a colon or underscore in the name, thats how we know it needs to be replaced.

				//Get Branch other fields.
				$default_branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-1000-default_branch_', TTi18n::getText('Default Branch').': ' );
				if (  !is_array($default_branch_options) ) {
					$default_branch_options = array();
				}
				$default_department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-2000-default_department_', TTi18n::getText('Default Department').': ' );
				if (  !is_array($default_department_options) ) {
					$default_department_options = array();
				}

				$branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-3000-branch_', TTi18n::getText('Branch').': ' );
				if ( !is_array($branch_options) ) {
					$branch_options = array();
				}
				$department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-4000-department_', TTi18n::getText('Department').': ' );
				if ( !is_array($department_options) ) {
					$department_options = array();
				}

				$retval = array_merge( $retval, (array)$default_branch_options, (array)$default_department_options, $branch_options, $department_options );
				break;
			case 'quickbooks_proj_options':
			case 'quickbooks_job_options':
			case 'quickbooks_item_options':
				$retval = array(
								0 => TTi18n::gettext('-- NONE --'),
								'default_branch' => TTi18n::gettext('Default Branch'),
								'default_department' => TTi18n::gettext('Default Department'),
								'group' => TTi18n::gettext('Group'),
								'title' => TTi18n::gettext('Title'),
								'branch_name' => TTi18n::gettext('Punch Branch'),
								'department_name' => TTi18n::gettext('Punch Department'),								
								);
				break;
            case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'PayrollExportReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::prependArray( Misc::addSortPrefix( $custom_column_labels, 9500 ), parent::_getOptions( $name, $params ) );
					} else {
						$retval = parent::_getOptions( $name, $params );
					}
				}
                break; 
            case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = Misc::prependArray( $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'PayrollExportReport', 'custom_column' ), parent::_getOptions( $name, $params ) );
				}
                break;                
            case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'PayrollExportReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval =  Misc::prependArray( Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 ), parent::_getOptions( $name, $params ) );
					} else {
						$retval = parent::_getOptions( $name, $params );
					}
				}
                break;
            case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'PayrollExportReport', 'custom_column' );
					if ( is_array($report_static_custom_column_labels) ) {
						$retval =  Misc::prependArray( Misc::addSortPrefix( $report_static_custom_column_labels, 9700 ), parent::_getOptions( $name, $params ) );
					} else {
						$retval = parent::_getOptions( $name, $params );
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
				return Misc::prependArray( array_merge( array( '-1480-sin' => TTi18n::gettext('SIN/SSN') ), (array)$this->getOptions('report_static_custom_column') ), parent::_getOptions( $name, $params ) );
				break;
			default:
				return parent::_getOptions( $name, $params );
				break;
		}

		return $retval;
	}

	function getExportTypeTemplate( $config, $format ) {
		$config = Misc::trimSortPrefix( $config );
		
		if ( $format == 'payroll_export' ) {
			unset($config['columns'],$config['group'],$config['sort'],$config['sub_total']);
			$config['other']['disable_grand_total'] = TRUE; //Disable grand totals.

			if ( isset($config['form']['export_type']) ) {
				$export_type = $config['form']['export_type'];
				$setup_data = $config['form']; //get setup data to determine custom formats...

				switch( strtolower($export_type) ) {
					case 'adp':
						$config['columns'][] = 'default_branch_id';
						$config['columns'][] = 'default_department_id';

						if ( isset($setup_data['adp']['company_code']) AND strpos( $setup_data['adp']['company_code'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['adp']['company_code'] );
						}
						if ( isset($setup_data['adp']['batch_id']) AND strpos( $setup_data['adp']['batch_id'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['adp']['batch_id'] );
						}
						if ( isset($setup_data['adp']['temp_dept']) AND strpos( $setup_data['adp']['temp_dept'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] );
						}
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'default_branch_id';
						$config['group'][] = 'default_department_id';
						if ( isset($setup_data['adp']['company_code']) AND strpos( $setup_data['adp']['company_code'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['adp']['company_code'] );
						}
						if ( isset($setup_data['adp']['batch_id']) AND strpos( $setup_data['adp']['batch_id'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['adp']['batch_id'] );
						}
						if ( isset($setup_data['adp']['temp_dept']) AND strpos( $setup_data['adp']['temp_dept'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] );
						}
						$config['group'][] = 'employee_number';

						if ( isset($setup_data['adp']['company_code']) AND strpos( $setup_data['adp']['company_code'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['adp']['company_code'] ) => 'asc' );
						}
						if ( isset($setup_data['adp']['batch_id']) AND strpos( $setup_data['adp']['batch_id'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['adp']['batch_id'] ) => 'asc' );
						}
						if ( isset($setup_data['adp']['temp_dept']) AND strpos( $setup_data['adp']['temp_dept'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] ) => 'asc' );
						}
						$config['sort'][] = array('employee_number' => 'asc' );
						break;
					case 'paychex_preview_advanced_job': //This uses the Job Analysis report instead, so handle the config later.
						break;
					case 'paychex_preview':
					case 'paychex_online':
					case 'millenium':
					case 'ceridian_insync':
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'employee_number';

						$config['sort'][] = array('employee_number' => 'asc');
						break;
					case 'quickbooks':
					case 'quickbooks_advanced':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'last_name';
						$config['columns'][] = 'first_name';
						$config['columns'][] = 'middle_name';

						//Support custom group based on PROJ field
						if ( isset($setup_data['quickbooks']['proj']) AND !empty($setup_data['quickbooks']['proj']) ) {
							$config['columns'][] = $setup_data['quickbooks']['proj'];
						}
						if ( isset($setup_data['quickbooks']['item']) AND !empty($setup_data['quickbooks']['item']) ) {
							$config['columns'][] = $setup_data['quickbooks']['item'];
						}
						if ( isset($setup_data['quickbooks']['job']) AND !empty($setup_data['quickbooks']['job']) ) {
							$config['columns'][] = $setup_data['quickbooks']['job'];
						}

						$config['columns'] += array_keys(  Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'last_name';
						$config['group'][] = 'first_name';
						$config['group'][] = 'middle_name';

						//Support custom group based on PROJ field
						if ( isset($setup_data['quickbooks']['proj']) AND !empty($setup_data['quickbooks']['proj']) ) {
							$config['group'][] = $setup_data['quickbooks']['proj'];
						}
						if ( isset($setup_data['quickbooks']['item']) AND !empty($setup_data['quickbooks']['item']) ) {
							$config['group'][] = $setup_data['quickbooks']['item'];
						}
						if ( isset($setup_data['quickbooks']['job']) AND !empty($setup_data['quickbooks']['job']) ) {
							$config['group'][] = $setup_data['quickbooks']['job'];
						}

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc');

						break;
					case 'surepayroll':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'last_name';
						$config['columns'][] = 'first_name';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'last_name';
						$config['group'][] = 'first_name';

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc');
						break;
					case 'chris21':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc');
						break;
					case 'csv':
						//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
						$config['columns'][] = 'full_name';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'default_branch';
						$config['columns'][] = 'default_department';
						$config['columns'][] = 'pay_period';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'full_name';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'default_branch';
						$config['group'][] = 'default_department';
						$config['group'][] = 'pay_period';

						$config['sort'][] = array('full_name' => 'asc', 'employee_number' => 'asc', 'default_branch' => 'asc', 'default_department' => 'asc', 'pay_period' => 'asc');
						break;
					case 'csv_advanced': //This uses the Job Analysis report instead, so handle the config later.
						break;
				}
				Debug::Arr($config, 'Export Type Template: '. $export_type, __FILE__, __LINE__, __METHOD__,10);
			} else {
				Debug::Text('No Export Type defined, not modifying config...', __FILE__, __LINE__, __METHOD__,10);
			}
		}

		return $config;
	}

	//Short circuit this function, as no postprocessing is required for exporting the data.
	function _postProcess( $format = NULL ) {
		if ( $format == 'payroll_export' ) {
			return TRUE;
		} else {
			return parent::_postProcess( $format );
		}
	}

	function _outputPayrollExport( $format = NULL ) {
		$setup_data = $this->getFormConfig();

		Debug::Text('Generating Payroll Export... Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

		if ( isset($setup_data['export_type']) ) {
			Debug::Text('Export Type: '. $setup_data['export_type'], __FILE__, __LINE__, __METHOD__,10);
		} else {
			Debug::Text('No Export Type defined!', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}
		Debug::Arr($setup_data, 'Setup Data: ', __FILE__, __LINE__, __METHOD__,10);
		$rows = $this->data;
		//Debug::Arr($rows, 'PreData: ', __FILE__, __LINE__, __METHOD__,10);

		$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.txt';
		$mime_type = 'application/text';
		$data = NULL;

		switch( strtolower(trim($setup_data['export_type'])) ) {
			case 'adp': //ADP export format.
				//File format supports multiple rows per employee (file #) all using the same columns. No need to jump through nasty hoops to fit everything on row.
				$export_column_map = array(
										'company_code' => 'Co Code',
										'batch_id' => 'Batch ID',
										'temp_dept' => 'Temp Dept',
										'employee_number' =>  'File #',
										'regular_time' => 'Reg Hours',
										'overtime' => 'O/T Hours',
										'3_code' => 'Hours 3 Code',
										'3_amount' => 'Hours 3 Amount',
										'4_code' => 'Hours 4 Code',
										'4_amount' => 'Hours 4 Amount',
										);

				ksort($setup_data['adp']['columns']);
				$setup_data['adp']['columns'] = Misc::trimSortPrefix( $setup_data['adp']['columns'] );

				foreach( $setup_data['adp']['columns'] as $column_id => $column_data ) {
					$column_name = NULL;
					if ( $column_data['hour_column'] == 'regular_time' ) {
						$export_data_map[$column_id] = 'regular_time';
					} elseif ($column_data['hour_column'] == 'overtime' ) {
						$export_data_map[$column_id] = 'overtime';
					} elseif ( $column_data['hour_column'] >= 3 ) {
						$export_data_map[$column_id] = $column_data;
					}
				}

				if ( !isset($setup_data['adp']['company_code_value']) ) {
					$setup_data['adp']['company_code_value'] = NULL;
				}
				if ( !isset($setup_data['adp']['batch_id_value']) ) {
					$setup_data['adp']['batch_id_value'] = NULL;
				}
				if ( !isset($setup_data['adp']['temp_dept_value']) ) {
					$setup_data['adp']['temp_dept_value'] = NULL;
				}

				$company_code_column = Misc::trimSortPrefix( $setup_data['adp']['company_code'] );
				$batch_id_column = Misc::trimSortPrefix( $setup_data['adp']['batch_id'] );
				$temp_dept_column = Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] );
				foreach($rows as $row) {
					$static_columns = array(
										'company_code' => ( isset($row[$company_code_column]) ) ? $row[$company_code_column] : $setup_data['adp']['company_code_value'],
										'batch_id' => ( isset($row[$batch_id_column]) ) ? $row[$batch_id_column] : $setup_data['adp']['batch_id_value'],
										'temp_dept' => ( isset($row[$temp_dept_column]) ) ? $row[$temp_dept_column] : $setup_data['adp']['temp_dept_value'],
										'employee_number' => str_pad( $row['employee_number'], 6, 0, STR_PAD_LEFT), //ADP employee numbers should always be 6 digits.
										);

					foreach( $setup_data['adp']['columns'] as $column_id => $column_data ) {
						$column_data = Misc::trimSortPrefix( $column_data, TRUE );
						Debug::Text('ADP Column ID: '. $column_id .' Hour Column: '. $column_data['hour_column'] .' Code: '. $column_data['hour_code'], __FILE__, __LINE__, __METHOD__,10);
						if ( isset( $row[$column_id] ) AND $column_data['hour_column'] != '0' ) {
							foreach( $export_column_map as $export_column_id => $export_column_name ) {
								Debug::Arr($row, 'Row: Column ID: '. $column_id .' Export Column ID: '. $export_column_id .' Name: '. $export_column_name, __FILE__, __LINE__, __METHOD__,10);

								if ( ( $column_data['hour_column'] == $export_column_id OR $column_data['hour_column'].'_code' == $export_column_id )
										AND !in_array( $export_column_id, array('company_code','batch_id','temp_dept', 'employee_number')) ) {
									if ( (int)substr( $export_column_id, 0, 1 ) > 0 ) {
										$tmp_row[$column_data['hour_column'].'_code'] = $column_data['hour_code'];
										$tmp_row[$column_data['hour_column'].'_amount'] = TTDate::getTimeUnit( $row[$column_id], 20 );
									} else {
										$tmp_row[$export_column_id] = TTDate::getTimeUnit( $row[$column_id], 20 );
									}

									//Break out every column onto its own row, that way its easier to handle multiple columns of the same type.
									$tmp_rows[] = array_merge( $static_columns, $tmp_row );
									unset($tmp_row);
								}
							}
						}
					}
				}

				$file_name = 'EPI000000.csv';
				if ( isset( $tmp_rows) ) {
					//File format supports multiple entries per employee (file #) all using the same columns. No need to jump through nasty hoops to fit everyone one row.
					$file_name = 'EPI'. $tmp_rows[0]['company_code'] . $tmp_rows[0]['batch_id'] .'.csv';

					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'adp_old': //ADP export format.
				$file_name = 'EPI'. $setup_data['adp']['company_code'] . $setup_data['adp']['batch_id'] .'.csv';

				$export_column_map = array();
				$static_export_column_map = array(
										'company_code' => 'Co Code',
										'batch_id' => 'Batch ID',
										'employee_number' =>  'File #',
										);

				$static_export_data_map = array(
								'company_code' => $setup_data['adp']['company_code'],
								'batch_id' => $setup_data['adp']['batch_id'],
								);

				//
				//Format allows for multiple duplicate columns.
				//ie: Hours 3 Code, Hours 3 Amount, Hours 3 Code, Hours 3 Amount, ...
				//However, we can only have a SINGLE O/T Hours column.
				//We also need to combine hours with the same code together.
				//
				ksort($setup_data['adp']['columns']);
				$setup_data['adp']['columns'] = Misc::trimSortPrefix( $setup_data['adp']['columns'] );

				foreach( $setup_data['adp']['columns'] as $column_id => $column_data ) {
					$column_name = NULL;
					if ( $column_data['hour_column'] == 'regular_time' ) {
						$column_name = 'Reg Hours';
						$export_data_map[$column_id] = trim($setup_data['adp']['columns'][$column_id]['hour_code']);
					} elseif ($column_data['hour_column'] == 'overtime' ) {
						$column_name = 'O/T Hours';
						$export_data_map[$column_id] = trim($setup_data['adp']['columns'][$column_id]['hour_code']);
					} elseif ( $column_data['hour_column'] >= 3 ) {
						$column_name = 'Hours '. $column_data['hour_column'] .' Amount';
						$export_column_map[$setup_data['adp']['columns'][$column_id]['hour_code'].'_code'] = 'Hours '. $column_data['hour_column'] .' Code';
						$export_data_map[$column_id] = trim($setup_data['adp']['columns'][$column_id]['hour_code']);
					}

					if ( $column_name != '' ) {
						$export_column_map[trim($setup_data['adp']['columns'][$column_id]['hour_code'])] = $column_name;
					}
				}
				$export_column_map = Misc::prependArray( $static_export_column_map, $export_column_map);

				//
				//Combine time from all columns with the same hours code.
				//
				$i=0;
				foreach($rows as $row) {
					foreach ( $static_export_column_map as $column_id => $column_name ) {
						if ( isset($static_export_data_map[$column_id]) ) {
							//Copy over static config values like company code/batch_id.
							$tmp_rows[$i][$column_id] = $static_export_data_map[$column_id];
						} elseif( isset($row[$column_id]) ) {
							if ( isset($static_export_column_map[$column_id]) ) {
								//Copy over employee_number. (File #)
								$tmp_rows[$i][$column_id] = $row[$column_id];
							}
						}
					}

					foreach ( $export_data_map as $column_id => $column_name ) {
						if ( !isset($tmp_rows[$i][$column_name]) ) {
							$tmp_rows[$i][$column_name] = 0;
						}

						if ( isset($row[$column_id]) ) {
							$tmp_rows[$i][$column_name] += $row[$column_id];
						}
						$tmp_rows[$i][$column_name.'_code']  = $column_name;
					}

					$i++;
				}

				//Convert time from seconds to hours.
				$convert_unit_columns = array_keys($static_export_column_map);

				foreach( $tmp_rows as $row => $data ) {
					foreach( $data as $column_id => $column_data ) {
						//var_dump($column_id,$column_data);
						if ( is_int($column_data) AND !in_array( $column_id, $convert_unit_columns ) ) {
							$tmp_rows[$row][$column_id] = TTDate::getTimeUnit( $column_data, 20 );
						}
					}
				}
				unset($row, $data, $column_id, $column_data);

				$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE );

				break;
			case 'paychex_preview_advanced_job': //PayChex Preview with job information
				unset($rows); //Ignore any existing timesheet summary data, we will be using our own job data below.
				//Debug::Arr($setup_data, 'PayChex Advanced Job Setup Data: ', __FILE__, __LINE__, __METHOD__,10);

				$config['columns'][] = 'employee_number';
				$config['columns'][] = 'date_stamp';
				$config['columns'] = array_merge( $config['columns'], (array)$setup_data['paychex_preview_advanced_job']['job_columns'] );
				$config['columns'][] = $setup_data['paychex_preview_advanced_job']['state_columns'];
				$config['columns'] += array_keys(  Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );

				$config['group'][] = 'employee_number';
				$config['group'][] = 'date_stamp';
				$config['group'] = array_merge( $config['columns'], (array)$setup_data['paychex_preview_advanced_job']['job_columns']);
				$config['group'][] = $setup_data['paychex_preview_advanced_job']['state_columns'];

				$config['sort'][] = array('employee_number' => 'asc');
				$config['sort'][] = array('date_stamp' => 'asc');
				//Debug::Arr($config, 'Job Detail Report Config: ', __FILE__, __LINE__, __METHOD__,10);

				//Get job data...
				$jar = TTNew('JobDetailReport');
				$jar->setAMFMessageID( $this->getAMFMessageID() );
				$jar->setUserObject( $this->getUserObject() );
				$jar->setPermissionObject( $this->getPermissionObject() );
				$jar->setConfig( $config );
				$jar->setFilterConfig( $this->getFilterConfig() );
				$jar->setSortConfig( $config['sort'] );
				$jar->_getData();
				$jar->_preProcess();
				$jar->sort();
				$rows = $jar->data;
				//Debug::Arr($rows, 'Raw Rows: ', __FILE__, __LINE__, __METHOD__,10);
				
				//Need to get job data from job report instead of TimeSheet Summary report.
				if ( !isset($setup_data['paychex_preview_advanced_job']['client_number']) ) {
					$setup_data['paychex_preview_advanced_job']['client_number'] = '0000';
				}

				$file_name = $setup_data['paychex_preview_advanced_job']['client_number'] .'_TA.txt';

				ksort($setup_data['paychex_preview_advanced_job']['columns']);
				$setup_data['paychex_preview_advanced_job']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_preview_advanced_job']['columns'] );

				$data = NULL;
				foreach($rows as $row) {
					foreach( $setup_data['paychex_preview_advanced_job']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							$data .= str_pad($row['employee_number'], 6, ' ', STR_PAD_LEFT);
							$data .= str_pad('', 31, ' ', STR_PAD_LEFT); //Blank space.
							if ( isset($setup_data['paychex_preview_advanced_job']['job_columns']) AND is_array($setup_data['paychex_preview_advanced_job']['job_columns']) ) {
								$job_column = array();
								foreach( $setup_data['paychex_preview_advanced_job']['job_columns'] as $tmp_job_column ) {
									$job_column[] = ( isset($row[$tmp_job_column]) ) ? $row[$tmp_job_column] : NULL ;
								}
								$data .= str_pad( substr( implode('-', $job_column), 0, 12 ) , 12, ' ', STR_PAD_LEFT);
								unset($job_column);
							} else {
								$data .= str_pad( '', 12, ' ', STR_PAD_LEFT);
							}
							$data .= str_pad('', 1, ' ', STR_PAD_LEFT); //Shift identifier.

							//Allow user to specify three digit hour codes to specify their own E/D codes. If codes are two digit, always use E.
							if ( strlen( trim($column_data['hour_code']) ) < 3 ) {
								$column_data['hour_code'] = 'E'.trim($column_data['hour_code']);
							}
							//Should start at col51
							$data .= str_pad( substr( trim($column_data['hour_code']), 0, 3), 3, ' ', STR_PAD_RIGHT);
							if ( isset($setup_data['paychex_preview_advanced_job']['include_hourly_rate']) AND $setup_data['paychex_preview_advanced_job']['include_hourly_rate'] == TRUE ) {
								$data .= str_pad( ( isset($row[$column_id.'_hourly_rate']) ? number_format( $row[$column_id.'_hourly_rate'], 4, '.', '') : NULL ), 9, 0, STR_PAD_LEFT); //Override rate
							} else {
								$data .= str_pad( '', 9, 0, STR_PAD_LEFT); //Override rate
							}
							$data .= str_pad( TTDate::getTimeUnit( $row[$column_id], 20 ), 8, 0, STR_PAD_LEFT);

							//Break out time by day.
							$data .= str_pad( TTDate::getYear($row['time_stamp']), 4, 0, STR_PAD_LEFT); //Year, based on time_stamp epoch column
							$data .= str_pad( TTDate::getMonth($row['time_stamp']), 2, 0, STR_PAD_LEFT); //Month, based on time_stamp epoch column. Can be space padded.
							$data .= str_pad( TTDate::getDayOfMonth($row['time_stamp']), 2, 0, STR_PAD_LEFT); //Day, based on time_stamp epoch column. Can be space padded.

							$data .= str_pad('', 4, ' ', STR_PAD_LEFT); //Filler
							$data .= str_pad( '' , 9, ' ', STR_PAD_LEFT); //Amount. This can always be calculated from hours and hourly rate above though.
							$data .= str_pad( '' , 13, ' ', STR_PAD_LEFT); //Blank space
							if ( isset($setup_data['paychex_preview_advanced_job']['state_columns']) ) {
								$data .= str_pad( ( isset($row[$setup_data['paychex_preview_advanced_job']['state_columns']]) ) ? $row[$setup_data['paychex_preview_advanced_job']['state_columns']] : NULL , 2, ' ', STR_PAD_LEFT); //State
							}
							$data .= "\n";
						}
					}
				}

				break;
			case 'paychex_preview': //Paychex Preview export format.
				//Add an advanced PayChex Preview format that supports rates perhaps?
				//http://kb.idb-sys.com/KnowledgebaseArticle10013.aspx
				if ( !isset($setup_data['paychex_preview']['client_number']) ) {
					$setup_data['paychex_preview']['client_number'] = '0000';
				}

				$file_name = $setup_data['paychex_preview']['client_number'] .'_TA.txt';

				ksort($setup_data['paychex_preview']['columns']);
				$setup_data['paychex_preview']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_preview']['columns'] );

				$data = NULL;
				foreach($rows as $row) {
					foreach( $setup_data['paychex_preview']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							$data .= str_pad($row['employee_number'], 6, ' ', STR_PAD_LEFT);
							$data .= str_pad('E'. str_pad( trim($column_data['hour_code']), 2, ' ', STR_PAD_RIGHT) , 47, ' ', STR_PAD_LEFT);
							$data .= str_pad( str_pad( TTDate::getTimeUnit( $row[$column_id], 20 ), 8, 0, STR_PAD_LEFT) , 17, ' ', STR_PAD_LEFT)."\n";
						}
					}
				}
				break;
			case 'paychex_online': //Paychex Online Payroll CSV
				ksort($setup_data['paychex_online']['columns']);
				$setup_data['paychex_online']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_online']['columns'] );

				$earnings = array();
				//Find all the hours codes
				foreach( $setup_data['paychex_online']['columns'] as $column_id => $column_data ) {
					$hour_code = $column_data['hour_code'];
					$earnings[] = $hour_code;
				}

				$export_column_map['employee_number'] = '';
				foreach($earnings as $key => $value) {
					$export_column_map[$value] = '';
				}

				$i=0;
				foreach($rows as $row) {
					if ( $i == 0 ) {
						//Include header.
						$tmp_row['employee_number'] = 'Employee Number';
						foreach($earnings as $key => $value) {
							$tmp_row[$value] = $value . ' Hours';
						}
						$tmp_rows[] = $tmp_row;
						unset($tmp_row);
					}

					//Combine all hours from the same code together.
					foreach( $setup_data['paychex_online']['columns'] as $column_id => $column_data ) {
						$hour_code = trim($column_data['hour_code']);
						if ( isset( $row[$column_id] ) AND $hour_code != '' ) {
							if ( !isset($tmp_hour_codes[$hour_code]) ) {
								$tmp_hour_codes[$hour_code] = 0;
							}
							$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$column_data['hour_code']], $row[$column_id] ); //Use seconds for math here.
						}
					}

					if ( isset($tmp_hour_codes) ) {
						$tmp_row['employee_number'] = $row['employee_number'];
						foreach($tmp_hour_codes as $hour_code => $hours ) {
							$tmp_row[$hour_code] = TTDate::getTimeUnit($hours, 20);
						}
						$tmp_rows[] = $tmp_row;
						unset($tmp_hour_codes, $hour_code, $hours, $tmp_row);
					}

					$i++;
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'millenium': //Millenium export format. Also used by Qqest.
				ksort($setup_data['millenium']['columns']);
				$setup_data['millenium']['columns'] = Misc::trimSortPrefix( $setup_data['millenium']['columns'] );

				$export_column_map = array('employee_number' => '', 'transaction_code' => '', 'hour_code' => '', 'hours' => '');
				foreach($rows as $row) {
					foreach( $setup_data['millenium']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							$tmp_rows[] = array(
												'employee_number' => $row['employee_number'],
												'transaction_code' => 'E',
												'hour_code' => trim($column_data['hour_code']),
												'hours' => TTDate::getTimeUnit( $row[$column_id], 20 )
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;

			case 'ceridian_insync': //Ceridian InSync export format. Needs to be .IMP to import? DOS line endings?
				if ( !isset($setup_data['ceridian_insync']['employer_number']) OR $setup_data['ceridian_insync']['employer_number'] == '' ) {
					$setup_data['ceridian_insync']['employer_number'] = '0001';
				}

				$file_name = strtolower(trim($setup_data['export_type'])).'_'. $setup_data['ceridian_insync']['employer_number'] .'_'. date('Y_m_d').'.imp';

				ksort($setup_data['ceridian_insync']['columns']);
				$setup_data['ceridian_insync']['columns'] = Misc::trimSortPrefix( $setup_data['ceridian_insync']['columns'] );

				$export_column_map = array(	'employer_number' => '', 'import_type_id' => '', 'employee_number' => '', 'check_type' => '',
											'hour_code' => '', 'value' => '', 'distribution' => '', 'rate' => '', 'premium' => '', 'day' => '', 'pay_period' => '');
				foreach($rows as $row) {
					foreach( $setup_data['ceridian_insync']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							$tmp_rows[] = array(
												'employer_number' => $setup_data['ceridian_insync']['employer_number'], //Employer No./Payroll Number
												'import_type_id' => 'COSTING', //This can change, must be configurable.
												'employee_number' => str_pad( $row['employee_number'], 9, '0', STR_PAD_LEFT),
												'check_type' => 'REG',
												'hour_code' => trim($column_data['hour_code']),
												'value' => TTDate::getTimeUnit( $row[$column_id], 20 ),
												'distribution' => NULL,
												'rate' => NULL, //This overrides whats in ceridian and seems to cause problems.
												//'rate' => ( isset($row[$column_id.'_hourly_rate']) ) ? $row[$column_id.'_hourly_rate'] : NULL,
												'premium' => NULL,
												'day' => NULL,
												'pay_period' => NULL,
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE, "\r\n" ); //Use DOS line endings only.
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);

				break;
			case 'quickbooks': //Quickbooks Pro export format.
			case 'quickbooks_advanced': //Quickbooks Pro export format.
				$file_name = 'payroll_export.iif';

				ksort($setup_data['quickbooks']['columns']);
				$setup_data['quickbooks']['columns'] = Misc::trimSortPrefix( $setup_data['quickbooks']['columns'] );

				//
				// Quickbooks header
				//
				/*
					Company Create Time can be found by first running an Timer Activity export in QuickBooks and viewing the output.

					PITEM field needs to be populated, as that is the PAYROLL ITEM in quickbooks. It can be the same as the ITEM field.
					ITEM is the service item, can be mapped to department/task?
					PROJ could be mapped to the default department/branch?
				*/
				$data =  "!TIMERHDR\tVER\tREL\tCOMPANYNAME\tIMPORTEDBEFORE\tFROMTIMER\tCOMPANYCREATETIME\n";
				$data .= "TIMERHDR\t8\t0\t". trim($setup_data['quickbooks']['company_name']) ."\tN\tY\t". trim($setup_data['quickbooks']['company_created_date']) ."\n";
				$data .= "!TIMEACT\tDATE\tJOB\tEMP\tITEM\tPITEM\tDURATION\tPROJ\tNOTE\tXFERTOPAYROLL\tBILLINGSTATUS\n";

				foreach($rows as $row) {
					foreach( $setup_data['quickbooks']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							//Make sure employee name is in format: LastName, FirstName MiddleInitial
							$tmp_employee_name = $row['last_name'].', '. $row['first_name'];
							if ( isset($row['middle_name']) AND strlen($row['middle_name']) > 0 ) {
								$tmp_employee_name .= ' '.substr(trim($row['middle_name']),0,1);
							}

							$proj = NULL;
							if ( isset($row[$setup_data['quickbooks']['proj']]) ) {
								$proj = $row[$setup_data['quickbooks']['proj']];
							}
							$item = NULL;
							if ( isset($row[$setup_data['quickbooks']['item']]) ) {
								$item = $row[$setup_data['quickbooks']['item']];
							}
							$job = NULL;
							if ( isset($row[$setup_data['quickbooks']['job']]) ) {
								$job = $row[$setup_data['quickbooks']['job']];
							}

							$data .= "TIMEACT\t". date('n/j/y', $row['pay_period_end_date'])."\t". $job ."\t". $tmp_employee_name ."\t". $item ."\t". trim($column_data['hour_code']) ."\t".  TTDate::getTimeUnit( $row[$column_id], 10 ) ."\t". $proj ."\t\tY\t0\n";
							unset($tmp_employee_name);
						}
					}
				}

				break;
			case 'surepayroll': //SurePayroll Export format.
				ksort($setup_data['surepayroll']['columns']);
				$setup_data['surepayroll']['columns'] = Misc::trimSortPrefix( $setup_data['surepayroll']['columns'] );

				//
				//header
				//
				$data = 'TC'."\n";
				$data .= '00001'."\n";

				$export_column_map = array(	'pay_period_end_date' => 'Entry Date',
											'employee_number' => 'Employee Number',
											'last_name' => 'Last Name',
											'first_name' => 'First Name',
											'hour_code' => 'Payroll Code',
											'value' => 'Hours' );

				foreach($rows as $row) {
					foreach( $setup_data['surepayroll']['columns'] as $column_id => $column_data ) {

						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							//Debug::Arr($column_data,'Output2', __FILE__, __LINE__, __METHOD__,10);
							$tmp_rows[] = array(
												'pay_period_end_date' => date('m/d/Y', $row['pay_period_end_date']),
												'employee_number' => $row['employee_number'],
												'last_name' => $row['last_name'],
												'first_name' => $row['first_name'],
												'hour_code' => trim($column_data['hour_code']),
												'value' => TTDate::getTimeUnit( $row[$column_id], 20 ),
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data .= Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
					$data = str_replace('"','', $data);
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;

			case 'chris21': //Chris21 Export format.
				//Columns required: Employee_number (2), Date (10), ADJUSTMENT_CODE (12), HOURS (13), SIGNED_HOURS(15)[?]
				//Use SIGNED_HOURS only, as it provides more space?
				//When using absences a leave start/end date must be specified other it won't be imported.
				ksort($setup_data['chris21']['columns']);
				$setup_data['chris21']['columns'] = Misc::trimSortPrefix( $setup_data['chris21']['columns'] );

				$data = '';
				foreach($rows as $row) {
					foreach( $setup_data['chris21']['columns'] as $column_id => $column_data ) {

						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							//Debug::Arr($column_data,'Output2: ID: '. $column_id, __FILE__, __LINE__, __METHOD__,10);
							$data .= str_repeat(' ', 8); 															//8 digits Blank
							$data .= str_pad( substr( $row['employee_number'], 0, 7), 7, ' ', STR_PAD_RIGHT); 		//7 digits
							$data .= str_repeat(' ', 11); 															//14 digits Blank
							$data .= date('dmy', $row['pay_period_end_date']);										//4 digits Date
							$data .= str_repeat(' ', 4); 															//4 digits Blank
							$data .= str_pad( substr( trim($column_data['hour_code']), 0, 4), 4, ' ', STR_PAD_RIGHT);//4 digits
							$data .= '0000'; 																		//4 digits HOURS field, always be 0, use SIGNED_HOURS instead.
							$data .= str_repeat(' ', 4); 															//CC_CODE: 4 digits Blank
							$data .= str_pad( str_replace('.','', TTDate::getTimeUnit( $row[$column_id], 20 ) ), 6, 0, STR_PAD_LEFT).'+'; //SIGNED_HOURS: Hours without decimal padded to 6 digits, with '+' on the end.
							//$data .= '+000000000'; 																	//Filler: Redefintion of SIGNED_HOURS.
							$data .= '000000000';																	//RATE: 9 chars
							$data .= str_repeat(' ', 20); 															//ACCT_NO: 20 chars
							$data .= str_repeat(' ', 16); 															//JOB_NUMBER: 16 chars
							if ( strpos( $column_id, 'absence' ) !== FALSE ) { //Absence column, include LEAVE dates.
								$data .= date('dmy', $row['pay_period_end_date']);										//LEAVE Start Date: 6 digits
								$data .= date('dmy', $row['pay_period_end_date']);										//LEAVE End Date: 6 digits
							}
							$data .= "\n";
						}
					}
				}
				unset($tmp_rows, $column_id, $column_data, $rows, $row);
				break;

			case 'csv': //Generic CSV.
				$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.csv';

				//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
				ksort($setup_data['csv']['columns']);
				$setup_data['csv']['columns'] = Misc::trimSortPrefix( $setup_data['csv']['columns'] );

				$export_column_map = array('employee' => '', 'employee_number' => '', 'default_branch' => '', 'default_department' => '', 'pay_period' => '', 'branch_name' => '', 'department_name' => '', 'hour_code' => '', 'hours' => '');

				$i=0;
				foreach($rows as $row) {
					if ( $i == 0 ) {
						//Include header.
						$tmp_rows[] = array(
											'employee' => 'Employee',
											'employee_number' => 'Employee Number',
											'default_branch' => 'Default Branch',
											'default_department' => 'Default Department',
											'pay_period' => 'Pay Period',
											'branch_name' => 'Branch',
											'department_name' => 'Department',
											'hour_code' => 'Hours Code',
											'hours' => 'Hours',
											);
					}

					//Combine all hours from the same code together.
					foreach( $setup_data['csv']['columns'] as $column_id => $column_data ) {
						$hour_code = trim($column_data['hour_code']);
						if ( isset( $row[$column_id] ) AND $hour_code != '' ) {
							if ( !isset($tmp_hour_codes[$hour_code]) ) {
								$tmp_hour_codes[$hour_code] = 0;
							}
							$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$column_data['hour_code']], $row[$column_id] ); //Use seconds for math here.
						}
					}

					if ( isset($tmp_hour_codes) ) {
						foreach($tmp_hour_codes as $hour_code => $hours ) {
							$tmp_rows[] = array(
												'employee' => ( isset($row['full_name']) ) ? $row['full_name'] : NULL,
												'employee_number' => ( isset($row['employee_number']) )? $row['employee_number'] : NULL,
												'default_branch' => ( isset($row['default_branch']) ) ? $row['default_branch'] : NULL,
												'default_department' => ( isset($row['default_department']) ) ? $row['default_department'] : NULL,
												'pay_period' => ( isset($row['pay_period']['display']) ) ? $row['pay_period']['display'] : NULL,
												'branch_name' => ( isset($row['branch_name']) ) ? $row['branch_name'] : NULL,
												'department_name' => ( isset($row['department_name']) ) ? $row['department_name'] : NULL,
												'hour_code' => $hour_code,
												'hours' => TTDate::getTimeUnit($hours, 20 ),
												);
						}
						unset($tmp_hour_codes, $hour_code, $hours);
					}

					$i++;
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'csv_advanced': //Generic CSV.
				unset($rows); //Ignore any existing timesheet summary data, we will be using our own job data below.

				//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...

				if ( !isset($setup_data['csv_advanced']['export_columns']) OR ( isset($setup_data['csv_advanced']['export_columns']) AND !is_array($setup_data['csv_advanced']['export_columns']) ) ) {
					$setup_data['csv_advanced']['export_columns'] = array(
																			'full_name',
																			'employee_number',
																			'default_branch',
																			'default_department',
																			'pay_period',
																			'date_stamp',
																		  );
				}

				if ( isset($setup_data['csv_advanced']['export_columns']) AND is_array($setup_data['csv_advanced']['export_columns']) ) {
					//Debug::Arr($setup_data['csv_advanced']['export_columns'], 'Custom Columns defined: ', __FILE__, __LINE__, __METHOD__,10);
					$config['columns'] = $config['group'] = $setup_data['csv_advanced']['export_columns'];

					//Force sorting...
					foreach( $setup_data['csv_advanced']['export_columns'] as $export_column ) {
						$config['sort'][] = array( $export_column => 'asc' );
					}
					
					$config['columns'] += array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
				}
				Debug::Arr($config, 'Job Detail Report Config: ', __FILE__, __LINE__, __METHOD__,10);

				//Get job data...
				if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCompanyObject() ) AND $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					Debug::Text('Using Job Detail Report...', __FILE__, __LINE__, __METHOD__,10);
					$jar = TTNew('JobDetailReport');
				} else {
					Debug::Text('Using TimeSheet Detail Report...', __FILE__, __LINE__, __METHOD__,10);
					$jar = TTNew('TimesheetDetailReport');
				}
				$jar->setAMFMessageID( $this->getAMFMessageID() );
				$jar->setUserObject( $this->getUserObject() );
				$jar->setPermissionObject( $this->getPermissionObject() );
				$jar->setConfig( $config );
				$jar->setFilterConfig( $this->getFilterConfig() );
				$jar->setSortConfig( $config['sort'] );
				$jar->_getData();
				$jar->_preProcess();
				$jar->group();
				$jar->sort();

				$columns = Misc::trimSortPrefix( $jar->getOptions('columns') );

				$rows = $jar->data;
				//Debug::Arr($rows, 'Raw Rows: ', __FILE__, __LINE__, __METHOD__,10);

				$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.csv';

				//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
				ksort($setup_data['csv_advanced']['columns']);
				$setup_data['csv_advanced']['columns'] = Misc::trimSortPrefix( $setup_data['csv_advanced']['columns'] );

				foreach( $setup_data['csv_advanced']['export_columns'] as $export_column ) {
					$export_column_map[$export_column] = '';
				}
				$export_column_map['hour_code'] = '';
				$export_column_map['hours'] = '';
				
				$i=0;
				foreach($rows as $row) {
					if ( $i == 0 ) {
						//Include header.
						foreach( $setup_data['csv_advanced']['export_columns'] as $export_column ) {
							Debug::Text('Header Row: '. $export_column, __FILE__, __LINE__, __METHOD__,10);
							$tmp_rows[$i][$export_column] = ( isset($columns[$export_column]) ) ? $columns[$export_column] : NULL;
						}
						$tmp_rows[$i]['hour_code'] = 'Hours Code';
						$tmp_rows[$i]['hours'] = 'Hours';
						
						$i++;
					}

					//Combine all hours from the same code together.
					foreach( $setup_data['csv_advanced']['columns'] as $column_id => $column_data ) {
						$hour_code = trim($column_data['hour_code']);
						if ( isset( $row[$column_id] ) AND $hour_code != '' ) {
							if ( !isset($tmp_hour_codes[$hour_code]) ) {
								$tmp_hour_codes[$hour_code] = 0;
							}
							$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$column_data['hour_code']], $row[$column_id] ); //Use seconds for math here.
						}
					}

					if ( isset($tmp_hour_codes) ) {
						foreach($tmp_hour_codes as $hour_code => $hours ) {
							foreach( $setup_data['csv_advanced']['export_columns'] as $export_column ) {
								$tmp_rows[$i][$export_column] = ( isset($row[$export_column]) ) ? ( isset($row[$export_column]['display']) ) ? $row[$export_column]['display'] : $row[$export_column] : NULL;
								$tmp_rows[$i]['hour_code'] = $hour_code;
								$tmp_rows[$i]['hours'] = TTDate::getTimeUnit($hours, 20);
							}
						}
						unset($tmp_hour_codes, $hour_code, $hours);
					}

					$i++;
				}
				//Debug::Arr($tmp_rows, 'Tmp Rows: ', __FILE__, __LINE__, __METHOD__,10);

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			default: //Send raw data so plugin can capture it and change it if needed.
				$data = $this->data;
				break;
		}

		//Debug::Arr($data, 'Export Data: ', __FILE__, __LINE__, __METHOD__,10);
		return array( 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $data );
	}

	function _output( $format = NULL ) {
		//Get Form Config data, which can use for the export config.
		if ( $format == 'payroll_export' ) {
			return $this->_outputPayrollExport( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
