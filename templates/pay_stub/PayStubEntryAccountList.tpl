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
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Name{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Order{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="ps_order" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Debit Account{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="debit_account" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Credit Account{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="credit_account" current_column="$sort_column" current_order="$sort_order"}
					</td>

					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$rows item=row name=pay_stub_entry_account}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $row.status_id == 20}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.pay_stub_entry_account.iteration}
						</td>

						<td>
							{$row.type}
						</td>
						<td>
							{$row.name}
						</td>
						<td>
							{$row.ps_order}
						</td>
						<td>
							{$row.debit_account|default:"--"}
						</td>
						<td>
							{$row.credit_account|default:"--"}
						</td>
						<td>
							{assign var="row_id" value=$row.id}
							{if $permission->Check('pay_stub_account','edit')}
								[ <a href="{urlbuilder script="EditPayStubEntryAccount.php" values="id=$row_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$row.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="8">
						{if $permission->Check('pay_stub_account','add')}
							<input type="submit" class="button" name="action:Add_Presets" value="{t}Add Presets{/t}" onClick="return confirmSubmit('{t}Are you sure you want to add all presets based on your company location?{/t}')">
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('pay_stub_account','delete')}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('pay_stub_account','undelete')}
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
