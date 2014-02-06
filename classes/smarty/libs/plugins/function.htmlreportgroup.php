<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_htmlReportGroup($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('function','html_options');

    $filter_data = $params['filter_data'];
    if ( isset($params['total']) ) {
        $total = $params['total'];
    } else {
        $total = 2;
    }

$retval .= '<tr onClick="showHelpEntry(\'group_by\')">
    <td colspan="2" class="cellLeftEditTableHeader">
        ' . TTi18n::gettext('Group By:') . '
    </td>
    <td class="cellRightEditTable">
        <select id="columns" name="filter_data[primary_group_by]">
            '. smarty_function_html_options( array('options' => $filter_data['group_by_options'], 'selected' => $filter_data['primary_group_by'] ), $smarty ) .'
        </select>';
if ( $total >= 2 ) {
    $retval .= '
        <b>' . TTi18n::gettext('then:') . '</b>
        <select id="columns" name="filter_data[secondary_group_by]">
            '. smarty_function_html_options( array('options' => $filter_data['group_by_options'], 'selected' => $filter_data['secondary_group_by'] ), $smarty ) .'
        </select>';
}
if ( $total >= 3 ) {
    $retval .= '
        <br>
        <select id="columns" name="filter_data[tertiary_group_by]">
            '. smarty_function_html_options( array('options' => $filter_data['group_by_options'], 'selected' => $filter_data['tertiary_group_by'] ), $smarty ) .'
        </select>';
}
if ( $total >= 4 ) {
    $retval .= '
        <b>' . TTi18n::gettext('then:') . '</b>
        <select id="columns" name="filter_data[quaternary_group_by]">
            '. smarty_function_html_options( array('options' => $filter_data['group_by_options'], 'selected' => $filter_data['quaternary_group_by'] ), $smarty ) .'
        </select>';
}

$retval .= '
    </td>
</tr>
';

    return $retval;
}

/* vim: set expandtab: */

?>
