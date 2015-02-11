<?php

// Initialize mno_settings variable
$mno_settings = new MnoSettings();

// Require Config files
require MAESTRANO_ROOT . '/app/config/1_app.php';
require MAESTRANO_ROOT . '/app/config/2_maestrano.php';

// Configure Maestrano Service
MaestranoService::configure($mno_settings);