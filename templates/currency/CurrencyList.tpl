{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="9" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				{if $base_currency == FALSE}
				<tr>
					<td id="rowError" colspan="9" align="center">
						{t}WARNING: There is no base currency set. Please create a base currency immediately.{/t}
					</td>
				</tr>
				{/if}
				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>				
					<td>
						{capture assign=label}{t}Name{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Currency{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="iso_code" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Rate{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="conversion_rate" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Auto Update{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="auto_update" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Base{/t}
					</td>
					<td>
						{t}Default{/t}
					</td>															
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$currencies name=currency item=currency}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $currency.deleted == TRUE OR $currency.status_id == 20 }
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.currency.iteration}
						</td>					
						<td>
							{$currency.name}
						</td>
						<td>
							{$currency.currency_name}
						</td>
						<td>
							{$currency.conversion_rate}
						</td>
						<td>
							{if $currency.auto_update == TRUE}
								{t}Yes{/t}
							{else}
								{t}No{/t}
							{/if}
						</td>
						<td>
							{if $currency.is_base == TRUE}
								{t}Yes{/t}
							{else}
								{t}No{/t}
							{/if}
						</td>						
						<td>
							{if $currency.is_default == TRUE}
								{t}Yes{/t}
							{else}
								{t}No{/t}
							{/if}
						</td>												
						<td>
							{assign var="currency_id" value=$currency.id}
							{if $permission->Check('currency','edit') }
								[ <a href="{urlbuilder script="EditCurrency.php" values="id=$currency_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$currency.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="9">
						{if $permission->Check('branch','add') }
							<input type="submit" name="action:Update_Rates" value="{t}Update Rates{/t}">
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
					<td class="tblPagingLeft" colspan="9" align="right">
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
