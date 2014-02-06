<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_htmlReportSave($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('function','html_options');
    require_once $smarty->_get_plugin_filepath('function','isvalid');

    $generic_data = $params['generic_data'];
    $object = $params['object'];
    if ( isset($params['button_prefix']) ) {
        $submit_button_prefix = $params['button_prefix'].':';
    } else {
        $submit_button_prefix = 'action:';
    }

    $onclick_html = $params['onclick'];

    if ( isset($params['action_element_id']) ) {
        $action_element_id = $params['action_element_id'];
    } else {
        $action_element_id = 'action';
    }

$retval .= '<tr>
    <td colspan="2" class="'. smarty_function_isvalid( array('object' => $object, 'label' => 'name', 'value' => 'cellLeftEditTable'), $smarty) .'">
        ' . TTi18n::gettext('Name:') . '
    </td>
    <td class="cellRightEditTable">
        <input type="text" name="generic_data[name]" value="'. $generic_data['name'] .'">

        <select id="generic_id" name="generic_data[id]" onChange="'. $onclick_html .'; this.form.target = \'_self\';document.getElementById(\''. $action_element_id .'\').name = \''. $submit_button_prefix .'load\';document.getElementById(\''. $action_element_id .'\').value = \'Load\'; this.form.submit()">
            '. smarty_function_html_options( array('options' => $generic_data['saved_report_options'], 'selected' => $generic_data['id'] ), $smarty ) .'
        </select>

        ' . TTi18n::gettext('Default:') . ' <input type="checkbox" class="checkbox" name="generic_data[is_default]" value="1">

        <input type="BUTTON" name="'.$submit_button_prefix.'action" value="' . TTi18n::gettext('Save') . '" onClick="selectAllReportCriteria(); '. $onclick_html .'; this.form.target = \'_self\';document.getElementById(\''. $action_element_id .'\').name = \''. $submit_button_prefix .'save\'; document.getElementById(\''. $action_element_id .'\').value = \'Save\'; this.form.submit()">
        <input type="BUTTON" name="'.$submit_button_prefix.'action" value="' . TTi18n::gettext('Delete') . '" onClick="'. $onclick_html .'; this.form.target = \'_self\';document.getElementById(\''. $action_element_id .'\').name = \''. $submit_button_prefix .'delete\'; document.getElementById(\''. $action_element_id .'\').value = \'Delete\'; this.form.submit()">
    </td>
</tr>
';

    return $retval;
}

/* vim: set expandtab: */

?>
