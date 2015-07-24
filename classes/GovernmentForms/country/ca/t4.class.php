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


include_once( 'CA.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_CA_T4 extends GovernmentForms_CA {
	public $pdf_template = 't4flat-10b.pdf';

	public $template_offsets = array( -10, 0 );

	/*
	public $payment_cutoff_amount = 7000; //Line5
	*/

	function getOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
								'-1010-O' => TTi18n::getText('Original'),
								'-1020-A' => TTi18n::getText('Amended'),
								'-1030-C' => TTi18n::getText('Cancel'),
								);
				break;
			case 'type':
				$retval = array(
								'government' => TTi18n::gettext('Government (Multiple Employees/Page)'),
								'employee' => TTi18n::gettext('Employee (One Employee/Page)'),
								);
				break;
		}

		return $retval;
	}

	function getPayrollDeductionObject() {
		if ( !isset($this->payroll_deduction_obj) ) {
			require_once( Environment::getBasePath(). DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .'payroll_deduction'. DIRECTORY_SEPARATOR .'PayrollDeduction.class.php');
			$this->payroll_deduction_obj = new PayrollDeduction( 'CA', NULL);
			$this->payroll_deduction_obj->setDate( TTDate::getTimeStamp( $this->year, 12, 31 ) );
		}

		return $this->payroll_deduction_obj;
	}
	function getCPPMaximumEarnings() {
		return $this->getPayrollDeductionObject()->getCPPMaximumEarnings();
	}

	function getEIMaximumEarnings() {
		return $this->getPayrollDeductionObject()->getEIMaximumEarnings();
	}

	//Set the type of form to display/print. Typically this would be:
	// government or employee.
	function getType() {
		if ( isset($this->type) ) {
			return $this->type;
		}

		return FALSE;
	}
	function setType( $value ) {
		$this->type = trim($value);
		return TRUE;
	}

	//Set the submission status. Original, Amended, Cancel.
	function getStatus() {
		if ( isset($this->status) ) {
			return $this->status;
		}

		return 'O'; //Original
	}
	function setStatus( $value ) {
		$this->status = strtoupper( trim($value) );
		return TRUE;
	}

	function getShowInstructionPage() {
		if ( isset($this->show_instruction_page) ) {
			return $this->show_instruction_page;
		}

		return FALSE;
	}
	function setShowInstructionPage( $value ) {
		$this->show_instruction_page = (bool)trim($value);
		return TRUE;
	}

	public function getPreCalcFunction( $name ) {
		$variable_function_map = array(
										'l24' => 'preCalcL24',
										'l26' => 'preCalcL26',
										'ei_exempt' => 'preCalcEIExempt',
										'cpp_exempt' => 'preCalcCPPExempt',
										'ppip_exempt' => 'preCalcPPIPExempt',
						);

		if ( isset($variable_function_map[$name]) ) {
			return $variable_function_map[$name];
		}

		return FALSE;
	}

	public function getFilterFunction( $name ) {
		$variable_function_map = array(
										'year' => 'isNumeric',
										//'ein' => array( 'stripNonNumeric', 'isNumeric'),
						);

		if ( isset($variable_function_map[$name]) ) {
			return $variable_function_map[$name];
		}

		return FALSE;
	}

	public function getTemplateSchema( $name = NULL ) {
		$template_schema = array(

								'year' => array(
										'page' => 1,
										'template_page' => 1,
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 349,
															'y' => 37,
															'h' => 17,
															'w' => 57,
															'halign' => 'C',
															//'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 14,
																'type' => 'B' )
									),

								//Company information
								'company_name' => array(
										'coordinates' => array(
															'x' => 35,
															'y' => 52,
															'h' => 12,
															'w' => 210,
															'halign' => 'L',
															),
										'font' => array(
																'size' => 8,
																'type' => 'B' )
									),
								'employment_province' => array( //Province of employment
										'coordinates' => array(
															'x' => 297,
															'y' => 109,
															'h' => 18,
															'w' => 28,
															'halign' => 'C',
															),
									),
								'payroll_account_number' => array(
										'function' => array('filterPayrollAccountNumber', 'drawNormal' ),
										'coordinates' => array(
															'x' => 52,
															'y' => 110,
															'h' => 17,
															'w' => 214,
															'halign' => 'L',
															),
										'font' => array(
																'size' => 8,
																'type' => '' )
									),

								//Employee information.
								'sin' => array(
										'coordinates' => array(
															'x' => 52,
															'y' => 145,
															'h' => 17,
															'w' => 120,
															'halign' => 'C',
															),
									),
								'cpp_exempt' => array(
										'function' => array( 'drawCheckBox' ),
										'coordinates' => array(
																array(
																	'x' => 202,
																	'y' => 145,
																	'h' => 18,
																	'w' => 15,
																	'halign' => 'C',
																)
															),
									),
								'ei_exempt' => array(
										'function' => array( 'drawCheckBox' ),
										'coordinates' => array(
																array(
																	'x' => 226,
																	'y' => 145,
																	'h' => 18,
																	'w' => 15,
																	'halign' => 'C',
																)
															),
									),
								'ppip_exempt' => array(
										'function' => array( 'drawCheckBox' ),
										'coordinates' => array(
																array(
																	'x' => 252,
																	'y' => 145,
																	'h' => 18,
																	'w' => 15,
																	'halign' => 'C',
																)
															),
									),
								'employment_code' => array(
										'coordinates' => array(
															'x' => 296,
															'y' => 145,
															'h' => 18,
															'w' => 29,
															'halign' => 'C',
															),
									),
								'last_name' => array(
										'coordinates' => array(
															'x' => 49,
															'y' => 197,
															'h' => 14,
															'w' => 170,
															'halign' => 'L',
															),
									),
								'first_name' => array(
										'coordinates' => array(
															'x' => 222,
															'y' => 197,
															'h' => 14,
															'w' => 60,
															'halign' => 'L',
															),
									),
								'middle_name' => array(
										'function' => array('filterMiddleName', 'drawNormal' ),
										'coordinates' => array(
															'x' => 290,
															'y' => 197,
															'h' => 14,
															'w' => 30,
															'halign' => 'R',
															),
									),

								'address' => array(
										'function' => array('filterAddress', 'drawNormal' ),
										'coordinates' => array(
															'x' => 49,
															'y' => 215,
															'h' => 42,
															'w' => 270,
															'halign' => 'L',
															),
										'font' => array(
																'size' => 8,
																'type' => '' ),
										'multicell' => TRUE,
									),
								'l14' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 320,
																		'y' => 72.5,
																		'h' => 18,
																		'w' => 98,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 418,
																		'y' => 72.5,
																		'h' => 18,
																		'w' => 33,
																		'halign' => 'C',
																		),
																	),
											),
								'l16' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 348,
																		'y' => 109,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 418,
																		'y' => 109,
																		'h' => 18,
																		'w' => 33,
																		'halign' => 'C',
																		),
																	),
											),
								'l17' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 348,
																		'y' => 145,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 418,
																		'y' => 145,
																		'h' => 18,
																		'w' => 33,
																		'halign' => 'C',
																		),
																	),
											),
								'l18' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 348,
																		'y' => 180,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 418,
																		'y' => 180,
																		'h' => 18,
																		'w' => 33,
																		'halign' => 'C',
																		),
																	),
											),
								'l20' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 348,
																		'y' => 217,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 418,
																		'y' => 217,
																		'h' => 18,
																		'w' => 33,
																		'halign' => 'C',
																		),
																	),
											),
								'l52' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 348,
																		'y' => 253,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 418,
																		'y' => 253,
																		'h' => 18,
																		'w' => 33,
																		'halign' => 'C',
																		),
																	),
											),
								'l55' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 348,
																		'y' => 290,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 418,
																		'y' => 290,
																		'h' => 18,
																		'w' => 33,
																		'halign' => 'C',
																		),
																	),
											),

								'l22' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 470,
																		'y' => 72.5,
																		'h' => 18,
																		'w' => 83,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 553,
																		'y' => 72.5,
																		'h' => 18,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
								'l24' => array(
												'function' => array( 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 483,
																		'y' => 109,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 553,
																		'y' => 109,
																		'h' => 18,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
								'l26' => array(
												'function' => array( 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 483,
																		'y' => 145,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 553,
																		'y' => 145,
																		'h' => 18,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
								'l44' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 483,
																		'y' => 180,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 553,
																		'y' => 180,
																		'h' => 18,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
								'l46' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 483,
																		'y' => 217,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 553,
																		'y' => 217,
																		'h' => 18,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
								'l50' => array(
												'function' => 'drawNormal',
												'coordinates' => array(
																		'x' => 483,
																		'y' => 253,
																		'h' => 18,
																		'w' => 103,
																		'halign' => 'R',
																		),
											),
								'l56' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 483,
																		'y' => 290,
																		'h' => 18,
																		'w' => 70,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 553,
																		'y' => 290,
																		'h' => 18,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),

								'other_box_0_code' => array(
										'coordinates' => array(
															'x' => 106,
															'y' => 325,
															'h' => 16,
															'w' => 27,
															'halign' => 'C',
															),
									),
								'other_box_0' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 142,
																		'y' => 325,
																		'h' => 16,
																		'w' => 84,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 226,
																		'y' => 325,
																		'h' => 16,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
								'other_box_1_code' => array(
										'coordinates' => array(
															'x' => 268,
															'y' => 325,
															'h' => 16,
															'w' => 27,
															'halign' => 'C',
															),
									),
								'other_box_1' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 304,
																		'y' => 325,
																		'h' => 16,
																		'w' => 84,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 388,
																		'y' => 325,
																		'h' => 16,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
								'other_box_2_code' => array(
										'coordinates' => array(
															'x' => 430,
															'y' => 325,
															'h' => 16,
															'w' => 27,
															'halign' => 'C',
															),
									),
								'other_box_2' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 466,
																		'y' => 325,
																		'h' => 16,
																		'w' => 84,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 550,
																		'y' => 325,
																		'h' => 16,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
								'other_box_3_code' => array(
										'coordinates' => array(
															'x' => 106,
															'y' => 357,
															'h' => 16,
															'w' => 27,
															'halign' => 'C',
															),
									),
								'other_box_3' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 142,
																		'y' => 357,
																		'h' => 16,
																		'w' => 84,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 226,
																		'y' => 357,
																		'h' => 16,
																		'w' => 30,
																		'halign' => 'C',
																		),
																	),
											),
								'other_box_4_code' => array(
										'coordinates' => array(
															'x' => 268,
															'y' => 357,
															'h' => 16,
															'w' => 27,
															'halign' => 'C',
															),
									),
								'other_box_4' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 304,
																		'y' => 357,
																		'h' => 16,
																		'w' => 84,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 388,
																		'y' => 357,
																		'h' => 16,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
								'other_box_5_code' => array(
										'coordinates' => array(
															'x' => 430,
															'y' => 357,
															'h' => 16,
															'w' => 27,
															'halign' => 'C',
															),
									),
								'other_box_5' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 466,
																		'y' => 357,
																		'h' => 16,
																		'w' => 84,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 550,
																		'y' => 357,
																		'h' => 16,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
											),
							);

		if ( isset($template_schema[$name]) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function preCalcL24( $value, $key, &$array ) {
		Debug::Text('EI Earning: '. $value .' Maximum: '. $this->getEIMaximumEarnings(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $value > $this->getEIMaximumEarnings() ) {
			return $this->getEIMaximumEarnings();
		}

		return $value;
	}
	function preCalcL26( $value, $key, &$array ) {
		if ( $value > $this->getCPPMaximumEarnings() ) {
			$value = $this->getCPPMaximumEarnings();
		}

		return $value;
	}
	function preCalcEIExempt( $value, $key, &$array ) {
		if ( $value == TRUE ) {
			$array['l24'] = 0;
		}

		return $value;
	}
	function preCalcCPPExempt( $value, $key, &$array ) {
		if ( $value == TRUE ) {
			$array['l26'] = 0;
		}

		return $value;
	}
	function preCalcPPIPExempt( $value, $key, &$array ) {
		if ( $value == TRUE ) {
			$array['l56'] = 0;
		}

		return $value;
	}

	function _outputXML() {
		//Maps other income box codes to XML element names.
		$other_box_code_map = array(
									30 => 'hm_brd_lodg_amt',
									31 => 'spcl_wrk_site_amt',
									32 => 'prscb_zn_trvl_amt',
									33 => 'med_trvl_amt',
									34 => 'prsnl_vhcl_amt',
									35 => 'rsn_per_km_amt',
									36 => 'low_int_loan_amt',
									37 => 'empe_hm_loan_amt',
									38 => 'sob_a00_feb_amt',
									39 => 'sod_d_a00_feb',
									40 => 'oth_tx_ben_amt',
									41 => 'sod_d1_a00_feb_amt',
									42 => 'empt_cmsn_amt',
									43 => 'cfppa_amt',
									53 => 'dfr_sob_amt',
									66 => 'elg_rtir_amt',
									67 => 'nelg_rtir_amt',
									68 => 'indn_elg_rtir_amt',
									69 => 'indn_nelg_rtir_amt',
									70 => 'mun_ofcr_examt',
									71 => 'indn_empe_amt',
									72 => 'oc_incamt',
									73 => 'oc_dy_cnt',
									74 => 'pr_90_cntrbr_amt',
									75 => 'pr_90_ncntrbr_amt',
									77 => 'cmpn_rpay_empr_amt',
									78 => 'fish_gro_ern_amt',
									79 => 'fish_net_ptnr_amt',
									80 => 'fish_shr_prsn_amt',
									81 => 'plcmt_emp_agcy_amt',
									82 => 'drvr_taxis_oth_amt',
									83 => 'brbr_hrdrssr_amt',
									84 => 'pub_trnst_pass',
									85 => 'epaid_hlth_pln_amt',
									86 => 'stok_opt_csh_out_eamt',
									);

		if ( is_object( $this->getXMLObject() ) ) {
			$xml = $this->getXMLObject();
		} else {
			return FALSE; //No XML object to append too. Needs T619 form first.
		}

		$xml->Return->addChild('T4');

		$records = $this->getRecords();
		if ( is_array($records) AND count($records) > 0 ) {

			$e = 0;
			foreach( $records as $employee_data ) {
				//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
				$this->arrayToObject( $employee_data ); //Convert record array to object

				$xml->Return->T4->addChild('T4Slip');

				$xml->Return->T4->T4Slip[$e]->addChild('EMPE_NM'); //Employee name
				$xml->Return->T4->T4Slip[$e]->EMPE_NM->addChild('snm', $this->last_name ); //Surname
				$xml->Return->T4->T4Slip[$e]->EMPE_NM->addChild('gvn_nm', substr( $this->first_name, 0, 12 ) ); //Given name
				if ( $this->filterMiddleName($this->middle_name) != '' ) { $xml->Return->T4->T4Slip[$e]->EMPE_NM->addChild('init', $this->filterMiddleName($this->middle_name) ); }

				$xml->Return->T4->T4Slip[$e]->addChild('EMPE_ADDR'); //Employee Address
				if ( $this->address1 != '' ) { $xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild('addr_l1_txt', substr( Misc::stripHTMLSpecialChars( $this->address1 ), 0, 30) ); }
				if ( $this->address2 != '' ) { $xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild('addr_l2_txt', substr( Misc::stripHTMLSpecialChars( $this->address2 ), 0, 30) ); }
				if ( $this->city != '' ) { $xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild('cty_nm', $this->city ); }
				if ( $this->province != '' ) { $xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild('prov_cd', $this->province ); }
				$xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild('cntry_cd', 'CAN' );
				if ( $this->postal_code != '' ) { $xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild('pstl_cd', $this->postal_code ); }

				$xml->Return->T4->T4Slip[$e]->addChild('sin', ( $this->sin != '' ) ? $this->sin : '000000000' ); //Required
				if ( $this->employee_number != '' ) {  $xml->Return->T4->T4Slip[$e]->addChild('empe_nbr', substr( $this->employee_number, 0, 20) ); }
				$xml->Return->T4->T4Slip[$e]->addChild('bn', str_replace(' ', '', $this->payroll_account_number ) ); //Payroll Account Number. Remove any spaces from the number.
				if ( isset($this->l50) AND $this->l50 != '' ) { $xml->Return->T4->T4Slip[$e]->addChild('rpp_dpsp_rgst_nbr', substr( $this->l50, 0, 7) ); }

				$xml->Return->T4->T4Slip[$e]->addChild('cpp_qpp_xmpt_cd', (int)$this->cpp_exempt ); //CPP Exempt
				$xml->Return->T4->T4Slip[$e]->addChild('ei_xmpt_cd', (int)$this->ei_exempt ); //EI Exempt
				//$xml->Return->T4->T4Slip[$e]->addChild('rpt_tcd', 'O' ); //Report Type Code: O = Originals, A = Amendment, C = Cancel
				$xml->Return->T4->T4Slip[$e]->addChild('rpt_tcd', $this->getStatus() ); //Report Type Code: O = Originals, A = Amendment, C = Cancel
				$xml->Return->T4->T4Slip[$e]->addChild('empt_prov_cd', $this->employment_province );
				//$xml->Return->T4->T4Slip[$e]->addChild('rpp_dpsp_rgst_nbr', $this->l50 ); //Box 50: RPP Registration number
				//$xml->Return->T4->T4Slip[$e]->addChild('prov_ppip_xmpt_cd', '' ); //PPIP Exempt
				//$xml->Return->T4->T4Slip[$e]->addChild('empt_cd', '' ); //Box 29: Employment Code

				$xml->Return->T4->T4Slip[$e]->addChild('T4_AMT'); //T4 Amounts

				if ( isset($this->l14) AND is_numeric($this->l14) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('empt_incamt', $this->MoneyFormat( (float)$this->l14, FALSE ) ); }
				if ( isset($this->l16) AND is_numeric($this->l16) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('cpp_cntrb_amt', $this->MoneyFormat( (float)$this->l16, FALSE ) ); }
				//$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('qpp_cntrb_amt', $this->MoneyFormat( $this->l17, FALSE ) );
				if ( isset($this->l18) AND is_numeric($this->l18) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('empe_eip_amt', $this->MoneyFormat( (float)$this->l18, FALSE ) ); }
				if ( isset($this->l20) AND is_numeric($this->l20) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('rpp_cntrb_amt', $this->MoneyFormat( (float)$this->l20, FALSE ) ); }
				if ( isset($this->l22) AND is_numeric($this->l22) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('itx_ddct_amt', $this->MoneyFormat( (float)$this->l22, FALSE ) ); }

				if ( $this->ei_exempt == FALSE AND isset($this->l24) AND is_numeric($this->l24) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('ei_insu_ern_amt', $this->MoneyFormat( (float)$this->l24, FALSE ) ); }
				if ( $this->cpp_exempt == FALSE AND isset($this->l26) AND is_numeric($this->l26) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('cpp_qpp_ern_amt', $this->MoneyFormat( (float)$this->l26, FALSE ) ); }
				if ( isset($this->l44) AND is_numeric($this->l44) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('unn_dues_amt', $this->MoneyFormat( (float)$this->l44, FALSE ) ); }
				if ( isset($this->l46) AND is_numeric($this->l46) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('chrty_dons_amt', $this->MoneyFormat( (float)$this->l46, FALSE ) ); }
				if ( isset($this->l52) AND is_numeric($this->l52) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('padj_amt', $this->MoneyFormat( (float)$this->l52, FALSE ) ); }
				if ( isset($this->l55) AND is_numeric($this->l55) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('prov_pip_amt', $this->MoneyFormat( (float)$this->l55, FALSE ) ); }
				if ( isset($this->l56) AND is_numeric($this->l56) ) { $xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('prov_insu_ern_amt', $this->MoneyFormat( (float)$this->l56, FALSE ) ); }

				$xml->Return->T4->T4Slip[$e]->addChild('OTH_INFO'); //Other Income Fields
				for( $i = 0; $i <= 6; $i++ ) {
					if ( isset($this->{'other_box_'.$i.'_code'}) AND isset($other_box_code_map[$this->{'other_box_'.$i.'_code'}]) ) {
						$xml->Return->T4->T4Slip[$e]->OTH_INFO->addChild($other_box_code_map[$this->{'other_box_'.$i.'_code'}], $this->MoneyFormat( (float)$this->{'other_box_'.$i}, FALSE ) );
					}
				}

				$e++;
			}
		}

		return TRUE;
	}

	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == TRUE ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[1] = $pdf->ImportPage(1);
			$this->template_index[2] = $pdf->ImportPage(2);
			//$this->template_index[3] = $pdf->ImportPage(3);
		}

		if ( $this->year == ''  ) {
			$this->year = $this->getYear();
		}

		if ( $this->getType() == 'government') {
			$employees_per_page = 2;
			$n = 1; //Don't loop the same employee.
		} else {
			$employees_per_page = 1;
			$n = 2; //Loop the same employee twice.
		}

		//Get location map, start looping over each variable and drawing
		$records = $this->getRecords();
		if ( is_array($records) AND count($records) > 0 ) {

			$template_schema = $this->getTemplateSchema();

			$e = 0;
			foreach( $records as $employee_data ) {
				//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
				$this->arrayToObject( $employee_data ); //Convert record array to object

				$template_page = NULL;

				for( $i = 0; $i < $n; $i++ ) {
					$this->page_offsets = array(0,0);

					if ( ( $employees_per_page == 1 AND $i > 0 )
							OR ( $employees_per_page == 2 AND $e % 2 != 0 )
							) {
						$this->page_offsets = array(0,394);
					}

					foreach( $template_schema as $field => $schema ) {
						$this->Draw( $this->$field, $schema );
					}
				}

				if ( $employees_per_page == 1 OR ( $employees_per_page == 2 AND $e % $employees_per_page != 0 ) ) {
					$this->resetTemplatePage();
					if ( $this->getShowInstructionPage() == TRUE ) {
						$this->addPage( array('template_page' => 2) );
					}
				}
				$e++;
			}
		}

		$this->clearRecords();

		return TRUE;
	}
}
?>