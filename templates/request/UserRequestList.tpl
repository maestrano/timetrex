{include file="header.tpl"}
<script	language=JavaScript>

{literal}
function editRequest(requestID,userID) {
	try {
		eR=window.open('{/literal}{$BASE_URL}{literal}request/EditRequest.php?id='+ encodeURI(requestID),"Request","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
	} catch (e) {
		//DN
	}
}

function viewRequest(requestID) {
	try {
		vR=window.open('{/literal}{$BASE_URL}{literal}request/ViewRequest.php?id='+ encodeURI(requestID),"Request_"+ requestID,"toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
	} catch (e) {
		//DN
	}
}
{/literal}
</script>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" name="request" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="5" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				{if $permission->Check('request','view') OR $permission->Check('request','view_child')}
				<tr class="tblHeader">
					<td colspan="5">
						{t}Employee:{/t}
						<a href="javascript:navSelectBox('filter_user', 'prev');document.request.submit()"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
						<select name="filter_user_id" id="filter_user" onChange="this.form.submit()">
							{html_options options=$user_options selected=$filter_user_id}
						</select>
						<a href="javascript:navSelectBox('filter_user', 'next');document.request.submit()"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
					</td>
				</tr>
				{else}
					<input type="hidden" name="filter_user_id" value="{$filter_user_id}">
				{/if}
				<tr class="tblHeader">
					<td>
						{capture assign=label}{t}Date{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Status{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="status_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$requests item=request}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $request.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{getdate type="DATE" epoch=$request.date_stamp}
						</td>
						<td>
							{$request.status}
						</td>
						<td>
							{$request.type}
						</td>
						<td>
							{assign var="request_id" value=$request.id}
							{if $permission->Check('request','view') OR $permission->Check('request','view_own')}
								[ <a href="javascript:viewRequest({$request_id})">{t}View{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$request.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="5">
						{if $permission->Check('request','add')}
							<input type="button" class="button" value="{t}Add{/t}" onclick="editRequest();">
						{/if}
						{if $permission->Check('request','delete')}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('request','undelete')}
							<input type="submit" class="button" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="5" align="right">
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
