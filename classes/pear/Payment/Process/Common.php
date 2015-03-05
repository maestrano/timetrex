<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Holds code shared between all processors
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
 * @version    CVS: $Id: Common.php,v 1.32 2005/12/13 00:00:29 jstump Exp $
 * @link       http://pear.php.net/package/Payment_Process
 */

require_once('Payment/Process.php');
require_once('Payment/Process/Type.php');

class Payment_Process_Common {
    // {{{ Private Properties
    /**
     * Options.
     *
     * @var array
     * @see setOptions()
     * @access private;
     */
    var $_options = '';

    /**
     * Array of fields which are required.
     *
     * @var array
     * @access private
     * @see _makeRequired()
     */
    var $_required = array();

    /**
     * Processor-specific data.
     *
     * @access private
     * @var array
     */
    var $_data = array();

    /**
     * $_driver
     *
     * @author Joe Stump <joe@joestump.net>
     * @var string $_driver
     * @access private
     */
    var $_driver = null;

    /**
     * PEAR::Log instance
     *
     * @var     object
     * @access  protected
     * @see     Log
     */
    var $_log;

    /**
     * Mapping between API fields and processors'
     *
     * @var mixed $_typeFieldMap
     * @access protected
     */
    var $_typeFieldMap = array();

    /**
     * Reference to payment type
     *
     * An internal reference to the Payment_Process_Type that is currently
     * being processed.
     *
     * @var mixed $_payment Instance of Payment_Type
     * @access protected
     * @see Payment_Process_Common::setPayment()
     */
    var $_payment = null;
    // }}}
    // {{{ Public Properties
    /**
     * Your login name to use for authentication to the online processor.
     *
     * @var string
     */
    var $login = '';

    /**
     * Your password to use for authentication to the online processor.
     *
     * @var string
     */
    var $password = '';

    /**
     * Processing action.
     *
     * This should be set to one of the PAYMENT_PROCESS_ACTION_* constants.
     *
     * @var int
     */
    var $action = '';

    /**
     * A description of the transaction (used by some processors to send
     * information to the client, normally not a required field).
     * @var string
     */
    var $description = '';

    /**
     * The transaction amount.
     *
     * @var double
     */
    var $amount = 0;

    /**
     * An invoice number.
     *
     * @var mixed string or int
     */
    var $invoiceNumber = '';

    /**
     * Customer identifier
     *
     * @var mixed string or int
     */
    var $customerId = '';

    /**
     * Transaction source.
     *
     * This should be set to one of the PAYMENT_PROCESS_SOURCE_* constants.
     *
     * @var int
     */
    var $transactionSource;
    // }}}
    // {{{ __construct($options = false)
    /**
     * __construct
     *
     * PHP 5.x constructor
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     */
    function __construct($options = false)
    {
        $this->setOptions($options);
    }
    // }}}
    // {{{ Payment_Process_Common($options = false)
    /**
     * Payment_Process_Common
     *
     * PHP 4.x constructor
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     */
    function Payment_Process_Common($options = false)
    {
        $this->__construct();
    }
    // }}}
    // {{{ setPayment(&$payment)
    /**
     * Sets payment
     *
     * Returns false if payment could not be set. This usually means the
     * payment type is not valid  or that the payment type is valid, but did
     * not validate. It could also mean that the payment type is not supported
     * by the given processor.
     *
     * @param mixed $payment Object of Payment_Process_Type
     * @return bool
     * @access public
     * @author Joe Stump <joe@joestump.net>
     */
    function setPayment(&$payment)
    {
        if (isset($this->_typeFieldMap[$payment->getType()]) &&
            is_array($this->_typeFieldMap[$payment->getType()]) &&
            count($this->_typeFieldMap[$payment->getType()])) {

            $result = Payment_Process_Type::isValid($payment);
            if (PEAR::isError($result)) {
                return $result;
            }

            $this->_payment = $payment;
            // Map over the payment specific fields. Check out
            // $_typeFieldMap for more information.
            $paymentType = $payment->getType();
            foreach ($this->_typeFieldMap[$paymentType] as $generic => $specific) {

                $func = '_handle'.ucfirst($generic);
                if (method_exists($this, $func)) {
                    $result = $this->$func();
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                } else {
                    // TODO This may screw things up - the problem is that
                    // CC information is no longer member variables, so we
                    // can't overwrite it. You could always handle this
                    // with a _handle funciton. I don't think it will cause
                    // problems, but it could.
                    if (!isset($this->_data[$specific])) {
                        $this->_data[$specific] = $this->_payment->$generic;
                    }
                }
            }

            return true;
        }

        return PEAR::raiseError('Invalid type field map');
    }
    // }}}
    // {{{ setFrom($where)
    /**
     * Set many fields.
     *
     * @param  array  $where  Associative array of data to set, in the format
     *                       'field' => 'value',
     * @return void
     */
    function setFrom($where)
    {
        foreach ($this->getFields() as $field) {
            if (isset($where[$field])) {
                $this->$field = $where[$field];
            }
        }
    }
    // }}}
    // {{{ process()
    /**
     * Processes the transaction.
     *
     * This function should be overloaded by the processor.
     */
    function process()
    {
        return PEAR::raiseError("process() is not implemented in this processor.", PAYMENT_PROCESS_ERROR_NOTIMPLEMENTED);
    }
    // }}}
    // {{{ &processCallback()
    /**
     * processCallback
     *
     * This should be overridden in driver classes. It will be used to process
     * communications from gateways to your application. For instance, the
     * Authorize.net gateway will post information about pending transactions
     * to a URL you specify. This function should handle such requests
     *
     * @return object Payment_Process_Result on success, PEAR_Error on failure
     */
    function &processCallback()
    {
        return PEAR::raiseError('processCallback() not implemented',
                                PAYMENT_PROCESS_ERROR_NOTIMPLEMENTED);
    }
    // }}}
    // {{{ validate()
    /**
     * validate
     *
     * Validates data before processing. This function may be overloaded by
     * the processor.
     *
     * @return boolean true if validation succeeded, PEAR_Error if it failed.
     */
    function validate()
    {
        foreach ($this->getFields() as $field) {
            $func = '_validate'.ucfirst($field);

            // Don't validate unset optional fields
            if (! $this->isRequired($field) && !strlen($this->$field)) {
                continue;
            }

            if (method_exists($this, $func)) {
                $res = $this->$func();
                if (PEAR::isError($res)) {
                    return $res;
                } elseif (is_bool($res) && $res == false) {
                    return PEAR::raiseError('Validation of field "'.$field.'" failed.', PAYMENT_PROCESS_ERROR_INVALID);
                }
            }
        }

        return true;
    }
    // }}}
    // {{{ set($field, $value)
    /**
     * Set a value.
     *
     * This will set a value, such as the credit card number. If the requested
     * field is not part of the basic set of supported fields, it is set in
     * $_options.
     *
     * @param  string  $field  The field to set
     * @param  string  $value  The value to set
     * @return void
     */
    function set($field, $value)
    {
        if (!$this->fieldExists($field)) {
            return PEAR::raiseError('Field "' . $field . '" does not exist.', PAYMENT_PROCESS_ERROR_INVALID);
        }
        $this->$field = $value;
        return true;
    }
    // }}}
    // {{{ isRequired($field)
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
    // }}}
    // {{{ fieldExists($field)
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
    // }}}
    // {{{ getFields()
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
    // }}}
    // {{{ setOptions($options = false, $defaultOptions = false)
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
        $defaultOptions = $defaultOptions ? $defaultOptions : $this->_defaultOptions;           $this->_options = @array_merge($defaultOptions, $options);
    }
    // }}}
    // {{{ getOption($option)
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
    // }}}
    // {{{ setOption($option,$value)
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
    // }}}
    // {{{ getResult()
    /**
     * Gets transaction result.
     *
     * This function should be overloaded by the processor.
     */
    function getResult()
    {
        return PEAR::raiseError("getResult() is not implemented in this processor.", PAYMENT_PROCESS_ERROR_NOTIMPLEMENTED);
    }
    // }}}
    // {{{ _isDefinedConstant($value, $class)
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
            if (strncmp($constClass, $constant, $length) === 0 &&
                $constVal == $value) {
                $found = true;
                break;
            }
        }

        return $found;
    }
    // }}}
    // {{{ _makeRequired()
    /**
     * Mark a field (or fields) as being required.
     *
     * @param  string  $field Field name
     * @param  string  ...
     * @return boolean always true.
     */
    function _makeRequired()
    {
        foreach (func_get_args() as $field) {
            $this->_required[$field] = true;
        }
        return true;
    }
    // }}}
    // {{{ _makeOptional()
    /**
     * Mark a field as being optional.
     *
     * @param  string  $field Field name
     * @param  ...
     * @return boolean always true.
     */
    function _makeOptional()
    {
        foreach (func_get_args() as $field) {
            unset($this->_required[$field]);
        }
        return true;
    }
    // }}}
    // {{{ _validateType()
    /**
     * Validates transaction type.
     *
     * @return boolean true on success, false on failure.
     * @access private
     */
    function _validateType()
    {
        return $this->_isDefinedConst($this->type, 'type');
    }
    // }}}
    // {{{ _validateAction()
    /**
     * Validates transaction action.
     *
     * @return boolean true on success, false on failure.
     * @access private
     */
    function _validateAction()
    {
        return (isset($GLOBALS['_Payment_Process_'.$this->_driver][$this->action]));
    }
    // }}}
    // {{{ _validateSource()
    /**
     * Validates transaction source.
     *
     * @return boolean true on success, false on failure.
     * @access private
     */
    function _validateSource()
    {
        return $this->_isDefinedConst($this->transactionSource, 'source');
    }
    // }}}
    // {{{ _validateAmount()
    /**
     * Validates the charge amount.
     *
     * Charge amount must be 8 characters long, double-precision.
     * Current min/max are rather arbitrarily set to $0.99 and $99999.99,
     * respectively.
     *
     * @return boolean true on success, false otherwise
     */
    function _validateAmount()
    {
        return Validate::number($this->amount, array(
            'decimal' => '.',
            'dec_prec' => 2,
            'min' => 0.99,
            'max' => 99999.99
        ));
    }
    // }}}
    // {{{ _handleAction()
    /**
     * Handles action
     *
     * Actions are defined in $GLOBALS['_Payment_Process_DriverName'] and then
     * handled here. We may decide to abstract the defines in the driver.
     *
     * @access private
     */
    function _handleAction()
    {
        $this->_data[$this->_fieldMap['action']] = $GLOBALS['_Payment_Process_'.$this->_driver][$this->action];
    }
    // }}}
    // {{{ _prepare()
    /**
     * Prepares the POST data.
     *
     * This function handles translating the data set in the front-end to the
     * format needed by the back-end. The prepared data is stored in
     * $this->_data. If a '_handleField' method exists in this class (e.g.
     * '_handleCardNumber()'), that function is called and /must/ set
     * $this->_data correctly. If no field-handler function exists, the data
     * from the front-end is mapped into $_data using $this->_fieldMap.
     *
     * @return array Data to POST
     * @access private
     */
    function _prepare()
    {
        /*
         * FIXME - because this only loops through stuff in the fieldMap, we
         *         can't have handlers for stuff which isn't specified in there.
         *         But the whole point of having a _handler() is that you need
         *         to do something more than simple mapping.
         */
        foreach ($this->_fieldMap as $generic => $specific) {
            $func = '_handle'.ucfirst($generic);
            if (method_exists($this, $func)) {
                $result = $this->$func();
                if (PEAR::isError($result)) {
                    return $result;
                }
            } else {
                // TODO This may screw things up - the problem is that
                // CC information is no longer member variables, so we
                // can't overwrite it. You could always handle this with
                // a _handle funciton. I don't think it will cause problems,
                // but it could.
                if (!isset($this->_data[$specific])) {
                    if (isset($this->$generic)) {
                        $this->_data[$specific] = $this->$generic;
                    }

                    // Form of payments data overrides those set in the
                    // Payment_Process_Common.
                    if (isset($this->_payment->$generic)) {
                        $this->_data[$specific] = $this->_payment->$generic;
                    }
                }
            }
        }

        return true;
    }
    // }}}
}

?>
