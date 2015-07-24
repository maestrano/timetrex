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
 * @package API\Report
 */
class APIReport extends APIFactory {
	public $report_obj = NULL;

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		$report_obj = TTNew( $this->main_class ); //Allow plugins to work with reports.
		$report_obj->setUserObject( $this->getCurrentUserObject() );
		$report_obj->setPermissionObject( $this->getPermissionObject() );

		$this->setMainClassObject( $report_obj );

		return TRUE;
	}

	function getReportObject() {
		return $this->getMainClassObject();
	}

	function getTemplate( $name = FALSE ) {
		return $this->returnHandler( $this->getReportObject()->getTemplate( $name ) );
	}

	function getConfig() {
		return $this->returnHandler( $this->getReportObject()->getConfig() );
	}
	function setConfig( $data = FALSE ) {
		return $this->returnHandler( $this->getReportObject()->setConfig( $data ) );
	}

	function getOtherConfig() {
		return $this->returnHandler( $this->getReportObject()->getOtherConfig() );
	}
	function getChartConfig() {
		return $this->returnHandler( $this->getReportObject()->getChartConfig() );
	}

	function setCompanyFormConfig( $data = FALSE ) {
		if ( $this->getReportObject()->checkPermissions() == TRUE ) {
			return $this->returnHandler( $this->getReportObject()->setCompanyFormConfig( $data ) );
		}

		return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('PERMISSION DENIED') );
	}
	function getCompanyFormConfig() {
		if ( $this->getReportObject()->checkPermissions() == TRUE ) {
			return $this->returnHandler( $this->getReportObject()->getCompanyFormConfig() );
		}

		return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('PERMISSION DENIED') );
	}

	function validateReport( $config = FALSE, $format = 'pdf' ) {
		$this->getReportObject()->setConfig( $config ); //Set config first, so checkPermissions can check/modify data in the config for Printing timesheets for regular employees.
		if ( $this->getReportObject()->checkPermissions() == TRUE ) {
			$validation_obj = $this->getReportObject()->validateConfig( $format );
			if ( $validation_obj->isValid() == FALSE ) {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), array( 0 => $validation_obj->getErrorsArray() ), array('total_records' => 1, 'valid_records' => 0 ) );
			}
		}

		return $this->returnHandler( TRUE );
	}

	//Use JSON API to download PDF files.
	function getReport( $config = FALSE, $format = 'pdf' ) {
		if ( Misc::isSystemLoadValid() == FALSE ) {
			return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('Please try again later...') );
		}

		$format = Misc::trimSortPrefix( $format );
		Debug::Text('Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);
		$this->getReportObject()->setConfig( $config ); //Set config first, so checkPermissions can check/modify data in the config for Printing timesheets for regular employees.
		if ( $this->getReportObject()->checkPermissions() == TRUE ) {
			$this->getReportObject()->setAMFMessageID( $this->getAMFMessageID() ); //This must be set *after* the all constructor functions are called, as its primarily called from JSON.

			$validation_obj = $this->getReportObject()->validateConfig( $format );
			if ( $validation_obj->isValid() == TRUE ) {
				//return Misc::APIFileDownload( 'report.pdf', 'application/pdf', $this->getReportObject()->getOutput( $format ) );
				$output_arr = $this->getReportObject()->getOutput( $format );
				if ( isset($output_arr['file_name']) AND isset($output_arr['mime_type']) AND isset($output_arr['data']) ) {
					//If using the SOAP API, return data base64 encoded so it can be decoded on the client side.
					if ( defined('TIMETREX_SOAP_API') AND TIMETREX_SOAP_API == TRUE ) {
						$output_arr['data'] = base64_encode( $output_arr['data'] );
						return $this->returnHandler( $output_arr );
					} else {
						Misc::APIFileDownload( $output_arr['file_name'], $output_arr['mime_type'], $output_arr['data'] );
						return NULL; //Don't send any additional data, so JSON encoding doesn't corrupt the download.
					}
				} elseif ( isset($output_arr['api_retval']) ) { //Pass through validation errors.
					Debug::Text('Report returned VALIDATION error, passing through...', __FILE__, __LINE__, __METHOD__, 10);
					return $this->returnHandler( $output_arr['api_retval'], $output_arr['api_details']['code'], $output_arr['api_details']['description'] );
					//return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('Please try again later...') );
				} elseif ( $output_arr !== FALSE ) {
					//Likely RAW data, return untouched.
					return $this->returnHandler( $output_arr );
				} else {
					//getOutput() returned FALSE, some error occurred. Likely load too high though.
					//return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('Error generating report...') );
					return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('ERROR: Please try again later or narrow your search criteria to decrease the size of your report').'...' );
				}
			} else {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), array( 0 => $validation_obj->getErrorsArray() ), array('total_records' => 1, 'valid_records' => 0 ) );
			}
		}

		return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('PERMISSION DENIED') );
	}

}
?>
