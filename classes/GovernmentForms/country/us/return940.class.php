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

/**
 * @package GovernmentForms
 */

//This is the header record for submitting XML forms to the CRA.
include_once( 'US.class.php' );
class GovernmentForms_US_RETURN940 extends GovernmentForms_US {
	public $xml_schema = '94x/94x/Return940.xsd';

	public function getFilterFunction( $name ) {
		$variable_function_map = array(
										//'year' => 'isNumeric',
										//'ein' => array( 'stripNonNumeric', 'isNumeric'),
						  );

		if ( isset($variable_function_map[$name]) ) {
			return $variable_function_map[$name];
		}

		return FALSE;
	}

	public function getTemplateSchema( $name = NULL ) {
		$template_schema = array();

		if ( isset($template_schema[$name]) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

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

	function _outputXML() {
        
		$xml = new SimpleXMLElement('<ReturnData xsi:schemaLocation="http://www.irs.gov/efile ReturnData940.xsd" xmlns="http://www.irs.gov/efile" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></ReturnData>'); //IRS940 must be wrapped in <ReturnData></ReturnData>
		$xml->addAttribute('documentCount', 0); // The number of return documents in the return.
        
        $this->setXMLObject( $xml );
        
        $xml->addChild('ContentLocation', '-'); // Must be unique within the transmission file and must match the value on the MIME Content-Location: line        
        
        $xml->addChild('ReturnHeader94x');
        $xml->ReturnHeader94x->addAttribute('documentId', '-'); // Must be unique within the return.
        
        $xml->ReturnHeader94x->addChild('TaxPeriodEndDate', $this->TaxPeriodEndDate); 
        $xml->ReturnHeader94x->addChild('ReturnType', $this->ReturnType);
        
        $xml->ReturnHeader94x->addChild('Business');
        
        $xml->ReturnHeader94x->Business->addChild('EIN', $this->ein);
        $xml->ReturnHeader94x->Business->addChild('BusinessName1', $this->BusinessName1);
        $xml->ReturnHeader94x->Business->addChild('BusinessNameControl', $this->BusinessNameControl);
        
        $xml->ReturnHeader94x->Business->addChild('USAddress');
        $xml->ReturnHeader94x->Business->USAddress->addChild('AddressLine', $this->AddressLine);
        $xml->ReturnHeader94x->Business->USAddress->addChild('City', $this->City);
        $xml->ReturnHeader94x->Business->USAddress->addChild('State', $this->State);
        $xml->ReturnHeader94x->Business->USAddress->addChild('ZIPCode', $this->ZIPCode);
            
        $xml->addChild('IRS940');    
                
        
		return TRUE;
	}

	function _outputPDF() {
		return FALSE;
	}
}
?>