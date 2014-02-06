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
						{capture assign=label}{t}Code{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="manual_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Name{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$departments name=department item=department}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $department.deleted == TRUE OR $department.status_id == 20}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.department.iteration}
						</td>
						<td>
							{$department.manual_id}
						</td>
						<td>
							{$department.name}
						</td>
						<td>
							{assign var="department_id" value=$department.id}
							{if $permission->Check('department','edit')}
								[ <a href="{urlbuilder script="EditDepartment.php" values="id=$department_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
							{if $permission->Check('department','assign')}
								[ <a href="{urlbuilder script="EditDepartmentBranchUser.php" values="id=$department_id" merge="FALSE"}">{t}Employees{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$department.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('department','add')}
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('department','delete')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('department','undelete')}
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
