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
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$rows item=row name=schedule}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $row.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.schedule.iteration}
						</td>
						<td>
							{$row.name}
						</td>
						<td>
							{$row.description}
						</td>
						<td>
							{assign var="row_id" value=$row.id}
							{if $permission->Check('recurring_schedule_template','edit') OR ($permission->Check('recurring_schedule_template','edit_own') AND $row.is_owner === TRUE)}
								[ <a href="{urlbuilder script="EditRecurringScheduleTemplate.php" values="id=$row_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
							{if $permission->Check('recurring_schedule','view') OR ($permission->Check('recurring_schedule','view_own') )}
							  [ <a href="{urlbuilder script="RecurringScheduleControlList.php" values="filter_template_id=$row_id" merge="FALSE"}">{t}Recurring Schedules{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$row.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('recurring_schedule_template','add')}
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
							<input type="submit" class="button" name="action:copy" value="{t}Copy{/t}">
						{/if}
						{if $permission->Check('recurring_schedule_template','delete') OR $permission->Check('recurring_schedule_template','delete_own')}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('recurring_schedule_template','undelete')}
							<input type="submit" class="button" name="action:undelete" value="{t}UnDelete{/t}">
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