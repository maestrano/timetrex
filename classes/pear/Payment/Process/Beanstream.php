<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Beanstream processor
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Payment
 * @package    Payment_Process
 * @author     Mike Benoit <ipso@snappymail.ca>                                |
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Beanstream.php,v 1.33 2005/11/01 18:55:29 jausions Exp $
 * @link       http://pear.php.net/package/Payment_Process
 */

require_once 'Payment/Process.php';
require_once 'Payment/Process/Common.php';
require_once 'Net/Curl.php';

/**
 * Defines global variables
 */
$GLOBALS['_Payment_Process_Beanstream'] = array(
    PAYMENT_PROCESS_ACTION_NORMAL   => 'P',
    PAYMENT_PROCESS_ACTION_AUTHONLY => 'PA',
    PAYMENT_PROCESS_ACTION_POSTAUTH => 'PAC',
    PAYMENT_PROCESS_ACTION_VOID     => 'VP'
);

/**
 * Payment_Process_Beanstream
 *
 * This is a processor for Beanstream's merchant payment gateway.
 * (http://www.beanstream.com/)
 *
 * @package    Payment_Process
 * @author     Mike Benoit <ipso@snappymail.ca>
 * @version    @version@
 * @link       http://www.beanstream.com/
 */
class Payment_Process_Beanstream extends Payment_Process_Common
{
    /**
     * Front-end -> back-end field map.
     *
     * This array contains the mapping from front-end fields (defined in
     * the Payment_Process class) to the field names Beanstream requires.
     *
     * @see _prepare()
     * @access private
     */
    var $_fieldMap = array(
        // Required
        'customerId'    => 'merchant_id',
        'login'         => 'username',
        'password'      => 'password',
        'action'        => 'trnType',
        'invoiceNumber' => 'trnOrderNumber',
        'amount'        => 'trnAmount',
        'name'          =>  '',
        'address'       => 'ordAddress1',
        'city'          => 'ordCity',
        'state'         => 'ordProvince',
        'country'       => 'ordCountry',
        'postalCode'    => 'ordPostalCode',
        'zip'           => 'ordPostalCode',
        'phone'         => 'ordPhoneNumber',
        'email'         => 'ordEmailAddress',
        'errorPage'     => 'errorPage',
    );

    /**
     * $_typeFieldMap
     *
     * @author Joe Stump <joe@joestump.net>
     * @access protected
     */
    var $_typeFieldMap = array(
           'CreditCard' => array(
                'firstName'  => 'firstName',
                'lastName'   => 'lastName',
                'cardNumber' => 'trnCardNumber',
                'cvv'        => 'trnCardCvd',
                'expDate'    => 'expDate'
           ),
    );

    /**
     * Default options for this processor.
     *
     * @see Payment_Process::setOptions()
     * @access private
     */
    var $_defaultOptions = array(
         'authorizeUri' => 'https://www.beanstream.com/scripts/process_transaction.asp',
         'requestType' => 'BACKEND',
         'cavEnabled' => 0,
         'cavServiceVersion' => '1.2',
    );

    /**
     * List of possible encapsulation characters
     *
     * @var string
     * @access private
     */
    var $_encapChars = '|~#$^*_=+-`{}![]:";<>?/&';

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
        $this->_driver = 'Beanstream';
        $this->_makeRequired('customerId','login', 'password', 'action', 'invoiceNumber');
    }

    function Payment_Process_Beanstream($options = false)
    {
        $this->__construct($options);
    }

    /**
     * Processes the transaction.
     *
     * Success here doesn't mean the transaction was approved. It means
     * the transaction was sent and processed without technical difficulties.
     *
     * @return mixed Payment_Process_Result on success, PEAR_Error on failure
     * @access public
     */
    function &process()
    {
        // Sanity check
        $result = $this->validate();
        if (PEAR::isError($result)) {
            return $result;
        }

        // Prepare the data
        $result = $this->_prepare();
        if (PEAR::isError($result)) {
            return $result;
        }

        $fields = $this->_prepareQueryString();
        if (PEAR::isError($fields)) {
            return $fields;
        }

        // Don't die partway through
        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

        $curl = new Net_Curl($this->_options['authorizeUri']);
        if (PEAR::isError($curl)) {
            PEAR::popErrorHandling();
            return $curl;
        }

        $curl->type = 'post';
        $curl->fields = $fields;
        $curl->userAgent = 'PEAR Payment_Process_Beanstream 0.1';

        //$curl->verboseAll();

        $result = $curl->execute();
        if (PEAR::isError($result)) {
            PEAR::popErrorHandling();
            return $result;
        } else {
            $curl->close();
        }

        $this->_responseBody = trim($result);
        $this->_processed = true;

        // Restore error handling
        PEAR::popErrorHandling();

        $response = Payment_Process_Result::factory($this->_driver,
                                                     $this->_responseBody,
                                                     $this);

        if (!PEAR::isError($response)) {
            $response->parse();
        }
        $response->action = $this->action;

        return $response;
    }

    /**
     * Processes a callback from payment gateway
     *
     * Success here doesn't mean the transaction was approved. It means
     * the callback was received and processed without technical difficulties.
     *
     * @return mixed Payment_Process_Result on success, PEAR_Error on failure
     */
    function &processCallback()
    {
        $this->_responseBody = $_POST;
        $this->_processed = true;

        $response = &Payment_Process_Result::factory($this->_driver,
                            $this->_responseBody);
        if (!PEAR::isError($response)) {
            $response->_request = $this;
            $response->parseCallback();

            $r = $response->isLegitimate();
            if (PEAR::isError($r)) {
                return $r;

            } elseif ($r === false) {
                return PEAR::raiseError('Illegitimate callback from gateway.');
            }
        }

        return $response;
    }

    /**
     * Get (completed) transaction status.
     *
     * @return string Two-digit status returned from gateway.
     */
    function getStatus()
    {
        return false;
    }

    /**
     * Prepare the POST query string.
     *
     * You will need PHP_Compat::str_split() if you run this processor
     * under PHP 4.
     *
     * @access private
     * @return string The query string
     */
    function _prepareQueryString()
    {
        $data = array_merge($this->_options, $this->_data);

        foreach ($data as $key => $val) {
            if (strlen($val) > 0 ) {
                $return[] = $key.'='.urlencode($val);
            }
        }

        $retval = implode('&', $return);

        return $retval;
    }

    /**
     * _handleName
     *
     * We need to combine firstName and lastName into a
     * single name.
     *
     * @access private
     */
    function _handleName()
    {
        $this->_data['trnCardOwner'] = $this->_payment->firstName.' '.$this->_payment->lastName;
        $this->_data['ordName'] = $this->_payment->firstName.' '.$this->_payment->lastName;
    }

    /**
     * _handleExpDate
     *
     * Convert ExpDate to seperate month/year
     *
     * @access private
     */
    function _handleExpDate()
    {
        $split_expire_date = explode('/', $this->_payment->expDate);
        $this->_data['trnExpMonth'] = $split_expire_date[0];
        $this->_data['trnExpYear'] = substr( $split_expire_date[1], -2, 2);
    }

}


class Payment_Process_Result_Beanstream extends Payment_Process_Result {

    var $_statusCodeMap = array('1' => PAYMENT_PROCESS_RESULT_APPROVED,
                                '2' => PAYMENT_PROCESS_RESULT_DECLINED,
                                '3' => PAYMENT_PROCESS_RESULT_OTHER,
                                '4' => PAYMENT_PROCESS_RESULT_REVIEW
                                );

    var $_avsCodeMap = array(
        'A' => PAYMENT_PROCESS_AVS_MISMATCH,
        'B' => PAYMENT_PROCESS_AVS_ERROR,
        'E' => PAYMENT_PROCESS_AVS_ERROR,
        'G' => PAYMENT_PROCESS_AVS_NOAPPLY,
        'N' => PAYMENT_PROCESS_AVS_MISMATCH,
        'P' => PAYMENT_PROCESS_AVS_NOAPPLY,
        'R' => PAYMENT_PROCESS_AVS_ERROR,
        'S' => PAYMENT_PROCESS_AVS_ERROR,
        'U' => PAYMENT_PROCESS_AVS_ERROR,
        'W' => PAYMENT_PROCESS_AVS_MISMATCH,
        'X' => PAYMENT_PROCESS_AVS_MATCH,
        'Y' => PAYMENT_PROCESS_AVS_MATCH,
        'Z' => PAYMENT_PROCESS_AVS_MISMATCH
    );

    var $_avsCodeMessages = array(
        '0' => 'Address verification not performed for this transaction',
        '5' => 'Invalid AVS repsonse',
        '0' => 'Address verification data contains edit error',
        'A' => 'Address matches, postal code does not',
        'B' => 'Address information not provided',
        'E' => 'Address Verification System Error',
        'G' => 'Non-U.S. Card Issuing Bank',
        'N' => 'No match on street address nor postal code',
        'P' => 'Address Verification System not applicable',
        'R' => 'Retry - System unavailable or timeout',
        'S' => 'Service not supported by issuer',
        'U' => 'Address information unavailable',
        'W' => '9-digit postal code matches, street address does not',
        'X' => 'Address and 9-digit postal code match',
        'Y' => 'Address and 5-digit postal code match',
        'Z' => '5-digit postal code matches, street address does not'
    );

    var $_cvvCodeMap = array('1' => PAYMENT_PROCESS_CVV_MATCH,
                             '2' => PAYMENT_PROCESS_CVV_MISMATCH,
                             '3' => PAYMENT_PROCESS_CVV_ERROR,
                             '4' => PAYMENT_PROCESS_CVV_ERROR,
                             '5' => PAYMENT_PROCESS_CVV_ERROR,
                             '6' => PAYMENT_PROCESS_CVV_ERROR
    );

    var $_cvvCodeMessages = array(
        1 => 'CVV code matches',
        2 => 'CVV code does not match',
        3 => 'CVV code was not processed',
        4 => 'CVV code should have been present',
        5 => 'Issuer unable to process request',
        6 => 'CVV not provided',
    );

    var $_fieldMap = array('0'  => 'code',
                           '2'  => 'messageCode',
                           '3'  => 'message',
                           '4'  => 'approvalCode',
                           '5'  => 'avsCode',
                           '6'  => 'transactionId',
                           '7'  => 'invoiceNumber',
                           '8'  => 'description',
                           '9'  => 'amount',
                           '12' => 'customerId',
                           '37' => 'md5Hash',
                           '38' => 'cvvCode'
    );

    function Payment_Process_Response_Beanstream($rawResponse)
    {
        $this->Payment_Process_Response($rawResponse);
    }

    /**
     * Parses the data received from the payment gateway
     *
     * @access public
     */
    function parse()
    {

        //get last line of response
        $split_response = explode("\n", $this->_rawResponse );
        $response_str = array_pop($split_response);

        if ( preg_match('/trnApproved=(.*)/i', $response_str ) ) {
            if ( isset($response_str) AND strlen($response_str) > 0 ) {

                //Parse URL for variables.
                $response_str_arr = explode('&', urldecode( $response_str ) );

                if ( is_array($response_str_arr) ) {
                    foreach( $response_str_arr as $response_value ) {
                        $split_response_value = explode('=', $response_value, 2);
                        if ( isset($split_response_value[0]) AND isset($split_response_value[1]) ) {
                            $responseArray[trim($split_response_value[0])] = trim($split_response_value[1]);
                        }
                    }
                    unset($response_str_arr, $response_value, $split_response_value);

                    //var_dump($responseArray);

                    if ( !isset($responseArray) AND !is_array( $responseArray) ) {
                        $this->_returnCode = PAYMENT_PROCESS_RESULT_OTHER;
                    }

                    if ( isset($responseArray['trnApproved']) AND $responseArray['trnApproved'] == 1 ) {
                        $this->_returnCode = PAYMENT_PROCESS_RESULT_APPROVED;
                    } else {
                        $this->_returnCode = PAYMENT_PROCESS_RESULT_DECLINED;
                    }

                    if ( isset($responseArray['messageId']) ) {
                        $this->code          = $responseArray['messageId'];
                        $this->messageCode   = $responseArray['messageId'];
                    }

                    if ( isset($responseArray['errorType']) AND $responseArray['errorType'] == 'N' ) {
                        $this->message = $responseArray['messageText'] .' (Code: '. $responseArray['messageId'] .')';
                    } elseif ( $responseArray['errorType'] != 'N' AND isset($responseArray['errorFields']) ) {
                        $this->message = $responseArray['messageText'] .' Code('. $responseArray['messageId'] .')';
                        if ( isset($responseArray['errorFields']) AND $responseArray['errorFields'] != '' ) {
                            $this->message .= ' Error Fields('.$responseArray['errorFields'].')';
                        }
                    }

                    if ( isset($responseArray['authCode']) ) {
                        $this->approvalCode = $responseArray['authCode'];
                    }

                    if ( isset($responseArray['trnId']) ) {
                        $this->transactionId = $responseArray['trnId'];
                    }

                    if ( isset($responseArray['trnOrderNumber']) ) {
                        $this->invoiceNumber = $responseArray['trnOrderNumber'];
                    }

                    if ( isset($responseArray['cvdId']) ) {
                        $this->cvvCode = $responseArray['cvdId'];
                        if ( isset($this->_cvvCodeMessages[$this->cvvCode]) ) {
                            $this->cvvMessage = $this->_cvvCodeMessages[$this->cvvCode];
                        }
                    }

                    //$this->avsCode       = '';
                }
                unset($location_url_arr);
            }
        } else {
            $this->_returnCode = PAYMENT_PROCESS_RESULT_OTHER;
            $this->message = 'Error parsing response.';
        }
    }
}
?>