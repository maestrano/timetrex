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
 * $Revision: 2286 $
 * $Id: CA.class.php 2286 2008-12-12 23:12:41Z ipso $
 * $Date: 2008-12-12 15:12:41 -0800 (Fri, 12 Dec 2008) $
 */

include_once( 'US.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_US_W3 extends GovernmentForms_US {
	public $pdf_template = 'w3.pdf';

	public $template_offsets = array( 0, 0 );

	public function getFilterFunction( $name ) {
		$variable_function_map = array(
										'year' => 'isNumeric',
										'ein' => array( 'stripNonNumeric', 'isNumeric'),
						  );

		if ( isset($variable_function_map[$name]) ) {
			return $variable_function_map[$name];
		}

		return FALSE;
	}

	public function getTemplateSchema( $name = NULL ) {
		$template_schema = array(
								array(
										'page' => 1,
										'template_page' => 2,
										'value' => $this->year,
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 360,
															'y' => 410,
															'h' => 20,
															'w' => 120,
															'halign' => 'C',
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 18,
																'type' => 'B' )
								),
								array(
										'value' => $this->year,
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 151,
															'y' => 481,
															'h' => 10,
															'w' => 20,
															'halign' => 'C',
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 8,
																'type' => '' )
								),
								array(
										'value' => $this->year+1,
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 533,
															'y' => 589,
															'h' => 10,
															'w' => 20,
															'halign' => 'C',
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 8,
																'type' => '' )
								),

								//Finish initializing page 1.

								'control_number' => array(
												'function' => array('filterControlNumber', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 100,
																		'y' => 45,
																		'h' => 15,
																		'w' => 110,
																		'halign' => 'C',
																		),
											),
								'kind_of_payer' => array(
												'function' => 'drawCheckBox',
												'coordinates' => array(
																		'941' => array(
																			'x' => 122,
																			'y' => 72,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																		'military' => array(
																			'x' => 158,
																			'y' => 72,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																		'943' => array(
																			'x' => 194,
																			'y' => 72,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																		'944' => array(
																			'x' => 230,
																			'y' => 72,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),

																		'ct-1' => array(
																			'x' => 122,
																			'y' => 96,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																		'hshld' => array(
																			'x' => 158,
																			'y' => 96,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																		'medicare' => array(
																			'x' => 194,
																			'y' => 96,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																	  ),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )
											),
								'kind_of_employer' => array(
												'function' => 'drawCheckBox',
												'coordinates' => array(
																		'none' => array(
																			'x' => 367,
																			'y' => 72,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																		'501c' => array(
																			'x' => 418,
																			'y' => 72,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																		'state/nonlocal' => array(
																			'x' => 367,
																			'y' => 96,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																		'state/local' => array(
																			'x' => 418,
																			'y' => 96,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),
																		'federal' => array(
																			'x' => 475,
																			'y' => 96,
																			'h' => 10,
																			'w' => 11,
																			'halign' => 'C',
																			),

																	  ),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )
											),
								'third_party_sick_pay' => array(
												'function' => 'drawCheckBox',
												'coordinates' => array(
																		array(
																		'x' => 540,
																		'y' => 96,
																		'h' => 10,
																		'w' => 11,
																		'halign' => 'C',
																		),
																),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )
											),

								'lc' => array( //Total W2 forms
												'coordinates' => array(
																		'x' => 38,
																		'y' => 117,
																		'h' => 15,
																		'w' => 110,
																		'halign' => 'C',
																		),
											),
								'ld' => array( //Establishment Number
												'coordinates' => array(
																		'x' => 152,
																		'y' => 117,
																		'h' => 15,
																		'w' => 110,
																		'halign' => 'C',
																		),
											),
								'ein' => array(
												'coordinates' => array(
																		'x' => 38,
																		'y' => 140,
																		'h' => 15,
																		'w' => 220,
																		'halign' => 'L',
																		),
											),
								'trade_name' => array(
												'coordinates' => array(
																		'x' => 38,
																		'y' => 165,
																		'h' => 15,
																		'w' => 220,
																		'halign' => 'L',
																		),
											),
								'company_address' => array(
												'function' => array('filterCompanyAddress', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 38,
																		'y' => 182,
																		'h' => 48,
																		'w' => 220,
																		'halign' => 'L',
																		),
												'font' => array(
																		'size' => 8,
																		'type' => '' ),
												'multicell' => TRUE,
											),
								'other_ein' => array(
												'coordinates' => array(
																		'x' => 38,
																		'y' => 260,
																		'h' => 15,
																		'w' => 220,
																		'halign' => 'L',
																		),
											),
								'company_state' => array(
												'coordinates' => array(
																		'x' => 38,
																		'y' => 285,
																		'h' => 15,
																		'w' => 40,
																		'halign' => 'L',
																		),
											),
								'company_state' => array(
												'coordinates' => array(
																		'x' => 38,
																		'y' => 285,
																		'h' => 15,
																		'w' => 40,
																		'halign' => 'L',
																		),
											),
								'state_id1' => array(
												'coordinates' => array(
																		'x' => 80,
																		'y' => 285,
																		'h' => 15,
																		'w' => 180,
																		'halign' => 'L',
																		),
											),

								'contact_name' => array(
												'coordinates' => array(
																		'x' => 38,
																		'y' => 332,
																		'h' => 15,
																		'w' => 220,
																		'halign' => 'L',
																		),
											),
								'contact_phone' => array(
												'coordinates' => array(
																		'x' => 270,
																		'y' => 332,
																		'h' => 15,
																		'w' => 150,
																		'halign' => 'L',
																		),
											),
								'contact_email' => array(
												'coordinates' => array(
																		'x' => 38,
																		'y' => 356,
																		'h' => 15,
																		'w' => 220,
																		'halign' => 'L',
																		),
											),
								'contact_fax' => array(
												'coordinates' => array(
																		'x' => 270,
																		'y' => 356,
																		'h' => 15,
																		'w' => 150,
																		'halign' => 'L',
																		),
											),
								'l1' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 267,
																		'y' => 117,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l2' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 422,
																		'y' => 117,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l3' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 267,
																		'y' => 140,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l4' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 422,
																		'y' => 140,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l5' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 267,
																		'y' => 165,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l6' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 422,
																		'y' => 165,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l7' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 267,
																		'y' => 189,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l8' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 422,
																		'y' => 189,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l9' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 267,
																		'y' => 213,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l10' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 422,
																		'y' => 213,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l11' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 267,
																		'y' => 236,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l12a' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 422,
																		'y' => 236,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l12b' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 422,
																		'y' => 261,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l13' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 267,
																		'y' => 261,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l14' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 267,
																		'y' => 284,
																		'h' => 15,
																		'w' => 309,
																		'halign' => 'R',
																		),
											),
								'l16' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 38,
																		'y' => 309,
																		'h' => 15,
																		'w' => 113,
																		'halign' => 'R',
																		),
											),
								'l17' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 152,
																		'y' => 309,
																		'h' => 15,
																		'w' => 113,
																		'halign' => 'R',
																		),
											),
								'l18' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 267,
																		'y' => 309,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
								'l19' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 422,
																		'y' => 309,
																		'h' => 15,
																		'w' => 154,
																		'halign' => 'R',
																		),
											),
							  );

		if ( isset($template_schema[$name]) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function filterCompanyAddress( $value ) {
		Debug::Text('Filtering company address: '. $value, __FILE__, __LINE__, __METHOD__,10);

		//Combine company address for multicell display.
		$retarr[] = $this->company_address1;
		if ( $this->company_address2 != '' ) {
			$retarr[] = $this->company_address2;
		}
		$retarr[] = $this->company_city. ', '.$this->company_state . ' ' . $this->company_zip_code;

		return implode("\n", $retarr );
	}

	function filterControlNumber( $value ) {
		$value = str_pad( $value, 4, 0, STR_PAD_LEFT );
		return $value;
	}

	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == TRUE ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[2] = $pdf->ImportPage(2);
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