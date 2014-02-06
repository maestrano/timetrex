{include file="sm_header.tpl"}
{include file="print.css.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<thead>
				<tr>
					<td class="tblPagingLeft" colspan="100" align="right">
						<a href="javascript: exportReport()"><img src="{$IMAGES_URL}/excel_icon.gif"></a>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="100">
						{t}Pay Period(s):{/t}
						{foreach from=$filter_data.pay_period_ids item=pay_period_id name=pay_period_rows}
							{$pay_period_options[$pay_period_id]}{if $smarty.foreach.pay_period_rows.first AND !$smarty.foreach.pay_period_rows.last}, {/if}
						{/foreach}
					</td>
				<tr>

				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>
					<td>
						{t}Date{/t}
					</td>
					<td>
						{t}Source{/t}
					</td>
					<td>
						{t}Comment{/t}
					</td>
					<td>
						{t}Account{/t}
					</td>
					<td>
						{t}Debit{/t}
					</td>
					<td>
						{t}Credit{/t}
					</td>
				</tr>
				</thead>
				<tbody>
				{foreach from=$rows item=row name=rows}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					<tr class="{$row_class}">
						{assign var="debit_count" value=$row.records.debit|@count}
						{assign var="credit_count" value=$row.records.credit|@count}
						<td rowspan="{$debit_count+$credit_count+2}">
							{$smarty.foreach.rows.iteration}
						</td>
						<td>
							 {getdate type="DATE" epoch=$row.transaction_date default=TRUE}
						</td>
						<td>
							 {$row.source|default:"N/A"}
						</td>
						<td>
							 {$row.comment}
						</td>
						<td colspan="3">
							<br>
						</td>
					</tr>
					{foreach from=$row.records.debit item=record name=records}
						<tr class="{$row_class}">
							<td colspan="3">
								<br>
							</td>
							<td>
								{$record.account}
							</td>
							<td align="right">
								{$record.amount}
							</td>
							<td align="right">
								-
							</td>
						</tr>
					{/foreach}
					{foreach from=$row.records.credit item=record name=records}
						<tr class="{$row_class}">
							<td colspan="3">
								<br>
							</td>
							<td>
								{$record.account}
							</td>
							<td align="right">
								-
							</td>
							<td align="right">
								{$record.amount}
							</td>
						</tr>
					{/foreach}
					{foreach from=$row.records.total item=record name=records}

						<tr class="{$row_class}" style="font-weight: bold; background: {if $record.total_diff != 0}red{else}green{/if}">
							<td align="right" colspan="3">
								{t}Total:{/t}<br>
							</td>
							<td align="right">
								{t}Difference:{/t} {$record.total_diff}
							</td>
							<td align="right">
								{$record.total_debits}
							</td>
							<td align="right">
								{$record.total_credits}
							</td>
						</tr>
					{/foreach}
				{foreachelse}
					<tr class="tblDataWhiteNH">
						<td colspan="100">
							{t}No results match your filter criteria.{/t}
						</td>
					</tr>
				{/foreach}

				<tr>
					<td class="tblHeader" colspan="100" align="center">
						{t}Generated:{/t} {getdate type="DATE+TIME" epoch=$generated_time}
					</td>
				</tr>
				</tbody>
			</table>
			<input type="hidden" name="filter_data" value="{$filter_data}">
		</form>
	</div>
</div>
{include file="footer.tpl"}