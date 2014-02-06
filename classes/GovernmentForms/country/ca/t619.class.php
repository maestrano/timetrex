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

//This is the header record for submitting XML forms to the CRA.
include_once( 'CA.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_CA_T619 extends GovernmentForms_CA {
	public $xml_schema = 'layout-topologie.xsd';

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

	function filterPhone( $value ) {
		//Strip non-digits.
		$value = $this->stripNonNumeric($value);

		return array( substr($value, 0,3), substr($value, 3,3), substr($value, 6,4) );
	}

	function _outputXML() {

		$xml = new SimpleXMLElement('<Submission xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="layout-topologie.xsd"></Submission>'); //T4 and T4 Summary must be wrapped in <Return></Return>
		$this->setXMLObject( $xml );

		$xml->addChild('T619');
		if ( $this->reference_id == '' ) {
			$this->reference_id = date('Ymd');
		}
		$xml->T619->addChild('sbmt_ref_id', $this->reference_id); //Submission Reference Identification, unique 8 char alphanumeric.
		//$xml->T619->addChild('rpt_tcd', 'O'); //Report Type, O = Original, A = Amended, C = Cancel. Entire batch must be the same I think.
		$xml->T619->addChild('rpt_tcd', $this->getStatus() ); //Report Type, O = Original, A = Amended, C = Cancel. Entire batch must be the same I think.
		if ( $this->transmitter_number == '' ) {
			$this->transmitter_number = 'MM555555'; //Default transmitter number to use if they don't supply one.
		}
		$xml->T619->addChild('trnmtr_nbr', $this->transmitter_number); //Transmitter number, provided by CRA if filing more then one return
		$xml->T619->addChild('trnmtr_tcd', 4); //Transmitter type indicator.  1 = Submitting your returns, 2 = Submitting others returns (service providers), 3 = Submitting returns using a purchased software package, 4 = Software Vendor.
		$xml->T619->addChild('summ_cnt', 0); //Total number of summary records.
		$xml->T619->addChild('lang_cd', 'E'); //Language

		//Transmitter name
		$xml->T619->addChild('TRNMTR_NM'); //Employee name
		$xml->T619->TRNMTR_NM->addChild('l1_nm', substr( $this->transmitter_name, 0, 30) ); //Transmitter name, max length 30.

		//Transmitter Address
		$xml->T619->addChild('TRNMTR_ADDR');
		$xml->T619->TRNMTR_ADDR->addChild('addr_l1_txt', $this->transmitter_address1 );
		if ( $this->transmitter_address2 != '' ) { $xml->T619->TRNMTR_ADDR->addChild('addr_l2_txt', $this->transmitter_address2 ); }
		$xml->T619->TRNMTR_ADDR->addChild('cty_nm', $this->transmitter_city );
		$xml->T619->TRNMTR_ADDR->addChild('prov_cd', $this->transmitter_province );
		$xml->T619->TRNMTR_ADDR->addChild('cntry_cd', 'CAN' );
		$xml->T619->TRNMTR_ADDR->addChild('pstl_cd', $this->transmitter_postal_code );

		//Contact
		$xml->T619->addChild('CNTC');
		$xml->T619->CNTC->addChild('cntc_nm', $this->contact_name );
		$phone_arr = $this->filterPhone( $this->contact_phone );
		if ( is_array($phone_arr) ) {
			$xml->T619->CNTC->addChild('cntc_area_cd', $phone_arr[0] );
			$xml->T619->CNTC->addChild('cntc_phn_nbr', $phone_arr[1].'-'.$phone_arr[2] );
			//$xml->T619->CNTC->addChild('cntc_extn_nbr', '' );
		}

		if ( $this->contact_email != '' ) { $xml->T619->CNTC->addChild('cntc_email_area', $this->contact_email ); }

		$xml->addChild('Return');

		return TRUE;
	}

	function _outputPDF() {
		return FALSE;
	}
}
?>