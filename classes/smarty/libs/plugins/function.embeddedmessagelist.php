<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_EmbeddedMessageList($params, &$smarty)
{

    $object_type_id = $params['object_type_id'];
    $object_id = $params['object_id'];
    $object_user_id = ( isset($params['object_user_id'])  ) ? $params['object_user_id'] : FALSE;
    $height = $params['height'];

    if ( empty($height) ) {
        $height = 250;
    }
    //urlbuilder script="../message/EmbeddedMessageList.php" values="object_type_id=10,object_id=$default_schedule_control_id" merge="FALSE"}
    $url = URLBuilder::getURL( array('object_type_id' => $object_type_id, 'object_id' => $object_id, 'object_user_id' => $object_user_id), Environment::getBaseURL().'/message/EmbeddedMessageList.php' );
    //$retval = '<iframe style="width:100%; height:'.$height.'px; border: 0px" id="MessageFactory" name="MessageFactory" src="'.$url.'#form_start"></iframe>';
    $retval = '<iframe style="width:100%; height:'.$height.'px; border: 5px" id="MessageFactory" name="MessageFactory" src="'.$url.'"></iframe>';

    return $retval;
}

/* vim: set expandtab: */

?>
