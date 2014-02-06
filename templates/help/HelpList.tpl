{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				<tr class="tblHeader">
					<td>
						{capture assign=label}{t}ID{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Status{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="station_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Heading{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="status_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Private{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="source" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$help_entries item=help}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $help.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$help.id}
						</td>
						<td>
							{$help.type}
						</td>
						<td>
							{$help.status}
						</td>
						<td>
							{$help.heading}
						</td>
						<td>
							{if $help.private == TRUE}Yes{else}No{/if}
						</td>
						<td>
							{assign var="help_id" value=$help.id}
							{if $permission->Check('help','view')}
								[ View ]
							{/if}
							{if $permission->Check('help','view')}
								[ <a href="{urlbuilder script="EditHelp.php" values="id=$help_id" merge="FALSE"}">Edit</a> ]
							{/if}
							{if $permission->Check('station','assign')}
								[ <a href="{urlbuilder script="EditStationUser.php" values="id=$help_id" merge="FALSE"}">Groups</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$help.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('help','add')}
							<input type="submit" name="action" value="Add">
						{/if}
						{if $permission->Check('help','delete')}
							<input type="submit" name="action" value="Delete">
						{/if}
						{if $permission->Check('help','undelete')}
							<input type="submit" name="action" value="UnDelete">
						{/if}
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>1
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
