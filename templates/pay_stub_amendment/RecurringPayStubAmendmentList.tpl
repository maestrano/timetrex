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
						{include file="column_sort.tpl" label=$label sort_column="a.name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Description{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="a.description" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Status{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="a.status_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Frequency{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="a.frequency" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="a.pay_stub_entry_name_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$recurring_pay_stub_amendments item=pay_stub_amendment name=rpsa}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $pay_stub_amendment.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.rpsa.iteration}
						</td>
						<td>
							{$pay_stub_amendment.name}
						</td>
						<td>
							{$pay_stub_amendment.description}
						</td>
						<td>
							{$pay_stub_amendment.status}
						</td>
						<td>
							{$pay_stub_amendment.frequency}
						</td>
						<td>
							{$pay_stub_amendment.pay_stub_entry_name}
						</td>

						<td>
							{assign var="pay_stub_amendment_id" value=$pay_stub_amendment.id}
							{*
							{if $permission->Check('pay_stub_amendment','view') OR $permission->Check('pay_stub_amendment','view_own')}
								[ <a href="{urlbuilder script="ViewPayStubAmendment.php" values="id=$pay_stub_amendment_id" merge="FALSE"}">{t}View{/t}</a> ]
							{/if}
							*}
							{if $permission->Check('pay_stub_amendment','edit') OR $permission->Check('pay_stub_amendment','edit_own')}
								[ <a href="{urlbuilder script="EditRecurringPayStubAmendment.php" values="id=$pay_stub_amendment_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}

							[ <a href="{urlbuilder script="PayStubAmendmentList.php" values="recurring_ps_amendment_id=$pay_stub_amendment_id" merge="FALSE"}">{t}PS Amendments{/t}</a> ]

						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$pay_stub_amendment.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="8">
						{if $permission->Check('pay_stub_amendment','add')}
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('pay_stub_amendment','delete')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('pay_stub_amendment','undelete')}
							<input type="submit" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
			<tr>
				<td class="tblPagingLeft" colspan="7" align="right">
					{include file="pager.tpl" pager_data=$paging_data}
				</td>
			</tr>
			<input type="hidden" name="user_id" value="{$user_id}">
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
