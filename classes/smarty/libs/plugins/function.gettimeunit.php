<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_gettimeunit($params, &$smarty)
{
   
    $value = $params['value'];

    $default = $params['default'];
    $abs = $params['abs'];
    //var_dump($default);
    if ($default == 'TRUE') {
        $default = 'N/A';
    } elseif ( $default == '0' ) {
        if ( $value === FALSE OR $value === NULL) {
            $value = 0;
        }
    } else {
        $default = NULL;   
    }
    
    if ( $abs == 'TRUE' ) {
        $value = abs($value);
    }
    //var_dump($value);
    //Make sure the default is set to TRUE to get "N/A"
    if ( $value === FALSE OR $value === NULL) {
        return $default;
    }

    $retval = TTDate::getTimeUnit($value);
    
    return $retval;
}

/* vim: set expandtab: */

?>
