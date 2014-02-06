{include file="header.tpl"}

<script	language=JavaScript>

{literal}

function toggleAckButton() {
	button = document.getElementById('ack_button');
	if ( button.disabled == true ) {
		button.disabled = false;
	} else {
		button.disabled = true;
	}

	return true;
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList" id="message_table">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
			<tr>
				<td class="tblPagingLeft" colspan="7" align="right">
					{include file="pager.tpl" pager_data=$paging_data}
				</td>
			</tr>

		{if $require_ack == TRUE}
			<tr class="tblDataError">
				<td colspan="8">
					{t escape="no"}<b>NOTICE:</b> This messages requires your acknowledgment.{/t}
				</td>
			</tr>
		{/if}

		{foreach name="messages" from=$messages item=message}
			{if $smarty.foreach.messages.first}
				<tr class="tblHeader">
					<td colspan="2">
						{t}Message{/t}
					</td>
				</tr>
			{/if}

			{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
			{if $message.deleted == TRUE}
				{assign var="row_class" value="tblDataDeleted"}
			{/if}

			<tr class="{$row_class}" style="text-align:left; vertical-align: top;">
				<td width="10%">
					<b>{t}From:{/t}</b>
				</td>
				<td>
					{$message.from_user_full_name}
				</td>
			</tr>
			<tr class="{$row_class}" style="text-align:left; vertical-align: top;">
				<td>
					<b>{t}To:{/t}</b>
				</td>
				<td>
					{$message.to_user_full_name}
				</td>
			</tr>
			<tr class="{$row_class}" style="text-align:left; vertical-align: top;">
				<td>
					<b>{t}Date:{/t}</b>
				</td>
				<td>
					{getdate type="DATE+TIME" epoch=$message.created_date}
				</td>
			</tr>
			<tr class="{$row_class}" style="text-align:left; vertical-align: top;">
				<td>
					<b>{t}Subject:{/t}</b>
				</td>
				<td>
					{$message.subject|escape:'html'}
				</td>
			</tr>
			<tr class="{$row_class}" style="text-align:left; vertical-align: top;">
				<td>
					<b>{t}Body:{/t}</b>
				</td>
				<td>
					{$message.body|escape:'html'|nl2br}
				</td>
			</tr>

			{*
			{if $message.require_ack == TRUE}
				<tr class="{$row_class}">
					<td style="text-align:center;" colspan="2">
						{if $message.is_ack == TRUE}
							{t escape="no" 1=$message.ack_by_full_name}This message was acknowledged by <b>%1</b> on{/t} <b>{getdate type="DATE+TIME" epoch=$message.ack_date}</b>.
						{else}
						{t}This message is pending acknowledgment.{/t}
						{/if}
					</td>
				</tr>
			{/if}
			*}
			<tr>
				<td>
				</td>
			</tr>
		{/foreach}

		{*
		{if $require_ack == TRUE}
			<tr>
				<td colspan="2">
					<table class="editTable">
					<tr class="tblHeader">
						<td colspan="2">
							{t}Message Acknowledgment{/t}
						</td>
					</tr>

					<tr onClick="showHelpEntry('ack')">
						<td class="cellRightEditTable" colspan="2" align="center">
							{t escape="no" 1=$current_user->getFullName()}By clicking the checkbox, I <b>%1</b> hereby acknowledge<br> that I have read and understand this message in its entirety on{/t} <b>{getdate type="DATE" epoch=$current_date}</b>: <input type="checkbox" class="checkbox" name="data[require_ack]" onClick="toggleAckButton();" value="1">
						</td>
					</tr>

					<tr class="tblHeader">
						<td colspan="2">
							<input type="submit" id="ack_button" name="action:acknowledge_message" value="{t}Acknowledge Message{/t}" disabled>
							<input type="hidden" name="ack_message_id" value="{$ack_message_id}">
						</td>
					</tr>
					</table>
				</td>
			</tr>
		{/if}
		*}

		{if $permission->Check('message','add') AND $filter_folder_id == 10}
			<tr>
				<td colspan="2">
					{if !$mcf->isValid()}
						{include file="form_errors.tpl" object="mcf"}
					{/if}

					<table class="editTable">
					<tr class="tblHeader">
						<td colspan="2">
							{t}Reply{/t}
						</td>
					</tr>

					<tr onClick="showHelpEntry('subject')">
						<td class="{isvalid object="mcf" label="subject" value="cellLeftEditTable"}" style="width: 20%;">
							<a name="form_start"></a>
							{t}Subject:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="45" name="message_data[subject]" value="{if !empty($message_data.subject)}{$message_data.subject}{else}{$default_subject}{/if}">
						</td>
					</tr>
					<tr onClick="showHelpEntry('body')">
						<td class="{isvalid object="mcf" label="body" value="cellLeftEditTable"}">
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

	<input type="hidden" name="id" value="{$id}">
	<input type="hidden" name="parent_id" value="{$parent_id}">
	<input type="hidden" name="object_type_id" value="{$object_type_id}">
	<input type="hidden" name="object_id" value="{$object_id}">
	<input type="hidden" name="filter_folder_id" value="{$filter_folder_id}">
	<input type="hidden" name="sort_column" value="{$sort_column}">
	<input type="hidden" name="sort_order" value="{$sort_order}">
	<input type="hidden" name="page" value="{$paging_data.current_page}">
	</table>
</form>
</div>
{include file="footer.tpl"}