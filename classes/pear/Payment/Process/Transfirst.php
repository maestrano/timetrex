<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at                              |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Ian Eure <ieure@php.net>                                    |
// +----------------------------------------------------------------------+
//
// $Id: Transfirst.php,v 1.3 2005/07/14 20:54:00 ieure Exp $

require_once('Payment/Process/Common.php');
require_once('Net/Curl.php');

// Transfirst transaction types
// Request authorization only - no funds are transferred.
define('PAYMENT_PROCESS_ACTION_TRANSFIRST_AUTH', 30);
// Transfer funds from a previous authorization.
define('PAYMENT_PROCESS_ACTION_TRANSFIRST_SETTLE', 40);
// Authorize & transfer funds
define('PAYMENT_PROCESS_ACTION_TRANSFIRST_AUTHSETTLE', 32);
// Debit the indicated amount to a previously-charged card.
define('PAYMENT_PROCESS_ACTION_TRANSFIRST_CREDIT', 20);
// Cancel authorization
define('PAYMENT_PROCESS_ACTION_TRANSFIRST_VOID', 61);

define('PAYMENT_PROCESS_RESULT_TRANSFIRST_APPROVAL', 00);
define('PAYMENT_PROCESS_RESULT_TRANSFIRST_DECLINE', 05);
define('PAYMENT_PROCESS_RESULT_TRANSFIRST_INVALIDAMOUNT', 13);
define('PAYMENT_PROCESS_RESULT_TRANSFIRST_INVALIDCARDNO', 14);
define('PAYMENT_PROCESS_RESULT_TRANSFIRST_REENTER', 19);

// Map actions
$GLOBALS['_Payment_Process_Transfirst'] = array(
    PAYMENT_PROCESS_ACTION_NORMAL => PAYMENT_PROCESS_ACTION_TRANSFIRST_AUTHSETTLE,
    PAYMENT_PROCESS_ACTION_AUTHONLY => PAYMENT_PROCESS_ACTION_TRANSFIRST_AUTH,
    PAYMENT_PROCESS_ACTION_POSTAUTH => PAYMENT_PROCESS_ACTION_TRANSFIRST_SETTLE
);

/**
 * Payment_Process_Transfirst
 *
 * This is a processor for TransFirst's merchant payment gateway, formerly known
 * as DPILink. (http://www.transfirst.com/)
 *
 * *** WARNING ***
 * This is BETA code. While I have tested it and it appears to work for me, I
 * strongly recommend that you do additional testing before using it in
 * production systems.
 *
 * @package Payment_Process
 * @author Ian Eure <ieure@php.net>
 * @version @version@
 */
class Payment_Process_Transfirst extends Payment_Process_Common {
    /**
     * Front-end -> back-end field map.
     *
     * This array contains the mapping from front-end fields (defined in
     * the Payment_Process class) to the field names Transfirst requires.
     *
     * @see _prepare()
     * @access private
     */
    var $_fieldMap = array(
        // Required
        'login'             => "DPIAccountNum",
        'password'          => "password",
        'action'            => "transactionCode",
        'invoiceNumber'     => "orderNum",
        'customerId'        => "customerNum",
        'amount'            => "transactionAmount",
        'transactionSource' => "ECommerce",
        // Credit Card Type
        'cardNumber'        => "cardAccountNum",
        'expDate'           => "expirationDate",
        'zip'               => "cardHolderZip",
        // Common Type
//         'name'              => "cardHolderName",
        'address'           => "cardHolderAddress",
        'city'              => "cardHolderCity",
        'state'             => "cardHolderState",
        'phone'             => "cardHolderPhone",
        'email'             => "cardHolderEmail"
    );

    /**
     * Default options for this processor.
     *
     * @see Payment_Process::setOptions()
     * @access private
     */
    var $_defaultOptions = array(
        'authorizeUri' => "https://epaysecure.transfirst.com/eLink/authpd.asp"
    );

    /**
     * Has the transaction been processed?
     *
     * @type boolean
     * @access private
     */
    var $_processed = false;

    /**
     * The response body sent back from the gateway.
     *
     * @access private
     */
    var $_responseBody = '';

    /**
     * Constructor.
     *
     * @param  array  $options  Class options to set.
     * @see Payment_Process::setOptions()
     * @return void
     */
    function __construct($options = false)
    {
        parent::__construct($options);
        $this->_driver = 'Transfirst';
        $this->_makeRequired('login', 'password', 'action', 'invoiceNumber', 'customerId', 'amount', 'cardNumber', 'expDate');
    }

    function Payment_Process_Transfirst($options = false)
    {
        $this->__construct($options);
    }

    /**
     * Prepare the data.
     *
     * This function handles the 'testTransaction' option, which is specific to
     * this processor.
     */
    function _prepare()
    {
        if ($this->_options['testTransaction']) {
            $this->_data['testTransaction'] = $this->_options['testTransaction'];
        }
        $this->_handleCardHolderName();
        return parent::_prepare();
    }

    /**
     * Process the transaction.
     *
     * @return mixed Payment_Process_Result on success, PEAR_Error on failure
     */
    function &process()
    {
        // Sanity check
        if(PEAR::isError($res = $this->validate())) {
            return($res);
        }

        // Prepare the data
        $this->_prepare();

        // Don't die partway through
        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

        $req = new Net_Curl($this->_options['authorizeUri']);
        if (PEAR::isError($req)) {
            PEAR::popErrorHandling();
            return $req;
        }
        $req->type = 'POST';
        $req->fields = $this->_prepareQueryString();
        $req->userAgent = 'PEAR Payment_Process_Transfirst 0.1';
        $res = &$req->execute();
        $req->close();
        if (PEAR::isError($res)) {
            PEAR::popErrorHandling();
            return $res;
        }

        $this->_processed = true;

        // Restore error handling
        PEAR::popErrorHandling();

        $response = trim($res);
        print "Response: {$response}\n";
        $result = &Payment_Process_Result::factory('Transfirst', $response);
        $result->_request = &$this;
        $this->_result = &$result;

        return $result;

        /*
         * HTTP_Request doesn't do SSL until PHP 4.3.0, but it
         * might be useful later...
        $req = new HTTP_Request($this->_authUri);
        $this->_setPostData();
        $req->sendRequest();
        */
    }

    /**
     * Get (completed) transaction status.
     *
     * @return string Two-digit status returned from gateway.
     */
    function getStatus()
    {
        if (!$this->_processed) {
            return PEAR::raiseError('The transaction has not been processed yet.', PAYMENT_PROCESS_ERROR_INCOMPLETE);
        }
        return $this->_result->code;
    }

    /**
     * Get transaction sequence.
     *
     * 'Sequence' is what Transfirst calls their transaction ID/approval code. This
     * function returns that code from a processed transaction.
     *
     * @return mixed  Sequence ID, or PEAR_Error if the transaction hasn't been
     *                processed.
     */
    function getSequence()
    {
        if (!$this->_processed) {
            return PEAR::raiseError('The transaction has not been processed yet.', PAYMENT_PROCESS_ERROR_INCOMPLETE);
        }
        return $this->_result->_sequenceNumber;
    }

    /**
     * Prepare the POST query string.
     *
     * @access private
     * @return string The query string
     */
    function _prepareQueryString()
    {
        foreach($this->_data as $var => $value) {
            if (strlen($value))
                $tmp[] = urlencode($var).'='.urlencode($value);
        }
        return @implode('&', $tmp);
    }

    /*
    function _setPostData(&$req)
    {
        foreach($this->_data as $var => $value) {
            $req->addPostData($var, $value);
        }
    }
    */

    /**
     * Handle transaction source.
     *
     * @access private
     */
    function _handleTransactionSource()
    {
        $specific = $this->_fieldMap['transactionSource'];
        if ($this->transactionSource == PAYMENT_PROCESS_SOURCE_ONLINE) {
            $this->_data[$specific] = 'Y';
        } else {
            $this->_data[$specific] = 'N';
        }
    }

    /**
     * Handle card expiration date.
     *
     * The gateway wants the date in the format MMYY, with no other chars.
     *
     * @access private
     */
    function _handleExpDate()
    {
        $specific = $this->_fieldMap['expDate'];
        if (isset($this->_data[$specific])) {
            $this->_data[$specific] = str_replace('/', '', $this->_data[$specific]);
        } else {
            $this->_data[$specific] = str_replace('/', '', $this->expDate);
        }
    }

    /**
     * Map firstName & lastName
     *
     * P_P now has split firstName/lastName fields, instead of 'name.' This
     * handles concatenating them into the Transfirst cardHolderName field.
     *
     * @return  void
     */
    function _handleCardHolderName()
    {
        $this->_data['cardHolderName'] = $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Validate the merchant account login.
     *
     * The Transfirst docs specify that the login is exactly eight digits.
     *
     * @access private
     * @return boolean true if valid, false otherwise
     */
    function _validateLogin()
    {
        return Validate::string($this->login, array(
            'format' => VALIDATE_NUM,
            'max_length' => 8,
            'min_length' => 8
        ));
    }

    /**
     * Validate the merchant account password.
     *
     * The Transfirst docs specify that the password is a string between 6 and 10
     * characters in length.
     *
     * @access private
     * @return boolean true if valid, false otherwise
     */
    function _validatePassword()
    {
        return Validate::string($this->password, array(
            'format' => VALIDATE_ALPHA . VALIDATE_NUM,
            'min_length' => 6,
            'max_length' => 10
        ));
    }

    /**
     * Validate the invoice number.
     *
     * Invoice number must be a 5-character long alphanumeric string.
     *
     * @return boolean true on success, false otherwise
     */
    function _validateInvoiceNumber()
    {
        return Validate::string($this->invoiceNumber, array(
            'format' => VALIDATE_NUM . VALIDATE_ALPHA,
            'min_length' => 5,
            'max_length' => 5
        ));
    }

    /**
     * Validate the invoice number.
     *
     * Invoice no. must be a 15-character long alphanumeric string.
     *
     * @return boolean true on success, false otherwise
     */
    function _validateCustomerId()
    {
        return Validate::string($this->customerId, array(
            'format' => VALIDATE_NUM . VALIDATE_ALPHA,
            'min_length' => 15,
            'max_length' => 15
        ));
    }

    /**
     * Validate the zip code.
     *
     * Zip is only required if AVS is enabled.
     *
     * @return boolean true on success, false otherwise.
     */
    function _validateZip()
    {
        if(strlen($this->zip) || $this->performAvs) {
            return parent::_validateZip();
        }
        return true;
    }
}

class Payment_Process_Result_Transfirst extends Payment_Process_Result {

    /**
     * Transfirst status codes.
     *
     * This array holds every possible status returned by the Transfirst gateway.
     *
     * See the Transfirst documentation for more details on each response.
     *
     * @see getStatusText()
     * @access private
     */
    var $_statusCodeMessages = array(
        '00' => "Approved",
        '01' => "Refer to issuer",
        '02' => "Refer to issuer - Special condition",
        '03' => "Invalid merchant ID",
        '04' => "Pick up card",
        '05' => "Declined",
        '06' => "General error",
        '07' => "Pick up card - Special condition",
        '13' => "Invalid amount",
        '14' => "Invalid card number",
        '15' => "No such issuer",
        '19' => "Re-enter transaction",
        '21' => "Unable to back out transaction",
        '28' => "File is temporarily unavailable",
        '39' => "No credit account",
        '41' => "Pick up card - Lost",
        '43' => "Pick up card - Stolen",
        '51' => "Insufficient funds",
        '54' => "Expired card",
        '57' => "Transaction not permitted - Card",
        '61' => "Amount exceeds withdrawal limit",
        '62' => "Invalid service code, restricted",
        '65' => "Activity limit exceeded",
        '76' => "Unable to locate, no match",
        '77' => "Inconsistent data, rev. or repeat",
        '78' => "No account",
        '80' => "Invalid date",
        '85' => "Card OK",
        '91' => "Issuer or switch is unavailable",
        '93' => "Violation, cannot complete",
        '96' => "System malfunction",
        '98' => "No matching transaction to void",
        '99' => "System timeout",
        'L0' => "General System Error - Contact Transfirst Account Exec.",
        'L1' => "Invalid or missing account number",
        'L2' => "Invalid or missing password",
        'L3' => "Expiration Date is not formatted correctly",
        'L4' => "Reference number not found",
        'L6' => "Order number is required but missing",
        'L7' => "Wrong transaction code",
        'L8' => "Network timeout",
        'L14' => "Invalid card number",
        'S5' => "Already settled",
        'S6' => "Not authorized",
        'S7' => "Declined",
        'V6' => "Invalid transaction type",
        'V7' => "Declined",
        'V8' => "Already voided",
        'V9' => "Already posted"
    );

    var $_avsCodeMap = array(
        'A' => "Address match",
        'E' => "Ineligible",
        'N' => "No match",
        'R' => "Retry",
        'S' => "Service unavailable",
        'U' => "Address information unavailable",
        'W' => "9-digit zip match",
        'X' => "Address and 9-digit zip match",
        'Y' => "Address and 5-digit zip match",
        'Z' => "5-digit zip match"
    );

    /**
     * Status code map
     *
     * This contains a map from the Processor-specific result codes to the generic
     * P_P codes. Anything not defined here is treated as a DECLINED result by
     * validate()
     *
     * @type array
     * @access private
     */
    var $_statusCodeMap = array(
        '00' => PAYMENT_PROCESS_RESULT_APPROVED,
        '05' => PAYMENT_PROCESS_RESULT_DECLINED,
        'V7' => PAYMENT_PROCESS_RESULT_DECLINED
    );

    var $_aciCodes = array(
        'A' => "CPS Qualified",
        'E' => "CPS Qualified  -  Card Acceptor Data was submitted in the authorization  request.",
        'M' => "Reserved - The card was not present and no AVS request for International transactions",
        'N' => "Not CPS Qualified",
        'V' => "CPS Qualified ? Included an address verification request in the authorization request."
    );

    var $_authSourceCodes = array(
        ' ' => "Terminal doesn't support",
        '0' => "Exception File",
        '1' => "Stand in Processing, time-out response",
        '2' => "Loss Control System (LCS) response provided",
        '3' => "STIP, response provided, issuer suppress inquiry mode",
        '4' => "STIP, response provided, issuer is down",
        '5' => "Response provided by issuer",
        '9' => "Automated referral service (ARS) stand-in"
    );

    var $_fieldMap = array(
        0  => '_null',                    // TF Internal Message Format
        1  => '_acctNo',                  // TF Account number
        2  => '_transactionCode',         // The transaction code from the request message passed by the original request.
        3  => 'transactionId',            // Assigned by TF used to uniquely identify transaction.
        4  => '_mailOrder',               // Mail Order Identifier
        5  => '_ccAcctNo',                // The credit card account number passed by the original request.
        6  => '_ccExpDate',               // The Expiration Date passed by the original request. The field is formatted YYMM (Year, Month)
        7  => '_authAmount',              // An eight-digit value, which denotes the dollar amount passed to TF, without a decimal. ( DDDDDDCC )
        8  => '_authDate',                // A six-digit value, which denotes the date the authorization, was attempted.  The field is formatted YYMMDD. (Year, Month, Date)
        9  => '_authTime',                // A six-digit value, which denotes the time the authorization, was attempted.  The field is formatted HHMMSS.  (Hour, Minute, Second)
        10 => 'messageCode',              // A two-digit value, which indicates the result of the authorization request.  Used to determine if the card was authorized, declined or timed out.
        11 => 'customerId',               // The Customer Number passed by the original request
        12 => 'invoiceNumber',            // The Order Number passed by the original request.
        13 => '_urn',                     // A number that uniquely identifies an individual transaction.  Assigned by TF and can be used when referencing a specific transaction.
        14 => '_authResponse',            // A number provided by the issuing bank indicating the authorization is valid and funds have been reserved for transfer to the merchants account at a later time.
        15 => '_authSource',              // A code that defines the source where an authorization was captured.
        16 => '_authCharacteristic',      // A code that defines the qualification level for the authorized transaction.
        17 => 'approvalCode',             // Assigned by Visa or MasterCard, used to uniquely identify and link together all related information and used to authorize and clear a transaction.
        18 => '_validationCode',          // Assigned by V.I.P. System that is used to determine the accuracy of the authorization data.
        19 => '_sicCatCode',              // A merchants industry classification.  Example - Mail Order/Phone Order Merchants (Direct Market) = 5969.
        20 => '_currencyCode',            // 840 indicate US Currency to date this is the only valid value.
        21 => 'avsCode',                  // A value that indicates the level of Address Verification that was validated.
        22 => '_merchantStoreNo',         // Identifies the specific terminal used at a location  1-4 Merchant store #, 5-8 specific terminal at store.
        23 => 'cvvCode'                   // A two-digit value, indicating the result of the card verification based on the CVV2 code provided by the cardholder.
    );

    /**
     * Constructor.
     *
     * @param  string  $rawResponse  The raw response from the gateway
     * @return mixed boolean true on success, PEAR_Error on failure
     */
    function Payment_Process_Result_Transfirst($rawResponse)
    {
        $res = $this->_validateResponse($rawResponse);
        if (!$res || PEAR::isError($res)) {
            if (!$res) {
                $res = PEAR::raiseError("Unable to validate response body");
            }
            return $res;
        }

        $this->_rawResponse = $rawResponse;
        $res = $this->_parseResponse();
    }

    function getAuthSource()
    {
        return @$this->_authSourceCodes[$this->_authSource];
    }

    function getAuthCharacteristic()
    {
        return @$this->_aciCodes[$this->_authChar];
    }

    function getCode()
    {
        return $this->_statusCodeMap[$this->messageCode];
    }

    /**
     * Parse Transfirst (DPILink) R1 response string.
     *
     * This function parses the response the gateway sends back, which is in
     * pipe-delimited format.
     *
     * @return void
     */
    function _parseResponse()
    {
        $this->_mapFields(explode('|', $this->_rawResponse));
    }

    /**
     * Validate a R1 response.
     *
     * @return boolean
     */
    function _validateResponse($resp)
    {
        if (strlen($resp) > 160)
            return false;

        // FIXME - add more tests

        return true;
    }
}

?>
