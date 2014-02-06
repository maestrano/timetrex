<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_formatCurrency($params, &$smarty)
{
   
    $amount = $params['amount'];
    $currency_code = $params['currency_code'];
    $show_code = $params['show_code'];
    
    return TTi18n::formatCurrency( $amount, $currency_code, $show_code );    
}

/* vim: set expandtab: */

?>
