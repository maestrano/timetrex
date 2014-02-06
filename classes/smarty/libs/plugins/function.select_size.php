<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_select_size($params, &$smarty)
{
    global $current_user_prefs;

    //Ignore this for Mozilla browsers?
    $array = $params['array'];
    $array2 = $params['array2'];
    $min = $params['min'];
    $max = $params['max'];

    $total = count($array);
    $total2 = count($array2);

    //Debug::text('Min: '. $min .' Max: '. $max .' Total: '.$total .' Total2: '. $total2, __FILE__, __LINE__, __METHOD__, 10);
    $total = $total+$total2;

    $retval = ceil($total / 3);

    if ( $min != '' AND $retval < $min ) {
        $retval = $min;
    } elseif ( $max != '' AND $retval > $max ) {
        $retval = $max;
    }

    //No point in having the select box larger then the browser screen.
    if ( $total > 0 AND $retval < 5 ) {
        if ( $total < 5 ) {
            $retval = $total;
        } else {
            $retval = 5;
        }
    } elseif ( $total == 0 ) {
        $retval = 2;
    }
    if ( $retval > 30 ) {
        $retval = 30;
    }
    //Debug::text('Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

    return ceil($retval);
}

/* vim: set expandtab: */

?>
