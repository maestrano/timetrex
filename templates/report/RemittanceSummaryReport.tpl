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
					<td class="tblPagingLeft" colspan="7" align="right">
						<Br>
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

				{if $form_data.amount_due > 0}
				<tr>
					<td colspan="100">
						<table class="tblList">
							<tr>
								<td colspan="10">
									<table width="100%">
										<tr class="tblHeader">
											<td>
												{t}CPP Contributions{/t}
											</td>
											<td>
												{t}EI Premiums{/t}
											</td>
											<td>
												{t}Tax Deductions{/t}
											</td>
											<td>
												{t}Current Payment{/t}
											</td>
											<td>
												{t}Gross Payroll{/t}
											</td>
											<td>
												{t}No. of Employees{/t}
											</td>
											<td>
												{t}End of Period{/t}
											</td>

											<td>
												{t}Due Date{/t}
											</td>
										</tr>
										<tr class="tblDataWhite">
											<td>
												{$form_data.cpp}
											</td>
											<td>
												{$form_data.ei}
											</td>
											<td>
												{$form_data.tax}
											</td>
											<td>
												{$form_data.amount_due}
											</td>
											<td>
												{$form_data.gross_payroll}
											</td>
											<td>
												{$form_data.employees}
											</td>
											<td>
												{$form_data.end_remitting_period}
											</td>
											<td>
												{getdate type="DATE" epoch=$form_data.due_date  default=TRUE}
											</td>
										</tr>
									</table>
								</td>
							</tr>

						</table>
					</td>
				</tr>
				{/if}

				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>

					{foreach from=$columns item=column name=column}
						<td>
							{$column}
						</td>
					{/foreach}
				</tr>
				</thead>

				<tbody>
				{foreach from=$rows item=row name=rows}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					<tr class="{$row_class}" {if $smarty.foreach.rows.last}style="font-weight: bold;"{/if}>
						<td>
							{if $smarty.foreach.rows.last}
								<br>
							{else}
								{$smarty.foreach.rows.iteration}
							{/if}
						</td>
						{foreach from=$columns key=key item=column name=column}
							<td>
								{$row[$key]|default:"--"}
							</td>
						{/foreach}
					</tr>
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
		</form>
	</div>
</div>
{include file="footer.tpl"}