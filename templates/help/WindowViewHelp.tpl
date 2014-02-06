<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

<link rel="stylesheet" href="{$BASE_URL}global.css.php" type="text/css" />
<title>{$APPLICATION_NAME} - Help</title>

<script type="text/javascript">
{literal}
function editHelpGroupControl() {
{/literal}
	self.opener.location = '../help/EditHelpGroupControl.php?script={$SCRIPT_BASE_NAME}';
{literal}
}
{/literal}

{literal}
function editHelpGroup() {
{/literal}
	self.opener.location = '../help/EditHelpGroup.php?script={$SCRIPT_BASE_NAME}';
{literal}
}
{/literal}

{literal}
function editHelpEntry(id) {
{/literal}
	self.opener.location = '../help/EditHelp.php?id='+ id;
{literal}
}
{/literal}
</script>
</head>

<body>

<table class="tblList">
	<tr class="tblHeader">
		<td colspan="2">
			<h3>{t}Help{/t} {if $title != ''}for {$title}{/if}</h3>
		</td>
	</tr>
	{foreach from=$help_entries item=help}

		{if $help.type_change == TRUE}
			<tr>
				<td colspan="2">
					<br>
				</td>
			</tr>
			<tr class="tblHeader">
				<td colspan="2">
					<b>{t}Glossary{/t}</b>
				</td>
			</tr>
		{/if}

		{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}

		{if !empty($help.heading)}
		<tr class="tblHeader">
			<td colspan="2" style="text-align: left">
				<b>{$help.heading}</b>
			{if $permission->Check('help','add') OR $permission->Check('help','edit')}
			 [ <a href="javascript:editHelpEntry({$help.id})">{t}Edit{/t}</a> ]
			{/if}
			</td>
		</tr>
		{/if}
		<tr class="{$row_class}">
			<td width="5">
				<br>
			</td>
			<td style="text-align: left">
				{$help.body|nl2br}
			</td>
		</tr>
	{foreachelse}
		<tr class="tblDataGreyNH">
			<td>
				{t}Sorry, no help is available at this moment.{/t}
			</td>
		</tr>
	{/foreach}
	{if $permission->Check('help','add') OR $permission->Check('help','edit')}
	<tr class="tblHeader">
		<td colspan="2">
			[ <a href="javascript:editHelpGroupControl()">{t}Help Group Control{/t}</a> ]
			[ <a href="javascript:editHelpGroup()">{t}Help Group{/t}</a> ]

		</td>
	</tr>
	{/if}
</table>
<input type="hidden" name="help_data[id]" value="{$help_data.id}">
</body>
</html>
