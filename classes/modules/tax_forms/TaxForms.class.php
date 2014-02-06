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
 * $Id: ROEFactory.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

require_once(Environment::getBasePath() .'/classes/fpdi/fpdi.php');
/**
 * @package Modules\Other
 */
class TaxForms {
	var $tax_forms; //data array
	var $form_layout_data = NULL; //Form layout data, contains coordinates of all boxes.
	var $pdf = NULL; //PDF object

	function __construct() {
		return TRUE;
	}

	function getPDFObject() {
		if ( $this->pdf == NULL ) {
			$this->pdf = new FPDI( 'P', 'mm' );
			$this->pdf->SetAutoPageBreak(FALSE);

			$this->pdf->setLeftMargin( $this->getLeftMargin() );
			$this->pdf->setRightMargin( $this->getRightMargin() );
			$this->pdf->setTopMargin( $this->getTopMargin() );

			//$this->pdf->setHeaderFont( array($this->getDefaultFont(),$this->getDefaultFont(),$this->getDefaultFont() ) );
			//$this->pdf->setFooterFont( $this->getDefaultFont() );
		}
		return $this->pdf;
	}

	function getDecimalFieldSize() {
		if ( isset($this->tax_forms['decimal_field_size']) ) {
			return $this->tax_forms['decimal_field_size'];
		}

		return '11';
	}
	function setDecimalFieldSize( $value ) {
		$this->tax_forms['decimal_field_size'] = trim($value);
		return TRUE;
	}

	//Set the type of form to display/print. Typically this would be:
	// government or employee.
	function getType() {
		if ( isset($this->tax_forms['type']) ) {
			return $this->tax_forms['type'];
		}

		return FALSE;
	}
	function setType( $value ) {
		$this->tax_forms['type'] = trim($value);
		return TRUE;
	}

	/*
	 Default font help functions
	*/
	function getDefaultFont() {
		if ( isset($this->tax_forms['default_font']) ) {
			return $this->tax_forms['default_font'];
		}

		return 'freeserif';
	}
	function setDefaultFont( $value ) {
		$this->tax_forms['default_font'] = trim($value);
		return TRUE;
	}
	function getDefaultFontSize() {
		if ( isset($this->tax_forms['default_font_size']) ) {
			return $this->tax_forms['default_font_size'];
		}

		return '10';
	}
	function setDefaultFontSize( $value ) {
		$this->tax_forms['default_font_size'] = (float)$value;
		return TRUE;
	}
	function getDefaultFontStyle() {
		if ( isset($this->tax_forms['default_font_style']) ) {
			return $this->tax_forms['default_font_style'];
		}

		return '';
	}
	function setDefaultFontStyle( $value ) {
		$this->tax_forms['default_font_style'] = trim($value);
		return TRUE;
	}
	function setFont() {
		$this->getPDFObject()->SetFont( $this->getDefaultFont(), $this->getDefaultFontStyle(), $this->getDefaultFontSize() );
		return TRUE;
	}

	/*
	 Page layout/printing functions
	 */

	//Show/Hide the background form.
	function getShowBackGround() {
		if ( isset($this->tax_forms['background']) ) {
			return $this->tax_forms['background'];
		}

		return TRUE;
	}
	function setShowBackGround( $value ) {
		$this->tax_forms['background'] = (bool)$value;
		return TRUE;
	}

	//Includes the back instruction page
	function getShowInstructionPage() {
		if ( isset($this->tax_forms['instruction_page']) ) {
			return $this->tax_forms['instruction_page'];
		}

		return FALSE;
	}
	function setShowInstructionPage( $value ) {
		$this->tax_forms['instruction_page'] = (bool)$value;
		return FALSE;
	}

	function getShowBorder() {
		if ( isset($this->tax_forms['border']) ) {
			return $this->tax_forms['border'];
		}

		return 0;
	}
	function setShowBorder( $value ) {
		$this->tax_forms['border'] = (int)$value;
		return TRUE;
	}

	function getLeftMargin() {
		if ( isset($this->tax_forms['left_margin']) ) {
			return $this->tax_forms['left_margin'];
		}

		return 0;
	}
	function setLeftMargin( $value ) {
		$this->tax_forms['left_margin'] = (float)$value;
		return TRUE;
	}
	function getTopMargin() {
		if ( isset($this->tax_forms['top_margin']) ) {
			return $this->tax_forms['top_margin'];
		}
		return 0;
	}
	function setTopMargin( $value ) {
		$this->tax_forms['top_margin'] = (float)$value;
		return TRUE;
	}
	function getRightMargin() {
		if ( isset($this->tax_forms['right_margin']) ) {
			return $this->tax_forms['right_margin'];
		}
		return 0;
	}
	function setRightMargin( $value ) {
		if ( isset($this->tax_forms['right_margin']) ) {
			$this->tax_forms['right_margin'] = (float)$value;
		}
		return 0;
	}

	function getXOffset() {
		if ( isset($this->tax_forms['x_offset']) ) {
			return $this->tax_forms['x_offset'];
		}
		return 0;
	}
	function setXOffset( $value ) {
		$this->tax_forms['x_offset'] = (float)$value;
		return TRUE;
	}

	function getYOffset() {
		if ( isset($this->tax_forms['y_offset']) ) {
			return $this->tax_forms['y_offset'];
		}
		return 0;
	}
	function setYOffset( $value ) {
		$this->tax_forms['y_offset'] = (float)$value;
		return TRUE;
	}


	function getFileName() {
		if ( isset($this->tax_forms['file_name']) ) {
			return $this->tax_forms['file_name'];
		}

		return 'report.pdf';
	}
	function setFileName( $value ) {
		$this->tax_forms['file_name'] = trim($value);
		return TRUE;
	}

	/*
	 Box positioning array, so we can easily change the coordinates of all the boxes.
	 We should also group these boxes together so we can apply multiple offsets in a single form.
	 */

	/*
	 Display/Exporting functions.
	*/
	function drawCell( $data, $field_layout_data , $additional_x_offset = 0, $additional_y_offset = 0 ) {
		if ( isset($data) AND $data !== NULL OR $this->getShowBorder() == TRUE ) {
			Debug::Text('&nbsp;&nbsp;Data: '. $data, __FILE__, __LINE__, __METHOD__,10);

			if ( isset($field_layout_data['x']) AND $field_layout_data['y'] ) {
				$this->getPDFObject()->setXY( Misc::AdjustXY($field_layout_data['x'], $this->getXOffset()+$additional_x_offset ), Misc::AdjustXY($field_layout_data['y'], $this->getYOffset()+$additional_y_offset ) );
			}

			if ( !isset($field_layout_data['ln']) ) {
				$field_layout_data['ln'] = 0;
			}


			if ( isset($field_layout_data['fontsize']) ) {
				if ( !isset($field_layout_data['fontstyle']) ) {
					$field_layout_data['fontstyle'] = $this->getDefaultFontStyle();
				}
				$this->getPDFObject()->SetFont( $this->getDefaultFont(), $field_layout_data['fontstyle'],$field_layout_data['fontsize']);
			}

			if ( isset($field_layout_data['w']) AND isset($field_layout_data['h']) AND isset($field_layout_data['align'])) {
				if ( isset($field_layout_data['multicell']) ) {
					$this->getPDFObject()->MultiCell( $field_layout_data['w'], $field_layout_data['h'], $data, $this->getShowBorder(), $field_layout_data['align']);
				} else {
					if ( isset($field_layout_data['split_decimal']) ) {

						$this->getPDFObject()->Cell( $field_layout_data['w']-$this->getDecimalFieldSize(), $field_layout_data['h'], Misc::getBeforeDecimal( $data ), $this->getShowBorder(), 0, $field_layout_data['align']);
						$this->getPDFObject()->Cell( $this->getDecimalFieldSize(), $field_layout_data['h'], Misc::getAfterDecimal( $data ), $this->getShowBorder(), $field_layout_data['ln'], $field_layout_data['align']);
					} else {
						$this->getPDFObject()->Cell( $field_layout_data['w'], $field_layout_data['h'], $data, $this->getShowBorder(), $field_layout_data['ln'], $field_layout_data['align']);
					}
				}
			}

			if ( isset($field_layout_data['fontsize']) ) {
				$this->SetFont(); //Return to default font
			}
		}

		return TRUE;
	}

	function displayPDF() {
		$output = $this->getPDF();

		if ( Debug::getVerbosity() == 11 ) {
			Debug::Display();
		} else {
			Misc::FileDownloadHeader( $this->getFileName(), 'application/pdf', strlen($output));
			echo $output;
			exit;
		}
	}
	function getPDF() {
		$this->_getPDF();

		return $this->getPDFObject()->Output('','S');
	}


	function getXML() {
		return $this->_getXML();
	}

}
?>
