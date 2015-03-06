<?php

// Tested on PHP 5.2, 5.3

// Check dependencies
if (!function_exists('curl_init')) {
  throw new Exception('Maestrano needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Maestrano needs the JSON PHP extension.');
}
if (!function_exists('mb_detect_encoding')) {
  throw new Exception('Maestrano needs the Multibyte String PHP extension.');
}

// Maestrano wrapper
require_once(dirname(__FILE__) . '/Maestrano/Maestrano.php');

// XMLSEC Libs
require_once(dirname(__FILE__) . '/Maestrano/Xmlseclibs/xmlseclibs.php');

// Maestrano NET
require_once(dirname(__FILE__) . '/Maestrano/Net/HttpClient.php');

// Maestrano Helpers
require_once(dirname(__FILE__) . '/Maestrano/Helper/DateTime.php');

// SAML
require_once(dirname(__FILE__) . '/Maestrano/Saml/XmlSec.php');
require_once(dirname(__FILE__) . '/Maestrano/Saml/Settings.php');
require_once(dirname(__FILE__) . '/Maestrano/Saml/Request.php');
require_once(dirname(__FILE__) . '/Maestrano/Saml/Response.php');

// Util
require_once(dirname(__FILE__) . '/Maestrano/Util/Set.php');

// SSO
require_once(dirname(__FILE__) . '/Maestrano/Sso/Service.php');
require_once(dirname(__FILE__) . '/Maestrano/Sso/Session.php');
require_once(dirname(__FILE__) . '/Maestrano/Sso/User.php');
require_once(dirname(__FILE__) . '/Maestrano/Sso/Group.php');

// Api
require_once(dirname(__FILE__) . '/Maestrano/Api/Object.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/Error.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/AttachedObject.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/ConnectionError.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/InvalidRequestError.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/Requestor.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/Util.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/AuthenticationError.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/Resource.php');

// Account API
require_once(dirname(__FILE__) . '/Maestrano/Account/Bill.php');
require_once(dirname(__FILE__) . '/Maestrano/Account/RecurringBill.php');
require_once(dirname(__FILE__) . '/Maestrano/Account/Group.php');
require_once(dirname(__FILE__) . '/Maestrano/Account/User.php');

// Connec Client
require_once(dirname(__FILE__) . '/Maestrano/Connec/Client.php');
