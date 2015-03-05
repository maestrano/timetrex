<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_js_array($params, &$smarty)
{	
	return Misc::getJSArray( $params['values'], $params['name'], $params['assoc'], $params['object']);
}

/* vim: set expandtab: */
?>
