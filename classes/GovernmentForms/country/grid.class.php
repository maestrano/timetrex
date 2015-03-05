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


include_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'GovernmentForms_Base.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_grid extends GovernmentForms_Base {

	public $pdf_template_pages = 1;

	public $grid_width = 20;
	public $grid_height = 10;

	public function getFilterFunction( $name ) {
		return FALSE;
	}

	function getTemplate() {
		return $this->pdf_template;
	}
	function setTemplate( $value ) {
		$this->pdf_template = $value;
		return TRUE;
	}

	function getTemplatePages() {
		return $this->pdf_template_pages;
	}
	function setTemplatePages( $value ) {
		$this->pdf_template_pages = $value;
		return TRUE;
	}

	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();


		if ( $this->getShowBackground() == TRUE AND $this->getTemplate() != '' ) {
			$pdf->setSourceFile( $this->getTemplate() );

			for( $i=1; $i <= $this->getTemplatePages(); $i++ ) {
				$this->template_index[$i] = $pdf->ImportPage($i);
			}
		}

		$pdf->AddPage();

		if ( isset($this->template_index[1]) ) {
			$pdf->useTemplate( $this->template_index[1], $this->getTemplateOffsets('x'), $this->getTemplateOffsets('y') );
		}


		$pdf->SetFont( $this->default_font, '', 4 );

		//Red
		//$pdf->SetTextColor( 255, 0, 0 );
		//$pdf->setDrawColor( 255, 0, 0 );

		//Blue
		$pdf->SetTextColor( 0, 0, 255 );
		$pdf->setDrawColor( 0, 0, 255 );

		//Draw grid.
		$continue = TRUE;
		$i=0;

		$x=0;
		$y=0;
		$page = 1;
		while( $continue AND $i < 1000000 ) {
			$pdf->setXY( $x, $y );
			$pdf->Cell( $this->grid_width, $this->grid_height, $x . 'x' . $y , 1, 0, 'L', 0 );

			$x = $x + $this->grid_width;
			if ( $x > $pdf->getPageWidth() ) {
				$x = 0;
				$y = $y + $this->grid_height;
			}

			if ( $y > $pdf->getPageHeight() AND $page < $this->getTemplatePages() ) {
				$page++;

				$pdf->AddPage();
				$pdf->useTemplate( $this->template_index[$page], $this->getTemplateOffsets('x'), $this->getTemplateOffsets('y') );

				$x = 0;
				$y = 0;
			} elseif ( $y > $pdf->getPageHeight() AND $page == $this->getTemplatePages() ) {
				$continue = FALSE;
				break;
			}

			$i++;
		}

		return TRUE;
	}
}
?>