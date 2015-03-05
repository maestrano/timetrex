<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_getQuickHelpData($params, &$smarty)
{
	global $permission;

	if ( $permission->Check('help','enabled')
			AND ( $permission->Check('help','view') OR $permission->Check('help','view_own') ) ) {

		/*
		$split_script = explode('/', $_SERVER['SCRIPT_NAME']);
		$script = $split_script[count($split_script)-1];
		*/
		$script = basename($_SERVER['SCRIPT_NAME']);

		$hlf = new HelpListFactory();

		$hlf->getByScriptNameAndType($script, 'Form');

		foreach ($hlf as $help_obj) {
			$tmp_entries[$help_obj->getColumn('group_name')][] = array(
																'id' => $help_obj->GetId(),
																'group_name' => $help_obj->getColumn('group_name'),
																'heading' => $help_obj->getHeading(),
																'body' => $help_obj->getBody()
																);
		}
		//var_dump($tmp_entries);

		if ( isset($tmp_entries) ) {
			foreach ($tmp_entries as $group_name => $value) {
				$help_entries[] = array(
								'group_name' => $group_name,
								'entries' => $value
								);
			}
		}
		//var_dump($help_entries);
		$smarty->assign_by_ref('SCRIPT_BASE_NAME', $script);
		$smarty->assign_by_ref('quick_help_entries', $help_entries);
		$smarty->display('help/QuickHelpList.tpl');
	}
}

/* vim: set expandtab: */

?>
