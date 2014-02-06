{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$accrual_policy} {t}for{/t} {$user_full_name}</span></div>
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
						#
					</td>
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Amount{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="amount" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Date{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="time_stamp" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$accruals item=accrual name=accrual}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $accrual.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.accrual.iteration}
						</td>
						<td>
							{$accrual.type}
						</td>
						<td>
							{gettimeunit value=$accrual.amount default=TRUE}
						</td>
						<td>
							{if $accrual.user_date_total_date_stamp != ''}
								{getdate type="DATE" epoch=$accrual.user_date_total_date_stamp default=TRUE}
								{assign var="date_stamp" value=$accrual.user_date_total_date_stamp}
							{else}
								{getdate type="DATE" epoch=$accrual.time_stamp default=TRUE}
								{assign var="date_stamp" value=$accrual.time_stamp}
							{/if}
						</td>
						<td>
							{assign var="accrual_id" value=$accrual.id}
							{assign var="user_id" value=$accrual.user_id}
							{if $accrual.user_date_total_id == '' AND $accrual.system_type == FALSE }
								{if $permission->Check('accrual','edit') OR $permission->Check('accrual','edit_child')}
									[ <a href="{urlbuilder script="EditUserAccrual.php" values="id=$accrual_id" merge="FALSE"}">{t}Edit{/t}</a> ]
								{/if}
							{/if}
							[ <a href="{urlbuilder script="../timesheet/ViewUserTimeSheet.php" values="filter_data[user_id]=$user_id,filter_data[date]=$date_stamp" merge="FALSE"}">{t}View{/t}</a> ]

						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$accrual.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('accrual','add')}
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('accrual','delete')}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('accrual','undelete')}
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
			<input type="hidden" name="user_id" value="{$user_id}">
			<input type="hidden" name="accrual_policy_id" value="{$accrual_policy_id}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
