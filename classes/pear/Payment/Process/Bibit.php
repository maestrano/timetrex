<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at                              |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Robin Ericsson <lobbin@localhost.nu>                        |
// +----------------------------------------------------------------------+
//
// $Id: Bibit.php,v 1.6 2005/07/28 02:52:58 jstump Exp $

require_once('Payment/Process.php');
require_once('Payment/Process/Common.php');
require_once('Net/Curl.php');
require_once('XML/Util.php');
require_once('XML/XPath.php');

define('PAYMENT_PROCESS_ACTION_BIBIT_AUTH', 300);
define('PAYMENT_PROCESS_ACTION_BIBIT_REDIRECT', 400);
define('PAYMENT_PROCESS_ACTION_BIBIT_REFUND', 500);
define('PAYMENT_PROCESS_ACTION_BIBIT_CAPTURE', 600);

// Map actions
$GLOBALS['_Payment_Process_Bibit'] = array(
    PAYMENT_PROCESS_ACTION_NORMAL   => PAYMENT_PROCESS_ACTION_BIBIT_REDIRECT,
    PAYMENT_PROCESS_ACTION_AUTHONLY => PAYMENT_PROCESS_ACTION_BIBIT_AUTH,
    PAYMENT_PROCESS_ACTION_CREDIT   => PAYMENT_PROCESS_ACTION_BIBIT_REFUND,
    PAYMENT_PROCESS_ACTION_SETTLE   => PAYMENT_PROCESS_ACTION_BIBIT_CAPTURE,
);

/**
 * Payment_Process_Bibit
 *
 * This is a process for Bibit's merchant payment gateway.
 * (http://www.bibit.com)
 *
 * *** WARNING ***
 * This is BETA code, and hos not been fully tested. It is not recommended
 * that you use it in a production environment without further testing.
 *
 * @package Payment_Process
 * @author Robin Ericsson <lobbin@localhost.nu>
 * @version @version@
 */
class Payment_Process_Bibit extends Payment_Process_Common {
    /**
     * Front-end -> back-end field map.
     *
     * This array contains the mapping from front-end fields (defined in
     * the Payment_Process class) to the field names Bibit requires.
     *
     * @see _prepare()
     * @access private
     */
    var $_fieldMap = array(
        // Required
        'login' => 'x_login',
        'password' => 'x_password',
        'ordercode' => 'x_ordercode',
        'description' => 'x_descr',
        'amount' => 'x_amount',
        'currency' => 'x_currency',
        'exponent' => 'x_exponent',
        'action' => 'x_action',
        // Optional
        'ordercontent' => 'x_ordercontent',
        'shopper_ip_address' => 'shopperIPAddress',
        'shopper_email_address' => 'shopperEmailAddress',
        'session_id' => 'sessionId',
        'authenticated_shopper_id' => 'authenticatedShopperID',
        'shipping_address' => 'shippingAddress',
        'payment_method_mask' => 'paymentMethodMask',
    );

    /**
     * Default options for this processor.
     *
     * @see Payment_Process::setOptions()
     * @access private
     */
    var $_defaultOptions = array(
        'authorizeUri' => 'https://secure.bibit.com/jsp/merchant/xml/paymentService.jsp',
        'authorizeTestUri' => 'https://secure-test.bibit.com/jsp/merchant/xml/paymentService.jsp',
        'x_version' => '1.4'
    );

    /**
     * The reponse body sent back from the gateway.
     *
     * @access private
     */
    var $_responseBody = '';

    /**
     * The orders unique code
     *
     * @access private
     */
    var $ordercode = '';

    /**
     * The order amounts currency
     *
     * @access private
     */
    var $currency = '';

    /**
     * The order amounts exponent
     * 
     * @access private
     */
    var $exponent = 0;

    /**
     * The orders content as displayed at bibit
     *
     * @access private
     */
    var $ordercontent = '';

    /**
     * The ip-address the order comes from
     *
     * @access private
     */
    var $shopper_ip_address;

    /**
     * The shoppers email-address
     *
     * @access private
     */
    var $shopper_email_address;
    
    /**
     * The unique id of the users session
     *
     * @access private
     */
    var $session_id;
    
    /**
     * Unique id of the authenticed shopper
     *
     * @access private
     */
    var $authenticated_shopper_id;
    
    /**
     * Shipping address
     *
     * @access private
     */
    var $shipping_address = array();
   
    /**
     * Payment method mask
     *
     * @access private
     */
    var $payment_method_mask = array();
   
    /**
     * $_typeFieldMap
     *
     * @access protected
     */
    var $_typeFieldMap = array(
        'CreditCard' => array(
            'cvv' => 'x_card_code',
            'expDate' => 'x_exp_date',
            'cardNumber' => 'x_card_num',
        )
    );

    /**
     * Constructor.
     *
     * @param array $options Class options to set.
     * @see Payment_Process::setOptions()
     * @return void
     */
    function __construct($options = false)
    {
        parent::__construct();
        $this->_driver = 'Bibit';
        $this->_makeRequired('login', 'password', 'ordercode', 'description', 'amount', 'currency', 'exponent', 'cardNumber', 'expDate', 'action');

    }

    function Payment_Process_Bibit($options = false)
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
        $curl = new Net_Curl(isset($this->_options['live']) ? $this->_options['authorizeUri'] : $this->_options['authorizeTestUri']);
        if (PEAR::isError($curl)) {
            PEAR::popErrorHandling();
            return $curl;
        }

        $curl->type = 'PUT';
        $curl->fields = $fields;
        $curl->userAgent = 'PEAR Payment_Process_Bibit 0.1';
        $curl->username = $this->_data['x_login'];
        $curl->password = $this->_data['x_password'];

        $result = &$curl->execute();
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

        $response = &Payment_Process_Result::factory($this->_driver, 
                                                     $this->_responseBody,
                                                     $this);
        if (!PEAR::isError($response)) {
            $response->parse();
        }

        return $response;
    }

    /**
     * Prepare the PUT query xml.
     *
     * @access private
     * @return string The query xml
     */
    function _prepareQueryString()
    {  
        $data = array_merge($this->_options,$this->_data);

        $doc = XML_Util::getXMLDeclaration();
        $doc .= '<!DOCTYPE paymentService PUBLIC "-//Bibit//DTD Bibit PaymentService v1//EN" "http://dtd.bibit.com/paymentService_v1.dtd">';

        $doc .= XML_Util::createStartElement('paymentService', array('version' =>  $data['x_version'], 'merchantCode' => $data['x_login']));
        if ($data['x_action'] == PAYMENT_PROCESS_ACTION_BIBIT_CAPTURE || $data['x_action'] == PAYMENT_PROCESS_ACTION_BIBIT_REFUND) {
            $doc .= XML_Util::createStartElement('modify');
            $doc .= XML_Util::createStartElement('orderModification', array('orderCode' => $data['x_ordercode']));
            if ($data['x_action'] == PAYMENT_PROCESS_ACTION_BIBIT_CAPTURE) {
                $doc .= XML_Util::createStartElement('capture');
                
                $d = array();
                $t = time() - 86400;
                $d['dayOfMonth'] = date('d', $t);
                $d['month'] = date('m', $t);
                $d['year'] = date('Y', $t);
                $d['hour'] = date('H', $t);
                $d['minute'] = date('i', $t);
                $d['second'] = date('s', $t);
                $doc .= XML_Util::createTag('date', $d);
                $doc .= XML_Util::createTag('amount', array('value' => $data['x_amount'],
                                                            'currencyCode' => $data['x_currency'],
                                                            'exponent' => $data['x_exponent']));

                $doc .= XML_Util::createEndElement('capture');
            } else if ($data['x_action'] == PAYMENT_PROCESS_ACTION_BIBIT_REFUND) {
                $doc .= XML_Util::createStartElement('refund');
                $doc .= XML_Util::createTag('amount', array('value' => $data['x_amount'],
                                                            'currencyCode' => $data['x_currency'],
                                                            'exponent' => $data['x_exponent']));
                $doc .= XML_Util::createEndElement('refund');
            }

            $doc .= XML_Util::createEndElement('orderModification');
            $doc .= XML_Util::createEndElement('modify');
        } else {
            $doc .= XML_Util::createStartElement('submit');
            $doc .= XML_Util::createStartElement('order', array('orderCode' => $data['x_ordercode']));
            
            $doc .= XML_Util::createTag('description', null, $data['x_descr']);
            $doc .= XML_Util::createTag('amount', array('value' => $data['x_amount'],
                                                        'currencyCode' => $data['x_currency'],
                                                        'exponent' => $data['x_exponent']));
            if (isset($data['x_ordercontent'])) {
                $doc .= XML_Util::createStartElement('orderContent');
                $doc .= XML_Util::createCDataSection($data['x_ordercontent']);
                $doc .= XML_Util::createEndElement('orderContent');
            }
         
            if ($data['x_action'] == PAYMENT_PROCESS_ACTION_BIBIT_REDIRECT) {
                if (is_array($data['paymentMethodMask']) && count($data['paymentMethodMask'] > 0)) {
                    $doc .= XML_Util::createStartElement('paymentMethodMask');
                    foreach($data['paymentMethodMask']['include'] as $code) {
                        $doc .= XML_Util::createTag('include', array('code' => $code));
                    }
                    foreach($data['paymentMethodMask']['exclude'] as $code) {
                        $doc .= XML_Util::createTag('exclude', array('code' => $code));
                    }
                    $doc .= XML_Util::createEndElement('paymentMethodMask');
                }
            } else if ($data['x_action'] == PAYMENT_PROCESS_ACTION_BIBIT_AUTH) {
                $doc .= XML_Util::createStartElement('paymentDetails');
                switch ($this->_payment->type) {
                    case PAYMENT_PROCESS_CC_VISA:       $cc_type = 'VISA-SSL'; break;
                    case PAYMENT_PROCESS_CC_MASTERCARD: $cc_type = 'ECMC-SSL'; break;
                    case PAYMENT_PROCESS_CC_AMEX:       $cc_type = 'AMEX-SSL'; break;
                }

                $doc .= XML_Util::createStartElement($cc_type);
                if (isset($data['x_card_num'])) {
                    $doc .= XML_Util::createTag('cardNumber', null, $data['x_card_num']);
                }
                if (isset($data['x_exp_date'])) {
                    $doc .= XML_Util::createStartElement('expiryDate');
                    $doc .= XML_Util::createTag('date', array('month' => substr($data['x_exp_date'], 0, 2),
                                                              'year' => substr($data['x_exp_date'], 3, 4)));
                    $doc .= XML_Util::createEndElement('expiryDate');
                }
                if (isset($this->_payment->firstName) &&
                    isset($this->_payment->lastName)) {
                    $doc .= XML_Util::createTag('cardHolderName', null, $this->_payment->firstName.' '.$this->_payment->lastName);
                }
                if (isset($data['x_card_code'])) {
                    $doc .= XML_Util::createTag('cvc', null, $data['x_card_code']);
                }
              
                $doc .= XML_Util::createEndElement($cc_type);
                
                if ((isset($data['shopperIPAddress']) || isset($data['sessionId']))
                &&  ($data['shopperIPAddress'] != ''  || $data['sessionId'] != '')) {
                    $t = array();
                    if ($data['shopperIPAddress'] != '') {
                        $t['shopperIPAddress'] = $data['shopperIPAddress'];
                    }
                    if ($data['sessionId'] != '') {
                        $t['id'] = $data['sessionId'];
                    }

                    $doc .= XML_Util::createTag('session', $t);
                    unset($t);
                }
              
                $doc .= XML_Util::createEndElement('paymentDetails');
            }
        
            if ((isset($data['shopperEmailAddress'])    && $data['shopperEmailAddress'] != '') 
            ||  (isset($data['authenticatedShopperID']) && $data['authenticatedShopperID'] != '')) {
                $doc .= XML_Util::createStartElement('shopper');
                
                if ($data['shopperEmailAddress'] != '') {
                    $doc .= XML_Util::createTag('shopperEmailAddress', null, $data['shopperEmailAddress']);
                }
                if ($data['authenticatedShopperID'] != '') {
                    $doc .= XML_Util::createTag('authenticatedShopperID', null, $data['authenticatedShopperID']);
                }

                $doc .= XML_Util::createEndElement('shopper');
            }
       
            if (is_array($data['shippingAddress']) && count($data['shippingAddress']) > 0) {
                $a = $data['shippingAddress'];
                
                $doc .= XML_Util::createStartElement('shippingAddress');
                $doc .= XML_Util::createStartElement('address');

                $fields = array('firstName',    'lastName',     'street',
                                'houseName',    'houseNumber',  'houseNumberExtension',
                                'postalCode',   'city',         'state',
                                'countryCode',  'telephoneNumber');

                foreach($fields as $field) {
                    if (isset($a[$field])) {
                        $doc .= XML_Util::createTag($field, null, $a[$field]);
                    }
                }
               
                $doc .= XML_Util::createEndElement('address');
                $doc .= XML_Util::createEndElement('shippingAddress');
            }
       
            $doc .= XML_Util::createEndElement('order');
            $doc .= XML_Util::createEndElement('submit');
        }
        $doc .= XML_Util::createEndElement('paymentService');

        $doc1 = domxml_open_mem($doc);

        return $doc;
    }

    /**
     * Prepare the ordercontent
     *
     * Docs says max size is 10k 
     *
     * @access private
     */
    function _handleOrdercontent()
    {
        $specific = $this->_fieldMap['ordercontent'];
        if ($this->ordercontent != '') {
            $this->_data[$specific] = substr($this->ordercontent, 0, 10240);
        }
    }

    /**
     * Validate the merchant account login.
     *
     * @access private
     * @return boolean true if valid, false otherwise
     */
    function _validateLogin()
    {
        return Validate::string($this->login, array(
            'format' => VALIDATE_ALPHA_UPPER,
            'min_length' => 1
        ));
    }

    /**
     * Validate the merchant account password.
     *
     * @access private
     * @return boolean true if valid, false otherwise
     */
    function _validatePassword()
    {
        return Validate::string($this->password, array(
            'min_length' => 1
        ));
    }

    /**
     * Validates the ordercode
     *
     * Docs says up to 64 characters, no spaces or specials characters allowed
     *
     * @access private
     * @return boolean true if valid, false otherwise
     */
    function _validateOrdercode()
    {
        return Validate::string($this->ordercode, array(
            'min_length' => 1,
            'max_length' => 64
        ));
    }

    /**
     * Validate the order description.
     *
     * Docs says maximum length is 50 characters...
     *
     * @access private
     * @return boolean true if valid, false otherwise
     */
    function _validateDescription()
    {
        return Validate::string($this->description, array(
            'min_length' => 1,
            'max_length' => 50,
        ));
    }

    /**
     * Validate the order amount.
     *
     * Should contain no digits, as those are set with the exponent option.
     *
     * @access private
     * @return boolean true if valid, false otherwise
     */
    function _validateAmount()
    {
        return Validate::number($this->amount, array(
            'decimal' => false
        ));
    }

    /** Validate the order amount currency
     *
     * The abbrivation for a currency, usually 2-3 chars
     *
     * @access private
     * @return boolean true if valid, false otherwise
     */
    function _validateCurrency()
    {
        return Validate::string($this->currency, array(
            'format' => VALIDATE_ALPHA_UPPER,
            'min_length' => 2,
            'max_length' => 3
        ));
    }

    /** Validate the exponent of the order amount
     *
     * Occording to the dtd, valid is 0, 2 or 3
     *
     * @access private
     * @return boolean true if valid, false otherwise
     */
    function _validateExponent()
    {
        switch ($this->exponent)
        {
            case 0:
            case 2:
            case 3:
                return true;
            default:
                return false;
        }
    }
}

/**
 * Payment_Process_Bibit_Result
 *
 *
 * @package Payment_Process
 * @author Robin Ericsson <lobbin@localhost.nu>
 * @version @version@
 */
class Payment_Process_Result_Bibit extends Payment_Process_Result
{
    var $_returnCode = PAYMENT_PROCESS_RESULT_DECLINED;

    var $_lastEvent = NULL;
    
    var $_fieldMap = array(
    );

    function Payment_Process_Result_Bibit($rawResponse)
    {
        $this->_rawResponse = $rawResponse;
    }

    function getErrorCode()
    {
        return $this->_errorCode;
    }

    function getCode()
    {
        return $this->_returnCode;
    }

    function parse()
    {
        $doc = new XML_XPath();

        $e = $doc->load($this->_rawResponse, 'string');
        if (PEAR::isError($e)) {
            $this->_returnCode = PAYMENT_PROCESS_RESULT_OTHER;
            $this->message = 'Error parsing reply: '.$e->getMessage()."\n";
            return;
        }

        $e = $doc->evaluate('//reply/error/attribute::code');
        if (!PEAR::isError($e) && $e->next()) {
            $this->_returnCode = PAYMENT_PROCESS_RESULT_OTHER;
            $this->_errorCode = $e->getData();
            
            $e = $doc->evaluate('//reply/error/text()');
            $this->message = $e->getData();
            return;
        }
        
        $orderType = $this->_request->_data['x_action'];
        switch ($orderType) {
        case PAYMENT_PROCESS_ACTION_BIBIT_AUTH:
            $e = $doc->evaluate('//reply/orderStatus/payment/lastEvent/text()');
            if (!PEAR::isError($e) && $e->next()) {
                $this->_lastEvent = $e->getData();
            }
        
            $amount = $doc->evaluate('//reply/orderStatus/payment/amount/attribute::value');
            if (!PEAR::isError($amount) && $amount->next()) {
               if ($this->_lastEvent == 'AUTHORISED') {
                    $this->_returnCode = PAYMENT_PROCESS_RESULT_APPROVED;
                    $this->message = '';
                    return;
                }
            }

            break;
        case PAYMENT_PROCESS_ACTION_BIBIT_CAPTURE:
            $amount = $doc->evaluate('//reply/ok/captureReceived/amount/attribute::value');
            if (!PEAR::isError($amount) && $amount->next()) {
                $this->_returnCode = PAYMENT_PROCESS_RESULT_APPROVED;
                return;
            }

            break;
        case PAYMENT_PROCESS_ACTION_BIBIT_REFUND:
            $amount = $doc->evaluate('//reply/ok/refundReceived/amount/attribute::value');
            if (!PEAR::isError($amount) && $amount->next()) {
                $this->_returnCode = PAYMENT_PROCESS_RESULT_APPROVED;
                return;
            }
            break;
        }
    }
}

?>
