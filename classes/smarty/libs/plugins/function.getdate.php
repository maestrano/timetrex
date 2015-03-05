<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_getDate($params, &$smarty)
{
   
    $type = $params['type'];
    $epoch = $params['epoch'];
    $nodst = $params['nodst'];
    //var_dump($epoch);
    //echo "Epoch: $epoch<br>\n";
    
    $default = $params['default'];
    //var_dump($default);
    if ($default == 'TRUE') {
        $default = '--';   
    } else {
        $default = NULL;   
    }

    if ( (int)$epoch == 0 ) {
        return $default;
    }
    $retval = TTDate::getDate($type, $epoch, $nodst);
    
    return $retval;
}

/* vim: set expandtab: */

?>
