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
class GovernmentForms_US_1099MISC extends GovernmentForms_US {
	public $pdf_template = '1099misc.pdf';

	public $template_offsets = array( 0, 0 );

	function getOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
								'government' => TTi18n::gettext('Government (Multiple Employees/Page)'),
								'employee' => TTi18n::gettext('Employee (One Employee/Page)'),
								);
				break;
		}

		return $retval;
	}

	//Set the type of form to display/print. Typically this would be:
	// government or employee.
	function getType() {
		if ( isset($this->_type) ) {
			return $this->_type;
		}

		return FALSE;
	}
	function setType( $value ) {
		$this->_type = trim($value);
		return TRUE;
	}

	function getShowInstructionPage() {
		if ( isset($this->_show_instruction_page) ) {
			return $this->_show_instruction_page;
		}

		return FALSE;
	}
	function setShowInstructionPage( $value ) {
		$this->_show_instruction_page = (bool)trim($value);
		return TRUE;
	}

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
								//
								// ** clearObject() seems to be clearing $this->type variable causing problems with which templates to display.
								// Magic get/set functions are getting in the way?
								//
								//
								//
								//
								//

								//
								//Need to create a filterFunction or some sort other type of functions that determines if
								//the box should be drawn or not, as some templates has some boxes and others don't.
								//
								//
								//
								//
								//
								//
								//
								//
								//
								//
								//
								//
								//
								//
								//
								//

								array(
										//'template_page' => 2, //All template pages
										'value' => $this->year,
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 395,
															'y' => 70,
															'h' => 20,
															'w' => 70,
															'halign' => 'C',
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 18,
																'type' => 'B' )
								),
								array(
										//'template_page' => 2,
										'function' => array('filterSmallYear', 'drawNormal' ),
										'value' => $this->year,
										'on_background' => TRUE,
										'coordinates' => array(
															'x' => 510,
															'y' => 249,
															'h' => 8,
															'w' => 24,
															'halign' => 'C',
															'fill_color' => array( 255, 255, 255 ),
															),
										'font' => array(
																'size' => 10,
																'type' => 'B' )
								),

								//Finish initializing page 1.
								'payer_id' => array(
												'coordinates' => array(
																		'x' => 48,
																		'y' => 170,
																		'h' => 15,
																		'w' => 122,
																		'halign' => 'C',
																		),
											),
								'recipient_id' => array(
												'coordinates' => array(
																		'x' => 172,
																		'y' => 170,
																		'h' => 15,
																		'w' => 120,
																		'halign' => 'C',
																		),
											),

								'trade_name' => array(
												'coordinates' => array(
																		'x' => 48,
																		'y' => 60,
																		'h' => 15,
																		'w' => 240,
																		'halign' => 'L',
																		),
											),
								'company_address' => array(
												'function' => array('filterCompanyAddress', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 48,
																		'y' => 75,
																		'h' => 48,
																		'w' => 240,
																		'halign' => 'L',
																		),
												'font' => array(
																		'size' => 8,
																		'type' => '' ),
												'multicell' => TRUE,
											),

								'name' => array(
												'function' => array('filterName', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 48,
																		'y' => 205,
																		'h' => 15,
																		'w' => 180,
																		'halign' => 'L',
																		),
											),
								'address' => array(
												'function' => array('filterAddress', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 48,
																		'y' => 250,
																		'h' => 25,
																		'w' => 180,
																		'halign' => 'L',
																		),
												'font' => array(
																		'size' => 8,
																		'type' => '' ),
												'multicell' => TRUE,
											),
								'city' => array(
												'function' => array('filterCity', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 48,
																		'y' => 285,
																		'h' => 15,
																		'w' => 180,
																		'halign' => 'L',
																		),
											),
								'account_number' => array(
												'coordinates' => array(
																		'x' => 48,
																		'y' => 320,
																		'h' => 15,
																		'w' => 180,
																		'halign' => 'L',
																		),
											),

								'l1' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 305,
																		'y' => 72,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),

								'l2' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 305,
																		'y' => 108,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l3' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 305,
																		'y' => 138,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l4' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 405,
																		'y' => 138,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l5' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 305,
																		'y' => 180,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l6' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 405,
																		'y' => 180,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l7' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 305,
																		'y' => 228,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l8' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 405,
																		'y' => 228,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l9' => array(
										'function' => 'drawCheckBox',
										'coordinates' => array(
																array(
																	'x' => 380,
																	'y' => 264,
																	'h' => 8,
																	'w' => 10,
																	'halign' => 'C',
																)
															),
									),

								'l10' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 405,
																		'y' => 263,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),

								'l13' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 305,
																		'y' => 324,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l14' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 405,
																		'y' => 324,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),

								'l15a' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 60,
																		'y' => 360,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l15b' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 182,
																		'y' => 360,
																		'h' => 12,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),

								'l16a' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 305,
																		'y' => 350,
																		'h' => 10,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l16b' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 305,
																		'y' => 362,
																		'h' => 10,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),

								'l17a' => array(
												'coordinates' => array(
																		'x' => 405,
																		'y' => 350,
																		'h' => 10,
																		'w' => 80,
																		'halign' => 'C',
																		),
											),
								'l17b' => array(
												'coordinates' => array(
																		'x' => 405,
																		'y' => 362,
																		'h' => 10,
																		'w' => 80,
																		'halign' => 'C',
																		),
											),

								'l18a' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 505,
																		'y' => 350,
																		'h' => 10,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
								'l18b' => array(
												'function' => array( 'MoneyFormat', 'drawNormal' ),
												'coordinates' => array(
																		'x' => 505,
																		'y' => 362,
																		'h' => 10,
																		'w' => 80,
																		'halign' => 'L',
																		),
											),
							  );

		if ( isset($template_schema[$name]) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function filterMiddleName( $value ) {
		//Return just initial
		$value = substr( $value, 0, 1);
		return $value;
	}
	function filterCompanyAddress( $value ) {
		Debug::Text('Filtering company address: '. $value, __FILE__, __LINE__, __METHOD__,10);

		//Combine company address for multicell display.
		$retarr[] = $this->company_address1;
		if ( $this->company_address2 != '' ) {
			$retarr[] = $this->company_address2;
		}
		$retarr[] = $this->company_city. ', '.$this->company_state . ' ' . $this->company_zip_code;
		$retarr[] = $this->company_phone;

		return implode("\n", $retarr );
	}

	function filterName( $value ) {
		return $this->first_name. ', '.$this->last_name . ' ' . $this->middle_name;
	}

	function filterAddress( $value ) {
		//Combine company address for multicell display.
		$retarr[] = $this->address1;
		if ( $this->address2 != '' ) {
			$retarr[] = $this->address2;
		}
		//$retarr[] = $this->city. ', '.$this->state . ' ' . $this->zip_code;

		return implode("\n", $retarr );
	}
	function filterCity( $value ) {
		return $this->city. ', '.$this->state . ' ' . $this->zip_code;
	}

	function filterSmallYear( $value ) {
		//Only show small year on 2nd template page.
		if ( in_array( (int)$this->current_template_index, array(0,3,4,6) ) ) {
			return FALSE;
		}

		return $value;
	}

	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == TRUE ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			for ( $tp=1; $tp <= 8; $tp++ ) {
				$this->template_index[$tp] = $pdf->ImportPage($tp);
			}
		}
		Debug::Arr($this->template_index, 'Template Index ', __FILE__, __LINE__, __METHOD__,10);

		if ( $this->year == ''  ) {
			$this->year = $this->getYear();
		}

		if ( $this->getType() == 'government') {
			$employees_per_page = 2;
			$n=2; //Don't loop the same employee.
			$form_template_pages = array(2,3,7); //Template pages to use.
		} else {
			$employees_per_page = 1;
			$n=1; //Loop the same employee twice.
			$form_template_pages = array(4,6); //Template pages to use.
		}

		//Get location map, start looping over each variable and drawing
		$records = $this->getRecords();
		if ( is_array($records) AND count($records) > 0 ) {
			$template_schema = $this->getTemplateSchema();

			foreach( $form_template_pages as $form_template_page ) {
				//Set the template used.
				Debug::Text('Template Page: '. $form_template_page, __FILE__, __LINE__, __METHOD__,10);
				$template_schema[0]['template_page'] = $form_template_page;

				if ( $this->getType() == 'government' AND count($records) > 1 ) {
					$template_schema[0]['combine_templates'] = array(
																	array( 'template_page' => $form_template_page, 'x'=> 0, 'y' => 0),
																	array( 'template_page' => $form_template_page, 'x'=> 0, 'y' => 400) //Place two templates on the same page.
																);
				} else {
					Debug::Text('zTemplate Page: '. $form_template_page .' C: '. count($records) .' B: '. $this->getShowBackground() .' D: '. $this->getType() .' X: '. $this->_type, __FILE__, __LINE__, __METHOD__,10);
				}

				$e=0;
				foreach( $records as $employee_data ) {
					//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
					//Debug::Text(' E: '. $e .' T: '. $form_template_page .' Employee : '. $employee_data['first_name'], __FILE__, __LINE__, __METHOD__,10);
					$this->arrayToObject( $employee_data ); //Convert record array to object

					for( $i=0; $i < $n; $i++ ) {
						$this->setPageOffsets( 0, 0 );

						if ( ( $employees_per_page == 1 AND $i > 0 )
								OR ( $employees_per_page == 2 AND $e % 2 != 0 )
								) {
							$this->setPageOffsets( 0, $template_schema[0]['combine_templates'][1]['y'] );
						}

						foreach( $template_schema as $field => $schema ) {
							$this->Draw( $this->$field, $schema );
						}
					}

					if ( $employees_per_page == 1 OR ( $employees_per_page == 2 AND  $e % $employees_per_page != 0 ) ) {
						$this->resetTemplatePage();
						//if ( $this->getShowInstructionPage() == TRUE ) {
						//	$this->addPage( array('template_page' => 2) );
						//}
					}

					$e++;
				}
			}
		}

		$this->clearRecords();

		return TRUE;
	}
}
?>