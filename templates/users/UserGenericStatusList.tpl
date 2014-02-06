{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList" id="user_tbl">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="10" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				{if $batch_title != ''}
				<tr class="tblHeader">
					<td colspan="8">
						{$batch_title} {if $batch_next_page != ''}[ <a href="{$batch_next_page}">{t}Continue{/t}</a> ]{/if}
					</td>
				</tr>
				{/if}
				<tr class="tblHeader">
					<td colspan="8">
						<font color="red">{t}Failed:{/t}</font> {$status_count.status.10.total}/{$status_count.total} ({$status_count.status.10.percent}%)
						&nbsp;&nbsp;&nbsp;&nbsp;<font color="blue">{t}Warning:{/t}</font> {$status_count.status.20.total}/{$status_count.total} ({$status_count.status.20.percent}%)
						&nbsp;&nbsp;&nbsp;&nbsp;<font color="green">{t}Success:{/t}</font> {$status_count.status.30.total}/{$status_count.total} ({$status_count.status.30.percent}%)
					</td>
				</tr>
				<tr class="tblHeader">
					<td>
						#
					</td>
					<td width="90%">
						{capture assign=label}{t}Label{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="label" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td width="10%">
						{capture assign=label}{t}Status{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="status_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
				</tr>
				{foreach from=$rows item=row name=row}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $roe.deleted == TRUE }
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td {if $row.description != ''}rowspan="2"{/if}>
							{$smarty.foreach.row.iteration}
						</td>
						<td align="left">
							&nbsp;<b>{$row.label}</b>
						</td>
						<td {if $row.description != ''}rowspan="2"{/if}>
							<font color="{if $row.status_id == 10}red{/if}{if $row.status_id == 20}blue{/if}{if $row.status_id == 30}green{/if}">
								<b>{$row.status}</b>
							</font>
						</td>
					</tr>
					{if $row.description != ''}
					<tr class="{$row_class}">
						<td colspan="1" align="left">
							<ul>{$row.description|nl2br}</ul>
						</td>
					</tr>
					{/if}
				{/foreach}
				<tr>
					<td class="tblPagingLeft" colspan="10" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			<input type="hidden" name="batch_id" value="{$batch_id}">
			<input type="hidden" name="batch_title" value="{$batch_title}">
			<input type="hidden" name="batch_next_page" value="{$batch_next_page}">

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
