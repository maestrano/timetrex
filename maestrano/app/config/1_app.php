<?php
// Get full host (protocal + server host)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$full_host = $protocol . $_SERVER['HTTP_HOST'];

// Name of your application
$mno_settings->app_name = 'my-app';

// Enable Maestrano SSO for this app
$mno_settings->sso_enabled = true;

// SSO initialization URL
$mno_settings->sso_init_url = $full_host . '/maestrano/auth/saml/index.php';

// SSO processing url
$mno_settings->sso_return_url = $full_host . '/maestrano/auth/saml/consume.php';