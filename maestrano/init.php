<?php

if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__FILE__) . '/../'); }

// Configuration
require_once 'app/config/1_app.php';
require_once 'app/config/2_maestrano.php';

// Include Maestrano required libraries
require_once ROOT_PATH . '/vendor/autoload.php';
Maestrano::configure(ROOT_PATH . '/maestrano.json');

// TimeTrex application
require_once ROOT_PATH . '/includes/global.inc.php';

require_once 'app/sso/MnoSsoUser.php';
require_once 'connec/init.php';
