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
 * $Revision: 9688 $
 * $Id: EFT.class.php 9688 2013-04-25 22:01:30Z ipso $
 * $Date: 2013-04-25 15:01:30 -0700 (Thu, 25 Apr 2013) $
 */

/*

How this needs to work
----------------------

Abstraction layer. Store all data in a "TimeTrex" format, then use
different classes to export that data to each EFT foramt.

Add:

setFormat()

We probably need to support CPA 005, a CVS standard, and 105/80 byte standards too

Add internal checks that totals the debits/credits before the format is compiled, then matches
with the compiled format as well?

*/

/*
Example Usage:

$eft = new EFT();
$eft->setOriginatorID(1234567890);
$eft->setFileCreationNumber(1777);
$eft->setDataCenter(00400);
	$record = new EFT_Record();
	$record->setType('C');
	$record->setCPACode(001);
	$record->setAmount(100.11);
	$record->setDueDate( time() + (86400 * 7) );
	$record->setInstitution( 123 );
	$record->setTransit( 12345 );
	$record->setAccount( 123456789012 );
	$record->setName( 'Mike Benoit' );

	$record->setOriginatorShortName( 'TimeTrex' );
	$record->setOriginatorLongName( 'TimeTrex Payroll Services' );
	$record->setOriginatorReferenceNumber( 987789 );

	$record->setReturnInstitution( 321 );
	$record->setReturnTransit( 54321 );
	$record->setReturnAccount( 210987654321 );
$eft->setRecord( $record );

$eft->compile();
$eft->save('/tmp/eft01.txt');
*/

/**
 * @package Modules\Other
 */
class EFT {

	var $file_format_options = array( '1464','105', 'HSBC', 'BEANSTREAM' );
	var $file_format = NULL; //File format

	var $header_data = NULL;
	var $data = NULL;

	var $compiled_data = NULL;

	function __construct( $options = NULL ) {
		Debug::Text(' Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		$this->setFileCreationDate( time() );

		return TRUE;
	}

	function removeDecimal( $value ) {
		$retval = str_replace('.','', number_format( $value, 2, '.','') );

		return $retval;
	}

	function toJulian( $epoch ) {

		$year = str_pad( date('y', $epoch), 3, 0, STR_PAD_LEFT);

		//PHP's day of year is 0 based, so we need to add one for the banks.
		$day = str_pad( date('z', $epoch)+1, 3, 0, STR_PAD_LEFT);

		$retval = $year.$day;

		Debug::Text('Converting: '. TTDate::getDate('DATE+TIME', $epoch) .' To Julian: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function isAlphaNumeric( $value ) {
		/*
		if ( preg_match('/^[-0-9A-Z\ ]+$/',$value) ) {
			return TRUE;
		}

		return FALSE;
		*/

		return TRUE;
	}

	function isNumeric( $value ) {
		if ( preg_match('/^[-0-9]+$/',$value) ) {
			return TRUE;
		}

		return FALSE;
	}

	function isFloat( $value ) {
		if ( preg_match('/^[-0-9\.]+$/',$value) ) {
			return TRUE;
		}

		return FALSE;
	}

	function getFileFormat() {
		if ( isset($this->file_format) ) {
			return $this->file_format;
		}

		return FALSE;
	}
	function setFileFormat($format) {
		$this->file_format = $format;

		return TRUE;
	}

	function getBusinessNumber() {
		if ( isset($this->header_data['business_number']) ) {
			return $this->header_data['business_number'];
		}
	}
	function setBusinessNumber($value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->header_data['business_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOriginatorID() {
		if ( isset($this->header_data['originator_id']) ) {
			return $this->header_data['originator_id'];
		}
	}
	function setOriginatorID($value) {
		$value = trim($value);
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->header_data['originator_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOriginatorShortName() {
		if ( isset($this->header_data['originator_short_name']) ) {
			return $this->header_data['originator_short_name'];
		}
	}
	function setOriginatorShortName($value) {
		$value = trim($value);
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 26 ) {
			$this->header_data['originator_short_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getFileCreationNumber() {
		if ( isset($this->header_data['file_creation_number']) ) {
			return $this->header_data['file_creation_number'];
		}
	}
	function setFileCreationNumber($value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 4 ) {
			$this->header_data['file_creation_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getInitialEntryNumber() {
		if ( isset($this->header_data['initial_entry_number']) ) {
			return $this->header_data['initial_entry_number'];
		}
	}
	function setInitialEntryNumber($value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 15 ) {
			$this->header_data['initial_entry_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getFileCreationDate() {
		if ( isset($this->header_data['file_creation_date']) ) {
			return $this->header_data['file_creation_date'];
		}
	}
	function setFileCreationDate($value) {
		if ( $value != '' ) {
			$this->header_data['file_creation_date'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDataCenter() {
		if ( isset($this->header_data['data_center']) ) {
			return $this->header_data['data_center'];
		}
	}
	function setDataCenter($value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 10 ) {
			$this->header_data['data_center'] = $value;

			return TRUE;
		}

		return FALSE;
	}
	function getDataCenterName() {
		if ( isset($this->header_data['data_center_name']) ) {
			return $this->header_data['data_center_name'];
		}
	}
	function setDataCenterName($value) {
		$value = trim($value);
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 23 ) {
			$this->header_data['data_center_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function setRecord( $obj ){
		$this->data[] = $obj;

		return TRUE;
	}

	/*
	  Functions to help process the data.
	*/

	function padRecord( $value, $length, $type ) {
		$type = strtolower($type);

		//Trim record incase its too long.
		$value = substr( $value, 0, $length);

		switch ($type) {
			case 'n':
				$retval = str_pad( $value, $length, 0, STR_PAD_LEFT);
				break;
			case 'an':
				$retval = str_pad( $value, $length, ' ', STR_PAD_RIGHT);
				break;
		}

		return $retval;
	}

	function padLine( $line, $length ) {
		$retval = str_pad( $line, $length, ' ', STR_PAD_RIGHT);

		return $retval."\r\n";
	}



	function getCompiledData() {
		if ( $this->compiled_data !== NULL AND $this->compiled_data !== FALSE ) {
			return $this->compiled_data;
		}

		return FALSE;
	}

	function compile() {
		/*
			$file_format_class_name = 'EFT_File_Format_'.$this->getFileFormat().'()';
			//$file_format_obj = new $file_format_class_name;
			$file_format_obj = new EFT_File_Format_{$this->getFileFormat()}( $this->header_data, $this->data );
		*/

		switch ( strtoupper( $this->getFileFormat() ) )  {
			case 1464:
				$file_format_obj = new EFT_File_Format_1464($this->header_data, $this->data);
				break;
			case 105:
				$file_format_obj = new EFT_File_Format_105($this->header_data, $this->data);
				break;
			case 'HSBC':
				$file_format_obj = new EFT_File_Format_HSBC($this->data);
				break;
			case 'BEANSTREAM':
				$file_format_obj = new EFT_File_Format_BEANSTREAM($this->data);
				break;
			case 'ACH':
				$file_format_obj = new EFT_File_Format_ACH($this->header_data, $this->data);
				break;
			default:
				Debug::Text('Format does not exist: '. $this->getFileFormat(), __FILE__, __LINE__, __METHOD__, 10);
				break;
		}

		Debug::Text('aData Lines: '. count($this->data), __FILE__, __LINE__, __METHOD__, 10);

		if ( is_object( $file_format_obj ) ) {
			$compiled_data = $file_format_obj->_compile();
			if ( $compiled_data !== FALSE ) {
				$this->compiled_data = $compiled_data;

				return TRUE;
			}
		}

		return FALSE;
	}


	function save($file_name) {
		//saves processed data to a file.

		if ( $this->getCompiledData() !== FALSE ) {

			if ( is_writable( dirname($file_name) ) AND !file_exists($file_name) ) {
				if ( file_put_contents($file_name, $this->getCompiledData() ) > 0 ) {
					Debug::Text('Write successfull:', __FILE__, __LINE__, __METHOD__, 10);

					return TRUE;
				} else {
					Debug::Text('Write failed:', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::Text('File is not writable, or already exists:', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		Debug::Text('Save Failed!:', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}


/**
 * @package Modules\Other
 */
class EFT_record extends EFT {

	var $record_data = NULL;

	function __construct( $options = NULL ) {
		Debug::Text(' EFT_Record Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function getType() {
		if ( isset($this->record_data['type']) ) {
			return strtoupper($this->record_data['type']);
		}

		return FALSE;
	}

	function setType($value) {
		$value = strtolower($value);

		if ( $value == 'd' OR $value == 'c' ) {
			$this->record_data['type'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getCPACode() {
		if ( isset($this->record_data['cpa_code']) ) {
			return $this->record_data['cpa_code'];
		}

		return FALSE;
	}

	function setCPACode($value) {
		//200 - Payroll deposit
		//460 - Accounts Payable
		//470 - Fees/Dues
		//452 - Expense Payment
		//700 - Business PAD
		//430 - Bill Payment
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 3 ) {
			$this->record_data['cpa_code'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getAmount() {
		if ( isset($this->record_data['amount']) ) {
			return $this->record_data['amount'];
		}

		return FALSE;
	}

	function setAmount($value) {
		if ( $this->isFloat( $value ) AND strlen( $value ) <= 10 ) {
			$this->record_data['amount'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDueDate() {
		if ( isset($this->record_data['due_date']) ) {
			return $this->record_data['due_date'];
		}

		return FALSE;
	}

	function setDueDate($value) {
		if ( $value != '' ) {
			$this->record_data['due_date'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getInstitution() {
		if ( isset($this->record_data['institution']) ) {
			return $this->record_data['institution'];
		}

		return FALSE;
	}

	function setInstitution($value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 3 ) {
			$this->record_data['institution'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getTransit() {
		if ( isset($this->record_data['transit']) ) {
			return $this->record_data['transit'];
		}

		return FALSE;
	}

	function setTransit($value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 9 ) { //EFT Transit <= 5, ACH Transit/Routing <= 9:
			$this->record_data['transit'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getAccount() {
		if ( isset($this->record_data['account']) ) {
			return $this->record_data['account'];
		}

		return FALSE;
	}

	function setAccount($value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 17 ) { //Needs to be 17 digits for US, 13 for CAD?
			$this->record_data['account'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOriginatorShortName() {
		if ( isset($this->record_data['originator_short_name']) ) {
			return $this->record_data['originator_short_name'];
		}

		return FALSE;
	}

	function setOriginatorShortName($value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 15 ) {
			$this->record_data['originator_short_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getName() {
		if ( isset($this->record_data['name']) ) {
			return $this->record_data['name'];
		}

		return FALSE;
	}

	function setName($value) {
		//Payor or Payee name
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 30 ) {
			$this->record_data['name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOriginatorLongName() {
		if ( isset($this->record_data['originator_long_name']) ) {
			return $this->record_data['originator_long_name'];
		}

		return FALSE;
	}

	function setOriginatorLongName($value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 30 ) {
			$this->record_data['originator_long_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOriginatorReferenceNumber() {
		if ( isset($this->record_data['originator_reference_number']) ) {
			return $this->record_data['originator_reference_number'];
		}

		return FALSE;
	}

	function setOriginatorReferenceNumber($value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 19 ) {
			$this->record_data['originator_reference_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getReturnInstitution() {
		if ( isset($this->record_data['return_institution']) ) {
			return $this->record_data['return_institution'];
		}

		return FALSE;
	}

	function setReturnInstitution($value) {
		//Must be 0004 for TD?
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 3 ) {
			$this->record_data['return_institution'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getReturnTransit() {
		if ( isset($this->record_data['return_transit']) ) {
			return $this->record_data['return_transit'];
		}

		return FALSE;
	}

	function setReturnTransit($value) {
		if ( $this->isNumeric( $value ) AND strlen( $value ) <= 5 ) {
			$this->record_data['return_transit'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getReturnAccount() {
		if ( isset($this->record_data['return_account']) ) {
			return $this->record_data['return_account'];
		}

		return FALSE;
	}

	function setReturnAccount($value) {
		if ( $this->isAlphaNumeric( $value ) AND strlen( $value ) <= 12 ) {
			$this->record_data['return_account'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherData( $key ) {
		if ( isset($this->record_data[$key]) ) {
			return $this->record_data[$key];
		}

		return FALSE;
	}

	function setOtherData($key, $value) {
		$this->record_data[$key] = $value;

		return TRUE;
	}

}



/**
 * CPA005 Specification: http://www.google.ca/url?sa=t&source=web&cd=2&ved=0CBkQFjAB&url=https%3A%2F%2Fwww.rbcroyalbank.com%2Fach%2Ffile-451771.pdf&rct=j&q=CPA%20005%20file%20format&ei=KMK8TNqaE46osQORn-ScDw&usg=AFQjCNECApa4o_mADTwtF4WIrBDf6cZq9w
 * @package Modules\Other
 */
class EFT_File_Format_1464 Extends EFT {
	var $header_data = NULL;
	var $data = NULL;

	function __construct( $header_data, $data ) {
		Debug::Text(' EFT_Format_1464 Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		$this->header_data = $header_data;
		$this->data = $data;

		return TRUE;
	}

	private function compileHeader() {
		$line[] = 'A'; //A Record
		$line[] = '000000001'; //A Record number
		$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'N');
		$line[] = $this->padRecord( $this->getFileCreationNumber(), 4, 'N');
		$line[] = $this->padRecord( $this->toJulian( $this->getFileCreationDate() ), 6, 'N');
		$line[] = $this->padRecord( $this->getDataCenter(), 5, 'N');

		$retval = $this->padLine( implode('', $line), 1464 );

		Debug::Text('A Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data for D Record:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$i=2;
		foreach ( $this->data as $key => $record ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$line[] = $record->getType();
			$line[] = $this->padRecord($i, 9, 'N');

			$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN');
			$line[] = $this->padRecord( $this->getFileCreationNumber(), 4, 'N');

			$line[] = $this->padRecord($record->getCPACode(), 3, 'N');
			$line[] = $this->padRecord( $this->removeDecimal( $record->getAmount() ), 10, 'N');

			$line[] = $this->padRecord( $this->toJulian( $record->getDueDate() ), 6, 'N');

			$line[] = '0'.$this->padRecord( $record->getInstitution(), 3, 'N').$this->padRecord( $record->getTransit(), 5, 'N');
			$line[] = $this->padRecord( $record->getAccount(), 12, 'AN');

			$line[] = str_repeat('0', 22); //Reserved
			$line[] = str_repeat('0', 3); //Reserved

			$sanity_check_1 = strlen( implode('', $line) );
			Debug::Text('Digits to Originator Short Name: '. $sanity_check_1 .' - Should be: 89', __FILE__, __LINE__, __METHOD__, 10);
			if ( $sanity_check_1 !== 89 ) {
				Debug::Text('Failed Sanity Check 1', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
			unset($sanity_check_1);

			$line[] = $this->padRecord($record->getOriginatorShortName(), 15, 'AN');
			$line[] = $this->padRecord($record->getName(), 30, 'AN');
			$line[] = $this->padRecord($record->getOriginatorLongName(), 30, 'AN');

			$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN');

			$line[] = $this->padRecord( $record->getOriginatorReferenceNumber(), 19, 'AN');

			$line[] = '0'.$this->padRecord( $record->getReturnInstitution(), 3, 'N').$this->padRecord( $record->getReturnTransit(), 5, 'N');
			$line[] = $this->padRecord( $record->getReturnAccount(), 12, 'AN');

			$sanity_check_2 = strlen( implode('', $line) );
			Debug::Text('Digits to END of return account: '. $sanity_check_2 .' - Should be: 214', __FILE__, __LINE__, __METHOD__, 10);
			if ( $sanity_check_2 !== 214 ) {
				Debug::Text('Failed Sanity Check 2', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
			unset($sanity_check_2);

			$line[] = $this->padRecord( NULL, 15, 'AN'); //Originators Sundry Info. -- Blank
			$line[] = $this->padRecord( NULL, 22, 'AN'); //Stored Trace Number -- Blank
			$line[] = $this->padRecord( NULL, 2, 'AN'); //Settlement Code -- Blank
			$line[] = $this->padRecord( NULL, 11, 'N'); //Invalid Data Element, must be 0's for HSBC to accept it -- Blank

			$sanity_check_3 = strlen( implode('', $line) );
			Debug::Text('Digits to END of invalid data element: '. $sanity_check_3 .' - Should be: 264', __FILE__, __LINE__, __METHOD__, 10);
			if ( $sanity_check_3 !== 264 ) {
				Debug::Text('Failed Sanity Check 3', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
			unset($sanity_check_3);

			$d_record = $this->padLine( implode('', $line), 1464 );
			//strlen($d_record) might show 1466 (2digits more), due to "/n" being at the end.
			Debug::Text('D Record:'. $d_record .' - Length: '. strlen($d_record), __FILE__, __LINE__, __METHOD__, 10);

			$retval[] = $d_record;

			unset($line);
			unset($d_record);

			$i++;
		}

		if ( isset($retval) ) {
			return $retval;
		}

		return FALSE;
	}

	private function compileFooter() {
		if ( count($this->data) == 0 ) {
			return FALSE;
		}

		$line[] = 'Z'; //Z Record

		//$line[] = '000000001'; //Z Record number
		$line[] = $this->padRecord( count($this->data)+2, 9, 'N'); //add 2, 1 for the A record, and 1 for the Z record.

		$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN');
		$line[] = $this->padRecord( $this->getFileCreationNumber(), 4, 'N');

		//Loop and get total value and number of records.
		$d_record_total = 0;
		$d_record_count = 0;
		$c_record_total = 0;
		$c_record_count = 0;
		foreach ( $this->data as $key => $record ) {
			if ( $record->getType() == 'D' ) {
				$d_record_total += $record->getAmount();
				$d_record_count++;
			} elseif ( $record->getType() == 'C' ) {
				$c_record_total += $record->getAmount();
				$c_record_count++;
			}
		}

		$line[] = $this->padRecord( $this->removeDecimal( $d_record_total ), 14, 'N');
		$line[] = $this->padRecord( $d_record_count, 8, 'N');

		$line[] = $this->padRecord( $this->removeDecimal( $c_record_total ), 14, 'N');
		$line[] = $this->padRecord( $c_record_count, 8, 'N');

		$line[] = $this->padRecord( NULL, 14, 'N'); //Invalid Data Element, must be 0's for HSBC to accept it -- Blank
		$line[] = $this->padRecord( NULL, 8, 'N'); //Invalid Data Element, must be 0's for HSBC to accept it -- Blank
		$line[] = $this->padRecord( NULL, 14, 'N'); //Invalid Data Element, must be 0's for HSBC to accept it -- Blank
		$line[] = $this->padRecord( NULL, 8, 'N'); //Invalid Data Element, must be 0's for HSBC to accept it -- Blank

		$retval = $this->padLine( implode('', $line), 1464 );

		Debug::Text('Z Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;

	}

	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
        //record numbers.

		$compiled_data = $this->compileHeader();
		$compiled_data .= @implode('', $this->compileRecords() );
		$compiled_data .= $this->compileFooter();

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= (1464 * 1) ) {
			//$this->compiled_data = $compiled_data;

			//return TRUE;

			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}



/**
 * @package Modules\Other
 */
class EFT_File_Format_105 Extends EFT {
	var $header_data = NULL;
	var $data = NULL;

	function __construct( $header_data, $data ) {
		Debug::Text(' EFT_Format_105 Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		$this->header_data = $header_data;
		$this->data = $data;

		return TRUE;
	}

	private function compileHeader() {
		$line[] = 'A'; //A Record
		$line[] = '000000001'; //A Record number

		//This should be the scotia bank "Customer Number"
		$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN');
		$line[] = $this->padRecord( $this->getFileCreationNumber(), 4, 'N');
		$line[] = $this->padRecord( $this->toJulian( $this->getFileCreationDate() ), 6, 'N');
		$line[] = $this->padRecord( $this->getDataCenter(), 5, 'N');
		$line[] = 'D';

		$retval = $this->padLine( implode('', $line), 105 );

		Debug::Text('A Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	private function compileCustomerHeader() {
		$record = $this->data[0]; //Just use info from first record;

		if ( is_object($record) ) {
			$line[] = 'Y';
			$line[] = $this->padRecord( $record->getOriginatorShortName(), 15, 'AN');
			$line[] = $this->padRecord( $record->getOriginatorLongName(), 30, 'AN');
			$line[] = $this->padRecord( $record->getReturnInstitution(), 3, 'N');

			$line[] = $this->padRecord( $record->getReturnTransit(), 5, 'N');
			$line[] = $this->padRecord( $record->getReturnAccount(), 12, 'AN');

			$retval = $this->padLine( implode('', $line), 105 );

			Debug::Text('Y Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}

		return FALSE;
	}

	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data for D Record:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$i=2;
		foreach ( $this->data as $key => $record ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$line[] = $record->getType();
			$line[] = $this->padRecord($record->getCPACode(), 3, 'N');
			$line[] = $this->padRecord( $this->removeDecimal( $record->getAmount() ), 10, 'N');
			$line[] = $this->padRecord( $this->toJulian( $record->getDueDate() ), 6, 'N');

			$sanity_check_1 = strlen( implode('', $line) );
			Debug::Text('Digits to Originator Short Name: '. $sanity_check_1 .' - Should be: 20', __FILE__, __LINE__, __METHOD__, 10);
			if ( $sanity_check_1 !== 20 ) {
				Debug::Text('Failed Sanity Check 1', __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
			unset($sanity_check_1);

			if ( $record->getType() == 'D' ) {
				$line[] = ' ';
			}

			Debug::Text('Institution: '. $record->getInstitution() .' Transit: '.$record->getTransit().' Bank Account Number: '. $record->getAccount(), __FILE__, __LINE__, __METHOD__, 10);

			$line[] = $this->padRecord( $record->getInstitution(), 3, 'N');
			$line[] = $this->padRecord( $record->getTransit(), 5, 'N');
			$line[] = $this->padRecord( $record->getAccount(), 12, 'AN');
			$line[] = $this->padRecord( $record->getName(), 30, 'AN');
			$line[] = $this->padRecord( $record->getOriginatorReferenceNumber(), 19, 'AN');

			$d_record = $this->padLine( implode('', $line), 105 );
			Debug::Text('D Record:'. $d_record .' - Length: '. strlen($d_record), __FILE__, __LINE__, __METHOD__, 10);

			$retval[] = $d_record;

			unset($line);
			unset($d_record);

			$i++;
		}

		if ( isset($retval) ) {
			//var_dump($retval);
			return $retval;
		}

		return FALSE;
	}

	private function compileFooter() {
		if ( count($this->data) == 0 ) {
			return FALSE;
		}

		$line[] = 'Z'; //Z Record
		$line[] = $this->padRecord( NULL, 9, 'AN');

		$line[] = $this->padRecord( $this->getOriginatorID(), 10, 'AN');
		$line[] = $this->padRecord( $this->getFileCreationNumber(), 4, 'N');

		//Loop and get total value and number of records.
		$d_record_total = 0;
		$d_record_count = 0;
		$c_record_total = 0;
		$c_record_count = 0;
		foreach ( $this->data as $key => $record ) {
			if ( $record->getType() == 'D' ) {
				$d_record_total += $record->getAmount();
				$d_record_count++;
			} elseif ( $record->getType() == 'C' ) {
				$c_record_total += $record->getAmount();
				$c_record_count++;
			}
		}

		$line[] = $this->padRecord( $this->removeDecimal( $d_record_total ), 14, 'N');
		$line[] = $this->padRecord( $d_record_count, 8, 'N');

		$line[] = $this->padRecord( $this->removeDecimal( $c_record_total ), 14, 'N');
		$line[] = $this->padRecord( $c_record_count, 8, 'N');

		$retval = $this->padLine( implode('', $line), 105 );

		Debug::Text('Z Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;

	}

	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
        //record numbers.

		$compiled_data = $this->compileHeader();
		$compiled_data .= $this->compileCustomerHeader();
		$compiled_data .= @implode('', $this->compileRecords() );
		$compiled_data .= $this->compileFooter();

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= (105 * 3) ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}


/**
 * @package Modules\Other
 */
class EFT_File_Format_HSBC Extends EFT {
	var $data = NULL;

	function __construct( $data ) {
		Debug::Text(' EFT_Format_HSBC Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		$this->data = $data;

		return TRUE;
	}

	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data for D Record:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$i=2;
		foreach ( $this->data as $key => $record ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);
			if ( $record->getType() == 'D' ) {
				$record_type = 'R'; //Receivable
			} else {
				$record_type = 'P'; //Payable
			}
			$line[] = $record_type;

			$line[] = $record->getOriginatorReferenceNumber();

			$line[] = '0'.$this->padRecord( $record->getInstitution(), 3, 'N').$this->padRecord( $record->getTransit(), 5, 'N');

			$line[] = $record->getAccount();

			$line[] = $this->padRecord( $this->removeDecimal( $record->getAmount() ), 10, 'N');

			$line[] = date('m/d/Y', $record->getDueDate() );

			$line[] = $record->getName();

			$line[] = 'B'; //BiWeekly

			$line[] = $record->getReturnAccount();

			$line[] = $this->padRecord($record->getCPACode(), 3, 'N');

			$d_record = '"'.implode('","', $line).'"';
			Debug::Text('D Record:'. $d_record .' - Length: '. strlen($d_record), __FILE__, __LINE__, __METHOD__, 10);

			$retval[] = $d_record;

			unset($line);
			unset($d_record);

			$i++;
		}

		if ( isset($retval) ) {
			return $retval;
		}

		return FALSE;
	}

	function _compile() {
		//Processes all the data, padding it.
		$compiled_data = @implode("\r\n", $this->compileRecords() );

		if ( strlen( $compiled_data ) >= 50 ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}


/**
 * @package Modules\Other
 */
class EFT_File_Format_ACH Extends EFT {
	/*
	Validator Program: http://www.download.com/3001-2066_4-10344585.html
	http://www.google.ca/url?sa=t&rct=j&q=ach%20file%20format%20immediate%20origin&source=web&cd=4&ved=0CEMQFjAD&url=http%3A%2F%2Fwww.commercebank.com%2FPDFs%2Fconnections%2FACH-fileformats.pdf&ei=mb0VT5tdo6OIAo3lqNwN&usg=AFQjCNEI0pnOfN65qRpVtCLYWTIM4inKtQ&cad=rja
	Google: nacha 94 byte file format OR International ACH (IAT) NACHA File Formats

		File Header Record
			Batch Header Record
				First entry detail record
				Second entry detail record
				...
				Last entry detail record
			Batch Control Record
			Batch Header Record
				First entry detail record
				Second entry detail record
				...
				Last entry detail record
			Batch Control Record
		File Control Record

	Additional Data to pass:
	- Immediate Destination
	- Immediate Origin
	*/

	var $header_data = NULL;
	var $data = NULL;

	protected $batch_number = 1;

	function __construct( $header_data, $data ) {
		Debug::Text(' EFT_Format_ACH Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		$this->header_data = $header_data;
		$this->data = $data;

		return TRUE;
	}

	private function compileFileHeader() {
		$line[] = '1'; //1 Record
		$line[] = '01'; //Priority code
		$line[] = ' '.$this->padRecord( $this->getDataCenter(), 9, 'N'); //Immidiate destination - Must be proceeded with a space then 9 digits. '072000805' - Standard Federal Bank
		$line[] = ' '.$this->padRecord( $this->getOriginatorID(), 9, 'N'); //Immediate Origin - Must be proceeded with a space then 9 digits. Recommend IRS Federal Tax ID Number

		$line[] = $this->padRecord( date('ymd', $this->getFileCreationDate() ), 6, 'N');
		$line[] = $this->padRecord( date('Hi', $this->getFileCreationDate() ), 4, 'N');

		$line[] = $this->padRecord( 0 , 1, 'N'); //0-9, input file ID modifier

		$line[] = $this->padRecord( 94 , 3, 'N'); //94 byte records
		$line[] = $this->padRecord( 10 , 2, 'N'); //Blocking factor
		$line[] = $this->padRecord( 1 , 1, 'N'); //Format code

		$line[] = $this->padRecord( strtoupper( $this->getDataCenterName() ), 23, 'AN'); //Immidiate destination name. Optional
		$line[] = $this->padRecord( strtoupper( $this->getOriginatorShortName() ), 23, 'AN'); //Company Short Name. Optional

		$line[] = $this->padRecord( '', 8, 'AN'); //File Reference Code

		$retval = $this->padLine( implode('', $line), 94 );

		Debug::Text('File Header Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	private function compileBatchHeader( $type, $due_date ) {
		$line[] = '5'; //5 Record

		if ( $type == 'C' ) {
			$line[] = '220'; //Batch Type
		} else {
			$line[] = '225'; //Batch Type
		}
		$line[] = $this->padRecord( strtoupper( $this->getOriginatorShortName() ), 16, 'AN'); //Company Short Name
		$line[] = $this->padRecord( '', 20, 'AN'); //Discretionary Data
		$line[] = $this->padRecord( $this->getBusinessNumber(), 10, 'N'); //Company Identification - Recommend IRS Federal Tax ID Number
		$line[] = $this->padRecord( 'PPD', 3, 'AN'); //Standard Entry Class. (PPD, CCD,CTX,TEL,WEB)
		$line[] = $this->padRecord( 'PAYROLL', 10, 'AN'); //Entry Description
		$line[] = $this->padRecord( date('ymd', $this->getFileCreationDate() ), 6, 'N'); //Date
		$line[] = $this->padRecord( date('ymd', $due_date ), 6, 'N'); //Date to post funds.
		$line[] = $this->padRecord( '', 3, 'AN'); //Blank
		$line[] = '1'; //Originator Status Code
		$line[] = $this->padRecord( $this->getDataCenter(), 8, 'N' ); //Originating Bank Transit
		$line[] = $this->padRecord( $this->batch_number, 7, 'N'); //Batch Number

		$retval = $this->padLine( implode('', $line), 94 );

		Debug::Text('Batch Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	private function compileBatchControl( $type, $record_count, $batch_amount, $hash ) {
		$line[] = '8'; //8 Record

		if ( $type == 'C' ) {
			$line[] = '220'; //Batch Type
		} else {
			$line[] = '225'; //Batch Type
		}

		$line[] = $this->padRecord( $record_count, 6, 'N'); //Entry and Addenda count.
		$line[] = $this->padRecord( substr( str_pad( $hash, 10, 0, STR_PAD_LEFT), -10), 10, 'N'); //Entry hash. If it exceeds 10 digits, use just the last 10.
		$line[] = $this->padRecord( 0, 12, 'N'); //Debit Total
		$line[] = $this->padRecord( $this->removeDecimal( $batch_amount ), 12, 'N'); //Credit Total
		$line[] = $this->padRecord( $this->getBusinessNumber(), 10, 'N'); //Company Identification - Recommend IRS Federal Tax ID Number
		$line[] = $this->padRecord( '', 19, 'AN'); //Blank
		$line[] = $this->padRecord( '', 6, 'AN'); //Blank
		$line[] = $this->padRecord( $this->getDataCenter(), 8, 'N' ); //Originating Bank Transit
		$line[] = $this->padRecord( $this->batch_number, 7, 'N'); //Batch Number

		$retval = $this->padLine( implode('', $line), 94 );

		Debug::Text('Batch Control Record:'. $retval .' Count: '. $record_count .' Amount: '. $batch_amount, __FILE__, __LINE__, __METHOD__, 10);

		$this->batch_number++;

		return $retval;
	}

	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data for D Record:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$i=1;
		$max=count($this->data);
		$prev_due_date = FALSE;
		$batch_amount = 0;
		$batch_record_count = 1;
		$batch_hash = 0;
		foreach ( $this->data as $key => $record ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);
			if ( $batch_amount == 0 ) {
				$retval[] = $this->compileBatchHeader( $record->getType(), $record->getDueDate() );
			}

			Debug::Text('Institution: '. $record->getInstitution() .' Transit: '.$record->getTransit().' Bank Account Number: '. $record->getAccount(), __FILE__, __LINE__, __METHOD__, 10);

			$line[] = '6'; //6 Record (PPD)

			//Transaction code used to default to 22 (checkings account) always.
			$transaction_type = substr( $record->getInstitution(), 0, 2);
			if ( (int)$transaction_type == 0 ) { //Institution defaults to '000' if its not set, so assume its a checkings account in that case.
				$transaction_type = 22;
			}
			$line[] = $this->padRecord( $transaction_type, 2, 'N'); //Transaction code - 22=Deposit destined for checking account, 32=Deposit destined for savings account

			$line[] = $this->padRecord( substr($record->getTransit(),0,8), 8, 'N'); //Transit
			$line[] = $this->padRecord( substr($record->getTransit(),-1,1), 1, 'AN'); //Check Digit
			$line[] = $this->padRecord( $record->getAccount(), 17, 'AN'); //Account number
			$line[] = $this->padRecord( $this->removeDecimal( $record->getAmount() ), 10, 'N'); //Amount
			$line[] = $this->padRecord( $record->getOriginatorReferenceNumber(), 15, 'AN'); //transaction identification number
			$line[] = $this->padRecord( $record->getName(), 22, 'AN'); //Name of receiver
			$line[] = $this->padRecord( '', 2, 'AN'); //discretionary data
			$line[] = $this->padRecord( 0, 1, 'N'); //Addenda record indicator
			$line[] = $this->padRecord( $this->getInitialEntryNumber() . str_pad( $i, ( 15-strlen($this->getInitialEntryNumber()) ), 0, STR_PAD_LEFT), 15, 'N'); //Trace number. Bank assigns?

			$d_record = $this->padLine( implode('', $line), 94 );

			$retval[] = $d_record;

			$batch_amount += $record->getAmount();
			$prev_due_date = $record->getDueDate();
			$batch_hash += substr($record->getTransit(),0,8);
			Debug::Text('PPD Record:'. $d_record .' - DueDate: '. $record->getDueDate() .' Batch Amount: '. $batch_amount .' Length: '. strlen($d_record) .' Hash1: '. substr($record->getTransit(),0,8) .' Hash2: '. $batch_hash, __FILE__, __LINE__, __METHOD__, 10);

			//Add BatchControl Record Here
			if ( $i == $max ) {
				$retval[] = $this->compileBatchControl( $record->getType(), $batch_record_count, $batch_amount, $batch_hash );
			} elseif ( isset($this->data[$key+1]) AND $prev_due_date == FALSE OR $prev_due_date != $this->data[$key+1]->getDueDate() ) {
				//Because each batch only has a due date, only start a new batch if the DueDate changes.
				//Add batch record here
				//Close the previous batch before starting a new one.
				$retval[] = $this->compileBatchControl( $record->getType(), $batch_record_count, $batch_amount, $batch_hash );

				$batch_amount = 0;
				$batch_record_count = 1;
				$batch_hash = 0;
			} else {
				$batch_record_count++;
			}

			unset($line);
			unset($d_record);
			$i++;
		}

		if ( isset($retval) ) {
			//var_dump($retval);
			return $retval;
		}

		return FALSE;
	}

	private function compileFileControl() {
		if ( count($this->data) == 0 ) {
			return FALSE;
		}

		//Loop and get total value and number of records.
		$d_record_total = 0;
		$d_record_count = 0;
		$c_record_total = 0;
		$c_record_count = 0;
		$hash_total = 0;
		foreach ( $this->data as $key => $record ) {
			if ( $record->getType() == 'D' ) {
				$d_record_total += $record->getAmount();
				$hash_total += substr($record->getTransit(),0,8);
				$d_record_count++;
			} elseif ( $record->getType() == 'C' ) {
				$c_record_total += $record->getAmount();
				$hash_total += substr($record->getTransit(),0,8);
				$c_record_count++;
			}
		}
		$hash_total = substr( str_pad( $hash_total, 10, 0, STR_PAD_LEFT), -10); //Last 10 chars.
		Debug::Text('File Hash:'. $hash_total, __FILE__, __LINE__, __METHOD__, 10);

		$line[] = '9'; //9 Record
		$line[] = $this->padRecord( $this->batch_number-1, 6, 'N'); //Total number of batches

		/*
		Total count of output lines, including the first and last lines, divided by 10,
         rounded up to the nearest integer e.g. 99.9 becomes 100); 6 columns, zero-padded on
         the left.
		*/
		$line[] = $this->padRecord( round( ((($c_record_count+$d_record_count)*2)+2)/10 ), 6, 'N'); //Block count?!?!

		$line[] = $this->padRecord( $c_record_count+$d_record_count, 8, 'N'); //Total entry count

		$line[] = $this->padRecord( $hash_total, 10, 'N'); //Entry hash
		$line[] = $this->padRecord( $this->removeDecimal( $d_record_total ), 12, 'N'); //Total Debit Amount
		$line[] = $this->padRecord( $this->removeDecimal( $c_record_total ), 12, 'N'); //Total Credit Amount

		$line[] = $this->padRecord( '', 39, 'AN'); //Blank

		$retval = $this->padLine( implode('', $line), 94 );

		Debug::Text('File Control Record:'. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;

	}

	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
        //record numbers.

		$compiled_data = $this->compileFileHeader();
		$compiled_data .= @implode('', $this->compileRecords() );
		$compiled_data .= $this->compileFileControl();

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= (94 * 3) ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}


/**
 * @package Modules\Other
 */
class EFT_File_Format_BEANSTREAM Extends EFT {
	var $data = NULL;

	function __construct( $data ) {
		Debug::Text(' EFT_Format_BEANSTREAM Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		$this->data = $data;

		return TRUE;
	}

	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data for D Record:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		foreach ( $this->data as $key => $record ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			//Transaction method, EFT = E, ACH = A
			if ( $record->getInstitution() == '' ) {
				//ACH
				$line[] = 'A';
			} else {
				//EFT
				$line[] = 'E';
			}

			//Transaction type
			$line[] = $record->getType(); //C = Credit, D= Debit

			if ( $record->getInstitution() != '' ) {
				$line[] = $record->getInstitution();
			}
			$line[] = $record->getTransit();
			$line[] = $record->getAccount();

			if ( $record->getInstitution() == '' ) {
				$line[] = 'CC'; //Corporate Checking Account, for ACH only.
			}

			$line[] = $this->removeDecimal( $record->getAmount() );

			$line[] = $record->getOriginatorReferenceNumber();

			$line[] = $record->getName();

			$d_record = implode(',', $line);
			Debug::Text('D Record:'. $d_record .' - Length: '. strlen($d_record), __FILE__, __LINE__, __METHOD__, 10);

			$retval[] = $d_record;

			unset($line);
			unset($d_record);
		}

		if ( isset($retval) ) {
			return $retval;
		}

		return FALSE;
	}

	function _compile() {
		//Processes all the data, padding it.
		$compiled_data = @implode("\r\n", $this->compileRecords() );

		if ( strlen( $compiled_data ) >= 25 ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}

?>
