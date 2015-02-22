<?php
//define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));

//-----------------------------------------------
// Require dependencies
//-----------------------------------------------
define('PHP_SAML_XMLSECLIBS_DIR', MAESTRANO_ROOT . '/lib/php-saml/ext/xmlseclibs/');
require PHP_SAML_XMLSECLIBS_DIR . 'xmlseclibs.php';

define('PHP_SAML_DIR', MAESTRANO_ROOT . '/lib/php-saml/src/OneLogin/Saml/');
require PHP_SAML_DIR . 'AuthRequest.php';
require PHP_SAML_DIR . 'Response.php';
require PHP_SAML_DIR . 'Settings.php';
require PHP_SAML_DIR . 'XmlSec.php';

//-----------------------------------------------
// Require Maestrano library
//-----------------------------------------------
define('MNO_PHP_DIR', MAESTRANO_ROOT . '/lib/mno-php/src/');
require MNO_PHP_DIR . 'MnoSettings.php';
require MNO_PHP_DIR . 'MaestranoService.php';
require MNO_PHP_DIR . 'sso/MnoSsoBaseUser.php';
require MNO_PHP_DIR . 'sso/MnoSsoSession.php';

//-----------------------------------------------
// Require Maestrano app files
//-----------------------------------------------
define('MNO_APP_DIR', MAESTRANO_ROOT . '/app/');
require MNO_APP_DIR . '/sso/MnoSsoUser.php';