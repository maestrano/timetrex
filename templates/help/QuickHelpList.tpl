{* <div id="quick_help" style="position:absolute; left:0; width:250; height:100; visibility:hidden;border: 1px solid black;"> *}
<script type="text/javascript">
{literal}
function editHelpGroupControl() {
{/literal}
	window.location = '../help/EditHelpGroupControl.php?script={$SCRIPT_BASE_NAME}&name='+ help_object_id;
{literal}
}
{/literal}

{literal}
function editHelpGroup() {
{/literal}
	window.location = '../help/EditHelpGroup.php?script={$SCRIPT_BASE_NAME}&name='+ help_object_id;
{literal}
}
{/literal}

{literal}
function editHelpEntry(id) {
{/literal}
	window.location = '../help/EditHelp.php?id='+ id;
{literal}
}
{/literal}

</script>
<div id="quick_help" style="position:absolute; left:0; width:0; height:0; visibility:hidden;z-index: 10">
	<table id="quick_help_table" width="100%" height="100%" border="0" bgcolor="#000000" cellspacing="1" cellpadding="2">
	<tr class="tblHeader">
		<td align="left" height="1">
			{t}Position:{/t} <a href="javascript:QuickHelpWindowPosistion('left');">Left</a> | <a href="javascript:QuickHelpWindowPosistion('right');">{t}Right{/t}</a> | <a href="javascript:QuickHelpWindowPosistion('bottom');">{t}Bottom{/t}</a>
		</td>
		<td align="right" width="1">
			<a href="javascript:hideQuickHelpWindow()" style="text-decoration: none"><b>{t}X{/t}</b></a>
		</td>
	</tr>

	<tbody id="help-default">
		<tr class="tblDataGreyNH">
			<td colspan="2">
				{t}Click the item you wish to get help on.{/t}
				<br>
				<br>
				{t}You may use the position links at the top of this window to move it around the screen,
				or to close the window press the X in the upper right of this window, or press the ESC key.{/t}
			</td>
		</tr>
	</tbody>

	<tbody id="help-missing" style="display:none">
		<tr class="tblDataGreyNH">
			<td colspan="2">
				{t}Sorry, no help is available for that item yet.{/t}
				{if $permission->Check('help','add') OR $permission->Check('help','edit')}
				<br>

				[ <a href="javascript:editHelpGroupControl()">{t}Help Group Control{/t}</a> ]
				[ <a href="javascript:editHelpGroup()">{t}Help Group{/t}</a> ]
				{/if}
			</td>
		</tr>
	</tbody>

	{foreach from=$quick_help_entries item=quick_help_entry}
		<tbody id="help-{$quick_help_entry.group_name}" style="display:none" >
			{foreach from=$quick_help_entry.entries item=group_entry}
				<tr class="tblDataGreyNH" style="text-align: left;vertical-align: top;">
					<td colspan="2">
						{if $group_entry.heading != ''}<b>{$group_entry.heading}:</b><br>{/if}
						{$group_entry.body|nl2br}
						{if $permission->Check('help','add') OR $permission->Check('help','edit')}
						<br>
						[ <a href="javascript:editHelpEntry({$group_entry.id})">{t}Edit{/t}</a> ]
						[ <a href="javascript:editHelpGroup()">{t}Help Group{/t}</a> ]
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	{/foreach}

	</table>
</div>
