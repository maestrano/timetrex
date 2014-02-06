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
						{include file="column_sort.tpl" label="ID" sort_column="id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{include file="column_sort.tpl" label="Script" sort_column="script_name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{include file="column_sort.tpl" label="Name" sort_column="name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$help_groups item=help}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $help.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$help.id}
						</td>
						<td>
							{$help.script_name}
						</td>
						<td>
							{$help.name}
						</td>
						<td>
							{assign var="help_id" value=$help.id}
							{if $permission->Check('help','view')}
								[ View ]
							{/if}
							{if $permission->Check('help','view')}
								[ <a href="{urlbuilder script="EditHelpGroupControl.php" values="id=$help_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
							{if $permission->Check('station','assign')}
								[ <a href="{urlbuilder script="EditHelpGroup.php" values="id=$help_id" merge="FALSE"}">{t}Help Entries{/t}</a> ]
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
				</tr>

			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
