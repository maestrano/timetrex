<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Tim Batz <ego at subport.org>                               |
// +----------------------------------------------------------------------+
//
// $Id: UPS.php,v 0.1dev1 2005/01/26 Ego Exp $

/**
* UPS Package 2004- by Tim Batz
*
* @package   Services_UPS
* @category  Services
* @author    Tim Batz <ego at subport.org>
* @since     December 2004
* @copyright Copyright &copy; 2004, Tim Batz
* @version   0.1dev1
* @license   http://www.php.net/license/3_0.txt
* @link      http://ec.ups.com/
*
* @todo      error handling is for sure not perfect
* @todo      resolving returned data as easy as possible
* @todo      lotsa other stuff for sure
* @todo      all services besides Tracking, Signature Tracking and Address Validation and maybe even those
*/

require_once 'PEAR.php';
require_once 'XML/Serializer.php';
require_once 'XML/Unserializer.php';

class UPS {

    /**
     * Set the registered UPS username (required)
     * @link http://ec.ups.com
     *
     * @name $username
     * @access public
     * @var string value of the UPS username
     */
    var $username;

    /**
     * Set the password for the registered UPS username (required)
     * @link http://ec.ups.com
     *
     * @name $userpw
     * @access public
     * @var string value of the UPS password
     */
    var $userpw;

    /**
     * Set the developer key for the registered UPS username (required)
     * sent by UPS after request
     * @link http://ec.ups.com
     *
     * @name $devkey
     * @access public
     * @var string value of the UPS developer key
     */
    var $devkey;

    /**
     * Set the developer key sent by UPS after request (required)
     *
     * First you get a key for developing purposes only.
     * Set this to property to FALSE to use the URL for testing.
     * After your work is finished you can request a real developer key
     * to track real packages. Set it to TRUE after you recieved your new
     * developer key.
     *
     * @name $devstate
     * @access public
     * @var boolean TRUE for productive state, FALSE for development state
     */
    var $devstate;

    /**
     * Set the complete path including the filename to
     * curl-ca-bundle.crt bundled with CURL (optional)
     *
     * If you dont set this property, then the SSL certificate of UPS will not be
     * verified, but is recommended. CURL_VERIFYPEER will be set to FALSE automatically then
     * to fullfill the request without verification.
     * curl-ca-bundle.crt is included in the SSL version of Curl.
     * Works with relative and absolute path.
     * A notice is printed, if this option is not used.
     * @link http://curl.haxx.se/
     *
     * @name $cacert
     * @access public
     * @var string set the path and filename to curl-ca-bundle.crt including the filename
     */
    var $cacert;

    /**
     * Set the proxy host and port (optional)
     *
     * @name $proxy_host
     * @access public
     * @var string set the proxy host and port with "host:port"
     */
    var $proxy_host;

    /**
     * Set the proxy username (optional)
     *
     * @name $proxy_username
     * @access public
     * @var string set the proxy username
     */
    var $proxy_username;

    /**
     * Set the proxy password (optional)
     *
     * @name $proxy_userpw
     * @access public
     * @var string set the proxy password
     */
    var $proxy_userpw;

    /**
     * Contains the response data from UPS
     *
     * @name $response
     * @access protected
     * @var array
     */
    var $response;

    /**
     * Sets the type of service you want to use
     *
     * Possible values/services are:
     * - "tracking" for Normal and Signature Tracking
     * - "shipping" for Shipping
     * - "address validation" for Address Validation
     * - "rates and services selection" for Rates and Services Selection
     * - "quantum view" for File Download for Quantum View
     * - "time in transit" for Time in Transit
     *
     * @name $_service
     * @access private
     * @var string
     */
    var $_service;

    /**
     * Contains the request data for UPS
     *
     * @name $request
     * @access private
     * @var string
     */
    var $_request;

    /**
     * PHP5 constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->ups();
    }

    /**
     * PHP4 constructor
     *
     * @access private
     */
    function ups()
    {
    }

    /**
     * Generate XML access data
     *
     * @access private
     * @return string
     */
    function _serialize_access()
    {
        if (!isset($this->devkey)) {
            $msg  = 'Property $devkey is not set, but required!';
        }
        if (!isset($this->username)) {
            $msg  = 'Property $username is not set, but required!';
        }
        if (!isset($this->userpw)) {
            $msg  = 'Property $userpw is not set, but required!';
        }
        if (isset($msg)) {
            PEAR::raiseError($msg, 0, PEAR_ERROR_DIE);
        }
        $serializer = new XML_Serializer;
        $xml_array = array(
            'AccessLicenseNumber'   => $this->devkey,
            'UserId'                => $this->username,
            'Password'              => $this->userpw
        );
        $options = array(
            'linebreak'         => '',
            'addDecl'           => TRUE,
            'rootName'          => 'AccessRequest',
            'rootAttributes'    => array('xml:lang' => 'en-US')
        );
        $serializer->serialize($xml_array, $options);
        // return $serializer->getSerializedData();
        // dirty fix to remove CDATA - help appreciated
        return preg_replace("/<!\[CDATA\[(.*?)\]\]>/", "\\1", $serializer->getSerializedData());
    }

    /**
     * Send XML request to UPS
     *
     * @access public
     */
    function request()
    {
        switch ($this->_service)
        {
            case 'tracking':
                if ($this->devstate) {
                    $url = 'https://www.ups.com/ups.app/xml/Track';
                } else {
                    $url = 'https://wwwcie.ups.com/ups.app/xml/Track';
                }
                break;

            case 'address validation':
                $url = 'https://www.ups.com/ups.app/xml/AV';
                break;

            case 'rates and services selection':
                if ($this->devstate) {
                    $url = 'https://www.ups.com/ups.app/xml/Rate';
                } else {
                    $url = 'https://wwwcie.ups.com/ups.app/xml/Rate';
                }
                break;

            case 'time in transit':
                if ($this->devstate) {
                    $url = 'https://wwwcie.ups.com/ups.app/xml/Rate';
                } else {
                    $url = 'https://www.ups.com/ups.app/xml/TimeInTransit';
                }
                break;

            case 'shipping':
                /*
                ShipConfirm phase https://wwwcie.ups.com/ups.app/xml/ShipConfirm.
                ShipAccept phase https://wwwcie.ups.com/ups.app/xml/ShipAccept.
                RequestVoid phase https://wwwcie.ups.com/ups.app/xml/Void.
                */
                $url = '';
                break;

            case 'quantum view':
                $url = 'https://www.ups.com/ups.app/xml/QVEvents';
                break;

            default:
                $msg  = 'No service set!';
                PEAR::raiseError($msg, 0, PEAR_ERROR_DIE);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'PHP CURL');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_request);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
        if (isset($this->cacert) && file_exists(realpath($this->cacert))) {
            curl_setopt($curl, CURLOPT_CAINFO, realpath($this->cacert));
        } else {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            $msg  = 'Unsecure connection! Usage of property $cacert recommended!';
            if (!file_exists(realpath($this->cacert))) {
                $msg .= " File $this->cacert not found!";
            } else {
                $msg .= ' Using $cacert is highly recommended for this package to due security reasons!';
            }
            //PEAR::raiseError($msg, 0, PEAR_ERROR_PRINT);
        }
        if (isset($this->proxy_host)) {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy_host);
        }
        if (isset($this->proxy_username) && isset($this->proxy_userpw)) {
            curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxy_username.':'.$this->proxy_userpw);
        }
        $this->response = curl_exec($curl);
        if (curl_errno($curl)!=0) {
            return "Curl Error: ".curl_errno($curl)." ".curl_error($curl);
        }
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            switch ($http_code) {
                case 200:
                    // $return = "No Error : Request processed successfully.";
                    break;

                case 240:
                    $return  = "Error 240 : ";
                    $return .= "Request Processed, some warnings exist. Check XML Doc for details.";
                    break;

                case 250:
                    $return  = "Error 250 : ";
                    $return .= "Request could not be processed. Check XML Doc for error information.";
                    break;

                case 500:
                    $return  = "Error 500 : ";
                    $return .= "UPS OnLine Tool unavailable. Try again later.";
                    break;
                default:
                    $return  = "Error ".$http_code." : Unknown Error";
        }
        curl_close($curl);
        if (isset($return)) {
            $this->response = array ('HTTPError' => $return);
        } else {
            $this->response = $this->_unserialize_response();
        }
    }

    /**
     * Generate XML request data for Normal and Signature Tracking
     *
     * $number:
     * Set the tracking number (required)
     *
     * $method
     * Set the method of tracking (optional)
     *
     * Possible values are:
     *  - "trackingnumber" (default)
     *  - "referencenumber"
     *  - "shipmentidentificationnumber"
     *
     * $option:
     * Set the option for tracking (optional)
     *
     * This value alters the type of response
     * Possible values are:
     *  - "none" or empty (default)
     *    Response will contain the last activity on the package.
     *  - "activity"
     *    Response will contain all activity on the package.
     *
     * @access public
     * @param $number
     * @param $method
     * @param $option
     * @return string
     */
    function tracking($number, $method='trackingnumber', $option='')
    {
        $this->_service = 'tracking';
        if (!isset($number) || strlen($number)!=18) {
            $msg  = 'Tracking number was not set or is invalid!';
            PEAR::raiseError($msg, 0, PEAR_ERROR_DIE);
        }
        $serializer = new XML_Serializer;
        $customercontext = 'Request generated by PHP PEAR Services_UPS Package by Tim Batz';
        $xml_array = array(
            'Request' => array(
                'TransactionReference' => array(
                    'CustomerContext'   => $customercontext,
                    'XpciVersion'       => '1.0001'
                ),
                'RequestAction' => 'Track'
            )
        );
        $xml_array['Request']['RequestOption'] = $option;
        switch ($method) {
            case 'trackingnumber':
                $xml_array['TrackingNumber'] = $number;
                break;

            case 'referencenumber':
                $xml_array['ReferenceNumber']['Value'] = $number;
                break;

            case 'shipmentidentificationnumber':
                $xml_array['ShipmentIdentificationNumber'] = $number;
                break;

            default:
                $xml_array['TrackingNumber'] = $number;
        }
        $options = array(
            'linebreak'         => '',
            'addDecl'           => TRUE,
            'rootName'          => 'TrackRequest',
            'rootAttributes'    => array('xml:lang' => 'en-US')
        );
        $serializer->serialize($xml_array, $options);
        $this->_request  = $this->_serialize_access();
        // $this->_request .= $serializer->getSerializedData();
        // dirty fix to remove CDATA - help appreciated
        $this->_request .= preg_replace("/<!\[CDATA\[(.*?)\]\]>/", "\\1", $serializer->getSerializedData());
    }

    function address_validation($city='', $postalcode='', $stateprovincecode='')
    {
        $this->_service = 'address validation';
        $serializer = new XML_Serializer;
        $customercontext = 'Request generated by PHP PEAR Services_UPS Package by Tim Batz';
        $xml_array = array(
            'Request' => array(
                'TransactionReference' => array(
                    'CustomerContext'   => $customercontext,
                    'XpciVersion'       => '1.0001'
                ),
                'RequestAction' => 'AV'
            )
        );
        $check = FALSE;
        if (!empty($postalcode) && strlen($postalcode)<=9) {
            $xml_array['Address']['PostalCode'] = $postalcode;
            $check = TRUE;
        }
        if (!empty($city) && strlen($city)<=40) {
            $xml_array['Address']['City'] = $city;
            $check = TRUE;
        }
        if (!empty($stateprovincecode) && strlen($stateprovincecode)<=2) {
            $xml_array['Address']['StateProvinceCode'] = $stateprovincecode;
        }
        if (!$check) {
            $msg  = 'At least a postalcode or city is needed to validate an address!';
            PEAR::raiseError($msg, 0, PEAR_ERROR_DIE);
        }
        $options = array(
            'linebreak'         => '',
            'addDecl'           => TRUE,
            'rootName'          => 'AddressValidationRequest',
            'rootAttributes'    => array('xml:lang' => 'en-US')
        );
        $serializer->serialize($xml_array, $options);
        $this->_request  = $this->_serialize_access();
        // $this->_request .= $serializer->getSerializedData();
        // dirty fix to remove CDATA - help appreciated
        $this->_request .= preg_replace("/<!\[CDATA\[(.*?)\]\]>/", "\\1", $serializer->getSerializedData());
    }

    /**
     * Return UPS XML data response as array
     *
     * @access private
     * @return array
     */
    function _unserialize_response()
    {
        $unserializer = new XML_Unserializer;
        $options['complexType'] = 'array';
        $unserializer->setOptions($options);
        $unserializer->unserialize($this->response);
        return $unserializer->getUnserializedData();
    }

    /**
     * TODO Stuff starting here
     */
    function rates_and_service_selection($shipper_arr, $ship_from_arr, $ship_to_arr, $shipment_arr, $request_option = 'shop' )
    {
        $this->_service = 'rates and services selection';
        $serializer = new XML_Serializer;

        $customercontext = 'Request generated by PHP PEAR Services_UPS Package by Tim Batz';
        $xml_array['Request']['TransactionReference']['CustomerContext'] = $customercontext;
        $xml_array['Request']['TransactionReference']['XpciVersion'] = '1.0001';
        $xml_array['Request']['RequestAction'] = 'Rate';

        $xml_array['Request']['RequestOption'] = $request_option; // rate(default) == One Rate or Shop == mulitple rates

		$xml_array['Shipment']['Shipper'] = $shipper_arr;
		$xml_array['Shipment']['ShipTo'] = $ship_to_arr;
		$xml_array['Shipment']['ShipFrom'] = $ship_from_arr;

		if ( isset($shipment_arr['Package']) ) {
			$xml_array['Shipment']['Package'] = $shipment_arr['Package'];

			if ( isset($xml_array['Shipment']['Package']['Dimensions']['Length'])
					AND $xml_array['Shipment']['Package']['Dimensions']['Length'] < 1 ) {
				$xml_array['Shipment']['Package']['Dimensions']['Length'] = 1;
			}
			if ( isset($xml_array['Shipment']['Package']['Dimensions']['Width'])
					AND $xml_array['Shipment']['Package']['Dimensions']['Width'] < 1 ) {
				$xml_array['Shipment']['Package']['Dimensions']['Width'] = 1;
			}
			if ( isset($xml_array['Shipment']['Package']['Dimensions']['Height'])
					AND $xml_array['Shipment']['Package']['Dimensions']['Height'] < 1 ) {
				$xml_array['Shipment']['Package']['Dimensions']['Height'] = 1;
			}

		}

		if ( isset($shipment_arr['Service']) ) {
			$xml_array['Shipment']['Service'] = $shipment_arr['Service'];
		}

		/*
        $xml_array['PickupType']['Code'] = '06'; //One time pickup

        $xml_array['CustomerClassification']['Code'] = '';

        $xml_array['Shipment']['Shipper']['ShipperNumber'] = '';
        $xml_array['Shipment']['Shipper']['Address']['AddressLine1'] = '';
        $xml_array['Shipment']['Shipper']['Address']['AddressLine2'] = '';
        $xml_array['Shipment']['Shipper']['Address']['AddressLine3'] = '';
        $xml_array['Shipment']['Shipper']['Address']['City'] = '';
        $xml_array['Shipment']['Shipper']['Address']['StateProvinceCode'] = '';
        $xml_array['Shipment']['Shipper']['Address']['PostalCode'] = '';
        $xml_array['Shipment']['Shipper']['Address']['CountryCode'] = '';

        $xml_array['Shipment']['ShipTo']['Address']['City'] = '';
        $xml_array['Shipment']['ShipTo']['Address']['StateProvinceCode'] = '';
        $xml_array['Shipment']['ShipTo']['Address']['PostalCode'] = '';
        $xml_array['Shipment']['ShipTo']['Address']['CountryCode'] = '';
        $xml_array['Shipment']['ShipTo']['Address']['ResidentialAddressIndicator'] = '';

        $xml_array['Shipment']['ShipFrom']['Address']['City'] = '';
        $xml_array['Shipment']['ShipFrom']['Address']['StateProvinceCode'] = '';
        $xml_array['Shipment']['ShipFrom']['Address']['PostalCode'] = '';
        $xml_array['Shipment']['ShipFrom']['Address']['CountryCode'] = '';

        $xml_array['Shipment']['ShipmentWeight']['UnitOfMeasurement'] = '';
        $xml_array['Shipment']['ShipmentWeight']['UnitOfMeasurement']['Code'] = '';
        $xml_array['Shipment']['ShipmentWeight']['Weight'] = '';

        $xml_array['Shipment']['Service']['Code'] = '';

        $xml_array['Shipment']['Package']['PackagingType']['Code'] = '';
        $xml_array['Shipment']['Package']['PackagingType']['Code'] = '';
        $xml_array['Shipment']['Package']['Dimensions']['UnitOfMeasurement']['Code'] = '';
        $xml_array['Shipment']['Package']['Dimensions']['Length'] = '';
        $xml_array['Shipment']['Package']['Dimensions']['Width'] = '';
        $xml_array['Shipment']['Package']['Dimensions']['Height'] = '';
        $xml_array['Shipment']['Package']['DimensionalWeight']['UnitOfMeasurement']['Code'] = '';
        $xml_array['Shipment']['Package']['PackageWeight']['UnitOfMeasurement']['Code'] = '';
        $xml_array['Shipment']['Package']['PackageWeight']['Weight'] = '';
        $xml_array['Shipment']['Package']['OversizePackage'] = '';
        $xml_array['Shipment']['Package']['PackageServiceOptions']['COD']['CODFundsCode'] = '';
        $xml_array['Shipment']['Package']['PackageServiceOptions']['COD']['CODCode'] = '';
        $xml_array['Shipment']['Package']['PackageServiceOptions']['COD']['CODAmount']['CurrencyCode'] = '';
        $xml_array['Shipment']['Package']['PackageServiceOptions']['COD']['CODAmount']['MonetaryValue'] = '';
        $xml_array['Shipment']['Package']['PackageServiceOptions']['DeliveryConfirmation']['DCISType'] = '';
        $xml_array['Shipment']['Package']['PackageServiceOptions']['DeliveryConfirmation']['DCISType'] = '';
        $xml_array['Shipment']['Package']['PackageServiceOptions']['InsuredValue']['CurrencyCode'] = '';
        $xml_array['Shipment']['Package']['PackageServiceOptions']['InsuredValue']['MonetaryValue'] = '';
        $xml_array['Shipment']['Package']['AdditionalHandlingIndicator'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['SaturdayPickupIndicator'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['SaturdayDeliveryIndicator'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['OnCallAir']['Schedule']['PickupDay'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['OnCallAir']['Schedule']['Method'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['CallTagARS']['Code'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['CallTagARS']['ScheduledPickupDate'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['CallTagARS']['Number'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['NotificationCode'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['AttentionName'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['CompanyName'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['FaxDestination']['FaxDestinationIndicator'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['FaxNumber']['StructuredPhoneNumber'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['FaxNumber']['StructuredPhoneNumber']['PhoneCountryCode'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['FaxNumber']['StructuredPhoneNumber']['PhoneDialPlanNumber'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['FaxNumber']['StructuredPhoneNumber']['PhoneLineNumber'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['FaxNumber']['StructuredPhoneNumber']['PhoneExtension'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['EmailAddress'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['Memo'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['Subject'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['FromEMailAddress'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['FromName'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['UndeliverableEMailAddress'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['MessageBody']['MIMEType'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['MessageBody']['EmailContent'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['MessageBody']['Image']['ImageFormat']['Code'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['MessageBody']['Image']['ImageFormat']['Description'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['MessageBody']['Image']['GraphicImage'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['MessageBody']['CharSet'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['EMailMessage']['MessageBody']['FileName'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['PhoneNumber'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['PhoneNumber']['StructuredPhoneNumber'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['PhoneNumber']['StructuredPhoneNumber']['PhoneCountryCode'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['PhoneNumber']['StructuredPhoneNumber']['PhoneDialPlanNumber'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['PhoneNumber']['StructuredPhoneNumber']['PhoneLineNumber'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['PhoneNumber']['StructuredPhoneNumber']['PhoneExtension'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['ShipmentNotification']['Memo']['StructuredPhoneNumber']['PhoneLineNumber'] = '';
        $xml_array['Shipment']['HandlingCharge']['FlatRate']['CurrencyCode'] = '';
        $xml_array['Shipment']['HandlingCharge']['FlatRate']['MonetaryValue'] = '';
        // $xml_array['Shipment']['ShipmentServiceOptions']['COD'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['COD']['CODCode'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['COD']['CODFundsCode'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['COD']['CODFundsCode']['CurrencyCode'] = '';
        $xml_array['Shipment']['ShipmentServiceOptions']['COD']['CODAmount']['MonetaryValue'] = '';
        $xml_array['Shipment']['HandlingCharge']['FlatRate']['CurrencyCode'] = '';
        $xml_array['Shipment']['HandlingCharge']['FlatRate']['MonetaryValue'] = '';
        $xml_array['Shipment']['HandlingCharge']['Percentage'] = '';
		*/

        $options = array(
            'linebreak'         => '',
            'addDecl'           => TRUE,
            'rootName'          => 'RatingServiceSelectionRequest',
            'rootAttributes'    => array('xml:lang' => 'en-US')
        );
        $serializer->serialize($xml_array, $options);
        $this->_request  = $this->_serialize_access();
        // $this->_request .= $serializer->getSerializedData();
        // dirty fix to remove CDATA - help appreciated
        $this->_request .= preg_replace("/<!\[CDATA\[(.*?)\]\]>/", "\\1", $serializer->getSerializedData());
    }

    function time_in_transit()
    {
        $this->service = 'time in transit';
        $serializer = new XML_Serializer;
        $serializer->setOption('linebreak', '');
        $serializer->setOption('addDecl', true);
        $serializer->setOption('rootName', 'TimeInTransitRequest');
        $root_attr['xml:lang'] = 'en-US';
        $serializer->setOption('rootAttributes', $root_attr);
        $customercontext = 'Request generated by PHP PEAR Services_UPS Package by Tim Batz';
        $xml_array['Request']['TransactionReference']['CustomerContext'] = $customercontext;
        $xml_array['Request']['TransactionReference']['XpciVersion'] = '1.0001';
        $xml_array['Request']['RequestAction'] = 'TimeInTransit';

        $xml_array['TransitFrom']['AddressArtifactFormat']['PoliticalDivision3'] = '';
        $xml_array['TransitFrom']['AddressArtifactFormat']['PoliticalDivision2'] = '';
        $xml_array['TransitFrom']['AddressArtifactFormat']['PoliticalDivision1'] = '';
        $xml_array['TransitFrom']['AddressArtifactFormat']['CountryCode'] = '';
        $xml_array['TransitFrom']['AddressArtifactFormat']['PostcodePrimaryLow'] = '';

        $xml_array['TransitTo']['AddressArtifactFormat']['PoliticalDivision3'] = '';
        $xml_array['TransitTo']['AddressArtifactFormat']['PoliticalDivision2'] = '';
        $xml_array['TransitTo']['AddressArtifactFormat']['PoliticalDivision1'] = '';
        $xml_array['TransitTo']['AddressArtifactFormat']['CountryCode'] = '';
        $xml_array['TransitTo']['AddressArtifactFormat']['PostcodePrimaryLow'] = '';
        $xml_array['TransitTo']['AddressArtifactFormat']['ResidentialAddressIndicator'] = '';

        $xml_array['PickupDate'] = '';

        $xml_array['ShipmentWeight']['UnitOfMeasurement']['Code'] = ''; // LBS/KGS
        $xml_array['ShipmentWeight']['UnitOfMeasurement']['Description'] = ''; // Pounds/Kilograms
        $xml_array['ShipmentWeight']['Weight'] = '';

        $xml_array['TotalPackagesInShipment'] = ''; // default 1

        $xml_array['InvoiceLineTotal']['CurrencyCode'] = '';
        $xml_array['InvoiceLineTotal']['MonetaryValue'] = '';


        $xml_array['DocumentsOnlyIndicator'] = ''; // set or not

        $xml_array['MaximumListSize'] = ''; // default 35 / 1-50

        /*
        $msg  = 'At least one parameter is needed validate an address!';
        PEAR::raiseError($msg, 0, PEAR_ERROR_DIE);
        */
        $serializer->serialize($xml_array);
        $this->request = $serializer->getSerializedData();
    }

    function shipping()
    {
        $this->service = 'shipping';

        $serializer->serialize($xml_array);
        $this->request = $serializer->getSerializedData();
    }

    function quantum_view()
    {
        $this->service = 'quantum view';

        $serializer->serialize($xml_array);
        $this->request = $serializer->getSerializedData();
    }

    /**
     * BETA Stuff starting here
     */
    function values()
    {
        // ISO Country Codes - resolved
        $isocountrycode['BN'] = 'Brunei Darussalam';
        $isocountrycode['BG'] = 'Bulgaria';
        $isocountrycode['BF'] = 'Burkina Faso';
        $isocountrycode['BI'] = 'Burundi';
        $isocountrycode['KH'] = 'Cambodia';
        $isocountrycode['CM'] = 'Cameroon';
        $isocountrycode['CA'] = 'Canada';
        $isocountrycode['IC'] = 'Canary Islands';
        $isocountrycode['CV'] = 'Cape Verde';
        $isocountrycode['KY'] = 'Cayman Islands';
        $isocountrycode['CF'] = 'Central African Republic';
        $isocountrycode['TD'] = 'Chad';
        $isocountrycode['CD'] = 'Channel Islands';
        $isocountrycode['CL'] = 'Chile';
        $isocountrycode['CN'] = 'China';
        $isocountrycode['CX'] = 'Christmas Island';
        $isocountrycode['CC'] = 'Cocos (Keeling) Islands';
        $isocountrycode['CO'] = 'Colombia';
        $isocountrycode['KM'] = 'Comoros';
        $isocountrycode['ZP'] = 'Congo (Democratic Republic of)';
        $isocountrycode['CK'] = 'Cook Islands';
        $isocountrycode['CR'] = 'Costa Rica';
        $isocountrycode['CI'] = "Cote D' Ivoire (Ivory Coast)";
        $isocountrycode['HR'] = 'Croatia (Hrvatska)';
        $isocountrycode['CB'] = 'Curacao';
        $isocountrycode['CY'] = 'Cyprus';
        $isocountrycode['CZ'] = 'Czech Republic';
        $isocountrycode['DK'] = 'Denmark';
        $isocountrycode['DJ'] = 'Djibouti';
        $isocountrycode['DM'] = 'Dominica';
        $isocountrycode['DO'] = 'Dominican Republic';
        $isocountrycode['TP'] = 'East Timor';
        $isocountrycode['EC'] = 'Ecuador';
        $isocountrycode['EG'] = 'Egypt';
        $isocountrycode['SV'] = 'El Salvador';
        $isocountrycode['EN'] = 'England';
        $isocountrycode['GQ'] = 'Equatorial Guinea';
        $isocountrycode['ER'] = 'Eritrea';
        $isocountrycode['EE'] = 'Estonia';
        $isocountrycode['ET'] = 'Ethiopia';
        $isocountrycode['FO'] = 'Faeroe Islands';
        $isocountrycode['FK'] = 'Falkland Islands (Malvinas)';
        $isocountrycode['FJ'] = 'Fiji';
        $isocountrycode['FI'] = 'Finland';
        $isocountrycode['FR'] = 'France';
        $isocountrycode['GF'] = 'French Guiana';
        $isocountrycode['PF'] = 'French Polynesia';
        $isocountrycode['TF'] = 'French Southern Territories';
        $isocountrycode['GA'] = 'Gabon';
        $isocountrycode['GM'] = 'Gambia';
        $isocountrycode['GE'] = 'Georgia';
        $isocountrycode['DE'] = 'Germany';
        $isocountrycode['GH'] = 'Ghana';
        $isocountrycode['GI'] = 'Gibraltar';
        $isocountrycode['GB'] = 'Great Britain (UK)';
        $isocountrycode['GR'] = 'Greece';
        $isocountrycode['GL'] = 'Greenland';
        $isocountrycode['GD'] = 'Grenada';
        $isocountrycode['GP'] = 'Guadeloupe';
        $isocountrycode['GU'] = 'Guam';
        $isocountrycode['GT'] = 'Guatemala';
        $isocountrycode['GN'] = 'Guinea';
        $isocountrycode['GW'] = 'Guinea-Bissau';
        $isocountrycode['GY'] = 'Guyana';
        $isocountrycode['HT'] = 'Haiti';
        $isocountrycode['HM'] = 'Heard Island and McDonald Islands';
        $isocountrycode['HN'] = 'Honduras';
        $isocountrycode['HK'] = 'Hong Kong';
        $isocountrycode['HU'] = 'Hungary';
        $isocountrycode['IS'] = 'Iceland';
        $isocountrycode['IN'] = 'India';
        $isocountrycode['ID'] = 'Indonesia';
        $isocountrycode['IE'] = 'Ireland';
        $isocountrycode['IL'] = 'Israel';
        $isocountrycode['IT'] = 'Italy';
        $isocountrycode['JM'] = 'Jamaica';
        $isocountrycode['JP'] = 'Japan';
        $isocountrycode['JO'] = 'Jordan';
        $isocountrycode['KZ'] = 'Kazakhstan';
        $isocountrycode['KE'] = 'Kenya';
        $isocountrycode['KI'] = 'Kiribati';
        $isocountrycode['KO'] = 'Kosrae';
        $isocountrycode['KW'] = 'Kuwait';
        $isocountrycode['KG'] = 'Kyrgyzstan';
        $isocountrycode['LA'] = 'Laos';
        $isocountrycode['LV'] = 'Latvia';
        $isocountrycode['LB'] = 'Lebanon';
        $isocountrycode['LS'] = 'Lesotho';
        $isocountrycode['LR'] = 'Liberia';
        $isocountrycode['LY'] = 'Libya';
        $isocountrycode['LI'] = 'Liechtenstein';
        $isocountrycode['LT'] = 'Lithuania';
        $isocountrycode['LU'] = 'Luxembourg';
        $isocountrycode['MO'] = 'Macau';
        $isocountrycode['MK'] = 'Macedonia';
        $isocountrycode['MG'] = 'Madagascar';
        $isocountrycode['ME'] = 'Madeira';
        $isocountrycode['MW'] = 'Malawi';
        $isocountrycode['MY'] = 'Malaysia';
        $isocountrycode['MV'] = 'Maldives';
        $isocountrycode['ML'] = 'Mali';
        $isocountrycode['MT'] = 'Malta';
        $isocountrycode['MH'] = 'Marshall Islands';
        $isocountrycode['MQ'] = 'Martinique';
        $isocountrycode['MR'] = 'Mauritania';
        $isocountrycode['MU'] = 'Mauritius';
        $isocountrycode['YT'] = 'Mayotte';
        $isocountrycode['MX'] = 'Mexico';
        $isocountrycode['FM'] = 'Micronesia';
        $isocountrycode['MD'] = 'Moldova';
        $isocountrycode['MC'] = 'Monaco';
        $isocountrycode['MN'] = 'Mongolia';
        $isocountrycode['MS'] = 'Montserrat';
        $isocountrycode['MA'] = 'Morocco';
        $isocountrycode['MZ'] = 'Mozambique';
        $isocountrycode['MM'] = 'Myanmar';
        $isocountrycode['NA'] = 'Namibia';
        $isocountrycode['NR'] = 'Nauru';
        $isocountrycode['NP'] = 'Nepal';
        $isocountrycode['NL'] = 'Netherlands';
        $isocountrycode['AN'] = 'Netherlands Antilles';
        $isocountrycode['NT'] = 'Neutral Zone';
        $isocountrycode['NC'] = 'New Caledonia';
        $isocountrycode['NZ'] = 'New Zealand (Aotearoa)';
        $isocountrycode['NI'] = 'Nicaragua';
        $isocountrycode['NE'] = 'Niger';
        $isocountrycode['NG'] = 'Nigeria';
        $isocountrycode['NU'] = 'Niue';
        $isocountrycode['NF'] = 'Norfolk Island';
        $isocountrycode['KP'] = 'North Korea';
        $isocountrycode['NB'] = 'Northern Ireland';
        $isocountrycode['MP'] = 'Northern Mariana Islands';
        $isocountrycode['NO'] = 'Norway';
        $isocountrycode['OM'] = 'Oman';
        $isocountrycode['PK'] = 'Pakistan';
        $isocountrycode['PW'] = 'Palau';
        $isocountrycode['PA'] = 'Panama';
        $isocountrycode['PG'] = 'Papua New Guinea';
        $isocountrycode['PY'] = 'Paraguay';
        $isocountrycode['PE'] = 'Peru';
        $isocountrycode['PH'] = 'Philippines';
        $isocountrycode['PN'] = 'Pitcairn';
        $isocountrycode['PL'] = 'Poland';
        $isocountrycode['PO'] = 'Ponape';
        $isocountrycode['PT'] = 'Portugal';
        $isocountrycode['PR'] = 'Puerto Rico';
        $isocountrycode['QA'] = 'Qatar';
        $isocountrycode['RE'] = 'Reunion';
        $isocountrycode['RO'] = 'Romania';
        $isocountrycode['RT'] = 'Rota';
        $isocountrycode['RU'] = 'Russian Federation';
        $isocountrycode['RW'] = 'Rwanda';
        $isocountrycode['SS'] = 'Saba';
        $isocountrycode['KN'] = 'Saint Kitts and Nevis';
        $isocountrycode['LC'] = 'Saint Lucia';
        $isocountrycode['VC'] = 'Saint Vincent and the Grenadines';
        $isocountrycode['SP'] = 'Saipan';
        $isocountrycode['WS'] = 'Samoa';
        $isocountrycode['SM'] = 'San Marino';
        $isocountrycode['ST'] = 'Sao Tome and Principe';
        $isocountrycode['SA'] = 'Saudi Arabia';
        $isocountrycode['SF'] = 'Scotland';
        $isocountrycode['SN'] = 'Senegal';
        $isocountrycode['CS'] = 'Serbia and Montenegro';
        $isocountrycode['SC'] = 'Seychelles';
        $isocountrycode['SL'] = 'Sierra Leone';
        $isocountrycode['SG'] = 'Singapore';
        $isocountrycode['SK'] = 'Slovak Republic';
        $isocountrycode['SI'] = 'Slovenia';
        $isocountrycode['SB'] = 'Solomon Islands';
        $isocountrycode['SO'] = 'Somalia';
        $isocountrycode['ZA'] = 'South Africa';
        $isocountrycode['GS'] = 'South Georgia and South Sandwich Islands';
        $isocountrycode['KR'] = 'South Korea';
        $isocountrycode['ES'] = 'Spain';
        $isocountrycode['LK'] = 'Sri Lanka';
        $isocountrycode['NT'] = 'St. Barthelemy';
        $isocountrycode['SW'] = 'St. Christopher';
        $isocountrycode['VI'] = 'St. Croix';
        $isocountrycode['EU'] = 'St. Eustatius';
        $isocountrycode['SH'] = 'St. Helena';
        $isocountrycode['UV'] = 'St. John';
        $isocountrycode['KN'] = 'St. Kitts and Nevis';
        $isocountrycode['LC'] = 'St. Lucia';
        $isocountrycode['MB'] = 'St. Maarten';
        $isocountrycode['TB'] = 'St. Martin';
        $isocountrycode['PM'] = 'St. Pierre and Miquelon';
        $isocountrycode['VL'] = 'St. Thomas';
        $isocountrycode['VC'] = 'St. Vincent/Grenadine';
        $isocountrycode['SD'] = 'Sudan';
        $isocountrycode['SR'] = 'Suriname';
        $isocountrycode['SJ'] = 'Svalbard and Jan Mayen Islands';
        $isocountrycode['SZ'] = 'Swaziland';
        $isocountrycode['SE'] = 'Sweden';
        $isocountrycode['CH'] = 'Switzerland';
        $isocountrycode['SY'] = 'Syria';
        $isocountrycode['TA'] = 'Tahiti';
        $isocountrycode['TW'] = 'Taiwan';
        $isocountrycode['TJ'] = 'Tajikistan';
        $isocountrycode['TZ'] = 'Tanzania';
        $isocountrycode['TH'] = 'Thailand';
        $isocountrycode['TI'] = 'Tinian';
        $isocountrycode['TG'] = 'Togo';
        $isocountrycode['TK'] = 'Tokelau';
        $isocountrycode['TO'] = 'Tonga';
        $isocountrycode['TL'] = 'Tortola';
        $isocountrycode['TT'] = 'Trinidad and Tobago';
        $isocountrycode['TU'] = 'Truk';
        $isocountrycode['TN'] = 'Tunisia';
        $isocountrycode['TR'] = 'Turkey';
        $isocountrycode['TM'] = 'Turkmenistan';
        $isocountrycode['TC'] = 'Turks and Caicos Islands';
        $isocountrycode['TV'] = 'Tuvalu';
        $isocountrycode['UG'] = 'Uganda';
        $isocountrycode['UA'] = 'Ukraine';
        $isocountrycode['UI'] = 'Union Island';
        $isocountrycode['AE'] = 'United Arab Emirates';
        $isocountrycode['US'] = 'United States';
        $isocountrycode['UY'] = 'Uruguay';
        $isocountrycode['UM'] = 'US Minor Outlying Islands';
        $isocountrycode['SU'] = 'USSR (former)';
        $isocountrycode['UZ'] = 'Uzbekistan';
        $isocountrycode['VU'] = 'Vanuatu';
        $isocountrycode['VA'] = 'Vatican City State (Holy See)';
        $isocountrycode['VE'] = 'Venezuela';
        $isocountrycode['VN'] = 'Vietnam';
        $isocountrycode['VR'] = 'Virgin Gorda';
        $isocountrycode['VG'] = 'Virgin Islands (British)';
        $isocountrycode['VI'] = 'Virgin Islands (U.S.)';
        $isocountrycode['WL'] = 'Wales';
        $isocountrycode['WF'] = 'Wallis and Futuna Islands';
        $isocountrycode['WS'] = 'Western Samoa';
        $isocountrycode['YA'] = 'Yap';
        $isocountrycode['YE'] = 'Yemen';
        $isocountrycode['ZR'] = 'Zaire';
        $isocountrycode['ZM'] = 'Zambia';
        $isocountrycode['ZW'] = 'Zimbabwe';

        // US State Codes - resolved
        $usstatecode['AL'] = 'Alabama';
        $usstatecode['AK'] = 'Alaska';
        $usstatecode['AZ'] = 'Arizona';
        $usstatecode['AR'] = 'Arkansas';
        $usstatecode['CA'] = 'California';
        $usstatecode['CO'] = 'Colorado';
        $usstatecode['CT'] = 'Connecticut';
        $usstatecode['DE'] = 'Delaware';
        $usstatecode['DC'] = 'District of Columbia';
        $usstatecode['FL'] = 'Florida';
        $usstatecode['GA'] = 'Georgia';
        $usstatecode['HI'] = 'Hawaii';
        $usstatecode['ID'] = 'Idaho';
        $usstatecode['IL'] = 'Illinois';
        $usstatecode['IN'] = 'Indiana';
        $usstatecode['IA'] = 'Iowa';
        $usstatecode['KS'] = 'Kansas';
        $usstatecode['KY'] = 'Kentucky';
        $usstatecode['LA'] = 'Louisiana';
        $usstatecode['ME'] = 'Maine';
        $usstatecode['MD'] = 'Maryland';
        $usstatecode['MA'] = 'Massachusetts';
        $usstatecode['MI'] = 'Michigan';
        $usstatecode['MN'] = 'Minnesota';
        $usstatecode['MS'] = 'Mississippi';
        $usstatecode['MO'] = 'Missouri';
        $usstatecode['MT'] = 'Montana';
        $usstatecode['NE'] = 'Nebraska';
        $usstatecode['NV'] = 'Nevada';
        $usstatecode['NH'] = 'New Hampshire';
        $usstatecode['NJ'] = 'New Jersey';
        $usstatecode['NM'] = 'New Mexico';
        $usstatecode['NY'] = 'New York';
        $usstatecode['NC'] = 'North Carolina';
        $usstatecode['ND'] = 'North Dakota';
        $usstatecode['OH'] = 'Ohio';
        $usstatecode['OK'] = 'Oklahoma';
        $usstatecode['OR'] = 'Oregon';
        $usstatecode['PA'] = 'Pennsylvania';
        $usstatecode['RI'] = 'Rhode Island';
        $usstatecode['SC'] = 'South Carolina';
        $usstatecode['SD'] = 'South Dakota';
        $usstatecode['TN'] = 'Tennessee';
        $usstatecode['TX'] = 'Texas';
        $usstatecode['UT'] = 'Utah';
        $usstatecode['VT'] = 'Vermont';
        $usstatecode['VA'] = 'Virginia';
        $usstatecode['WA'] = 'Washington';
        $usstatecode['WV'] = 'West Virginia';
        $usstatecode['WI'] = 'Wisconsin';
        $usstatecode['WY'] = 'Wyoming';

        // Canadian State Codes - resolved
        $canadianstatecode['AB'] = 'Alberta';
        $canadianstatecode['BC'] = 'British Columbia';
        $canadianstatecode['MB'] = 'Manitoba';
        $canadianstatecode['NB'] = 'New Brunswick';
        $canadianstatecode['NL'] = 'Newfoundland';
        $canadianstatecode['NT'] = 'Northwest Territories';
        $canadianstatecode['NS'] = 'Nova Scotia';
        $canadianstatecode['NU'] = 'Nunavut';
        $canadianstatecode['ON'] = 'Ontario';
        $canadianstatecode['PE'] = 'Prince Edward Island';
        $canadianstatecode['QC'] = 'Quebec';
        $canadianstatecode['SK'] = 'Saskatchewan';
        $canadianstatecode['YT'] = 'Yukon';

        // Pickup Type Codes - not resolved yet
        $pickuptypecode['01'] = 'Daily Pickup';
        $pickuptypecode['03'] = 'Customer Counter';
        $pickuptypecode['07'] = 'On Call Air Pickup';
        $pickuptypecode['11'] = 'Suggested Retail Rates';
        $pickuptypecode['19'] = 'Letter Center';
        $pickuptypecode['20'] = 'Air Service Center';

        // Package Type Codes - not resolved yet, but resolvable
        $packagetypecode['01'] = 'UPS Letter/UPS Express Envelope';
        $packagetypecode['02'] = 'Package';
        $packagetypecode['03'] = 'UPS Tube';
        $packagetypecode['04'] = 'UPS Pak';
        $packagetypecode['21'] = 'UPS Express Box';
        $packagetypecode['24'] = 'UPS 25KG Box';
        $packagetypecode['25'] = 'UPS 10KG Box';

        // Reference Number Codes - not resolved yet
        $referencenumbercode['28'] = 'Purchase Order No.';
        $referencenumbercode['33'] = 'Model Number';
        $referencenumbercode['34'] = 'Part Number';
        $referencenumbercode['35'] = 'Serial Number';
        $referencenumbercode['50'] = 'Department Number';
        $referencenumbercode['51'] = 'Store Number';
        $referencenumbercode['54'] = 'FDA Product Code';
        $referencenumbercode['55'] = 'Acct. Rec. Customer Acct.';
        $referencenumbercode['56'] = 'Appropriation Number';
        $referencenumbercode['57'] = 'Bill of Lading Number';
        $referencenumbercode['58'] = "Employer's ID Number";
        $referencenumbercode['59'] = 'Invoice Number';
        $referencenumbercode['60'] = 'Manifest Key Number';
        $referencenumbercode['61'] = 'Dealer Order Number';
        $referencenumbercode['62'] = 'Production Code';
        $referencenumbercode['63'] = 'Purchase Req. Number';
        $referencenumbercode['64'] = 'Salesperson Number';
        $referencenumbercode['65'] = 'Social Security Number';
        $referencenumbercode['66'] = 'Fed Taxpayer ID No.';
        $referencenumbercode['67'] = 'Transaction Ref. No.';
        $referencenumbercode['RZ'] = 'RMA';
        $referencenumbercode['9V'] = 'COD Number';

        // COD Codes - not resolved yet
        $codcode['1'] = 'Regular COD';
        $codcode['2'] = 'Express COD';
        $codcode['3'] = 'Tagless COD';

        // COD Funds Codes - not resolved yet
        $codfundscode['0'] = 'All supported funds accepted';
        $codfundscode['8'] = 'Cashier’s check or money order – no cash allowed';

        // DCIS Type Codes - not resolved yet
        $dcistypecode['1'] = 'Name/Date';
        $dcistypecode['2'] = 'Signature/Date';

        // Document Codes - not resolved yet
        $documentcode['1'] = 'Letter (Document only)';
        $documentcode['2'] = 'Document (Non-letter only)';

        // Hazardous Materials Codes - not resolved yet
        $hazardousmaterialscode['0'] = 'Default';
        $hazardousmaterialscode['1'] = 'Hazardous Materials';
        $hazardousmaterialscode['2'] = 'Electronically billed Hazardous Materials';

        // Information Level Codes - not resolved yet
        $informationlevelcode['S'] = 'Shipment';
        $informationlevelcode['P'] = 'Package';

        // License Information Codes - not resolved yet
        $licenseinformationcode['APR']  = 'Items for export or re-export not controlled for nuclear nonproliferation, missile technology or crime control';
        $licenseinformationcode['AVS']  = 'U.S. aircraft on foreign sojourn into foreign country';
        $licenseinformationcode['BAG']  = 'Individual or exporting carriers crew members baggage';
        $licenseinformationcode['CIV']  = 'National security items for civil end users';
        $licenseinformationcode['CTP']  = 'Computers and computer parts';
        $licenseinformationcode['ENC']  = 'Encrypted software and hardware – financial institutions';
        $licenseinformationcode['GBS']  = 'Export or re-export to Country Group B; controlled for national security reasons';
        $licenseinformationcode['GFT']  = 'Gift shipments; packages to individuals , religious, charitable or educational, donations of basic needs';
        $licenseinformationcode['GOV']  = 'Government shipments, covers shipments for U.S. government agencies, personnel or of cooperating foreign governments';
        $licenseinformationcode['KMI']  = 'Encrypted software and hardware';
        $licenseinformationcode['LVS']  = 'Value of Shipments limited';
        $licenseinformationcode['NLR']  = 'No license';
        $licenseinformationcode['RPL']  = 'Servicing and replacement of parts and equipment, one for one replacement parts service or replacement of equipment';
        $licenseinformationcode['TMP']  = 'Temporary exports, export and re-export of beta test software';
        $licenseinformationcode['TSPA'] = 'Software or technology outside the scope of export regulations';
        $licenseinformationcode['TSR']  = 'Technology and software, national security reasons, Country Group B';
        $licenseinformationcode['TSU']  = 'Technology and software shipments, of basic requirements, data supporting prospective or actual bids, ';
        $licenseinformationcode['TSU'] .= 'offers to sell, lease or supply an item, software update for fixing programs, mass marketed software';

        // Parties To Transaction Codes - not resolved yet
        $partiestotransactioncode['R'] = 'Related';
        $partiestotransactioncode['N'] = 'Non-Related';

        // Shipper Export Declaration Codes - not resolved yet
        $shipperexportdeclarationcode['D'] = 'S.E.D. included with export documents';
        $shipperexportdeclarationcode['E'] = 'Electronically filed by the shipper';
        $shipperexportdeclarationcode['U'] = 'UPS prepared on the shipper’s behalf';
        $shipperexportdeclarationcode['Y'] = 'S.E.D. exists, but type unknown';

        // Shipment Codes - resolved
        $shipmentcode['I'] = 'In transit';
        $shipmentcode['D'] = 'Delivered';
        $shipmentcode['X'] = 'Exception';
        $shipmentcode['P'] = 'Pickup';
        $shipmentcode['M'] = 'Manifest pickup';

        // Terms Of Shipment Codes - not resolved yet
        $termsofshipmentcode['CFR'] = 'Cost and Freight';
        $termsofshipmentcode['CIF'] = 'Cost, Insurance and Freight';
        $termsofshipmentcode['CIP'] = 'Carriage and Insurance Paid';
        $termsofshipmentcode['CPT'] = 'Carriage Paid To';
        $termsofshipmentcode['DAF'] = 'Delivered at Frontier';
        $termsofshipmentcode['DDP'] = 'Delivered Duty Paid';
        $termsofshipmentcode['DDU'] = 'Delivered Duty Unpaid';
        $termsofshipmentcode['DEQ'] = 'Delivered Ex Quay';
        $termsofshipmentcode['DES'] = 'Delivered Ex Ship';
        $termsofshipmentcode['EXW'] = 'Ex Works';
        $termsofshipmentcode['FAS'] = 'Free Along Side';
        $termsofshipmentcode['FCA'] = 'Free Carrier';
        $termsofshipmentcode['FOB'] = 'Free On Board';

        // Trade Agreement Type Codes - not resolved yet
        $tradeagreementtypecode['EEC']   = 'European Economic Community';
        $tradeagreementtypecode['EFTA']  = 'European Free Trade Agreement';
        $tradeagreementtypecode['NAFTA'] = 'North American Free Trade Agreement';

        // UPS Returned Data - not resolved yet
        $upsreturneddata['01'] = 'Daily Pickup';
        $upsreturneddata['03'] = 'Customer Counter';
        $upsreturneddata['07'] = 'On Call Air Pickup';
        $upsreturneddata['11'] = 'Suggested Retail Rates';
        $upsreturneddata['19'] = 'Letter Center';
        $upsreturneddata['20'] = 'Air Service Center';

		//Package delivery services. By originating countries.
		$this->packagedelivery['US']['01'] = 'Next Day Air';
		$this->packagedelivery['US']['02'] = 'Second Day Air';
		$this->packagedelivery['US']['03'] = 'Ground';
		$this->packagedelivery['US']['07'] = 'Worldwide Express';
		$this->packagedelivery['US']['08'] = 'Worldwide Expedited';
		$this->packagedelivery['US']['11'] = 'Standard';
		$this->packagedelivery['US']['12'] = 'Three-Day Select';
		$this->packagedelivery['US']['13'] = 'Next Day Air Saver';
		$this->packagedelivery['US']['14'] = 'Next Day Air Early A.M.';
		$this->packagedelivery['US']['54'] = 'Worldwide Express Plus';
		$this->packagedelivery['US']['59'] = 'Second Day Air A.M.';
		$this->packagedelivery['US']['65'] = 'Saver';

		$this->packagedelivery['CA']['01'] = 'Express';
		$this->packagedelivery['CA']['02'] = 'Expedited';
		$this->packagedelivery['CA']['07'] = 'Worldwide Express';
		$this->packagedelivery['CA']['08'] = 'Worldwide Expedited';
		$this->packagedelivery['CA']['11'] = 'Standard';
		$this->packagedelivery['CA']['12'] = 'Three-Day Select';
		$this->packagedelivery['CA']['13'] = 'Next Day Air Saver';
		$this->packagedelivery['CA']['14'] = 'Express Early A.M.';
		$this->packagedelivery['CA']['54'] = 'Worldwide Express Plus';
		$this->packagedelivery['CA']['65'] = 'Express Saver';

/*
		$this->packagedelivery['82'] = 'UPS Today Standard';
		$this->packagedelivery['83'] = 'UPS Today Dedicated Courrier';
		$this->packagedelivery['84'] = 'UPS Today Intercity';
		$this->packagedelivery['85'] = 'UPS Today Express';
		$this->packagedelivery['86'] = 'UPS Today Express Saver';
*/
    }

    /**
     * output functions for testing purposes only
     */
    function output_array($array, $string="")
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $string .= htmlentities($key).'<br>';
                $output .= $this->output_array($value, $string);
            } else {
                $output .= '<tr>';
                $output .= '<td><b>'.$string.htmlentities($key).'</b></td>';
                $output .= '<td width="100%">'.htmlentities($value);
                $output .= '</td>';
                $output .= '</tr>';
            }
        }
        return $output;
    }

    function output_table($array)
    {
        $output .= '<table align="center" border="1" width="100%">';
        $output .= '<tr>';
        $output .= '<td align="center"><b>Array Keys</b></td>';
        $output .= '<td align="center"><b>Array Value</b></td>';
        $output .= '</tr>';
        $output .= $this->output_array($array);
        $output .= '</table>';
        return $output;
    }

    /**
     * PHP5 destructor
     * @access private
     */
    function __destruct()
    {
    }
}
?>
