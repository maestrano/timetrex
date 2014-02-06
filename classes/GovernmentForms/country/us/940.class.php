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
class GovernmentForms_US_940 extends GovernmentForms_US {
    public $xml_schema = '94x/94x/IRS940.xsd';
	public $pdf_template = '940.pdf';

	public $payment_cutoff_amount = 7000; //Line5

	public $futa_tax_before_adjustment_rate = 0.006; //Line8

	public $futa_tax_rate = 0.054; //Line9

	public $line_16_cutoff_amount = 500; //Line16

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
								//Initialize page1, replace years on template.
								array(
										'page' => 1,
										'template_page' => 1,
										'value' => '940 for '. $this->year,
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 53,
															'y' => 30,
															'h' => 28,
															'w' => 99,
															'halign' => 'C',
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 16,
																'type' => 'B' )
									),
								array(
										'value' => $this->year, //Type of Return section
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 449,
															'y' => 140,
															'h' => 8,
															'w' => 20,
															'halign' => 'C',
															'fill_color' => array( 245, 245, 245 ),
															),
										'font' => array(
																'size' => 9 )
									),
								array(
										'value' => '('. $this->year .')', //Page Footer
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 554,
															'y' => 709,
															'h' => 11,
															'w' => 25,
															'halign' => 'C',
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 7 )
									),
								array(
										'value' => $this->year, //Part 2
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 290.5,
															'y' => 288.5,
															'h' => 6,
															'w' => 20,
															'halign' => 'C',
															//'text_color' => array( 255, 255, 255 ),
															//'fill_color' => array( 30, 30, 30 ),
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 9,
																'type' => 'B')
									),
								array(
										'value' => $this->year,
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 350,
															'y' => 558.5,
															'h' => 6,
															'w' => 20,
															'halign' => 'C',
															//'text_color' => array( 255, 255, 255 ),
															//'fill_color' => array( 30, 30, 30 ),
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 9,
																'type' => 'B')
									),
								//Finish initializing page 1.

								'ein' => array(
												'page' => 1,
												'template_page' => 1,
												'function' => 'drawChars', //custom drawing function.
												'coordinates' => array(
																	   array( 'type' => 'static', //static or relative
																		'x' => 152,
																		'y' => 67,
																		'h' => 18,
																		'w' => 19,
																		'halign' => 'C',
																		),
																	   array(
																		'x' => 180,
																		'y' => 67,
																		'h' => 18,
																		'w' => 19,
																		'halign' => 'C',
																		),
																	   array(
																		'x' => 218,
																		'y' => 67,
																		'h' => 18,
																		'w' => 19,
																		'halign' => 'C',
																		),
																	   array(
																		'x' => 243,
																		'y' => 67,
																		'h' => 18,
																		'w' => 19,
																		'halign' => 'C',
																		),
																	   array(
																		'x' => 268,
																		'y' => 67,
																		'h' => 18,
																		'w' => 19,
																		'halign' => 'C',
																		),
																	   array(
																		'x' => 294,
																		'y' => 67,
																		'h' => 18,
																		'w' => 19,
																		'halign' => 'C',
																		),
																	   array(
																		'x' => 320,
																		'y' => 67,
																		'h' => 18,
																		'w' => 19,
																		'halign' => 'C',
																		),
																	   array(
																		'x' => 345,
																		'y' => 67,
																		'h' => 18,
																		'w' => 19,
																		'halign' => 'C',
																		),
																	   array(
																		'x' => 370,
																		'y' => 67,
																		'h' => 18,
																		'w' => 19,
																		'halign' => 'C',
																		),
																	  ),
												'font' => array (
																		'size' => 12,
																		'type' => 'B' )
											),
								'name' => array(
												'coordinates' => array(
																		'x' => 136,
																		'y' => 91,
																		'h' => 18,
																		'w' => 252,
																		'halign' => 'L',
																		),
											),
								'trade_name' => array(
												'coordinates' => array(
																		'x' => 115,
																		'y' => 115,
																		'h' => 18,
																		'w' => 273,
																		'halign' => 'L',
																		),
											),
								'address' => array(
												'coordinates' => array(
																		'x' => 79,
																		'y' => 139,
																		'h' => 18,
																		'w' => 310,
																		'halign' => 'L',
																		),
											),
								'city' => array(
												'coordinates' => array(
																		'x' => 79,
																		'y' => 165,
																		'h' => 18,
																		'w' => 186,
																		'halign' => 'L',
																		),
											),
								'state' => array(
												'coordinates' => array(
																		'x' => 274,
																		'y' => 165,
																		'h' => 18,
																		'w' => 36,
																		'halign' => 'C',
																		),
											),
								'zip_code' => array(
												'coordinates' => array(
																		'x' => 317,
																		'y' => 165,
																		'h' => 18,
																		'w' => 72,
																		'halign' => 'C',
																		),
											),

								'return_type' => array(
												'function' => 'drawCheckBox',
												'coordinates' => array(
																		'a' => array(
																			'x' => 426,
																			'y' => 97,
																			'h' => 11,
																			'w' => 12,
																			'halign' => 'C',
																			),
																		'b' => array(
																			'x' => 426,
																			'y' => 115,
																			'h' => 11,
																			'w' => 12,
																			'halign' => 'C',
																			),
																		'c' => array(
																			'x' => 426,
																			'y' => 133,
																			'h' => 11,
																			'w' => 12,
																			'halign' => 'C',
																			),
																		'd' => array(
																			'x' => 426,
																			'y' => 151,
																			'h' => 11,
																			'w' => 12,
																			'halign' => 'C',
																			),
																	  ),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )
											),

								'l1a' => array(
												'function' => 'drawChars',
												'coordinates' => array(
																		array(
																			'x' => 455,
																			'y' => 221,
																			'h' => 18,
																			'w' => 22,
																			'halign' => 'C',
																			),
																		array(
																			'x' => 490,
																			'y' => 221,
																			'h' => 18,
																			'w' => 23,
																			'halign' => 'C',
																			),
																	  ),
											),
								'l1b' => array(
												'function' => 'drawCheckbox',
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 251,
																		'h' => 8,
																		'w' => 10,
																		'halign' => 'C',
																		),
																	),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )
											),
								'l2' => array(
												'function' => array( 'filterL2', 'drawCheckbox' ),
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 270,
																		'h' => 8,
																		'w' => 10,
																		'halign' => 'C',
																		),
																	),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )

											),
								'l3' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 305,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 305,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l4' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 310,
																		'y' => 324,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 410,
																		'y' => 324,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l4a' => array(
												'function' => 'drawCheckbox',
												'coordinates' => array(
																	array(
																		'x' => 158.5,
																		'y' => 347.5,
																		'h' => 8,
																		'w' => 10,
																		'halign' => 'C',
																		),
																	),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )

											),
								'l4b' => array(
												'function' => 'drawCheckbox',
												'coordinates' => array(
																	array(
																		'x' => 158.5,
																		'y' => 360,
																		'h' => 8,
																		'w' => 10,
																		'halign' => 'C',
																		),
																	),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )

											),
								'l4c' => array(
												'function' => 'drawCheckbox',
												'coordinates' => array(
																	array(
																		'x' => 310,
																		'y' => 347.5,
																		'h' => 8,
																		'w' => 10,
																		'halign' => 'C',
																		),
																	),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )

											),
								'l4d' => array(
												'function' => 'drawCheckbox',
												'coordinates' => array(
																	array(
																		'x' => 310,
																		'y' => 360,
																		'h' => 8,
																		'w' => 10,
																		'halign' => 'C',
																		),
																	),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )

											),
								'l4e' => array(
												'function' => 'drawCheckbox',
												'coordinates' => array(
																	array(
																		'x' => 432,
																		'y' => 347.5,
																		'h' => 8,
																		'w' => 10,
																		'halign' => 'C',
																		),
																	),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )

											),

								'l5' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 310,
																		'y' => 378,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 410,
																		'y' => 378,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l6' => array(
												'function' => array( 'calcL6', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 397,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 397,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l7' => array(
												'function' => array( 'calcL7', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 420,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 420,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l8' => array(
												'function' => array( 'calcL8', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 445,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 445,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l9' => array(
												'function' => array( 'calcL9', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 485,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 485,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l10' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 515,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 515,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l11' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 538,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 538,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l12' => array(
												'function' => array( 'calcL12', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 575,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 575,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l13' => array(
												'function' => 'drawSplitDecimalFloat',
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 598,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 598,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l14' => array(
												'function' => array( 'calcL14', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 636,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 636,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l15' => array(
												'function' => array( 'calcL15', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 454,
																		'y' => 660,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 554,
																		'y' => 660,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l15a' => array(
												'function' => array( 'filterL15', 'drawCheckbox'),
												'coordinates' => array(
																	array(
																		'x' => 420.5,
																		'y' => 680,
																		'h' => 8,
																		'w' => 10,
																		'halign' => 'C',
																		),
																	),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )
											),
								'l15b' => array(
												'function' => array( 'filterL15', 'drawCheckbox'),
												'coordinates' => array(
																	array(
																		'x' => 492,
																		'y' => 680,
																		'h' => 8,
																		'w' => 10,
																		'halign' => 'C',
																		),
																	),
												'font' => array (
																		'size' => 10,
																		'type' => 'B' )

											),

								//Initialize Page 2
								array(
												'page' => 2,
												'template_page' => 2,
												'value' => $this->name,
												'coordinates' => array(
																		'x' => 37,
																		'y' => 56,
																		'h' => 15,
																		'w' => 355,
																		'halign' => 'L',
																		),
											),
								array(
												'value' => $this->ein,
												'coordinates' => array(
																		'x' => 400,
																		'y' => 56,
																		'h' => 15,
																		'w' => 175,
																		'halign' => 'C',
																		),
											),

								array(
												'value' => '('. $this->year .')',
												'on_background' => TRUE,
												'coordinates' => array(
																		'x' => 554,
																		'y' => 697,
																		'h' => 11,
																		'w' => 25,
																		'halign' => 'C',
																		'fill_color' => array( 255, 255, 255 ),
																		),
												'font' => array(
																		'size' => 7 )
											),
								//Finish initialize Page 2

								'l16a' => array(
												'page' => 2,
												'template_page' => 2,
												'function' => array( 'filterL16', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 346,
																		'y' => 120,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 446,
																		'y' => 120,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l16b' => array(
												'function' => array( 'filterL16', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 346,
																		'y' => 144,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 446,
																		'y' => 144,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l16c' => array(
												'function' => array( 'filterL16', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 346,
																		'y' => 168,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 446,
																		'y' => 168,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l16d' => array(
												'function' => array( 'filterL16', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 346,
																		'y' => 192,
																		'h' => 18,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 446,
																		'y' => 192,
																		'h' => 18,
																		'w' => 23,
																		'halign' => 'C',
																		),
																	),
											),
								'l17' => array(
												'function' => array( 'calcL17', 'drawSplitDecimalFloat' ),
												'coordinates' => array(
																	array(
																		'x' => 346,
																		'y' => 216.5,
																		'h' => 17,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 446,
																		'y' => 216.5,
																		'h' => 17,
																		'w' => 22,
																		'halign' => 'C',
																		),
																	),
											),

								//Initialize Page 3
								array(
												'page' => 3,
												'template_page' => 3,
												'value' => substr( $this->year, 2, 2),
												'on_background' => TRUE,
												'coordinates' => array(
																		'x' => 536,
																		'y' => 582.5,
																		'h' => 0,
																		'w' => 30,
																		'halign' => 'L',
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
																		'x' => 258,
																		'y' => 204.5,
																		'h' => 11,
																		'w' => 22,
																		'halign' => 'C',
																		'fill_color' => array( 255, 255, 255 ),
																		),
												'font' => array(
																		'size' => 10 )
											),
								array(
												'value' => $this->year,
												'on_background' => TRUE,
												'coordinates' => array(
																		'x' => 398,
																		'y' => 286,
																		'h' => 11,
																		'w' => 22,
																		'halign' => 'C',
																		'fill_color' => array( 255, 255, 255 ),
																		),
												'font' => array(
																		'size' => 10 )
											),
								//Finish initialize Page 3

								array(
												'page' => 3,
												'template_page' => 3,
												'value' => $this->ein,
												//'function' => 'drawPage3EIN',
												'coordinates' =>
																	array(
																		'x' => 95,
																		'y' => 620,
																		'h' => 15,
																		'w' => 100,
																		'halign' => 'C',
																		),
												'font' => array(
																		'size' => 10 )
											),
								array(
												'function' => array( 'calcL14', 'drawSplitDecimalFloat'),
												'coordinates' => array(
																	array(
																		'x' => 440,
																		'y' => 613,
																		'h' => 15,
																		'w' => 95,
																		'halign' => 'R',
																		),
																	array(
																		'x' => 542,
																		'y' => 613,
																		'h' => 15,
																		'w' => 32,
																		'halign' => 'C',
																		),
																	),
												'font' => array(
																		'size' => 22 )

											),

								array(
												'value' => $this->trade_name,
												'coordinates' => array(
																		'x' => 229,
																		'y' => 651,
																		'h' => 15,
																		'w' => 250,
																		'halign' => 'L',
																		),
												'font' => array(
																		'size' => 10 )
											),
								array(
												'value' => $this->address,
												'coordinates' => array(
																		'x' => 229,
																		'y' => 674,
																		'h' => 15,
																		'w' => 250,
																		'halign' => 'L',
																		),
												'font' => array(
																		'size' => 10 )
											),
								array(
												'value' => $this->city . ', ' . $this->state . ', ' . $this->zip_code,
												'coordinates' => array(
																		'x' => 229,
																		'y' => 698,
																		'h' => 15,
																		'w' => 250,
																		'halign' => 'L',
																		),
												'font' => array(
																		'size' => 10 )
											),
							  );

		if ( isset($template_schema[$name]) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function filterL2( $value ) {
		if ( $this->l11 > 0 ) {
			return TRUE;
		}

		return FALSE;
	}

	function filterL15( $value ) {
		if ( $this->l15 > 0 ) {
			return $value;
		}

		return FALSE;
	}

	function filterL16( $value ) {
		if ( $this->l12 > $this->line_16_cutoff_amount ) {
			return $value;
		}

		return FALSE;
	}

	function calcL6( $value, $schema ) {
		//Subtotal: Line 4 + Line 5
		$this->l6 = $this->l4 + $this->l5;
		return $this->l6;
	}
	function calcL7( $value, $schema ) {
		//Total Taxable FUTA wages: Line 3 - Line 6
		$this->l7 = $this->l3 - $this->l6;
		return $this->l7;
	}
	function calcL8( $value, $schema ) {
		//FUTA tax before adjustments
		$this->l8 = $this->l7 * $this->futa_tax_before_adjustment_rate;
		return $this->l8;
	}
	function calcL9( $value, $schema ) {
		//Taxable FUTA wages

		//If line 10 or 11 are filled out, do not fill out line 9.
		if ( ( $this->l10 == '' OR $this->l10 == 0 ) AND ( $this->l11 == '' OR $this->l11 == 0 ) ) {
			$this->l9 = $this->l7 * $this->futa_tax_rate;
			return $this->l9;
		}

		return FALSE;
	}
	function calcL12( $value, $schema ) {
		//Total FUTA tax after adjustments
		$this->l12 = $this->l8 + $this->l9 + $this->l10 + $this->l11;
		return $this->l12;
	}
	function calcL14( $value, $schema ) {
		//Balance Due
		if ( $this->l12 > $this->l13 ) {
			$this->l14 = $this->l12 - $this->l13;
			return $this->l14;
		}

		return FALSE;
	}
	function calcL15( $value, $schema ) {
		//Balance Due

		if ( $this->l13 > $this->l12 ) {
			$this->l15 = $this->l13 - $this->l12;
			return $this->l15;
		}

		return FALSE;
	}
	function calcL17( $value, $schema ) {
		//Total tax liability for the year
		if ( $this->l12 > $this->line_16_cutoff_amount ) {
			$this->l17 = $this->l16a + $this->l16b + $this->l16c + $this->l16d;

			if ( $this->MoneyFormat( $this->l17 ) != $this->MoneyFormat( $this->l12 ) ) {
				$schema['coordinates'][0]['fill_color'] = array( 255, 0, 0 );
				$schema['coordinates'][1]['fill_color'] = array( 255, 0, 0 );
			}
			return $this->l17;
		}

		return FALSE;
	}

	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == TRUE ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[1] = $pdf->ImportPage(1);
			$this->template_index[2] = $pdf->ImportPage(2);
			$this->template_index[3] = $pdf->ImportPage(3);
		}

		if ( $this->year == ''  ) {
			$this->year = $this->getYear();
		}

		//Get location map, start looping over each variable and drawing
		$template_schema = $this->getTemplateSchema();
		if ( is_array( $template_schema) ) {

			$template_page = NULL;

			foreach( $template_schema as $field => $schema ) {
				$this->Draw( $this->$field, $schema );
			}
		}

		return TRUE;
	}
    
    function _outputXML() {        
                
        if ( is_object( $this->getXMLObject() ) ) {
			$xml = $this->getXMLObject();
		} else {
			return FALSE; //No XML object to append too. Needs return940 form first.
		}
        
        if ( isset( $this->return_type ) ) {
            foreach( $this->return_type as $return_type ) {
                switch( $return_type ) {
                    case 'b':
                        $xml->IRS940->addChild('SuccessorEmployer', 'X');
                        break;
                    case 'c':
                        $xml->IRS940->addChild('NoPayments', 'X');
                        break;
                    case 'd':
                        $xml->IRS940->addChild('FinalReturn', 'X');
                        break;
                }
            }
        }
        
        if ( isset( $this->l1a ) ) {
            $xml->IRS940->addChild('SingleStateCode', $this->l1a);
        } elseif ( isset( $this->l1b ) ) {
            $xml->IRS940->addChild('MultiStateContribution', $this->l1b);
        }
        
        if ( isset( $this->l3 ) ) {
            $xml->IRS940->addChild('TotalWages', $this->l3);
        }  
        
        if ( isset( $this->l4 ) ) {
            $xml->IRS940->addChild('ExemptWages');
            
            $xml->IRS940->ExemptWages->addChild('ExemptWagesAmt', $this->l4 );
            
            $xml->IRS940->ExemptWages->addChild('ExemptionCategory');
            foreach( range('a','e') as $z ) {
                $col = 'l4'.$z;
                if ( isset( $this->$col ) ) {
                    switch( $z ) {
                        case 'a':
                            $xml->IRS940->ExemptWages->ExemptionCategory->addChild('FringeBenefits', 'X');
                            break;
                        case 'b':
                            $xml->IRS940->ExemptWages->ExemptionCategory->addChild('GroupTermLifeIns', 'X');
                            break;
                        case 'c':
                            $xml->IRS940->ExemptWages->ExemptionCategory->addChild('RetirementPension', 'X');
                            break;
                        case 'd':
                            $xml->IRS940->ExemptWages->ExemptionCategory->addChild('DependentCare', 'X');
                            break;
                        case 'e':
                            $xml->IRS940->ExemptWages->ExemptionCategory->addChild('OtherExemption', 'X');
                            break;
                    }
                }               
                
            }
        }
        
        if ( isset( $this->l5 ) ) {
            $xml->IRS940->addChild('WagesOverLimitAmt', $this->l5);
        }
              
        if ( $this->calcL6(NULL,NULL) >= 0 ) {
            $xml->IRS940->addChild('TotalExemptWagesAmt', $this->calcL6(NULL,NULL));
        }   
        if ( $this->calcL7(NULL,NULL) >= 0 ) {
            $xml->IRS940->addChild('TotalTaxableWagesAmt', $this->calcL7(NULL,NULL)); 
        } else {
            $xml->IRS940->addChild('TotalTaxableWagesAmt', 0.00); 
        }         
        
        if ( $this->calcL8(NULL,NULL) >= 0 ) {
            $xml->IRS940->addChild('FUTATaxBeforeAdjustmentsAmt', $this->calcL8(NULL,NULL));
        }
          
        if ( $this->calcL9(NULL,NULL) >= 0 ) {
            $xml->IRS940->addChild('MaximumCreditAmt', $this->calcL9(NULL,NULL));
        } elseif ( isset( $this->l10 ) ) {
            $xml->IRS940->addChild('AdjustmentsToFUTATax');
            $xml->IRS940->AdjustmentsToFUTATax->addChild('FUTAAdjustmentAmt', $this->l10);           
            
        }
        
        if ( $this->calcL12(NULL,NULL) >= 0 ) {
            $xml->IRS940->addChild('FUTATaxAfterAdjustments',$this->calcL12(NULL,NULL) );
        }
        
        if ( isset($this->l13) ) {
            
            $xml->IRS940->addChild('TotalTaxDepositedAmt',$this->l13 );
        }
        if ( $this->calcL14(NULL,NULL) >= 0 ) {
            $xml->IRS940->addChild('BalanceDue',$this->calcL14(NULL,NULL) );
        } elseif ( $this->calcL15(NULL,NULL) >= 0 ) {
            $xml->IRS940->addChild('Overpayment' );
            $xml->IRS940->Overpayment->addChild('Amount',$this->calcL15(NULL,NULL) );
            $xml->IRS940->Overpayment->addChild('Refund','X' );
        }
        
        foreach( range('a','d') as $z ) {
            $col = 'l16'.$z;
            if ( isset( $this->$col ) ) {
                switch( $z ) {
                    case 'a':
                        $xml->IRS940->addChild('Quarter1LiabilityAmt', $this->$col);
                        break;
                    case 'b':
                        $xml->IRS940->addChild('Quarter2LiabilityAmt', $this->$col);
                        break;
                    case 'c':
                        $xml->IRS940->addChild('Quarter3LiabilityAmt', $this->$col);
                        break;
                    case 'd':
                        $xml->IRS940->addChild('Quarter4LiabilityAmt', $this->$col);
                        break;
                }
            }               
            
        }
                
        if ( $this->calcL17(NULL,NULL) >= 0 ) {
            $xml->IRS940->addChild('TotalYearLiabilityAmt', $this->calcL17(NULL,NULL));
        }
        
        
		return TRUE;
	} 
    
    
}
?>