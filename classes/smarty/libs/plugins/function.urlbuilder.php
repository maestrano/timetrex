<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_URLBuilder($params, &$smarty)
{

    $values = $params['values'];
    $script = $params['script'];
    $merge = strtolower(trim( $params['merge']) );

    if ($merge == 'false') {
        $merge = FALSE;
    } else {
        $merge = TRUE;
    }

/*
    if ( empty($script) ) {
        $smarty->trigger_error("URLBuilder: missing 'script' parameter");
    }
*/
    if ( !empty($values) ) {
        $values_arr = explode(",", $values);

        $url_builder_arr = array();

        foreach ($values_arr as $value_pair) {
            $split_pairs = explode("=", $value_pair);

            $url_builder_arr[$split_pairs[0]] = $split_pairs[1];
        }

        $retval = URLBuilder::getURL($url_builder_arr, $script, $merge);
    } else {
        $retval = URLBuilder::getURL(NULL,$script);
    }

    //Debug::Text('URL: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

    return $retval;
}

/* vim: set expandtab: */

?>
