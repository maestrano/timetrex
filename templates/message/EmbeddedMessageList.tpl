<html>
<head>
<link rel="stylesheet" href="{$BASE_URL}global.css.php" type="text/css" />
<SCRIPT language=JavaScript src="{$BASE_URL}global.js.php" type=text/javascript></SCRIPT>
</head>
<body bgcolor="#7a9bbd">

<div id="rowContentInner">
		<table class="tblList" id="message_table">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
			<tr>
				<td class="tblPagingLeft" colspan="7" align="right">
					{include file="pager.tpl" pager_data=$paging_data}
				</td>
			</tr>

		{foreach name="messages" from=$messages item=message}
			{if $smarty.foreach.messages.first}
			<tr class="tblHeader">
				<td>
					{t}Posted By / Date{/t}
				</td>
				<td width="80%">
					{t}Message{/t}
				</td>
			</tr>
			{/if}

			{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
			{if $message.deleted == TRUE}
				{assign var="row_class" value="tblDataDeleted"}
			{/if}

			<tr class="{$row_class}">
				<td style="text-align:left;">
					{$message.from_user_full_name}
				</td>
				<td rowspan="2" style="text-align:left; vertical-align: top;">
					<b>{t}Subject:{/t}</b> {$message.subject|escape:'html'}<br><br>
					{$message.body|escape:'html'|nl2br}
				</td>
			</tr>
			<tr class="{$row_class}">
				<td style="text-align:left;">
					{getdate type="DATE+TIME" epoch=$message.created_date default=TRUE}
				</td>
			</tr>
			<tr>
				<td>
				</td>
			</tr>
		{/foreach}

		{if $permission->Check('message','add')}
			<tr>
			<td colspan="2">
				<table class="editTable">
				{if !$mcf->isValid()}
					{include file="form_errors.tpl" object="mcf"}
				{/if}
				<tr class="tblHeader">
					<td colspan="2">
						{t}New Message{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('subject')">
					<td class="{isvalid object="mf" label="subject" value="cellLeftEditTable"}" style="width: 20%;">
						<a name="form_start"></a>
						{t}Subject:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="45" name="message_data[subject]" value="{if !empty($message_data.subject)}{$message_data.subject}{else}{$default_subject}{/if}">
					</td>
				</tr>
				<tr onClick="showHelpEntry('body')">
					<td class="{isvalid object="mf" label="body" value="cellLeftEditTable"}">
						{t}Body:{/t}
					</td>
					<td class="cellRightEditTable">
						<textarea rows="5" cols="50" name="message_data[body]">{$message_data.body}</textarea>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2">
						<input type="submit" name="action:Submit_Message" value="{t}Submit Message{/t}">
					</td>
				</tr>
				</table>
			</td>
			</tr>
		{/if}
		<tr>
			<td class="tblPagingLeft" colspan="7" align="right">
				{include file="pager.tpl" pager_data=$paging_data}
			</td>
		</tr>

	<input type="hidden" name="parent_id" value="{$parent_id}">
	<input type="hidden" name="object_type_id" value="{$object_type_id}">
	<input type="hidden" name="object_id" value="{$object_id}">
    <input type="hidden" name="object_user_id" value="{$object_user_id}">
	<input type="hidden" name="sort_column" value="{$sort_column}">
	<input type="hidden" name="sort_order" value="{$sort_order}">
	<input type="hidden" name="page" value="{$paging_data.current_page}">
	</table>
</form>
</div>

<script language="JavaScript">
{literal}
<!--
        var contentObj = document.getElementById('message_table');
        var contentHeight = contentObj.offsetHeight;

        //alert('Height: '+ contentHeight);
        var targetObj = parent.document.getElementById('MessageFactory');

        var newTargetHeight = contentHeight + 4;

        targetObj.style.height = newTargetHeight + "px";
{/literal}
//-->
</script>

{* {include file="footer.tpl"} *}
</body>
</html>