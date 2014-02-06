<?php

/*

How this needs to work
----------------------

Abstraction layer. Store all data in a "TimeTrex" format, then use
different classes to export that data to each EFT foramt.

Add:


/*
Example Usage:

$gle = new GeneralLedgerExport();
$gle->setFileFormat('Simply');
	$je = new GeneralLedgerExport_JournalEntry();

	$je->setDate( time() );
	$je->setSource( 0000101 );
	$je->setComment( "Benoit, Wendy" );

	$record = new GeneralLedgerExport_Record();
	$record->setAccount( 2500 );
	$record->setType('CREDIT'); //Or Debit?
	$record->setAmount( 10.00 );

	$je->setRecord($record);

$gle->setJournalEntry($je);

$gle->compile();

$eft->save('/tmp/gl01.txt');
*/

/**
 * @package Core\GeneralLedgerExport
 */
class GeneralLedgerExport {

	var $file_format_options = array( 'CSV' );
	var $file_format = NULL; //File format
	var $set_journal_entry_errors = 0;

	function __construct( $options = NULL ) {
		Debug::Text(' Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
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

	function setJournalEntry( $obj ) {
		//Make sure accounts balance.

		if ( $obj->checkBalance() == TRUE AND $obj->combineRecords() == TRUE) {
			$this->data[] = $obj;

			return TRUE;
		} else {
			//Count errors, so we can NOT compile data if something doesn't balance
			Debug::Text(' Journal Entry did not balance: Errors: '. $this->set_journal_entry_errors, __FILE__, __LINE__, __METHOD__, 10);
			$this->set_journal_entry_errors++;
		}

		return FALSE;
	}

	/*
	  Functions to help process the data.
	*/

	function getCompiledData() {
		if ( isset($this->compiled_data) AND $this->compiled_data !== NULL AND $this->compiled_data !== FALSE ) {
			return $this->compiled_data;
		}

		return FALSE;
	}

	function compile() {
		if ( !isset( $this->data) OR $this->set_journal_entry_errors > 0 ) {
			Debug::Text(' No Data, or Journal Entry did not balance: Errors: '. $this->set_journal_entry_errors, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		switch ( strtolower( $this->getFileFormat() ) )  {
			case 'simply':
				$file_format_obj = new GeneralLedgerExport_File_Format_Simply( $this->data );
				break;
			case 'quickbooks':
				$file_format_obj = new GeneralLedgerExport_File_Format_QuickBooks( $this->data );
				break;
			case 'csv':
				$file_format_obj = new GeneralLedgerExport_File_Format_CSV( $this->data );
				break;

		}

		Debug::Text('aData Lines: '. count($this->data), __FILE__, __LINE__, __METHOD__, 10);

		$compiled_data = $file_format_obj->_compile();
		if ( $compiled_data !== FALSE ) {
			$this->compiled_data = $compiled_data;

			return TRUE;
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
 * @package Core\GeneralLedgerExport
 */
class GeneralLedgerExport_JournalEntry extends GeneralLedgerExport {
	var $journal_entry_data = NULL;

	function __construct( $options = NULL ) {
		Debug::Text(' GLE_JournalEntry Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function getDate() {
		if ( isset($this->journal_entry_data['date']) ) {
			return $this->journal_entry_data['date'];
		}

		return FALSE;
	}

	function setDate($value) {
		if ( $value != '' ) {
			$this->journal_entry_data['date'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getSource() {
		if ( isset($this->journal_entry_data['source']) ) {
			return $this->journal_entry_data['source'];
		}

		return FALSE;
	}

	function setSource($value) {
		$value = trim($value);

		$this->journal_entry_data['source'] = substr( $value, 0, 13);

		return TRUE;
	}

	function getComment() {
		if ( isset($this->journal_entry_data['comment']) ) {
			return $this->journal_entry_data['comment'];
		}

		return FALSE;
	}

	function setComment($value) {
		$value = trim($value);

		if ( strlen( $value ) <= 39 ) {
			$this->journal_entry_data['comment'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getRecords() {
		if ( isset($this->journal_entry_data['records']) AND $this->journal_entry_data['records'] != NULL ) {
			return $this->journal_entry_data['records'];
		}

		return FALSE;
	}

	function setRecord( $obj ) {
		if ( $obj->Validate() == TRUE ) {
			$this->journal_entry_data['records'][] = $obj;
			return TRUE;
		}

		return FALSE;
	}

	function combineRecords() {
		//See if there are multiple records with the same type AND account
		//If so, combine them in to one.
		$records = $this->getRecords();

		$i=0;
		foreach( $records as $record ) {
			if ( isset($account_list[$record->getType()][$record->getAccount()]) ) {
				$original_id = $account_list[$record->getType()][$record->getAccount()];
				Debug::Text(' Found duplicate Account, combining: '. $i .' with '. $original_id .' Type: '. $record->getType() .' Account: '. $record->getAccount(), __FILE__, __LINE__, __METHOD__,10);

				//Combine two accounts in to one.
				$new_record = new GeneralLedgerExport_Record();
				$new_record->setAccount( $record->getAccount() );
				$new_record->setType( $record->getType() );
				$new_record->setAmount( $new_records[$original_id]->getAmount() + $record->getAmount() );

				$new_records[$original_id] = $new_record;

				unset($new_record);
			} else {
				$account_list[$record->getType()][$record->getAccount()] = $i;

				$new_records[$i] = $record;
			}


			$i++;
		}

		$this->journal_entry_data['records'] = $new_records;

		return $this->checkBalance();
	}

	function checkBalance() {
		Debug::Text(' Checking Balance of Journal Entry...', __FILE__, __LINE__, __METHOD__,10);
		$records = $this->getRecords();
		if ( $records == FALSE ) {
			return FALSE;
		}

		$debit_amount = 0;
		$credit_amount = 0;

		$i=0;
		foreach($records as $record) {
			Debug::Text($i.'. Type: '. $record->getType() .' Amount: '. $record->getAmount() .' Account: '. $record->getAccount() , __FILE__, __LINE__, __METHOD__,10);
			if ( $record->getType() == 'debit' ) {
				$debit_amount += $record->getAmount();
			} elseif($record->getType() == 'credit') {
				$credit_amount += $record->getAmount();
			} else {
				Debug::Text('NO ACCOUNT TYPE BAD!!', __FILE__, __LINE__, __METHOD__,10);
			}

			$i++;
		}


		Debug::Text(' Debit Amount: '. $debit_amount .' Credit Amount: '. $credit_amount, __FILE__, __LINE__, __METHOD__,10);
		if ( $debit_amount != 0 AND $credit_amount != 0
				AND round($debit_amount,2) == round($credit_amount,2) ) {
			Debug::Text(' JE balances!', __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		}

		Debug::Text(' Journal Entry DOES NOT BALANCE!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}


}


/**
 * @package Core\GeneralLedgerExport
 */
class GeneralLedgerExport_Record extends GeneralLedgerExport_JournalEntry {

	var $record_data = NULL;
/*
	function __construct( $options = NULL ) {
		Debug::Text(' GLE_Record Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}
*/
	function getType() {
		if ( isset($this->record_data['type']) ) {
			return $this->record_data['type'];
		}

		return FALSE;
	}

	function setType($value) {
		$value = strtolower($value);

		if ( $value == 'credit' OR $value == 'debit' ) {
			$this->record_data['type'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getAmount() {
		if ( isset($this->record_data['amount']) ) {
			return number_format($this->record_data['amount'], 2, '.', '');
		}

		return FALSE;
	}

	function setAmount($value) {
		//Allow negative values, for example if someone is trying to export negative values (for things like vacation accrual)
		if ( $this->isFloat( $value ) AND strlen( $value ) <= 10 AND $value != 0 ) {
			$this->record_data['amount'] = $value;

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
		$value = trim($value);

		if ( strlen( $value ) <= 100 ) { //Allow long account values for more job tracking.
			$this->record_data['account'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->getType() == FALSE OR $this->getAccount() == FALSE OR $this->getAmount() == FALSE ) {
			return FALSE;
		}

		return TRUE;
	}

}


/**
 * @package Core\GeneralLedgerExport
 */
class GeneralLedgerExport_File_Format_Simply Extends GeneralLedgerExport {
	var $data = NULL;

	function __construct( $data ) {
		Debug::Text(' General Ledger Format Simply Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		$this->data = $data;

		return TRUE;
	}

	private function toDate($epoch) {
		return date('m-d-y', $epoch);
	}


	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data records:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		foreach ( $this->data as $key => $journal_entry ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$line1[] = $this->toDate( $journal_entry->getDate() );
			$line1[] = '"'.$journal_entry->getSource().'"';
			$line1[] = '"'.$journal_entry->getComment().'"';

			$line1 = implode(',', $line1);
			Debug::Text('Line 1: '. $line1, __FILE__, __LINE__, __METHOD__, 10);
			$retval[] = $line1;

			$records = $journal_entry->getRecords();
			foreach ($records as $record) {
				$line[] = $record->getAccount();
				if ( $record->getType() == 'credit' ) {
					//Credits are negative.
					$amount = number_format( ($record->getAmount()*-1), 2, '.', '');
				} else {
					$amount = $record->getAmount();
				}
				$line[] = $amount;
				$line = implode(',', $line);
				Debug::Text('Line: '. $line, __FILE__, __LINE__, __METHOD__, 10);
				$retval[] = $line;

				unset($line);
			}
			unset($line1);
		}

		if ( isset($retval) ) {
			Debug::Text('Returning Compiled Records: ', __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}

		return FALSE;
	}

	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
        //record numbers.

		$compiled_data = @implode("\r\n", $this->compileRecords() );

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 10 ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}


/**
 * @package Core\GeneralLedgerExport
 */
class GeneralLedgerExport_File_Format_CSV Extends GeneralLedgerExport {
	var $data = NULL;

	function __construct( $data ) {
		Debug::Text(' General Ledger Format CSV Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		$this->data = $data;

		return TRUE;
	}

	private function toDate($epoch) {
		return date('m-d-y', $epoch);
	}


	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data records:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Column headers
		$retval[] = 'Date,Source,Comment,Account,Debit,Credit';

		foreach ( $this->data as $key => $journal_entry ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$line1[] = $this->toDate( $journal_entry->getDate() );
			$line1[] = '"'.$journal_entry->getSource().'"';
			$line1[] = '"'.$journal_entry->getComment().'"';
			$line1[] = NULL;
			$line1[] = NULL;
			$line1[] = NULL;

			$line1 = implode(',', $line1);
			Debug::Text('Line 1: '. $line1, __FILE__, __LINE__, __METHOD__, 10);
			$retval[] = $line1;

			$records = $journal_entry->getRecords();
			foreach ($records as $record) {
				for( $i = 0; $i < 3; $i++ ) {
					$line[] = NULL;
				}

				$line[] = $record->getAccount();
				if ( $record->getType() == 'debit' ) {
					$line[] = $record->getAmount();
					$line[] = NULL;
				} else {
					$line[] = NULL;
					$line[] = $record->getAmount();
				}

				$line = implode(',', $line);
				Debug::Text('Line: '. $line, __FILE__, __LINE__, __METHOD__, 10);
				$retval[] = $line;

				unset($line);
			}
			unset($line1);
		}

		if ( isset($retval) ) {
			Debug::Text('Returning Compiled Records: ', __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}

		return FALSE;
	}

	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
        //record numbers.

		$compiled_data = @implode("\r\n", $this->compileRecords() );

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 10 ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}

/**
 * @package Core\GeneralLedgerExport
 */
class GeneralLedgerExport_File_Format_QuickBooks Extends GeneralLedgerExport {
	var $data = NULL;

	function __construct( $data ) {
		Debug::Text(' General Ledger Format QuickBooks Contruct... ', __FILE__, __LINE__, __METHOD__,10);

		$this->data = $data;

		return TRUE;
	}

	private function toDate($epoch) {
		return date('m/d/y', $epoch);
	}


	private function compileRecords() {
		//gets all Detail records.

		if ( count($this->data) == 0 ) {
			Debug::Text('No data records:', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}


		/*
		!TRNS   TRNSID  TRNSTYPE        DATE    ACCNT   CLASS   AMOUNT  DOCNUM  MEMO
		!SPL    SPLID   TRNSTYPE        DATE    ACCNT   CLASS   AMOUNT  DOCNUM  MEMO
		!ENDTRNS
		TRNS            GENERAL JOURNAL 7/1/1998        Checking                650
		SPL             GENERAL JOURNAL 7/1/1998        Expense Account         -650
		ENDTRNS
		*/
		//Column headers
		$retval[] = "!TRNS\tTRNSID\tTRNSTYPE\tDATE\tACCNT\tCLASS\tAMOUNT\tDOCNUM\tMEMO";
		$retval[] = "!SPL\tSPLID\tTRNSTYPE\tDATE\tACCNT\tCLASS\tAMOUNT\tDOCNUM\tMEMO";
		$retval[] = '!ENDTRNS';

		foreach ( $this->data as $key => $journal_entry ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$records = $journal_entry->getRecords();
			$i=0;
			foreach ($records as $record) {
				if( $i == 0 ) {
					$line[] = 'TRANS';
				} else {
					$line[] = 'SPL';
				}

				$line[] = NULL; //TRANSID
				$line[] = 'GENERAL JOURNAL'; //TRNSTYPE
				$line[] = $this->toDate( $journal_entry->getDate() );

				$line[] = $record->getAccount();

				$line[] = NULL; //Class

				if ( $record->getType() == 'debit' ) {
					$line[] = $record->getAmount();
				} else {
					$line[] = $record->getAmount()*-1; //Credits are negative.
				}

				$line[] = NULL; //DOCNUM
				$line[] = $journal_entry->getComment(); //Memo

				$line = implode("\t", $line);
				Debug::Text('Line: '. $line, __FILE__, __LINE__, __METHOD__, 10);
				$retval[] = $line;

				unset($line);

				$i++;
			}

			$retval[] = 'ENDTRNS';
		}

		if ( isset($retval) ) {
			Debug::Text('Returning Compiled Records: ', __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}

		return FALSE;
	}

	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
        //record numbers.

		$compiled_data = @implode("\r\n", $this->compileRecords() );

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 10 ) {
			return $compiled_data;
		}

		Debug::Text('Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
}

?>
