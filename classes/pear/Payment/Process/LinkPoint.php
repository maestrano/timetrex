<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * LinkPoint processor
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
 * @author     Joe Stump <joe@joestump.net> 
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Revision: 1.13 $
 * @link       http://pear.php.net/package/Payment_Process
 * @link       http://www.linkpoint.net/
 */


require_once('Payment/Process.php');
require_once('Payment/Process/Common.php');
require_once('Net/Curl.php');
require_once('XML/Parser.php');

$GLOBALS['_Payment_Process_LinkPoint'] = array(
    PAYMENT_PROCESS_ACTION_NORMAL   => 'SALE',
    PAYMENT_PROCESS_ACTION_AUTHONLY => 'PREAUTH',
    PAYMENT_PROCESS_ACTION_POSTAUTH => 'POSTAUTH'
);

/**
 * Payment_Process_LinkPoint
 *
 * This is a processor for LinkPoint's merchant payment gateway.
 * (http://www.linkpoint.net/)
 *
 * *** WARNING ***
 * This is BETA code, and has not been fully tested. It is not recommended
 * that you use it in a production envorinment without further testing.
 *
 * @package Payment_Process
 * @author Joe Stump <joe@joestump.net>
 * @version @version@
 */
class Payment_Process_LinkPoint extends Payment_Process_Common
{
    /**
     * Front-end -> back-end field map.
     *
     * This array contains the mapping from front-end fields (defined in
     * the Payment_Process class) to the field names DPILink requires.
     *
     * @see _prepare()
     * @access private
     */
    var $_fieldMap = array(
        // Required
        'login'         => 'configfile',
        'action'        => 'ordertype',
        'invoiceNumber' => 'oid',
        'customerId'    => 'x_cust_id',
        'amount'        => 'chargetotal',
        'name'          => '',
        'zip'           => 'zip',
        // Optional
        'company'       => 'company',
        'address'       => 'address1',
        'city'          => 'city',
        'state'         => 'state',
        'country'       => 'country',
        'phone'         => 'phone',
        'email'         => 'email',
        'ip'            => 'ip',
    );

    /**
    * $_typeFieldMap
    *
    * @author Joe Stump <joe@joestump.net>
    * @access protected
    */
    var $_typeFieldMap = array(

           'CreditCard' => array(

                    'cardNumber' => 'cardnumber',
                    'cvv'        => 'cvm',
                    'expDate'    => 'expDate'

           ),

           'eCheck' => array(

                    'routingCode'   => 'routing',
                    'accountNumber' => 'account',
                    'type'          => 'type',
                    'bankName'      => 'bank',
                    'name'          => 'name',
                    'driversLicense'      => 'dl',
                    'driversLicenseState' => 'dlstate'

           )
    );

    /**
     * Default options for this processor.
     *
     * @see Payment_Process::setOptions()
     * @access private
     */
    var $_defaultOptions = array(
         'host'   => 'secure.linkpt.net',
         'port'   => '1129',
         'result' => 'LIVE'
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
        $this->_driver = 'LinkPoint';
    }

    /**
     * Payment_Process_LinkPoint
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @param array $options
     * @return void
     */
    function Payment_Process_LinkPoint($options = false)
    {
        $this->__construct($options);
    }

    /**
     * Process the transaction.
     *
     * @author Joe Stump <joe@joestump.net> 
     * @access public
     * @return mixed Payment_Process_Result on success, PEAR_Error on failure
     */
    function &process()
    {
        if (!strlen($this->_options['keyfile']) ||
            !file_exists($this->_options['keyfile'])) {
            return PEAR::raiseError('Invalid key file');
        }

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

        // Don't die partway through
        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);


        $xml = $this->_prepareQueryString();
        if (PEAR::isError($xml)) {
            return $xml;
        }

        $url = 'https://'.$this->_options['host'].':'.$this->_options['port'].
               '/LSGSXML';

        $curl = new Net_Curl($url);
        $result = $curl->create();
        if (PEAR::isError($result)) {
            return $result;
        }

        $curl->type = 'POST';
        $curl->fields = $xml;
        $curl->sslCert = $this->_options['keyfile'];

        // LinkPoint's staging server has a boned certificate. If they are
        // testing against staging we need to turn off SSL host verification.
        if ($this->_options['host'] == 'staging.linkpt.net') {
            $curl->verifyPeer = false;
            $curl->verifyHost = 0;
        }

        $curl->userAgent = 'PEAR Payment_Process_LinkPoint 0.1';

        $result = &$curl->execute();
        if (PEAR::isError($result)) {
            return PEAR::raiseError('cURL error: '.$result->getMessage());
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
     * Prepare the POST query string.
     *
     * @access private
     * @return string The query string
     */
    function _prepareQueryString()
    {

        $data = array_merge($this->_options,$this->_data);

        $xml  = '<!-- Payment_Process order -->'."\n";
        $xml .= '<order>'."\n";
        $xml .= '<merchantinfo>'."\n";
        $xml .= '  <configfile>'.$data['configfile'].'</configfile>'."\n";
        $xml .= '  <keyfile>'.$data['keyfile'].'</keyfile>'."\n";
        $xml .= '  <host>'.$data['authorizeUri'].'</host>'."\n";
        $xml .= '  <appname>PEAR Payment_Process</appname>'."\n";
        $xml .= '</merchantinfo>'."\n";
        $xml .= '<orderoptions>'."\n";
        $xml .= '  <ordertype>'.$data['ordertype'].'</ordertype>'."\n";
        $xml .= '  <result>'.$data['result'].'</result>'."\n";
        $xml .= '</orderoptions>'."\n";
        $xml .= '<payment>'."\n";
        $xml .= '  <subtotal>'.$data['chargetotal'].'</subtotal>'."\n";
        $xml .= '  <tax>0.00</tax>'."\n";
        $xml .= '  <shipping>0.00</shipping>'."\n";
        $xml .= '  <chargetotal>'.$data['chargetotal'].'</chargetotal>'."\n";
        $xml .= '</payment>'."\n";

        // Set payment method to eCheck if our payment type is eCheck.
        // Default is Credit Card.
        $data['x_method'] = 'CC';
        switch ($this->_payment->getType())
        {
            case 'eCheck':
                return PEAR::raiseError('eCheck not currently supported',
                                        PAYMENT_PROCESS_ERROR_NOTIMPLEMENTED);

                $xml .= '<telecheck>'."\n";
                $xml .= '  <routing></routing>'."\n";
                $xml .= '  <account></account>'."\n";
                $xml .= '  <checknumber></checknumber>'."\n";
                $xml .= '  <bankname></bankname>'."\n";
                $xml .= '  <bankstate></bankstate>'."\n";
                $xml .= '  <dl></dl>'."\n";
                $xml .= '  <dlstate></dlstate>'."\n";
                $xml .= '  <accounttype>pc|ps|bc|bs</accounttype>'."\n";
                $xml .= '<telecheck>'."\n";
                break;
            case 'CreditCard':
                $xml .= '<creditcard>'."\n";
                $xml .= '  <cardnumber>'.$data['cardnumber'].'</cardnumber>'."\n";
                list($month,$year) = explode('/',$data['expDate']);
                if (strlen($year) == 4) {
                    $year = substr($year,2);
                }

                $month = sprintf('%02d',$month);

                $xml .= '  <cardexpmonth>'.$month.'</cardexpmonth>'."\n";
                $xml .= '  <cardexpyear>'.$year.'</cardexpyear>'."\n";
                if (strlen($data['cvm'])) {
                    $xml .= '  <cvmvalue>'.$data['cvm'].'</cvmvalue>'."\n";
                    $xml .= '  <cvmindicator>provided</cvmindicator>'."\n";
                }
                $xml .= '</creditcard>'."\n";
        }

        if (isset($this->_payment->firstName) &&
            isset($this->_payment->lastName)) {
            $xml .= '<billing>'."\n";
            $xml .= '  <userid>'.$this->_payment->customerId.'</userid>'."\n";
            $xml .= '  <name>'.$this->_payment->firstName.' '.$this->_payment->lastName.'</name>'."\n";
            $xml .= '  <company>'.$this->_payment->company.'</company>'."\n";
            $xml .= '  <address1>'.$this->_payment->address.'</address1>'."\n";
            $xml .= '  <city>'.$this->_payment->city.'</city>'."\n";
            $xml .= '  <state>'.$this->_payment->state.'</state>'."\n";
            $xml .= '  <zip>'.$this->_payment->zip.'</zip>'."\n";
            $xml .= '  <country>'.$this->_payment->country.'</country>'."\n";
            $xml .= '  <phone>'.$this->_payment->phone.'</phone>'."\n";
            $xml .= '  <email>'.$this->_payment->email.'</email>'."\n";
            $xml .= '  <addrnum>'.$this->_payment->address.'</addrnum>'."\n";
            $xml .= '</billing>'."\n";
        }

        $xml .= '</order>'."\n";

        return $xml;
    }
}

/**
 * Payment_Process_Result_LinkPoint
 * 
 * LinkPoint result class 
 *
 * @author Joe Stump <joe@joestump.net>
 * @package Payment_Process
 */
class Payment_Process_Result_LinkPoint extends Payment_Process_Result
{

    var $_statusCodeMap = array('APPROVED' => PAYMENT_PROCESS_RESULT_APPROVED,
                                'DECLINED' => PAYMENT_PROCESS_RESULT_DECLINED,
                                'FRAUD' => PAYMENT_PROCESS_RESULT_FRAUD);

    /**
     * LinkPoint status codes
     *
     * This array holds many of the common response codes. There are over 200
     * response codes - so check the LinkPoint manual if you get a status
     * code that does not match (see "Response Reason Codes & Response
     * Reason Text" in the AIM manual).
     *
     * @see getStatusText()
     * @access private
     */
    var $_statusCodeMessages = array(
        'APPROVED' => 'This transaction has been approved.',
        'DECLINED' => 'This transaction has been declined.',
        'FRAUD' => 'This transaction has been determined to be fraud.');

    var $_avsCodeMap = array(
        'YY' => PAYMENT_PROCESS_AVS_MATCH,
        'YN' => PAYMENT_PROCESS_AVS_MISMATCH,
        'YX' => PAYMENT_PROCESS_AVS_ERROR,
        'NY' => PAYMENT_PROCESS_AVS_MISMATCH,
        'XY' => PAYMENT_PROCESS_AVS_MISMATCH,
        'NN' => PAYMENT_PROCESS_AVS_MISMATCH,
        'NX' => PAYMENT_PROCESS_AVS_MISMATCH,
        'XN' => PAYMENT_PROCESS_AVS_MISMATCH,
        'XX' => PAYMENT_PROCESS_AVS_ERROR
    );

    var $_avsCodeMessages = array(
        'YY' => 'Address matches, zip code matches',
        'YN' => 'Address matches, zip code does not match',
        'YX' => 'Address matches, zip code comparison not available',
        'NY' => 'Address does not match, zip code matches',
        'XY' => 'Address comparison not available, zip code matches',
        'NN' => 'Address comparison does not match, zip code does not match',
        'NX' => 'Address does not match, zip code comparison not available',
        'XN' => 'Address comparison not available, zip code does not match',
        'XX' => 'Address comparison not available, zip code comparison not available'
    );

    var $_cvvCodeMap = array('M' => PAYMENT_PROCESS_CVV_MATCH,
                             'N' => PAYMENT_PROCESS_CVV_MISMATCH,
                             'P' => PAYMENT_PROCESS_CVV_ERROR,
                             'S' => PAYMENT_PROCESS_CVV_ERROR,
                             'U' => PAYMENT_PROCESS_CVV_ERROR,
                             'X' => PAYMENT_PROCESS_CVV_ERROR
    );

    var $_cvvCodeMessages = array(
        'M' => 'Card Code Match',
        'N' => 'Card code does not match',
        'P' => 'Not processed',
        'S' => 'Merchant has indicated that the card code is not present on the card',
        'U' => 'Issuer is not certified and/or has not proivded encryption keys',
        'X' => 'No response from the credit card association was received'
    );

    var $_fieldMap = array('r_approved'  => 'code',
                           'r_error'  => 'message',
                           'r_code'  => 'approvalCode',
                           'r_ordernum'  => 'transactionId'
    );

    function Payment_Process_Response_LinkPoint($rawResponse)
    {
        $this->Payment_Process_Response($rawResponse);
    }

    /**
    * parse
    *
    * @author Joe Stump <joe@joestump.net>
    * @access public
    * @return void
    */
    function parse()
    {
        $xml = new Payment_Processor_LinkPoint_XML_Parser();
        $xml->parseString('<response>'.$this->_rawResponse.'</response>');
        if (is_array($xml->response) && count($xml->response)) {
            $this->avsCode = substr($xml->response['r_avs'],0,2);
            $this->cvvCode = substr($xml->response['r_avs'],2,1);
            $this->customerId = $this->_request->customerId;
            $this->invoiceNumber = $this->_request->invoiceNumber;
            $this->_mapFields($xml->response);

            // switch to DECLINED since a duplicate isn't *really* fraud
            if(eregi('duplicate',$this->message)) {
                $this->messageCode = 'DECLINED';
            }
        }
    }
}

/**
 * Payment_Processor_LinkPoint_XML_Parser
 *
 * XML Parser for the LinkPoint response
 *
 * @author Joe Stump <joe@joestump.net>
 * @package Payment_Process
 */
class Payment_Processor_LinkPoint_XML_Parser extends XML_Parser
{
    /**
     * $response
     * 
     * @var array $response Raw response as an array
     * @access public
     */
    var $response = array();

    /**
     * $log
     *
     * @var string $tag Current tag
     * @access private
     */
    var $tag = null;

    /**
     * Payment_Processor_LinkPoint_XML_Parser
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @return void
     * @see XML_Parser
     */
    function Payment_Processor_LinkPoint_XML_Parser()
    {
        $this->XML_Parser();
    }

    /**
     * startHandler
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @param resource $xp XML processor handler
     * @param string $elem Name of XML entity
     * @return void
     */
    function startHandler($xp, $elem, &$attribs)
    {
        $this->tag = $elem;
    }

    /**
     * endHandler
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @param resource $xp XML processor handler
     * @param string $elem Name of XML entity
     * @return void
     */
    function endHandler($xp, $elem)
    {

    }

    /**
     * defaultHandler
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @param resource $xp XML processor handler
     * @param string $data
     * @return void
     */
    function defaultHandler($xp,$data)
    {
        $this->response[strtolower($this->tag)] = $data;
    }
}

?>
