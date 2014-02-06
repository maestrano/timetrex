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
// | Authors: Joe Stump <joe@joestump.net>                                |
// |          Robert Peake <robert.peake@trustcommerce.com>               |
// +----------------------------------------------------------------------+
//
// $Id: TrustCommerce.php,v 1.5 2005/07/08 00:13:25 jstump Exp $

require_once('Payment/Process.php');
require_once('Payment/Process/Common.php');
require_once('Net/Curl.php');

$GLOBALS['_Payment_Process_TrustCommerce'] = array(
    PAYMENT_PROCESS_ACTION_NORMAL   => 'sale',
    PAYMENT_PROCESS_ACTION_AUTHONLY => 'preauth',
    PAYMENT_PROCESS_ACTION_POSTAUTH => 'postauth'
);

/**
 * Payment_Process_TrustCommerce
 *
 * This is a processor for TrustCommerce's merchant payment gateway.
 * (http://www.trustcommerce.com/)
 *
 * *** WARNING ***
 * This is ALPHA code, and has not been fully tested. It is not recommended
 * that you use it in a production envorinment without further testing.
 *
 * @package Payment_Process
 * @author Robert Peake <robert.peake@trustcommerce.com> 
 * @version @version@
 */
class Payment_Process_TrustCommerce extends Payment_Process_Common {
    /**
     * Front-end -> back-end field map.
     *
     * This array contains the mapping from front-end fields (defined in
     * the Payment_Process class) to the field names TrustCommerce requires.
     *
     * @see _prepare()
     * @access private
     */
    var $_fieldMap = array(
        // Required
        'login' => 'custid',
        'password' => 'password',
        'action' => 'action',
        'amount' => 'amount',
        //PostAuth
        'transactionId' => 'transid',
        // Optional
        'name' => 'name',
        'address' => 'address1',
        'city' => 'city',
        'state' => 'state',
        'country' => 'country',
        'phone' => 'phone',
        'email' => 'email',
        'zip' => 'zip',
        'currency' => 'currency',
    );

    /**
    * $_typeFieldMap
    *
    * @author Robert Peake <robert.peake@trustcommerce.com>
    * @access protected
    */
    var $_typeFieldMap = array(

           'CreditCard' => array(

                    'cardNumber' => 'cc',
                    'cvv' => 'cvv',
                    'expDate' => 'exp'

           ),

           'eCheck' => array(

                    'routingCode' => 'routing',
                    'accountNumber' => 'account',
                    'name' => 'name'

           )
    );

    /**
     * Default options for this processor.
     *
     * @see Payment_Process::setOptions()
     * @access private
     */
    var $_defaultOptions = array();

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
        $this->_driver = 'TrustCommerce';
    }

    function Payment_Process_TrustCommerce($options = false)
    {
        $this->__construct($options);
    }

    /**
     * Process the transaction.
     *
     * @return mixed Payment_Process_Result on success, PEAR_Error on failure
     */
    function &process()
    {
        // Sanity check
        $result = $this->validate();
        if(PEAR::isError($result)) {
            return $result;
        }

        // Prepare the data
        $result = $this->_prepare();
        if (PEAR::isError($result)) {
            return $result; 
        }

        // Don't die partway through
        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

        $fields = $this->_prepareQueryString();

        if(function_exists('tclink_send')) {
            /** USE TCLINK **/
            $result = tclink_send($fields);
            $r_keys = array_keys($result);
            for($i=0;$i<sizeof($r_keys);$i++) {
                $key = $r_keys[$i];
                $value = $result[$key];
                $result_string .= $key.'='.$value."\n";
            }
            if (PEAR::isError($result_string)) {
                PEAR::popErrorHandling();
                return $result_string;
            } else {
                $result = $result_string;
            }
        } else {
            /** USE CURL **/
            $curl = new Net_Curl('https://vault.trustcommerce.com/trans/');
            if (PEAR::isError($curl)) {
                PEAR::popErrorHandling();
                return $curl;
            }

            $curl->type = 'PUT';
            $curl->fields = $fields;
            $curl->userAgent = 'PEAR Payment_Process_TrustCommerce 0.1a';

            $result = &$curl->execute();
            if (PEAR::isError($result)) {
                PEAR::popErrorHandling();
                return $result;
            } else {
                $curl->close();
            }
        }
        /** END TCLINK/CURL CASE STATEMENT **/

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

        return $response;
    }

    /**
     * Get (completed) transaction status.
     *
     * @return boolean status.
     */
    function getStatus()
    {
        return false;
    }

    /**
     * Prepare the POST query string.
     *
     * @access private
     * @return string The query string
     */
    function _prepareQueryString()
    {

        $data = $this->_data;

        /* expiration is expressed as mmyy */
        $fulldate = $data['exp'];
        $month = strtok($fulldate,'/');
        $year = strtok('');
        $exp = $month.substr($year,2,2);
        $data['exp'] = $exp;
        /* end expiration mangle */

        /* amount is expressed in cents with leading zeroes */
        $data['amount'] = $data['amount']*100;
        if (strlen($data['amount']) == 1) {
            $data['amount'] = "00".$data['amount'];
        } else if(strlen($data['amount']) < 3) {
            $data['amount'] = "0".$data['amount'];
        } else if(strlen($data['amount']) > 8) {
            $amount_message = 'Amount: '.$data['amount'].' too large.';
            PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
            PEAR::raiseError($amount_message);
            PEAR::popErrorHandling();
        }
        /* end amount mangle */

        if ($this->_payment->getType() == 'CreditCard' && 
            $this->action != PAYMENT_PROCESS_ACTION_POSTAUTH) {
            $data['media'] = 'cc';
        }

        if ($this->_payment->getType() == 'eCheck') {
            $data['media'] = 'ach';
        }

        $return = array();
        $sets = array();
        foreach ($data as $key => $val) {
            if (strlen($val)) {
                $return[$key] = $val;
                $sets[] = $key.'='.urlencode($val);
            }
        }
        
        $this->_options['authorizeUri'] = 'https://vault.trustcommerce.com/trans/?'.implode('&',$sets);

        return $return;
    }
}

class Payment_Process_Result_TrustCommerce extends Payment_Process_Result {

    var $_statusCodeMap = array('approved' => PAYMENT_PROCESS_RESULT_APPROVED,
                                'accepted' => PAYMENT_PROCESS_RESULT_APPROVED,
                                'declined' => PAYMENT_PROCESS_RESULT_DECLINED,
                                'baddata' => PAYMENT_PROCESS_RESULT_OTHER,
                                'error' => PAYMENT_PROCESS_RESULT_OTHER);

    /**
     * TrustCommerce status codes
     *
     * This array holds response codes. 
     *
     * @see getStatusText()
     * @access private
     */
    var $_statusCodeMessages = array(
        'approved' => 'The transaction was successfully authorized.',
        'accepted' => 'The transaction has been successfully accepted into the system.',
        'decline' => 'The transaction was declined, see declinetype for further details.',
        'baddata' => 'Invalid parameters passed, see error for further details.',
        'error' => 'System error when processing the transaction, see errortype for details.',
    );

    var $_avsCodeMap = array(
        'N' => PAYMENT_PROCESS_AVS_MISMATCH,
        'U' => PAYMENT_PROCESS_AVS_NOAPPLY,
        'G' => PAYMENT_PROCESS_AVS_NOAPPLY,
        'R' => PAYMENT_PROCESS_AVS_ERROR,
        'E' => PAYMENT_PROCESS_AVS_ERROR,
        'S' => PAYMENT_PROCESS_AVS_ERROR,
        'O' => PAYMENT_PROCESS_AVS_ERROR
    );

    var $_avsCodeMessages = array(
         'X' => 'Exact match, 9 digit zipcode.',
         'Y' => 'Exact match, 5 digit zipcode.',
         'A' => 'Street address match only.',
         'W' => '9 digit zipcode match only.',
         'Z' => '5 digit zipcode match only.',
         'N' => 'No mtach on street address or zipcode.',
         'U' => 'AVS unavailable on this card.',
         'G' => 'Non-US card issuer, AVS unavailable.',
         'R' => 'Card issuer system currently down, try again later.',
         'E' => 'Error, ineligible - not a mail/phone order.',
         'S' => 'Service not supported.',
         'O' => 'General decline or other error'
    );

    var $_cvvCodeMap = array('cvv' => PAYMENT_PROCESS_CVV_MISMATCH
    );

    var $_cvvCodeMessages = array( 'cvv' => 'The CVV number is not valid.'
    );

    var $_fieldMap = array('status'  => 'code',
                           'avs'  => 'avsCode',
                           'transid'  => 'transactionId'
    );


    function Payment_Process_Response_TrustCommerce($rawResponse) 
    {
        $this->Payment_Process_Response($rawResponse);
    }

    function parse()
    {
      $array = preg_split("/\n/",$this->_rawResponse,0,PREG_SPLIT_NO_EMPTY);
      for($i=0;$i<sizeof($array);$i++)
      {
          $response_line = $array[$i];
          $response_array = preg_split("/=/",$response_line);
          $key = $response_array[0];
          $value = $response_array[1];
          $responseArray[$key] = $value;
      }
      $this->_mapFields($responseArray);
    }

    function _mapFields($responseArray)
    {
        foreach($this->_fieldMap as $key => $val) {
            $this->$val = $responseArray[$key];
        }
        if (!isset($this->_statusCodeMessages[$this->messageCode])) 
        {
            $message = $this->_statusCodeMessages[$responseArray['status']];
            if($responseArray['error'])
            {
                $message .= "\nError type: ".$responseArray['error'].'.';
                if($responseArray['offenders'])
                {
                    $message .= "\nOffending fields: ".$responseArray['offenders'].'.';
                }
            }
            $this->_statusCodeMessages[$this->messageCode] = $message;
        } 
    }
}
?>
