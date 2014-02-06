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
						{capture assign=label}{t}City{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="city" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Province / State{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="province" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$branches name=branch item=branch}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $branch.deleted == TRUE OR $branch.status_id == 20}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.branch.iteration}
						</td>
						<td>
							{$branch.manual_id}
						</td>
						<td>
							{$branch.name}
						</td>
						<td>
							{$branch.city}
						</td>
						<td>
							{$branch.province}
						</td>
						<td>
							{assign var="branch_id" value=$branch.id}
							{if $permission->Check('branch','edit') }
								[ <a href="{urlbuilder script="EditBranch.php" values="id=$branch_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
							{if $branch.map_url != ''}
							  [ <a href="{$branch.map_url}" target="_blank">{t}Map{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$branch.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('branch','add') }
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('branch','delete') }
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('branch','undelete') }
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
