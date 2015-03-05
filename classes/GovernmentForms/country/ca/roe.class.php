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
class GovernmentForms_CA_ROE extends GovernmentForms_CA {

	public $xml_schema = 'BulkRoeHeader_Basic.xsd';
	public $pdf_template = 'roe.pdf';

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
								//Initialize page1, replace years on template.
								array(
										'page' => 1,
										'template_page' => 1,
										'on_background' => TRUE,
									),

								//Serial
								'serial' => array(

										'coordinates' => array(
															'x' => 35,
															'y' => 58,
															'h' => 8,
															'w' => 115,
															'halign' => 'R',

													),
										'font' => array(
														'size' => 12,
														'type' => '',
													)

									),

								//Employer Info
								//Company information
								'company_name' => array(
										'coordinates' => array(
															'x' => 35,
															'y' => 90,
															'h' => 10,
															'w' => 310,
															'halign' => 'L',

															),
										'font' => array(
																'size' => 12,
																'type' => '' )
									),

								'company_address' => array(
										'function' => array('filterCompanyAddress', 'drawNormal' ),
										'coordinates' => array(
															'x' => 35,
															'y' => 105,
															'h' => 10,
															'w' => 310,
															'halign' => 'L',

															),
										'font' => array(
																'size' => 12,
																'type' => '' ),
										'multicell' => TRUE,
									),
								'company_postal_code' => array(

										'coordinates' => array(
																'x' => 280,
																'y' => 138,
																'h' => 10,
																'w' => 65,
																'halign' => 'C',

															),
										'font' => array(
																'size' => 10,
																'type' => '' ),

									),

								//Business Number
								'business_number' => array(
										'coordinates' => array(
															'x' => 370,
															'y' => 85,
															'h' => 10,
															'w' => 210,
															'halign' => 'R',

															),
										'font' => array(
																'size' => 12,
																'type' => '' ),
									),
								//Employee info
								'employee_full_name' => array(
										'coordinates' => array(
															'x' => 35,
															'y' => 165,
															'h' => 10,
															'w' => 310,
															'halign' => 'L',

															),
										'font' => array(
																'size' => 12,
																'type' => '' ),
									),

								'employee_address' => array(
										'function' => array('filterEmployeeAddress', 'drawNormal' ),
										'coordinates' => array(
															'x' => 35,
															'y' => 180,
															'h' => 10,
															'w' => 310,
															'halign' => 'L',

															),
										'font' => array(
																'size' => 12,
																'type' => '' ),
										'multicell' => TRUE,
									),

								//Pay Period Type
								'pay_period_type' => array(

										'coordinates' => array(
															'x' => 370,
															'y' => 112,
															'h' => 10,
															'w' => 210,
															'halign' => 'R',

															),
										'font' => array(
																'size' => 12,
																'type' => '' ),

									),
								//SIN
								'sin' => array(
										'coordinates' => array(
															'x' => 370,
															'y' => 137,
															'h' => 10,
															'w' => 210,
															'halign' => 'R',

															),
										'font' => array(
																'size' => 12,
																'type' => '' ),

									),

								//Employee Title
								'title' => array(
										'coordinates' => array(
															'x' => 35,
															'y' => 240,
															'h' => 10,
															'w' => 310,
															'halign' => 'L',

															),
										'font' => array(
															'size' => 12,
															'type' => ''
															),
									),
								//First Day Worked
								'first_date' => array(
										'function' => array('filterDate', 'drawSegments'),
										'coordinates' => array(
															array(
															'x' => 490,
															'y' => 160,
															'h' => 15,
															'w' => 20,
															'halign' => 'C',

															),
															array(
															'x' => 512,
															'y' => 160,
															'h' => 15,
															'w' => 26,
															'halign' => 'C',

															),
															array(
															'x' => 540,
															'y' => 160,
															'h' => 15,
															'w' => 40,
															'halign' => 'C',

															),
													),
										'font' => array(
															'size' => 10,
															'type' => '',
													),
									),


								//Last day paid

								'last_date' => array(

										'function' => array('filterDate', 'drawSegments'),
										'coordinates' => array(
															array(
															'x' => 490,
															'y' => 185,
															'h' => 17,
															'w' => 21,
															'halign' => 'C',

															),
															array(
															'x' => 513,
															'y' => 185,
															'h' => 17,
															'w' => 25,
															'halign' => 'C',

															),
															array(
															'x' => 540,
															'y' => 185,
															'h' => 17,
															'w' => 40,
															'halign' => 'C',

															),
													),
										'font' => array(
															'type' => '',
															'size' => 10,
													),
									),

								//Pay Period End Date
								'pay_period_end_date' => array(

										'function' => array('filterDate', 'drawSegments'),
										'coordinates' => array(
															array(
															'x' => 490,
															'y' => 210,
															'h' => 18,
															'w' => 21,
															'halign' => 'C',

															),
															array(
															'x' => 513,
															'y' => 210,
															'h' => 18,
															'w' => 25,
															'halign' => 'C',

															),
															array(
															'x' => 540,
															'y' => 210,
															'h' => 18,
															'w' => 40,
															'halign' => 'C',

															),
													),
										'font' => array(
															'type' => '',
															'size' => 10,
													),
									),

								'recall_date' => array(
										'function' => array('filterDate', 'drawSegments'),
										'coordinates' => array(
															array(
															'x' => 490,
															'y' => 240,
															'h' => 18,
															'w' => 21,
															'halign' => 'C',

															),
															array(
															'x' => 513,
															'y' => 240,
															'h' => 18,
															'w' => 25,
															'halign' => 'C',

															),
															array(
															'x' => 540,
															'y' => 240,
															'h' => 18,
															'w' => 40,
															'halign' => 'C',

															),
													),
										'font' => array(
															'type' => '',
															'size' => 10,
													),

									),

								// not returning
								'not_returning' => array(
										'function' => 'drawCheckBox',
										'coordinates' => array(
															array(
																'x' => 423,
																'y' => 242,
																'h' => 8,
																'w' => 11,
																'halign' => 'C',

															),
													),
										'font' => array(
															'type' => '',
															'size' => 10,
													),
									),
								//Insurable Hours
								'insurable_hours' => array(
										'coordinates' => array(
															'x' => 170,
															'y' => 268,
															'h' => 10,
															'w' => 85,
															'halign' => 'R',
															),
										'font' => array(
															'type' => '',
															'size' => 10,
													),

									),
								//Insurable Earnings

								'insurable_earnings' => array(
										'function' => 'drawSplitDecimalFloat',
										'coordinates' => array(
															array(
																'x' => 180,
																'y' => 298,
																'h' => 10,
																'w' => 60,
																'halign' => 'R',

																),
															array(
																'x' => 238,
																'y' => 298,
																'h' => 10,
																'w' => 14,
																'halign' => 'L',

																),
															),
										'font' => array(
															'type' => '',
															'size' => 10,
													),
									),

								//Enter Code
								'code_id' => array(
										'coordinates' => array(
															'x' => 544,
															'y' => 268,
															'h' => 10,
															'w' => 15,
															'halign' => 'C',

															),
										'font' => array(
															'type' => '',
															'size' => 10,
													),
									),
								//Further Information Contact Name
								'created_user_full_name' => array(

										'function' => 'drawPiecemeal',

										'coordinates' => array(
															array(
																'x' => 270,
																'y' => 294,
																'h' => 10,
																'w' => 310,
																'halign' => 'L',

															),
															array(
																'x' => 275,
																'y' => 710,
																'h' => 10,
																'w' => 210,
																'halign' => 'L',

															),
													),
										'font' => array(
															'type' => '',
															'size' => 10,
													),
									),
								'created_user_work_phone' => array(

										'function' => 'drawPiecemeal',
										'coordinates' => array(
															array(
																'x' => 330,
																'y' => 305,
																'h' => 8,
																'w' => 250,
																'halign' => 'L',

															),
															array(
																'x' => 155,
																'y' => 715,
																'h' => 10,
																'w' => 110,
																'halign' => 'L',

															),
													),
										'font' => array(
															'type' => '',
															'size' => 10,
													),
									),


								'vacation_pay' => array(
										'function' => 'drawSplitDecimalFloat',
										'coordinates' => array(
															array(
																'x' => 512,
																'y' => 351,
																'h' => 10,
																'w' => 42,
																'halign' => 'R',

																),
															array(
																'x' => 551,
																'y' => 351,
																'h' => 10,
																'w' => 16,
																'halign' => 'L',
																),

															),
										'font' => array(
															'type' => '',
															'size' => 10,
													),

									),
								'comments' => array(
										'coordinates' => array(
															'x' => 290,
															'y' => 540,
															'h' => 45,
															'w' => 290,
															'halign' => 'L',

															),
										'font' => array(
															'type' => '',
															'size' => 10,
													),
									),

								//English
								'english' => array(
										'function' => 'drawCheckBox',
										'coordinates' => array(
															array(
																'x' => 40,
																'y' => 713,
																'h' => 8,
																'w' => 8,
																'halign' => 'L',

															),
													),
										'font' => array(
															'type' => '',
															'size' => 10,
													),
									),

								'created_date' => array(
										'function' => array('filterDate', 'drawSegments'),
										'coordinates' => array(
															array(
															'x' => 490,
															'y' => 717,
															'h' => 8,
															'w' => 25,
															'halign' => 'C',

															),
															array(
															'x' => 518,
															'y' => 717,
															'h' => 8,
															'w' => 25,
															'halign' => 'C',

															),
															array(
															'x' => 544,
															'y' => 717,
															'h' => 8,
															'w' => 35,
															'halign' => 'C',

															),
													),
										'font' => array(
															'type' => '',
															'size' => 10,
													),

									),

								'pay_period_earnings' => array(
										'function' => 'drawGrid',
										'grid' => array(
															'column' => 3, // total columns
															'top_left_x' => 35, // start x
															'top_left_y' => 368,   // start y
															'h' => 18,  // Each of the height of the grid
															'w' => 60, // Each of the width of the grid
															'step_x' => 84,
															'step_y' => 18,
													),
										'coordinates' => array(
															'halign' => 'R',

															// not in here handle .
															//'x' => 35,
															//'y' => 368,
															//'h' => 18,
															//'w' => 60,
													),
										'font' => array(
															'type' => '',
															'size' => 8,
													),
									),
								'statutory_holiday' => array(
										'function' => 'drawSplitDecimalFloatGrid',
										'coordinates' => array(
													array(
														array(
															'x' => 352,
															'y' => 380,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 396,
															'y' => 380,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),

													),
													array(
														array(
															'x' => 507,
															'y' => 380,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 551,
															'y' => 380,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',
														),
													),
													array(
														array(
															'x' => 352,
															'y' => 397,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 396,
															'y' => 397,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
													array(
														array(
															'x' => 507,
															'y' => 397,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 551,
															'y' => 397,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
													array(
														array(
															'x' => 352,
															'y' => 414,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 396,
															'y' => 414,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
													array(
														array(
															'x' => 507,
															'y' => 414,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 551,
															'y' => 414,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
													array(
														array(
															'x' => 352,
															'y' => 429,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 396,
															'y' => 429,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
													array(
														array(
															'x' => 507,
															'y' => 429,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 551,
															'y' => 429,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
													array(
														array(
															'x' => 352,
															'y' => 446,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 396,
															'y' => 446,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
													array(
														array(
															'x' => 507,
															'y' => 446,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 551,
															'y' => 446,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),

										),
										'font' => array(
															'type' => '',
															'size' => 10,
													),


									),
								'other_monies' => array(
										'function' => 'drawSplitDecimalFloatGrid',
										'coordinates' => array(
													array(
														array(
															'x' => 507,
															'y' => 477,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 551,
															'y' => 477,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
													array(
														array(
															'x' => 507,
															'y' => 495,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 551,
															'y' => 495,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
													array(
														array(
															'x' => 507,
															'y' => 515,
															'h' => 10,
															'w' => 47,
															'halign' => 'R',

														),
														array(
															'x' => 551,
															'y' => 515,
															'h' => 10,
															'w' => 17,
															'halign' => 'L',

														),
													),
										),
										'font' => array(
															'type' => '',
															'size' => 10,
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
		//Combine company address for multicell display.
		//Dont specify postal code though, as thats in a separate box.
		return Misc::formatAddress( NULL, $this->company_address1, $this->company_address2, $this->company_city, $this->company_province );
	}

	function filterEmployeeAddress( $value ) {
		//Combine employee address for multicell display.
		return Misc::formatAddress( NULL, $this->employee_address1, $this->employee_address2, $this->employee_city, $this->employee_province, $this->employee_postal_code );
	}
	function filterDate( $value ) {
		if ( $value != '' OR $value != NULL ) {
			$value = getdate( $value );
			$value = array( $value['mday'], $value['mon'], $value['year'] );
		}
		return $value;
	}

	function _outputXML() {

		$records = $this->getRecords();
		Debug::Arr($records, 'Output XML Records: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array($records) AND count($records) > 0 ) {

			$pay_period_type_options = array(
											//5 => TTi18n::gettext('Manual'),
											10  => 'W',
											20  => 'B',
											30  => 'S',
											50  => 'M'
										);

			$xml = new SimpleXMLElement('<ROEHEADER Application="RoeWeb" FileVersion="1.00"></ROEHEADER>');
			$this->setXMLObject( $xml );

			$e = 0;
			foreach( $records as $employee_data ) {

				//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
				$this->arrayToObject( $employee_data ); //Convert record array to object

				$xml->addChild('Roe');

				// Box2
				if ( $this->serial != '' ) {

					$xml->Roe[$e]->addChild('B2', substr( $this->serial, 0, 9)); //maxLength 9  minOccurs="0"
				}

				// Box3
				if ( $this->payroll_reference_number != '' ) {
					$xml->Roe[$e]->addChild('B3', substr( $this->payroll_reference_number, 0, 15)); //maxLength 15  minOccurs="0"
				}
				// Box5
				$xml->Roe[$e]->addChild('B5', substr($this->business_number, 0, 15)); //maxLength 15
				// Box6
				$xml->Roe[$e]->addChild('B6', $pay_period_type_options[$this->pay_period_type_id]); //maxLength 1
				// Box8
				$xml->Roe[$e]->addChild('B8', substr($this->sin, 0, 9)); //maxLength 9
				// Box9
				$xml->Roe[$e]->addChild('B9');
				$xml->Roe[$e]->B9->addChild('FN', $this->first_name); //maxLength 20
				if ( $this->middle_name != '' ) {
					$xml->Roe[$e]->B9->addChild('MN', substr( $this->middle_name, 0, 4));//maxLength 4  minOccurs="0"
				}
				$xml->Roe[$e]->B9->addChild('LN', $this->last_name);    //maxLength 28
				$xml->Roe[$e]->B9->addChild('A1', substr( $this->employee_address1 .' '. $this->employee_address2, 0, 35 ) );//maxLength 35
				if ( $this->employee_city != '' ) {
					$xml->Roe[$e]->B9->addChild('A2', $this->employee_city);//maxLength 35  minOccurs="0"
				}
				if ( $this->employee_province != '' OR $this->employee_postal_code != '' ) {
					$xml->Roe[$e]->B9->addChild('A3', $this->employee_province .' '. $this->employee_postal_code); //maxLength 35  minOccurs="0"
				}

				// Box10
				$xml->Roe[$e]->addChild('B10', date('dmY', $this->first_date )); //maxLength 8
				// Box11
				$xml->Roe[$e]->addChild('B11', date('dmY', $this->last_date)); //maxLength 8
				// Box12
				$xml->Roe[$e]->addChild('B12', date('dmY', $this->pay_period_end_date)); //maxLength 8
				// Box13
				if ( $this->title != '' ) {
					$xml->Roe[$e]->addChild('B13', $this->title); //maxLength 40  minOccurs="0"
				}
				// Box14
				if ( $this->recall_code != '' ) {
					$xml->Roe[$e]->addChild('B14'); // minOccurs="0"

					$xml->Roe[$e]->B14->addChild('CD', $this->recall_code); //maxLength 1
					$xml->Roe[$e]->B14->addChild('DT', date('dmY', $this->recall_date)); //maxLength 8   minOccurs="0"
				}

				// Box15A
				$xml->Roe[$e]->addChild('B15A', substr(round($this->insurable_hours), 0, 4)); //maxLength 4
				// Box15B
				$xml->Roe[$e]->addChild('B15B', (float)substr($this->insurable_earnings, -9, 9)); //maxLength 9
				// Box15C
				$xml->Roe[$e]->addChild('B15C');

				if ( is_array( $this->pay_period_earnings ) ) {
					$i = 1;
					$x = 0;
					foreach( $this->pay_period_earnings as $pay_period_earning ) {
						if ( $x == 53 ) {
							break;
						}
						$xml->Roe[$e]->B15C->addChild('PP'); //maxOccurs="53"
						$xml->Roe[$e]->B15C->PP[$x]->addAttribute('nbr', $i);
						$xml->Roe[$e]->B15C->PP[$x]->addChild('AMT', (float)substr($pay_period_earning, -9, 9) ); //maxLength 9
						$i++;
						$x++;
					}
				} else {
					$xml->Roe[$e]->B15C->addChild('PP'); //maxOccurs="53"
					$xml->Roe[$e]->B15C->PP->addAttribute('nbr', 0);
					$xml->Roe[$e]->B15C->PP->addChild('AMT', 0 ); //maxLength 9
				}
				// Box16
				$xml->Roe[$e]->addChild('B16');
				$xml->Roe[$e]->B16->addChild('CD', $this->code_id); //maxLength 1
				$xml->Roe[$e]->B16->addChild('FN', $this->created_user_first_name); //maxLength 20
				$xml->Roe[$e]->B16->addChild('LN', $this->created_user_last_name); //maxLength 28

				if ( $this->created_user_work_phone != '' ) {
					$phone = $this->created_user_work_phone;
				} elseif ( $this->company_work_phone != '' ) {
					$phone = $this->company_work_phone;

				}

				$validator = new Validator();
				$phone = $validator->stripNonNumeric($phone);

				$xml->Roe[$e]->B16->addChild('AC', substr($phone, 0, 3 ) ); //maxLength 3
				$xml->Roe[$e]->B16->addChild('TEL', substr($phone, 3, 7 ) ); //maxLength 7

				// Box17A
				if ( $this->vacation_pay > 0 ) {
					$xml->Roe[$e]->addChild('B17A', (float)substr($this->vacation_pay, -9, 9)); // maxLength 9   minOccurs="0"
				}

				// Box17B

				if ( is_array( $this->statutory_holiday ) ) {
					$xml->Roe[$e]->addChild('B17B'); // minOccurs="0"

					$x = 0;
					$i = 0;
					foreach( $this->statutory_holiday as $holiday ) {

						if ( $x == 3 ) {
							break;
						}
						if ( is_array( $holiday ) ) {
							$xml->Roe[$e]->B17B->addChild('SH'); //minOccurs="0" maxOccurs="3"
							$xml->Roe[$e]->B17B->SH[$x]->addAttribute('nbr', $i);
							$xml->Roe[$e]->B17B->SH[$x]->addChild('DT', date('dmY', $holiday['date'] )); //maxLength 8
							$xml->Roe[$e]->B17B->SH[$x]->addChild('AMT', (float)substr($holiday['amount'], -9, 9)); //maxLength 9

							$x++;
							$i++;
						} else {
							continue;
						}


					}
				}


				// Box17C

				if ( is_array( $this->other_monies ) ) {

					$xml->Roe[$e]->addChild('B17C'); //minOccurs="0"

					$x = 0;
					$i = 0;
					foreach( $this->other_monies as $monies ) {
						if ( $x == 3 ) {
							break;
						}
						if ( is_array( $monies ) ) {
							$xml->Roe[$e]->B17C->addChild('OM'); //minOccurs="0" maxOccurs="3"
							$xml->Roe[$e]->B17C->OM[$x]->addAttribute('nbr', $i);
							$xml->Roe[$e]->B17C->OM[$x]->addChild('CD', $monies['other_monies_code']); //maxLength 1
							$xml->Roe[$e]->B17C->OM[$x]->addChild('AMT', (float)substr($monies['amount'], -9, 9)); //maxLength 9

							$x++;
							$i++;
						} else {
							continue;
						}
					}
				}

				// Box18
				if ( $this->comments != '' ) {
					$xml->Roe[$e]->addChild('B18', $this->comments ); //minOccurs="0"
				}


				// Box19   //minOccurs="0"


				// Box20
				$xml->Roe[$e]->addChild('B20', 'E'); //Language //minOccurs="0"  //maxLength 1

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
			//$this->template_index[2] = $pdf->ImportPage(2);
			//$this->template_index[3] = $pdf->ImportPage(3);
		}

		if ( $this->year == ''  ) {
			$this->year = $this->getYear();
		}

		//Get location map, start looping over each variable and drawing
		$records = $this->getRecords();
		if ( is_array($records) AND count($records) > 0 ) {

			$template_schema = $this->getTemplateSchema();

			$e = 0;
			foreach( $records as $employee_data ) {
				//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
				$this->arrayToObject( $employee_data ); //Convert record array to object

				foreach( $template_schema as $field => $schema ) {
					Debug::text('Drawing Cell... Field: '. $field, __FILE__, __LINE__, __METHOD__, 10);
					$this->Draw( $this->$field, $schema );
				}

				$this->resetTemplatePage();

				$e++;
			}
		}

		$this->clearRecords();

		return TRUE;
	}
}
?>