<?php

// Run scripts located under maestrano/init/scripts
// Scripts are run only once on application initialize

// Run init scripts
$init_script_file = ROOT_PATH . '/maestrano/var/_init_scripts';
$init_script_content = file_get_contents($init_script_file);
$script_dirs = ROOT_PATH . '/maestrano/scripts/scripts';
$script_files = array_diff(scandir($script_dirs), array('..', '.'));

// Iterate over already loaded scripts
foreach ($script_files as $script_file) {
  $contained = strpos($init_script_content, $script_file);
  if($contained === false) {
    // Run script file
    require_once($script_dirs . "/" . $script_file);
    file_put_contents($init_script_file, $script_file . "\n", FILE_APPEND);
  }
}