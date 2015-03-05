<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_htmlReportSort($params, &$smarty)
{   
    require_once $smarty->_get_plugin_filepath('function','html_options');
   
    $filter_data = $params['filter_data'];
    
$retval .= '<tr onClick="showHelpEntry(\'sort\')">
    <td colspan="2" class="cellLeftEditTableHeader">
        ' . TTi18n::gettext('Sort By:') . '
    </td>
    <td class="cellRightEditTable">
        <select id="columns" name="filter_data[primary_sort]">
            {html_options options=$filter_data.sort_options selected=$filter_data.primary_sort}
            '. smarty_function_html_options( array('options' => $filter_data['sort_options'], 'selected' => $filter_data['primary_sort'] ), $smarty ) .'            
        </select>
        <select id="columns" name="filter_data[primary_sort_dir]">
            '. smarty_function_html_options( array('options' => $filter_data['sort_direction_options'], 'selected' => $filter_data['primary_sort_dir'] ), $smarty ) .'            
        </select>
        <b>' . TTi18n::gettext('then:') . '</b>
        <select id="columns" name="filter_data[secondary_sort]">
            '. smarty_function_html_options( array('options' => $filter_data['sort_options'], 'selected' => $filter_data['secondary_sort'] ), $smarty ) .'            
        </select>
        <select id="columns" name="filter_data[secondary_sort_dir]">
            '. smarty_function_html_options( array('options' => $filter_data['sort_direction_options'], 'selected' => $filter_data['secondary_sort_dir'] ), $smarty ) .'                        
        </select>

    </td>
</tr>
';

    return $retval;
}

/* vim: set expandtab: */

?>
