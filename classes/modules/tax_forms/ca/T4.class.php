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
 * $Id: T4.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Modules\Other
 */
class T4 extends TaxForms {
	var $src_pdf_t4 = NULL;
	var $src_pdf_t4summary = NULL;
	var $src_pdf_t4a = NULL;

	var $data; //Holds form data.
	var $t4_data; //Holds all employee data.
	var $t4sum_data; //Holds all T4 summary data.
	var $t4a_data; //Holds all T4a employee data.
	var $t4asum_data; //Holds all T4a summary data.

	var $form_layout_data = array(
								  't4_summary' => array(
													'year' => array('x' => 74, 'y' => 15.5, 'w' => 10, 'h' => 6, 'align' => 'C', 'fontsize' => 16, 'fontstyle' => 'B' ),
													'business_number' => array('x' => 96, 'y' => 28, 'w' => 75, 'h' => 7, 'align' => 'C' ),

													'company_name' => array('x' => 96, 'y' => 38, 'w' => 70, 'h' => 5, 'align' => 'L' ),
													'company_address' => array('x' => 96, 'y' => 43, 'w' => 70, 'h' => 5, 'align' => 'L', 'multicell' => TRUE),

													'total_t4' => array('x' => 20, 'y' => 74, 'w' => 45, 'h' => 6.5, 'align' => 'C' ),
													'employment_income' => array('x' => 20, 'y' => 87, 'w' => 60, 'h' => 6.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'employee_rpp' => array('x' => 20, 'y' => 100, 'w' => 60, 'h' => 6.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'pension_adjustment' => array('x' => 20, 'y' => 112, 'w' => 60, 'h' => 6.5, 'align' => 'C', 'split_decimal' => TRUE ),

													'employee_cpp' => array('x' => 91, 'y' => 74, 'w' => 55, 'h' => 6.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'employer_cpp' => array('x' => 91, 'y' => 87, 'w' => 55, 'h' => 6.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'employee_ei' => array('x' => 91, 'y' => 100, 'w' => 55, 'h' => 6.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'employer_ei' => array('x' => 91, 'y' => 112, 'w' => 55, 'h' => 6.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'income_tax' => array('x' => 91, 'y' => 125, 'w' => 55, 'h' => 6.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'total_deduction' => array('x' => 91, 'y' => 138, 'w' => 55, 'h' => 6.5, 'align' => 'C', 'split_decimal' => TRUE ),
													),
								  't4' => array(
													'company_name' => array('x' => 12, 'y' => 10, 'w' => 70, 'h' => 5, 'align' => 'L' ),
													'company_address' => array('x' => 12, 'y' => 15, 'w' => 70, 'h' => 5, 'align' => 'L', 'multicell' => TRUE),
													'year' => array('x' => 122, 'y' => 13, 'w' => 20, 'h' => 6, 'align' => 'C', 'fontsize' => 16 ),
													'business_number' => array('x' => 17, 'y' => 38, 'w' => 75, 'h' => 7, 'align' => 'C' ),
													'sin' => array('x' => 17, 'y' => 51, 'w' => 42, 'h' => 6, 'align' => 'C' ),

													'cpp_exempt' => array('x' => 70, 'y' => 51, 'w' => 5, 'h' => 6, 'align' => 'C' ),
													'ei_exempt' => array('x' => 78, 'y' => 51, 'w' => 5.5, 'h' => 6, 'align' => 'C' ),

													'province' => array('x' => 103, 'y' => 38, 'w' => 10, 'h' => 7, 'align' => 'C' ),
													'employment_code' => array('x' => 103, 'y' => 51, 'w' => 10, 'h' => 7, 'align' => 'C' ),

													'last_name' => array('x' => 16, 'y' => 69, 'w' => 55, 'h' => 6, 'align' => 'C' ),
													'first_name' => array('x' => 71, 'y' => 69, 'w' => 30, 'h' => 6, 'align' => 'C' ),
													'middle_name' => array('x' => 101, 'y' => 69, 'w' => 10, 'h' => 6, 'align' => 'C' ),
													'address' => array('x' => 16, 'y' => 75, 'w' => 95, 'h' => 5, 'align' => 'L', 'ln' => 2, 'multicell' => TRUE),

													'employment_income' => array('x' => 112, 'y' => 25, 'w' => 45, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),
													'income_tax' => array('x' => 164, 'y' => 25, 'w' => 40, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),

													'employee_cpp' => array('x' => 121, 'y' => 38, 'w' => 36, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),
													'ei_earnings' => array('x' => 169, 'y' => 38, 'w' => 36, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),

													//'employee_qpp
													'cpp_earnings' => array('x' => 169, 'y' => 50.5, 'w' => 36, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),

													'employee_ei' => array('x' => 121, 'y' => 63, 'w' => 36, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),
													'union_dues' => array('x' => 169, 'y' => 63, 'w' => 36, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),

													'employee_rpp' => array('x' => 121, 'y' => 76, 'w' => 36, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),
													'charity' => array('x' => 169, 'y' => 76, 'w' => 36, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),

													'pension_adjustment' => array('x' => 121, 'y' => 89, 'w' => 36, 'h' => 7, 'align' => 'C', 'split_decimal' => TRUE ),
													//'rpp_number'

													'other_box_0_code' => array('x' => 36, 'y' => 114, 'w' => 10, 'h' => 6, 'align' => 'C'),
													'other_box_0' => array('x' => 49, 'y' => 114, 'w' => 40, 'h' => 6, 'align' => 'C', 'split_decimal' => TRUE),

													'other_box_1_code' => array('x' => 93, 'y' => 114, 'w' => 10, 'h' => 6, 'align' => 'C'),
													'other_box_1' => array('x' => 106, 'y' => 114, 'w' => 40, 'h' => 6, 'align' => 'C', 'split_decimal' => TRUE),

													'other_box_2_code' => array('x' => 150, 'y' => 114, 'w' => 10, 'h' => 6, 'align' => 'C'),
													'other_box_2' => array('x' => 163, 'y' => 114, 'w' => 40, 'h' => 6, 'align' => 'C', 'split_decimal' => TRUE),

													'other_box_3_code' => array('x' => 36, 'y' => 126, 'w' => 10, 'h' => 6, 'align' => 'C'),
													'other_box_3' => array('x' => 49, 'y' => 126, 'w' => 40, 'h' => 6, 'align' => 'C', 'split_decimal' => TRUE),

													'other_box_4_code' => array('x' => 93, 'y' => 126, 'w' => 10, 'h' => 6, 'align' => 'C'),
													'other_box_4' => array('x' => 106, 'y' => 126, 'w' => 40, 'h' => 6, 'align' => 'C', 'split_decimal' => TRUE),

													'other_box_5_code' => array('x' => 150, 'y' => 126, 'w' => 10, 'h' => 6, 'align' => 'C'),
													'other_box_5' => array('x' => 163, 'y' => 126, 'w' => 40, 'h' => 6, 'align' => 'C', 'split_decimal' => TRUE),
													),
									't4a' => array(
													'year' => array('x' => 82, 'y' => 7, 'w' => 20, 'h' => 6, 'align' => 'C', 'fontsize' => 16 ),
													'last_name' => array('x' => 14, 'y' => 58, 'w' => 55, 'h' => 5, 'align' => 'C' ),
													'first_name' => array('x' => 69, 'y' => 58, 'w' => 30, 'h' => 5, 'align' => 'C' ),
													'middle_name' => array('x' => 99, 'y' => 58, 'w' => 15, 'h' => 5, 'align' => 'C' ),
													'address' => array('x' => 14, 'y' => 63, 'w' => 95, 'h' => 5, 'align' => 'L', 'ln' => 2, 'multicell' => TRUE),
													'sin' => array('x' => 37, 'y' => 43, 'w' => 30, 'h' => 6, 'align' => 'C' ),

													'company_name' => array('x' => 120, 'y' => 51, 'w' => 78, 'h' => 5, 'align' => 'C' ),
													'business_number' => array('x' => 158, 'y' => 40.5, 'w' => 40, 'h' => 7, 'align' => 'C' ),

													'pension' => array('x' => 8, 'y' => 18, 'w' => 28, 'h' => 5, 'align' => 'C', 'split_decimal' => TRUE ),
													'lump_sum_payment' => array('x' => 36, 'y' => 18, 'w' => 28, 'h' => 5, 'align' => 'C', 'split_decimal' => TRUE ),
													'income_tax' => array('x' => 92, 'y' => 18, 'w' => 28, 'h' => 5, 'align' => 'C', 'split_decimal' => TRUE ),
													'eligible_retiring_allowance' => array('x' => 148.5, 'y' => 18, 'w' => 25, 'h' => 5, 'align' => 'C', 'split_decimal' => TRUE ),
													'non_eligible_retiring_allowance' => array('x' => 174, 'y' => 18, 'w' => 25, 'h' => 5, 'align' => 'C', 'split_decimal' => TRUE ),

													'other_income' => array('x' => 8, 'y' => 31, 'w' => 28, 'h' => 5, 'align' => 'C', 'split_decimal' => TRUE ),
													'employee_rpp' => array('x' => 64, 'y' => 31, 'w' => 28, 'h' => 5, 'align' => 'C', 'split_decimal' => TRUE ),
													'pension_adjustment' => array('x' => 92, 'y' => 31, 'w' => 28, 'h' => 5, 'align' => 'C', 'split_decimal' => TRUE ),

													'charity' => array('x' => 8, 'y' => 43.5, 'w' => 28, 'h' => 5, 'align' => 'C', 'split_decimal' => TRUE ),
												   ),
									't4a_summary' => array(
													'year' => array('x' => 64, 'y' => 17.5, 'w' => 10, 'h' => 6, 'align' => 'C', 'fontsize' => 16, 'fontstyle' => 'B' ),

													'business_number' => array('x' => 87, 'y' => 33, 'w' => 70, 'h' => 6.5, 'align' => 'C' ),

													'company_name' => array('x' => 87, 'y' => 44, 'w' => 70, 'h' => 5, 'align' => 'L' ),
													'company_address' => array('x' => 87, 'y' => 49, 'w' => 70, 'h' => 5, 'align' => 'L', 'multicell' => TRUE),

													'total_t4a' => array('x' => 150, 'y' => 85.5, 'w' => 34, 'h' => 6, 'align' => 'C' ),

													'pension' => array('x' => 150, 'y' => 92, 'w' => 34, 'h' => 4.5, 'align' => 'C', 'split_decimal' => TRUE ),

													'lump_sum_payment' => array('x' => 150, 'y' => 96.5, 'w' => 34, 'h' => 4.5, 'align' => 'C', 'split_decimal' => TRUE ),

													'eligible_retiring_allowance' => array('x' => 150, 'y' => 110.5, 'w' => 34, 'h' => 4.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'non_eligible_retiring_allowance' => array('x' => 150, 'y' => 114.5, 'w' => 34, 'h' => 4.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'other_income' => array('x' => 150, 'y' => 119.5, 'w' => 34, 'h' => 4.5, 'align' => 'C', 'split_decimal' => TRUE ),

													'employee_rpp' => array('x' => 150, 'y' => 128.5, 'w' => 34, 'h' => 4.5, 'align' => 'C', 'split_decimal' => TRUE ),
													'pension_adjustment' => array('x' => 150, 'y' => 133, 'w' => 34, 'h' => 4.5, 'align' => 'C', 'split_decimal' => TRUE ),

													'income_tax' => array('x' => 157, 'y' => 149, 'w' => 34, 'h' => 4.5, 'align' => 'C', 'split_decimal' => TRUE ),
												   ),
								  );

	function __construct() {
		$this->src_pdf_t4 = Environment::getBasePath().'interface'. DIRECTORY_SEPARATOR .'forms'. DIRECTORY_SEPARATOR .'ca'. DIRECTORY_SEPARATOR .'tax'. DIRECTORY_SEPARATOR .'t4flat-06b.pdf';
		$this->src_pdf_t4summary = Environment::getBasePath().'interface'. DIRECTORY_SEPARATOR .'forms'. DIRECTORY_SEPARATOR .'ca'. DIRECTORY_SEPARATOR .'tax'. DIRECTORY_SEPARATOR .'t4-sum-08b.pdf';

		$this->src_pdf_t4a = Environment::getBasePath().'interface'. DIRECTORY_SEPARATOR .'forms'. DIRECTORY_SEPARATOR .'ca'. DIRECTORY_SEPARATOR .'tax'. DIRECTORY_SEPARATOR .'t4a-flat-08b.pdf';
		$this->src_pdf_t4asummary = Environment::getBasePath().'interface'. DIRECTORY_SEPARATOR .'forms'. DIRECTORY_SEPARATOR .'ca'. DIRECTORY_SEPARATOR .'tax'. DIRECTORY_SEPARATOR .'t4a-sum-08b.pdf';

		return TRUE;
	}


	function getEnableT4() {
		return $this->data['enable_t4'];
	}
	function setEnableT4( $value ) {
		$this->data['enable_t4'] = (bool)$value;
	}
	function getEnableT4Summary() {
		return $this->data['enable_t4sum'];
	}
	function setEnableT4Summary( $value ) {
		$this->data['enable_t4'] = (bool)$value;
	}

	/*
	 Primary dataset, used across more then one forms.
	*/
	function getYear() {
		return $this->data['year'];
	}
	function setYear( $value ) {
		$this->data['year'] = trim($value);
	}

	function getBusinessNumber() {
		return $this->data['business_number'];
	}
	function setBusinessNumber( $value ) {
		$this->data['business_number'] = trim($value);
		return TRUE;
	}

	function getCompanyName() {
		return $this->data['company_name'];
	}
	function setCompanyName( $value ) {
		$this->data['company_name'] = trim($value);
		return TRUE;
	}

	function getCompanyAddress1() {
		return $this->data['company_address1'];
	}
	function setCompanyAddress1( $value ) {
		$this->data['company_address1'] = trim($value);
		return TRUE;
	}

	function getCompanyAddress2() {
		return $this->data['company_address2'];
	}
	function setCompanyAddress2( $value ) {
		$this->data['company_address2'] = trim($value);
		return TRUE;
	}

	function getCompanyAddress3() {
		return $this->data['company_address3'];
	}
	function setCompanyAddress3( $value ) {
		$this->data['company_address3'] = trim($value);
		return TRUE;
	}

	function getCompanyCity() {
		return $this->data['company_city'];
	}
	function setCompanyCity( $value ) {
		$this->data['company_city'] = trim($value);
		return TRUE;
	}

	function getCompanyProvince() {
		return $this->data['company_province'];
	}
	function setCompanyProvince( $value ) {
		$this->data['company_province'] = trim($value);
		return TRUE;
	}

	function getCompanyPostalCode() {
		return $this->data['company_postal_code'];
	}
	function setCompanyPostalCode( $value ) {
		$this->data['company_postal_code'] = trim($value);
		return TRUE;
	}

	/*
	 Handling functions for additional forms/pages
	*/
	function addT4Employee( $obj ) {
		$this->t4_data[] = $obj->t4_data;
		return TRUE;
	}

	function addT4Summary( $obj ) {
		$this->t4sum_data = $obj->t4sum_data;
		return TRUE;
	}

	function addT4AEmployee( $obj ) {
		$this->t4a_data[] = $obj->t4a_data;
		return TRUE;
	}
	function addT4ASummary( $obj ) {
		$this->t4asum_data = $obj->t4asum_data;
		return TRUE;
	}

	/*
	 Filter functions for PDF
	*/
	function filterPDFGetCPP_Exempt( $ee_data ) {
		$data = NULL;
		if ( isset( $ee_data['cpp_exempt']) ) {
			$data = 'X';
		}

		return $data;
	}
	function filterPDFGetEI_Exempt( $ee_data ) {
		$data = NULL;
		if ( isset( $ee_data['ei_exempt']) ) {
			$data = 'X';
		}

		return $data;
	}

	function filterPDFGetSIN( $ee_data ) {
		$data = NULL;

		$data .= substr($ee_data['sin'],0,3);
		$data .= '   '.substr($ee_data['sin'],3,3);
		$data .= '   '.substr($ee_data['sin'],6,3);

		return $data;
	}

	function filterPDFGetCompany_Address( $ee_data ) {
		$data = NULL;
		if ( isset( $this->data['company_address1']) AND $this->data['company_address1'] != '' ) {
			$data .= $this->data['company_address1'] ."\n";
		}
		if ( isset($this->data['company_address2']) AND $this->data['company_address2'] != '' ) {
			$data .= $this->data['company_address2'] ."\n";
		}
		if ( isset($this->data['company_address3']) AND $this->data['company_address3'] != '' ) {
			$data .= $this->data['company_address3'] ."\n";
		}
		if ( isset($this->data['company_city']) ) {
			$data .= $this->data['company_city'];
		}
		if ( isset($this->data['company_province']) ) {
			$data .= ', '.$this->data['company_province'];
		}
		if ( isset($this->data['company_postal_code']) ) {
			$data .= ' '.$this->data['company_postal_code'];
		}

		return $data;
	}

	function filterPDFGetAddress( $ee_data ) {
		$data = NULL;
		if ( isset($ee_data['address1']) AND $ee_data['address1'] != '' ) {
			$data .= $ee_data['address1'] ."\n";
		}
		if ( isset($ee_data['address2']) AND $ee_data['address2'] != ''  ) {
			$data .= $ee_data['address2'] ."\n";
		}
		if ( isset($ee_data['address3']) AND $ee_data['address3'] != ''  ) {
			$data .= $ee_data['address3'] ."\n";
		}
		if ( isset($ee_data['city']) ) {
			$data .= $ee_data['city'];
		}
		if ( isset($ee_data['province']) ) {
			$data .= ', '.$ee_data['province'];
		}
		if ( isset($ee_data['postal_code']) ) {
			$data .= ' '.$ee_data['postal_code'];
		}

		return $data;
	}

	/*
	 Display/Exporting functions.
	*/
	function compileT4Summary() {
		//Import original Gov't supplied PDF.
		$pagecount = $this->getPDFObject()->setSourceFile( $this->src_pdf_t4summary );
		$tplidx = $this->getPDFObject()->ImportPage(1);
		$tplidx_back = $this->getPDFObject()->ImportPage(2);

		$this->setFont(); //Default font settings;
		$this->setDecimalFieldSize(11);

		Debug::Arr($this->form_layout_data, 'Form Layout Data', __FILE__, __LINE__, __METHOD__,10);

		$this->getPDFObject()->AddPage();
		if ( $this->getShowBackGround() == TRUE ) {
			$this->getPDFObject()->useTemplate($tplidx, -5, 0 );
		}

		$sum_data = $this->t4sum_data;
		foreach( $this->form_layout_data['t4_summary'] as $field => $field_layout_data ) {
			//Use this switch statement to have custom formatting based on the column name.
			switch( $field ) {
				case 'year':
					$this->drawCell(substr($this->data['year'],2,2), $field_layout_data );
					break;
				default:
					Debug::Text('Field: '. $field, __FILE__, __LINE__, __METHOD__,10);
					if ( isset($sum_data[$field]) ) {
						$data = $sum_data[$field];
					} elseif ( isset($this->data[$field]) ) {
						$data = $this->data[$field];
					}

					$filter_function_name = 'filterPDFGet'.$field;
					if ( method_exists($this, $filter_function_name) ) {
						$data = $this->$filter_function_name( $ee_data );
					}

					if ( !isset($data) AND $this->getShowBorder() == TRUE ) {
						$data = $field;
					}

					if ( !isset($data) ) {
						$data = NULL;
					}

					//Don't display numeric fields with 0 amounts.
					if ( !is_numeric( $data ) OR (is_numeric( $data ) AND $data != 0 ) ) {
						$this->drawCell($data, $field_layout_data );
					}
					break;
			}
			unset($data);
		}

		if ( $this->getShowInstructionPage() == TRUE ) {
			$this->getPDFObject()->AddPage();
			$this->getPDFObject()->useTemplate($tplidx_back, -5, 0);
		}

		return TRUE;
	}

	function compileT4() {
		//Import original Gov't supplied PDF.
		$pagecount = $this->getPDFObject()->setSourceFile( $this->src_pdf_t4 );
		$tplidx = $this->getPDFObject()->ImportPage(1);
		$tplidx_back = $this->getPDFObject()->ImportPage(2);

		$this->setFont(); //Default font settings;
		$this->setDecimalFieldSize(11);

		if ( $this->getType() == 'government') {
			$employees_per_page = 2;
			$n=1; //Loop the same employee twice.
		} else {
			$employees_per_page = 1;
			$n=2; //Loop the same employee twice.
		}

		Debug::Arr($this->form_layout_data, 'Form Layout Data', __FILE__, __LINE__, __METHOD__,10);
		if ( is_array( $this->t4_data ) ) {
			$e=0;
			foreach( $this->t4_data as $ee_row => $ee_data ) {
				if ( $e == 0 OR $e % $employees_per_page == 0 ) {
					$this->getPDFObject()->AddPage();
					if ( $this->getShowBackGround() == TRUE ) {
						$this->getPDFObject()->useTemplate($tplidx, -5, 0 );
					}
				}

				for( $i=0; $i < $n; $i++ ) {
					$bottom_y_offset = 0;
					if ( $employees_per_page == 2 ) {
						if ( $e > 0 AND $e % $employees_per_page != 0 ) {
							$bottom_y_offset = 139.5;
						}
					} else {
						if ( $i % 2 == 0 ) {
							$bottom_y_offset = 139.5;
						}
					}

					foreach( $this->form_layout_data['t4'] as $field => $field_layout_data ) {
						//Use this switch statement to have custom formatting based on the column name.
						switch( $field ) {
							default:
								Debug::Text('Field: '. $field, __FILE__, __LINE__, __METHOD__,10);
								if ( isset($ee_data[$field]) ) {
									$data = $ee_data[$field];
								} elseif ( isset($this->data[$field]) ) {
									$data = $this->data[$field];
								}

								$filter_function_name = 'filterPDFGet'.$field;
								if ( method_exists($this, $filter_function_name) ) {
									$data = $this->$filter_function_name( $ee_data );
								}

								if ( !isset($data) AND $this->getShowBorder() == TRUE ) {
									$data = $field;
								}

								if ( !isset($data) ) {
									$data = NULL;
								}

								//Don't display numeric fields with 0 amounts.
								if ( !is_numeric( $data ) OR (is_numeric( $data ) AND $data != 0 ) ) {
									$this->drawCell($data, $field_layout_data, 0, $bottom_y_offset );
								}
								break;
						}
						unset($data);
					}
				}
				$i=0;
				$e++;

				if ( $this->getShowInstructionPage() == TRUE ) {
					if ( $employees_per_page == 1 OR ( $employees_per_page == 2 AND  $e % $employees_per_page == 0 ) ) {
						$this->getPDFObject()->AddPage();
						$this->getPDFObject()->useTemplate($tplidx_back, 0, 0);
					}
				}
			}
		}
		return TRUE;
	}

	function compileT4A() {
		//Import original Gov't supplied PDF.
		$pagecount = $this->getPDFObject()->setSourceFile( $this->src_pdf_t4a );
		$tplidx = $this->getPDFObject()->ImportPage(1);
		$tplidx_back = $this->getPDFObject()->ImportPage(2);

		$this->setFont(); //Default font settings;
		$this->setDecimalFieldSize(8);

		if ( $this->getType() == 'government') {
			$employees_per_page = 3;
			$n=1; //Loop the same employee twice.
		} else {
			$employees_per_page = 1;
			$n=3; //Loop the same employee twice.
		}

		Debug::Arr($this->form_layout_data, 'Form Layout Data', __FILE__, __LINE__, __METHOD__,10);
		if ( is_array( $this->t4a_data ) ) {
			$e=0;
			foreach( $this->t4a_data as $ee_row => $ee_data ) {
				if ( $e == 0 OR $e % $employees_per_page == 0 ) {
					$this->getPDFObject()->AddPage();
					if ( $this->getShowBackGround() == TRUE ) {
						$this->getPDFObject()->useTemplate($tplidx, -5, 0 );
					}
				}

				for( $i=0; $i < $n; $i++ ) {
					$bottom_y_offset = 0;
					if ( $employees_per_page == 3 ) {
						if ( $e > 0 AND $e % $employees_per_page != 0 ) {
							$bottom_y_offset = ($e % 3)*101+(($e % 3)*0.55);
						}
					} else {
						if ( $i > 0 ) {
							$bottom_y_offset = ($i % 3)*101+(($i % 3)*0.55);
						}
					}

					foreach( $this->form_layout_data['t4a'] as $field => $field_layout_data ) {
						//Use this switch statement to have custom formatting based on the column name.
						switch( $field ) {
							default:
								Debug::Text('Field: '. $field, __FILE__, __LINE__, __METHOD__,10);
								if ( isset($ee_data[$field]) ) {
									$data = $ee_data[$field];
								} elseif ( isset($this->data[$field]) ) {
									$data = $this->data[$field];
								}

								$filter_function_name = 'filterPDFGet'.$field;
								if ( method_exists($this, $filter_function_name) ) {
									$data = $this->$filter_function_name( $ee_data );
								}

								if ( !isset($data) AND $this->getShowBorder() == TRUE ) {
									$data = $field;
								}

								if ( !isset($data) ) {
									$data = NULL;
								}

								//Don't display numeric fields with 0 amounts.
								if ( !is_numeric( $data ) OR (is_numeric( $data ) AND $data != 0 ) ) {
									$this->drawCell($data, $field_layout_data, 0, $bottom_y_offset );
								}
								break;
						}
						unset($data);
					}
				}
				$i=0;
				$e++;

				if ( $this->getShowInstructionPage() == TRUE ) {
					if ( $employees_per_page == 1 OR ( $employees_per_page == 3 AND  $e % $employees_per_page == 0 ) ) {
						$this->getPDFObject()->AddPage();
						$this->getPDFObject()->useTemplate($tplidx_back, 0, -100);
					}
				}
			}
		}
		return TRUE;
	}

	function compileT4ASummary() {
		//Import original Gov't supplied PDF.
		$pagecount = $this->getPDFObject()->setSourceFile( $this->src_pdf_t4asummary );
		$tplidx = $this->getPDFObject()->ImportPage(1);
		$tplidx_back = $this->getPDFObject()->ImportPage(2);

		$this->setFont(); //Default font settings;
		$this->setDecimalFieldSize(6.5);

		Debug::Arr($this->form_layout_data, 'Form Layout Data', __FILE__, __LINE__, __METHOD__,10);

		$this->getPDFObject()->AddPage();
		if ( $this->getShowBackGround() == TRUE ) {
			$this->getPDFObject()->useTemplate($tplidx, -5, 0 );
		}

		$sum_data = $this->t4asum_data;
		foreach( $this->form_layout_data['t4a_summary'] as $field => $field_layout_data ) {
			//Use this switch statement to have custom formatting based on the column name.
			switch( $field ) {
				case 'year':
					$this->drawCell(substr($this->data['year'],2,2), $field_layout_data );
					break;
				default:
					Debug::Text('Field: '. $field, __FILE__, __LINE__, __METHOD__,10);
					if ( isset($sum_data[$field]) ) {
						$data = $sum_data[$field];
					} elseif ( isset($this->data[$field]) ) {
						$data = $this->data[$field];
					}

					$filter_function_name = 'filterPDFGet'.$field;
					if ( method_exists($this, $filter_function_name) ) {
						$data = $this->$filter_function_name( $ee_data );
					}

					if ( !isset($data) AND $this->getShowBorder() == TRUE ) {
						$data = $field;
					}

					if ( !isset($data) ) {
						$data = NULL;
					}

					//Don't display numeric fields with 0 amounts.
					if ( !is_numeric( $data ) OR (is_numeric( $data ) AND $data != 0 ) ) {
						$this->drawCell($data, $field_layout_data );
					}
					break;
			}
			unset($data);
		}

		if ( $this->getShowInstructionPage() == TRUE ) {
			$this->getPDFObject()->AddPage();
			$this->getPDFObject()->useTemplate($tplidx_back, -5, 0);
		}

		return TRUE;
	}

	function _getPDF() {
		$this->setFileName( 'T4_'. $this->getYear().'.pdf' );

		return TRUE;
	}
	function _getXML() {
		return TRUE;
	}
}

class T4Employee {
	var $t4_data; //Holds all data.

	/*
	 Employee contact information
	*/
	function getFirstName() {
		return $this->t4_data['first_name'];
	}
	function setFirstName( $value ) {
		$this->t4_data['first_name'] = trim($value);
		return TRUE;
	}

	function getMiddleName() {
		return $this->t4_data['middle_name'];
	}
	function setMiddleName( $value ) {
		$this->t4_data['middle_name'] = trim(substr( $value, 0, 1) );
		return TRUE;
	}

	function getLastName() {
		return $this->t4_data['last_name'];
	}
	function setLastName( $value ) {
		$this->t4_data['last_name'] = trim($value);
		return TRUE;
	}

	function getSIN() {
		return $this->t4_data['sin'];
	}
	function setSIN( $value ) {
		$this->t4_data['sin'] = trim($value);
		return TRUE;
	}

	function getAddress1() {
		return $this->t4_data['address1'];
	}
	function setAddress1( $value ) {
		$this->t4_data['address1'] = trim($value);
		return TRUE;
	}

	function getAddress2() {
		return $this->t4_data['address2'];
	}
	function setAddress2( $value ) {
		$this->t4_data['address2'] = trim($value);
		return TRUE;
	}

	function getAddress3() {
		return $this->t4_data['address3'];
	}
	function setAddress3( $value ) {
		$this->t4_data['address3'] = trim($value);
		return TRUE;
	}

	function getCity() {
		return $this->t4_data['city'];
	}
	function setCity( $value ) {
		$this->t4_data['city'] = trim($value);
		return TRUE;
	}

	function getProvince() {
		return $this->t4_data['province'];
	}
	function setProvince( $value ) {
		$this->t4_data['province'] = trim($value);
		return TRUE;
	}

	function getPostalCode() {
		return $this->t4_data['postal_code'];
	}
	function setPostalCode( $value ) {
		$this->t4_data['postal_code'] = trim($value);
		return TRUE;
	}

	function getEmploymentCode() {
		return $this->t4_data['employment_code'];
	}
	function setEmployementCode( $value ) {
		$this->t4_data['employment_code'] = trim($value);
		return TRUE;
	}

	/*
	 T4 form data
	*/
	function getExemptCPP() {
		return $this->t4_data['cpp_exempt'];
	}
	function setExemptCPP( $value ) {
		$this->t4_data['cpp_exempt'] = (bool)$value;
		return TRUE;
	}
	function getExemptEI() {
		return $this->t4_data['ei_exempt'];
	}
	function setExemptEI( $value ) {
		$this->t4_data['ei_exempt'] = (bool)$value;
		return TRUE;
	}


	function getEmploymentIncome() {
		return $this->t4_data['employment_income'];
	}
	function setEmploymentIncome( $value ) {
		$this->t4_data['employment_income'] = (float)$value;
		return TRUE;
	}
	function getIncomeTax() {
		return $this->t4_data['income_tax'];
	}
	function setIncomeTax( $value ) {
		$this->t4_data['income_tax'] = (float)$value;
		return TRUE;
	}
	function getEmployeeCPP() {
		return $this->t4_data['employee_cpp'];
	}
	function setEmployeeCPP( $value ) {
		$this->t4_data['employee_cpp'] = (float)$value;
		return TRUE;
	}
	function getEIEarnings() {
		return $this->t4_data['ei_earnings'];
	}
	function setEIEarnings( $value ) {
		$this->t4_data['ei_earnings'] = (float)$value;
		return TRUE;
	}
	function getCPPEarnings() {
		return $this->t4_data['cpp_earnings'];
	}
	function setCPPEarnings( $value ) {
		$this->t4_data['cpp_earnings'] = (float)$value;
		return TRUE;
	}

	function getEmployeeEI() {
		return $this->t4_data['employee_ei'];
	}
	function setEmployeeEI( $value ) {
		$this->t4_data['employee_ei'] = (float)$value;
		return TRUE;
	}


	function getUnionDues() {
		return $this->t4_data['union_dues'];
	}
	function setUnionDues( $value ) {
		$this->t4_data['union_dues'] = (float)$value;
		return TRUE;
	}

	function getEmployeeRPP() {
		return $this->t4_data['employee_rpp'];
	}
	function setEmployeeRPP( $value ) {
		$this->t4_data['employee_rpp'] = (float)$value;
		return TRUE;
	}

	function getCharityDonations() {
		return $this->t4_data['charity'];
	}
	function setCharityDonations( $value ) {
		$this->t4_data['charity'] = (float)$value;
		return TRUE;
	}

	function getPensionAdjustment() {
		return $this->t4_data['pension_adjustment'];
	}
	function setPensionAdjustment( $value ) {
		$this->t4_data['pension_adjustment'] = (float)$value;
		return TRUE;
	}

	function getOtherBox1Code() {
		return $this->t4_data['other_box_0_code'];
	}
	function setOtherBox1Code( $value ) {
		$this->t4_data['other_box_0_code'] = trim($value);
		return TRUE;
	}
	function getOtherBox2Code() {
		return $this->t4_data['other_box_1_code'];
	}
	function setOtherBox2Code( $value ) {
		$this->t4_data['other_box_1_code'] = trim($value);
		return TRUE;
	}
	function getOtherBox3Code() {
		return $this->t4_data['other_box_2_code'];
	}
	function setOtherBox3Code( $value ) {
		$this->t4_data['other_box_2_code'] = trim($value);
		return TRUE;
	}
	function getOtherBox4Code() {
		return $this->t4_data['other_box_3_code'];
	}
	function setOtherBox4Code( $value ) {
		$this->t4_data['other_box_3_code'] = trim($value);
		return TRUE;
	}
	function getOtherBox5Code() {
		return $this->t4_data['other_box_4_code'];
	}
	function setOtherBox5Code( $value ) {
		$this->t4_data['other_box_4_code'] = trim($value);
		return TRUE;
	}
	function getOtherBox6Code() {
		return $this->t4_data['other_box_5_code'];
	}
	function setOtherBox6Code( $value ) {
		$this->t4_data['other_box_5_code'] = trim($value);
		return TRUE;
	}

	function getOtherBox1() {
		return $this->t4_data['other_box_0'];
	}
	function setOtherBox1( $value ) {
		$this->t4_data['other_box_0'] = trim($value);
		return TRUE;
	}
	function getOtherBox2() {
		return $this->t4_data['other_box_1'];
	}
	function setOtherBox2( $value ) {
		$this->t4_data['other_box_1'] = trim($value);
		return TRUE;
	}
	function getOtherBox3() {
		return $this->t4_data['other_box_2'];
	}
	function setOtherBox3( $value ) {
		$this->t4_data['other_box_2'] = trim($value);
		return TRUE;
	}
	function getOtherBox4() {
		return $this->t4_data['other_box_3'];
	}
	function setOtherBox4( $value ) {
		$this->t4_data['other_box_3'] = trim($value);
		return TRUE;
	}
	function getOtherBox5() {
		return $this->t4_data['other_box_4'];
	}
	function setOtherBox5( $value ) {
		$this->t4_data['other_box_4'] = trim($value);
		return TRUE;
	}
	function getOtherBox6() {
		return $this->t4_data['other_box_5'];
	}
	function setOtherBox6( $value ) {
		$this->t4_data['other_box_5'] = trim($value);
		return TRUE;
	}
}

class T4Summary {
	var $t4sum_data; //Holds all data.

	function getTotalT4s() {
		return $this->t4sum_data['total_t4'];
	}
	function setTotalT4s( $value ) {
		$this->t4sum_data['total_t4'] = trim($value);
		return TRUE;
	}

	function getEmploymentIncome() {
		return $this->t4sum_data['employment_income'];
	}
	function setEmploymentIncome( $value ) {
		$this->t4sum_data['employment_income'] = (float)$value;
		return TRUE;
	}
	function getIncomeTax() {
		return $this->t4sum_data['income_tax'];
	}
	function setIncomeTax( $value ) {
		$this->t4sum_data['income_tax'] = (float)$value;
		return TRUE;
	}

	function getEmployeeCPP() {
		return $this->t4sum_data['employee_cpp'];
	}
	function setEmployeeCPP( $value ) {
		$this->t4sum_data['employee_cpp'] = (float)$value;
		return TRUE;
	}
	function getEmployeeEI() {
		return $this->t4sum_data['employee_ei'];
	}
	function setEmployeeEI( $value ) {
		$this->t4sum_data['employee_ei'] = (float)$value;
		return TRUE;
	}

	function getEmployerCPP() {
		return $this->t4sum_data['employer_cpp'];
	}
	function setEmployerCPP( $value ) {
		$this->t4sum_data['employer_cpp'] = (float)$value;
		return TRUE;
	}
	function getEmployerEI() {
		return $this->t4sum_data['employer_ei'];
	}
	function setEmployerEI( $value ) {
		$this->t4sum_data['employer_ei'] = (float)$value;
		return TRUE;
	}


	function getEmployeeRPP() {
		return $this->t4sum_data['employee_rpp'];
	}
	function setEmployeeRPP( $value ) {
		$this->t4sum_data['employee_rpp'] = (float)$value;
		return TRUE;
	}

	function getPensionAdjustment() {
		return $this->t4sum_data['pension_adjustment'];
	}
	function setPensionAdjustment( $value ) {
		$this->t4sum_data['pension_adjustment'] = (float)$value;
		return TRUE;
	}


	function getTotalDeductions() {
		return $this->t4sum_data['total_deduction'];
	}
	function setTotalDeductions( $value ) {
		$this->t4sum_data['total_deduction'] = (float)$value;
		return TRUE;
	}

}

class T4AEmployee {
	var $t4a_data; //Holds all data.

	/*
	 Employee contact information
	*/
	function getFirstName() {
		return $this->t4a_data['first_name'];
	}
	function setFirstName( $value ) {
		$this->t4a_data['first_name'] = trim($value);
		return TRUE;
	}

	function getMiddleName() {
		return $this->t4a_data['middle_name'];
	}
	function setMiddleName( $value ) {
		$this->t4a_data['middle_name'] = trim(substr( $value, 0, 1) );
		return TRUE;
	}

	function getLastName() {
		return $this->t4a_data['last_name'];
	}
	function setLastName( $value ) {
		$this->t4a_data['last_name'] = trim($value);
		return TRUE;
	}

	function getSIN() {
		return $this->t4a_data['sin'];
	}
	function setSIN( $value ) {
		$this->t4a_data['sin'] = trim($value);
		return TRUE;
	}

	function getAddress1() {
		return $this->t4a_data['address1'];
	}
	function setAddress1( $value ) {
		$this->t4a_data['address1'] = trim($value);
		return TRUE;
	}

	function getAddress2() {
		return $this->t4a_data['address2'];
	}
	function setAddress2( $value ) {
		$this->t4a_data['address2'] = trim($value);
		return TRUE;
	}

	function getAddress3() {
		return $this->t4a_data['address3'];
	}
	function setAddress3( $value ) {
		$this->t4a_data['address3'] = trim($value);
		return TRUE;
	}

	function getCity() {
		return $this->t4a_data['city'];
	}
	function setCity( $value ) {
		$this->t4a_data['city'] = trim($value);
		return TRUE;
	}

	function getProvince() {
		return $this->t4a_data['province'];
	}
	function setProvince( $value ) {
		$this->t4a_data['province'] = trim($value);
		return TRUE;
	}

	function getPostalCode() {
		return $this->t4a_data['postal_code'];
	}
	function setPostalCode( $value ) {
		$this->t4a_data['postal_code'] = trim($value);
		return TRUE;
	}

	/*
	 T4a form data
	*/
	function getLumpSumPayment() {
		return $this->t4a_data['lump_sum_payment'];
	}
	function setLumpSumPayment( $value ) {
		$this->t4a_data['lump_sum_payment'] = (float)$value;
		return TRUE;
	}
	function getIncomeTax() {
		return $this->t4a_data['income_tax'];
	}
	function setIncomeTax( $value ) {
		$this->t4a_data['income_tax'] = (float)$value;
		return TRUE;
	}

	function getOtherIncome() {
		return $this->t4a_data['other_income'];
	}
	function setOtherIncome( $value ) {
		$this->t4a_data['other_income'] = (float)$value;
		return TRUE;
	}
	function getEligibleRetiringAllowance() {
		return $this->t4a_data['eligible_retiring_allowance'];
	}
	function setEligibleRetiringAllowance( $value ) {
		$this->t4a_data['eligible_retiring_allowance'] = (float)$value;
		return TRUE;
	}
	function getNonEligibleRetiringAllowance() {
		return $this->t4a_data['non_eligible_retiring_allowance'];
	}
	function setNonEligibleRetiringAllowance( $value ) {
		$this->t4a_data['non_eligible_retiring_allowance'] = (float)$value;
		return TRUE;
	}

	function getEmployeeRPP() {
		return $this->t4a_data['employee_rpp'];
	}
	function setEmployeeRPP( $value ) {
		$this->t4a_data['employee_rpp'] = (float)$value;
		return TRUE;
	}

	function getCharityDonations() {
		return $this->t4a_data['charity'];
	}
	function setCharityDonations( $value ) {
		$this->t4a_data['charity'] = (float)$value;
		return TRUE;
	}

	function getPension() {
		return $this->t4a_data['pension'];
	}
	function setPension( $value ) {
		$this->t4a_data['pension'] = (float)$value;
		return TRUE;
	}

	function getPensionAdjustment() {
		return $this->t4a_data['pension_adjustment'];
	}
	function setPensionAdjustment( $value ) {
		$this->t4a_data['pension_adjustment'] = (float)$value;
		return TRUE;
	}
}

class T4ASummary {
	var $t4asum_data; //Holds all data.

	/*
	 T4a Summary form data
	*/
	function getTotalT4As() {
		return $this->t4asum_data['total_t4a'];
	}
	function setTotalT4As( $value ) {
		$this->t4asum_data['total_t4a'] = trim($value);
		return TRUE;
	}

	function getLumpSumPayment() {
		return $this->t4asum_data['lump_sum_payment'];
	}
	function setLumpSumPayment( $value ) {
		$this->t4asum_data['lump_sum_payment'] = (float)$value;
		return TRUE;
	}
	function getIncomeTax() {
		return $this->t4asum_data['income_tax'];
	}
	function setIncomeTax( $value ) {
		$this->t4asum_data['income_tax'] = (float)$value;
		return TRUE;
	}

	function getOtherIncome() {
		return $this->t4asum_data['other_income'];
	}
	function setOtherIncome( $value ) {
		$this->t4asum_data['other_income'] = (float)$value;
		return TRUE;
	}
	function getEligibleRetiringAllowance() {
		return $this->t4asum_data['eligible_retiring_allowance'];
	}
	function setEligibleRetiringAllowance( $value ) {
		$this->t4asum_data['eligible_retiring_allowance'] = (float)$value;
		return TRUE;
	}
	function getNonEligibleRetiringAllowance() {
		return $this->t4asum_data['non_eligible_retiring_allowance'];
	}
	function setNonEligibleRetiringAllowance( $value ) {
		$this->t4asum_data['non_eligible_retiring_allowance'] = (float)$value;
		return TRUE;
	}

	function getEmployeeRPP() {
		return $this->t4asum_data['employee_rpp'];
	}
	function setEmployeeRPP( $value ) {
		$this->t4asum_data['employee_rpp'] = (float)$value;
		return TRUE;
	}

	function getCharityDonations() {
		return $this->t4asum_data['charity'];
	}
	function setCharityDonations( $value ) {
		$this->t4asum_data['charity'] = (float)$value;
		return TRUE;
	}

	function getPension() {
		return $this->t4asum_data['pension'];
	}
	function setPension( $value ) {
		$this->t4asum_data['pension'] = (float)$value;
		return TRUE;
	}

	function getPensionAdjustment() {
		return $this->t4asum_data['pension_adjustment'];
	}
	function setPensionAdjustment( $value ) {
		$this->t4asum_data['pension_adjustment'] = (float)$value;
		return TRUE;
	}
}

?>
