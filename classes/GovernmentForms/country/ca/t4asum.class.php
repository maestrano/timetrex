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
class GovernmentForms_CA_T4ASum extends GovernmentForms_CA {
	public $pdf_template = 't4a-sum-11b.pdf';

	public $template_offsets = array( -10, 0 );

	//Set the submission status. Original, Amended, Cancel.
	function getStatus() {
		if ( isset($this->status) ) {
			return $this->status;
		}

		return 'O'; //Original
	}
	function setStatus( $value ) {
		if ( strtoupper($value) == 'C' ) {
			$value = 'A'; //Cancel isn't valid for this, only original and amendment.
		}
		$this->status = strtoupper( trim($value) );
		return TRUE;
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
															'x' => 162,
															'y' => 51,
															'h' => 19,
															'w' => 59,
															'halign' => 'C',
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 14,
																'type' => 'B' )
									),

								//Company information
								'company_name' => array(
										'coordinates' => array(
															'x' => 250,
															'y' => 125,
															'h' => 12,
															'w' => 210,
															'halign' => 'L',
															),
										'font' => array(
																'size' => 8,
																'type' => 'B' )
									),
								'company_address' => array(
										'function' => array('filterCompanyAddress', 'drawNormal' ),
										'coordinates' => array(
															'x' => 250,
															'y' => 139,
															'h' => 12,
															'w' => 210,
															'halign' => 'L',
															),
										'font' => array(
																'size' => 8,
																'type' => '' ),
										'multicell' => TRUE,
									),
								'payroll_account_number' => array(
										'coordinates' => array(
															'x' => 250,
															'y' => 95,
															'h' => 17,
															'w' => 214,
															'halign' => 'L',
															),
										'font' => array(
																'size' => 8,
																'type' => '' )
									),

								'l88' => array(
										'coordinates' => array(
															'x' => 435,
															'y' => 243,
															'h' => 16,
															'w' => 92,
															'halign' => 'R',
															),
									),

								'l16' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 260,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 260,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l18' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 273,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 273,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l20' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 286,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 286,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l24' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 299,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 299,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l28' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 312,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 312,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l30' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 325,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 325,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l32' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 338,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 338,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l34' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 351,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 351,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l40' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 364,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 364,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l42' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 377,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 377,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l48' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 390,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 390,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l101' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 435,
																		'y' => 403,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 507,
																		'y' => 403,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),

								'l22' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 455,
																		'y' => 422,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 527,
																		'y' => 422,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l82' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 455,
																		'y' => 442,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 527,
																		'y' => 442,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'l82_diff' => array(
												'function' => array( 'calcL82Diff', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 455,
																		'y' => 465,
																		'h' => 13,
																		'w' => 72,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 527,
																		'y' => 465,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'amount_enclosed' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 410,
																		'y' => 508,
																		'h' => 13,
																		'w' => 90,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 500,
																		'y' => 508,
																		'h' => 13,
																		'w' => 25,
																		'halign' => 'C',
																		),
																	),
											),
								'l84' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 92,
																		'y' => 508,
																		'h' => 13,
																		'w' => 90,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 182,
																		'y' => 508,
																		'h' => 13,
																		'w' => 25,
																		'halign' => 'C',
																		),
																	),
											),
								'l86' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 260,
																		'y' => 508,
																		'h' => 13,
																		'w' => 90,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 350,
																		'y' => 508,
																		'h' => 13,
																		'w' => 25,
																		'halign' => 'C',
																		),
																	),
											),
								'l76' => array(
												'coordinates' => array(
																	'x' => 55,
																	'y' => 591,
																	'h' => 13,
																	'w' => 230,
																	'halign' => 'R',
																	),
											),
								'l78' => array(
												'function' => array('filterL78', 'drawSegments'),
												'coordinates' => array(
																	array(
																		'x' => 335,
																		'y' => 591,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	array(
																		'x' => 385,
																		'y' => 591,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	array(
																		'x' => 440,
																		'y' => 591,
																		'h' => 13,
																		'w' => 20,
																		'halign' => 'C',
																		),
																	),
											),
								'date' => array(
												'value' => date( 'd-M-Y' ),
												'coordinates' => array(
																	'x' => 50,
																	'y' => 642,
																	'h' => 18,
																	'w' => 110,
																	'halign' => 'C',
																	),
											),
							  );

		if ( isset($template_schema[$name]) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function filterL78( $value ) {
		//Strip non-digits.
		$value = $this->stripNonNumeric($value);

		return array( substr($value, 0, 3), substr($value, 3, 3), substr($value, 6, 4) );
	}

	function calcL82Diff( $value, $schema ) {
		//Subtotal: 22 - 82
		$this->l82_diff = $this->l22 - $this->l82;

		if ( $this->l82_diff > 0 ) {
			$this->l86 = $this->amount_enclosed = $this->l82_diff;
		} else {
			$this->l84 = abs($this->l82_diff);
			unset($this->amount_enclosed);
		}
		return $this->l82_diff;
	}

	function _outputXML() {
		if ( is_object( $this->getXMLObject() ) ) {
			$xml = $this->getXMLObject();
		} else {
			return FALSE; //No XML object to append too. Needs T619 form first.
		}

		$xml->Return->T4A->addChild('T4ASummary');

		$xml->Return->T4A->T4ASummary->addChild('bn', $this->formatPayrollAccountNumber( $this->payroll_account_number ) );
		$xml->Return->T4A->T4ASummary->addChild('tx_yr', $this->year );
		$xml->Return->T4A->T4ASummary->addChild('slp_cnt', $this->l88 );
		$xml->Return->T4A->T4ASummary->addChild('rpt_tcd', $this->getStatus() ); //Report Type Code: O = Originals, A = Amendment, C = Cancel

		$xml->Return->T4A->T4ASummary->addChild('PAYR_NM'); //Employer name
		$xml->Return->T4A->T4ASummary->PAYR_NM->addChild('l1_nm', substr( Misc::stripHTMLSpecialChars( $this->company_name ), 0, 30)  );

		$xml->Return->T4A->T4ASummary->addChild('PAYR_ADDR'); //Employer Address
		$xml->Return->T4A->T4ASummary->PAYR_ADDR->addChild('addr_l1_txt', Misc::stripHTMLSpecialChars( $this->company_address1 ) );
		if ( $this->company_address2 != '' ) { $xml->Return->T4A->T4ASummary->PAYR_ADDR->addChild('addr_l2_txt', Misc::stripHTMLSpecialChars( $this->company_address2 ) ); }
		$xml->Return->T4A->T4ASummary->PAYR_ADDR->addChild('cty_nm', $this->company_city );
		$xml->Return->T4A->T4ASummary->PAYR_ADDR->addChild('prov_cd', $this->company_province );
		$xml->Return->T4A->T4ASummary->PAYR_ADDR->addChild('cntry_cd', 'CAN' );
		$xml->Return->T4A->T4ASummary->PAYR_ADDR->addChild('pstl_cd', $this->company_postal_code );

		$xml->Return->T4A->T4ASummary->addChild('CNTC'); //Contact Name
		$xml->Return->T4A->T4ASummary->CNTC->addChild('cntc_nm', $this->l76 );
		$phone_arr = $this->filterL78( $this->l78 );
		if ( is_array($phone_arr) ) {
			$xml->Return->T4A->T4ASummary->CNTC->addChild('cntc_area_cd', $phone_arr[0] );
			$xml->Return->T4A->T4ASummary->CNTC->addChild('cntc_phn_nbr', $phone_arr[1].'-'.$phone_arr[2] );
			//$xml->Return->T4A->T4ASummary->CNTC->addChild('cntc_extn_nbr', '' );
		}

		$xml->Return->T4A->T4ASummary->addChild('T4A_TAMT');
		$xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('tot_pens_spran_amt', $this->MoneyFormat( $this->l16, FALSE ) );
		$xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('tot_lsp_amt', $this->MoneyFormat( $this->l18, FALSE ) );
		$xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('tot_self_cmsn_amt', $this->MoneyFormat( $this->l20, FALSE ) );

		( isset($this->l30) ) ? $xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('tot_ptrng_aloc_amt', $this->MoneyFormat( $this->l30, FALSE ) ) : NULL;
		( isset($this->l32) ) ? $xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('tot_past_srvc_amt', $this->MoneyFormat( $this->l32, FALSE ) ) : NULL;
		( isset($this->l34) ) ? $xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('tot_padj_amt', $this->MoneyFormat( $this->l34, FALSE ) ) : NULL;
		( isset($this->l42) ) ? $xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('tot_resp_aip_amt', $this->MoneyFormat( $this->l42, FALSE ) ) : NULL;

		$xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('tot_itx_dedn_amt', $this->MoneyFormat( $this->l22, FALSE ) );
		$xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('tot_annty_incamt', $this->MoneyFormat( $this->l24, FALSE ) );
		$xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('rpt_tot_fee_srvc_amt', $this->MoneyFormat( $this->l48, FALSE ) );
		$xml->Return->T4A->T4ASummary->T4A_TAMT->addChild('rpt_tot_oth_info_amt', $this->MoneyFormat( $this->l101, FALSE ) );

		return TRUE;
	}

	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == TRUE ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[1] = $pdf->ImportPage(1);
		}

		if ( $this->year == ''  ) {
			$this->year = $this->getYear();
		}

		//Get location map, start looping over each variable and drawing
		$template_schema = $this->getTemplateSchema();
		if ( is_array( $template_schema) ) {

			$template_page = NULL;

			foreach( $template_schema as $field => $schema ) {
				Debug::text('Drawing Cell... Field: '. $field, __FILE__, __LINE__, __METHOD__, 10);
				$this->Draw( $this->$field, $schema );
			}
		}

		return TRUE;
	}
}
?>