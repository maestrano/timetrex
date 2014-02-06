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
 * $Revision: 9883 $
 * $Id: TTPDF.class.php 9883 2013-05-14 22:00:44Z ipso $
 * $Date: 2013-05-14 15:00:44 -0700 (Tue, 14 May 2013) $
 */
require_once(Environment::getBasePath() .'/classes/tcpdf/tcpdf.php');

//Automatically create TCPDF cache path if it doesn't exist.
if ( !file_exists( K_PATH_CACHE ) ) {
	mkdir( K_PATH_CACHE );
}

/**
 * @package Core
 */
class TTPDF extends tcpdf {
	protected function _freadint($f) {
		//Read a 4-byte integer from file
		$a=unpack('Ni',fread($f,4));

		//Fixed bug in PHP v5.2.1 and less where it is returning a huge negative number.
		//See: http://ca3.php.net/manual/en/function.unpack.php
		//If you are trying to make unpack 'N' work with unsigned long on 64 bit machines, you should take a look to this bug:
		//http://bugs.php.net/bug.php?id=40543
		$b = sprintf("%b", $a['i']); // binary representation
		if ( strlen($b) == 64 ) {
			$new = substr($b, 33);
			$a['i'] = bindec($new);
		}
		return $a['i'];
	}

	function __construct($orientation='P', $unit='mm', $format='A4', $encoding='UTF-8', $diskcache=FALSE) {
		if ( TTi18n::getPDFDefaultFont() != 'freeserif' AND $encoding == 'ISO-8859-1' ) {
			parent::__construct($orientation, $unit, $format, FALSE, 'ISO-8859-1', $diskcache); //Make sure TCPDF constructor is called with all the arguments
		} else {
			parent::__construct($orientation, $unit, $format, TRUE, $encoding, $diskcache); //Make sure TCPDF constructor is called with all the arguments
		}
		Debug::Text('PDF Encoding: '. $encoding, __FILE__, __LINE__, __METHOD__,10);

		/*
		if ( TTi18n::getPDFDefaultFont() == 'freeserif' ) {
			Debug::Text('Using unicode PDF: Font: freeserif Unicode: '. (int)$unicode .' Encoding: '. $encoding, __FILE__, __LINE__, __METHOD__,10);
		} else {
			//If we're only using English, default to faster non-unicode settings.
			//unicode=FALSE and encoding='ISO-8859-1' is about 20-30% faster.
			Debug::Text('Using non-unicode PDF: Font: helvetica Unicode: '. (int)$unicode .' Encoding: '. $encoding, __FILE__, __LINE__, __METHOD__,10);
			parent::__construct($orientation, $unit, $format, FALSE, 'ISO-8859-1', $diskcache); //Make sure TCPDF constructor is called with all the arguments
		}
		*/

		//Using freeserif font enabling font subsetting is slow and produces PDFs at least 1mb. Helvetica is fine though.
		$this->setFontSubsetting(TRUE); //When enabled, makes PDFs smaller, but severly slows down TCPDF if enabled. (+6 seconds per PDF)

		$this->SetCreator( APPLICATION_NAME.' '. getTTProductEditionName() .' v'. APPLICATION_VERSION );

		return TRUE;
	}

	//TCPDF oddly enough defines standard header/footers, instead of disabling them
	//in every script, just override them as blank here.
	function header() {
		return TRUE;
	}
	function footer() {
		return TRUE;
	}
}

?>
