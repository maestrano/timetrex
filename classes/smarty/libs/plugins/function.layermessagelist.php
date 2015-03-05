<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_LayerMessageList($params, &$smarty)
{

    $object_type_id = $params['object_type_id'];
    $object_id = $params['object_id'];
    $height = $params['height'];

	if ( $object_type_id == '' ) {
		return FALSE;
	}

	if ( $object_id == '' ) {
		return FALSE;
	}

    if ( empty($height) ) {
        $height = 335;
    }
    $url = URLBuilder::getURL( array('template' => 1, 'object_type_id' => $object_type_id, 'object_id' => $object_id), Environment::getBaseURL().'/message/EmbeddedMessageList.php' );

    $retval = '
<div id="MessageFactoryLayer" style="background:#000000;visibility:hidden; position: absolute; left: 5000px; top: 130px; width: 90%; height:'. $height .'px">
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">Messages</span></div></div>
</div>
<div id="rowContentInner">
	<div id="contentBoxTwoEdit">
			<table class="tblList">
				<tr>
					<td>
    ';

    $retval .= '<iframe style="width:100%; height:'.$height.'px; border: 5px" id="LayerMessageFactoryFrame" name="LayerMessageFactoryFrame" src="'.$url.'"></iframe>';

    $retval .= '
					</td>
				</tr>
			</table>
	</div>
</div>
</div>
';

    return $retval;
}

/* vim: set expandtab: */

?>
