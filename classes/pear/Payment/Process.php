<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Main package file
 *
 * Process.php is a unified OOP abstraction layer for credit card and echeck
 * processing gateways (similar to what DB does for database calls).
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
 * @author     Ian Eure <ieure@php.net>
 * @author     Joe Stump <joe@joestump.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Process.php,v 1.49 2005/12/16 18:45:57 ieure Exp $
 * @link       http://pear.php.net/package/Payment_Process
 */

require_once('PEAR.php');
require_once('Validate.php');
require_once('Validate/Finance/CreditCard.php');
require_once('Payment/Process/Type.php');

/**
 * Error codes
 */
define('PAYMENT_PROCESS_ERROR_NOTIMPLEMENTED', -100);
define('PAYMENT_PROCESS_ERROR_NOFIELD',        -101);
define('PAYMENT_PROCESS_ERROR_NOPROCESSOR',    -102);
define('PAYMENT_PROCESS_ERROR_INCOMPLETE', -1);
define('PAYMENT_PROCESS_ERROR_INVALID',    -2);
define('PAYMENT_PROCESS_ERROR_AVS',        -3);
define('PAYMENT_PROCESS_ERROR_CVV',        -4);

/**
 * Transaction actions
 */
/**
 * A normal transaction
 */
define('PAYMENT_PROCESS_ACTION_NORMAL',   200);

/**
 * Authorize only. No funds are transferred.
 */
define('PAYMENT_PROCESS_ACTION_AUTHONLY', 201);

/**
 * Credit funds back from a previously-charged transaction.
 */
define('PAYMENT_PROCESS_ACTION_CREDIT',   202);

/**
 * Post-authorize an AUTHONLY transaction.
 */
define('PAYMENT_PROCESS_ACTION_POSTAUTH', 203);

/**
 * Clear a previous transaction
 */
define('PAYMENT_PROCESS_ACTION_VOID',     204);

/**
 * Transaction sources
 */
define('PAYMENT_PROCESS_SOURCE_POS',      300);
define('PAYMENT_PROCESS_SOURCE_ONLINE',   301);

/**
 * Result codes
 */
define('PAYMENT_PROCESS_RESULT_APPROVED', 400);
define('PAYMENT_PROCESS_RESULT_DECLINED', 401);
define('PAYMENT_PROCESS_RESULT_OTHER',    402);
define('PAYMENT_PROCESS_RESULT_FRAUD',    403);
define('PAYMENT_PROCESS_RESULT_DUPLICATE',404);
define('PAYMENT_PROCESS_RESULT_REVIEW',   405);

define('PAYMENT_PROCESS_AVS_MATCH',       500);
define('PAYMENT_PROCESS_AVS_MISMATCH',    501);
define('PAYMENT_PROCESS_AVS_ERROR',       502);
define('PAYMENT_PROCESS_AVS_NOAPPLY',     503);

define('PAYMENT_PROCESS_CVV_MATCH',       600);
define('PAYMENT_PROCESS_CVV_MISMATCH',    601);
define('PAYMENT_PROCESS_CVV_ERROR',       602);
define('PAYMENT_PROCESS_CVV_NOAPPLY',     603);

/**
 * Payment_Process
 *
 * @author Ian Eure <ieure@php.net>
 * @package Payment_Process
 * @category Payment
 * @version @version@
 */
class Payment_Process {
    /**
     * Return an instance of a specific processor.
     *
     * @param  string  $type     Name of the processor
     * @param  array   $options  Options for the processor
     * @return mixed Instance of the processor object, or a PEAR_Error object.
     */
	// static function,avoid PHP strict error
    static function &factory($type, $options = false)
    {
        $class = "Payment_Process_".$type;
        if (include_once "Payment/Process/{$type}.php") {
            if (class_exists($class)) {
                $object = new $class($options);
                return $object;
            }
        }

        $ret = PEAR::raiseError('"'.$type.'" processor does not exist',
                                PAYMENT_PROCESS_ERROR_NOPROCESSOR);
        return $ret;

    }

    /**
     * Determine if a field is required.
     *
     * @param  string $field Field to check
     * @return boolean true if required, false if optional.
     */
    function isRequired($field)
    {
        return (isset($this->_required[$field]));
    }

    /**
     * Determines if a field exists.
     *
     * @author Ian Eure <ieure@php.net>
     * @param  string  $field  Field to check
     * @return boolean true if field exists, false otherwise
     */
    function fieldExists($field)
    {
        return @in_array($field, $this->getFields());
    }

    /**
     * Get a list of fields.
     *
     * This function returns an array containing all the possible fields which
     * may be set.
     *
     * @author Ian Eure <ieure@php.net>
     * @access public
     * @return array Array of valid fields.
     */
    function getFields()
    {
        $vars = array_keys(get_class_vars(get_class($this)));
        foreach ($vars as $idx => $field) {
            if ($field{0} == '_') {
                unset($vars[$idx]);
            }
        }

        return $vars;
    }

    /**
     * Set class options.
     *
     * @author Ian Eure <ieure@php.net>
     * @param  Array  $options         Options to set
     * @param  Array  $defaultOptions  Default options
     * @return void
     */
    function setOptions($options = false, $defaultOptions = false)
    {
        $defaultOptions = $defaultOptions ? $defaultOptions : $this->_defaultOptions;
        $this->_options = @array_merge($defaultOptions, $options);
    }

    /**
     * Get an option value.
     *
     * @author Ian Eure <ieure@php.net>
     * @param  string  $option  Option to get
     * @return mixed   Option value
     */
    function getOption($option)
    {
        return @$this->_options[$option];
    }

    /**
     * Set an option value
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @param  string  $option  Option name to set
     * @param  mixed   $value   Value to set
     */
    function setOption($option,$value)
    {
        return ($this->_options[$option] = $value);
    }

    /**
     * See if a value is a defined constant.
     *
     * This function checks to see if $value is defined in one of
     * PAYMENT_PROCESS_{$class}_*. It's used to verify that e.g.
     * $object->action is one of PAYMENT_PROCESS_ACTION_NORMAL,
     * PAYMENT_PROCESS_ACTION_AUTHONLY etc.
     *
     * @access private
     * @param  mixed    $value  Value to check
     * @param  mixed    $class  Constant class to check
     * @return boolean  true if it is defined, false otherwise.
     */
    function _isDefinedConst($value, $class)
    {
        $constClass = 'PAYMENT_PROCESS_'.strtoupper($class).'_';
        $length = strlen($constClass);
        $consts = get_defined_constants();
        $found = false;
        foreach ($consts as $constant => $constVal) {
            if (strncmp($constClass, $constant, $length) === 0
                  && $constVal == $value) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    /**
     * Statically check a Payment_Result class for success
     *
     * @param  mixed  $obj
     * @return bool
     * @access public
     * @static
     * @author Joe Stump <joe@joestump.net>
     */
    function isSuccess($obj)
    {
        if (is_a($obj, 'Payment_Process_Result')) {
            if ($obj->getCode() == PAYMENT_PROCESS_RESULT_APPROVED) {
                return true;
            }
        }

        return false;
    }

    /**
     * Statically check a Payment_Result class for error
     *
     * @param  mixed  $obj
     * @return bool
     * @access public
     * @author Joe Stump <joe@joestump.net>
     */
    function isError($obj)
    {
        if (PEAR::isError($obj)) {
            return true;
        }

        if (is_a($obj, 'Payment_Process_Result')) {
            if ($obj->getCode() != PAYMENT_PROCESS_RESULT_APPROVED) {
                return true;
            }
        }

        return false;
    }
}

/**
 * Payment_Process_Result
 *
 * The core result class that should be returned from each driver's process()
 * function. This should be extended as Payment_Process_Result_DriverName and
 * then have the appropriate fields mapped out accordingly.
 *
 * Take special care to appropriately create a parse() function in your result
 * class. You can then call _mapFields() with a resultArray (ie. exploded
 * result) to map your results from parse() into the member variables.
 *
 * Please note that this class keeps your original codes intact so they can
 * be accessed directly and then uses the function wrappers to return uniform
 * Payment_Process codes.
 *
 * @author Joe Stump <joe@joestump.net>
 * @package Payment_Process
 * @category Payment
 * @version @version@
 */
class Payment_Process_Result {
    /**
     * Processor instance which this result was instantiated from.
     *
     * This should contain a reference to the requesting Processor.
     *
     * @author Ian Eure <ieure@php.net>
     * @access private
     * @var    Object
     */
    var $_request;

    /**
     * The raw response (ie. from cURL)
     *
     * @author Joe Stump <joe@joestump.net>
     * @access protected
     * @var    string  $_rawResponse
     */
    var $_rawResponse = null;

    /**
     * The approval/decline code
     *
     * The value returned by your gateway as approved/declined should be mapped
     * into this variable. Valid results should then be mapped into the
     * appropriate PAYMENT_PROCESS_RESULT_* code using the $_statusCodeMap
     * array. Values returned into $code should be mapped as keys in the map
     * with PAYMENT_PROCESS_RESULT_* as the values.
     *
     * @author  Joe Stump <joe@joestump.net>
     * @access  public
     * @var     mixed  $code
     * @see    PAYMENT_PROCESS_RESULT_APPROVED, PAYMENT_PROCESS_RESULT_DECLINED
     * @see    PAYMENT_PROCESS_RESULT_OTHER, $_statusCodeMap
     */
    var $code;

    /**
     * Message/Response Code
     *
     * Along with the response (yes/no) you usually get a response/message
     * code that translates into why it was approved/declined. This is where
     * you map that code into. Your $_statusCodeMessages would then be keyed by
     * valid messageCode values.
     *
     * @author  Joe Stump <joe@joestump.net>
     * @access  public
     * @var     mixed  $messageCode
     * @see     $_statusCodeMessages
     */
    var $messageCode;

    /**
     * Message from gateway
     *
     * Map the textual message from the gateway into this variable. It is not
     * currently returned or used (in favor of the $_statusCodeMessages map, but
     * can be accessed directly for debugging purposes.
     *
     * @author  Joe Stump <joe@joestump.net>
     * @access  public
     * @var     string   $message
     * @see     $_statusCodeMessages
     */
    var $message = 'No message from gateway';

    /**
     * Authorization/Approval code
     *
     * @author  Joe Stump <joe@joestump.net>
     * @access  public
     * @var     string  $approvalCode
     */
    var $approvalCode;

    /**
     * Address verification code
     *
     * The AVS code returned from your gateway. This should then be mapped to
     * the appropriate PAYMENT_PROCESS_AVS_* code using $_avsCodeMap. This value
     * should also be mapped to the appropriate textual message via the
     * $_avsCodeMessages array.
     *
     * @author  Joe Stump <joe@joestump.net>
     * @access  public
     * @var     string  $avsCode
     * @see     PAYMENT_PROCESS_AVS_MISMATCH, PAYMENT_PROCESS_AVS_ERROR
     * @see     PAYMENT_PROCESS_AVS_MATCH, PAYMENT_PROCESS_AVS_NOAPPLY, $_avsCodeMap
     * @see     $_avsCodeMessages
     */
    var $avsCode;

    /**
     * Transaction ID
     *
     * This is the unique transaction ID, which is used by gateways to modify
     * transactions (credit, update, etc.). Map the appropriate value into this
     * variable.
     *
     * @author  Joe Stump <joe@joestump.net>
     * @access  public
     * @var     string $transactionId
     */
    var $transactionId;

    /**
     * Invoice Number
     *
     * Unique internal invoiceNumber (ie. your company's order/invoice number
     * that you assign each order as it is processed). It is always a good idea
     * to pass this to the gateway (which is usually then echo'd back).
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @var string $invoiceNumber
     */
    var $invoiceNumber;

    /**
     * Customer ID
     *
     * Unique internal customer ID (ie. your company's customer ID used to
     * track individual customers).
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @var string $customerId
     */
    var $customerId;

    /**
     * CVV Code
     *
     * The CVV code is the 3-4 digit number on the back of most credit cards.
     * This value should be mapped via the $_cvvCodeMap variable to the
     * appropriate PAYMENT_PROCESS_CVV_* values.
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @var string $cvvCode
     */
    var $cvvCode = PAYMENT_PROCESS_CVV_NOAPPLY;

    /**
     * CVV Message
     *
     * Your cvvCode value should be mapped to appropriate messages via the
     * $_cvvCodeMessage array. This value is merely here to hold the value
     * returned from the gateway (if any).
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @var string $cvvMessage
     */
    var $cvvMessage = 'No CVV message from gateway';

    function Payment_Process_Result($rawResponse, $request)
    {
        $this->_rawResponse = $rawResponse;
        $this->_request = $request;
    }

    /**
    * factory
    *
    * @author Joe Stump <joe@joestump.net>
    * @author Ian Eure <ieure@php.net>
    * @param string $type
    * @param string $rawResponse
    * @param mixed $request
    * @return mixed Payment_Process_Result on succes, PEAR_Error on failure
    */
    static function &factory($type, $rawResponse, $request)
    {
        $class = 'Payment_Process_Result_'.$type;
        if (class_exists($class)) {
            $ret = new $class($rawResponse, $request);
            return $ret;
        }

        $ret = PEAR::raiseError('Invalid response type: '.$type.'('.$class.')');
        return $ret;
    }

    /**
     * validate
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @return mixed
     */
    function validate()
    {
        if ($this->_request->getOption('avsCheck') === true) {
            if ($this->getAVSCode() != PAYMENT_PROCESS_AVS_MATCH) {
                return PEAR::raiseError('AVS check failed',
                                        PAYMENT_PROCESS_ERROR_AVS);
            }
        }

        $paymentType = $this->_request->_payment->_type;
        if ($this->_request->getOption('cvvCheck') === true &&
            $paymentType == 'CreditCard') {

            if ($this->getCvvCode() != PAYMENT_PROCESS_CVV_MATCH) {
                return PEAR::raiseError('CVV check failed',
                                        PAYMENT_PROCESS_ERROR_CVV);
            }

        }

        if ($this->getCode() != PAYMENT_PROCESS_RESULT_APPROVED) {
            return PEAR::raiseError($this->getMessage(),
                                    PAYMENT_PROCESS_RESULT_DECLINED);
        }

        return true;
    }

    /**
     * parse
     *
     * @abstract
     * @author Joe Stump <joe@joestump.net>
     * @access public
     */
    function parse()
    {
        return PEAR::raiseError('parse() not implemented',
                                PAYMENT_PROCESS_ERROR_NOTIMPLEMENTED);
    }

    /**
     * parseCallback
     *
     * @abstract
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @see Payment_Process_Common::processCallback()
     */
    function parseCallback()
    {
        return PEAR::raiseError('parse() not implemented',
                                PAYMENT_PROCESS_ERROR_NOTIMPLEMENTED);
    }

    /**
     * isLegitimate
     *
     * Some gateways allow you to validate the response to make sure it's
     * actually them that are sending the response. Override this in the
     * driver result class and implement this if your gateway provides such
     * a mechanism. Any information required should be passed via $options.
     *
     * @abstract
     * @author Joe Stump <joe@joestump.net>
     * @access public
     */
    function isLegitimate()
    {
        return PEAR::raiseError('parse() not implemented',
                                PAYMENT_PROCESS_ERROR_NOTIMPLEMENTED);
    }

    /**
     * getCode
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     */
    function getCode()
    {
        if (isset($this->_statusCodeMap[$this->code])) {
            return $this->_statusCodeMap[$this->code];
        } else {
            return PAYMENT_PROCESS_RESULT_DECLINED;
        }
    }

    /**
     * getMessage
     *
     * Return the message from the code map, or return the raw message if
     * there is one. Otherwise, return a worthless message.
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @return string
     */
    function getMessage()
    {
        if (isset($this->_statusCodeMessages[$this->messageCode])) {
            return $this->_statusCodeMessages[$this->messageCode];
        } elseif(strlen($this->message)) {
            return $this->message;
        } else {
            return 'No message reported';
        }
    }

    function getAVSCode()
    {
        return $this->_avsCodeMap[$this->avsCode];
    }

    function getAVSMessage()
    {
        return $this->_avsCodeMessages[$this->avsCode];
    }

    function getCvvCode()
    {
        return $this->_cvvCodeMap[$this->cvvCode];
    }

    function getCvvMessage()
    {
        return $this->_cvvCodeMessages[$this->cvvCode];
    }

    /**
     * _mapFields
     *
     * @author Joe Stump <joe@joestump.net>
     * @access private
     * @param mixed $responseArray
     */
    function _mapFields($responseArray) {
        foreach($this->_fieldMap as $key => $val) {
            $this->$val = $responseArray[$key];
        }
    }

    /**
     * Accept an object
     *
     * @param   object   $object  Object to accept
     * @return  boolean  true if accepted, false otherwise
     */
    function accept(&$object)
    {
        if (is_a($object, 'Log')) {
            $this->_log = $object;
            return true;
        }
        return false;
    }

    /**
     * Log a message
     *
     * @param   string  $message   Message to log
     * @param   string  $priority  Message priority
     * @return  mixed   Return value of Log::log(), or false if no Log instance
     *                  has been accepted.
     */
    function log($message, $priority = null)
    {
        if (isset($this->_log) && is_object($this->_log)) {
            return $this->_log->log($message, $priority);
        }
        return false;
    }
}

?>
