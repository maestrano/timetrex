<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Beanstream Batch EFT processor
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
$GLOBALS['_Payment_Process_BeanstreamEFT'] = array(
    PAYMENT_PROCESS_ACTION_NORMAL   => 'D',
    PAYMENT_PROCESS_ACTION_AUTHONLY => NULL,
    PAYMENT_PROCESS_ACTION_POSTAUTH  => NULL,
    PAYMENT_PROCESS_ACTION_VOID     => 'VD'
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
class Payment_Process_BeanstreamEFT extends Payment_Process_Common
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
        'customerId'    => 'loginCompany',
        'login'         => 'loginUser',
        'password'      => 'loginPass',
        'action'        => 'trnType',
        'invoiceNumber' => 'trnOrderNumber',
        'amount'        => 'trnAmount',
        'processDate'   => 'processDate',
        'batchFile'     => 'batchFile',
        //'name'          => 'name',
        //'address'       => '',
        //'city'          => '',
        //'state'         => '',
        //'country'       => '',
        //'postalCode'    => '',
        //'zip'           => '',
        //'phone'         => '',
        //'email'         => '',
        //'errorPage'     => 'errorPage',
    );

    /**
     * $_typeFieldMap
     *
     * @author Joe Stump <joe@joestump.net>
     * @access protected
     */
    var $_typeFieldMap = array(
            'ACH' => array(
                'routingCode' => 'routingNumber',
                'accountNumber' => 'accountNumber',
                'accountCode' => 'accountCode',
                'name' => 'ordName'
            ),
            'EFT' => array(
                'institutionCode' => 'institutionId',
                'routingCode' => 'transitNumber',
                'accountNumber' => 'accountNumber',
                'name' => 'ordName'
            ),
    );

    /**
     * Default options for this processor.
     *
     * @see Payment_Process::setOptions()
     * @access private
     */
    var $_defaultOptions = array(
         'authorizeUri' => 'https://www.beanstream.com/scripts/batch_upload.asp',
         //'requestType' => 'BACKEND',
         //'cavEnabled' => 0,
         'ServiceVersion' => '1.1',
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
        $this->_driver = 'BeanstreamEFT';
        $this->_makeRequired('customerId','login', 'password', 'action', 'invoiceNumber');
    }

    function Payment_Process_BeanstreamEFT($options = false)
    {
        $this->__construct($options);
    }

    /**
     * Prepare the batch data.
     *
     * This function handles the 'testTransaction' option, which is specific to
     * this processor.
     */
    function _prepare()
    {
        $result = parent::_prepare();
        if ( $result !== TRUE ) {
            return $result;
        }

        //Build batch file for each type of transaction type.
        if ($this->_payment->getType() == 'EFT') {
            $batch_fields = array('trnType', 'institutionId', 'transitNumber', 'accountNumber', 'trnAmount', 'trnOrderNumber', 'ordName' );
            if ( is_array($batch_fields) ) {
                $batch_data[] = substr( $this->_payment->getType(), 0, 1);
                foreach( $batch_fields as $batch_field ) {
                    $batch_data[] = $this->_data[$batch_field];
                }
            }
        } elseif ($this->_payment->getType() == 'ACH') {
            $batch_fields = array('trnType', 'routingNumber', 'accountNumber', 'accountCode', 'trnAmount', 'trnOrderNumber', 'ordName' );
            if ( is_array($batch_fields) ) {
                $batch_data[] = substr( $this->_payment->getType(), 0, 1);
                foreach( $batch_fields as $batch_field ) {
                    if ( $batch_field == 'accountCode' ) {
                        $batch_data[] = 'CC';
                    } else {
                        $batch_data[] = $this->_data[$batch_field];
                    }
                }
            }
        }

        if ( is_array($batch_data) ) {
            $this->batchFile = implode(',', $batch_data);
        }

        return TRUE;
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

        //Must use GET for login information.
        $curl = new Net_Curl($this->_options['authorizeUri'].'?'.$fields);
        if (PEAR::isError($curl)) {
            PEAR::popErrorHandling();
            return $curl;
        }

        $temp_file_prefix = 'eft_';
        if ( isset($this->_data['trnOrderNumber']) AND preg_match( '/^[A-Z0-9_\-]+$/i', $this->_data['trnOrderNumber'] ) ) {
            $temp_file_prefix .= $this->_data['trnOrderNumber'].'_';
        }
        $temp_file = tempnam( sys_get_temp_dir(), $temp_file_prefix ).'.csv';
        file_put_contents( $temp_file, $this->batchFile);

        //$curl->type = 'get';
        //$curl->fields = $fields;
        $curl->fields = array('batchFile' => '@'.$temp_file);
        $curl->userAgent = 'PEAR Payment_Process_Beanstream 0.1';

        //$curl->verboseAll();

        $result = $curl->execute();
        unlink($temp_file);

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
        $url_fields = array('ServiceVersion', 'loginCompany', 'loginUser', 'loginPass', 'processDate' );

        $data = array_merge($this->_options, $this->_data);

        foreach ($data as $key => $val) {
            if ( in_array($key, $url_fields) AND strlen($val) > 0 ) {
            //if ( strlen($val) > 0 ) {
                $return[] = $key.'='.urlencode($val);
            }
        }

        $retval = implode('&', $return);

        return $retval;
    }

   /**
     * Validates the charge amount.
     *
     * Charge amount must be 8 characters long, double-precision.
     * Current min/max are rather arbitrarily set to $0.01 and $99999.99,
     * respectively.
     *
     * @return boolean true on success, false otherwise
     */
    function _validateAmount()
    {
        return Validate::number($this->amount, array(
            'decimal' => '.',
            'dec_prec' => 2,
            'min' => 0.01,
            'max' => 99999.99
        ));
    }

    /**
     * _handleAmount
     *
     * Amounts must always be in pennies.
     *
     * @access private
     */
    function _handleAmount()
    {
        $this->_data['trnAmount'] = $this->amount = $this->amount*100;
    }

    /**
     * _handleProcessDate
     *
     * We need to make sure process date is in format YYYYMMDD and if its after 11AM it must be the next day.
     *
     * @access private
     */
    function _handleProcessDate()
    {
        if ( isset($this->processDate) ) {
            $epoch = (int)$this->processDate;
        } else {
            $this->processDate = FALSE;
            $epoch = time();
        }

        if ( $epoch < time() ) {
            $epoch = time();
        }

        $cutoff_hour = date('G', $epoch );
        if ( $cutoff_hour > 10 ) { // 11AM
            //Process date is next day
            $epoch += 86400;
        } else {
            //Process date can be today.
        }

        $this->processDate = $this->_data['processDate'] = date('Ymd', $epoch );
    }
}


class Payment_Process_Result_BeanstreamEFT extends Payment_Process_Result {

    var $_statusCodeMap = array('1' => PAYMENT_PROCESS_RESULT_APPROVED,
                                '2' => PAYMENT_PROCESS_RESULT_OTHER,
                                '3' => PAYMENT_PROCESS_RESULT_OTHER,
                                '4' => PAYMENT_PROCESS_RESULT_REVIEW,
                                '5' => PAYMENT_PROCESS_RESULT_REVIEW,
                                '6' => PAYMENT_PROCESS_RESULT_REVIEW,
                                '7' => PAYMENT_PROCESS_RESULT_DECLINED,
                                '8' => PAYMENT_PROCESS_RESULT_REVIEW,
                                '9' => PAYMENT_PROCESS_RESULT_REVIEW,
                                '10' => PAYMENT_PROCESS_RESULT_REVIEW,
                                '11' => PAYMENT_PROCESS_RESULT_REVIEW,
                                '12' => PAYMENT_PROCESS_RESULT_REVIEW,
                                '13' => PAYMENT_PROCESS_RESULT_REVIEW,
                                );

    var $_statusCodeMessages = array(
        '1' => 'File successfully received',
        '2' => 'Secure connection required',
        '3' => 'Service version not supported',
        '4' => 'Invalid login credentials',
        '5' => 'Insufficient user permissions',
        '6' => 'Batch Processing service not enabled',
        '7' => 'Invalid processing date',
        '8' => 'Service is busy importing another file. Try again later',
        '9' => 'File greater than maximum allowable size',
        '10' => 'Unexpected error',
        '11' => 'No batch file received in request',
        '12' => 'Merchant account status cannot be Disabled or Closed for operation',
        '13' => 'Upload rejected. File name is limited to 32 characters in length, including file type extension',
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

    function Payment_Process_Response_BeanstreamEFT($rawResponse)
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
        if ( preg_match('/<code>([\d]+)<\/code>/i', $this->_rawResponse, $code_match ) ) {
            if ( isset($code_match[1]) ) {
                $code = (int)$code_match[1];

                $this->code          = $code;
                $this->messageCode   = $code;

                if ( $code == 1 ) {
                    $this->_returnCode = PAYMENT_PROCESS_RESULT_APPROVED;

                    if ( preg_match('/<batch_id>(.*)<\/batch_id>/i', $this->_rawResponse, $batch_id_match ) ) {
                        $this->approvalCode = $this->transactionId = $batch_id_match[1];
                    }
                } else {
                    $this->_returnCode = PAYMENT_PROCESS_RESULT_DECLINED;
                }

                if ( preg_match('/<message>(.*)<\/message>/i', $this->_rawResponse, $message_match ) ) {
                    $this->message = $message_match[1] .' (Code: '. $code .')';
                }

            } else {
                $this->_returnCode = PAYMENT_PROCESS_RESULT_OTHER;
                $this->message = 'Error parsing response.';
            }
        } else {
            $this->_returnCode = PAYMENT_PROCESS_RESULT_OTHER;
            $this->message = 'Error parsing response.';
        }
    }
}
?>