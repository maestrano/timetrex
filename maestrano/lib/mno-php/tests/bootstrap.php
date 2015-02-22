<?php

define('TEST_ROOT', __DIR__);

// Dependency: php-saml
define('PHP_SAML_DIR', './../../php-saml/src/OneLogin/Saml/');
require PHP_SAML_DIR . 'AuthRequest.php';
require PHP_SAML_DIR . 'Response.php';
require PHP_SAML_DIR . 'Settings.php';
require PHP_SAML_DIR . 'XmlSec.php';

// Require Settings
require './../src/MnoSettings.php';

// Load tested library: SSO
define('MNO_PHP_SSO_DIR', './../src/sso/');
require MNO_PHP_SSO_DIR . 'MnoSsoBaseUser.php';
require MNO_PHP_SSO_DIR . 'MnoSsoSession.php';

// Set timezone
date_default_timezone_set('UTC');