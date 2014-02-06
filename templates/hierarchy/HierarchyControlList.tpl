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
						{t}#{/t}
					</td>
					<td>
						{capture assign=label}{t}Name{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Description{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="description" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Objects{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="objects" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$hierarchy_controls name=hierarchy_control item=hierarchy_control}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $hierarchy_control.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.hierarchy_control.iteration}
						</td>
						<td>
							{$hierarchy_control.name}
						</td>
						<td>
							{$hierarchy_control.description}
						</td>
						<td>
							{foreach from=$hierarchy_control.object_types item=object_type}
								{$object_type}<br>
							{/foreach}
						</td>
						<td>
							{assign var="hierarchy_control_id" value=$hierarchy_control.id}
							{*
							{if $permission->Check('hierarchy','view_own') OR $permission->Check('hierarchy','view')}
								[ <a href="{urlbuilder script="HierarchyList.php" values="hierarchy_id=$hierarchy_control_id" merge="FALSE"}">{t}View{/t}</a> ]
							{/if}
							*}
							{if $permission->Check('hierarchy','edit_own') OR $permission->Check('hierarchy','edit')}
								[ <a href="{urlbuilder script="EditHierarchyControl.php" values="hierarchy_control_id=$hierarchy_control_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$hierarchy_control.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('hierarchy','add')}
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('hierarchy','delete')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('hierarchy','undelete')}
							<input type="submit" name="action:undelete" value="{t}UnDelete{/t}">
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
