<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_EmbeddedDocumentAttachmentList($params, &$smarty)
{

    $object_type_id = $params['object_type_id'];
    $object_id = $params['object_id'];
    $height = $params['height'];

    if ( empty($height) ) {
        $height = 75;
    }
    $url = URLBuilder::getURL( array('object_type_id' => $object_type_id, 'object_id' => $object_id), Environment::getBaseURL().'/document/EmbeddedDocumentAttachmentList.php' );
    $retval = '<iframe style="width:100%; height:'.$height.'px; border: 5px" id="DocumentAttachmentFactory" name="DocumentAttachmentFactory" src="'.$url.'"></iframe>';

    return $retval;
}

/* vim: set expandtab: */

?>
