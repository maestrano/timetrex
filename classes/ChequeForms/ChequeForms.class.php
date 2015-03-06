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


/**
 * @package ChequeForms
 */
class ChequeForms {
	var $objs = NULL;

	var $tcpdf_dir = '../tcpdf/'; //TCPDF class directory.
	var $fpdi_dir = '../fpdi/'; //FPDI class directory.

	function __construct() {
		return TRUE;
	}

	function getFormObject( $form ) {
		$class_name = 'ChequeForms';
		$class_name .= '_'.$form;
		
        $class_directory = dirname( __FILE__ );
		$class_file_name = $class_directory . DIRECTORY_SEPARATOR . strtolower($form) .'.class.php';

		Debug::text('Class Directory: '. $class_directory, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Class File Name: '. $class_file_name, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Class Name: '. $class_name, __FILE__, __LINE__, __METHOD__, 10);

		if ( file_exists( $class_file_name ) ) {
			include_once( $class_file_name );

			$obj = new $class_name;
			$obj->setClassDirectory( $class_directory );
			$obj->default_font = TTi18n::getPDFDefaultFont();

			return $obj;
		} else {
			Debug::text('Class File does not exist!', __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	function addForm( $obj ) {
		if ( is_object( $obj ) ) {
			$this->objs[] = $obj;

			return TRUE;
		}

		return FALSE;
	}

	function Output( $type ) {
		$type = strtolower($type);

		//Initialize PDF object so all subclasses can access it.
		//Loop through all objects and combine the output from each into a single document.
		if ( $type == 'pdf' ) {
            $pdf = new TTPDF();
			$pdf->setMargins(0, 0, 0, 0);
			$pdf->SetAutoPageBreak(FALSE);
			//$pdf->setFontSubsetting(FALSE);

			foreach( (array)$this->objs as $obj ) {
				$obj->setPDFObject( $pdf );
				$obj->Output( $type );
			}

			return $pdf->Output('', 'S');
		}
	}
}
?>
