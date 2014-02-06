<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_DisplayBreadCrumbs($params, &$smarty)
{
    return BreadCrumb::Display();
}

/* vim: set expandtab: */

?>
