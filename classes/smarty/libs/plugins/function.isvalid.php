<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_isvalid($params, &$smarty)
{

    $object = $params['object'];
    $value = $params['value'];


    $label = $params['label'];
    if ( strstr($label,",") ) {
        $labels = explode(",", $label);
    } else {
        $labels = array($label);
    }

    //Debug::Arr($labels, 'Labels: ', __FILE__, __LINE__, __METHOD__, 10);

    $error_value = $params['error_value'];
    if (empty($error_value)) {
        $error_value = $value.'_error';
    }

    //If even one label is invalid, return the error label.
    foreach ($labels as $label) {
        if ( is_object( $smarty->_tpl_vars[$object]->Validator )
            AND $smarty->_tpl_vars[$object]->Validator->isValid($label) == FALSE) {
            return $error_value;
        }
    }

    return $value;
}
/* vim: set expandtab: */

?>
