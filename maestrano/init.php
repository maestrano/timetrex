<?php

if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__FILE__) . '/../'); }

// Include Maestrano required libraries
require_once ROOT_PATH . '/vendor/autoload.php';
Maestrano::configure(ROOT_PATH . '/maestrano.json');
require_once 'app/sso/MnoSsoUser.php';

// TimeTrex application
require_once ROOT_PATH . '/includes/global.inc.php';
