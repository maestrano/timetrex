{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
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
						{t}#{/t}
					</td>
					<td>
						{capture assign=label}{t}Name{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>

					<td>
						{capture assign=label}{t}Status{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="status_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Start{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="start_date" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}End{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="end_date" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Transaction{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="transaction_date" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$pay_periods item=pay_period name=pay_period}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $pay_period.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.pay_period.iteration}
						</td>
						<td>
							{$pay_period.name}
						</td>
						<td>
							{$pay_period.type}
						</td>
						<td>
							{$pay_period.status}
						</td>
						<td>
							{$pay_period.start_date}
						</td>
						<td>
							{$pay_period.end_date}
						</td>
						<td>
							{$pay_period.transaction_date}
						</td>
						<td>
							{if $pay_period.id}
								{assign var="pay_period_id" value=$pay_period.id}
								[ <a href="{urlbuilder script="ViewPayPeriod.php" values="pay_period_id=$pay_period_id" merge="FALSE"}">{t}View{/t}</a> ]
								[ <a href="{urlbuilder script="EditPayPeriod.php" values="id=$pay_period_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							{if $pay_period.id != '' }
							<input type="checkbox" class="checkbox" name="ids[]" value="{$pay_period.id}">
							{/if}
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="10">
						{if $permission->Check('pay_period_schedule','add')}
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('pay_period_schedule','delete')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit('{t}WARNING: Deleting a pay period will also delete all punches assigned to that pay period. Once data is deleted it can not be recovered.\\n\\nAre you sure you wish to continue?{/t}')">
						{/if}
					</td>
				</tr>

				<tr>
					<td class="tblPagingLeft" colspan="10" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>

			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			<input type="hidden" name="id" value="{$id}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
