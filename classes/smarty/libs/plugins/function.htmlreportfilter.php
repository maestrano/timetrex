<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_htmlReportFilter($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('function','html_options');
    require_once $smarty->_get_plugin_filepath('function','select_size');

    $filter_data = $params['filter_data'];
    $label = $params['label'];
    $colspan = $params['colspan'];
    $date_type = $params['date_type'];
    $order = $params['order'];
    $display_name = TTi18n::gettext($params['display_name']);
    $display_plural_name = TTi18n::gettext($params['display_plural_name']);

    //Defines type of filter criteria so we can customize it more.
    //Possible types: job
    $type = $params['type'];

    $src_select_box_size = smarty_function_select_size( array('array' => $filter_data['src_'. $label .'_options'], 'min' => 2 ), $smarty );
    $select_box_size = smarty_function_select_size( array('array' => $filter_data['selected_'. $label .'_options'], 'min' => 2 ), $smarty );
    if ( $src_select_box_size > $select_box_size ) {
        $max_select_box_size = $src_select_box_size;
    } else {
        $max_select_box_size = $select_box_size;
    }

    if ( $max_select_box_size <= 2 ) {
        $max_select_box_size = 2;
    }

    if ( $colspan == '' ) {
        $colspan = 2;
    }

    $type_src_html = NULL;
    $type_dst_html = NULL;
    if ( $type == 'job' ) {
        $type_src_html = '<b>Code:</b> <input type="text" size="4" id="src_quick_job_id_'. $label .'" onKeyUp="TIMETREX.punch.selectJobOption( \'src_quick_job_id_'. $label .'\', \'src_filter_'. $label .'\' );">';
        $type_dst_html = '<b>Code:</b> <input type="text" size="4" id="quick_job_id_'. $label .'" onKeyUp="TIMETREX.punch.selectJobOption( \'quick_job_id_'. $label .'\', \'filter_'. $label .'\' );">';
    }

    $order_html = NULL;
    if ( $order == TRUE ) {
        $order_html = '<br><br><a href="javascript:select_item_move_up(document.getElementById(\'filter_'. $label .'\') );"><img style="vertical-align: middle" src="'. Environment::getImagesURL().'nav_up.gif"></a><a href="javascript:select_item_move_down(document.getElementById(\'filter_'. $label .'\') );"><img style="vertical-align: middle" src="'. Environment::getImagesURL().'nav_down.gif"></a>';
    }

    $retval = '<tr>';

    if ( $date_type == TRUE ) {
        $colspan = 1;
        if ( $filter_data['date_type'] == $label.'_ids' ) {
            $date_type_selected = 'checked';
        } else {
            $date_type_selected = NULL;
        }

        $retval .= '<td class="cellReportRadioColumn">
    <input type="radio" class="checkbox" id="date_type_'. $label .'" name="filter_data[date_type]" value="'.$label.'_ids" onClick="showReportDateType();" '. $date_type_selected .'>
</td>
';
    }

$retval .= '
    <td colspan="'. $colspan .'" class="cellLeftEditTableHeader" nowrap>
        <b>'. $display_name .':</b><a href="javascript:toggleReportCriteria(\'filter_'. $label.'\');"><img style="vertical-align: middle" id="filter_'. $label .'_img" src="'. Environment::getImagesURL().'nav_bottom_sm.gif"></a>
    </td>
    <td id="filter_'. $label .'_right_cell" class="cellRightEditTableHeader">
        <div id="filter_'. $label .'_on" style="display:none">
            <table class="editTable">
                <tr class="tblHeader">
                    <td>
                        ' . sprintf(TTi18n::gettext('UnSelected %1$s'),  $display_plural_name) .'
                    </td>
                    <td>
                        <br>
                    </td>
                    <td>
                        ' . sprintf(TTi18n::gettext('Selected %1$s'),  $display_plural_name) .'
                    </td>
                </tr>
                <tr>
                    <td class="cellRightEditTable" width="50%" align="center">
                        '. $type_src_html .'
                        <input type="button" name="Select All" value="' . TTi18n::gettext('Select All') . '" onClick="selectAll(document.getElementById(\'src_filter_'. $label .'\'))">
                        <input type="button" name="Un-Select" value="' . TTi18n::gettext('Un-Select All') . '" onClick="unselectAll(document.getElementById(\'src_filter_'. $label .'\'))">
                        <br>
                        <select id="src_filter_'. $label .'" style="width:90%;margin:5px 0 5px 0;" size="'. $src_select_box_size .'" multiple>
                            '. smarty_function_html_options( array('options' => $filter_data['src_'. $label .'_options'] ), $smarty ) .'
                        </select>
                    </td>
                    <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                        <a href="javascript:moveReportCriteriaItems(\'src_filter_'. $label .'\', \'filter_'. $label .'\', '. $max_select_box_size .', true, \'value\' );"><img style="vertical-align: middle" src="'. Environment::getImagesURL().'nav_last.gif"></a>
                        <a href="javascript:moveReportCriteriaItems(\'filter_'. $label .'\', \'src_filter_'. $label .'\', '. $max_select_box_size .', true, \'value\' );"><img style="vertical-align: middle" src="'. Environment::getImagesURL().'nav_first.gif"></a>
                        '. $order_html .'
                    </td>
                    <td class="cellRightEditTable" align="center">
                        '. $type_dst_html .'
                        <input type="button" name="Select All" value="' . TTi18n::gettext('Select All') . '" onClick="selectAll(document.getElementById(\'filter_'. $label .'\'))">
                        <input type="button" name="Un-Select" value="' . TTi18n::gettext('Un-Select All') . '" onClick="unselectAll(document.getElementById(\'filter_'. $label .'\'))">
                        <br>
                        <select name="filter_data['.$label.'_ids][]" id="filter_'. $label .'" style="width:90%;margin:5px 0 5px 0;" size="'. $select_box_size.'" multiple>
                            '. smarty_function_html_options( array('options' => $filter_data['selected_'. $label .'_options'], 'selected' => $filter_data['selected_'. $label .'_options'] ), $smarty ) .'
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div id="filter_'. $label .'_off" >
            <span id="filter_'. $label .'_count">' . TTi18n::gettext('N/A') . '</span> ' . TTi18n::gettext('currently selected, click the arrow to modify.' ) . '
        </div>
    </td>
</tr>
';

    return $retval;
}

/* vim: set expandtab: */

?>
