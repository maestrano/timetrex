<?php
/**
 * This controller creates a SAML request and redirects to
 * Maestrano SAML Identity Provider
 *
 */

//-----------------------------------------------
// Define root folder
//-----------------------------------------------
define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));

error_reporting(0);

require MAESTRANO_ROOT . '/app/init/auth.php';

// Get Maestrano Service
$maestrano = MaestranoService::getInstance();

// Build SAML request and Redirect to IDP
$authRequest = new OneLogin_Saml_AuthRequest($maestrano->getSettings()->getSamlSettings());
$url = $authRequest->getRedirectUrl();

header("Location: $url");