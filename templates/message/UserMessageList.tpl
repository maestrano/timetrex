{include file="header.tpl"}
<script	language=JavaScript>

{literal}
function viewRequest(requestID) {
	help=window.open('{/literal}{$BASE_URL}{literal}request/ViewRequest.php?id='+ encodeURI(requestID),"Request","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="9" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>

				{if $require_ack == TRUE}
					<tr class="tblDataError">
						<td colspan="9">
							{t escape="no"}<b>NOTICE:</b> Messages marked in red require your immediate attention.{/t}
						</td>
					</tr>
				{/if}

				<tr class="tblHeader">
					<td colspan="9">
						{t}Folder:{/t}
						<select name="filter_folder_id" onChange="this.form.submit()">
							{html_options options=$folder_options selected=$filter_folder_id}
						</select>

					</td>
				</tr>
				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>
					<td>
						{if $filter_folder_id == 10}
							{capture assign=label}{t}From{/t}{/capture}
							{include file="column_sort.tpl" label=$label sort_column="from_last_name" current_column="$sort_column" current_order="$sort_order"}
						{else}
							{capture assign=label}{t}To{/t}{/capture}
							{include file="column_sort.tpl" label=$label sort_column="to_last_name" current_column="$sort_column" current_order="$sort_order"}
						{/if}
					</td>
					<td>
						{capture assign=label}{t}Subject{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="subject" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="object_type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Date{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="a.created_date" current_column="$sort_column" current_order="$sort_order"}
					</td>
					{if $filter_folder_id == 20 AND $show_ack_column == TRUE }
					<td>
						{capture assign=label}{t}Requires Ack{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="require_ack" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Ack Date{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="ack_date" current_column="$sort_column" current_order="$sort_order"}
					</td>
					{/if}
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>

				{foreach from=$messages item=message name=message}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $message.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					{if $message.require_ack == TRUE AND $message.ack_date == ''}
						{assign var="row_class" value="tblDataError"}
					{/if}
					<tr class="{$row_class}" {if $filter_folder_id == 10 AND $message.status_id == 10}style="font-weight: bold;"{/if}>
						<td>
							{$smarty.foreach.message.iteration}
						</td>
						<td>
							{$message.user_full_name}
						</td>
						<td>
							{$message.subject|escape:'html'}
						</td>
						<td>
							{$message.object_type}
						</td>
						<td>
							{getdate type="DATE+TIME" epoch=$message.created_date default=TRUE}
						</td>
						{if $filter_folder_id == 20 AND $show_ack_column == TRUE }
						<td>
							{if $message.require_ack == TRUE}
								{t}Yes{/t}
							{else}
								{t}No{/t}
							{/if}
						</td>

						<td>
							{getdate type="DATE+TIME" epoch=$message.ack_date default=TRUE}
						</td>
						{/if}
						<td>
							{assign var="object_id" value=$message.object_id}
							{assign var="message_id" value=$message.id}
							{if $message.object_type_id == 90}
								{if $permission->Check('message','view') OR $permission->Check('message','view_own')}
									<a href="{urlbuilder script="../message/ViewMessage.php" values="object_type_id=90,object_id=$object_id,id=$message_id" merge="FALSE"}">{t}View{/t}</a>
								{/if}
							{/if}
							{if $message.object_type_id == 50}
								{if $permission->Check('request','view') OR $permission->Check('request','view_own')}
									<a href="javascript:viewRequest({$object_id})">{t}View{/t}</a>
								{/if}
							{/if}
							{if $message.object_type_id == 5}
								{if $permission->Check('message','view') OR $permission->Check('message','view_own')}
									<a href="{urlbuilder script="../message/ViewMessage.php" values="filter_folder_id=$filter_folder_id,object_type_id=5,object_id=$object_id,id=$message_id" merge="FALSE"}">{t}View{/t}</a>
								{/if}
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$message.id}">
						</td>
					</tr>
				{/foreach}

				<tr>
					<td class="tblActionRow" colspan="9">
						{if $permission->Check('message','add')}
							<input type="submit" name="action:new_message" value="{t}New Message{/t}">
						{/if}
						{if $permission->Check('message','delete') OR $permission->Check('message','delete_own')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('message','undelete')}
							<input type="submit" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>

				<tr>
					<td class="tblPagingLeft" colspan="9" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
