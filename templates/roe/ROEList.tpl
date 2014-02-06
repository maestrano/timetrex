{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title} {if $user_id != ''}for {$user_full_name}{/if}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="10" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				<tr class="tblHeader">
					<td>
						{capture assign=label}{t}First Day{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="first_date" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Last Day{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="last_dat" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Code{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="code" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$roes item=roe}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $roe.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{getdate type="DATE" epoch=$roe.first_date}
						</td>
						<td>
							{getdate type="DATE" epoch=$roe.last_date}
						</td>
						<td>
							{$roe.code}
						</td>
						<td>
							{assign var="id" value=$roe.id}
							{if $permission->Check('roe','edit')}
								[ <a href="{urlbuilder script="EditROE.php" values="id=$id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
							{if $permission->Check('roe','view')}
								[ <a href="{urlbuilder script="ROEList.php" values="action:view,id=$id" merge="FALSE"}">{t}View{/t}</a> ]
								[ <a href="{urlbuilder script="ROEList.php" values="action:print,id=$id" merge="FALSE"}">{t}Print{/t}</a> ]
								[ <a href="{urlbuilder script="ROEList.php" values="action:export,id=$id" merge="FALSE"}">{t}Export{/t}</a> ]
							{/if}

						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$roe.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="10">
						{if $permission->Check('roe','view')}
							<input type="submit" name="action:view" value="{t}View{/t}">
							<input type="submit" name="action:print" value="{t}Print{/t}">
							<input type="submit" name="action:export" value="{t}Export{/t}">
						{/if}
						{if $permission->Check('user','add')}
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('user','delete')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('user','undelete')}
							<input type="submit" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="10" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
			<input type="hidden" name="user_id" value="{$user_id}">
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
